<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AiFeatureType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateEssayFeedbackRequest;
use App\Http\Requests\AI\GenerateLessonPlanRequest;
use App\Http\Requests\AI\GenerateQuestionSetRequest;
use App\Http\Requests\AI\GenerateRubricRequest;
use App\Http\Requests\AI\UpdateAiDraftRequest;
use App\Models\AiGenerationDraft;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Services\AI\AiQuotaManager;
use App\Services\AI\TeacherAiService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAiAssistantController extends Controller
{
    public function __construct(
        protected TeacherAiService $aiService,
        protected AiQuotaManager $quotaManager
    ) {}

    /**
     * Dashboard overview of AI Teacher Assistant.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);

        $featureFilter = $request->query('feature');
        $statusFilter = $request->query('status', 'draft');

        $query = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->latest();

        if ($featureFilter) {
            $query->where('feature_type', $featureFilter);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $drafts = $query->paginate(12)->withQueryString();

        return view('teacher.ai.index', compact('quota', 'drafts', 'featureFilter', 'statusFilter'));
    }

    /**
     * 1. View Lesson Plan Generator.
     */
    public function lessonPlan(Request $request): View
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('order')->get();

        $recentDrafts = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->where('feature_type', AiFeatureType::LESSON_PLAN)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.ai.lesson-plan', compact('quota', 'subjects', 'gradeLevels', 'recentDrafts'));
    }

    /**
     * Generate Lesson Plan action.
     */
    public function storeLessonPlan(GenerateLessonPlanRequest $request): RedirectResponse
    {
        try {
            $draft = $this->aiService->generateLessonPlan(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('teacher.ai.drafts.show', $draft)
                ->with('success', 'Draf Modul Ajar berhasil disusun oleh AI. Harap tinjau dan sesuaikan sebelum disetujui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 2. View Question Set Generator.
     */
    public function questions(Request $request): View
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('order')->get();

        $recentDrafts = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->where('feature_type', AiFeatureType::QUESTION_SET)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.ai.question-generator', compact('quota', 'subjects', 'gradeLevels', 'recentDrafts'));
    }

    /**
     * Generate Question Set action.
     */
    public function storeQuestions(GenerateQuestionSetRequest $request): RedirectResponse
    {
        try {
            $draft = $this->aiService->generateQuestionSet(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('teacher.ai.drafts.show', $draft)
                ->with('success', 'Draf Paket Soal berhasil disusun oleh AI. Periksa kunci jawaban dan pembahasan sebelum diimpor ke CBT.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 3. View Rubric Generator.
     */
    public function rubrics(Request $request): View
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();

        $recentDrafts = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->where('feature_type', AiFeatureType::GRADING_RUBRIC)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.ai.rubric-generator', compact('quota', 'subjects', 'recentDrafts'));
    }

    /**
     * Generate Rubric action.
     */
    public function storeRubric(GenerateRubricRequest $request): RedirectResponse
    {
        try {
            $draft = $this->aiService->generateGradingRubric(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('teacher.ai.drafts.show', $draft)
                ->with('success', 'Draf Rubrik Penilaian berhasil disusun. Silakan sesuaikan kriteria sesuai karakteristik kelas Anda.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. View Essay Feedback Assistant.
     */
    public function essayFeedback(Request $request): View
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);

        $recentDrafts = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->where('feature_type', AiFeatureType::ESSAY_FEEDBACK)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.ai.essay-feedback', compact('quota', 'recentDrafts'));
    }

    /**
     * Generate Essay Feedback action.
     */
    public function storeEssayFeedback(GenerateEssayFeedbackRequest $request): RedirectResponse
    {
        try {
            $draft = $this->aiService->generateEssayFeedback(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('teacher.ai.drafts.show', $draft)
                ->with('success', 'Saran umpan balik esai berhasil dianalisis. Keputusan nilai dan catatan akhir sepenuhnya berada di tangan Anda.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show draft detail for teacher review, edit, accept, or discard.
     */
    public function show(Request $request, AiGenerationDraft $draft): View
    {
        $user = $request->user();
        abort_unless($draft->school_id === $user->school_id && $draft->teacher_id === $user->id, 403);

        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('order')->get();

        return view('teacher.ai.show', compact('draft', 'subjects', 'gradeLevels'));
    }

    /**
     * Update draft: save edit, accept, discard, or submit feedback.
     */
    public function update(UpdateAiDraftRequest $request, AiGenerationDraft $draft): RedirectResponse
    {
        $user = $request->user();
        abort_unless($draft->school_id === $user->school_id && $draft->teacher_id === $user->id, 403);

        $action = $request->validated('action');

        switch ($action) {
            case 'accept':
                $draft->accept();

                return back()->with('success', 'Draf berhasil disetujui dan ditandai sebagai materi resmi guru.');

            case 'discard':
                $draft->discard();

                return redirect()->route('teacher.ai.index')->with('info', 'Draf telah dibuang dan tidak akan dipublikasikan.');

            case 'feedback':
                $draft->submitFeedback(
                    (int) $request->validated('feedback_rating', 5),
                    $request->validated('feedback_notes'),
                    (bool) $request->validated('is_flagged_bad_output', false)
                );

                return back()->with('success', 'Terima kasih! Umpan balik kualitas AI telah dicatat untuk evaluasi.');

            case 'save_edit':
            default:
                if ($request->has('edited_content')) {
                    $draft->update([
                        'edited_content' => $request->validated('edited_content'),
                        'version' => $draft->version + 1,
                    ]);
                }

                return back()->with('success', 'Perubahan draf berhasil disimpan.');
        }
    }

    /**
     * Import question set draft into CBT Exam Questions bank.
     */
    public function importToCbt(Request $request, AiGenerationDraft $draft): RedirectResponse
    {
        $user = $request->user();
        abort_unless($draft->school_id === $user->school_id && $draft->teacher_id === $user->id, 403);

        $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
        ]);

        try {
            $createdQuestions = $this->aiService->importQuestionsToCbt(
                $draft,
                (int) $request->input('subject_id'),
                $request->input('grade_level_id') ? (int) $request->input('grade_level_id') : null
            );

            return redirect()->route('exam-questions.index')
                ->with('success', count($createdQuestions).' butir soal dari draf AI berhasil diimpor ke Bank Soal CBT dengan status aktif.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengimpor soal ke CBT: '.$e->getMessage());
        }
    }
}
