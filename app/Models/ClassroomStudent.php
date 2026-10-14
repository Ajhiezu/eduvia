<?php

namespace App\Models;

use Database\Factories\ClassroomStudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['classroom_id', 'student_id', 'academic_year_id', 'enrollment_status', 'joined_at', 'left_at', 'notes'])]
class ClassroomStudent extends Model
{
    /** @use HasFactory<ClassroomStudentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    /**
     * Scope to active enrollments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enrollment_status', 'active');
    }

    /**
     * The classroom this enrollment belongs to.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * The student user.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * The academic year context.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Indonesian readable enrollment status label.
     */
    public function statusLabel(): string
    {
        return match ($this->enrollment_status) {
            'active' => 'Aktif',
            'transferred' => 'Dipindahkan',
            'completed' => 'Lulus / Selesai',
            'dropped' => 'Dikeluarkan / Mundur',
            default => ucfirst($this->enrollment_status),
        };
    }

    /**
     * Badge visual variant.
     */
    public function badgeVariant(): string
    {
        return match ($this->enrollment_status) {
            'active' => 'success',
            'transferred' => 'warning',
            'completed' => 'primary',
            'dropped' => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Alias for badge visual variant.
     */
    public function statusBadgeVariant(): string
    {
        return $this->badgeVariant();
    }
}
