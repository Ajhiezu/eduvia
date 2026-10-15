<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\School;
use App\Services\ParentPortalService;
use App\Services\SchoolFinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentFinanceController extends Controller
{
    public function __construct(
        protected ParentPortalService $parentPortalService,
        protected SchoolFinanceService $financeService
    ) {}

    /**
     * Parent Portal finance overview for active child.
     */
    public function index(Request $request): View
    {
        $parent = $request->user();
        $school = $parent->school ?? School::current();

        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $requestedChildId = $request->filled('child_id') ? (int) $request->query('child_id') : null;
        $activeChild = $this->parentPortalService->getActiveChild($parent, $requestedChildId);

        $invoices = collect();
        $payments = collect();
        $totalInvoiced = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $pendingPaymentsCount = 0;

        if ($activeChild) {
            $invoices = Invoice::query()
                ->where('student_id', $activeChild->id)
                ->where('status', '!=', Invoice::STATUS_CANCELLED)
                ->with(['feeCategory', 'academicYear', 'items'])
                ->latest('due_date')
                ->get();

            $payments = Payment::query()
                ->where('student_id', $activeChild->id)
                ->with(['invoice.feeCategory', 'receipt', 'verifier'])
                ->latest('payment_date')
                ->get();

            $totalInvoiced = (int) $invoices->sum('total_amount');
            $totalPaid = (int) $invoices->sum('paid_amount');
            $totalOutstanding = max(0, $totalInvoiced - $totalPaid);
            $pendingPaymentsCount = $payments->where('status', Payment::STATUS_PENDING_VERIFICATION)->count();
        }

        return view('parent.finance.index', compact(
            'parent',
            'school',
            'activeChildren',
            'activeChild',
            'invoices',
            'payments',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding',
            'pendingPaymentsCount'
        ));
    }

    /**
     * Show detail of an invoice for parent's verified child.
     */
    public function showInvoice(Invoice $invoice, Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);

        // Security / IDOR check: must belong to one of parent's active children
        if (! $activeChildren->pluck('id')->contains($invoice->student_id)) {
            abort(403, 'Akses ditolak: Tagihan ini bukan milik anak yang terhubung dengan akun Anda.');
        }

        $invoice->load([
            'student.studentProfile',
            'academicYear',
            'feeCategory',
            'items',
            'payments.receipt',
            'payments.verifier',
        ]);

        return view('parent.finance.show', compact('invoice'));
    }

    /**
     * Show payment submission form for an invoice.
     */
    public function pay(Invoice $invoice, Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);

        if (! $activeChildren->pluck('id')->contains($invoice->student_id)) {
            abort(403, 'Akses ditolak: Tagihan ini bukan milik anak yang terhubung dengan akun Anda.');
        }

        if ($invoice->isPaid() || $invoice->status === Invoice::STATUS_CANCELLED) {
            abort(400, 'Tagihan ini telah lunas atau telah dibatalkan.');
        }

        return view('parent.finance.pay', compact('invoice'));
    }

    /**
     * Process manual bank transfer payment proof submission.
     */
    public function submitPayment(Invoice $invoice, Request $request): RedirectResponse
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);

        if (! $activeChildren->pluck('id')->contains($invoice->student_id)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->financeService->submitPaymentProof(
            invoice: $invoice,
            payer: $parent,
            amount: (int) $validated['amount'],
            paymentDate: $validated['payment_date'],
            paymentMethod: $validated['payment_method'],
            referenceNumber: $validated['reference_number'] ?? null,
            proofFile: $request->file('proof_file'),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('parent.finance.index')
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Petugas keuangan sekolah akan memverifikasi bukti Anda.');
    }
}
