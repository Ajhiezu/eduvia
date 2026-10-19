<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pilot\PilotFeedback;
use App\Models\Pilot\PilotSurvey;
use App\Services\Pilot\PilotFeedbackService;
use App\Services\Pilot\PilotMetricsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PilotSupportApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PilotFeedbackService $feedbackService,
        protected PilotMetricsService $metricsService
    ) {}

    /**
     * Submit in-app user feedback (Bug, UX confusion, suggestion).
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:bug,ux_friction,feature_suggestion,training_needed',
            'severity' => 'nullable|string|in:p0,p1,p2,p3',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'screenshot' => 'nullable|image|max:5120', // 5MB max
            'app_version' => 'nullable|string|max:30',
            'device_info' => 'nullable|array',
        ]);

        $feedback = $this->feedbackService->submitFeedback(
            $validated,
            $request->user(),
            $request->file('screenshot')
        );

        return $this->apiSuccess(
            data: [
                'id' => $feedback->id,
                'category' => $feedback->category,
                'severity' => $feedback->severity,
                'status' => $feedback->status,
                'created_at' => $feedback->created_at->toIso8601String(),
            ],
            message: 'Umpan balik berhasil dikirimkan ke tim pendamping pilot.',
            statusCode: 201
        );
    }

    /**
     * List feedbacks for triaging by school admin or pilot support.
     */
    public function listFeedbacks(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PilotFeedback::where('school_id', $user->school_id)
            ->with('user:id,name,role')
            ->orderByDesc('created_at');

        if ($request->has('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $feedbacks = $query->paginate(20);

        return $this->apiPaginated($feedbacks, 'Daftar umpan balik pilot berhasil dimuat.');
    }

    /**
     * Triage and update feedback status.
     */
    public function triageFeedback(Request $request, PilotFeedback $feedback): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,in_triage,in_progress,resolved,rejected',
            'severity' => 'nullable|string|in:p0,p1,p2,p3',
            'triage_notes' => 'nullable|string|max:1000',
            'response_template' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $updated = $this->feedbackService->triageFeedback($feedback, $validated, $request->user());

        return $this->apiSuccess(
            data: $updated,
            message: 'Status umpan balik berhasil diperbarui.'
        );
    }

    /**
     * Submit periodic anonymous survey.
     */
    public function submitSurvey(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'wave_number' => 'required|integer|min:1|max:6',
            'satisfaction_score' => 'required|integer|min:1|max:5',
            'ease_of_use_score' => 'required|integer|min:1|max:5',
            'main_obstacle' => 'nullable|string|max:255',
            'constructive_feedback' => 'nullable|string|max:1500',
        ]);

        $survey = PilotSurvey::create([
            'school_id' => $user->school_id,
            'wave_number' => $validated['wave_number'],
            'respondent_role' => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role,
            'satisfaction_score' => $validated['satisfaction_score'],
            'ease_of_use_score' => $validated['ease_of_use_score'],
            'main_obstacle' => $validated['main_obstacle'] ?? null,
            'constructive_feedback' => $validated['constructive_feedback'] ?? null,
        ]);

        return $this->apiSuccess(
            data: ['id' => $survey->id],
            message: 'Terima kasih atas penilaian Anda terhadap uji coba Eduvia.',
            statusCode: 201
        );
    }

    /**
     * Get weekly aggregate report with zero PII.
     */
    public function weeklyReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', now()->endOfWeek()->toDateString());

        $report = $this->metricsService->generateWeeklyReport($user->school_id, $startDate, $endDate);

        return $this->apiSuccess(
            data: $report,
            message: 'Laporan metrik mingguan pilot berhasil dimuat.'
        );
    }
}
