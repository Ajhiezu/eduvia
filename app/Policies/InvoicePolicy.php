<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view invoices listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::STAFF,
            UserRole::STUDENT,
            UserRole::PARENT
        );
    }

    /**
     * Determine whether the user can view a specific invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // 1. Same tenant check
        if ($user->school_id && $invoice->school_id && (int) $user->school_id !== (int) $invoice->school_id) {
            return false;
        }

        // 2. School admin and finance staff can view all school invoices
        if ($user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF)) {
            return true;
        }

        // 3. Student can view own invoice
        if ($user->isStudent()) {
            return (int) $user->id === (int) $invoice->student_id;
        }

        // 4. Parent can view invoice of their linked child
        if ($user->isParent() && $invoice->student) {
            return $user->isParentOf($invoice->student);
        }

        return false;
    }

    /**
     * Determine whether the user can create an invoice (Maker).
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can update an invoice (Maker).
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->school_id && $invoice->school_id && (int) $user->school_id !== (int) $invoice->school_id) {
            return false;
        }

        // Invoices cannot be modified if already paid or cancelled
        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            return false;
        }

        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can delete an invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if ($user->school_id && $invoice->school_id && (int) $user->school_id !== (int) $invoice->school_id) {
            return false;
        }

        // Invoices with successful payments cannot be deleted
        if ($invoice->payments()->where('status', 'success')->exists()) {
            return false;
        }

        return $user->hasRole(UserRole::SCHOOL_ADMIN);
    }

    /**
     * Determine whether the user can pay the invoice (Student or Parent).
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        if ($user->school_id && $invoice->school_id && (int) $user->school_id !== (int) $invoice->school_id) {
            return false;
        }

        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return false;
        }

        if ($user->isStudent()) {
            return (int) $user->id === (int) $invoice->student_id;
        }

        if ($user->isParent() && $invoice->student) {
            return $user->isParentOf($invoice->student);
        }

        return false;
    }

    /**
     * Determine whether the user can verify/approve payments (Checker role).
     */
    public function verifyPayment(User $user, Invoice $invoice): bool
    {
        if ($user->school_id && $invoice->school_id && (int) $user->school_id !== (int) $invoice->school_id) {
            return false;
        }

        // Administrative staff and school admin act as Checker
        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }
}
