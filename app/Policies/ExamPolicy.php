<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPolicy
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
     * Determine whether the user can view exams list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::SUPER_ADMIN,
            UserRole::SCHOOL_ADMIN,
            UserRole::TEACHER,
            UserRole::STAFF,
            UserRole::STUDENT
        );
    }

    /**
     * Determine whether the user can view the specific exam.
     */
    public function view(User $user, Exam $exam): bool
    {
        if ($user->school_id && $exam->school_id && (int) $user->school_id !== (int) $exam->school_id) {
            return false;
        }

        if ($user->hasRole(UserRole::SCHOOL_ADMIN, UserRole::STAFF)) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $user->id === (int) $exam->teacher_id;
        }

        if ($user->isStudent()) {
            // Student can view exam if published and student belongs to the exam's classroom
            if ($exam->status !== 'published') {
                return false;
            }

            return $user->classrooms()->where('classrooms.id', $exam->classroom_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create exams.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER);
    }

    /**
     * Determine whether the user can update the exam.
     */
    public function update(User $user, Exam $exam): bool
    {
        if ($user->school_id && $exam->school_id && (int) $user->school_id !== (int) $exam->school_id) {
            return false;
        }

        if ($user->isSchoolAdmin()) {
            return true;
        }

        return $user->isTeacher() && (int) $user->id === (int) $exam->teacher_id;
    }

    /**
     * Determine whether the user can delete the exam.
     */
    public function delete(User $user, Exam $exam): bool
    {
        if ($user->school_id && $exam->school_id && (int) $user->school_id !== (int) $exam->school_id) {
            return false;
        }

        // Cannot delete exam if attempts have already been submitted
        if ($exam->attempts()->where('status', 'completed')->exists()) {
            return false;
        }

        if ($user->isSchoolAdmin()) {
            return true;
        }

        return $user->isTeacher() && (int) $user->id === (int) $exam->teacher_id;
    }

    /**
     * Determine whether the student can take/attempt the exam.
     */
    public function take(User $user, Exam $exam): bool
    {
        if ($user->school_id && $exam->school_id && (int) $user->school_id !== (int) $exam->school_id) {
            return false;
        }

        if (! $user->isStudent()) {
            return false;
        }

        if ($exam->status !== 'published') {
            return false;
        }

        return $user->classrooms()->where('classrooms.id', $exam->classroom_id)->exists();
    }
}
