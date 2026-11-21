<?php

namespace App\Services\AI;

use App\Enums\UserRole;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AiAgentSettings
{
    public static function canUse(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER, UserRole::STAFF);
    }

    public static function canManage(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN);
    }

    public function apiKey(int $schoolId): ?string
    {
        $encryptedKey = SystemSetting::get('ai_agent_api_key', null, $schoolId);

        if ($encryptedKey) {
            try {
                return Crypt::decryptString($encryptedKey);
            } catch (DecryptException) {
                return null;
            }
        }

        return config('services.gemini.api_key') ?: null;
    }

    public function enabled(int $schoolId): bool
    {
        $enabled = SystemSetting::get('ai_agent_enabled', null, $schoolId);

        return $enabled === null ? filled($this->apiKey($schoolId)) : (bool) $enabled;
    }

    /** @return array{enabled: bool, configured: bool, model: string} */
    public function status(int $schoolId): array
    {
        return [
            'enabled' => $this->enabled($schoolId),
            'configured' => filled($this->apiKey($schoolId)),
            'model' => config('services.gemini.model'),
        ];
    }

    public function update(int $schoolId, ?string $apiKey, bool $enabled): void
    {
        if (filled($apiKey)) {
            SystemSetting::set('ai_agent_api_key', Crypt::encryptString($apiKey), 'string', 'ai_agent', $schoolId);
        }

        SystemSetting::set('ai_agent_enabled', $enabled, 'boolean', 'ai_agent', $schoolId);
    }
}
