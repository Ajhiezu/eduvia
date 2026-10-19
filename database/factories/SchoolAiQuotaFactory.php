<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolAiQuota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolAiQuota>
 */
class SchoolAiQuotaFactory extends Factory
{
    protected $model = SchoolAiQuota::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'is_ai_enabled' => true,
            'monthly_budget_idr' => 250000.00,
            'current_month_cost_idr' => 0.00,
            'monthly_token_limit' => 500000,
            'current_month_tokens_used' => 0,
            'rate_limit_per_minute' => 30,
            'quota_reset_at' => now()->startOfMonth(),
        ];
    }
}
