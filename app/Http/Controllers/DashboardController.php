<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AdmissionApplicant;
use App\Models\AdmissionPeriod;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\School;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the authenticated user's role-tailored dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $school = School::current();
        $academicYear = $school?->activeAcademicYear;
        $semester = $academicYear?->activeSemester;

        $viewData = [
            'school' => $school,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'user' => $user,
        ];

        if ($user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN)) {
            $viewData['totalClassrooms'] = Classroom::count();
            $viewData['totalStudents'] = User::where('role', UserRole::STUDENT)->count();
            $viewData['totalTeachers'] = User::where('role', UserRole::TEACHER)->count();
            $viewData['activePpdbPeriod'] = AdmissionPeriod::where('is_active', true)->first();
            $viewData['totalApplicants'] = AdmissionApplicant::count();
            $viewData['verifiedApplicants'] = AdmissionApplicant::whereIn('status', ['verified', 'passed', 'accepted', 'registered'])->count();
            $viewData['recentSessions'] = AttendanceSession::with(['teacher', 'classroom', 'teachingAssignment.subject'])
                ->latest('id')
                ->take(5)
                ->get();
        } elseif ($user->hasRole(UserRole::TEACHER)) {
            $viewData['homeroomClassrooms'] = Classroom::where('homeroom_teacher_id', $user->id)
                ->with(['academicYear', 'gradeLevel'])
                ->get();
            $viewData['teachingAssignments'] = TeachingAssignment::where('teacher_id', $user->id)
                ->with(['classroom', 'subject'])
                ->get();
            $viewData['recentTeacherSessions'] = AttendanceSession::where('teacher_id', $user->id)
                ->with(['classroom', 'teachingAssignment.subject'])
                ->latest('date')
                ->latest('id')
                ->take(5)
                ->get();
            $viewData['totalTeacherSessions'] = AttendanceSession::where('teacher_id', $user->id)->count();
            $viewData['draftTeacherSessions'] = AttendanceSession::where('teacher_id', $user->id)->where('status', 'draft')->count();
            $viewData['finalizedTeacherSessions'] = AttendanceSession::where('teacher_id', $user->id)->where('status', 'finalized')->count();
        } elseif ($user->hasRole(UserRole::STUDENT)) {
            $studentProfile = $user->studentProfile;
            $enrollment = ClassroomStudent::with(['classroom.academicYear', 'classroom.gradeLevel', 'classroom.homeroomTeacher'])
                ->where('student_id', $user->id)
                ->latest('id')
                ->first();

            $attendanceRecords = AttendanceRecord::where('student_id', $user->id)->get();
            $totalAttendance = $attendanceRecords->count();
            $presentCount = $attendanceRecords->where('status', 'present')->count();
            $lateCount = $attendanceRecords->where('status', 'late')->count();
            $sickCount = $attendanceRecords->where('status', 'sick')->count();
            $permitCount = $attendanceRecords->where('status', 'permit')->count();
            $absentCount = $attendanceRecords->where('status', 'absent')->count();
            $attendanceRate = $totalAttendance > 0
                ? round((($presentCount + $lateCount) / $totalAttendance) * 100, 1)
                : 100.0;

            $recentAttendance = AttendanceRecord::with(['session.teachingAssignment.subject', 'session.teacher'])
                ->where('student_id', $user->id)
                ->latest('id')
                ->take(5)
                ->get();

            $viewData['studentProfile'] = $studentProfile;
            $viewData['enrollment'] = $enrollment;
            $viewData['attendanceSummary'] = [
                'total' => $totalAttendance,
                'present' => $presentCount,
                'late' => $lateCount,
                'sick' => $sickCount,
                'permit' => $permitCount,
                'absent' => $absentCount,
                'rate' => $attendanceRate,
            ];
            $viewData['recentAttendance'] = $recentAttendance;
        } elseif ($user->hasRole(UserRole::PARENT)) {
            return redirect()->route('parent.dashboard');
        }

        return view('dashboard', $viewData);
    }
}
