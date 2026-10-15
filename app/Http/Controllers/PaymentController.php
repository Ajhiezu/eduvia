<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\School;
use App\Services\SchoolFinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected SchoolFinanceService $financeService
    ) {}

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Display a listing of verified payments & transactions.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $status = $request->input('status');
        $method = $request->input('method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $query = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student.studentProfile', 'invoice', 'receipt', 'recorder', 'verifier'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->when($startDate, fn ($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('payment_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('invoice', function ($iq) use ($search) {
                            $iq->where('invoice_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('payment_date')
            ->latest('id');

        $payments = $query->paginate(20)->withQueryString();

        // Metrics for authorized school
        $baseQuery = Payment::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $totalVerifiedAmount = (clone $baseQuery)->where('status', Payment::STATUS_VERIFIED)->sum('amount');
        $totalPendingCount = (clone $baseQuery)->where('status', Payment::STATUS_PENDING_VERIFICATION)->count();
        $totalReversedCount = (clone $baseQuery)->where('status', Payment::STATUS_REVERSED)->count();

        return view('finance.payments.index', compact(
            'payments',
            'totalVerifiedAmount',
            'totalPendingCount',
            'totalReversedCount'
        ));
    }

    /**
     * Display the payment verification queue for staff.
     */
    public function verificationQueue(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $pendingPayments = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', Payment::STATUS_PENDING_VERIFICATION)
            ->with(['student.studentProfile', 'invoice', 'payer'])
            ->latest('id')
            ->paginate(15);

        return view('finance.payments.queue', compact('pendingPayments'));
    }

    /**
     * Show form for recording a manual payment for an invoice.
     */
    public function create(Request $request): View
    {
        $schoolId = $this->currentSchoolId();
        $invoiceId = $request->query('invoice_id');

        $selectedInvoice = null;
        if ($invoiceId) {
            $selectedInvoice = Invoice::query()
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->with(['student.studentProfile', 'feeCategory'])
                ->findOrFail($invoiceId);
        }

        $unpaidInvoices = Invoice::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->unpaid()
            ->with(['student.studentProfile', 'feeCategory'])
            ->orderBy('due_date')
            ->take(50)
            ->get();

        return view('finance.payments.create', compact('selectedInvoice', 'unpaidInvoices'));
    }

    /**
     * Store a manual payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|integer|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->findOrFail($validated['invoice_id']);

        $payment = $this->financeService->recordManualPayment(
            $invoice,
            $validated,
            auth()->user()
        );

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', "Pembayaran {$payment->payment_number} sejumlah {$payment->formatted_amount} berhasil dicatat.");
    }

    /**
     * Verify a pending manual payment submission.
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $payment->school_id !== (int) $schoolId) {
            abort(403);
        }

        $this->financeService->verifyPaymentSubmission($payment, auth()->user());

        return back()->with('success', "Pembayaran {$payment->payment_number} telah disetujui & diverifikasi.");
    }

    /**
     * Reject a pending manual payment submission.
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $payment->school_id !== (int) $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->financeService->rejectPaymentSubmission(
            $payment,
            auth()->user(),
            $validated['rejection_reason']
        );

        return back()->with('success', "Pembayaran {$payment->payment_number} telah ditolak dengan catatan.");
    }

    /**
     * Reverse / correct a previously verified payment.
     */
    public function reverse(Request $request, Payment $payment): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $payment->school_id !== (int) $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reversal = $this->financeService->reversePayment(
            $payment,
            auth()->user(),
            $validated['reason']
        );

        return back()->with('success', "Pembayaran {$payment->payment_number} berhasil dibatalkan/dikoreksi.");
    }

    /**
     * Download or view payment proof from private storage securely.
     */
    public function downloadProof(Payment $payment): StreamedResponse
    {
        $user = auth()->user();
        $schoolId = $this->currentSchoolId();

        // Tenant and authorization check
        if ($schoolId && (int) $payment->school_id !== (int) $schoolId) {
            abort(403);
        }

        // Parent can only view proof they uploaded
        if ($user->isParent() && (int) $payment->paid_by !== (int) $user->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses bukti pembayaran milik Anda.');
        }

        if (! $payment->proof_path || ! Storage::disk('local')->exists($payment->proof_path)) {
            abort(404, 'File bukti pembayaran tidak ditemukan pada penyimpanan privat.');
        }

        return Storage::disk('local')->download($payment->proof_path);
    }
}
