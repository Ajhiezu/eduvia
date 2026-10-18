<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Workflow\ApprovalWorkflowService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApprovalWorkflowController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApprovalWorkflowService $workflowService
    ) {}

    /**
     * Get pending approvals requiring action by current user.
     */
    public function pendingInbox(Request $request): JsonResponse
    {
        $user = $request->user();
        $pending = $this->workflowService->getPendingApprovalsForUser($user);

        $data = $pending->map(fn (ApprovalRequest $r) => [
            'id' => $r->id,
            'request_number' => $r->request_number,
            'title' => $r->title,
            'workflow' => $r->workflow->name,
            'workflow_code' => $r->workflow->code,
            'requester_name' => $r->requester->name,
            'current_step' => $r->current_step,
            'current_step_name' => $r->currentStepDefinition()?->name,
            'submitted_at' => $r->submitted_at->translatedFormat('d M Y, H:i'),
            'payload' => $r->payload,
        ]);

        return $this->apiSuccess(
            data: $data,
            message: 'Kotak masuk persetujuan berhasil dimuat.'
        );
    }

    /**
     * Get requests submitted by current user.
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = ApprovalRequest::where('requester_id', $user->id)
            ->with(['workflow.steps', 'histories.actor'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (ApprovalRequest $r) => [
                'id' => $r->id,
                'request_number' => $r->request_number,
                'title' => $r->title,
                'workflow_name' => $r->workflow->name,
                'status' => $r->status,
                'current_step' => $r->current_step,
                'current_step_name' => $r->currentStepDefinition()?->name ?? 'Selesai',
                'submitted_at' => $r->submitted_at->translatedFormat('d M Y, H:i'),
                'completed_at' => $r->completed_at ? $r->completed_at->translatedFormat('d M Y, H:i') : null,
                'cancellation_reason' => $r->cancellation_reason,
            ]);

        return $this->apiSuccess(
            data: $requests,
            message: 'Riwayat pengajuan Anda berhasil dimuat.'
        );
    }

    /**
     * Detail of an approval request with audit histories and steps.
     */
    public function show(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        Gate::authorize('view', $approvalRequest);

        $approvalRequest->load(['workflow.steps', 'requester', 'histories.actor', 'histories.delegatedFrom']);

        return $this->apiSuccess(
            data: [
                'id' => $approvalRequest->id,
                'request_number' => $approvalRequest->request_number,
                'title' => $approvalRequest->title,
                'workflow' => [
                    'id' => $approvalRequest->workflow->id,
                    'code' => $approvalRequest->workflow->code,
                    'name' => $approvalRequest->workflow->name,
                    'steps' => $approvalRequest->workflow->steps->map(fn ($s) => [
                        'order' => $s->step_order,
                        'name' => $s->name,
                        'role' => $s->approver_role,
                        'timeout_hours' => $s->timeout_hours,
                    ]),
                ],
                'requester' => [
                    'id' => $approvalRequest->requester->id,
                    'name' => $approvalRequest->requester->name,
                    'email' => $approvalRequest->requester->email,
                ],
                'current_step' => $approvalRequest->current_step,
                'status' => $approvalRequest->status,
                'payload' => $approvalRequest->payload,
                'submitted_at' => $approvalRequest->submitted_at->translatedFormat('d M Y, H:i'),
                'completed_at' => $approvalRequest->completed_at ? $approvalRequest->completed_at->translatedFormat('d M Y, H:i') : null,
                'histories' => $approvalRequest->histories->map(fn ($h) => [
                    'step_number' => $h->step_number,
                    'actor_name' => $h->actor->name,
                    'delegated_from' => $h->delegatedFrom?->name,
                    'action' => $h->action,
                    'comment' => $h->comment,
                    'acted_at' => $h->acted_at->translatedFormat('d M Y, H:i'),
                ]),
            ],
            message: 'Detail pengajuan persetujuan berhasil dimuat.'
        );
    }

    /**
     * Submit tuition / fee relief request (Parent / Staff).
     */
    public function submitFeeRelief(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'relief_type' => 'required|string|in:scholarship,discount_percentage,discount_fixed,installment_extension',
            'discount_percentage' => 'nullable|numeric|min:1|max:100',
            'discount_fixed_amount' => 'nullable|integer|min:1000',
            'installments_count' => 'nullable|integer|min:2|max:12',
            'reason' => 'required|string|max:1500',
            'family_income_estimate' => 'nullable|integer',
        ]);

        $invoice = null;
        if (! empty($validated['invoice_id'])) {
            $invoice = Invoice::find($validated['invoice_id']);
        }

        $student = User::find($validated['student_id']);
        $title = "Permohonan Keringanan SPP: {$student->name}";

        $approvalReq = $this->workflowService->createRequest(
            requester: $user,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: $title,
            payload: array_merge($validated, [
                'student_name' => $student->name,
                'student_nisn' => $student->studentProfile?->nisn ?? '-',
                'classroom' => $student->classroom?->name ?? '-',
                'invoice_number' => $invoice?->invoice_number,
            ]),
            approvable: $invoice
        );

        return $this->apiSuccess(
            data: [
                'request_id' => $approvalReq->id,
                'request_number' => $approvalReq->request_number,
                'status' => $approvalReq->status,
                'current_step' => $approvalReq->current_step,
            ],
            message: 'Permohonan keringanan berhasil diajukan untuk ditinjau secara berjenjang.',
            statusCode: 201
        );
    }

    /**
     * Approve current step.
     */
    public function approve(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        Gate::authorize('act', $approvalRequest);

        $validated = $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->workflowService->approve(
                $approvalRequest,
                $request->user(),
                $validated['comment'] ?? null
            );

            return $this->apiSuccess(
                data: [
                    'id' => $result->id,
                    'status' => $result->status,
                    'current_step' => $result->current_step,
                    'is_completed' => $result->isCompleted(),
                ],
                message: 'Persetujuan langkah berhasil dicatat.'
            );
        } catch (\DomainException $e) {
            return $this->apiError($e->getMessage(), 422);
        }
    }

    /**
     * Reject request.
     */
    public function reject(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        Gate::authorize('act', $approvalRequest);

        $validated = $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        try {
            $result = $this->workflowService->reject(
                $approvalRequest,
                $request->user(),
                $validated['comment']
            );

            return $this->apiSuccess(
                data: [
                    'id' => $result->id,
                    'status' => $result->status,
                ],
                message: 'Penolakan pengajuan berhasil dicatat.'
            );
        } catch (\DomainException $e) {
            return $this->apiError($e->getMessage(), 422);
        }
    }

    /**
     * Cancel request by requester.
     */
    public function cancel(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        Gate::authorize('cancel', $approvalRequest);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->workflowService->cancel(
                $approvalRequest,
                $request->user(),
                $validated['reason']
            );

            return $this->apiSuccess(
                data: [
                    'id' => $result->id,
                    'status' => $result->status,
                ],
                message: 'Pengajuan berhasil dibatalkan.'
            );
        } catch (\DomainException $e) {
            return $this->apiError($e->getMessage(), 422);
        }
    }

    /**
     * Delegate approval authority to another user for a date range.
     */
    public function storeDelegation(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'delegatee_id' => 'required|exists:users,id|different:user_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ]);

        if ($validated['delegatee_id'] == $user->id) {
            return $this->apiError('Penerima delegasi tidak boleh diri Anda sendiri.', 422);
        }

        $delegation = ApprovalDelegation::create([
            'school_id' => $user->school_id,
            'delegator_id' => $user->id,
            'delegatee_id' => $validated['delegatee_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'is_active' => true,
        ]);

        return $this->apiSuccess(
            data: $delegation,
            message: 'Pendelegasian wewenang persetujuan berhasil disimpan.',
            statusCode: 201
        );
    }

    /**
     * List active delegations for current school.
     */
    public function listDelegations(Request $request): JsonResponse
    {
        $user = $request->user();

        $delegations = ApprovalDelegation::where('school_id', $user->school_id)
            ->with(['delegator', 'delegatee'])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'delegator_name' => $d->delegator->name,
                'delegatee_name' => $d->delegatee->name,
                'start_date' => $d->start_date->toDateString(),
                'end_date' => $d->end_date->toDateString(),
                'reason' => $d->reason,
                'is_active' => $d->isCurrentlyActive(),
            ]);

        return $this->apiSuccess(
            data: $delegations,
            message: 'Daftar delegasi wewenang berhasil dimuat.'
        );
    }
}
