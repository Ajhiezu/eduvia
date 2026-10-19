<?php

namespace App\Services\Pilot;

use App\Models\AttendanceSession;
use App\Models\ParentStudentRelation;
use App\Models\Pilot\PilotAggregateMetric;
use App\Models\Pilot\PilotFeedback;
use App\Models\Pilot\PilotSurvey;
use App\Models\School;
use App\Models\User;

class PilotMetricsService
{
    /**
     * Record a discrete aggregate metric entry (Idempotent per school, date, and key).
     */
    public function recordMetric(
        int $schoolId,
        string $metricDate,
        string $key,
        float $value,
        ?array $dimensions = null
    ): PilotAggregateMetric {
        return PilotAggregateMetric::updateOrCreate(
            [
                'school_id' => $schoolId,
                'metric_date' => $metricDate,
                'metric_key' => $key,
            ],
            [
                'metric_value' => $value,
                'dimensions' => $dimensions,
            ]
        );
    }

    /**
     * Generate comprehensive weekly pilot performance report without any PII.
     *
     * @return array<string, mixed>
     */
    public function generateWeeklyReport(int $schoolId, string $weekStart, string $weekEnd): array
    {
        $school = School::findOrFail($schoolId);

        // 1. Teacher Activity Metrics
        $totalTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->count();
        $activeTeachers = AttendanceSession::where('school_id', $schoolId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->distinct('teacher_id')
            ->count('teacher_id');

        $teacherWauPct = $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100, 1) : 0.0;

        // 2. Attendance Completion Metrics
        $sessionsCount = AttendanceSession::where('school_id', $schoolId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->count();

        $finalizedSessions = AttendanceSession::where('school_id', $schoolId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('status', 'finalized')
            ->count();

        $attendanceCompletionPct = $sessionsCount > 0 ? round(($finalizedSessions / $sessionsCount) * 100, 1) : 0.0;

        // 3. Parent Linking Rate
        $totalStudents = User::where('school_id', $schoolId)->where('role', 'student')->count();
        $linkedStudents = ParentStudentRelation::where('school_id', $schoolId)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');

        $parentAdoptionPct = $totalStudents > 0 ? round(($linkedStudents / $totalStudents) * 100, 1) : 0.0;

        // 4. Feedback & Issues Breakdown
        $feedbacks = PilotFeedback::where('school_id', $schoolId)
            ->whereBetween('created_at', [$weekStart.' 00:00:00', $weekEnd.' 23:59:59'])
            ->get();

        $severityBreakdown = [
            'p0' => $feedbacks->where('severity', 'p0')->count(),
            'p1' => $feedbacks->where('severity', 'p1')->count(),
            'p2' => $feedbacks->where('severity', 'p2')->count(),
            'p3' => $feedbacks->where('severity', 'p3')->count(),
            'total' => $feedbacks->count(),
            'resolved' => $feedbacks->where('status', 'resolved')->count(),
        ];

        // 5. Survey Satisfaction Score (CSAT)
        $surveys = PilotSurvey::where('school_id', $schoolId)
            ->whereBetween('created_at', [$weekStart.' 00:00:00', $weekEnd.' 23:59:59'])
            ->get();

        $avgSatisfaction = $surveys->count() > 0 ? round($surveys->avg('satisfaction_score'), 2) : null;
        $avgEaseOfUse = $surveys->count() > 0 ? round($surveys->avg('ease_of_use_score'), 2) : null;

        return [
            'metadata' => [
                'school_id' => $schoolId,
                'school_name' => $school->name,
                'reporting_period' => "{$weekStart} s/d {$weekEnd}",
                'generated_at' => now()->toIso8601String(),
            ],
            'teacher_engagement' => [
                'total_teachers' => $totalTeachers,
                'active_teachers_this_week' => $activeTeachers,
                'teacher_wau_percentage' => $teacherWauPct,
                'status' => $teacherWauPct >= 85.0 ? 'TARGET_MET' : ($teacherWauPct >= 65.0 ? 'NEEDS_ATTENTION' : 'ALERT_LOW'),
            ],
            'attendance_operations' => [
                'total_sessions_created' => $sessionsCount,
                'finalized_sessions' => $finalizedSessions,
                'completion_rate_percentage' => $attendanceCompletionPct,
            ],
            'parent_adoption' => [
                'total_students' => $totalStudents,
                'linked_students' => $linkedStudents,
                'adoption_percentage' => $parentAdoptionPct,
                'status' => $parentAdoptionPct >= 70.0 ? 'TARGET_MET' : ($parentAdoptionPct >= 50.0 ? 'PROGRESSING' : 'BELOW_THRESHOLD'),
            ],
            'issues_and_triages' => $severityBreakdown,
            'user_satisfaction' => [
                'surveys_collected' => $surveys->count(),
                'avg_satisfaction_score' => $avgSatisfaction,
                'avg_ease_of_use_score' => $avgEaseOfUse,
            ],
        ];
    }
}
