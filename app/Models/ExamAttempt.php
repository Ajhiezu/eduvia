<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_GRADED = 'graded';

    public const STATUS_ABANDONED = 'abandoned';

    public const STATUS_EXPIRED = 'expired';

    /**
     * Alias for grade percentage.
     */
    public function getGradePercentageAttribute(): float
    {
        return (float) ($this->final_percentage ?? 0.0);
    }

    /**
     * Check if attempt passed KKM.
     */
    public function isPassed(): bool
    {
        return (bool) $this->is_passed;
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            self::STATUS_SUBMITTED => 'Terkumpul (Perlu Dinilai)',
            self::STATUS_GRADED => 'Selesai Dinilai',
            self::STATUS_ABANDONED => 'Dibatalkan',
            self::STATUS_EXPIRED => 'Waktu Habis',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'student_id',
        'attempt_number',
        'started_at',
        'expires_at',
        'submitted_at',
        'status',
        'questions_snapshot',
        'total_score',
        'final_percentage',
        'is_passed',
        'graded_at',
        'graded_by',
        'teacher_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'questions_snapshot' => 'array',
        'total_score' => 'decimal:2',
        'final_percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'attempt_number' => 'integer',
    ];

    /**
     * Exam relationship.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
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
     * Answers submitted in this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_attempt_id');
    }

    /**
     * Check if attempt is actively in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if attempt is already submitted.
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_GRADED], true);
    }

    /**
     * Check if attempt has expired based on server time.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Remaining seconds based strictly on server time.
     */
    public function remainingSeconds(): int
    {
        if (! $this->isInProgress()) {
            return 0;
        }

        $diff = (int) now()->diffInSeconds($this->expires_at, false);

        return max(0, $diff);
    }

    /**
     * Check if there are answers waiting for manual teacher grading.
     */
    public function hasPendingManualGrading(): bool
    {
        return $this->answers()->whereHas('question', fn ($q) => $q->where('question_type', ExamQuestion::TYPE_ESSAY))
            ->whereNull('graded_at')
            ->exists();
    }

    /**
     * Calculate and finalize grade for this attempt.
     */
    public function calculateFinalGrade(?int $graderId = null): void
    {
        $earnedScore = (float) $this->answers()->sum('score');

        // Total possible score: either exam total_score or sum of weights from snapshot
        $maxScore = (float) ($this->exam?->total_score ?? 0);
        if ($maxScore <= 0 && is_array($this->questions_snapshot)) {
            $maxScore = (float) collect($this->questions_snapshot)->sum('score');
        }

        $percentage = $maxScore > 0 ? round(($earnedScore / $maxScore) * 100, 2) : 0.00;

        $passingGrade = $this->exam?->passing_grade;
        $isPassed = $passingGrade !== null ? ($percentage >= (float) $passingGrade) : null;

        $hasPending = $this->hasPendingManualGrading();

        $this->update([
            'total_score' => $earnedScore,
            'final_percentage' => $percentage,
            'is_passed' => $isPassed,
            'status' => $hasPending ? self::STATUS_SUBMITTED : self::STATUS_GRADED,
            'graded_at' => $hasPending ? null : now(),
            'graded_by' => $hasPending ? null : ($graderId ?? $this->graded_by),
        ]);
    }
}
