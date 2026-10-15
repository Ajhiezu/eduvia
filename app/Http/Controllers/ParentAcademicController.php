<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\ExamAttempt;
use App\Models\StudentReportCard;
use App\Services\ParentPortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentAcademicController extends Controller
{
    public function __construct(
        protected ParentPortalService $parentPortalService
    ) {}

    /**
     * Display detailed attendance history for active child.
     */
    public function attendance(Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $activeChild = $this->parentPortalService->getActiveChild($parent);

        $records = collect();
        $summary = null;

        if ($activeChild) {
            $summary = $this->parentPortalService->getAttendanceSummary($activeChild);

            $query = AttendanceRecord::with(['session.teachingAssignment.subject', 'session.teacher', 'session.classroom'])
                ->where('student_id', $activeChild->id)
                ->latest('id');

            if ($request->filled('status')) {
                $query->where('status', $request->query('status'));
            }

            if ($request->filled('start_date')) {
                $query->whereHas('session', fn ($q) => $q->whereDate('date', '>=', $request->query('start_date')));
            }

            if ($request->filled('end_date')) {
                $query->whereHas('session', fn ($q) => $q->whereDate('date', '<=', $request->query('end_date')));
            }

            $records = $query->paginate(15)->withQueryString();
        }

        return view('parent.academic.attendance', compact(
            'activeChildren',
            'activeChild',
            'summary',
            'records'
        ));
    }

    /**
     * Display assignments and submission statuses for active child.
     */
    public function assignments(Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $activeChild = $this->parentPortalService->getActiveChild($parent);

        $assignments = collect();
        $enrollment = null;

        if ($activeChild) {
            $enrollment = $this->parentPortalService->getActiveEnrollment($activeChild);

            if ($enrollment) {
                $query = Assignment::query()
                    ->where('classroom_id', $enrollment->classroom_id)
                    ->where('status', 'published')
                    ->with(['subject', 'teacher', 'submissions' => fn ($q) => $q->where('student_id', $activeChild->id)])
                    ->latest('due_date');

                if ($request->filled('subject_id')) {
                    $query->where('subject_id', $request->query('subject_id'));
                }

                $assignments = $query->paginate(15)->withQueryString();
            }
        }

        return view('parent.academic.assignments', compact(
            'activeChildren',
            'activeChild',
            'enrollment',
            'assignments'
        ));
    }

    /**
     * Display published grades and CBT exam results for active child.
     */
    public function grades(Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $activeChild = $this->parentPortalService->getActiveChild($parent);

        $examAttempts = collect();
        $assignmentGrades = collect();

        if ($activeChild) {
            $examAttempts = ExamAttempt::query()
                ->where('student_id', $activeChild->id)
                ->whereIn('status', ['submitted', 'graded'])
                ->whereHas('exam', fn ($q) => $q->whereIn('status', ['published', 'closed']))
                ->with(['exam.subject', 'exam.teacher', 'exam.academicYear'])
                ->latest('submitted_at')
                ->paginate(10, ['*'], 'exams_page');

            $enrollment = $this->parentPortalService->getActiveEnrollment($activeChild);
            if ($enrollment) {
                $assignmentGrades = Assignment::query()
                    ->where('classroom_id', $enrollment->classroom_id)
                    ->where('status', 'published')
                    ->whereHas('submissions', fn ($q) => $q->where('student_id', $activeChild->id)->whereNotNull('score'))
                    ->with(['subject', 'teacher', 'submissions' => fn ($q) => $q->where('student_id', $activeChild->id)])
                    ->latest('due_date')
                    ->paginate(10, ['*'], 'assignments_page');
            }
        }

        return view('parent.academic.grades', compact(
            'activeChildren',
            'activeChild',
            'examAttempts',
            'assignmentGrades'
        ));
    }

    /**
     * Display list of published report cards for active child.
     */
    public function reports(Request $request): View
    {
        $parent = $request->user();
        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $activeChild = $this->parentPortalService->getActiveChild($parent);

        $reportCards = collect();

        if ($activeChild) {
            $reportCards = $this->parentPortalService->getPublishedReportCards($activeChild);
        }

        return view('parent.academic.reports', compact(
            'activeChildren',
            'activeChild',
            'reportCards'
        ));
    }

    /**
     * Display detailed published report card for active child.
     */
    public function reportDetail(Request $request, StudentReportCard $reportCard): View
    {
        $parent = $request->user();

        // Security check: Must belong to a child that this parent has active relation with
        $childIds = $parent->activeChildren()->pluck('users.id')->all();
        if (! in_array((int) $reportCard->student_id, $childIds, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk melihat buku rapor siswa ini.');
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
            'student.studentProfile',
        ]);

        return view('parent.academic.report-detail', compact('reportCard'));
    }
}
