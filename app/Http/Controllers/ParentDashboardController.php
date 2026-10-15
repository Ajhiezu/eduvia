<?php

namespace App\Http\Controllers;

use App\Models\ParentTeacherConversation;
use App\Models\School;
use App\Services\ParentPortalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function __construct(
        protected ParentPortalService $parentPortalService
    ) {}

    /**
     * Display the parent portal dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $parent = $request->user();
        $school = $parent->school ?? School::current();

        $activeChildren = $this->parentPortalService->getActiveChildren($parent);
        $requestedChildId = $request->filled('child_id') ? (int) $request->query('child_id') : null;
        $activeChild = $this->parentPortalService->getActiveChild($parent, $requestedChildId);

        $academicYear = $school?->activeAcademicYear;
        $semester = $academicYear?->activeSemester;

        $enrollment = null;
        $attendanceSummary = null;
        $assignments = collect();
        $examResults = collect();
        $reportCards = collect();
        $announcements = collect();
        $recentConversations = collect();
        $todaySchedules = collect();

        if ($activeChild) {
            $enrollment = $this->parentPortalService->getActiveEnrollment($activeChild);
            $attendanceSummary = $this->parentPortalService->getAttendanceSummary($activeChild);
            $assignments = $this->parentPortalService->getStudentAssignments($activeChild, 5);
            $examResults = $this->parentPortalService->getStudentExamResults($activeChild, 5);
            $reportCards = $this->parentPortalService->getPublishedReportCards($activeChild);
            $announcements = $this->parentPortalService->getAnnouncementsForChild($activeChild, 5);

            // Recent conversations for active child
            $recentConversations = ParentTeacherConversation::query()
                ->where('parent_id', $parent->id)
                ->where('student_id', $activeChild->id)
                ->with(['teacher', 'latestMessage'])
                ->latest('last_message_at')
                ->take(3)
                ->get();

            // Schedules for today
            $dayOfWeek = Carbon::now()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $allSchedules = $this->parentPortalService->getLessonSchedules($activeChild);
            $todaySchedules = $allSchedules->where('day_of_week', $dayOfWeek);
        }

        return view('parent.dashboard', compact(
            'parent',
            'school',
            'academicYear',
            'semester',
            'activeChildren',
            'activeChild',
            'enrollment',
            'attendanceSummary',
            'assignments',
            'examResults',
            'reportCards',
            'announcements',
            'recentConversations',
            'todaySchedules'
        ));
    }

    /**
     * Switch the active child in session.
     */
    public function switchChild(Request $request, mixed $child): RedirectResponse
    {
        $parent = $request->user();
        $childId = is_object($child) && isset($child->id) ? (int) $child->id : (int) $child;
        $this->parentPortalService->getActiveChild($parent, $childId);

        return redirect()->route('parent.dashboard')
            ->with('success', 'Fokus pantauan berhasil dialihkan.');
    }
}
