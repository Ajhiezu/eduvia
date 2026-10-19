<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LibraryPolicy
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
     * Determine whether the user can browse books catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific book.
     */
    public function view(User $user, LibraryBook $book): bool
    {
        if ($user->school_id && $book->school_id && (int) $user->school_id !== (int) $book->school_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can download the digital ebook.
     */
    public function downloadDigital(User $user, LibraryBook $book): bool
    {
        if ($user->school_id && $book->school_id && (int) $user->school_id !== (int) $book->school_id) {
            return false;
        }

        return ! empty($book->digital_file_path);
    }

    /**
     * Determine whether the user can manage books and catalog copies.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::STAFF);
    }
}
