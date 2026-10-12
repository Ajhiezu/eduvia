<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'is_active', 'avatar', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasRole(UserRole|string ...$roles): bool
    {
        $currentRoleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        foreach ($roles as $role) {
            $expectedRoleValue = $role instanceof UserRole ? $role->value : (string) $role;
            if ($currentRoleValue === $expectedRoleValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(Permission $permission): bool
    {
        if ($this->hasRole(UserRole::SUPER_ADMIN)) {
            return true;
        }

        if (! $this->role instanceof UserRole) {
            return false;
        }

        return in_array($permission, Permission::forRole($this->role), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN);
    }

    public function isSchoolAdmin(): bool
    {
        return $this->hasRole(UserRole::SCHOOL_ADMIN);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(UserRole::TEACHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(UserRole::STUDENT);
    }

    public function isParent(): bool
    {
        return $this->hasRole(UserRole::PARENT);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
