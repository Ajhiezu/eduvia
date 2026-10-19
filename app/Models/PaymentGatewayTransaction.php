<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\PaymentGatewayTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'school_id',
    'student_id',
    'payer_id',
    'order_id',
    'payment_method',
    'gross_amount',
    'transaction_status',
    'va_number',
    'bill_key',
    'biller_code',
    'qr_code_url',
    'expiry_time',
    'settlement_time',
    'idempotency_key',
    'raw_payload',
    'raw_response',
])]
class PaymentGatewayTransaction extends Model
{
    /** @use HasFactory<PaymentGatewayTransactionFactory> */
    use BelongsToSchool, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SETTLEMENT = 'settlement';

    public const STATUS_EXPIRE = 'expire';

    public const STATUS_CANCEL = 'cancel';

    public const STATUS_FAILURE = 'failure';

    public const METHOD_BCA_VA = 'bca_va';

    public const METHOD_BNI_VA = 'bni_va';

    public const METHOD_BRI_VA = 'bri_va';

    public const METHOD_MANDIRI_BILL = 'mandiri_bill';

    public const METHOD_PERMATA_VA = 'permata_va';

    public const METHOD_QRIS = 'qris';

    protected function casts(): array
    {
        return [
            'gross_amount' => 'integer',
            'expiry_time' => 'datetime',
            'settlement_time' => 'datetime',
            'raw_payload' => 'array',
            'raw_response' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function isPending(): bool
    {
        return $this->transaction_status === self::STATUS_PENDING;
    }

    public function isSettled(): bool
    {
        return $this->transaction_status === self::STATUS_SETTLEMENT;
    }

    public function isExpired(): bool
    {
        return $this->transaction_status === self::STATUS_EXPIRE || ($this->isPending() && now()->greaterThan($this->expiry_time));
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_BCA_VA => 'BCA Virtual Account',
            self::METHOD_BNI_VA => 'BNI Virtual Account',
            self::METHOD_BRI_VA => 'BRI Virtual Account',
            self::METHOD_MANDIRI_BILL => 'Mandiri Bill Payment',
            self::METHOD_PERMATA_VA => 'Permata Virtual Account',
            self::METHOD_QRIS => 'QRIS (Semua E-Wallet / Mobile Banking)',
            default => strtoupper($this->payment_method),
        };
    }
}
