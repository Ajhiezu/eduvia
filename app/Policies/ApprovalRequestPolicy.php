<?php

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalRequestPolicy
{
    /**
     * Determine whether the user can view the approval request.
     */
    public function view(User $user, ApprovalRequest $request): bool
    {
        // Tenant isolation check
        if ($user->school_id && $user->school_id !== $request->school_id) {
            return false;
        }

        // Requester can view
        if ($user->id === $request->requester_id) {
            return true;
        }

        $roleStr = is_string($user->role) ? $user->role : ($user->role?->value ?? (string) $user->role);

        // Superadmins can view
        if (in_array($roleStr, ['superadmin', 'super_admin'], true)) {
            return true;
        }

        // Current approver or step role can view
        $step = $request->currentStepDefinition();
        if ($step && ($roleStr === $step->approver_role || $user->id === $step->specific_user_id)) {
            return true;
        }

        // Anyone in the history can view
        return $request->histories()->where('actor_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can act (approve/reject) on the request.
     */
    public function act(User $user, ApprovalRequest $request): bool
    {
        // Must belong to same school
        if ($user->school_id && $user->school_id !== $request->school_id) {
            return false;
        }

        // Must be currently pending
        if (! $request->isPending()) {
            return false;
        }

        // Separation of duties: requester cannot act on own request
        if ($user->id === $request->requester_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can cancel the request.
     */
    public function cancel(User $user, ApprovalRequest $request): bool
    {
        if ($user->school_id && $user->school_id !== $request->school_id) {
            return false;
        }

        if (! $request->isPending()) {
            return false;
        }

        return $user->id === $request->requester_id || in_array($user->role, ['superadmin', 'super_admin']);
    }
}
