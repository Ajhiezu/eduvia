<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    use HasFactory;

    public const TYPE_MC_SINGLE = 'multiple_choice_single';

    public const TYPE_MC_MULTI = 'multiple_choice_multi';

    public const TYPE_TRUE_FALSE = 'true_false';

    public const TYPE_SHORT_ANSWER = 'short_answer';

    public const TYPE_ESSAY = 'essay';

    public const TYPE_MULTIPLE_CHOICE_SINGLE = self::TYPE_MC_SINGLE;

    public const TYPE_MULTIPLE_CHOICE_MULTI = self::TYPE_MC_MULTI;

    public const TYPES = [
        self::TYPE_MC_SINGLE => 'Pilihan Ganda (Satu Jawaban)',
        self::TYPE_MC_MULTI => 'Pilihan Ganda (Banyak Jawaban)',
        self::TYPE_TRUE_FALSE => 'Benar / Salah',
        self::TYPE_SHORT_ANSWER => 'Isian Singkat',
        self::TYPE_ESSAY => 'Uraian / Esai',
    ];

    /**
     * Alias for question content attribute.
     */
    public function getQuestionAttribute(): string
    {
        return $this->attributes['content'] ?? '';
    }

    public function setQuestionAttribute(string $value): void
    {
        $this->attributes['content'] = $value;
    }

    /**
     * Alias for question type attribute.
     */
    public function getTypeAttribute(): string
    {
        return $this->attributes['question_type'] ?? '';
    }

    public function setTypeAttribute(string $value): void
    {
        $this->attributes['question_type'] = $value;
    }

    public function typeLabel(): string
    {
        return $this->typeName();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'created_by',
        'subject_id',
        'grade_level_id',
        'question_type',
        'content',
        'options',
        'correct_answer',
        'default_score',
        'explanation',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'default_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Author / Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Subject relationship.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Grade level relationship.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Exam question items relationship.
     */
    public function examItems(): HasMany
    {
        return $this->hasMany(ExamQuestionItem::class, 'question_id');
    }

    /**
     * Exams that use this question.
     */
    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_question_items', 'question_id', 'exam_id')
            ->withPivot('order', 'score')
            ->withTimestamps();
    }

    /**
     * Scope for active questions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific school.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for a specific subject.
     */
    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for question type.
     */
    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    /**
     * Human-readable type name in Indonesian.
     */
    public function typeName(): string
    {
        return self::TYPES[$this->question_type] ?? ucfirst(str_replace('_', ' ', $this->question_type));
    }

    /**
     * Check if question is objectively gradable by server algorithm.
     */
    public function isObjective(): bool
    {
        return in_array($this->question_type, [
            self::TYPE_MC_SINGLE,
            self::TYPE_MC_MULTI,
            self::TYPE_TRUE_FALSE,
            self::TYPE_SHORT_ANSWER,
        ], true);
    }

    /**
     * Check if question is an essay requiring human evaluation.
     */
    public function isEssay(): bool
    {
        return $this->question_type === self::TYPE_ESSAY;
    }

    /**
     * Check student answer against correct answer.
     *
     * @return bool|null Returns boolean for auto-gradable types, null for essay
     */
    public function checkAnswer(mixed $studentAnswer): ?bool
    {
        if ($this->isEssay()) {
            return null; // Essay requires manual teacher evaluation
        }

        if ($studentAnswer === null || $studentAnswer === '') {
            return false;
        }

        $correct = $this->correct_answer;

        return match ($this->question_type) {
            self::TYPE_MC_SINGLE => is_array($correct)
                ? in_array((string) $studentAnswer, array_map('strval', $correct), true)
                : strcasecmp(trim((string) $studentAnswer), trim((string) $correct)) === 0,

            self::TYPE_TRUE_FALSE => is_array($correct)
                ? in_array((string) $studentAnswer, array_map('strval', $correct), true)
                : strcasecmp(trim((string) $studentAnswer), trim((string) $correct)) === 0,

            self::TYPE_MC_MULTI => (function () use ($studentAnswer, $correct) {
                if (! is_array($studentAnswer) || ! is_array($correct)) {
                    return false;
                }
                $normStudent = array_map('strval', $studentAnswer);
                $normCorrect = array_map('strval', $correct);
                sort($normStudent);
                sort($normCorrect);

                return $normStudent === $normCorrect;
            })(),

            self::TYPE_SHORT_ANSWER => (function () use ($studentAnswer, $correct) {
                $studentText = mb_strtolower(trim((string) $studentAnswer));
                if (is_array($correct)) {
                    foreach ($correct as $possible) {
                        if (mb_strtolower(trim((string) $possible)) === $studentText) {
                            return true;
                        }
                    }

                    return false;
                }

                return mb_strtolower(trim((string) $correct)) === $studentText;
            })(),

            default => null,
        };
    }
}
