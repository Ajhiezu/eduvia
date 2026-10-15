<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['school_id', 'academic_year_id', 'grade_level_id', 'homeroom_teacher_id', 'name', 'code', 'capacity', 'academic_program', 'is_active', 'notes'])]
class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * School this classroom belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Academic year for this study group.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Grade level reference.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Homeroom teacher (wali kelas).
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    /**
     * All student enrollment records.
     */
    public function students(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class);
    }

    /**
     * Active student enrollment records.
     */
    public function activeStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class)->where('enrollment_status', 'active');
    }

    /**
     * Teaching assignments in this classroom.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Attendance sessions in this classroom.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Learning materials in this classroom.
     */
    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class);
    }

    /**
     * Assignments in this classroom.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get current count of active students.
     */
    public function activeStudentCount(): int
    {
        return $this->activeStudents()->count();
    }

    /**
     * Check if classroom is at or above capacity.
     */
    public function isFull(): bool
    {
        if (is_null($this->capacity) || $this->capacity <= 0) {
            return false;
        }

        return $this->activeStudentCount() >= $this->capacity;
    }

    /**
     * Add a student to this classroom with strict duplicate prevention.
     *
     * @throws ValidationException
     */
    public function addStudent(User|int $student, ?string $joinedAt = null, ?string $notes = null): ClassroomStudent
    {
        $studentId = $student instanceof User ? $student->id : $student;
        $studentUser = $student instanceof User ? $student : User::findOrFail($studentId);

        // Verify user is indeed a student
        if (! $studentUser->hasRole(UserRole::STUDENT)) {
            throw ValidationException::withMessages([
                'student_id' => 'Pengguna terpilih bukan merupakan peserta didik (siswa).',
            ]);
        }

        if (! $studentUser->isActive()) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa berstatus non-aktif dan tidak dapat ditempatkan ke rombel.',
            ]);
        }

        return DB::transaction(function () use ($studentId, $joinedAt, $notes) {
            // Check if already active in this classroom
            $existingInThis = $this->activeStudents()
                ->where('student_id', $studentId)
                ->first();

            if ($existingInThis) {
                throw ValidationException::withMessages([
                    'student_id' => 'Siswa sudah terdaftar aktif di rombongan belajar ini.',
                ]);
            }

            // Check if active in another classroom in the SAME academic year
            $existingInOther = ClassroomStudent::where('student_id', $studentId)
                ->where('academic_year_id', $this->academic_year_id)
                ->where('enrollment_status', 'active')
                ->where('classroom_id', '!=', $this->id)
                ->with('classroom')
                ->first();

            if ($existingInOther) {
                throw ValidationException::withMessages([
                    'student_id' => "Siswa sudah terdaftar aktif di rombel {$existingInOther->classroom?->name} pada tahun ajaran ini. Gunakan fitur pindah kelas.",
                ]);
            }

            return ClassroomStudent::create([
                'classroom_id' => $this->id,
                'student_id' => $studentId,
                'academic_year_id' => $this->academic_year_id,
                'enrollment_status' => 'active',
                'joined_at' => $joinedAt ?? now()->toDateString(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Transfer a student from this classroom to another classroom.
     *
     * @throws ValidationException
     */
    public function transferStudent(User|int $student, self $targetClassroom, ?string $effectiveDate = null, ?string $notes = null): ClassroomStudent
    {
        $studentId = $student instanceof User ? $student->id : $student;
        $date = $effectiveDate ?? now()->toDateString();

        if ($this->id === $targetClassroom->id) {
            throw ValidationException::withMessages([
                'target_classroom_id' => 'Rombongan belajar tujuan tidak boleh sama dengan rombel asal.',
            ]);
        }

        if ($this->academic_year_id !== $targetClassroom->academic_year_id) {
            throw ValidationException::withMessages([
                'target_classroom_id' => 'Rombongan belajar tujuan harus berada pada tahun ajaran yang sama.',
            ]);
        }

        return DB::transaction(function () use ($studentId, $targetClassroom, $date, $notes) {
            $currentEnrollment = $this->activeStudents()
                ->where('student_id', $studentId)
                ->first();

            if (! $currentEnrollment) {
                throw ValidationException::withMessages([
                    'student_id' => 'Siswa tidak ditemukan sebagai anggota aktif di rombongan belajar ini.',
                ]);
            }

            // Mark old enrollment as transferred
            $currentEnrollment->update([
                'enrollment_status' => 'transferred',
                'left_at' => $date,
                'notes' => $notes ? $currentEnrollment->notes."\n[Pindah]: ".$notes : $currentEnrollment->notes."\n[Pindah ke {$targetClassroom->name}]",
            ]);

            // Create new active enrollment in target classroom
            return ClassroomStudent::create([
                'classroom_id' => $targetClassroom->id,
                'student_id' => $studentId,
                'academic_year_id' => $targetClassroom->academic_year_id,
                'enrollment_status' => 'active',
                'joined_at' => $date,
                'notes' => "Pindahan dari rombel {$this->name}. ".($notes ?? ''),
            ]);
        });
    }

    /**
     * Remove or drop a student from active enrollment.
     *
     * @throws ValidationException
     */
    public function removeStudent(User|int $student, string $status = 'dropped', ?string $notes = null): bool
    {
        $studentId = $student instanceof User ? $student->id : $student;

        $enrollment = $this->activeStudents()
            ->where('student_id', $studentId)
            ->first();

        if (! $enrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa tidak ditemukan sebagai anggota aktif di rombongan belajar ini.',
            ]);
        }

        return (bool) $enrollment->update([
            'enrollment_status' => $status,
            'left_at' => now()->toDateString(),
            'notes' => $notes ? $enrollment->notes."\n[Dikeluarkan]: ".$notes : $enrollment->notes,
        ]);
    }

    /**
     * Scopes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForGradeLevel(Builder $query, int $gradeLevelId): Builder
    {
        return $query->where('grade_level_id', $gradeLevelId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhereHas('homeroomTeacher', function (Builder $teacherQuery) use ($term) {
                    $teacherQuery->where('name', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Subject gradebooks in this classroom.
     *
     * @return HasMany<SubjectGradebook, $this>
     */
    public function subjectGradebooks(): HasMany
    {
        return $this->hasMany(SubjectGradebook::class);
    }

    /**
     * Student report cards in this classroom.
     *
     * @return HasMany<StudentReportCard, $this>
     */
    public function studentReportCards(): HasMany
    {
        return $this->hasMany(StudentReportCard::class);
    }
}
