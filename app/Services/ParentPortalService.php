<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\ClassroomStudent;
use App\Models\ExamAttempt;
use App\Models\LessonSchedule;
use App\Models\ParentLinkCode;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\StudentReportCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ParentPortalService
{
    /**
     * Resolve the active verified child for the authenticated parent.
     */
    public function getActiveChild(User $parent, ?int $requestedChildId = null): ?User
    {
        $activeChildren = $this->getActiveChildren($parent);

        if ($activeChildren->isEmpty()) {
            return null;
        }

        if ($requestedChildId) {
            $matched = $activeChildren->firstWhere('id', $requestedChildId);
            if ($matched) {
                session(['parent_active_child_id' => $matched->id]);

                return $matched;
            }
        }

        $sessionChildId = session('parent_active_child_id');
        if ($sessionChildId) {
            $matched = $activeChildren->firstWhere('id', $sessionChildId);
            if ($matched) {
                return $matched;
            }
        }

        $first = $activeChildren->first();
        session(['parent_active_child_id' => $first->id]);

        return $first;
    }

    /**
     * Get all active verified children for the parent.
     *
     * @return Collection<int, User>
     */
    public function getActiveChildren(User $parent): Collection
    {
        return $parent->activeChildren()
            ->with([
                'studentProfile',
                'classroomEnrollments.classroom.gradeLevel',
                'classroomEnrollments.classroom.homeroomTeacher',
                'classroomEnrollments.classroom.academicYear',
            ])
            ->get();
    }

    /**
     * Get the active classroom enrollment for a student.
     */
    public function getActiveEnrollment(User $student): ?ClassroomStudent
    {
        return ClassroomStudent::with([
            'classroom.gradeLevel',
            'classroom.homeroomTeacher',
            'classroom.academicYear',
            'classroom.teachingAssignments.teacher',
            'classroom.teachingAssignments.subject',
        ])
            ->where('student_id', $student->id)
            ->latest('id')
            ->first();
    }

    /**
     * Get attendance summary statistics for a student.
     *
     * @return array<string, mixed>
     */
    public function getAttendanceSummary(User $student): array
    {
        $records = AttendanceRecord::where('student_id', $student->id)->get();
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $late = $records->where('status', 'late')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();

        $percentage = $total > 0
            ? round((($present + $late) / $total) * 100, 1)
            : 100.0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'sick' => $sick,
            'permit' => $permit,
            'absent' => $absent,
            'percentage' => $percentage,
            'recent' => AttendanceRecord::with(['session.teachingAssignment.subject', 'session.teacher'])
                ->where('student_id', $student->id)
                ->latest('id')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Get upcoming or pending assignments for a student's enrolled classroom.
     *
     * @return Collection<int, Assignment>
     */
    public function getStudentAssignments(User $student, int $limit = 5): Collection
    {
        $enrollment = $this->getActiveEnrollment($student);
        if (! $enrollment) {
            return new Collection;
        }

        return Assignment::query()
            ->where('classroom_id', $enrollment->classroom_id)
            ->where('status', 'published')
            ->with(['subject', 'teacher', 'submissions' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderBy('due_date', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Get published exam attempts and CBT results for a student.
     *
     * @return Collection<int, ExamAttempt>
     */
    public function getStudentExamResults(User $student, int $limit = 5): Collection
    {
        return ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->whereHas('exam', fn ($q) => $q->whereIn('status', ['published', 'closed']))
            ->with(['exam.subject', 'exam.teacher'])
            ->latest('submitted_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get published digital report cards for a student.
     *
     * @return Collection<int, StudentReportCard>
     */
    public function getPublishedReportCards(User $student): Collection
    {
        return StudentReportCard::query()
            ->where('student_id', $student->id)
            ->where('status', StudentReportCard::STATUS_PUBLISHED)
            ->with(['academicYear', 'semester', 'classroom.gradeLevel', 'homeroomTeacher'])
            ->latest('published_at')
            ->get();
    }

    /**
     * Get announcements for the active child.
     *
     * @return Collection<int, SchoolAnnouncement>
     */
    public function getAnnouncementsForChild(User $student, int $limit = 5): Collection
    {
        $enrollment = $this->getActiveEnrollment($student);
        $schoolId = $student->school_id ?? School::current()?->id;

        if (! $schoolId) {
            return new Collection;
        }

        $classroomId = $enrollment?->classroom_id;
        $gradeLevelId = $enrollment?->classroom?->grade_level_id;

        return SchoolAnnouncement::forParentOfStudent($schoolId, $classroomId, $gradeLevelId)
            ->with(['author'])
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get weekly lesson schedules for a student.
     *
     * @return Collection<int, LessonSchedule>
     */
    public function getLessonSchedules(User $student): Collection
    {
        $enrollment = $this->getActiveEnrollment($student);
        if (! $enrollment) {
            return new Collection;
        }

        return LessonSchedule::query()
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
            ])
            ->where('is_active', true)
            ->whereHas('teachingAssignment', function ($q) use ($enrollment) {
                $q->where('classroom_id', $enrollment->classroom_id);
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Redeem / claim a parent invitation link code.
     *
     * @throws ValidationException
     */
    public function claimLinkCode(User $parent, string $code, ?string $relationshipType = null): ParentStudentRelation
    {
        $normalizedCode = strtoupper(trim($code));

        $linkCode = ParentLinkCode::where('code', $normalizedCode)->first();

        if (! $linkCode) {
            throw ValidationException::withMessages([
                'code' => 'Kode penautan tidak ditemukan. Pastikan Anda memasukkan kode dengan benar.',
            ]);
        }

        if ($linkCode->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'Kode penautan ini telah kedaluwarsa. Silakan minta kode baru kepada pihak sekolah.',
            ]);
        }

        if ($linkCode->isMaxedOut()) {
            throw ValidationException::withMessages([
                'code' => 'Kode penautan ini sudah pernah digunakan dan telah mencapai batas pemakaian.',
            ]);
        }

        $student = $linkCode->student;
        if (! $student || $student->role !== UserRole::STUDENT) {
            throw ValidationException::withMessages([
                'code' => 'Siswa yang terkait dengan kode ini tidak valid.',
            ]);
        }

        // Prevent cross-school link
        if ($parent->school_id && $student->school_id && (int) $parent->school_id !== (int) $student->school_id) {
            throw ValidationException::withMessages([
                'code' => 'Akun orang tua dan siswa terdaftar pada sekolah yang berbeda.',
            ]);
        }

        // Check if relation already exists
        $existing = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->isActive()) {
            throw ValidationException::withMessages([
                'code' => 'Anda sudah terhubung secara aktif dengan siswa '.$student->name.'.',
            ]);
        }

        // If parent has no school_id yet, associate with student's school
        if (! $parent->school_id && $student->school_id) {
            $parent->update(['school_id' => $student->school_id]);
        }

        $relation = ParentStudentRelation::updateOrCreate(
            [
                'parent_id' => $parent->id,
                'student_id' => $student->id,
            ],
            [
                'school_id' => $student->school_id ?? $parent->school_id ?? 1,
                'relationship_type' => $relationshipType ?: ($linkCode->relationship_type ?: 'guardian'),
                'status' => ParentStudentRelation::STATUS_ACTIVE,
                'verified_at' => now(),
                'verified_by' => $linkCode->created_by,
                'notes' => 'Ditautkan otomatis melalui kode undangan '.$linkCode->code,
            ]
        );

        // Update code usage
        $linkCode->increment('used_count');
        $linkCode->update([
            'used_by' => $parent->id,
            'used_at' => now(),
        ]);

        // Auto switch active child to this new child
        session(['parent_active_child_id' => $student->id]);

        return $relation;
    }
}
