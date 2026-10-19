<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToSchool;
use Carbon\Carbon;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'school_id',
    'invoice_number',
    'student_id',
    'academic_year_id',
    'fee_category_id',
    'billing_period_key',
    'title',
    'issue_date',
    'due_date',
    'subtotal',
    'discount_amount',
    'total_amount',
    'paid_amount',
    'status',
    'notes',
    'created_by',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use Auditable, BelongsToSchool, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', Payment::STATUS_VERIFIED);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || ($this->total_amount > 0 && $this->paid_amount >= $this->total_amount);
    }

    /**
     * Remaining outstanding balance in Rupiah (integer).
     */
    public function getRemainingBalanceAttribute(): int
    {
        return max(0, (int) $this->total_amount - (int) $this->paid_amount);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedPaidAttribute(): string
    {
        return 'Rp '.number_format($this->paid_amount, 0, ',', '.');
    }

    public function getFormattedRemainingAttribute(): string
    {
        return 'Rp '.number_format($this->remaining_balance, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedDiscountAttribute(): string
    {
        return 'Rp '.number_format($this->discount_amount, 0, ',', '.');
    }

    /**
     * Status label helper in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_ISSUED => 'Diterbitkan',
            self::STATUS_PARTIALLY_PAID => 'Sebagian Dibayar',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_OVERDUE => 'Jatuh Tempo',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700 border-gray-200',
            self::STATUS_ISSUED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::STATUS_PARTIALLY_PAID => 'bg-amber-50 text-amber-700 border-amber-200',
            self::STATUS_PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_OVERDUE => 'bg-red-50 text-red-700 border-red-200',
            self::STATUS_CANCELLED => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    /**
     * Recalculate invoice totals from items and verified payments.
     */
    public function recalculate(): void
    {
        if ($this->items()->exists()) {
            $subtotal = 0;
            $discountTotal = 0;

            foreach ($this->items as $item) {
                $subtotal += (int) ($item->quantity * $item->unit_price);
                $discountTotal += (int) $item->discount_amount;
            }

            $this->subtotal = $subtotal;
            $this->discount_amount = min($discountTotal, $subtotal);
            $this->total_amount = max(0, $subtotal - $this->discount_amount);
        }

        // Sum verified payments
        $paid = (int) $this->payments()->where('status', Payment::STATUS_VERIFIED)->sum('amount');
        $this->paid_amount = $paid;

        if ($this->status !== self::STATUS_CANCELLED) {
            if ($this->paid_amount >= $this->total_amount && $this->total_amount > 0) {
                $this->status = self::STATUS_PAID;
            } elseif ($this->paid_amount > 0) {
                $this->status = self::STATUS_PARTIALLY_PAID;
            } elseif ($this->due_date && $this->due_date->isPast() && $this->status !== self::STATUS_DRAFT) {
                $this->status = self::STATUS_OVERDUE;
            } else {
                $this->status = self::STATUS_ISSUED;
            }
        }

        $this->save();
    }

    /**
     * Scope for invoices that are unpaid or partially paid.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ISSUED, self::STATUS_PARTIALLY_PAID, self::STATUS_OVERDUE]);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_OVERDUE)
                ->orWhere(function ($sub) {
                    $sub->whereIn('status', [self::STATUS_ISSUED, self::STATUS_PARTIALLY_PAID])
                        ->where('due_date', '<', Carbon::today());
                });
        });
    }
}
