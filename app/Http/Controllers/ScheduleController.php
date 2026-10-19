<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonSchedule;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Resolve the active school ID for the current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Ensure the schedule belongs to the current school tenant.
     */
    protected function authorizeTenant(LessonSchedule $schedule): void
    {
        $schoolId = $this->currentSchoolId();
        if (! $schoolId) {
            return;
        }

        if ($schedule->school_id && (int) $schedule->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Jadwal ini tidak terdaftar pada sekolah Anda.');
        }

        if ($schedule->teachingAssignment && (int) $schedule->teachingAssignment->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Penugasan mengajar jadwal ini tidak terdaftar pada sekolah Anda.');
        }
    }

    /**
     * Display a listing of lesson schedules with multiple view modes (weekly, daily, list).
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');
        $educationalLevel = $request->input('educational_level');
        $classroomId = $request->input('classroom_id');
        $teacherId = $request->input('teacher_id');
        $subjectId = $request->input('subject_id');
        $dayOfWeek = $request->input('day_of_week');
        $status = $request->input('status');
        $search = $request->input('search');
        $viewMode = $request->input('view', 'weekly'); // 'weekly', 'daily', 'list'

        // Active Academic Year default if not provided
        $activeAcademicYear = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        if (! $academicYearId && $activeAcademicYear) {
            $academicYearId = $activeAcademicYear->id;
        }

        $query = LessonSchedule::query()
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.classroom.gradeLevel',
                'teachingAssignment.teacher.staffProfile',
                'teachingAssignment.academicYear',
                'teachingAssignment.semester',
            ])
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->when($academicYearId, fn ($q) => $q->forAcademicYear((int) $academicYearId))
            ->when($classroomId, fn ($q) => $q->forClassroom((int) $classroomId))
            ->when($teacherId, fn ($q) => $q->forTeacher((int) $teacherId))
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->whereHas('teachingAssignment', fn ($sub) => $sub->where('subject_id', (int) $subjectId));
            })
            ->when($educationalLevel, function ($q) use ($educationalLevel) {
                $q->whereHas('teachingAssignment.classroom.gradeLevel', fn ($g) => $g->where('educational_level', $educationalLevel));
            })
            ->when($semesterId, function ($q) use ($semesterId) {
                $q->whereHas('teachingAssignment', fn ($sub) => $sub->where('semester_id', (int) $semesterId));
            })
            ->when($dayOfWeek, fn ($q) => $q->forDay((int) $dayOfWeek))
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', (bool) $status);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('room', 'like', "%{$search}%")
                        ->orWhereHas('teachingAssignment.subject', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('teachingAssignment.classroom', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('teachingAssignment.teacher', fn ($tq) => $tq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        // Prepare view-specific data
        if ($viewMode === 'list') {
            $schedules = $query->paginate(20)->withQueryString();
            $schedulesByDay = collect();
        } else {
            $allMatchingSchedules = $query->get();
            $schedulesByDay = $allMatchingSchedules->groupBy('day_of_week');
            $schedules = $allMatchingSchedules;
        }

        // Dropdown filter options
        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('semester_type')
            ->get();

        $classrooms = Classroom::with('gradeLevel')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($educationalLevel, fn ($q) => $q->whereHas('gradeLevel', fn ($g) => $g->where('educational_level', $educationalLevel)))
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->gradeLevel?->order ?? 0) <=> ($b->gradeLevel?->order ?? 0),
                fn ($a, $b) => ($a->gradeLevel?->level_number ?? 0) <=> ($b->gradeLevel?->level_number ?? 0),
                fn ($a, $b) => strnatcasecmp($a->name, $b->name),
            ])
            ->values();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($educationalLevel, fn ($q) => $q->where('educational_level', $educationalLevel))
            ->where('is_active', true)
            ->orderBy('educational_level')
            ->orderBy('name')
            ->get();

        $teachers = User::with('staffProfile')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('role', UserRole::TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $days = LessonSchedule::DAYS;
        $currentIsoDay = Carbon::now()->dayOfWeekIso;
        $selectedDailyDay = $dayOfWeek ? (int) $dayOfWeek : ($currentIsoDay <= 6 ? $currentIsoDay : 1);

        return view('schedules.index', compact(
            'schedules',
            'schedulesByDay',
            'viewMode',
            'academicYears',
            'semesters',
            'classrooms',
            'subjects',
            'teachers',
            'days',
            'academicYearId',
            'semesterId',
            'educationalLevel',
            'classroomId',
            'teacherId',
            'subjectId',
            'dayOfWeek',
            'status',
            'search',
            'selectedDailyDay'
        ));
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $activeAcademicYear = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        $selectedClassroomId = $request->input('classroom_id');
        $selectedDay = $request->input('day_of_week', 1);

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))
            ->orderBy('semester_type')
            ->get();

        $classrooms = Classroom::with('gradeLevel')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->gradeLevel?->order ?? 0) <=> ($b->gradeLevel?->order ?? 0),
                fn ($a, $b) => ($a->gradeLevel?->level_number ?? 0) <=> ($b->gradeLevel?->level_number ?? 0),
                fn ($a, $b) => strnatcasecmp($a->name, $b->name),
            ])
            ->values();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('educational_level')
            ->orderBy('name')
            ->get();

        $teachers = User::with('staffProfile')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('role', UserRole::TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Distinct existing room names in school for autocomplete / suggestions
        $existingRooms = LessonSchedule::when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->whereNotNull('room')
            ->where('room', '!=', '')
            ->distinct()
            ->pluck('room')
            ->sort()
            ->values();

        $days = LessonSchedule::DAYS;

        return view('schedules.create', compact(
            'academicYears',
            'semesters',
            'classrooms',
            'subjects',
            'teachers',
            'existingRooms',
            'days',
            'activeAcademicYear',
            'selectedClassroomId',
            'selectedDay'
        ));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        $validated = $request->validated();

        $classroomId = (int) $validated['classroom_id'];
        $subjectId = (int) $validated['subject_id'];
        $teacherId = (int) $validated['teacher_id'];

        // Determine Academic Year
        $academicYearId = ! empty($validated['academic_year_id'])
            ? (int) $validated['academic_year_id']
            : (int) (Classroom::find($classroomId)?->academic_year_id ?? School::current()?->activeAcademicYear?->id);

        $semesterId = ! empty($validated['semester_id']) ? (int) $validated['semester_id'] : null;

        DB::transaction(function () use ($schoolId, $academicYearId, $semesterId, $classroomId, $subjectId, $teacherId, $validated, $request) {
            // Find or create teaching assignment
            $teachingAssignment = TeachingAssignment::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYearId,
                    'classroom_id' => $classroomId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                ],
                [
                    'semester_id' => $semesterId,
                    'hours_per_week' => 2,
                    'is_active' => true,
                ]
            );

            if (! $teachingAssignment->is_active) {
                $teachingAssignment->update(['is_active' => true]);
            }

            LessonSchedule::create([
                'school_id' => $schoolId,
                'teaching_assignment_id' => $teachingAssignment->id,
                'day_of_week' => (int) $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'room' => ! empty($validated['room']) ? trim($validated['room']) : null,
                'is_active' => $request->boolean('is_active', true),
            ]);
        });

        return redirect()->route('schedules.index', [
            'classroom_id' => $classroomId,
            'academic_year_id' => $academicYearId,
        ])->with('success', 'Jadwal pelajaran baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit(LessonSchedule $schedule): View
    {
        $this->authorizeTenant($schedule);
        $schoolId = $this->currentSchoolId();

        $schedule->load(['teachingAssignment.subject', 'teachingAssignment.classroom', 'teachingAssignment.teacher', 'teachingAssignment.academicYear', 'teachingAssignment.semester']);

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $semesters = Semester::when($schoolId, fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('school_id', $schoolId)))
            ->orderBy('semester_type')
            ->get();

        $classrooms = Classroom::with('gradeLevel')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->gradeLevel?->order ?? 0) <=> ($b->gradeLevel?->order ?? 0),
                fn ($a, $b) => ($a->gradeLevel?->level_number ?? 0) <=> ($b->gradeLevel?->level_number ?? 0),
                fn ($a, $b) => strnatcasecmp($a->name, $b->name),
            ])
            ->values();

        $subjects = Subject::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('educational_level')
            ->orderBy('name')
            ->get();

        $teachers = User::with('staffProfile')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('role', UserRole::TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $existingRooms = LessonSchedule::when($schoolId, fn ($q) => $q->forSchool($schoolId))
            ->whereNotNull('room')
            ->where('room', '!=', '')
            ->distinct()
            ->pluck('room')
            ->sort()
            ->values();

        $days = LessonSchedule::DAYS;

        return view('schedules.edit', compact(
            'schedule',
            'academicYears',
            'semesters',
            'classrooms',
            'subjects',
            'teachers',
            'existingRooms',
            'days'
        ));
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(UpdateScheduleRequest $request, LessonSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule);
        $schoolId = $this->currentSchoolId();
        $validated = $request->validated();

        $classroomId = (int) $validated['classroom_id'];
        $subjectId = (int) $validated['subject_id'];
        $teacherId = (int) $validated['teacher_id'];

        $academicYearId = ! empty($validated['academic_year_id'])
            ? (int) $validated['academic_year_id']
            : (int) (Classroom::find($classroomId)?->academic_year_id ?? School::current()?->activeAcademicYear?->id);

        $semesterId = ! empty($validated['semester_id']) ? (int) $validated['semester_id'] : null;

        DB::transaction(function () use ($schedule, $schoolId, $academicYearId, $semesterId, $classroomId, $subjectId, $teacherId, $validated, $request) {
            $teachingAssignment = TeachingAssignment::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYearId,
                    'classroom_id' => $classroomId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                ],
                [
                    'semester_id' => $semesterId,
                    'hours_per_week' => 2,
                    'is_active' => true,
                ]
            );

            if (! $teachingAssignment->is_active) {
                $teachingAssignment->update(['is_active' => true]);
            }

            $schedule->update([
                'school_id' => $schoolId,
                'teaching_assignment_id' => $teachingAssignment->id,
                'day_of_week' => (int) $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'room' => ! empty($validated['room']) ? trim($validated['room']) : null,
                'is_active' => $request->boolean('is_active', true),
            ]);
        });

        return redirect()->route('schedules.index', [
            'classroom_id' => $classroomId,
            'academic_year_id' => $academicYearId,
        ])->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Remove or deactivate the specified schedule from storage.
     */
    public function destroy(LessonSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule);

        // Check if attendance sessions or other learning records are linked to the teaching assignment
        $ta = $schedule->teachingAssignment;
        $hasLinkedAttendance = $ta && $ta->attendanceSessions()->exists();

        if ($hasLinkedAttendance) {
            // Soft deactivate to maintain integrity of past attendance logs
            $schedule->update(['is_active' => false]);
            $message = 'Jadwal pelajaran dinonaktifkan karena sudah memiliki riwayat presensi pembelajaran.';
        } else {
            $schedule->delete();
            $message = 'Jadwal pelajaran berhasil dihapus.';
        }

        return back()->with('success', $message);
    }

    /**
     * Toggle active status of the schedule.
     */
    public function toggleStatus(LessonSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule);

        $schedule->update([
            'is_active' => ! $schedule->is_active,
        ]);

        $statusLabel = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status jadwal berhasil {$statusLabel}.");
    }
}
