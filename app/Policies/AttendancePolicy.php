<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
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
     * Determine whether the user can view attendance sessions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::TEACHER,
            UserRole::STAFF,
            UserRole::STUDENT,
            UserRole::PARENT
        );
    }

    /**
     * Determine whether the user can view a specific attendance session.
     */
    public function view(User $user, AttendanceSession $session): bool
    {
        if ($user->school_id && $session->school_id && (int) $user->school_id !== (int) $session->school_id) {
            return false;
        }

        if ($user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF)) {
            return true;
        }

        if ($user->isTeacher()) {
            // Teacher is the session teacher or homeroom teacher of this classroom
            if ((int) $user->id === (int) $session->teacher_id) {
                return true;
            }

            if ($session->classroom && $user->isHomeroomTeacher($session->classroom)) {
                return true;
            }

            return false;
        }

        if ($user->isStudent()) {
            return $session->records()->where('student_id', $user->id)->exists();
        }

        if ($user->isParent()) {
            $childIds = $user->activeChildren()->pluck('users.id');

            return $session->records()->whereIn('student_id', $childIds)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create attendance sessions.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF, UserRole::TEACHER);
    }

    /**
     * Determine whether the user can record attendance data.
     */
    public function update(User $user, AttendanceSession $session): bool
    {
        if ($user->school_id && $session->school_id && (int) $user->school_id !== (int) $session->school_id) {
            return false;
        }

        if ($user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF)) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $user->id === (int) $session->teacher_id
                || ($session->classroom && $user->isHomeroomTeacher($session->classroom));
        }

        return false;
    }

    /**
     * Determine whether the user can delete an attendance session.
     */
    public function delete(User $user, AttendanceSession $session): bool
    {
        if ($user->school_id && $session->school_id && (int) $user->school_id !== (int) $session->school_id) {
            return false;
        }

        return $user->hasRole(UserRole::SCHOOL_ADMIN);
    }
}
