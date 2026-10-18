<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'answer',
        'is_marked_for_review',
        'is_correct',
        'score',
        'feedback',
        'graded_at',
        'graded_by',
        'saved_at',
        'client_sequence_id',
        'client_timestamp',
    ];

    /**
     * Alias for student answer attribute.
     */
    public function getStudentAnswerAttribute(): mixed
    {
        return $this->attributes['answer'] ?? null;
    }

    public function setStudentAnswerAttribute(mixed $value): void
    {
        $this->attributes['answer'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'answer' => 'array',
        'is_marked_for_review' => 'boolean',
        'is_correct' => 'boolean',
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
        'saved_at' => 'datetime',
        'client_sequence_id' => 'integer',
        'client_timestamp' => 'datetime',
    ];

    /**
     * Exam attempt relationship.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    /**
     * Question relationship.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    /**
     * Grader (Teacher) relationship.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Perform automatic scoring based on question definition.
     */
    public function autoGrade(float $maxScore): void
    {
        $q = $this->question;
        if (! $q) {
            return;
        }

        $check = $q->checkAnswer($this->answer);

        if ($check === null) {
            // Essay requires manual evaluation
            $this->is_correct = null;
            $this->score = 0.00;
        } elseif ($check === true) {
            $this->is_correct = true;
            $this->score = $maxScore;
            $this->graded_at = now();
        } else {
            $this->is_correct = false;
            $this->score = 0.00;
            $this->graded_at = now();
        }

        $this->save();
    }
}
