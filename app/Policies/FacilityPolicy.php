<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FacilityReservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityPolicy
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
     * Determine whether the user can view rooms catalog.
     */
    public function viewRooms(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::STAFF,
            UserRole::TEACHER
        );
    }

    /**
     * Determine whether the user can manage rooms.
     */
    public function manageRooms(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can reserve a facility room.
     */
    public function reserve(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::STAFF,
            UserRole::TEACHER
        );
    }

    /**
     * Determine whether the user can cancel a reservation.
     */
    public function cancelReservation(User $user, FacilityReservation $reservation): bool
    {
        if ($user->school_id && $reservation->school_id && (int) $user->school_id !== (int) $reservation->school_id) {
            return false;
        }

        // Creator can cancel if pending
        if ((int) $user->id === (int) $reservation->user_id) {
            return in_array($reservation->status, ['pending', 'approved'], true);
        }

        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can approve or reject reservations (Checker).
     */
    public function reviewReservation(User $user, FacilityReservation $reservation): bool
    {
        if ($user->school_id && $reservation->school_id && (int) $user->school_id !== (int) $reservation->school_id) {
            return false;
        }

        // Must be staff or school admin
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }
}
