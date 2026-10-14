<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSubmission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'assignment_id',
        'student_id',
        'status',
        'content',
        'student_notes',
        'submitted_at',
        'is_late',
        'score',
        'feedback',
        'teacher_feedback',
        'graded_at',
        'graded_by',
        'is_grade_published',
        'grade_published_at',
        'revision_requested_at',
        'revision_notes',
        'attempt_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'grade_published_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'is_late' => 'boolean',
        'is_grade_published' => 'boolean',
        'score' => 'decimal:2',
        'attempt_number' => 'integer',
    ];

    /**
     * Assignment relationship.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Student relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Grader (Teacher) relationship.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Attachments uploaded with this submission.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentSubmissionAttachment::class);
    }

    /**
     * Revision & grading history records.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(AssignmentSubmissionHistory::class)->orderBy('attempt_number', 'asc');
    }

    /**
     * Check if submission is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if submission is submitted (either on time or late).
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'late', 'resubmitted'], true);
    }

    /**
     * Check if submission has been graded.
     */
    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    /**
     * Check if teacher requested revision.
     */
    public function isRevisionRequested(): bool
    {
        return $this->status === 'revision_requested';
    }

    /**
     * Check if student can edit / resubmit answer.
     */
    public function canStudentEdit(): bool
    {
        if ($this->isDraft() || $this->isRevisionRequested()) {
            return true;
        }

        return false;
    }

    /**
     * Alias for content attribute.
     */
    public function getStudentNotesAttribute(): ?string
    {
        return $this->content;
    }

    public function setStudentNotesAttribute(?string $value): void
    {
        $this->content = $value;
    }

    /**
     * Alias for feedback attribute.
     */
    public function getTeacherFeedbackAttribute(): ?string
    {
        return $this->feedback ?? $this->revision_notes;
    }

    public function setTeacherFeedbackAttribute(?string $value): void
    {
        $this->feedback = $value;
    }

    /**
     * Check if grade has been published to student.
     */
    public function isGradePublished(): bool
    {
        return (bool) ($this->is_grade_published || ($this->grade_published_at && $this->grade_published_at->isPast()));
    }

    /**
     * Record snapshot into history before updating/revising.
     */
    public function snapshotHistory(): void
    {
        AssignmentSubmissionHistory::create([
            'assignment_submission_id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'content' => $this->content,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->graded_at,
            'graded_by' => $this->graded_by,
        ]);
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Jawaban',
            'submitted' => 'Terkumpul Tepat Waktu',
            'late' => 'Terkumpul Terlambat',
            'resubmitted' => 'Terkumpul (Revisi)',
            'graded' => 'Telah Dinilai',
            'revision_requested' => 'Perlu Revisi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Status badge variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'draft' => 'neutral',
            'submitted' => 'primary',
            'late' => 'danger',
            'resubmitted' => 'primary',
            'graded' => 'success',
            'revision_requested' => 'warning',
            default => 'neutral',
        };
    }
}
