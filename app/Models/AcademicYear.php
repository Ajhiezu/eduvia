<?php

namespace App\Models;

use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'school_id',
    'name',
    'start_date',
    'end_date',
    'is_active',
    'description',
])]
class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
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
     * School this academic year belongs to.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Semesters under this academic year.
     *
     * @return HasMany<Semester, $this>
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * Classrooms under this academic year.
     *
     * @return HasMany<Classroom, $this>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Currently active semester in this academic year.
     *
     * @return HasOne<Semester, $this>
     */
    public function activeSemester(): HasOne
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }

    /**
     * Atomically activate this academic year, deactivating others in the same school.
     */
    public function activate(): void
    {
        DB::transaction(function (): void {
            static::where('school_id', $this->school_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            $this->update(['is_active' => true]);
        });
    }

    /**
     * Deactivate this academic year and its semesters.
     */
    public function deactivate(): void
    {
        DB::transaction(function (): void {
            $this->update(['is_active' => false]);
            $this->semesters()->update(['is_active' => false]);
        });
    }
}
