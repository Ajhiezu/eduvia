<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonSchedule;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherScheduleController extends Controller
{
    /**
     * Display the logged-in teacher's teaching schedule and homeroom timetable.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id ?? School::current()?->id;

        $viewMode = $request->input('view', 'weekly'); // 'weekly', 'daily', 'homeroom'
        $selectedDay = $request->input('day');

        $activeAcademicYear = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        // Base query strictly scoped to logged-in teacher's teaching assignments
        $schedulesQuery = LessonSchedule::query()
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.classroom.gradeLevel',
                'teachingAssignment.academicYear',
                'teachingAssignment.semester',
            ])
            ->active()
            ->whereHas('teachingAssignment', function ($q) use ($user, $schoolId, $activeAcademicYear) {
                $q->where('teacher_id', $user->id)
                    ->where('is_active', true);

                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }

                if ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                }
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        $allTeacherSchedules = $schedulesQuery->get();
        $schedulesByDay = $allTeacherSchedules->groupBy('day_of_week');

        // Today's schedule & Next upcoming lesson calculation
        $now = Carbon::now();
        $todayIsoDay = $now->dayOfWeekIso; // 1 = Monday, ..., 7 = Sunday
        $todaySchedules = $allTeacherSchedules->where('day_of_week', $todayIsoDay)->values();

        $currentTime = $now->format('H:i:s');
        $ongoingLesson = $todaySchedules->first(fn ($s) => $s->isOngoing($now));
        $nextUpcomingToday = $todaySchedules->first(fn ($s) => $s->isUpcomingToday($now));

        // Homeroom Class Timetable (if user is a homeroom teacher)
        $homeroomClassroom = Classroom::where('homeroom_teacher_id', $user->id)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->with(['gradeLevel', 'academicYear'])
            ->first();

        $homeroomSchedulesByDay = collect();
        if ($homeroomClassroom) {
            $homeroomSchedules = LessonSchedule::query()
                ->with([
                    'teachingAssignment.subject',
                    'teachingAssignment.teacher',
                    'teachingAssignment.classroom',
                ])
                ->active()
                ->whereHas('teachingAssignment', function ($q) use ($homeroomClassroom) {
                    $q->where('classroom_id', $homeroomClassroom->id)
                        ->where('is_active', true);
                })
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            $homeroomSchedulesByDay = $homeroomSchedules->groupBy('day_of_week');
        }

        $days = LessonSchedule::DAYS;
        $activeDailyDay = $selectedDay ? (int) $selectedDay : ($todayIsoDay <= 6 ? $todayIsoDay : 1);

        return view('schedules.teacher', compact(
            'allTeacherSchedules',
            'schedulesByDay',
            'todaySchedules',
            'ongoingLesson',
            'nextUpcomingToday',
            'homeroomClassroom',
            'homeroomSchedulesByDay',
            'viewMode',
            'days',
            'activeDailyDay',
            'todayIsoDay',
            'activeAcademicYear'
        ));
    }
}
