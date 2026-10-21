<?php

use App\Http\Controllers\Api\ApprovalWorkflowController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\PilotSupportApiController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\V1\Teacher\TeacherAiApiController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Eduvia Platform
|--------------------------------------------------------------------------
|
| Versioned endpoints adhering to Phase 0 API Architecture conventions:
| - Consistent JSON responses via ApiResponse trait
| - Standard HTTP status codes
| - Defense-in-depth rate limiting
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public Health Check & Observability Endpoint
    Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

    // Public App Version Check & Maintenance Status
    Route::get('/app/version-check', function () {
        return response()->json([
            'success' => true,
            'message' => 'Status versi aplikasi berhasil diperiksa.',
            'data' => [
                'min_version' => '1.0.0',
                'latest_version' => '1.2.0',
                'force_update' => false,
                'maintenance_mode' => false,
                'download_url' => 'https://play.google.com/store/apps/details?id=com.eduvia.app',
            ],
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('app.version_check');

    // Authentication Endpoints
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    // Authenticated Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        // Universal Notifications (Student, Parent, Teacher)
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::post('/device-token', [NotificationController::class, 'registerDeviceToken'])->name('device_token.register');
            Route::delete('/device-token', [NotificationController::class, 'removeDeviceToken'])->name('device_token.remove');
            Route::get('/preferences', [NotificationController::class, 'getPreferences'])->name('preferences.get');
            Route::put('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
            Route::get('/logs', [NotificationController::class, 'getLogs'])->name('logs');
        });

        // Universal Approval Workflow Engine (Fee relief, letters, leave, etc.)
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/pending', [ApprovalWorkflowController::class, 'pendingInbox'])->name('pending');
            Route::get('/my-requests', [ApprovalWorkflowController::class, 'myRequests'])->name('my_requests');
            Route::get('/delegations', [ApprovalWorkflowController::class, 'listDelegations'])->name('delegations.index');
            Route::post('/delegations', [ApprovalWorkflowController::class, 'storeDelegation'])->name('delegations.store');
            Route::post('/fee-relief', [ApprovalWorkflowController::class, 'submitFeeRelief'])->name('fee_relief.submit');
            Route::get('/{approvalRequest}', [ApprovalWorkflowController::class, 'show'])->name('show');
            Route::post('/{approvalRequest}/approve', [ApprovalWorkflowController::class, 'approve'])->name('approve');
            Route::post('/{approvalRequest}/reject', [ApprovalWorkflowController::class, 'reject'])->name('reject');
            Route::post('/{approvalRequest}/cancel', [ApprovalWorkflowController::class, 'cancel'])->name('cancel');
        });

        // Student Mobile App Endpoints (guarded with role:student ability)
        Route::middleware('ability:role:student')->prefix('student')->name('student.')->group(function () {
            Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
            Route::get('/schedules', [StudentController::class, 'schedules'])->name('schedules');
            Route::get('/agenda', [StudentController::class, 'agenda'])->name('agenda');
            Route::get('/materials', [StudentController::class, 'materials'])->name('materials');
            Route::get('/materials/{material}/attachments/{attachment}/download', [StudentController::class, 'downloadMaterialAttachment'])->name('materials.attachment.download');
            Route::get('/assignments', [StudentController::class, 'assignments'])->name('assignments');
            Route::post('/assignments/{assignment}/submit', [StudentController::class, 'submitAssignment'])->name('assignments.submit');
            Route::get('/reports', [StudentController::class, 'reports'])->name('reports');
            Route::get('/conversations', [StudentController::class, 'conversations'])->name('conversations');
            Route::get('/conversations/{conversation}/messages', [StudentController::class, 'conversationMessages'])->name('conversations.messages');
            Route::post('/conversations/{conversation}/messages', [StudentController::class, 'sendConversationMessage'])->name('conversations.messages.send');
            Route::get('/cbt', [StudentController::class, 'cbt'])->name('cbt');
            Route::post('/cbt/{exam}/start', [StudentController::class, 'startCbt'])->name('cbt.start');
            Route::post('/cbt/{exam}/save-answer', [StudentController::class, 'saveCbtAnswer'])->name('cbt.save_answer');
            Route::post('/cbt/{exam}/save-answers-batch', [StudentController::class, 'saveBatchCbtAnswers'])->name('cbt.save_answers_batch');
            Route::post('/cbt/{exam}/integrity-events', [StudentController::class, 'logIntegrityEvent'])->name('cbt.integrity_events');
            Route::post('/cbt/{exam}/finish', [StudentController::class, 'finishCbt'])->name('cbt.finish');
        });

        // Parent Mobile App Endpoints (guarded with role:parent ability)
        Route::middleware('ability:role:parent')->prefix('parent')->name('parent.')->group(function () {
            Route::get('/children', [ParentController::class, 'children'])->name('children');
            Route::post('/claim-code', [ParentController::class, 'claimCode'])->name('claim_code')->middleware('throttle:5,1');
            Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
            Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
            Route::get('/leaves', [LeaveRequestController::class, 'parentIndex'])->name('leaves.index');
            Route::post('/leaves', [LeaveRequestController::class, 'parentStore'])->name('leaves.store');
            Route::get('/finance', [ParentController::class, 'finance'])->name('finance');
            Route::post('/invoices/{invoice}/pay', [ParentController::class, 'submitInvoicePayment'])->name('invoices.pay');
            Route::get('/reports', [ParentController::class, 'reports'])->name('reports');
            Route::get('/conversations', [ParentController::class, 'conversations'])->name('conversations');
            Route::get('/conversations/{conversation}/messages', [ParentController::class, 'conversationMessages'])->name('conversations.messages');
            Route::post('/conversations/{conversation}/messages', [ParentController::class, 'sendConversationMessage'])->name('conversations.messages.send');
        });

        // Teacher Mobile App Endpoints (guarded with role:teacher ability)
        Route::middleware('ability:role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
            Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
            Route::get('/schedules', [TeacherController::class, 'schedules'])->name('schedules');
            Route::get('/classes', [TeacherController::class, 'classes'])->name('classes');
            Route::get('/attendance/sessions', [TeacherController::class, 'attendanceSessions'])->name('attendance.sessions');
            Route::post('/attendance/sessions', [TeacherController::class, 'storeAttendanceSession'])->name('attendance.sessions.store');
            Route::post('/attendance/sessions/{session}/records', [TeacherController::class, 'saveAttendanceRecords'])->name('attendance.records.save');
            Route::get('/assignments', [TeacherController::class, 'assignments'])->name('assignments');
            Route::get('/assignments/{assignment}/submissions', [TeacherController::class, 'submissions'])->name('assignments.submissions');
            Route::post('/assignments/{assignment}/submissions/{submission}/grade', [TeacherController::class, 'gradeSubmission'])->name('assignments.grade');
            Route::put('/chat-settings', [TeacherController::class, 'updateChatSettings'])->name('chat_settings.update');
            Route::get('/leaves', [LeaveRequestController::class, 'teacherIndex'])->name('leaves.index');
            Route::post('/leaves/{leave}/review', [LeaveRequestController::class, 'teacherReview'])->name('leaves.review');

            // AI Teacher Assistant Endpoints (Phase 4B - guarded by tenant.ai)
            Route::middleware('tenant.ai')->prefix('ai')->name('ai.')->group(function () {
                Route::get('/quota', [TeacherAiApiController::class, 'quota'])->name('quota');
                Route::get('/drafts', [TeacherAiApiController::class, 'drafts'])->name('drafts');
                Route::post('/generate-lesson-plan', [TeacherAiApiController::class, 'generateLessonPlan'])->name('generate_lesson_plan');
                Route::post('/generate-questions', [TeacherAiApiController::class, 'generateQuestions'])->name('generate_questions');
                Route::post('/generate-rubric', [TeacherAiApiController::class, 'generateRubric'])->name('generate_rubric');
                Route::post('/generate-essay-feedback', [TeacherAiApiController::class, 'generateEssayFeedback'])->name('generate_essay_feedback');
                Route::get('/drafts/{draft}', [TeacherAiApiController::class, 'show'])->name('drafts.show');
                Route::put('/drafts/{draft}', [TeacherAiApiController::class, 'update'])->name('drafts.update');
            });
        });

        // Automated Payment Gateway & Financial Ledger (Step 3C)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::post('/checkout', [PaymentGatewayController::class, 'checkout'])->name('checkout');
            Route::get('/status/{orderId}', [PaymentGatewayController::class, 'checkStatus'])->name('status');
            Route::post('/reconcile', [PaymentGatewayController::class, 'reconcile'])->name('reconcile');
            Route::get('/ledger', [PaymentGatewayController::class, 'ledger'])->name('ledger');
        });

        // Pilot Support, Feedback & Telemetry (Phase 6B)
        Route::prefix('pilot')->name('pilot.')->group(function () {
            Route::post('/feedback', [PilotSupportApiController::class, 'submitFeedback'])->name('feedback.submit');
            Route::get('/feedbacks', [PilotSupportApiController::class, 'listFeedbacks'])->name('feedbacks.list');
            Route::put('/feedbacks/{feedback}/triage', [PilotSupportApiController::class, 'triageFeedback'])->name('feedback.triage');
            Route::post('/surveys', [PilotSupportApiController::class, 'submitSurvey'])->name('survey.submit');
            Route::get('/metrics/weekly', [PilotSupportApiController::class, 'weeklyReport'])->name('metrics.weekly');
        });
    });

    // Public Midtrans Webhook (Unauthenticated, Signature-verified)
    Route::post('/webhooks/payment-gateway', [PaymentGatewayController::class, 'handleWebhook'])
        ->name('webhooks.payment_gateway');
});
