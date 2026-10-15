<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\FeeCategory;
use App\Models\GradeLevel;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ParentStudentRelation;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\Receipt;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentDiscount;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\SchoolFinanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected School $otherSchool;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected GradeLevel $gradeLevel;

    protected Classroom $classroom;

    protected User $admin;

    protected User $otherAdmin;

    protected User $student;

    protected User $otherStudent;

    protected User $parent;

    protected SchoolFinanceService $financeService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->school = School::factory()->create([
            'name' => 'SMA Eduvia Utama',
            'slug' => 'sma-eduvia-utama',
            'educational_levels' => ['sma'],
        ]);

        $this->otherSchool = School::factory()->create([
            'name' => 'SMP Eduvia Sebelah',
            'slug' => 'smp-eduvia-sebelah',
            'educational_levels' => ['smp'],
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->gradeLevel = GradeLevel::firstOrCreate(
            ['educational_level' => 'sma', 'level_number' => 10],
            ['name' => 'X (Sepuluh)', 'order' => 10]
        );

        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_level_id' => $this->gradeLevel->id,
            'name' => 'X-MIPA-1',
        ]);

        $this->admin = User::factory()->schoolAdmin()->create([
            'school_id' => $this->school->id,
        ]);

        $this->otherAdmin = User::factory()->schoolAdmin()->create([
            'school_id' => $this->otherSchool->id,
        ]);

        $this->student = User::factory()->student()->create([
            'school_id' => $this->school->id,
            'name' => 'Ahmad Santoso',
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'school_id' => $this->school->id,
            'nis' => '10001',
            'student_status' => 'active',
        ]);

        ClassroomStudent::create([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_status' => 'active',
            'joined_at' => now()->subMonths(3),
        ]);

        $this->otherStudent = User::factory()->student()->create([
            'school_id' => $this->otherSchool->id,
            'name' => 'Budi Pratama',
        ]);

        StudentProfile::create([
            'user_id' => $this->otherStudent->id,
            'school_id' => $this->otherSchool->id,
            'nis' => '20001',
            'student_status' => 'active',
        ]);

        $this->parent = User::factory()->parentRole()->create([
            'school_id' => $this->school->id,
            'name' => 'Ibu Siti',
        ]);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'mother',
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'verified_at' => now(),
        ]);

        $this->financeService = app(SchoolFinanceService::class);
    }

    /**
     * Test 1: Admin can configure fee category and specialized fee rule.
     */
    public function test_admin_can_create_fee_category_and_rule(): void
    {
        $response = $this->actingAs($this->admin)->post(route('finance.categories.store'), [
            'code' => 'SPP',
            'name' => 'SPP Bulanan Reguler',
            'type' => 'recurring',
            'billing_period' => 'monthly',
            'amount' => 500000,
            'description' => 'Iuran bulanan operasional',
        ]);

        $response->assertRedirect(route('finance.categories.index'));

        $category = FeeCategory::where('code', 'SPP')->where('school_id', $this->school->id)->first();
        $this->assertNotNull($category);
        $this->assertEquals(500000, $category->amount);

        // Store specific rule override for classroom
        $ruleResponse = $this->actingAs($this->admin)->post(route('finance.rules.store'), [
            'fee_category_id' => $category->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'amount' => 450000,
            'notes' => 'Tarif subsidi kelas X-MIPA-1',
        ]);

        $ruleResponse->assertRedirect(route('finance.categories.index'));

        $effective = $category->getEffectiveAmount($this->academicYear->id, null, $this->classroom->id);
        $this->assertEquals(450000, $effective);
    }

    /**
     * Test 2: Admin can create single invoice with multiple line items and integer currency.
     */
    public function test_admin_can_create_single_invoice_with_items(): void
    {
        $feeCategory = FeeCategory::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 500000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('finance.invoices.store'), [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_category_id' => $feeCategory->id,
            'title' => 'Tagihan SPP & Seragam',
            'issue_date' => Carbon::today()->toDateString(),
            'due_date' => Carbon::today()->addDays(14)->toDateString(),
            'items' => [
                [
                    'item_name' => 'SPP November 2026',
                    'quantity' => 1,
                    'unit_price' => 500000,
                    'discount_amount' => 50000,
                    'fee_category_id' => $feeCategory->id,
                ],
                [
                    'item_name' => 'Seragam Olahraga',
                    'quantity' => 2,
                    'unit_price' => 150000,
                    'discount_amount' => 0,
                    'fee_category_id' => null,
                ],
            ],
        ]);

        $invoice = Invoice::where('student_id', $this->student->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('finance.invoices.show', $invoice));

        // Subtotal = 500.000 + (2 * 150.000) = 800.000
        // Discount = 50.000
        // Total = 750.000
        $this->assertEquals(800000, $invoice->subtotal);
        $this->assertEquals(50000, $invoice->discount_amount);
        $this->assertEquals(750000, $invoice->total_amount);
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals(750000, $invoice->remaining_balance);
        $this->assertEquals(Invoice::STATUS_ISSUED, $invoice->status);
        $this->assertCount(2, $invoice->items);
    }

    /**
     * Test 3: Bulk recurring invoice generation respects active discounts and prevents duplicates.
     */
    public function test_bulk_recurring_invoices_generation_and_duplicate_prevention(): void
    {
        $feeCategory = FeeCategory::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'SPP Bulanan',
            'amount' => 500000,
        ]);

        // Student has 20% active scholarship
        StudentDiscount::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'name' => 'Beasiswa Prestasi 20%',
            'type' => StudentDiscount::TYPE_PERCENTAGE,
            'value' => 20,
            'is_active' => true,
        ]);

        // First bulk generation
        $result = $this->financeService->bulkGenerateRecurringInvoices(
            school: $this->school,
            feeCategory: $feeCategory,
            academicYear: $this->academicYear,
            billingPeriodKey: '2026-11',
            dueDate: '2026-11-10',
            gradeLevelId: $this->gradeLevel->id,
            classroomId: $this->classroom->id,
            creator: $this->admin
        );

        $this->assertEquals(1, $result['total_candidates']);
        $this->assertEquals(1, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_count']);

        $invoice = Invoice::where('student_id', $this->student->id)
            ->where('billing_period_key', '2026-11')
            ->first();

        $this->assertNotNull($invoice);
        // Base: 500.000, 20% discount: 100.000, Total: 400.000
        $this->assertEquals(500000, $invoice->subtotal);
        $this->assertEquals(100000, $invoice->discount_amount);
        $this->assertEquals(400000, $invoice->total_amount);

        // Second bulk generation with same period should skip without duplication
        $secondResult = $this->financeService->bulkGenerateRecurringInvoices(
            school: $this->school,
            feeCategory: $feeCategory,
            academicYear: $this->academicYear,
            billingPeriodKey: '2026-11',
            dueDate: '2026-11-10',
            gradeLevelId: $this->gradeLevel->id,
            classroomId: $this->classroom->id,
            creator: $this->admin
        );

        $this->assertEquals(0, $secondResult['generated_count']);
        $this->assertEquals(1, $secondResult['skipped_count']);
        $this->assertEquals(1, Invoice::where('student_id', $this->student->id)->where('billing_period_key', '2026-11')->count());
    }

    /**
     * Test 4: Recording manual payment updates invoice balance and issues official receipt.
     */
    public function test_record_manual_payment_and_receipt_issuance(): void
    {
        $invoice = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'SPP',
            'quantity' => 1,
            'unit_price' => 500000,
            'discount_amount' => 0,
            'subtotal' => 500000,
        ]);

        // Partial payment: Rp 200.000
        $payment1 = $this->financeService->recordManualPayment($invoice, [
            'amount' => 200000,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => Payment::METHOD_CASH,
            'notes' => 'Cicilan 1',
        ], $this->admin);

        $invoice->refresh();
        $this->assertEquals(200000, $invoice->paid_amount);
        $this->assertEquals(300000, $invoice->remaining_balance);
        $this->assertEquals(Invoice::STATUS_PARTIALLY_PAID, $invoice->status);
        $this->assertNotNull($payment1->receipt);
        $this->assertEquals(200000, $payment1->receipt->amount);

        // Final payment: Rp 300.000 -> Status Lunas
        $payment2 = $this->financeService->recordManualPayment($invoice, [
            'amount' => 300000,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'reference_number' => 'TRX-12345',
            'notes' => 'Pelunasan',
        ], $this->admin);

        $invoice->refresh();
        $this->assertEquals(500000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->remaining_balance);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertTrue($invoice->isPaid());
        $this->assertCount(2, $invoice->receipts);
    }

    /**
     * Test 5: Reversal of payment restores invoice balance and logs audit trail.
     */
    public function test_payment_reversal_restores_invoice_balance(): void
    {
        $invoice = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'SPP',
            'quantity' => 1,
            'unit_price' => 500000,
            'subtotal' => 500000,
        ]);

        $payment = $this->financeService->recordManualPayment($invoice, [
            'amount' => 500000,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => Payment::METHOD_CASH,
        ], $this->admin);

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);

        // Reverse payment
        $reversal = $this->financeService->reversePayment($payment, $this->admin, 'Salah input nominal kasir');

        $this->assertInstanceOf(PaymentReversal::class, $reversal);
        $this->assertEquals(500000, $reversal->amount);

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals(Payment::STATUS_REVERSED, $payment->status);
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals(500000, $invoice->remaining_balance);
        $this->assertEquals(Invoice::STATUS_ISSUED, $invoice->status);
    }

    /**
     * Test 6: Parent portal can view linked child invoice and submit bank transfer proof.
     */
    public function test_parent_can_view_child_finance_and_submit_proof(): void
    {
        $invoice = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
        ]);

        // Parent accesses finance overview
        $response = $this->actingAs($this->parent)->get(route('parent.finance.index'));
        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);

        // Parent submits payment confirmation
        $proofFile = UploadedFile::fake()->create('bukti_transfer.jpg', 500, 'image/jpeg');

        $submitResponse = $this->actingAs($this->parent)->post(route('parent.finance.submit-pay', $invoice), [
            'amount' => 500000,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-BNI-998811',
            'proof_file' => $proofFile,
            'notes' => 'Transfer dari rekening Ibu Siti',
        ]);

        $submitResponse->assertRedirect(route('parent.finance.index'));

        $payment = Payment::where('invoice_id', $invoice->id)->where('status', Payment::STATUS_PENDING_VERIFICATION)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(500000, $payment->amount);
        $this->assertNotNull($payment->proof_path);

        // Admin verifies the pending submission
        $verifyResponse = $this->actingAs($this->admin)->post(route('finance.payments.verify', $payment));
        $verifyResponse->assertRedirect();

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertEquals(500000, $invoice->paid_amount);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($payment->receipt);
    }

    /**
     * Test 7: IDOR security check - Parent cannot access unrelated student invoice.
     */
    public function test_parent_cannot_access_unrelated_student_invoice(): void
    {
        $unrelatedInvoice = Invoice::factory()->create([
            'school_id' => $this->otherSchool->id,
            'student_id' => $this->otherStudent->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
        ]);

        // Attempt view
        $response = $this->actingAs($this->parent)->get(route('parent.finance.show', $unrelatedInvoice));
        $response->assertStatus(403);

        // Attempt pay
        $payResponse = $this->actingAs($this->parent)->get(route('parent.finance.pay', $unrelatedInvoice));
        $payResponse->assertStatus(403);
    }

    /**
     * Test 8: Tenant isolation - School admin cannot view or edit invoices from another school.
     */
    public function test_cross_tenant_invoice_access_denied(): void
    {
        $otherInvoice = Invoice::factory()->create([
            'school_id' => $this->otherSchool->id,
            'student_id' => $this->otherStudent->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('finance.invoices.show', $otherInvoice));
        $response->assertStatus(403);
    }

    /**
     * Test 9: PDF invoice and receipt downloads.
     */
    public function test_authorized_user_can_download_pdf_invoice_and_receipt(): void
    {
        $invoice = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'status' => Invoice::STATUS_PAID,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'SPP',
            'quantity' => 1,
            'unit_price' => 500000,
            'subtotal' => 500000,
        ]);

        $payment = Payment::factory()->create([
            'school_id' => $this->school->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 500000,
            'status' => Payment::STATUS_VERIFIED,
        ]);

        $receipt = Receipt::create([
            'school_id' => $this->school->id,
            'receipt_number' => 'KW-001-9988',
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 500000,
            'issued_at' => now(),
            'issued_by' => $this->admin->id,
        ]);

        // Student can download own invoice
        $invResponse = $this->actingAs($this->student)->get(route('finance.pdf.invoice', ['invoice' => $invoice, 'mode' => 'download']));
        $invResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $invResponse->headers->get('content-type'));

        // Parent can download linked child receipt
        $rcpResponse = $this->actingAs($this->parent)->get(route('finance.pdf.receipt', ['receipt' => $receipt, 'mode' => 'download']));
        $rcpResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $rcpResponse->headers->get('content-type'));
    }

    /**
     * Test 10: Financial report accurately distinguishes invoiced, collected, pending, and reversed amounts.
     */
    public function test_financial_reports_calculation_and_csv_export(): void
    {
        $feeCategory = FeeCategory::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'SPP Bulanan',
        ]);

        $invoice = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'fee_category_id' => $feeCategory->id,
            'issue_date' => Carbon::today()->toDateString(),
            'total_amount' => 1000000,
            'paid_amount' => 600000,
            'status' => Invoice::STATUS_PARTIALLY_PAID,
        ]);

        // Verified payment
        Payment::factory()->create([
            'school_id' => $this->school->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 600000,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'status' => Payment::STATUS_VERIFIED,
        ]);

        // Pending submission (not counted in collections)
        Payment::factory()->create([
            'school_id' => $this->school->id,
            'invoice_id' => $invoice->id,
            'student_id' => $this->student->id,
            'amount' => 400000,
            'payment_date' => Carbon::today()->toDateString(),
            'status' => Payment::STATUS_PENDING_VERIFICATION,
        ]);

        $reportResponse = $this->actingAs($this->admin)->get(route('finance.reports.index', [
            'start_date' => Carbon::today()->startOfMonth()->toDateString(),
            'end_date' => Carbon::today()->endOfMonth()->toDateString(),
        ]));

        $reportResponse->assertStatus(200);
        $reportResponse->assertSee('1.000.000'); // Invoiced
        $reportResponse->assertSee('600.000'); // Collected

        // CSV Export
        $csvResponse = $this->actingAs($this->admin)->get(route('finance.reports.export-csv', [
            'start_date' => Carbon::today()->startOfMonth()->toDateString(),
            'end_date' => Carbon::today()->endOfMonth()->toDateString(),
        ]));

        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('content-type'));
    }
}
