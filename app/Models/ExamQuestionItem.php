<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'question_id',
        'order',
        'score',
    ];

    /**
     * Alias for exam_question_id.
     */
    public function getExamQuestionIdAttribute(): ?int
    {
        return $this->attributes['question_id'] ?? null;
    }

    public function setExamQuestionIdAttribute(?int $value): void
    {
        $this->attributes['question_id'] = $value;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'score' => 'decimal:2',
    ];

    /**
     * Exam relationship.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Question relationship.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
