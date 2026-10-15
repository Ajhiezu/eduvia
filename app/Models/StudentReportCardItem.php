<?php

namespace App\Models;

use Database\Factories\StudentReportCardItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReportCardItem extends Model
{
    /** @use HasFactory<StudentReportCardItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_report_card_id',
        'subject_id',
        'subject_gradebook_item_id',
        'subject_name',
        'subject_code',
        'subject_group',
        'teacher_name',
        'passing_grade',
        'final_score',
        'predicate',
        'is_passed',
        'achievement_description',
        'order_index',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'passing_grade' => 'decimal:2',
        'final_score' => 'decimal:2',
        'is_passed' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Report card relationship.
     */
    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(StudentReportCard::class, 'student_report_card_id');
    }

    /**
     * Subject relationship.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Subject gradebook item relationship.
     */
    public function subjectGradebookItem(): BelongsTo
    {
        return $this->belongsTo(SubjectGradebookItem::class);
    }

    /**
     * Predicate badge visual variant.
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
