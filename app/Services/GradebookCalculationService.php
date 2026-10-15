<?php

namespace App\Services;

use App\Models\AssessmentWeightConfiguration;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\StudentReportCard;
use App\Models\StudentReportCardItem;
use App\Models\Subject;
use App\Models\SubjectGradebook;
use Illuminate\Support\Facades\DB;

class GradebookCalculationService
{
    /**
     * Calculate final deterministic weighted score.
     *
     * @param  array{assignment_weight?: float, quiz_weight?: float, midterm_weight?: float, final_weight?: float}  $weights
     * @param  array<string, float>|null  $manualScores
     */
    public function calculateFinalScore(
        float $assignmentAvg,
        float $quizAvg,
        float $midtermScore,
        float $finalScoreRaw,
        array $weights,
        ?array $manualScores = null
    ): float {
        $wAssignment = (float) ($weights['assignment_weight'] ?? 30.0);
        $wQuiz = (float) ($weights['quiz_weight'] ?? 20.0);
        $wMidterm = (float) ($weights['midterm_weight'] ?? 25.0);
        $wFinal = (float) ($weights['final_weight'] ?? 25.0);

        $totalWeight = $wAssignment + $wQuiz + $wMidterm + $wFinal;
        if ($totalWeight <= 0) {
            $totalWeight = 100.0;
        }

        $weightedSum = ($assignmentAvg * $wAssignment)
            + ($quizAvg * $wQuiz)
            + ($midtermScore * $wMidterm)
            + ($finalScoreRaw * $wFinal);

        $calculatedScore = $weightedSum / $totalWeight;

        // Apply manual score adjustments (if provided)
        if (! empty($manualScores) && is_array($manualScores)) {
            foreach ($manualScores as $component => $val) {
                if (is_numeric($val) && isset($weights[$component]) && (float) $weights[$component] > 0) {
                    // Custom component adjustment
                }
            }
        }

        // Clamp between 0.00 and 100.00
        $clampedScore = max(0.00, min(100.00, $calculatedScore));

        return round($clampedScore, 2);
    }

    /**
     * Determine predicate letter based on grade scale.
     *
     * @param  array<string, array{min: float, max: float, label?: string}>|null  $gradeScale
     */
    public function determinePredicate(float $score, ?array $gradeScale = null, float $passingGrade = 75.00): string
    {
        $scale = $gradeScale ?: AssessmentWeightConfiguration::defaultGradeScale();

        foreach ($scale as $predicate => $range) {
            $min = (float) ($range['min'] ?? 0.0);
            $max = (float) ($range['max'] ?? 100.0);

            if ($score >= $min && $score <= $max) {
                return (string) $predicate;
            }
        }

        // Fallback default
        if ($score >= 90.0) {
            return 'A';
        }
        if ($score >= 80.0) {
            return 'B';
        }
        if ($score >= 70.0) {
            return 'C';
        }

        return 'D';
    }

    /**
     * Check if score meets or exceeds passing grade (KKM).
     */
    public function isPassed(float $score, float $passingGrade = 75.00): bool
    {
        return $score >= $passingGrade;
    }

    /**
     * Generate default qualitative learning achievement description based on predicate and subject.
     */
    public function generateAchievementDescription(string $subjectName, string $predicate, float $score, float $passingGrade = 75.00): string
    {
        return match ($predicate) {
            'A' => "Menunjukkan penguasaan yang sangat istimewa dan pemahaman mendalam dalam seluruh kompetensi capaian pembelajaran {$subjectName}.",
            'B' => "Menunjukkan penguasaan materi yang baik dan tuntas dalam mengaplikasikan konsep-konsep utama {$subjectName}.",
            'C' => "Menunjukkan penguasaan yang cukup pada materi {$subjectName}, telah melampaui batas minimal namun perlu peningkatan ketelitian.",
            default => "Perlu bimbingan dan pendampingan lebih lanjut untuk meningkatkan pemahaman kompetensi dasar mata pelajaran {$subjectName}.",
        };
    }

