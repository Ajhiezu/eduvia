<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmissionHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'assignment_submission_id',
        'attempt_number',
        'content',
        'score',
        'feedback',
        'status',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'decimal:2',
        'attempt_number' => 'integer',
    ];

    /**
     * Submission relationship.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'assignment_submission_id');
    }

    /**
     * Grader relationship.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Accessor for student_notes.
     */
    public function getStudentNotesAttribute(): ?string
    {
        return $this->content;
    }

    /**
     * Accessor for teacher_feedback.
     */
    public function getTeacherFeedbackAttribute(): ?string
    {
        return $this->feedback;
    }
}
