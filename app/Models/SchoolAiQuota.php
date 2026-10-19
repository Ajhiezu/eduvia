<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'monthly_token_limit',
    'monthly_budget_idr',
    'current_month_tokens_used',
    'current_month_cost_idr',
    'quota_reset_at',
    'is_ai_enabled',
    'rate_limit_per_minute',
])]
class SchoolAiQuota extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'monthly_token_limit' => 'integer',
            'monthly_budget_idr' => 'decimal:2',
            'current_month_tokens_used' => 'integer',
            'current_month_cost_idr' => 'decimal:2',
            'quota_reset_at' => 'date',
            'is_ai_enabled' => 'boolean',
            'rate_limit_per_minute' => 'integer',
        ];
    }

    /**
     * School this quota belongs to.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if quota has room for requested tokens.
     */
    public function hasAvailableTokens(int $estimatedTokens = 1000): bool
    {
        if (! $this->is_ai_enabled) {
            return false;
        }

        $this->checkAndResetMonthlyQuota();

        return ($this->current_month_tokens_used + $estimatedTokens) <= $this->monthly_token_limit;
    }

    /**
     * Record consumed tokens and cost.
     */
    public function recordUsage(int $tokens, float $costIdr): void
    {
        $this->checkAndResetMonthlyQuota();

        $this->increment('current_month_tokens_used', $tokens);
        $this->increment('current_month_cost_idr', $costIdr);
    }

    /**
     * Reset quota if month rolled over.
     */
    public function checkAndResetMonthlyQuota(): void
    {
        $now = now();
        if (! $this->quota_reset_at || $now->greaterThanOrEqualTo($this->quota_reset_at)) {
            $this->update([
                'current_month_tokens_used' => 0,
                'current_month_cost_idr' => 0.00,
                'quota_reset_at' => $now->copy()->addMonth()->startOfMonth(),
            ]);
        }
    }

    /**
     * Get percentage of monthly quota consumed.
     */
    public function usagePercentage(): float
    {
        if ($this->monthly_token_limit <= 0) {
            return 100.0;
        }

        return round(($this->current_month_tokens_used / $this->monthly_token_limit) * 100, 1);
    }
}
