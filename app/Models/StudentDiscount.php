<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'student_id',
    'fee_category_id',
    'academic_year_id',
    'name',
    'type',
    'value',
    'reason',
    'authorized_by',
    'is_active',
])]
class StudentDiscount extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public const TYPE_FIXED = 'fixed';

    public const TYPE_PERCENTAGE = 'percentage';

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'is_active' => 'boolean',
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

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * Calculate discount amount for a given base price.
     */
    public function calculateDiscount(int $baseAmount): int
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = (int) round(($baseAmount * $this->value) / 100);
        } else {
            $discount = (int) $this->value;
        }

        // Never exceed base amount
        return min($discount, $baseAmount);
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return "{$this->value}%";
        }

        return 'Rp '.number_format($this->value, 0, ',', '.');
    }
}
