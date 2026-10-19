<?php

namespace App\Models;

use App\Services\TenantManager;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use RuntimeException;

class AuditLog extends Model
{
    use BelongsToSchool;

    /**
     * Disable updated_at since audit logs are strictly append-only.
     */
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot model events to guarantee absolute immutability.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new RuntimeException('Audit logs are immutable and cannot be modified.');
        });

        static::deleting(function () {
            throw new RuntimeException('Audit logs are immutable and cannot be deleted.');
        });
    }

    /**
     * User who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relation to the entity being audited.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mask sensitive PII data before persisting in audit logs.
     */
    public static function maskSensitiveData(array $attributes): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'access_token',
            'refresh_token',
            'secret',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'cvv',
            'pin',
        ];

        foreach ($attributes as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, $sensitiveKeys, true)) {
                $attributes[$key] = '********';

                continue;
            }

            // Mask Indonesian NIK (16 digits): keep first 6 (region) and last 4, mask middle 6
            if ($lowerKey === 'nik' && is_string($value) && strlen($value) >= 10) {
                $attributes[$key] = substr($value, 0, 6).str_repeat('*', max(0, strlen($value) - 10)).substr($value, -4);

                continue;
            }

            // Mask Bank Account Number: show last 4 digits only
            if (in_array($lowerKey, ['bank_account_number', 'account_number', 'rekening'], true) && is_string($value) && strlen($value) > 4) {
                $attributes[$key] = str_repeat('*', strlen($value) - 4).substr($value, -4);

                continue;
            }

            if (is_array($value)) {
                $attributes[$key] = self::maskSensitiveData($value);
            }
        }

        return $attributes;
    }

    /**
     * Helper to quickly record an audit log entry with request context.
     */
    public static function record(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $schoolId = null
    ): self {
        $user = auth()->user();
        $tenantSchoolId = $schoolId ?? app(TenantManager::class)->getSchoolId() ?? $user?->school_id;

        $creator = function () use ($tenantSchoolId, $user, $auditable, $action, $oldValues, $newValues, $description) {
            return self::create([
                'school_id' => $tenantSchoolId,
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System/Guest',
                'role' => $user?->role instanceof \BackedEnum ? $user->role->value : (string) ($user?->role ?? 'system'),
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->getKey(),
                'action' => $action,
                'old_values' => $oldValues ? self::maskSensitiveData($oldValues) : null,
                'new_values' => $newValues ? self::maskSensitiveData($newValues) : null,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'CLI/Internal',
                'request_id' => Request::header('X-Request-ID') ?? (string) Str::uuid(),
                'description' => $description,
            ]);
        };

        if ($schoolId !== null) {
            return app(TenantManager::class)->withoutTenancy($creator, 'Recording explicit school audit log');
        }

        return $creator();
    }
}
