<?php

namespace App\Models;

use Database\Factories\ParentStudentRelationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentStudentRelation extends Model
{
    /** @use HasFactory<ParentStudentRelationFactory> */
    use HasFactory;

    public const TYPE_FATHER = 'father';

    public const TYPE_MOTHER = 'mother';

    public const TYPE_GUARDIAN = 'guardian';

    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'parent_id',
        'student_id',
        'relationship_type',
        'status',
        'is_primary_contact',
        'verified_at',
        'verified_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary_contact' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->school_id)) {
                $model->school_id = $model->student?->school_id
                    ?? $model->parent?->school_id
                    ?? auth()->user()?->school_id;
            }
        });
    }

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
     * Parent user account relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Student user account relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Verifier user account relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope active relations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pending verification relations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Human-readable label for relationship type.
     */
    public function relationshipLabel(): string
    {
        return match ($this->relationship_type) {
            self::TYPE_FATHER => 'Ayah Kandung',
            self::TYPE_MOTHER => 'Ibu Kandung',
            self::TYPE_GUARDIAN => 'Wali Murid',
            default => 'Keluarga / Lainnya',
        };
    }

    /**
     * Human-readable label for relationship status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Terverifikasi & Aktif',
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_INACTIVE => 'Nonaktif',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS classes for status.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::STATUS_PENDING => 'bg-amber-50 text-amber-800 border-amber-200',
            self::STATUS_INACTIVE => 'bg-zinc-100 text-zinc-600 border-zinc-200',
            self::STATUS_REJECTED => 'bg-rose-50 text-rose-800 border-rose-200',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
        };
    }

    /**
     * Check if relation is fully verified and active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
