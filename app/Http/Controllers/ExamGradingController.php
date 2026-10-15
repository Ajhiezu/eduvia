<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamGradingController extends Controller
{
    /**
     * Resolve the active school ID for current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Ensure the exam belongs to the current school tenant.
     */
    protected function authorizeTenant(Exam $exam): void
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $exam->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Ujian ini tidak terdaftar pada sekolah Anda.');
        }
    }

    /**
     * Display a listing of attempts for the given exam.
     */
    public function index(Request $request, Exam $exam): View
    {
        $this->authorizeTenant($exam);

        $classroomId = $request->input('classroom_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $attemptsQuery = $exam->attempts()
            ->with(['student.classroom', 'student.studentProfile', 'answers'])
            ->latest('started_at');

        if ($classroomId) {
            $attemptsQuery->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', (int) $classroomId);
            });
        }

        if ($status) {
            $attemptsQuery->where('status', $status);
        }

        if ($search) {
            $attemptsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $attempts = $attemptsQuery->paginate(20)->withQueryString();

        $classrooms = $exam->classrooms;

        $stats = [
            'total_attempts' => $exam->attempts()->count(),
            'submitted' => $exam->attempts()->where('status', ExamAttempt::STATUS_SUBMITTED)->count(),
            'graded' => $exam->attempts()->where('status', ExamAttempt::STATUS_GRADED)->count(),
            'in_progress' => $exam->attempts()->where('status', ExamAttempt::STATUS_IN_PROGRESS)->count(),
            'average_score' => round((float) $exam->attempts()->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED])->avg('total_score'), 1),
            'highest_score' => (float) $exam->attempts()->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED])->max('total_score'),
            'lowest_score' => (float) $exam->attempts()->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED])->min('total_score'),
        ];

        return view('cbt.exams.grading.index', compact('exam', 'attempts', 'classrooms', 'stats'));
    }

    /**
     * Show the detailed grading page for a student attempt.
     */
    public function show(Exam $exam, ExamAttempt $attempt): View
    {
        $this->authorizeTenant($exam);

        if ((int) $attempt->exam_id !== (int) $exam->id) {
            abort(404, 'Percobaan ujian tidak valid untuk ujian ini.');
        }

        $attempt->load(['student.classroom', 'answers.question']);

        // Indexed answers by question_id for rapid lookup
        $answersByQuestionId = $attempt->answers->keyBy('question_id');

        // Questions from frozen snapshot or live relations
        $questionsSnapshot = $attempt->questions_snapshot ?? [];

        return view('cbt.exams.grading.show', compact('exam', 'attempt', 'answersByQuestionId', 'questionsSnapshot'));
    }

    /**
     * Grade an attempt manually (save scores and feedback for essay/subjective questions).
     */
    public function grade(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorizeTenant($exam);

        if ((int) $attempt->exam_id !== (int) $exam->id) {
            abort(404, 'Percobaan ujian tidak valid untuk ujian ini.');
        }

        $scores = $request->input('scores', []);
        $feedbacks = $request->input('feedbacks', []);

        // Load items with max scores to validate against
        $examQuestionItems = $exam->questionItems()->get()->keyBy('question_id');
        $graderId = auth()->id();
        $now = now();

        DB::transaction(function () use ($attempt, $scores, $feedbacks, $examQuestionItems, $graderId, $now) {
            foreach ($scores as $questionId => $score) {
                $questionId = (int) $questionId;
                $item = $examQuestionItems->get($questionId);
                $maxScore = (float) ($item?->score ?? 10.0);

                $numericScore = max(0, min((float) $score, $maxScore));
                $feedback = $feedbacks[$questionId] ?? null;

                $answer = ExamAnswer::firstOrNew([
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                ]);

                $answer->score = $numericScore;
                $answer->is_correct = ($numericScore >= $maxScore && $maxScore > 0);
                $answer->feedback = $feedback;
                $answer->graded_by = $graderId;
                $answer->graded_at = $now;
                $answer->save();
            }

            // Recalculate total grade and update attempt status
            $attempt->calculateFinalGrade();
        });

        return redirect()
            ->route('exams.grading.index', $exam)
            ->with('success', "Penilaian untuk peserta {$attempt->student->name} berhasil disimpan.");
    }
}
