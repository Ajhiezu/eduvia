<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'teaching_assignment_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'title',
        'topic',
        'instructions',
        'submission_type',
        'max_file_size_mb',
        'allowed_file_types',
        'due_date',
        'max_score',
        'status',
        'published_at',
        'allow_late_submission',
        'allow_resubmission',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'published_at' => 'datetime',
        'max_file_size_mb' => 'integer',
        'max_score' => 'integer',
        'allow_late_submission' => 'boolean',
        'allow_resubmission' => 'boolean',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Teaching assignment relationship.
     */
    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
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
     * Attachments relationship.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    /**
     * Student submissions relationship.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Submission for a specific student.
     */
    public function studentSubmission(int $studentId): HasOne
    {
        return $this->hasOne(AssignmentSubmission::class)->where('student_id', $studentId);
    }

    /**
     * Check if assignment is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if assignment has passed its due date.
     */
    public function isPastDue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Calculate summary statistics for teacher dashboard.
     *
     * @return array{enrolled_students: int, submitted: int, unsubmitted: int, late: int, graded: int}
     */
    public function submissionStats(): array
    {
        $enrolledStudentsCount = ClassroomStudent::where('classroom_id', $this->classroom_id)
            ->where('enrollment_status', 'active')
            ->count();

        $submittedCount = $this->submissions()
            ->whereIn('status', ['submitted', 'late', 'graded', 'revision_requested', 'resubmitted'])
            ->count();

        $gradedCount = $this->submissions()
            ->where('status', 'graded')
            ->count();

        $lateCount = $this->submissions()
            ->where('is_late', true)
            ->count();

        $unsubmittedCount = max(0, $enrolledStudentsCount - $submittedCount);

        return [
            'enrolled_students' => $enrolledStudentsCount,
            'submitted' => $submittedCount,
            'unsubmitted' => $unsubmittedCount,
            'late' => $lateCount,
            'graded' => $gradedCount,
        ];
    }

    /**
     * Scope for published assignments only.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for a specific classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'published' => 'Diterbitkan',
            'draft' => 'Draft',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Status badge variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'cancelled' => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Submission type Indonesian label.
     */
    public function submissionTypeLabel(): string
    {
        return match ($this->submission_type) {
            'text_only' => 'Teks Jawaban Langsung',
            'file_only' => 'Unggah Berkas / Dokumen',
            'both' => 'Teks & Berkas Dokumen',
            default => ucfirst($this->submission_type),
        };
    }
}
