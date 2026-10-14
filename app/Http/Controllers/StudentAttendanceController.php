<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    /**
     * Display the authenticated student's attendance summary and history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $records = AttendanceRecord::with([
            'session.teachingAssignment.subject',
            'session.classroom',
            'session.teacher',
        ])
            ->where('student_id', $user->id)
            ->latest('id')
            ->paginate(20);

        // Calculate student's overall statistics
        $allRecords = AttendanceRecord::where('student_id', $user->id)->get();
        $present = $allRecords->where('status', 'present')->count();
        $late = $allRecords->where('status', 'late')->count();
        $sick = $allRecords->where('status', 'sick')->count();
        $permit = $allRecords->where('status', 'permit')->count();
        $absent = $allRecords->where('status', 'absent')->count();
        $total = $allRecords->count();

        $rate = $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0.0;

        return view('attendance.student.index', compact(
            'records',
            'present',
            'late',
            'sick',
            'permit',
            'absent',
            'total',
            'rate'
        ));
    }
}
