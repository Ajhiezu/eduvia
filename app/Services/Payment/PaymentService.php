<?php

namespace App\Services\Payment;

use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentGatewayTransaction;
use App\Models\Receipt;
use App\Models\User;
use App\Services\Notification\NotificationPayload;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected PaymentGatewayInterface $gateway,
        protected NotificationService $notificationService
    ) {}

    /**
     * Create automated payment checkout for one or more student invoices.
     *
     * @param  array<int>  $invoiceIds
     */
    public function checkout(User $payer, int $studentId, array $invoiceIds, string $paymentMethod): PaymentGatewayTransaction
    {
        if (empty($invoiceIds)) {
            throw new \InvalidArgumentException('Minimal harus memilih 1 tagihan untuk dibayar.');
        }

        // Fetch invoices belonging to student and valid status
        /** @var Collection<int, Invoice> $invoices */
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->where('student_id', $studentId)
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])
            ->get();

        if ($invoices->count() !== count(array_unique($invoiceIds))) {
            throw new \DomainException('Sebagian atau seluruh tagihan yang dipilih tidak valid atau sudah lunas.');
        }

        $schoolId = $invoices->first()->school_id;

        // Verify all invoices belong to the same school
        foreach ($invoices as $inv) {
            if ($inv->school_id !== $schoolId) {
                throw new \DomainException('Tagihan dari sekolah yang berbeda tidak dapat digabung.');
            }
        }

        // Calculate gross amount and allocation breakdown
        $grossAmount = 0;
        $allocationBreakdown = [];
        $order = 1;

        foreach ($invoices as $inv) {
            $remaining = (int) max(0, $inv->total_amount - $inv->paid_amount);
            if ($remaining > 0) {
                $grossAmount += $remaining;
                $allocationBreakdown[] = [
                    'invoice_id' => $inv->id,
                    'amount' => $remaining,
                    'order' => $order++,
                ];
            }
        }

        if ($grossAmount <= 0) {
            throw new \DomainException('Total tagihan bernilai 0 rupiah.');
        }

        // Check for existing pending transaction within 24 hours to prevent duplicate checkout requests
        $sortedIds = $invoiceIds;
        sort($sortedIds);
        $idempotencyKey = md5(implode(',', $sortedIds).":{$studentId}:{$grossAmount}:{$paymentMethod}:".date('YmdH'));

        $existing = PaymentGatewayTransaction::where('idempotency_key', $idempotencyKey)
            ->where('transaction_status', PaymentGatewayTransaction::STATUS_PENDING)
            ->where('expiry_time', '>', now())
            ->first();

        if ($existing) {
            return $existing;
        }

        $orderId = 'PAY-'.date('YmdHis').'-'.strtoupper(Str::random(5));

        $chargeResult = $this->gateway->charge([
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'payment_method' => $paymentMethod,
            'customer_details' => [
                'name' => $payer->name,
                'email' => $payer->email ?? 'parent@eduvia.sch.id',
            ],
        ]);

        return DB::transaction(function () use (
            $schoolId,
            $studentId,
            $payer,
            $orderId,
            $paymentMethod,
            $grossAmount,
            $chargeResult,
            $idempotencyKey,
            $allocationBreakdown
        ) {
            $tx = PaymentGatewayTransaction::create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'payer_id' => $payer->id,
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'gross_amount' => $grossAmount,
                'transaction_status' => PaymentGatewayTransaction::STATUS_PENDING,
                'va_number' => $chargeResult['va_number'] ?? null,
                'bill_key' => $chargeResult['bill_key'] ?? null,
                'biller_code' => $chargeResult['biller_code'] ?? null,
                'qr_code_url' => $chargeResult['qr_code_url'] ?? null,
                'expiry_time' => Carbon::parse($chargeResult['expiry_time']),
                'idempotency_key' => $idempotencyKey,
                'raw_payload' => $chargeResult['raw_response'] ?? [],
            ]);

            foreach ($allocationBreakdown as $item) {
                PaymentAllocation::create([
                    'payment_gateway_transaction_id' => $tx->id,
                    'invoice_id' => $item['invoice_id'],
                    'amount_applied' => $item['amount'],
                    'allocation_order' => $item['order'],
                ]);
            }

            return $tx;
        });
    }

    /**
     * Process incoming webhook notification idempotently with signature verification.
     *
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, status: string, order_id: string}
     */
    public function handleWebhook(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($signatureKey)) {
            throw new \InvalidArgumentException('Payload webhook Midtrans tidak lengkap.');
        }

        // Verify SHA512 signature
        if (! $this->gateway->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook invalid signature', ['order_id' => $orderId, 'payload' => $payload]);
            throw new \InvalidArgumentException('Tanda tangan webhook (signature key) tidak valid.');
        }

        return DB::transaction(function () use ($orderId, $transactionStatus, $payload) {
            /** @var PaymentGatewayTransaction $tx */
            $tx = PaymentGatewayTransaction::where('order_id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            // IDEMPOTENCY GUARD: If already settled, do not double-process
            if ($tx->transaction_status === PaymentGatewayTransaction::STATUS_SETTLEMENT) {
                return [
                    'success' => true,
                    'status' => 'already_settled',
                    'order_id' => $orderId,
                ];
            }

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $this->applySettlement($tx, $payload);

                return [
                    'success' => true,
                    'status' => 'settlement',
                    'order_id' => $orderId,
                ];
            }

            if (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
                $tx->update([
                    'transaction_status' => PaymentGatewayTransaction::STATUS_EXPIRE,
                    'raw_response' => $payload,
                ]);

                return [
                    'success' => true,
                    'status' => 'expired',
                    'order_id' => $orderId,
                ];
            }

            return [
                'success' => true,
                'status' => $transactionStatus,
                'order_id' => $orderId,
            ];
        });
    }

    /**
     * Apply settlement across invoices, issue payments & receipts, and write append-only double-entry ledger.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function applySettlement(PaymentGatewayTransaction $tx, array $payload): void
    {
        $tx->update([
            'transaction_status' => PaymentGatewayTransaction::STATUS_SETTLEMENT,
            'settlement_time' => now(),
            'raw_response' => $payload,
        ]);

        $allocations = $tx->allocations()->with('invoice')->orderBy('allocation_order')->get();

        foreach ($allocations as $allocation) {
            /** @var Invoice $invoice */
            $invoice = Invoice::where('id', $allocation->invoice_id)->lockForUpdate()->firstOrFail();

            // 1. Record official payment entry
            $paymentNumber = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $payment = Payment::create([
                'school_id' => $tx->school_id,
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'student_id' => $tx->student_id,
                'paid_by' => $tx->payer_id,
                'amount' => $allocation->amount_applied,
                'payment_date' => today(),
                'payment_method' => $tx->payment_method,
                'reference_number' => $tx->order_id,
                'status' => Payment::STATUS_VERIFIED,
                'verified_at' => now(),
                'notes' => "Terverifikasi otomatis via Midtrans Gateway ({$tx->method_label})",
            ]);

            $allocation->update([
                'payment_id' => $payment->id,
            ]);

            // 2. Update invoice paid amount and status
            $newPaid = (int) ($invoice->paid_amount + $allocation->amount_applied);
            $newStatus = ($newPaid >= $invoice->total_amount)
                ? Invoice::STATUS_PAID
                : Invoice::STATUS_PARTIALLY_PAID;

            $invoice->update([
                'paid_amount' => $newPaid,
                'status' => $newStatus,
            ]);

            // 3. Issue official receipt
            $receiptNumber = 'RCP-'.date('Ymd').'-'.strtoupper(Str::random(6));
            Receipt::create([
                'school_id' => $tx->school_id,
                'receipt_number' => $receiptNumber,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'student_id' => $tx->student_id,
                'amount' => $allocation->amount_applied,
                'issued_at' => now(),
            ]);

            // 4. Record Append-Only Double-Entry Journal into Financial Ledger
            // Entry 1: DEBIT Kas & Bank (Midtrans Clearing)
            $entryDebitNum = 'JRN-'.date('Ymd').'-'.strtoupper(Str::random(6));
            FinancialLedgerEntry::create([
                'school_id' => $tx->school_id,
                'entry_number' => $entryDebitNum,
                'entry_type' => FinancialLedgerEntry::TYPE_DEBIT,
                'account_code' => FinancialLedgerEntry::ACC_CASH_BANK,
                'account_name' => 'Kas & Bank Penerimaan (Midtrans Gateway)',
                'amount' => $allocation->amount_applied,
                'reference_type' => 'Payment',
                'reference_id' => $payment->id,
                'description' => "Penerimaan pembayaran tagihan {$invoice->invoice_number} ({$tx->method_label})",
                'posted_at' => now(),
            ]);

            // Entry 2: CREDIT Pendapatan SPP / Biaya Sekolah
            $entryCreditNum = 'JRN-'.date('Ymd').'-'.strtoupper(Str::random(6));
            FinancialLedgerEntry::create([
                'school_id' => $tx->school_id,
                'entry_number' => $entryCreditNum,
                'entry_type' => FinancialLedgerEntry::TYPE_CREDIT,
                'account_code' => FinancialLedgerEntry::ACC_TUITION_REVENUE,
                'account_name' => 'Pendapatan Biaya Pendidikan',
                'amount' => $allocation->amount_applied,
                'reference_type' => 'Payment',
                'reference_id' => $payment->id,
                'description' => "Pengakuan pendapatan atas tagihan {$invoice->invoice_number} ({$invoice->title})",
                'posted_at' => now(),
            ]);

            // 5. Dispatch privacy-compliant notification to parent
            if ($tx->payer) {
                $this->notificationService->send(
                    new NotificationPayload(
                        userId: $tx->payer->id,
                        schoolId: $tx->school_id,
                        eventKey: 'finance.payment_confirmed',
                        title: 'Pembayaran Dikonfirmasi Otomatis',
                        body: sprintf(
                            'Pembayaran %s sebesar Rp %s telah berhasil diterima dan divalidasi sistem. Kuitansi resmi: %s.',
                            $invoice->title,
                            number_format($allocation->amount_applied, 0, ',', '.'),
                            $receiptNumber
                        ),
                        data: [
                            'payment_id' => $payment->id,
                            'receipt_number' => $receiptNumber,
                            'invoice_id' => $invoice->id,
                        ]
                    )
                );
            }
        }
    }

    /**
     * Daily automated reconciliation comparing gateway transactions against gateway records.
     *
     * @return array{scanned: int, settled: int, expired: int, discrepancies: array<int, string>}
     */
    public function reconcile(): array
    {
        $pendingTransactions = PaymentGatewayTransaction::where('transaction_status', PaymentGatewayTransaction::STATUS_PENDING)
            ->where('created_at', '>=', now()->subDays(3))
            ->get();

        $scanned = 0;
        $settled = 0;
        $expired = 0;
        $discrepancies = [];

        foreach ($pendingTransactions as $tx) {
            $scanned++;
            try {
                $statusData = $this->gateway->getStatus($tx->order_id);
                $gwStatus = $statusData['transaction_status'] ?? 'pending';

                if (in_array($gwStatus, ['settlement', 'capture'])) {
                    $this->applySettlement($tx, $statusData);
                    $settled++;
                } elseif (in_array($gwStatus, ['expire', 'cancel', 'deny']) || $tx->isExpired()) {
                    $tx->update(['transaction_status' => PaymentGatewayTransaction::STATUS_EXPIRE]);
                    $expired++;
                }
            } catch (\Throwable $e) {
                $discrepancies[] = "Order {$tx->order_id}: Gagal sinkronisasi gateway ({$e->getMessage()})";
            }
        }

        return [
            'scanned' => $scanned,
            'settled' => $settled,
            'expired' => $expired,
            'discrepancies' => $discrepancies,
        ];
    }
}
