<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class DataSubjectRightsService
{
    /**
     * Record explicit consent from user or parent on behalf of child.
     */
    public function recordConsent(
        User $user,
        string $type,
        string $version = '1.0',
        bool $isGranted = true,
        ?User $parent = null
    ): UserConsent {
        // If an existing active consent of this type exists, revoke it first (version bump)
        UserConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'is_granted' => false,
            ]);

        $consent = UserConsent::create([
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'consent_type' => $type,
            'version' => $version,
            'is_granted' => $isGranted,
            'parent_user_id' => $parent?->id,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'CLI/System',
            'consented_at' => now(),
        ]);

        AuditLog::record(
            action: 'consent_recorded',
            auditable: $consent,
            newValues: [
                'type' => $type,
                'version' => $version,
                'is_granted' => $isGranted,
                'parent_user_id' => $parent?->id,
            ],
            description: "Persetujuan pemrosesan data '{$type}' berhasil dicatat.",
            schoolId: $user->school_id
        );

        return $consent;
    }

    /**
     * Revoke active consent.
     */
    public function revokeConsent(User $user, string $type): bool
    {
        $updated = UserConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->update([
                'is_granted' => false,
                'revoked_at' => now(),
            ]);

        if ($updated) {
            AuditLog::record(
                action: 'consent_revoked',
                auditable: $user,
                newValues: ['type' => $type, 'revoked_at' => now()->toIso8601String()],
                description: "Persetujuan pemrosesan data '{$type}' ditarik kembali oleh pengguna/wali.",
                schoolId: $user->school_id
            );

            return true;
        }

        return false;
    }

    /**
     * Export all personal data belonging to the user (Right to Portability, UU PDP Pasal 7).
     *
     * @return array<string, mixed>
     */
    public function exportUserData(User $user): array
    {
        AuditLog::record(
            action: 'data_portability_export',
            auditable: $user,
            description: "Permintaan ekspor data pribadi (UU PDP Pasal 7) diproses untuk pengguna ID: {$user->id}.",
            schoolId: $user->school_id
        );

        return [
            'export_metadata' => [
                'platform' => 'Eduvia Education SaaS',
                'exported_at' => now()->toIso8601String(),
                'subject_id' => $user->id,
                'school_id' => $user->school_id,
            ],
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'profile' => $user->studentProfile ? [
                'nis' => $user->studentProfile->nis,
                'nisn' => $user->studentProfile->nisn,
                'gender' => $user->studentProfile->gender,
                'birth_place' => $user->studentProfile->birth_place,
                'birth_date' => $user->studentProfile->birth_date?->toDateString(),
                'address' => $user->studentProfile->address,
                'parent_name' => $user->studentProfile->parent_name,
            ] : null,
            'classrooms' => $user->classrooms()->get()->map(fn ($c) => [
                'classroom_name' => $c->name,
                'academic_year' => $c->academicYear?->name,
            ])->toArray(),
            'consents' => UserConsent::where('user_id', $user->id)->get()->map(fn ($c) => [
                'consent_type' => $c->consent_type,
                'version' => $c->version,
                'is_granted' => (bool) $c->is_granted,
                'consented_at' => $c->consented_at?->toIso8601String(),
                'revoked_at' => $c->revoked_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    /**
     * Anonymize user PII data upon deletion request (Right to Erasure, UU PDP Pasal 8),
     * while preserving financial and academic integrity under legal retention schedule.
     */
    public function anonymizeUserData(User $user, ?User $operator = null): bool
    {
        return DB::transaction(function () use ($user) {
            $anonymousId = 'ANON-'.Str::upper(Str::random(8));

            // Record audit log before anonymization
            AuditLog::record(
                action: 'data_erasure_anonymized',
                auditable: $user,
                oldValues: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                newValues: [
                    'anonymous_id' => $anonymousId,
                    'status' => 'anonymized',
                ],
                description: "Pelaksanaan hak penghapusan data pribadi (UU PDP Pasal 8). Data identitas disamarkan menjadi {$anonymousId}.",
                schoolId: $user->school_id
            );

            // Anonymize User account
            $user->update([
                'name' => "Eks-Peserta Didik ({$anonymousId})",
                'email' => "anonymized_{$anonymousId}@anonymized.local",
                'phone' => null,
                'password' => bcrypt(Str::random(32)),
                'avatar' => null,
                'is_active' => false,
                'remember_token' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]);

            // Anonymize Student Profile if exists
            if ($user->studentProfile) {
                $user->studentProfile->update([
                    'nik' => null,
                    'address' => 'Disamarkan / Dihapus',
                    'birth_place' => 'Disamarkan',
                    'birth_date' => null,
                    'parent_name' => 'Disamarkan',
                    'parent_phone' => null,
                ]);
            }

            // Revoke all tokens
            $user->tokens()->delete();

            // Revoke all consents
            UserConsent::where('user_id', $user->id)->update([
                'is_granted' => false,
                'revoked_at' => now(),
            ]);

            return true;
        });
    }
}
