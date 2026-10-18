<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'receipt_number',
    'payment_id',
    'invoice_id',
    'student_id',
    'amount',
    'issued_at',
    'issued_by',
    'pdf_path',
])]
class Receipt extends Model
{
    use BelongsToSchool, HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'issued_at' => 'datetime',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }
}
