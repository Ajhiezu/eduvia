<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Models\ParentStudentRelation;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\Receipt;
use App\Models\School;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomatedPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $parent;

    protected User $student;

    protected AcademicYear $academicYear;

    protected Invoice $invoice1;

    protected Invoice $invoice2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create([
            'name' => 'SMP Eduvia Bandung',
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
            'name' => 'Bintang Ramadhan',
        ]);

        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'parent',
            'name' => 'Pak Joko Widodo',
        ]);

        // Link parent and student
        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => ParentStudentRelation::STATUS_ACTIVE,
        ]);

        // Create invoices
        $this->invoice1 = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'title' => 'SPP Bulan Oktober 2026',
            'subtotal' => 500000,
            'discount_amount' => 0,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
            'created_by' => $this->student->id,
        ]);

        $this->invoice2 = Invoice::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'title' => 'Uang Praktikum Komputer',
            'subtotal' => 250000,
            'discount_amount' => 0,
            'total_amount' => 250000,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
            'created_by' => $this->student->id,
        ]);
    }

    public function test_parent_can_checkout_single_invoice_and_receives_va_or_qris(): void
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/v1/payments/checkout', [
            'student_id' => $this->student->id,
            'invoice_ids' => [$this->invoice1->id],
            'payment_method' => 'bca_va',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_method', 'bca_va')
            ->assertJsonPath('data.gross_amount', 500000)
            ->assertJsonPath('data.transaction_status', 'pending');

        $this->assertNotNull($response->json('data.va_number'));
        $this->assertNotNull($response->json('data.order_id'));

        $this->assertDatabaseHas('payment_gateway_transactions', [
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'payment_method' => 'bca_va',
            'gross_amount' => 500000,
            'transaction_status' => 'pending',
        ]);

        $this->assertDatabaseHas('payment_allocations', [
            'invoice_id' => $this->invoice1->id,
            'amount_applied' => 500000,
            'allocation_order' => 1,
        ]);
    }

    public function test_parent_can_checkout_multiple_invoices_with_correct_allocation_breakdown(): void
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/v1/payments/checkout', [
            'student_id' => $this->student->id,
            'invoice_ids' => [$this->invoice1->id, $this->invoice2->id],
            'payment_method' => 'qris',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.gross_amount', 750000)
            ->assertJsonPath('data.payment_method', 'qris');

        $this->assertNotNull($response->json('data.qr_code_url'));

        $this->assertDatabaseCount('payment_allocations', 2);
    }

    public function test_idempotent_checkout_returns_existing_active_transaction(): void
    {
        $resp1 = $this->actingAs($this->parent, 'sanctum')->postJson('/api/v1/payments/checkout', [
            'student_id' => $this->student->id,
            'invoice_ids' => [$this->invoice1->id],
            'payment_method' => 'bni_va',
        ]);
        $resp1->assertOk();
        $orderId1 = $resp1->json('data.order_id');

        // Immediate retry of identical checkout
        $resp2 = $this->actingAs($this->parent, 'sanctum')->postJson('/api/v1/payments/checkout', [
            'student_id' => $this->student->id,
            'invoice_ids' => [$this->invoice1->id],
            'payment_method' => 'bni_va',
        ]);
        $resp2->assertOk();
        $orderId2 = $resp2->json('data.order_id');

        $this->assertEquals($orderId1, $orderId2);
        $this->assertDatabaseCount('payment_gateway_transactions', 1);
    }

    public function test_unauthenticated_webhook_with_invalid_signature_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/webhooks/payment-gateway', [
            'order_id' => 'PAY-TEST-FAKE',
            'status_code' => '200',
            'gross_amount' => '500000',
            'signature_key' => 'invalid_sha512_hash',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(400);
    }

    public function test_valid_webhook_settlement_creates_payment_receipt_and_double_entry_ledger(): void
    {
        // 1. Create a checkout transaction first
        $service = app(PaymentService::class);
        $tx = $service->checkout(
            payer: $this->parent,
            studentId: $this->student->id,
            invoiceIds: [$this->invoice1->id, $this->invoice2->id],
            paymentMethod: 'bca_va'
        );

        $this->assertEquals(750000, $tx->gross_amount);

        // 2. Generate valid SHA512 signature using driver's server key
        $serverKey = 'SB-Mid-server-eduvia-mock-key';
        $signature = hash('sha512', $tx->order_id.'200'.'750000'.$serverKey);

        // 3. Post webhook notification
        $webhookResponse = $this->postJson('/api/v1/webhooks/payment-gateway', [
            'order_id' => $tx->order_id,
            'status_code' => '200',
            'gross_amount' => '750000',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'settlement_time' => now()->toDateTimeString(),
        ]);

        $webhookResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'settlement');

        // 4. Verify transaction settled
        $tx->refresh();
        $this->assertEquals(PaymentGatewayTransaction::STATUS_SETTLEMENT, $tx->transaction_status);
        $this->assertNotNull($tx->settlement_time);

        // 5. Verify invoices marked paid
        $this->invoice1->refresh();
        $this->invoice2->refresh();
        $this->assertEquals(500000, $this->invoice1->paid_amount);
        $this->assertEquals(Invoice::STATUS_PAID, $this->invoice1->status);
        $this->assertEquals(250000, $this->invoice2->paid_amount);
        $this->assertEquals(Invoice::STATUS_PAID, $this->invoice2->status);

        // 6. Verify payments & receipts created
        $this->assertEquals(2, Payment::where('student_id', $this->student->id)->count());
        $this->assertEquals(2, Receipt::where('student_id', $this->student->id)->count());

        // 7. Verify append-only double-entry financial ledger entries
        // 2 invoices settled = 2 debit entries + 2 credit entries = 4 entries total
        $this->assertEquals(4, FinancialLedgerEntry::where('school_id', $this->school->id)->count());

        $debitTotal = FinancialLedgerEntry::where('school_id', $this->school->id)->where('entry_type', 'debit')->sum('amount');
        $creditTotal = FinancialLedgerEntry::where('school_id', $this->school->id)->where('entry_type', 'credit')->sum('amount');
        $this->assertEquals(750000, $debitTotal);
        $this->assertEquals(750000, $creditTotal);
    }

    public function test_webhook_is_idempotent_when_called_multiple_times_with_same_settlement(): void
    {
        $service = app(PaymentService::class);
        $tx = $service->checkout(
            payer: $this->parent,
            studentId: $this->student->id,
            invoiceIds: [$this->invoice1->id],
            paymentMethod: 'bca_va'
        );

        $serverKey = 'SB-Mid-server-eduvia-mock-key';
        $signature = hash('sha512', $tx->order_id.'200'.'500000'.$serverKey);

        $payload = [
            'order_id' => $tx->order_id,
            'status_code' => '200',
            'gross_amount' => '500000',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        // Call webhook first time
        $resp1 = $this->postJson('/api/v1/webhooks/payment-gateway', $payload);
        $resp1->assertOk()->assertJsonPath('data.status', 'settlement');

        $this->assertEquals(1, Payment::count());
        $this->assertEquals(2, FinancialLedgerEntry::count()); // 1 debit + 1 credit

        // Call webhook second time (duplicate delivery)
        $resp2 = $this->postJson('/api/v1/webhooks/payment-gateway', $payload);
        $resp2->assertOk()->assertJsonPath('data.status', 'already_settled');

        // MUST NOT create duplicate payments or ledger entries
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(2, FinancialLedgerEntry::count());
    }

    public function test_ledger_entries_are_immutable_and_cannot_be_updated_or_deleted(): void
    {
        $entry = FinancialLedgerEntry::create([
            'school_id' => $this->school->id,
            'entry_number' => 'JRN-TEST-001',
            'entry_type' => FinancialLedgerEntry::TYPE_DEBIT,
            'account_code' => '1101',
            'account_name' => 'Kas & Bank',
            'amount' => 500000,
            'reference_type' => 'Payment',
            'reference_id' => 1,
            'description' => 'Test immutable ledger',
            'posted_at' => now(),
        ]);

        $this->expectException(\LogicException::class);
        $entry->update(['amount' => 600000]);
    }
}
