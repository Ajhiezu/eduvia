<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use BelongsToSchool, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    public const EXPLANATION_NEVER = 'never';

    public const EXPLANATION_AFTER_SUBMIT = 'after_submit';

    public const EXPLANATION_AFTER_CLOSED = 'after_closed';

    public const EXPLANATION_POLICY_NEVER = self::EXPLANATION_NEVER;

    public const EXPLANATION_POLICY_IMMEDIATE = self::EXPLANATION_AFTER_SUBMIT;

    public const EXPLANATION_POLICY_AFTER_EXAM_END = self::EXPLANATION_AFTER_CLOSED;

    /**
     * Alias for explanation policy attribute.
     */
    public function getExplanationPolicyAttribute(): string
    {
        return $this->attributes['show_explanation_policy'] ?? self::EXPLANATION_AFTER_CLOSED;
    }

    public function setExplanationPolicyAttribute(string $val): void
    {
        $this->attributes['show_explanation_policy'] = $val;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'start_time',
        'end_time',
        'max_attempts',
        'passing_grade',
        'status',
        'shuffle_questions',
        'shuffle_options',
        'show_result_immediately',
        'show_explanation_policy',
        'total_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'max_attempts' => 'integer',
        'passing_grade' => 'decimal:2',
        'total_score' => 'decimal:2',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'show_result_immediately' => 'boolean',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Teacher (creator) relationship.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Subject relationship.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Academic Year relationship.
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
     * Target classrooms for this exam.
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'exam_classrooms')
            ->withTimestamps();
    }

    /**
     * Question items composition in this exam.
     */
    public function questionItems(): HasMany
    {
        return $this->hasMany(ExamQuestionItem::class)->orderBy('order');
    }

    /**
     * Questions belonging to this exam.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(ExamQuestion::class, 'exam_question_items', 'exam_id', 'question_id')
            ->withPivot('order', 'score')
            ->orderByPivot('order')
            ->withTimestamps();
    }

    /**
     * Student attempts for this exam.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Check if exam is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if exam is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if exam is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED || ($this->end_time && now()->isAfter($this->end_time));
    }

    /**
     * Check if exam is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED || ($this->status === self::STATUS_PUBLISHED && $this->start_time && now()->isBefore($this->start_time));
    }

    /**
     * Human-readable label for exam status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PUBLISHED => $this->isAvailableNow() ? 'Sedang Berlangsung' : ($this->isClosed() ? 'Ditutup' : 'Terjadwal'),
            self::STATUS_CLOSED => 'Ditutup',
            self::STATUS_ARCHIVED => 'Diarsipkan',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Check if exam is available right now.
     */
    public function isAvailableNow(): bool
    {
        return $this->isOpenForTaking();
    }

    /**
     * Check if exam is currently open and accepting submissions based on server time.
     */
    public function isOpenForTaking(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        $now = now();

        return $now->isAfter($this->start_time) && $now->isBefore($this->end_time);
    }

    /**
     * Check if exam is scheduled for future.
     */
    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && now()->isBefore($this->start_time);
    }

    /**
     * Check if explanation / answer key can be viewed by students.
     */
    public function canStudentViewExplanation(): bool
    {
        if ($this->show_explanation_policy === self::EXPLANATION_NEVER) {
            return false;
        }

        if ($this->show_explanation_policy === self::EXPLANATION_AFTER_SUBMIT) {
            return true;
        }

        if ($this->show_explanation_policy === self::EXPLANATION_AFTER_CLOSED) {
            return $this->isClosed();
        }

        return false;
    }

    /**
     * Recalculate total score from question items.
     */
    public function recalculateTotalScore(): void
    {
        $total = $this->questionItems()->sum('score');
        $this->update(['total_score' => $total]);
    }

    /**
     * Validate prerequisites before publishing.
     *
     * @return list<string>
     */
    public function validateForPublishing(): array
    {
        $errors = [];

        if (empty(trim((string) $this->title))) {
            $errors[] = 'Judul ujian wajib diisi.';
        }

        if ($this->duration_minutes < 1) {
            $errors[] = 'Durasi ujian minimal 1 menit.';
        }

        if (! $this->start_time || ! $this->end_time || $this->start_time->gte($this->end_time)) {
            $errors[] = 'Waktu mulai harus lebih awal daripada waktu berakhir ujian.';
        }

        if ($this->classrooms()->count() === 0) {
            $errors[] = 'Pilih minimal satu rombongan belajar (kelas) peserta ujian.';
        }

        $questionCount = $this->questionItems()->count();
        if ($questionCount === 0) {
            $errors[] = 'Ujian belum memiliki soal. Tambahkan minimal satu soal dari bank soal.';
        }

        return $errors;
    }

    /**
     * Build frozen questions snapshot for student attempt.
     *
     * @return list<array<string, mixed>>
     */
    public function buildFrozenSnapshot(): array
    {
        $items = $this->questionItems()->with('question')->get();

        if ($this->shuffle_questions) {
            $items = $items->shuffle();
        }

        $snapshot = [];
        $order = 1;

        foreach ($items as $item) {
            $q = $item->question;
            if (! $q) {
                continue;
            }

            $options = $q->options ?? [];
            if ($this->shuffle_options && is_array($options) && in_array($q->question_type, [ExamQuestion::TYPE_MC_SINGLE, ExamQuestion::TYPE_MC_MULTI], true)) {
                shuffle($options);
            }

            $snapshot[] = [
                'id' => $q->id,
                'question_id' => $q->id,
                'order' => $order++,
                'type' => $q->question_type,
                'question_type' => $q->question_type,
                'question' => $q->content,
                'content' => $q->content,
                'options' => $options,
                'score' => (float) $item->score,
                'explanation' => $q->explanation,
                // Note: correct_answer is intentionally kept on server only!
            ];
        }

        return $snapshot;
    }

    /**
     * Scope for a specific school.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for a specific classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->whereHas('classrooms', fn ($q) => $q->where('classrooms.id', $classroomId));
    }
}
