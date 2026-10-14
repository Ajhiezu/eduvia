<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LearningMaterial extends Model
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
        'description',
        'content',
        'video_url',
        'status',
        'published_at',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'order' => 'integer',
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
        return $this->hasMany(LearningMaterialAttachment::class);
    }

    /**
     * Progress relationship for all students.
     */
    public function progressRecords(): HasMany
    {
        return $this->hasMany(LearningMaterialProgress::class);
    }

    /**
     * Progress relationship for authenticated / specified student.
     */
    public function studentProgress(int $studentId): HasOne
    {
        return $this->hasOne(LearningMaterialProgress::class)->where('student_id', $studentId);
    }

    /**
     * Check if material is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if material is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if material is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Scope for published materials only.
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
     * Scope for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Get Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'published' => 'Diterbitkan',
            'draft' => 'Draft',
            'archived' => 'Diarsipkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge variant.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'neutral',
            default => 'neutral',
        };
    }
}
