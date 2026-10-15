<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonSchedule extends Model
{
    use HasFactory;

    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'teaching_assignment_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'day_of_week' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Booted method to guarantee school_id consistency.
     */
    protected static function booted(): void
    {
        static::saving(function (LessonSchedule $schedule) {
            if (! $schedule->school_id && $schedule->teachingAssignment) {
                $schedule->school_id = $schedule->teachingAssignment->school_id;
            }
        });
    }

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
     * Direct subject accessor via teaching assignment.
     */
    public function getSubjectAttribute(): ?Subject
    {
        return $this->teachingAssignment?->subject;
    }

    /**
     * Direct classroom accessor via teaching assignment.
     */
    public function getClassroomAttribute(): ?Classroom
    {
        return $this->teachingAssignment?->classroom;
    }

    /**
     * Direct teacher accessor via teaching assignment.
     */
    public function getTeacherAttribute(): ?User
    {
        return $this->teachingAssignment?->teacher;
    }

    /**
     * Direct academic year accessor via teaching assignment.
     */
    public function getAcademicYearAttribute(): ?AcademicYear
    {
        return $this->teachingAssignment?->academicYear;
    }

    /**
     * Direct semester accessor via teaching assignment.
     */
    public function getSemesterAttribute(): ?Semester
    {
        return $this->teachingAssignment?->semester;
    }

    /**
     * Day name in Indonesian.
     */
    public function dayName(): string
    {
        return self::DAYS[$this->day_of_week] ?? '-';
    }

    /**
     * Formatted time range e.g. "07:30 - 09:00".
     */
    public function timeRange(): string
    {
        $start = substr((string) $this->start_time, 0, 5);
        $end = substr((string) $this->end_time, 0, 5);

        return "{$start} - {$end}";
    }

    /**
     * Format start time to HH:MM.
     */
    public function formattedStartTime(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    /**
     * Format end time to HH:MM.
     */
    public function formattedEndTime(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    /**
     * Calculate duration in minutes.
     */
    public function durationMinutes(): int
    {
        $start = Carbon::createFromFormat('H:i:s', strlen($this->start_time) === 5 ? $this->start_time.':00' : $this->start_time);
        $end = Carbon::createFromFormat('H:i:s', strlen($this->end_time) === 5 ? $this->end_time.':00' : $this->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Check if this lesson is currently ongoing right now on the specified day.
     */
    public function isOngoing(?Carbon $now = null): bool
    {
        $now = $now ?? now();
        $isoDay = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)

        if ($this->day_of_week !== $isoDay) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        $startTime = strlen($this->start_time) === 5 ? $this->start_time.':00' : $this->start_time;
        $endTime = strlen($this->end_time) === 5 ? $this->end_time.':00' : $this->end_time;

        return $currentTime >= $startTime && $currentTime < $endTime;
    }

    /**
     * Check if this lesson has already passed today.
     */
    public function isPastToday(?Carbon $now = null): bool
    {
        $now = $now ?? now();
        $isoDay = $now->dayOfWeekIso;

        if ($this->day_of_week !== $isoDay) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        $endTime = strlen($this->end_time) === 5 ? $this->end_time.':00' : $this->end_time;

        return $currentTime >= $endTime;
    }

    /**
     * Check if this lesson is upcoming today.
     */
    public function isUpcomingToday(?Carbon $now = null): bool
    {
        $now = $now ?? now();
        $isoDay = $now->dayOfWeekIso;

        if ($this->day_of_week !== $isoDay) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        $startTime = strlen($this->start_time) === 5 ? $this->start_time.':00' : $this->start_time;

        return $currentTime < $startTime;
    }

    /**
     * Scope for active schedules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific school tenant.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where(function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)
                ->orWhereHas('teachingAssignment', fn ($sub) => $sub->where('school_id', $schoolId));
        });
    }

    /**
     * Scope for a specific day of the week.
     */
    public function scopeForDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope for a specific classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->whereHas('teachingAssignment', fn ($q) => $q->where('classroom_id', $classroomId));
    }

    /**
     * Scope for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $teacherId));
    }

    /**
     * Scope for a specific academic year.
     */
    public function scopeForAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->whereHas('teachingAssignment', fn ($q) => $q->where('academic_year_id', $academicYearId));
    }

    /**
     * Robust Backend Conflict Detection Engine.
     *
     * Overlapping interval rule:
     * existing_start < requested_end AND existing_end > requested_start
     *
     * @return array<string, string> Array of detected conflict messages keyed by type ('teacher', 'classroom', 'room')
     */
    public static function findConflicts(
        int $schoolId,
        int $academicYearId,
        ?int $semesterId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        int $teacherId,
        int $classroomId,
        ?string $room = null,
        ?int $excludeScheduleId = null
    ): array {
        $conflicts = [];

        // Normalize time strings to HH:MM:SS
        $normStart = strlen($startTime) === 5 ? $startTime.':00' : $startTime;
        $normEnd = strlen($endTime) === 5 ? $endTime.':00' : $endTime;

        // Base query for active schedules in the same school on the same day of week with overlapping time intervals
        $baseQuery = static::query()
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<', $normEnd)
            ->where('end_time', '>', $normStart)
            ->whereHas('teachingAssignment', function ($q) use ($schoolId, $academicYearId, $semesterId) {
                $q->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('is_active', true);

                if ($semesterId) {
                    $q->where(function ($sub) use ($semesterId) {
                        $sub->whereNull('semester_id')->orWhere('semester_id', $semesterId);
                    });
                }
            })
            ->with(['teachingAssignment.teacher', 'teachingAssignment.classroom', 'teachingAssignment.subject']);

        if ($excludeScheduleId) {
            $baseQuery->where('id', '!=', $excludeScheduleId);
        }

        $overlappingSchedules = $baseQuery->get();

        foreach ($overlappingSchedules as $existing) {
            $ta = $existing->teachingAssignment;
            if (! $ta) {
                continue;
            }

            // 1. Teacher Conflict: teacher cannot teach two classes at the same time
            if ($ta->teacher_id === $teacherId && ! isset($conflicts['teacher'])) {
                $teacherName = $ta->teacher?->name ?? 'Guru pengampu';
                $className = $ta->classroom?->name ?? 'Kelas lain';
                $subjectName = $ta->subject?->name ?? 'Mata pelajaran lain';
                $conflicts['teacher'] = "Bentrok Guru: {$teacherName} sudah memiliki jadwal mengajar di {$className} ({$subjectName}) pada pukul {$existing->timeRange()}.";
            }

            // 2. Classroom Conflict: class cannot have two lessons at the same time
            if ($ta->classroom_id === $classroomId && ! isset($conflicts['classroom'])) {
                $className = $ta->classroom?->name ?? 'Kelas';
                $subjectName = $ta->subject?->name ?? 'Mata pelajaran lain';
                $teacherName = $ta->teacher?->name ?? 'Guru';
                $conflicts['classroom'] = "Bentrok Kelas: {$className} sudah memiliki jadwal {$subjectName} bersama {$teacherName} pada pukul {$existing->timeRange()}.";
            }

            // 3. Room Conflict: room cannot be used by two classes at the same time (only if room is specified)
            if (! empty($room) && ! empty($existing->room) && strcasecmp(trim((string) $existing->room), trim((string) $room)) === 0 && ! isset($conflicts['room'])) {
                $className = $ta->classroom?->name ?? 'Kelas lain';
                $subjectName = $ta->subject?->name ?? 'Mata pelajaran';
                $conflicts['room'] = "Bentrok Ruang: Ruang '{$existing->room}' sedang digunakan oleh {$className} ({$subjectName}) pada pukul {$existing->timeRange()}.";
            }
        }

        return $conflicts;
    }
}
