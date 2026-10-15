<?php

namespace App\Models;

use Database\Factories\SubjectGradebookItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGradebookItem extends Model
{
    /** @use HasFactory<SubjectGradebookItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subject_gradebook_id',
        'student_id',
        'assignment_avg',
        'quiz_avg',
        'midterm_score',
        'final_score_raw',
        'manual_scores',
        'final_score',
        'predicate',
        'is_passed',
        'is_complete',
        'achievement_description',
        'improvement_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assignment_avg' => 'decimal:2',
        'quiz_avg' => 'decimal:2',
        'midterm_score' => 'decimal:2',
        'final_score_raw' => 'decimal:2',
        'final_score' => 'decimal:2',
        'manual_scores' => 'array',
        'is_passed' => 'boolean',
        'is_complete' => 'boolean',
    ];

    /**
     * Gradebook relationship.
     */
    public function subjectGradebook(): BelongsTo
    {
        return $this->belongsTo(SubjectGradebook::class);
    }

    /**
     * Student relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Badge visual variant for predicate.
     */
    public function predicateBadgeVariant(): string
    {
        return match ($this->predicate) {
            'A' => 'success',
            'B' => 'brand',
            'C' => 'warning',
            default => 'danger',
        };
    }
}
