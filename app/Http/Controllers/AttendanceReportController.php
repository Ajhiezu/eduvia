<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\School;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    /**
     * Display attendance report and summary matrix.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

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

        $assignmentId = $request->input('teaching_assignment_id');
        $selectedAssignment = null;
        $sessions = collect();
        $studentsSummary = collect();
        $classAverageRate = 0.0;

        if ($assignmentId) {
            $selectedAssignment = TeachingAssignment::with(['classroom.gradeLevel', 'subject', 'teacher', 'academicYear'])
                ->findOrFail($assignmentId);

            // Authorization check
            if ($user->hasRole(UserRole::TEACHER) && ! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
                if ($selectedAssignment->teacher_id !== $user->id) {
                    abort(403);
                }
            }

            // Get all sessions for this assignment ordered by meeting number
            $sessions = AttendanceSession::where('teaching_assignment_id', $selectedAssignment->id)
                ->orderBy('meeting_number')
                ->orderBy('date')
                ->get();

            // Get all active students enrolled in this classroom
            $enrolledStudents = $selectedAssignment->classroom->activeStudents()
                ->with('student')
                ->get()
                ->map(fn ($enrollment) => $enrollment->student);

            $sessionIds = $sessions->pluck('id');

            // Preload all attendance records for these sessions
            $records = AttendanceRecord::whereIn('attendance_session_id', $sessionIds)
                ->get()
                ->groupBy('student_id');

            $totalClassRateSum = 0;
            $studentCount = 0;

            foreach ($enrolledStudents as $student) {
                $studentRecords = $records->get($student->id, collect());
                $present = $studentRecords->where('status', 'present')->count();
                $late = $studentRecords->where('status', 'late')->count();
                $sick = $studentRecords->where('status', 'sick')->count();
                $permit = $studentRecords->where('status', 'permit')->count();
                $absent = $studentRecords->where('status', 'absent')->count();
                $totalSessions = $sessions->count();

                $rate = $totalSessions > 0 ? round((($present + $late) / $totalSessions) * 100, 1) : 0.0;

                $totalClassRateSum += $rate;
                $studentCount++;

                $studentsSummary->push([
                    'student' => $student,
                    'present' => $present,
                    'late' => $late,
                    'sick' => $sick,
                    'permit' => $permit,
                    'absent' => $absent,
                    'total' => $totalSessions,
                    'rate' => $rate,
                ]);
            }

            $classAverageRate = $studentCount > 0 ? round($totalClassRateSum / $studentCount, 1) : 0.0;
        }

        return view('attendance.report.index', compact(
            'classrooms',
            'assignments',
            'assignmentId',
            'selectedAssignment',
            'sessions',
            'studentsSummary',
            'classAverageRate'
        ));
    }
}
