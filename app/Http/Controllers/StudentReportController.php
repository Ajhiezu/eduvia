<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\School;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentReportController extends Controller
{
    /**
     * Display the student demographics and lifecycle report.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        // Status breakdown
        $statusCounts = StudentProfile::query()
            ->selectRaw('student_status, count(*) as count')
            ->groupBy('student_status')
            ->pluck('count', 'student_status')
            ->toArray();

        $activeCount = (int) ($statusCounts['active'] ?? 0);
        $transferredCount = (int) ($statusCounts['transferred'] ?? 0);
        $graduatedCount = (int) ($statusCounts['graduated'] ?? 0);
        $droppedCount = (int) ($statusCounts['dropped'] ?? 0);
        $totalStudents = $activeCount + $transferredCount + $graduatedCount + $droppedCount;

        // Gender breakdown
        $genderCounts = StudentProfile::query()
            ->selectRaw('gender, count(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        $maleCount = (int) ($genderCounts['male'] ?? ($genderCounts['L'] ?? 0));
        $femaleCount = (int) ($genderCounts['female'] ?? ($genderCounts['P'] ?? 0));

        // Distribution by classroom
        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['gradeLevel', 'academicYear'])
            ->withCount(['activeStudents'])
            ->orderBy('name')
            ->get();

        return view('students.report.index', compact(
            'totalStudents',
            'activeCount',
            'transferredCount',
            'graduatedCount',
            'droppedCount',
            'maleCount',
            'femaleCount',
            'classrooms'
        ));
    }
}
