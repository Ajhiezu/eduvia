<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Attendance\StoreAttendanceSessionRequest;
use App\Http\Requests\Attendance\UpdateAttendanceSessionRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\School;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceSessionController extends Controller
{
    /**
     * Display a listing of attendance sessions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

        $classroomId = $request->input('classroom_id');
        $assignmentId = $request->input('teaching_assignment_id');
        $status = $request->input('status');
        $date = $request->input('date');

        $query = AttendanceSession::query()
            ->with(['classroom', 'teachingAssignment.subject', 'teacher'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($classroomId, fn ($q) => $q->where('classroom_id', $classroomId))
            ->when($assignmentId, fn ($q) => $q->where('teaching_assignment_id', $assignmentId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($date, fn ($q) => $q->whereDate('date', $date));

        // If teacher, only show sessions for assignments taught by this teacher
        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            $query->where('teacher_id', $user->id);
        }

        $sessions = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        // Filters data
        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->orderBy('name')
            ->get();

        $assignmentsQuery = TeachingAssignment::query()
            ->with(['subject', 'classroom'])
            ->active();

        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            $assignmentsQuery->where('teacher_id', $user->id);
        }

        $assignments = $assignmentsQuery->get();

        // High-level statistics
        $totalSessionsCount = (clone $query)->count();
        $finalizedSessionsCount = (clone $query)->where('status', 'finalized')->count();
        $draftSessionsCount = (clone $query)->where('status', 'draft')->count();

        return view('attendance.sessions.index', compact(
            'sessions',
            'classrooms',
            'assignments',
            'classroomId',
            'assignmentId',
            'status',
            'date',
            'totalSessionsCount',
            'finalizedSessionsCount',
            'draftSessionsCount'
        ));
    }

    /**
     * Show the form for creating a new attendance session.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

        $assignmentsQuery = TeachingAssignment::query()
            ->with(['subject', 'classroom.gradeLevel', 'academicYear'])
            ->active();

        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            $assignmentsQuery->where('teacher_id', $user->id);
        }

        $assignments = $assignmentsQuery->get();

        return view('attendance.sessions.create', compact('assignments'));
    }

    /**
     * Store a newly created attendance session.
     */
    public function store(StoreAttendanceSessionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $assignment = TeachingAssignment::with(['classroom', 'academicYear'])->findOrFail($request->input('teaching_assignment_id'));

        // Prevent duplicate meeting session for the same assignment and date/number
        $existing = AttendanceSession::where('teaching_assignment_id', $assignment->id)
            ->where('date', $request->input('date'))
            ->where('meeting_number', $request->input('meeting_number'))
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['meeting_number' => "Sesi presensi pertemuan ke-{$request->input('meeting_number')} pada tanggal tersebut sudah ada."]);
        }

        $session = DB::transaction(function () use ($request, $assignment, $user) {
            $newSession = AttendanceSession::create([
                'school_id' => $assignment->school_id,
                'classroom_id' => $assignment->classroom_id,
                'teaching_assignment_id' => $assignment->id,
                'academic_year_id' => $assignment->academic_year_id,
                'semester_id' => $assignment->semester_id,
                'teacher_id' => $user->id,
                'session_type' => 'subject_session',
                'meeting_number' => $request->input('meeting_number'),
                'date' => $request->input('date'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'topic' => $request->input('topic'),
                'status' => $request->input('status', 'draft'),
                'notes' => $request->input('notes'),
            ]);

            // Pre-populate attendance records for all active students in this classroom
            $activeStudents = $assignment->classroom->activeStudents()->with('student')->get();
            foreach ($activeStudents as $enrollment) {
                AttendanceRecord::create([
                    'attendance_session_id' => $newSession->id,
                    'student_id' => $enrollment->student_id,
                    'status' => 'present',
                ]);
            }

            return $newSession;
        });

        return redirect()->route('attendance-sessions.show', $session)
            ->with('status', "Sesi presensi Pertemuan ke-{$session->meeting_number} berhasil dibuat. Silakan verifikasi kehadiran peserta didik.");
    }

    /**
     * Display the specified attendance session with student attendance sheet.
     */
    public function show(AttendanceSession $session, Request $request): View
    {
        $user = $request->user();

        // Authorization check for teacher
        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            if ($session->teacher_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk sesi presensi ini.');
            }
        }

        $session->load([
            'classroom.gradeLevel',
            'teachingAssignment.subject',
            'academicYear',
            'teacher',
            'finalizer',
            'records.student',
            'records.auditLogs.user',
        ]);

        $records = $session->records->sortBy(fn ($r) => $r->student->name);
        $summary = $session->attendanceSummary();

        // Collect all audit logs for this session
        $auditLogs = collect();
        foreach ($session->records as $r) {
            foreach ($r->auditLogs as $log) {
                $auditLogs->push($log);
            }
        }
        $auditLogs = $auditLogs->sortByDesc('created_at');

        return view('attendance.sessions.show', compact('session', 'records', 'summary', 'auditLogs'));
    }

    /**
     * Update the attendance records and session details.
     */
    public function update(UpdateAttendanceSessionRequest $request, AttendanceSession $session): RedirectResponse
    {
        $user = $request->user();

        $session->update([
            'topic' => $request->input('topic', $session->topic),
            'notes' => $request->input('notes', $session->notes),
        ]);

        $session->recordAttendance(
            $request->input('records', []),
            $user,
            $request->input('correction_reason')
        );

        if ($request->input('action') === 'finalize') {
            $session->finalize($user);
            $message = 'Presensi pertemuan berhasil disimpan dan difinalisasi.';
        } else {
            $message = 'Draft presensi pertemuan berhasil disimpan.';
        }

        return redirect()->route('attendance-sessions.show', $session)->with('status', $message);
    }

    /**
     * Finalize the attendance session.
     */
    public function finalize(Request $request, AttendanceSession $session): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            if ($session->teacher_id !== $user->id) {
                abort(403);
            }
        }

        $session->finalize($user);

        return redirect()->route('attendance-sessions.show', $session)
            ->with('status', 'Sesi presensi berhasil difinalisasi.');
    }

    /**
     * Reopen a finalized attendance session for correction.
     */
    public function reopen(Request $request, AttendanceSession $session): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            if ($session->teacher_id !== $user->id) {
                abort(403);
            }
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $session->reopen($user, $request->input('reason'));

        return redirect()->route('attendance-sessions.show', $session)
            ->with('status', 'Sesi presensi berhasil dibuka kembali untuk perbaikan.');
    }

    /**
     * Remove the specified attendance session from storage.
     */
    public function destroy(Request $request, AttendanceSession $session): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) && $session->teacher_id !== $user->id) {
            abort(403);
        }

        if ($session->isFinalized() && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            return redirect()->back()->withErrors([
                'error' => 'Sesi yang sudah difinalisasi hanya dapat dihapus oleh Administrator Sekolah.',
            ]);
        }

        $session->delete();

        return redirect()->route('attendance-sessions.index')
            ->with('status', 'Sesi presensi berhasil dihapus.');
    }
}
