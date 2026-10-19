<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\LessonSchedule;
use App\Models\ParentStudentRelation;
use App\Models\StudentLeaveRequest;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Notification\NotificationPayload;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    use ApiResponse;

    /**
     * Teacher dashboard overview.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
        $todayDate = Carbon::today()->toDateString();

        // 1. Today's schedules
        $teachingAssignmentIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('id');

        $todaySchedules = LessonSchedule::whereIn('teaching_assignment_id', $teachingAssignmentIds)
            ->where('day_of_week', $todayDayOfWeek)
            ->where('is_active', true)
            ->with(['teachingAssignment.subject', 'teachingAssignment.classroom'])
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'subject_name' => $s->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                'classroom_name' => $s->teachingAssignment?->classroom?->name ?? 'Kelas',
                'start_time' => Carbon::parse($s->start_time)->format('H:i'),
                'end_time' => Carbon::parse($s->end_time)->format('H:i'),
                'room' => $s->room,
            ]);

        // 2. Counts
        $homeroomClassroomIds = Classroom::where('homeroom_teacher_id', $teacher->id)->pluck('id');

        $pendingLeavesCount = StudentLeaveRequest::whereIn('classroom_id', $homeroomClassroomIds)
            ->where('status', StudentLeaveRequest::STATUS_PENDING)
            ->count();

        $ungradedSubmissionsCount = AssignmentSubmission::whereHas('assignment', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->whereNull('score')->count();

        $pendingSessionsCount = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('date', $todayDate)
            ->where('status', 'draft')
            ->count();

        return $this->apiSuccess([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'is_homeroom' => $homeroomClassroomIds->isNotEmpty(),
            ],
            'today_schedules' => $todaySchedules,
            'summary' => [
                'today_classes_count' => $todaySchedules->count(),
                'pending_attendance_sessions_count' => $pendingSessionsCount,
                'ungraded_submissions_count' => $ungradedSubmissionsCount,
                'pending_leave_requests_count' => $pendingLeavesCount,
            ],
        ], 'Dashboard guru berhasil dimuat.');
    }

    /**
     * Weekly teaching schedule for this teacher.
     */
    public function schedules(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $assignmentIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('id');

        $schedules = LessonSchedule::whereIn('teaching_assignment_id', $assignmentIds)
            ->where('is_active', true)
            ->with(['teachingAssignment.subject', 'teachingAssignment.classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($items, $day) {
                return [
                    'day_number' => (int) $day,
                    'day_name' => LessonSchedule::DAYS[(int) $day] ?? "Hari {$day}",
                    'schedules' => $items->map(fn ($s) => [
                        'id' => $s->id,
                        'teaching_assignment_id' => $s->teaching_assignment_id,
                        'subject_id' => $s->teachingAssignment?->subject_id,
                        'subject_name' => $s->teachingAssignment?->subject?->name,
                        'classroom_id' => $s->teachingAssignment?->classroom_id,
                        'classroom_name' => $s->teachingAssignment?->classroom?->name,
                        'start_time' => Carbon::parse($s->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($s->end_time)->format('H:i'),
                        'room' => $s->room,
                    ]),
                ];
            })
            ->values();

        return $this->apiSuccess($schedules, 'Jadwal mengajar berhasil dimuat.');
    }

    /**
     * Get classrooms and enrolled students for this teacher.
     */
    public function classes(Request $request): JsonResponse
    {
        $teacher = $request->user();

        // Classes taught via teaching assignments OR managed as homeroom teacher
        $assignmentClassroomIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('classroom_id');

        $homeroomClassroomIds = Classroom::where('homeroom_teacher_id', $teacher->id)->pluck('id');

        $allClassroomIds = $assignmentClassroomIds->merge($homeroomClassroomIds)->unique();

        $classrooms = Classroom::whereIn('id', $allClassroomIds)
            ->where('is_active', true)
            ->with(['activeStudents.student:id,name,email,avatar', 'activeStudents.student.studentProfile'])
            ->get()
            ->map(function ($c) use ($teacher) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->code,
                    'is_homeroom' => $c->homeroom_teacher_id === $teacher->id,
                    'students_count' => $c->activeStudents->count(),
                    'students' => $c->activeStudents->map(function ($cs) {
                        $st = $cs->student;

                        return [
                            'id' => $st->id,
                            'name' => $st->name,
                            'email' => $st->email,
                            'nis' => $st->studentProfile?->nis ?? '-',
                            'nisn' => $st->studentProfile?->nisn ?? '-',
                            'avatar' => $st->avatar ? asset('storage/'.$st->avatar) : null,
                        ];
                    }),
                ];
            });

        return $this->apiSuccess($classrooms, 'Daftar kelas berhasil dimuat.');
    }

    /**
     * List attendance sessions for this teacher.
     */
    public function attendanceSessions(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $query = AttendanceSession::where('teacher_id', $teacher->id)
            ->with(['classroom:id,name', 'teachingAssignment.subject:id,name'])
            ->latest('date');

        if ($request->filled('date')) {
            $query->where('date', $request->input('date'));
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->input('classroom_id'));
        }

        $sessions = $query->paginate(15);

        return $this->apiPaginated($sessions, 'Sesi presensi berhasil dimuat.');
    }

    /**
     * Create or open an attendance session.
     */
    public function storeAttendanceSession(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'teaching_assignment_id' => ['nullable', 'integer', 'exists:teaching_assignments,id'],
            'date' => ['required', 'date'],
            'session_type' => ['nullable', 'string', 'in:daily,subject'],
            'topic' => ['nullable', 'string', 'max:255'],
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);

        $session = AttendanceSession::firstOrCreate(
            [
                'school_id' => $teacher->school_id,
                'classroom_id' => $classroom->id,
                'teaching_assignment_id' => $validated['teaching_assignment_id'] ?? null,
                'date' => $validated['date'],
                'session_type' => $validated['session_type'] ?? 'daily',
            ],
            [
                'academic_year_id' => $classroom->academic_year_id,
                'teacher_id' => $teacher->id,
                'start_time' => now()->format('H:i:s'),
                'topic' => $validated['topic'] ?? 'Presensi Pembelajaran',
                'status' => 'draft',
            ]
        );

        return $this->apiSuccess($session->load(['classroom', 'records']), 'Sesi presensi siap digunakan.', 201);
    }

    /**
     * Submit attendance records for a session (Idempotent submission).
     */
    public function saveAttendanceRecords(Request $request, AttendanceSession $session): JsonResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:users,id'],
            'records.*.status' => ['required', 'string', 'in:present,sick,permit,absent,late'],
            'records.*.notes' => ['nullable', 'string', 'max:255'],
            'finalize' => ['nullable', 'boolean'],
        ]);

        $session->recordAttendance($validated['records'], $teacher);

        if (! empty($validated['finalize'])) {
            $session->finalize($teacher);

            // Dispatch generic notification to parents of non-present students
            foreach ($validated['records'] as $recordItem) {
                if ($recordItem['status'] !== 'present') {
                    $student = User::find($recordItem['student_id']);
                    if ($student) {
                        $parentRelations = ParentStudentRelation::where('student_id', $student->id)
                            ->where('status', 'active')
                            ->with('parent')
                            ->get();

                        foreach ($parentRelations as $rel) {
                            if ($rel->parent) {
                                SendNotificationJob::dispatch(new NotificationPayload(
                                    userId: $rel->parent->id,
                                    schoolId: $session->school_id,
                                    eventKey: 'attendance.status_alert',
                                    title: 'Pemberitahuan Presensi Siswa',
                                    body: sprintf('Pembaruan presensi %s: Status %s pada tanggal %s. Buka aplikasi untuk info lengkap.', $student->name, ucfirst($recordItem['status']), $session->date?->format('d/m/Y') ?? 'hari ini'),
                                    data: ['student_id' => $student->id, 'status' => $recordItem['status']],
                                    isUrgent: false
                                ));
                            }
                        }
                    }
                }
            }
        }

        return $this->apiSuccess([
            'session' => $session->fresh(['records']),
            'summary' => $session->attendanceSummary(),
        ], 'Presensi berhasil disimpan.');
    }

    /**
     * List assignments managed by this teacher.
     */
    public function assignments(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->with(['classroom:id,name', 'subject:id,name'])
            ->withCount(['submissions'])
            ->latest()
            ->paginate(15);

        return $this->apiPaginated($assignments, 'Daftar tugas berhasil dimuat.');
    }

    /**
     * Submissions for a specific assignment.
     */
    public function submissions(Request $request, Assignment $assignment): JsonResponse
    {
        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->with(['student:id,name,email,avatar'])
            ->latest('submitted_at')
            ->paginate(20);

        return $this->apiPaginated($submissions, 'Daftar pengumpulan tugas berhasil dimuat.');
    }

    /**
     * Grade a student's assignment submission.
     */
    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission): JsonResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'teacher_feedback' => ['nullable', 'string', 'max:1000'],
            'publish_now' => ['nullable', 'boolean'],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? $submission->teacher_feedback,
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => $teacher->id,
            'is_grade_published' => $validated['publish_now'] ?? true,
            'grade_published_at' => ($validated['publish_now'] ?? true) ? now() : null,
        ]);

        // Notify student about feedback (Without leaking score on lockscreen per Rule 6)
        SendNotificationJob::dispatch(new NotificationPayload(
            userId: $submission->student_id,
            schoolId: $teacher->school_id,
            eventKey: 'assignment.graded',
            title: 'Tugas Selesai Dinilai',
            body: sprintf('Tugas "%s" telah diperiksa dan dinilai oleh guru. Silakan buka aplikasi Eduvia untuk melihat hasil dan umpan balik.', $assignment->title),
            data: ['assignment_id' => $assignment->id, 'submission_id' => $submission->id],
            isUrgent: false
        ));

        return $this->apiSuccess($submission->fresh(['student:id,name']), 'Nilai tugas berhasil disimpan.');
    }

    /**
     * Update teacher chat availability and auto-reply settings.
     */
    public function updateChatSettings(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'chat_service_hours_start' => ['nullable', 'date_format:H:i'],
            'chat_service_hours_end' => ['nullable', 'date_format:H:i'],
            'chat_service_days' => ['nullable', 'array'],
            'chat_service_days.*' => ['integer', 'between:1,7'],
            'chat_auto_reply_enabled' => ['nullable', 'boolean'],
            'chat_auto_reply_message' => ['nullable', 'string', 'max:500'],
        ]);

        $teacher->update($validated);

        return $this->apiSuccess([
            'chat_service_hours_start' => $teacher->chat_service_hours_start,
            'chat_service_hours_end' => $teacher->chat_service_hours_end,
            'chat_service_days' => $teacher->chat_service_days,
            'chat_auto_reply_enabled' => (bool) $teacher->chat_auto_reply_enabled,
            'chat_auto_reply_message' => $teacher->chat_auto_reply_message,
        ], 'Pengaturan jam layanan chat guru berhasil diperbarui.');
    }
}
