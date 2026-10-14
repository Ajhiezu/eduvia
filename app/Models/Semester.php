<?php

namespace App\Models;

use Database\Factories\SemesterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'academic_year_id',
    'name',
    'semester_type',
    'start_date',
    'end_date',
    'is_active',
])]
class Semester extends Model
{
    /** @use HasFactory<SemesterFactory> */
    use HasFactory;

    /**
     * Get attributes cast types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Academic year this semester belongs to.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Atomically activate this semester.
     * Ensures parent academic year is active, and only this semester is active in the school.
     */
    public function activate(): void
    {
        DB::transaction(function (): void {
            $academicYear = $this->academicYear;

            if ($academicYear) {
                // Ensure parent academic year is active
                if (! $academicYear->is_active) {
                    $academicYear->activate();
                }

                // Deactivate all other semesters in this school's academic years
                static::whereHas('academicYear', function ($query) use ($academicYear): void {
                    $query->where('school_id', $academicYear->school_id);
                })
                    ->where('id', '!=', $this->id)
                    ->update(['is_active' => false]);
            }

            $this->update(['is_active' => true]);
        });
    }

    /**
     * Deactivate this semester.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
