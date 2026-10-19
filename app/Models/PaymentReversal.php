<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'payment_id',
    'invoice_id',
    'amount',
    'reason',
    'reversed_by',
])]
class PaymentReversal extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }
}
