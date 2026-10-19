<?php

namespace App\Services\AI;

use App\Enums\AiFeatureType;
use App\Exceptions\AiBudgetExceededException;
use App\Exceptions\AiFeatureDisabledException;
use App\Models\AiUsageLedger;
use App\Models\School;
use App\Models\SchoolAiQuota;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiQuotaManager
{
    /**
     * Get or initialize quota record for a school.
     */
    public function getOrCreateQuota(int $schoolId): SchoolAiQuota
    {
        return SchoolAiQuota::firstOrCreate(
            ['school_id' => $schoolId],
            [
                'monthly_token_limit' => 500000,
                'monthly_budget_idr' => 100000.00,
                'current_month_tokens_used' => 0,
                'current_month_cost_idr' => 0.00,
                'quota_reset_at' => now()->addMonth()->startOfMonth(),
                'is_ai_enabled' => false, // Default OFF per tenant requirement
                'rate_limit_per_minute' => 10,
            ]
        );
    }

    /**
     * Ensure school has feature enabled and sufficient token budget.
     *
     * @throws AiFeatureDisabledException
     * @throws AiBudgetExceededException
     * @throws RuntimeException
     */
    public function authorizeUsage(User $user, int $estimatedTokens = 1500): SchoolAiQuota
    {
        $schoolId = $user->school_id;
        if (! $schoolId) {
            throw new RuntimeException('Pengguna tidak terafiliasi dengan sekolah mana pun.');
        }

        $quota = $this->getOrCreateQuota($schoolId);

        // 1. Check feature toggle
        if (! $quota->is_ai_enabled) {
            throw new AiFeatureDisabledException;
        }

        // 2. Check monthly quota
        if (! $quota->hasAvailableTokens($estimatedTokens)) {
            throw new AiBudgetExceededException;
        }

        // 3. User rate limiting per minute
        $cacheKey = "ai_rate_limit:user:{$user->id}";
        $hits = (int) Cache::get($cacheKey, 0);

        if ($hits >= $quota->rate_limit_per_minute) {
            throw new RuntimeException("Batas laju permintaan tercapai ({$quota->rate_limit_per_minute} req/menit). Mohon tunggu beberapa detik sebelum mengajukan draf baru.");
        }

        Cache::put($cacheKey, $hits + 1, now()->addMinute());

        return $quota;
    }

    /**
     * Assert that a school or user can generate AI content.
     *
     * @throws AiFeatureDisabledException
     * @throws AiBudgetExceededException
     */
    public function assertCanGenerate(int|School|User $target, int $estimatedTokens = 1500): void
    {
        $schoolId = match (true) {
            $target instanceof User => $target->school_id,
            $target instanceof School => $target->id,
            default => (int) $target,
        };

        $quota = $this->getOrCreateQuota($schoolId);

        if (! $quota->is_ai_enabled) {
            throw new AiFeatureDisabledException;
        }

        if (! $quota->hasAvailableTokens($estimatedTokens)) {
            throw new AiBudgetExceededException;
        }
    }

    /**
     * Record usage after successful LLM response.
     */
    public function recordUsage(
        User $user,
        AiFeatureType $featureType,
        string $provider,
        string $modelName,
        int $promptTokens,
        int $completionTokens,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AiUsageLedger {
        $totalTokens = $promptTokens + $completionTokens;
        $costIdr = ($totalTokens / 1000) * $featureType->estimatedCostPer1kTokensIdr();

        return DB::transaction(function () use (
            $user,
            $featureType,
            $provider,
            $modelName,
            $promptTokens,
            $completionTokens,
            $totalTokens,
            $costIdr,
            $ipAddress,
            $userAgent
        ) {
            $quota = $this->getOrCreateQuota($user->school_id);
            $quota->recordUsage($totalTokens, $costIdr);

            return AiUsageLedger::create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'feature_type' => $featureType->value,
                'provider' => $provider,
                'model_name' => $modelName,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'estimated_cost_idr' => $costIdr,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            ]);
        });
    }

    /**
     * Toggle AI feature for a school.
     */
    public function toggleSchoolFeature(int $schoolId, bool $enabled): SchoolAiQuota
    {
        $quota = $this->getOrCreateQuota($schoolId);
        $quota->update(['is_ai_enabled' => $enabled]);

        return $quota;
    }

    /**
     * Update limits for a school.
     */
    public function updateLimits(int $schoolId, int $tokenLimit, float $budgetIdr, int $rateLimit = 10): SchoolAiQuota
    {
        $quota = $this->getOrCreateQuota($schoolId);
        $quota->update([
            'monthly_token_limit' => $tokenLimit,
            'monthly_budget_idr' => $budgetIdr,
            'rate_limit_per_minute' => $rateLimit,
        ]);

        return $quota;
    }
}
