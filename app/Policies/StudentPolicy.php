<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Intercept all checks: Super Admin can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view student lists.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::TEACHER,
            UserRole::STAFF
        );
    }

    /**
     * Determine whether the user can view the specific student's dossier.
     */
    public function view(User $user, User $student): bool
    {
        // 1. Must belong to the same school tenant
        if ($user->school_id && $student->school_id && (int) $user->school_id !== (int) $student->school_id) {
            return false;
        }

        // 2. School admin or general staff
        if ($user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF)) {
            return true;
        }

        // 3. Teacher: must teach this student or be homeroom teacher
        if ($user->isTeacher()) {
            return $user->teachesStudent($student);
        }

        // 4. Student: can only view own dossier
        if ($user->isStudent()) {
            return (int) $user->id === (int) $student->id;
        }

        // 5. Parent: can only view dossier of own verified child
        if ($user->isParent()) {
            return $user->isParentOf($student);
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, User $student): bool
    {
        if ($user->school_id && $student->school_id && (int) $user->school_id !== (int) $student->school_id) {
            return false;
        }

        return $user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, User $student): bool
    {
        if ($user->school_id && $student->school_id && (int) $user->school_id !== (int) $student->school_id) {
            return false;
        }

        return $user->hasRole(UserRole::SCHOOL_ADMIN);
    }
}
