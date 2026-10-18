<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\FeeCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'school_id',
    'code',
    'name',
    'description',
    'type',
    'billing_period',
    'amount',
    'is_active',
])]
class FeeCategory extends Model
{
    /** @use HasFactory<FeeCategoryFactory> */
    use BelongsToSchool, HasFactory;

    public const TYPE_RECURRING = 'recurring';

    public const TYPE_ONE_TIME = 'one_time';

    public const PERIOD_MONTHLY = 'monthly';

    public const PERIOD_SEMESTER = 'semester';

    public const PERIOD_ANNUAL = 'annual';

    public const PERIOD_ONCE = 'once';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FeeRule::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    public function getEffectiveAmount(?int $academicYearId = null, ?int $gradeLevelId = null, ?int $classroomId = null): int
    {
        if ($academicYearId) {
            $rule = $this->rules()
                ->where('is_active', true)
                ->where('academic_year_id', $academicYearId)
                ->where(function ($q) use ($gradeLevelId, $classroomId) {
                    if ($classroomId) {
                        $q->where('classroom_id', $classroomId);
                    }
                    if ($gradeLevelId) {
                        $q->orWhere('grade_level_id', $gradeLevelId);
                    }
                })
                ->orderByDesc('classroom_id')
                ->orderByDesc('grade_level_id')
                ->first();

            if ($rule) {
                return (int) $rule->amount;
            }
        }

        return (int) $this->amount;
    }
}
