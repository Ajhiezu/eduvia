<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateEssayFeedbackRequest;
use App\Http\Requests\AI\GenerateLessonPlanRequest;
use App\Http\Requests\AI\GenerateQuestionSetRequest;
use App\Http\Requests\AI\GenerateRubricRequest;
use App\Http\Requests\AI\UpdateAiDraftRequest;
use App\Models\AiGenerationDraft;
use App\Services\AI\AiQuotaManager;
use App\Services\AI\TeacherAiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAiApiController extends Controller
{
    public function __construct(
        protected TeacherAiService $aiService,
        protected AiQuotaManager $quotaManager
    ) {}

    /**
     * Get quota and usage stats for teacher's school.
     */
    public function quota(Request $request): JsonResponse
    {
        $user = $request->user();
        $quota = $this->quotaManager->getOrCreateQuota($user->school_id);

        return response()->json([
            'success' => true,
            'data' => [
                'is_ai_enabled' => $quota->is_ai_enabled,
                'monthly_token_limit' => $quota->monthly_token_limit,
                'monthly_budget_idr' => $quota->monthly_budget_idr,
                'current_month_tokens_used' => $quota->current_month_tokens_used,
                'current_month_cost_idr' => $quota->current_month_cost_idr,
                'usage_percentage' => $quota->usagePercentage(),
                'quota_reset_at' => $quota->quota_reset_at?->format('Y-m-d'),
                'rate_limit_per_minute' => $quota->rate_limit_per_minute,
            ],
        ]);
    }

    /**
     * List AI drafts for the authenticated teacher.
     */
    public function drafts(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AiGenerationDraft::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->latest();

        if ($feature = $request->query('feature')) {
            $query->where('feature_type', $feature);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $drafts = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $drafts->items(),
            'pagination' => [
                'current_page' => $drafts->currentPage(),
                'last_page' => $drafts->lastPage(),
                'total' => $drafts->total(),
            ],
        ]);
    }

    /**
     * Generate Lesson Plan draft.
     */
    public function generateLessonPlan(GenerateLessonPlanRequest $request): JsonResponse
    {
        try {
            $draft = $this->aiService->generateLessonPlan(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Draf Modul Ajar berhasil disusun oleh AI.',
                'data' => $draft,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate Question Set draft.
     */
    public function generateQuestions(GenerateQuestionSetRequest $request): JsonResponse
    {
        try {
            $draft = $this->aiService->generateQuestionSet(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Draf Paket Soal berhasil disusun oleh AI.',
                'data' => $draft,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate Rubric draft.
     */
    public function generateRubric(GenerateRubricRequest $request): JsonResponse
    {
        try {
            $draft = $this->aiService->generateGradingRubric(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Draf Rubrik Penilaian berhasil disusun oleh AI.',
                'data' => $draft,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate Essay Feedback draft.
     */
    public function generateEssayFeedback(GenerateEssayFeedbackRequest $request): JsonResponse
    {
        try {
            $draft = $this->aiService->generateEssayFeedback(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Analisis dan saran umpan balik esai berhasil dibuat oleh AI.',
                'data' => $draft,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get specific draft detail.
     */
    public function show(Request $request, AiGenerationDraft $draft): JsonResponse
    {
        $user = $request->user();
        if ($draft->school_id !== $user->school_id || $draft->teacher_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $draft,
        ]);
    }

    /**
     * Update or take action on draft (accept/discard/edit).
     */
    public function update(UpdateAiDraftRequest $request, AiGenerationDraft $draft): JsonResponse
    {
        $user = $request->user();
        if ($draft->school_id !== $user->school_id || $draft->teacher_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $action = $request->validated('action');

        switch ($action) {
            case 'accept':
                $draft->accept();
                $msg = 'Draf AI berhasil disetujui.';
                break;
            case 'discard':
                $draft->discard();
                $msg = 'Draf AI dibuang.';
                break;
            case 'feedback':
                $draft->submitFeedback(
                    (int) $request->validated('feedback_rating', 5),
                    $request->validated('feedback_notes'),
                    (bool) $request->validated('is_flagged_bad_output', false)
                );
                $msg = 'Umpan balik kualitas AI berhasil dicatat.';
                break;
            case 'save_edit':
            default:
                if ($request->has('edited_content')) {
                    $draft->update([
                        'edited_content' => $request->validated('edited_content'),
                        'version' => $draft->version + 1,
                    ]);
                }
                $msg = 'Perubahan draf berhasil disimpan.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => $draft->fresh(),
        ]);
    }
}
