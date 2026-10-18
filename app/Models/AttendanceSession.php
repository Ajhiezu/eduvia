<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSession extends Model
{
    use BelongsToSchool, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'classroom_id',
        'teaching_assignment_id',
        'academic_year_id',
        'semester_id',
        'teacher_id',
        'session_type',
        'meeting_number',
        'date',
        'start_time',
        'end_time',
        'topic',
        'status',
        'finalized_at',
        'finalized_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
        'meeting_number' => 'integer',
    ];

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Classroom relationship.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Teaching assignment relationship.
     */
    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    /**
     * Academic year relationship.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Semester relationship.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Teacher (recording teacher) relationship.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * User who finalized this session.
     */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Attendance records for each student.
     */
    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Check if session is finalized.
     */
    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    /**
     * Finalize the attendance session.
     */
    public function finalize(User $actor): void
    {
        $this->update([
            'status' => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $actor->id,
        ]);
    }

    /**
     * Reopen session for correction.
     */
    public function reopen(User $actor, ?string $reason = null): void
    {
        $this->update([
            'status' => 'draft',
            'notes' => $reason ? ($this->notes ? $this->notes."\n[Dibuka Ulang oleh {$actor->name}]: {$reason}" : "[Dibuka Ulang]: {$reason}") : $this->notes,
        ]);
    }

    /**
     * Save/Update attendance records with atomic transaction and audit trail.
     *
     * @param  array<int, array{student_id: int, status: string, notes?: string|null}>  $recordsData
     *
     * @throws ValidationException
     */
    public function recordAttendance(array $recordsData, User $actor, ?string $correctionReason = null): void
    {
        $validStatuses = ['present', 'sick', 'permit', 'absent', 'late'];

        DB::transaction(function () use ($recordsData, $actor, $validStatuses, $correctionReason) {
            foreach ($recordsData as $data) {
                $studentId = $data['student_id'];
                $status = $data['status'];
                $notes = $data['notes'] ?? null;

                if (! in_array($status, $validStatuses, true)) {
                    throw ValidationException::withMessages([
                        'status' => "Status presensi '{$status}' tidak valid.",
                    ]);
                }

                $existingRecord = AttendanceRecord::where('attendance_session_id', $this->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existingRecord) {
                    if ($existingRecord->status !== $status) {
                        // Audit log for correction
                        AttendanceAuditLog::create([
                            'attendance_record_id' => $existingRecord->id,
                            'user_id' => $actor->id,
                            'previous_status' => $existingRecord->status,
                            'new_status' => $status,
                            'reason' => $correctionReason ?? 'Koreksi presensi oleh pengampu/admin',
                        ]);

                        $existingRecord->update([
                            'status' => $status,
                            'notes' => $notes ?? $existingRecord->notes,
                        ]);
                    } elseif ($notes !== null && $existingRecord->notes !== $notes) {
                        $existingRecord->update(['notes' => $notes]);
                    }
                } else {
                    AttendanceRecord::create([
                        'attendance_session_id' => $this->id,
                        'student_id' => $studentId,
                        'status' => $status,
                        'notes' => $notes,
                    ]);
                }
            }
        });
    }

    /**
     * Attendance summary statistics and documented attendance rate formula.
     * Denominator: Total active students enrolled in this session.
     * Formula: (Present + Late) / Total Students * 100%
     *
     * @return array{present: int, sick: int, permit: int, absent: int, late: int, total: int, rate: float}
     */
    public function attendanceSummary(): array
    {
        $counts = $this->records()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $present = (int) ($counts['present'] ?? 0);
        $sick = (int) ($counts['sick'] ?? 0);
        $permit = (int) ($counts['permit'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);
        $late = (int) ($counts['late'] ?? 0);
        $total = $present + $sick + $permit + $absent + $late;

        $rate = $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0.0;

        return [
            'present' => $present,
            'sick' => $sick,
            'permit' => $permit,
            'absent' => $absent,
            'late' => $late,
            'total' => $total,
            'rate' => $rate,
        ];
    }

    /**
     * Scope for a specific classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }

    /**
     * Scope for a specific teaching assignment.
     */
    public function scopeForAssignment(Builder $query, int $assignmentId): Builder
    {
        return $query->where('teaching_assignment_id', $assignmentId);
    }
}
