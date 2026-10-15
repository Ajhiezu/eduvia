<?php

namespace App\Http\Controllers;

use App\Models\ClassroomStudent;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentExamController extends Controller
{
    /**
     * Resolve the active school ID for current context.
     */
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Get IDs of classrooms the student is actively enrolled in.
     */
    protected function getEnrolledClassroomIds(): array
    {
        return ClassroomStudent::where('student_id', auth()->id())
            ->where('enrollment_status', 'active')
            ->pluck('classroom_id')
            ->toArray();
    }

    /**
     * Check if student is enrolled in any of the exam's target classrooms.
     */
    protected function authorizeStudentForExam(Exam $exam): void
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $exam->school_id !== (int) $schoolId) {
            abort(403, 'Ujian ini tidak terdaftar pada sekolah Anda.');
        }

        $enrolledClassroomIds = $this->getEnrolledClassroomIds();
        $targetClassroomIds = $exam->classrooms()->pluck('classrooms.id')->toArray();

        $isEnrolled = count(array_intersect($enrolledClassroomIds, $targetClassroomIds)) > 0;
        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar sebagai peserta pada kelas ujian ini.');
        }
    }

    /**
     * Display student CBT portal dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $enrolledClassroomIds = $this->getEnrolledClassroomIds();
        $schoolId = $this->currentSchoolId();

        $query = Exam::query()
            ->with(['subject', 'teacher', 'classrooms'])
            ->withCount('questionItems')
            ->whereHas('classrooms', function ($q) use ($enrolledClassroomIds) {
                $q->whereIn('classrooms.id', $enrolledClassroomIds);
            })
            ->when($schoolId, fn ($q) => $q->forSchool($schoolId));

        $filter = $request->input('tab', 'available');

        if ($filter === 'upcoming') {
            $query->where(function ($q) {
                $q->where('status', Exam::STATUS_SCHEDULED)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Exam::STATUS_PUBLISHED)
                            ->where('start_time', '>', now());
                    });
            });
        } elseif ($filter === 'completed') {
            $query->whereHas('attempts', function ($q) use ($user) {
                $q->where('student_id', $user->id)
                    ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED, ExamAttempt::STATUS_EXPIRED]);
            });
        } else {
            // Default: Available / Ongoing
            $query->where('status', Exam::STATUS_PUBLISHED)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now());
        }

        $exams = $query->latest('start_time')->paginate(12)->withQueryString();

        // Load attempts for current student on these exams
        $examIds = $exams->pluck('id')->toArray();
        $myAttempts = ExamAttempt::whereIn('exam_id', $examIds)
            ->where('student_id', $user->id)
            ->get()
            ->groupBy('exam_id');

        return view('cbt.student.index', compact('exams', 'myAttempts', 'filter'));
    }

    /**
     * Show pre-exam briefing and instructions.
     */
    public function show(Exam $exam): View
    {
        $this->authorizeStudentForExam($exam);

        $exam->load(['subject', 'teacher', 'classrooms']);
        $exam->loadCount('questionItems');

        $user = auth()->user();
        $attempts = $exam->attempts()
            ->where('student_id', $user->id)
            ->latest('started_at')
            ->get();

        $activeAttempt = $attempts->firstWhere('status', ExamAttempt::STATUS_IN_PROGRESS);
        if ($activeAttempt && $activeAttempt->isExpired()) {
            $this->finalizeAttempt($activeAttempt);
            $activeAttempt = null;
        }

        $attemptsUsed = $attempts->count();
        $remainingAttempts = max(0, $exam->max_attempts - $attemptsUsed);

        $isAvailable = $exam->isAvailableNow();
        $isScheduled = $exam->isScheduled();
        $isClosed = $exam->isClosed() || now()->isAfter($exam->end_time);

        return view('cbt.student.show', compact(
            'exam',
            'attempts',
            'activeAttempt',
            'attemptsUsed',
            'remainingAttempts',
            'isAvailable',
            'isScheduled',
            'isClosed'
        ));
    }

    /**
     * Start or resume an exam attempt.
     */
    public function start(Exam $exam): RedirectResponse
    {
        $this->authorizeStudentForExam($exam);

        $user = auth()->user();

        // Check if there is already an active in-progress attempt
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->where('status', ExamAttempt::STATUS_IN_PROGRESS)
            ->first();

        if ($existingAttempt) {
            if (! $existingAttempt->isExpired()) {
                return redirect()->route('student.exams.take', [$exam, $existingAttempt]);
            }

            // If expired, finalize it
            $this->finalizeAttempt($existingAttempt);
        }

        // Validate exam timing and status
        if ($exam->status !== Exam::STATUS_PUBLISHED) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Ujian ini belum dipublikasikan atau sudah ditutup.');
        }

        if (now()->isBefore($exam->start_time)) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Ujian belum dimulai. Jadwal mulai: '.$exam->start_time->format('d M Y H:i'));
        }

        if (now()->isAfter($exam->end_time)) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Waktu ujian telah berakhir pada: '.$exam->end_time->format('d M Y H:i'));
        }

        // Check attempts limit
        $attemptsCount = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->count();

        if ($attemptsCount >= $exam->max_attempts) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', "Anda telah mencapai batas maksimal ({$exam->max_attempts} kali) percobaan ujian ini.");
        }

        // Authoritative server-side expiration calculation
        $startedAt = now();
        $plannedExpiresAt = $startedAt->copy()->addMinutes($exam->duration_minutes);
        $expiresAt = $plannedExpiresAt->isAfter($exam->end_time) ? $exam->end_time : $plannedExpiresAt;

        // Build frozen snapshot of questions (no answer keys disclosed)
        $snapshot = $exam->buildFrozenSnapshot();

        if (empty($snapshot)) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Ujian ini belum memiliki daftar soal aktif. Silakan hubungi guru pengampu.');
        }

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'attempt_number' => $attemptsCount + 1,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'questions_snapshot' => $snapshot,
            'status' => ExamAttempt::STATUS_IN_PROGRESS,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
        ]);

        return redirect()->route('student.exams.take', [$exam, $attempt]);
    }

    /**
     * Interactive exam-taking interface.
     */
    public function take(Exam $exam, ExamAttempt $attempt): View|RedirectResponse
    {
        if ((int) $attempt->student_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak: Percobaan ujian ini bukan milik Anda.');
        }

        if ((int) $attempt->exam_id !== (int) $exam->id) {
            abort(404, 'Percobaan tidak sesuai dengan ujian.');
        }

        // If attempt is already finished, redirect to result
        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('student.exams.result', [$exam, $attempt]);
        }

        // Authoritative expiration check
        if ($attempt->isExpired()) {
            $this->finalizeAttempt($attempt);

            return redirect()->route('student.exams.result', [$exam, $attempt])
                ->with('info', 'Waktu ujian telah berakhir. Jawaban Anda telah otomatis dikumpulkan oleh sistem.');
        }

        // Questions from frozen snapshot
        $questions = $attempt->questions_snapshot ?? [];

        // Preload student's saved answers
        $savedAnswers = $attempt->answers()->get()->keyBy('question_id');

        $remainingSeconds = $attempt->remainingSeconds();

        return view('cbt.student.take', compact('exam', 'attempt', 'questions', 'savedAnswers', 'remainingSeconds'));
    }

    /**
     * Autosave endpoint for student answer (AJAX).
     */
    public function saveAnswer(Request $request, Exam $exam, ExamAttempt $attempt): JsonResponse
    {
        if ((int) $attempt->student_id !== (int) auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized attempt owner.'], 403);
        }

        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return response()->json(['status' => 'error', 'message' => 'Ujian sudah dikumpulkan atau tidak aktif.'], 422);
        }

        if ($attempt->isExpired()) {
            $this->finalizeAttempt($attempt);

            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu pengerjaan telah habis. Ujian otomatis dikumpulkan.',
                'redirect_url' => route('student.exams.result', [$exam, $attempt]),
            ], 422);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['nullable'],
            'is_marked_for_review' => ['nullable', 'boolean'],
        ]);

        $questionId = (int) $validated['question_id'];
        $rawAnswer = $validated['answer'] ?? null;
        $isMarked = (bool) ($validated['is_marked_for_review'] ?? false);

        // Verify question exists in snapshot
        $snapshot = collect($attempt->questions_snapshot ?? []);
        $questionInSnapshot = $snapshot->firstWhere('id', $questionId);

        if (! $questionInSnapshot) {
            return response()->json(['status' => 'error', 'message' => 'Soal tidak ditemukan pada berkas ujian.'], 404);
        }

        // Format student answer
        $studentAnswer = is_array($rawAnswer) ? $rawAnswer : ($rawAnswer !== null ? trim((string) $rawAnswer) : null);

        $answer = ExamAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'answer' => $studentAnswer,
                'is_marked_for_review' => $isMarked,
                'saved_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'saved_at' => now()->format('H:i:s'),
            'remaining_seconds' => $attempt->remainingSeconds(),
            'is_marked_for_review' => $answer->is_marked_for_review,
        ]);
    }

    /**
     * Submit and finalize exam attempt.
     */
    public function submit(Request $request, Exam $exam, ExamAttempt $attempt): RedirectResponse
    {
        if ((int) $attempt->student_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak: Percobaan ujian ini bukan milik Anda.');
        }

        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('student.exams.result', [$exam, $attempt])
                ->with('info', 'Ujian telah dikumpulkan sebelumnya.');
        }

        $this->finalizeAttempt($attempt);

        return redirect()->route('student.exams.result', [$exam, $attempt])
            ->with('success', 'Ujian CBT berhasil dikumpulkan. Terima kasih atas partisipasi Anda!');
    }

    /**
     * Display exam results & discussion (subject to exam review policy).
     */
    public function result(Exam $exam, ExamAttempt $attempt): View
    {
        if ((int) $attempt->student_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak: Percobaan ujian ini bukan milik Anda.');
        }

        $exam->load(['subject', 'teacher']);
        $attempt->load(['answers.question']);

        $canViewExplanation = $exam->canStudentViewExplanation();
        $canViewScore = $exam->show_result_immediately || $attempt->status === ExamAttempt::STATUS_GRADED;

        $answersByQuestionId = $attempt->answers->keyBy('question_id');
        $questionsSnapshot = $attempt->questions_snapshot ?? [];

        return view('cbt.student.result', compact(
            'exam',
            'attempt',
            'canViewExplanation',
            'canViewScore',
            'answersByQuestionId',
            'questionsSnapshot'
        ));
    }

    /**
     * Authoritatively finalize an attempt, auto-grading objective questions.
     */
    protected function finalizeAttempt(ExamAttempt $attempt): void
    {
        if ($attempt->status !== ExamAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        DB::transaction(function () use ($attempt) {
            $exam = $attempt->exam;
            $attempt->submitted_at = now();

            $items = $exam->questionItems()->with('question')->get()->keyBy('question_id');
            $existingAnswers = $attempt->answers()->get()->keyBy('question_id');

            foreach ($items as $questionId => $item) {
                $question = $item->question;
                if (! $question) {
                    continue;
                }

                $answer = $existingAnswers->get($questionId);
                if (! $answer) {
                    $answer = new ExamAnswer([
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'answer' => null,
                        'saved_at' => now(),
                    ]);
                }

                if ($question->isObjective()) {
                    $isCorrect = $question->checkAnswer($answer->answer);
                    $answer->is_correct = $isCorrect;
                    $answer->score = $isCorrect ? (float) $item->score : 0.0;
                    $answer->save();
                } else {
                    if (! $answer->exists) {
                        $answer->save();
                    }
                }
            }

            // If exam has essays, attempt status is SUBMITTED until teacher grades; else GRADED
            $hasEssay = $items->contains(fn ($i) => $i->question?->isEssay());
            $attempt->status = $hasEssay ? ExamAttempt::STATUS_SUBMITTED : ExamAttempt::STATUS_GRADED;

            $attempt->calculateFinalGrade();
        });
    }
}
