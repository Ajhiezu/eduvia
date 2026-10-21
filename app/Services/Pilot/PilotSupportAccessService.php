<?php

namespace App\Services\Pilot;

use App\Models\AuditLog;
use App\Models\Pilot\PilotSupportAccessLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use RuntimeException;

class PilotSupportAccessService
{
    /**
     * Grant time-bound, PIC-authorized diagnostic access (Default max 60 minutes).
     */
    public function grantDiagnosticAccess(
        User $operator,
        User $targetUser,
        string $authorizedByPic,
        string $reason,
        int $durationMinutes = 60
    ): PilotSupportAccessLog {
        if ($operator->id === $targetUser->id) {
            throw new RuntimeException('Operator tidak dapat membuat log akses diagnostik untuk dirinya sendiri.');
        }

        // Revoke any previous active session for this operator and target
        PilotSupportAccessLog::where('operator_id', $operator->id)
            ->where('target_user_id', $targetUser->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $log = PilotSupportAccessLog::create([
            'school_id' => $targetUser->school_id,
            'operator_id' => $operator->id,
            'target_user_id' => $targetUser->id,
            'authorized_by_pic' => trim($authorizedByPic),
            'reason' => trim($reason),
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'access_type' => 'scrubbed_diagnostics',
            'expires_at' => now()->addMinutes($durationMinutes),
        ]);

        AuditLog::record(
            action: 'support_access_granted',
            auditable: $log,
            newValues: [
                'target_user_id' => $targetUser->id,
                'authorized_by' => $authorizedByPic,
                'expires_at' => $log->expires_at->toIso8601String(),
            ],
            description: "Akses diagnostik terbatas disetujui oleh PIC {$authorizedByPic} untuk keperluan: {$reason}",
            schoolId: $targetUser->school_id
        );

        return $log;
    }

    /**
     * Fetch sanitized, PII-scrubbed diagnostic view of a user account for troubleshooting.
     *
     * @return array<string, mixed>
     */
    public function getScrubbedDiagnostics(User $operator, User $targetUser): array
    {
        $activeSession = PilotSupportAccessLog::where('operator_id', $operator->id)
            ->where('target_user_id', $targetUser->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $activeSession) {
            throw new RuntimeException('Akses diagnostik ditolak. Tidak ada sesi aktif yang diotorisasi PIC sekolah.');
        }

        // PII Masking: First 2 chars + asterisk
        $maskedEmail = preg_replace('/(?<=..).(?=.*@)/u', '*', $targetUser->email);

        return [
            'diagnostic_session' => [
                'session_id' => $activeSession->id,
                'authorized_by' => $activeSession->authorized_by_pic,
                'expires_in_minutes' => (int) max(0, now()->diffInMinutes($activeSession->expires_at, false)),
            ],
            'account_status' => [
                'id' => $targetUser->id,
                'school_id' => $targetUser->school_id,
                'role' => $targetUser->role instanceof \BackedEnum ? $targetUser->role->value : (string) $targetUser->role,
                'is_active' => (bool) $targetUser->is_active,
                'masked_email' => $maskedEmail,
                'has_two_factor' => (bool) $targetUser->two_factor_confirmed_at,
                'created_at' => $targetUser->created_at?->toIso8601String(),
            ],
            'academic_context' => [
                'enrolled_classrooms_count' => $targetUser->classroomEnrollments()->count(),
                'active_tokens_count' => $targetUser->tokens()->count(),
            ],
        ];
    }

    /**
     * Revoke active diagnostic access immediately.
     */
    public function revokeAccess(PilotSupportAccessLog $log, User $operator): bool
    {
        $log->update(['revoked_at' => now()]);

        AuditLog::record(
            action: 'support_access_revoked',
            auditable: $log,
            newValues: ['revoked_at' => now()->toIso8601String()],
            description: "Akses diagnostik ID {$log->id} dicabut oleh {$operator->name}.",
            schoolId: $log->school_id
        );

        return true;
    }
}
