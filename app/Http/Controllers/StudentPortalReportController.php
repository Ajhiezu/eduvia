<?php

namespace App\Http\Controllers;

use App\Models\StudentReportCard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalReportController extends Controller
{
    /**
     * Display a listing of published report cards for the authenticated student.
     */
    public function index(Request $request): View
    {
        $student = auth()->user();

        $reportCards = StudentReportCard::query()
            ->where('student_id', $student->id)
            ->where('status', StudentReportCard::STATUS_PUBLISHED)
            ->with(['academicYear', 'semester', 'classroom'])
            ->latest('published_at')
            ->get();

        return view('students.reports.index', compact('reportCards'));
    }

    /**
     * Display the specified published report card for the student.
     */
    public function show(StudentReportCard $reportCard): View
    {
        $student = auth()->user();

        // Security check: Must belong to this student
        if ((int) $reportCard->student_id !== (int) $student->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses buku rapor milik Anda sendiri.');
        }

        // Security check: Must be published
        if (! $reportCard->isPublished()) {
            abort(404, 'Buku rapor untuk periode ini belum resmi diterbitkan.');
        }

        $reportCard->load([
            'school',
            'classroom.gradeLevel',
            'academicYear',
            'semester',
            'homeroomTeacher.staffProfile',
            'items',
        ]);

        return view('students.reports.show', compact('reportCard'));
    }
}
