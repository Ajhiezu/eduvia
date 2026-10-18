<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\ParentLinkCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ParentLinkCode extends Model
{
    /** @use HasFactory<ParentLinkCodeFactory> */
    use BelongsToSchool, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'code',
        'relationship_type',
        'expires_at',
        'max_uses',
        'used_count',
        'used_by',
        'used_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
    ];

    /**
     * School relationship.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Student relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * User who used the code.
     *
     * @return BelongsTo<User, $this>
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * User who created the code.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope valid and unexpired codes.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now())
            ->whereColumn('used_count', '<', 'max_uses');
    }

    /**
     * Check if code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if code has reached maximum uses.
     */
    public function isMaxedOut(): bool
    {
        return $this->used_count >= $this->max_uses;
    }

    /**
     * Check if code is still usable.
     */
    public function isUsable(): bool
    {
        return ! $this->isExpired() && ! $this->isMaxedOut();
    }

    /**
     * Generate an unambiguous, secure invitation code.
     * Format: EDU-P-XXXX-XXXX
     */
    public static function generateCode(): string
    {
        do {
            $code = 'EDUV-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
