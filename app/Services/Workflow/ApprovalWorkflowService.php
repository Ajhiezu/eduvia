<?php

namespace App\Services\Workflow;

use App\Models\ApprovalDelegation;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\User;
use App\Services\Notification\NotificationPayload;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalWorkflowService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Submit a new approval request into a workflow.
     */
    public function createRequest(
        User $requester,
        string $workflowCode,
        string $title,
        array $payload,
        ?Model $approvable = null
    ): ApprovalRequest {
        $schoolId = $requester->school_id;

        // Ensure default workflow exists for tenant
        $workflow = ApprovalWorkflow::where('school_id', $schoolId)
            ->where('code', $workflowCode)
            ->where('is_active', true)
            ->first();

        if (! $workflow) {
            $workflow = $this->createDefaultWorkflow($schoolId, $workflowCode);
        }

        return DB::transaction(function () use ($requester, $workflow, $title, $payload, $approvable, $schoolId) {
            do {
                $reqNumber = 'REQ-'.date('Ym').'-'.strtoupper(Str::random(6));
            } while (ApprovalRequest::where('request_number', $reqNumber)->exists());

            $request = ApprovalRequest::create([
                'school_id' => $schoolId,
                'workflow_id' => $workflow->id,
                'request_number' => $reqNumber,
                'title' => $title,
                'requester_id' => $requester->id,
                'current_step' => 1,
                'status' => ApprovalRequest::STATUS_PENDING,
                'payload' => $payload,
                'approvable_type' => $approvable ? get_class($approvable) : null,
                'approvable_id' => $approvable?->getKey(),
                'submitted_at' => now(),
            ]);

            ApprovalHistory::create([
                'request_id' => $request->id,
                'step_number' => 1,
                'actor_id' => $requester->id,
                'action' => ApprovalHistory::ACTION_SUBMITTED,
                'comment' => 'Pengajuan diajukan ke sistem.',
                'acted_at' => now(),
            ]);

            // Notify initial approver (privacy-compliant lock screen)
            $this->notifyCurrentApprover($request);

            return $request;
        });
    }

    /**
     * Approve the request at the current step with pessimistic locking.
     */
    public function approve(ApprovalRequest $request, User $actor, ?string $comment = null): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $actor, $comment) {
            /** @var ApprovalRequest $lockedRequest */
            $lockedRequest = ApprovalRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw new \DomainException("Pengajuan ini sudah berstatus {$lockedRequest->status} dan tidak dapat diproses lagi.");
            }

            // Enforce Separation of Duties
            if ($actor->id === $lockedRequest->requester_id) {
                throw new \DomainException('Pemohon dilarang menyetujui pengajuannya sendiri (Separation of Duties).');
            }

            // Verify authority (role, specific user, or active delegation)
            $step = $lockedRequest->currentStepDefinition();
            if (! $step) {
                throw new \DomainException('Langkah alur persetujuan tidak ditemukan.');
            }

            $delegatedFromId = $this->resolveAuthorizedDelegatorId($lockedRequest, $step, $actor);
            if (! $delegatedFromId && ! $this->userMatchesStep($actor, $step)) {
                throw new \DomainException('Anda tidak memiliki wewenang untuk menyetujui langkah ini.');
            }

            ApprovalHistory::create([
                'request_id' => $lockedRequest->id,
                'step_number' => $lockedRequest->current_step,
                'actor_id' => $actor->id,
                'delegated_from_id' => $delegatedFromId,
                'action' => ApprovalHistory::ACTION_APPROVED,
                'comment' => $comment ?? 'Disetujui',
                'acted_at' => now(),
            ]);

            // Check if there is a next step
            $nextStep = $lockedRequest->workflow->steps()
                ->where('step_order', '>', $lockedRequest->current_step)
                ->orderBy('step_order')
                ->first();

            if ($nextStep) {
                $lockedRequest->update([
                    'current_step' => $nextStep->step_order,
                ]);
                $this->notifyCurrentApprover($lockedRequest);
            } else {
                $lockedRequest->update([
                    'status' => ApprovalRequest::STATUS_APPROVED,
                    'completed_at' => now(),
                ]);
                $this->notifyRequester($lockedRequest, 'Pengajuan Anda telah disetujui sepenuhnya.');
            }

            return $lockedRequest;
        });
    }

    /**
     * Reject the request with required rejection reasoning.
     */
    public function reject(ApprovalRequest $request, User $actor, string $comment): ApprovalRequest
    {
        if (empty(trim($comment))) {
            throw new \InvalidArgumentException('Alasan penolakan pengajuan wajib diisi.');
        }

        return DB::transaction(function () use ($request, $actor, $comment) {
            /** @var ApprovalRequest $lockedRequest */
            $lockedRequest = ApprovalRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw new \DomainException("Pengajuan ini sudah berstatus {$lockedRequest->status}.");
            }

            if ($actor->id === $lockedRequest->requester_id) {
                throw new \DomainException('Pemohon dilarang menolak pengajuannya sendiri.');
            }

            $step = $lockedRequest->currentStepDefinition();
            $delegatedFromId = $this->resolveAuthorizedDelegatorId($lockedRequest, $step, $actor);
            if (! $delegatedFromId && ! $this->userMatchesStep($actor, $step)) {
                throw new \DomainException('Anda tidak memiliki wewenang untuk meninjau langkah ini.');
            }

            ApprovalHistory::create([
                'request_id' => $lockedRequest->id,
                'step_number' => $lockedRequest->current_step,
                'actor_id' => $actor->id,
                'delegated_from_id' => $delegatedFromId,
                'action' => ApprovalHistory::ACTION_REJECTED,
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $lockedRequest->update([
                'status' => ApprovalRequest::STATUS_REJECTED,
                'completed_at' => now(),
            ]);

            $this->notifyRequester($lockedRequest, "Pengajuan Anda ditolak: {$comment}");

            return $lockedRequest;
        });
    }

    /**
     * Cancel an active request (requester or superadmin).
     */
    public function cancel(ApprovalRequest $request, User $actor, string $reason): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $actor, $reason) {
            /** @var ApprovalRequest $lockedRequest */
            $lockedRequest = ApprovalRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw new \DomainException('Hanya pengajuan yang sedang diproses yang dapat dibatalkan.');
            }

            if ($actor->id !== $lockedRequest->requester_id && ! in_array($actor->role, ['superadmin', 'super_admin'])) {
                throw new \DomainException('Hanya pemohon atau Administrator yang dapat membatalkan pengajuan ini.');
            }

            ApprovalHistory::create([
                'request_id' => $lockedRequest->id,
                'step_number' => $lockedRequest->current_step,
                'actor_id' => $actor->id,
                'action' => ApprovalHistory::ACTION_CANCELLED,
                'comment' => $reason,
                'acted_at' => now(),
            ]);

            $lockedRequest->update([
                'status' => ApprovalRequest::STATUS_CANCELLED,
                'completed_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $lockedRequest;
        });
    }

    /**
     * Automatically escalate stale requests pending beyond timeout.
     */
    public function escalateStaleRequests(): int
    {
        $escalatedCount = 0;
        $pendingRequests = ApprovalRequest::where('status', ApprovalRequest::STATUS_PENDING)
            ->with(['workflow.steps', 'school'])
            ->get();

        foreach ($pendingRequests as $req) {
            $step = $req->currentStepDefinition();
            if (! $step) {
                continue;
            }

            $timeoutHours = $step->timeout_hours ?: 48;
            $lastActivity = $req->histories()->latest('id')->first()?->acted_at ?? $req->submitted_at;

            if (Carbon::parse($lastActivity)->addHours($timeoutHours)->isPast()) {
                DB::transaction(function () use ($req, $step, &$escalatedCount) {
                    ApprovalHistory::create([
                        'request_id' => $req->id,
                        'step_number' => $req->current_step,
                        'actor_id' => $req->requester_id, // System logged
                        'action' => ApprovalHistory::ACTION_ESCALATED,
                        'comment' => "Batas waktu persetujuan ({$step->timeout_hours} jam) terlampaui. Notifikasi eskalasi dikirim ke pimpinan.",
                        'acted_at' => now(),
                    ]);

                    // Escalate notification to School Admin
                    $schoolAdmin = User::where('school_id', $req->school_id)
                        ->whereIn('role', ['school_admin', 'superadmin'])
                        ->first();

                    if ($schoolAdmin) {
                        $this->notificationService->sendToUser(
                            $schoolAdmin,
                            new NotificationPayload(
                                title: 'Eskalasi Persetujuan Melebihi Batas Waktu',
                                body: "Tiket {$req->request_number} memerlukan atensi segera pimpinan.",
                                channel: 'fcm',
                                data: ['request_id' => $req->id]
                            )
                        );
                    }

                    $escalatedCount++;
                });
            }
        }

        return $escalatedCount;
    }

    /**
     * Get pending approvals requiring action by given user.
     */
    public function getPendingApprovalsForUser(User $user): Collection
    {
        $schoolId = $user->school_id;

        $requests = ApprovalRequest::where('school_id', $schoolId)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->where('requester_id', '!=', $user->id) // Separation of duties
            ->with(['workflow.steps', 'requester'])
            ->get();

        return $requests->filter(function (ApprovalRequest $req) use ($user) {
            $step = $req->currentStepDefinition();
            if (! $step) {
                return false;
            }

            return $this->userMatchesStep($user, $step) || (bool) $this->resolveAuthorizedDelegatorId($req, $step, $user);
        })->values();
    }

    /**
     * Check if a user matches the step criteria.
     */
    protected function userMatchesStep(User $user, ApprovalWorkflowStep $step): bool
    {
        if ($step->specific_user_id && $step->specific_user_id === $user->id) {
            return true;
        }

        $userRoleStr = is_string($user->role) ? $user->role : ($user->role?->value ?? (string) $user->role);

        if ($userRoleStr === $step->approver_role) {
            return true;
        }

        // Superadmins can act as emergency fallback
        if (in_array($userRoleStr, ['superadmin', 'super_admin'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Check active delegations where delegator is authorized for this step and user is delegatee.
     */
    protected function resolveAuthorizedDelegatorId(ApprovalRequest $req, ApprovalWorkflowStep $step, User $user): ?int
    {
        $delegations = ApprovalDelegation::where('school_id', $req->school_id)
            ->where('delegatee_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->with('delegator')
            ->get();

        foreach ($delegations as $d) {
            if ($d->delegator && $this->userMatchesStep($d->delegator, $step)) {
                return $d->delegator_id;
            }
        }

        return null;
    }

    /**
     * Send privacy-compliant notification to the step approver.
     */
    protected function notifyCurrentApprover(ApprovalRequest $request): void
    {
        $step = $request->currentStepDefinition();
        if (! $step) {
            return;
        }

        $targetUser = $step->specificUser ?? User::where('school_id', $request->school_id)
            ->where('role', $step->approver_role)
            ->first();

        if ($targetUser) {
            $this->notificationService->send(
                new NotificationPayload(
                    userId: $targetUser->id,
                    schoolId: $request->school_id,
                    eventKey: 'workflow.approval_required',
                    title: 'Permohonan Persetujuan Baru',
                    body: "Terdapat permohonan ({$step->name}) yang memerlukan tinjauan Anda.",
                    data: ['request_id' => $request->id]
                )
            );
        }
    }

    /**
     * Notify requester with masked safe update.
     */
    protected function notifyRequester(ApprovalRequest $request, string $message): void
    {
        $requester = $request->requester;
        if ($requester) {
            $this->notificationService->send(
                new NotificationPayload(
                    userId: $requester->id,
                    schoolId: $request->school_id,
                    eventKey: 'workflow.status_updated',
                    title: 'Pembaruan Status Pengajuan',
                    body: $message,
                    data: ['request_id' => $request->id]
                )
            );
        }
    }

    /**
     * Seed default workflow configuration per module code.
     */
    public function createDefaultWorkflow(int $schoolId, string $code): ApprovalWorkflow
    {
        return DB::transaction(function () use ($schoolId, $code) {
            $name = match ($code) {
                ApprovalWorkflow::CODE_FEE_RELIEF => 'Persetujuan Keringanan & Cicilan Biaya',
                ApprovalWorkflow::CODE_SCHOOL_LETTER => 'Persetujuan Surat Keluar & Legalitas',
                ApprovalWorkflow::CODE_TEACHER_LEAVE => 'Persetujuan Cuti Pendidik & Tenaga Kependidikan',
                ApprovalWorkflow::CODE_GRADE_CORRECTION => 'Persetujuan Koreksi Nilai Rapor Resmi',
                ApprovalWorkflow::CODE_BUDGET_REQUEST => 'Persetujuan Pengajuan Anggaran Unit',
                default => 'Alur Persetujuan Sekolah',
            };

            $workflow = ApprovalWorkflow::create([
                'school_id' => $schoolId,
                'code' => $code,
                'name' => $name,
                'description' => "Definisi alur persetujuan resmi untuk modul {$code}.",
                'is_active' => true,
            ]);

            if ($code === ApprovalWorkflow::CODE_FEE_RELIEF) {
                // Tiered: Step 1: Wali Kelas, Step 2: Kepala Sekolah, Step 3: Bendahara Yayasan
                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => 1,
                    'name' => 'Validasi Kondisi oleh Wali Kelas',
                    'approver_role' => 'teacher',
                    'timeout_hours' => 48,
                ]);

                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => 2,
                    'name' => 'Persetujuan Awal oleh Kepala Sekolah',
                    'approver_role' => 'school_admin',
                    'timeout_hours' => 48,
                ]);

                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => 3,
                    'name' => 'Persetujuan Akhir & Eksekusi Bendahara Yayasan',
                    'approver_role' => 'super_admin',
                    'timeout_hours' => 72,
                ]);
            } elseif ($code === ApprovalWorkflow::CODE_SCHOOL_LETTER) {
                // Simple: Step 1: Kepala Sekolah
                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => 1,
                    'name' => 'Tanda Tangan & Persetujuan Kepala Sekolah',
                    'approver_role' => 'school_admin',
                    'timeout_hours' => 24,
                ]);
            }

            return $workflow;
        });
    }
}
