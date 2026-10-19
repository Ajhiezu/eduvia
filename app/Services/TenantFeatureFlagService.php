<?php

namespace App\Services;

use App\Models\SchoolAiQuota;
use App\Models\SystemSetting;

class TenantFeatureFlagService
{
    public const AI_ASSISTANT = 'feature_ai_assistant';

    public const CENTRALIZED_HR = 'feature_centralized_hr';

    public const TEACHING_JOURNAL = 'feature_teaching_journal';

    public const VOCATIONAL_SMK = 'feature_vocational_smk';

    public const PRIMARY_SD = 'feature_primary_sd';

    public const DAPODIK_SYNC = 'feature_dapodik_sync';

    public const ALUMNI_EVENT = 'feature_alumni_event';

    /**
     * Check if a specific feature is enabled for a school tenant.
     * DEFAULT: ALWAYS FALSE (Rule 7: all new Phase 4 features must be wrapped in flags, default OFF).
     */
    public static function isEnabled(string $flag, ?int $schoolId = null): bool
    {
        if ($schoolId === null && auth()->check()) {
            $schoolId = auth()->user()->school_id;
        }

        if (! $schoolId) {
            return false;
        }

        // Dedicated AI quota table flag check
        if ($flag === self::AI_ASSISTANT) {
            $quota = SchoolAiQuota::where('school_id', $schoolId)->first();

            return $quota?->is_ai_enabled ?? false;
        }

        // Generic per-tenant system setting
        return (bool) SystemSetting::get($flag, default: false, schoolId: $schoolId);
    }

    /**
     * Enable feature for a school tenant.
     */
    public static function enable(string $flag, int $schoolId): void
    {
        if ($flag === self::AI_ASSISTANT) {
            SchoolAiQuota::updateOrCreate(
                ['school_id' => $schoolId],
                ['is_ai_enabled' => true]
            );
        }

        SystemSetting::set($flag, '1', 'boolean', 'feature_flags', $schoolId);
    }

    /**
     * Disable feature for a school tenant.
     */
    public static function disable(string $flag, int $schoolId): void
    {
        if ($flag === self::AI_ASSISTANT) {
            SchoolAiQuota::where('school_id', $schoolId)->update(['is_ai_enabled' => false]);
        }

        SystemSetting::set($flag, '0', 'boolean', 'feature_flags', $schoolId);
    }
}
