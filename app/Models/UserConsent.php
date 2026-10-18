<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    use BelongsToSchool;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_granted' => 'boolean',
            'consented_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * The user whose data processing consent is recorded.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The parent who granted consent on behalf of a child (if applicable).
     */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /**
     * Scope to currently granted and active consents.
     */
    public function scopeGranted(Builder $query): Builder
    {
        return $query->where('is_granted', true)->whereNull('revoked_at');
    }

    /**
     * Scope by consent type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Revoke this consent.
     */
    public function revoke(): void
    {
        $this->update([
            'is_granted' => false,
            'revoked_at' => now(),
        ]);
    }
}
