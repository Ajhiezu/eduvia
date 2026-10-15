<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FeeCategory;
use App\Models\GradeLevel;
use App\Models\Invoice;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolFinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected SchoolFinanceService $financeService
    ) {}

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Display a listing of invoices with filters and metrics.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $status = $request->input('status');
        $academicYearId = $request->input('academic_year_id');
        $classroomId = $request->input('classroom_id');
        $search = $request->input('search');

        $query = Invoice::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student.studentProfile', 'academicYear', 'feeCategory'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($classroomId, function ($q) use ($classroomId) {
                $q->whereHas('student.classroomEnrollments', function ($ce) use ($classroomId) {
                    $ce->where('classroom_id', $classroomId)->where('enrollment_status', 'active');
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%")
                                ->orWhereHas('studentProfile', function ($sp) use ($search) {
                                    $sp->where('nis', 'like', "%{$search}%")
                                        ->orWhere('nisn', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest('id');

        $invoices = $query->paginate(20)->withQueryString();

        // Metrics for authorized school
        $metricsQuery = Invoice::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $totalInvoiced = (clone $metricsQuery)->where('status', '!=', Invoice::STATUS_CANCELLED)->sum('total_amount');
        $totalPaid = (clone $metricsQuery)->where('status', '!=', Invoice::STATUS_CANCELLED)->sum('paid_amount');
        $totalOutstanding = max(0, $totalInvoiced - $totalPaid);
        $totalOverdueCount = (clone $metricsQuery)->overdue()->count();

        $academicYears = AcademicYear::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('start_date')
            ->get();

        $classrooms = Classroom::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get();

        return view('finance.invoices.index', compact(
            'invoices',
            'academicYears',
            'classrooms',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding',
            'totalOverdueCount'
        ));
    }

    /**
     * Show form for creating a single invoice.
     */
    public function create(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $students = User::query()
            ->where('role', UserRole::STUDENT->value)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['studentProfile', 'classroomEnrollments.classroom'])
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->get();

        $feeCategories = FeeCategory::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('finance.invoices.create', compact('students', 'academicYears', 'feeCategories'));
    }

    /**
     * Store a single invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $school = School::find($this->currentSchoolId()) ?? School::firstOrFail();

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'title' => 'required|string|max:200',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount_amount' => 'nullable|integer|min:0',
            'items.*.fee_category_id' => 'nullable|exists:fee_categories,id',
        ]);

        $student = User::findOrFail($validated['student_id']);

        $invoice = $this->financeService->createInvoice($school, $student, $validated, auth()->user());

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil diterbitkan.");
    }

    /**
     * Show form for bulk generating recurring invoices.
     */
    public function bulkCreate(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $feeCategories = FeeCategory::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->get();

        $school = $schoolId ? School::find($schoolId) : School::current();

        $gradeLevels = GradeLevel::query()
            ->when($school && ! empty($school->educational_levels), function ($q) use ($school) {
                $q->whereIn('educational_level', (array) $school->educational_levels);
            })
            ->orderBy('order')
            ->get();

        $classrooms = Classroom::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get();

        return view('finance.invoices.bulk', compact(
            'feeCategories',
            'academicYears',
            'gradeLevels',
            'classrooms'
        ));
    }

    /**
     * Process bulk recurring invoices generation.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $school = School::find($this->currentSchoolId()) ?? School::firstOrFail();

        $validated = $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'billing_period_key' => 'required|string|max:30', // e.g., '2026-10'
            'due_date' => 'required|date',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $feeCategory = FeeCategory::findOrFail($validated['fee_category_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        $result = $this->financeService->bulkGenerateRecurringInvoices(
            school: $school,
            feeCategory: $feeCategory,
            academicYear: $academicYear,
            billingPeriodKey: $validated['billing_period_key'],
            dueDate: $validated['due_date'],
            gradeLevelId: $validated['grade_level_id'] ?? null,
            classroomId: $validated['classroom_id'] ?? null,
            creator: auth()->user()
        );

        $msg = sprintf(
            'Pembuatan tagihan massal selesai. Total kandidat: %d, Berhasil diterbitkan: %d, Dilewati (sudah ada): %d.',
            $result['total_candidates'],
            $result['generated_count'],
            $result['skipped_count']
        );

        return redirect()->route('finance.invoices.index')->with('success', $msg);
    }

    /**
     * Show detailed view of an invoice.
     */
    public function show(Invoice $invoice): View
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $invoice->school_id !== (int) $schoolId) {
            abort(403);
        }

        $invoice->load([
            'student.studentProfile',
            'academicYear',
            'feeCategory',
            'items.feeCategory',
            'payments.receipt',
            'payments.recorder',
            'payments.verifier',
            'reminders.sender',
            'creator',
        ]);

        return view('finance.invoices.show', compact('invoice'));
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $invoice->school_id !== (int) $schoolId) {
            abort(403);
        }

        if ($invoice->paid_amount > 0) {
            return back()->withErrors(['invoice' => 'Tagihan yang sudah memiliki transaksi pembayaran tidak dapat dibatalkan secara langsung.']);
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil dibatalkan.");
    }

    /**
     * Send payment reminder.
     */
    public function sendReminder(Request $request, Invoice $invoice): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $invoice->school_id !== (int) $schoolId) {
            abort(403);
        }

        $type = $request->input('reminder_type', 'due_date');
        $this->financeService->logPaymentReminder($invoice, auth()->user(), $type, 'in_app');

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Pengingat pembayaran berhasil dicatat dan dikirim.');
    }
}
