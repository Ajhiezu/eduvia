<?php

namespace App\Http\Controllers;

use App\Models\ClassroomStudent;
use App\Models\LessonSchedule;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScheduleController extends Controller
{
    /**
     * Display the logged-in student's classroom lesson timetable.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id ?? School::current()?->id;

        $viewMode = $request->input('view', 'weekly'); // 'weekly', 'daily', 'list'
        $selectedDay = $request->input('day');

        // Resolve student's active classroom enrollment strictly from server
        $activeEnrollment = ClassroomStudent::query()
            ->where('student_id', $user->id)
            ->active()
            ->with(['classroom.academicYear', 'classroom.gradeLevel', 'classroom.homeroomTeacher'])
            ->latest('id')
            ->first();

        $classroom = $activeEnrollment?->classroom;

        $schedules = collect();
        $schedulesByDay = collect();
        $todaySchedules = collect();
        $ongoingLesson = null;
        $nextUpcomingToday = null;

        $now = Carbon::now();
        $todayIsoDay = $now->dayOfWeekIso; // 1 = Monday, ..., 7 = Sunday

        if ($classroom) {
            $schedules = LessonSchedule::query()
                ->with([
                    'teachingAssignment.subject',
                    'teachingAssignment.teacher',
                    'teachingAssignment.academicYear',
                    'teachingAssignment.semester',
                ])
                ->active()
                ->whereHas('teachingAssignment', function ($q) use ($classroom, $schoolId) {
                    $q->where('classroom_id', $classroom->id)
                        ->where('is_active', true);

                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            $schedulesByDay = $schedules->groupBy('day_of_week');

            // Today's lessons & next upcoming lesson
            $todaySchedules = $schedules->where('day_of_week', $todayIsoDay)->values();
            $ongoingLesson = $todaySchedules->first(fn ($s) => $s->isOngoing($now));
            $nextUpcomingToday = $todaySchedules->first(fn ($s) => $s->isUpcomingToday($now));
        }

        $days = LessonSchedule::DAYS;
        $activeDailyDay = $selectedDay ? (int) $selectedDay : ($todayIsoDay <= 6 ? $todayIsoDay : 1);

        return view('schedules.student', compact(
            'classroom',
            'activeEnrollment',
            'schedules',
            'schedulesByDay',
            'todaySchedules',
            'ongoingLesson',
            'nextUpcomingToday',
            'viewMode',
            'days',
            'activeDailyDay',
            'todayIsoDay'
        ));
    }
}