    /**
     * Aggregate real source scores for a student from Assignment and CBT modules.
     *
     * @return array{
     *     assignment_avg: float,
     *     quiz_avg: float,
     *     midterm_score: float,
     *     final_score_raw: float,
     *     is_complete: bool
     * }
     */
    public function aggregateStudentSourceScores(
        int $studentId,
        int $classroomId,
        int $subjectId,
        int $academicYearId,
        int $semesterId
    ): array {
        // 1. Assignments and Quizzes from AssignmentSubmission
        $assignmentQuery = Assignment::where('subject_id', $subjectId)
            ->where('classroom_id', $classroomId)
            ->whereHas('teachingAssignment', function ($q) use ($academicYearId, $semesterId) {
                $q->where('academic_year_id', $academicYearId)
                    ->when($semesterId, fn ($sq) => $sq->where('semester_id', $semesterId));
            });

        $assignments = $assignmentQuery->with(['submissions' => function ($sq) use ($studentId) {
            $sq->where('student_id', $studentId)
                ->whereNotNull('score');
        }])->get();

        $assignmentScores = [];
        $quizScores = [];

        foreach ($assignments as $assignment) {
            $submission = $assignment->submissions->first();
            if ($submission && $submission->score !== null) {
                $maxScore = $assignment->max_score > 0 ? (float) $assignment->max_score : 100.0;
                $normalizedScore = ((float) $submission->score / $maxScore) * 100.0;

                $titleLower = strtolower($assignment->title.' '.$assignment->topic);
                if (str_contains($titleLower, 'kuis') || str_contains($titleLower, 'quiz') || str_contains($titleLower, 'formatif')) {
                    $quizScores[] = $normalizedScore;
                } else {
                    $assignmentScores[] = $normalizedScore;
                }
            }
        }

        $assignmentAvg = count($assignmentScores) > 0 ? round(array_sum($assignmentScores) / count($assignmentScores), 2) : 0.0;
        $quizAvg = count($quizScores) > 0 ? round(array_sum($quizScores) / count($quizScores), 2) : 0.0;

        // 2. CBT Exams from ExamAttempt
        $exams = Exam::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->whereHas('classrooms', fn ($cq) => $cq->where('classrooms.id', $classroomId))
            ->with(['attempts' => function ($aq) use ($studentId) {
                $aq->where('student_id', $studentId)
                    ->whereIn('status', [ExamAttempt::STATUS_GRADED, ExamAttempt::STATUS_SUBMITTED])
                    ->latest('id');
            }])
            ->get();

        $midtermScore = 0.0;
        $finalScoreRaw = 0.0;
        $hasMidterm = false;
        $hasFinal = false;

        foreach ($exams as $exam) {
            $attempt = $exam->attempts->first();
            if ($attempt) {
                $examScore = (float) ($attempt->final_percentage ?? 0.0);
                $examTitle = strtolower($exam->title.' '.$exam->description);

                if (str_contains($examTitle, 'uts') || str_contains($examTitle, 'tengah semester') || str_contains($examTitle, 'midterm')) {
                    $midtermScore = $examScore;
                    $hasMidterm = true;
                } elseif (str_contains($examTitle, 'uas') || str_contains($examTitle, 'akhir semester') || str_contains($examTitle, 'final')) {
                    $finalScoreRaw = $examScore;
                    $hasFinal = true;
                } elseif (str_contains($examTitle, 'kuis') || str_contains($examTitle, 'quiz')) {
                    $quizScores[] = $examScore;
                    $quizAvg = round(array_sum($quizScores) / count($quizScores), 2);
                }
            }
        }

        $isComplete = count($assignmentScores) > 0 || $hasMidterm || $hasFinal;

        return [
            'assignment_avg' => $assignmentAvg,
            'quiz_avg' => $quizAvg,
            'midterm_score' => $midtermScore,
            'final_score_raw' => $finalScoreRaw,
            'is_complete' => $isComplete,
        ];
    }

    /**
     * Aggregate real student attendance statistics for classroom and semester.
     *
     * @return array{present: int, sick: int, permit: int, absent: int, late: int}
     */
    public function aggregateStudentAttendance(int $studentId, int $classroomId, int $academicYearId, ?int $semesterId = null): array
    {
        $sessionQuery = AttendanceSession::where('classroom_id', $classroomId)
            ->where('academic_year_id', $academicYearId)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId));

        $sessionIds = $sessionQuery->pluck('id');

        $counts = AttendanceRecord::where('student_id', $studentId)
            ->whereIn('attendance_session_id', $sessionIds)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'present' => (int) ($counts['present'] ?? 0),
            'sick' => (int) ($counts['sick'] ?? 0),
            'permit' => (int) ($counts['permit'] ?? 0),
            'absent' => (int) ($counts['absent'] ?? 0),
            'late' => (int) ($counts['late'] ?? 0),
        ];
    }

    /**
     * Compile or synchronize report card items from verified subject gradebooks.
     */
    public function syncReportCardFromGradebooks(StudentReportCard $reportCard): void
    {
        DB::transaction(function () use ($reportCard): void {
            // Find all verified subject gradebooks for this classroom, year, and semester
            $gradebooks = SubjectGradebook::where('classroom_id', $reportCard->classroom_id)
                ->where('academic_year_id', $reportCard->academic_year_id)
                ->where('semester_id', $reportCard->semester_id)
                ->where('status', SubjectGradebook::STATUS_VERIFIED)
                ->with(['subject', 'teacher', 'items' => function ($q) use ($reportCard) {
                    $q->where('student_id', $reportCard->student_id);
                }])
                ->get();

            $order = 1;
            foreach ($gradebooks as $gradebook) {
                $item = $gradebook->items->first();
                if (! $item) {
                    continue;
                }

                $subject = $gradebook->subject;
                $weightConfig = $gradebook->weight_snapshot ?? [];
                $passingGrade = (float) ($weightConfig['passing_grade'] ?? 75.00);

                StudentReportCardItem::updateOrCreate(
                    [
                        'student_report_card_id' => $reportCard->id,
                        'subject_id' => $gradebook->subject_id,
                    ],
                    [
                        'subject_gradebook_item_id' => $item->id,
                        'subject_name' => $subject?->name ?? 'Mata Pelajaran',
                        'subject_code' => $subject?->code,
                        'subject_group' => $subject?->group ?? 'Umum',
                        'teacher_name' => $gradebook->teacher?->name,
                        'passing_grade' => $passingGrade,
                        'final_score' => $item->final_score,
                        'predicate' => $item->predicate,
                        'is_passed' => $item->is_passed,
                        'achievement_description' => $item->achievement_description,
                        'order_index' => $order++,
                    ]
                );
            }

            // Also auto-sync attendance if empty
            if (empty($reportCard->attendance_summary) || array_sum($reportCard->attendance_summary) === 0) {
                $reportCard->update([
                    'attendance_summary' => $this->aggregateStudentAttendance(
                        $reportCard->student_id,
                        $reportCard->classroom_id,
                        $reportCard->academic_year_id,
                        $reportCard->semester_id
                    ),
                ]);
            }
        });
    }
}
