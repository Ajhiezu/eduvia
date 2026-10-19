<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Database\Factories\SubjectGradebookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectGradebook extends Model
{
    /** @use HasFactory<SubjectGradebookFactory> */
    use BelongsToSchool, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'semester_id',
        'assessment_weight_configuration_id',
        'weight_snapshot',
        'status',
        'submission_notes',
        'verification_notes',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight_snapshot' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Classroom relationship.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Subject relationship.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Teacher relationship.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Academic year relationship.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Semester relationship.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Assessment weight configuration relationship.
     */
    public function assessmentWeightConfiguration(): BelongsTo
    {
        return $this->belongsTo(AssessmentWeightConfiguration::class);
    }

    /**
     * Verifier user relationship.
     */
    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Student grade items in this gradebook.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SubjectGradebookItem::class);
    }

    /**
     * Human-readable Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draf Guru',
            self::STATUS_SUBMITTED => 'Menunggu Verifikasi',
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_REVISION_REQUESTED => 'Perlu Revisi',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Badge visual variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'neutral',
            self::STATUS_SUBMITTED => 'warning',
            self::STATUS_VERIFIED => 'success',
            self::STATUS_REVISION_REQUESTED => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Whether the gradebook can be edited by teacher.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUESTED], true);
    }

    /**
     * Scope for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for verified gradebooks.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }
}
