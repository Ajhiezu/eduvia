<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedgerEntry;
use App\Models\ParentStudentRelation;
use App\Models\PaymentGatewayTransaction;
use App\Services\Payment\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Parent / Student checkout for one or more unpaid invoices.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'integer|exists:invoices,id',
            'payment_method' => 'required|string|in:bca_va,bni_va,bri_va,mandiri_bill,permata_va,qris',
        ]);

        $studentId = (int) $validated['student_id'];

        // Parent-Student authorization check
        $roleStr = is_string($user->role) ? $user->role : ($user->role?->value ?? (string) $user->role);
        if ($roleStr === 'parent') {
            $isChild = ParentStudentRelation::where('parent_id', $user->id)
                ->where('student_id', $studentId)
                ->where('status', ParentStudentRelation::STATUS_ACTIVE)
                ->exists();
            if (! $isChild) {
                return $this->apiError('Anda tidak memiliki otorisasi untuk membayar tagihan siswa ini.', 403);
            }
        } elseif ($roleStr === 'student' && $user->id !== $studentId) {
            return $this->apiError('Siswa hanya dapat membayar tagihannya sendiri.', 403);
        }

        try {
            $transaction = $this->paymentService->checkout(
                payer: $user,
                studentId: $studentId,
                invoiceIds: $validated['invoice_ids'],
                paymentMethod: $validated['payment_method']
            );

            return $this->apiSuccess(
                data: [
                    'order_id' => $transaction->order_id,
                    'payment_method' => $transaction->payment_method,
                    'method_label' => $transaction->method_label,
                    'gross_amount' => $transaction->gross_amount,
                    'formatted_amount' => 'Rp '.number_format($transaction->gross_amount, 0, ',', '.'),
                    'transaction_status' => $transaction->transaction_status,
                    'va_number' => $transaction->va_number,
                    'bill_key' => $transaction->bill_key,
                    'biller_code' => $transaction->biller_code,
                    'qr_code_url' => $transaction->qr_code_url,
                    'expiry_time' => $transaction->expiry_time->toIso8601String(),
                    'expiry_formatted' => $transaction->expiry_time->translatedFormat('d M Y, H:i'),
                    'invoices' => $transaction->allocations->map(fn ($alloc) => [
                        'invoice_id' => $alloc->invoice_id,
                        'invoice_number' => $alloc->invoice?->invoice_number,
                        'title' => $alloc->invoice?->title,
                        'amount_applied' => $alloc->amount_applied,
                    ]),
                ],
                message: 'Instruksi pembayaran berhasil dibuat.'
            );
        } catch (\DomainException $e) {
            return $this->apiError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Checkout failure', ['error' => $e->getMessage()]);

            return $this->apiError('Terjadi kendala saat memproses checkout: '.$e->getMessage(), 500);
        }
    }

    /**
     * Check transaction status directly by orderId.
     */
    public function checkStatus(Request $request, string $orderId): JsonResponse
    {
        $user = $request->user();

        /** @var PaymentGatewayTransaction|null $tx */
        $tx = PaymentGatewayTransaction::where('order_id', $orderId)
            ->with(['allocations.invoice', 'student', 'payer'])
            ->first();

        if (! $tx) {
            return $this->apiError('Transaksi pembayaran tidak ditemukan.', 404);
        }

        // Tenant and authorization isolation
        if ($user->school_id && $user->school_id !== $tx->school_id) {
            return $this->apiError('Akses ditolak (Beda Sekolah).', 403);
        }

        // Auto-sync with Midtrans API if status is still pending
        if ($tx->transaction_status === PaymentGatewayTransaction::STATUS_PENDING) {
            $this->paymentService->syncTransaction($tx);
            $tx->refresh();
        }

        return $this->apiSuccess(
            data: [
                'order_id' => $tx->order_id,
                'status' => $tx->transaction_status,
                'is_settled' => $tx->isSettled(),
                'is_expired' => $tx->isExpired(),
                'gross_amount' => $tx->gross_amount,
                'va_number' => $tx->va_number,
                'qr_code_url' => $tx->qr_code_url,
                'expiry_time' => $tx->expiry_time->toIso8601String(),
                'settlement_time' => $tx->settlement_time?->toIso8601String(),
            ],
            message: 'Status transaksi berhasil diperoleh.'
        );
    }

    /**
     * Public Midtrans Webhook Endpoint (Unauthenticated, SHA512 signature verified).
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            $result = $this->paymentService->handleWebhook($payload);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook berhasil diproses.',
                'data' => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan di database sekolah.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Webhook handler critical error', ['error' => $e->getMessage(), 'payload' => $payload]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error saat memproses notifikasi pembayaran.',
            ], 500);
        }
    }

    /**
     * Run manual reconciliation on demand (School Admin / Treasurer / Super Admin).
     */
    public function reconcile(Request $request): JsonResponse
    {
        $user = $request->user();
        $roleStr = is_string($user->role) ? $user->role : ($user->role?->value ?? (string) $user->role);

        if (! in_array($roleStr, ['super_admin', 'superadmin', 'school_admin', 'staff'])) {
            return $this->apiError('Hanya bendahara atau admin yang dapat menjalankan rekonsiliasi.', 403);
        }

        $result = $this->paymentService->reconcile();

        return $this->apiSuccess(
            data: $result,
            message: 'Rekonsiliasi transaksi gateway berhasil dieksekusi.'
        );
    }

    /**
     * Fetch immutable financial ledger entries (Auditor / Treasurer / School Admin).
     */
    public function ledger(Request $request): JsonResponse
    {
        $user = $request->user();
        $roleStr = is_string($user->role) ? $user->role : ($user->role?->value ?? (string) $user->role);

        if (! in_array($roleStr, ['super_admin', 'superadmin', 'school_admin', 'staff'])) {
            return $this->apiError('Hanya bendahara atau admin sekolah yang dapat mengakses jurnal buku besar.', 403);
        }

        $query = FinancialLedgerEntry::query()
            ->when($user->school_id, fn ($q) => $q->where('school_id', $user->school_id))
            ->orderBy('posted_at', 'desc');

        if ($request->has('account_code')) {
            $query->where('account_code', $request->string('account_code'));
        }

        if ($request->has('entry_type')) {
            $query->where('entry_type', $request->string('entry_type'));
        }

        $entries = $query->paginate($request->integer('per_page', 20));

        return $this->apiSuccess(
            data: [
                'total_debit' => (int) FinancialLedgerEntry::when($user->school_id, fn ($q) => $q->where('school_id', $user->school_id))->where('entry_type', 'debit')->sum('amount'),
                'total_credit' => (int) FinancialLedgerEntry::when($user->school_id, fn ($q) => $q->where('school_id', $user->school_id))->where('entry_type', 'credit')->sum('amount'),
                'entries' => $entries->items(),
                'pagination' => [
                    'current_page' => $entries->currentPage(),
                    'last_page' => $entries->lastPage(),
                    'total' => $entries->total(),
                ],
            ],
            message: 'Data jurnal buku besar keuangan berhasil dimuat.'
        );
    }
}
