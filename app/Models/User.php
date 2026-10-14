<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\StaffPosition;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'is_active', 'avatar', 'password', 'school_id', 'email_verified_at'])]
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

    /**
     * Classroom enrollments for student.
     */
    public function classroomEnrollments(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class, 'student_id');
    }

    /**
     * Classrooms where this user is the homeroom teacher.
     */
    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    /**
     * Teaching assignments where this user is the teacher.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    /**
     * Attendance records for this student.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    /**
     * Extended profile for student.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    /**
     * Status change histories for student.
     */
    public function studentStatusHistories(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class, 'student_id')->latest('effective_date')->latest('id');
    }

    /**
     * Learning materials created by this user (teacher).
     */
    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'teacher_id');
    }

    /**
     * Assignments created by this user (teacher).
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    /**
     * Assignment submissions by this user (student).
     */
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    /**
     * Learning material progress records for this user (student).
     */
    public function materialProgress(): HasMany
    {
        return $this->hasMany(LearningMaterialProgress::class, 'student_id');
    }

    /**
     * Extended profile for staff / teacher.
     */
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class, 'user_id');
    }

    /**
     * School relationship.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Audit histories for this staff account.
     */
    public function staffStatusHistories(): HasMany
    {
        return $this->hasMany(StaffStatusHistory::class, 'user_id')->latest('created_at');
    }

    public function isStaffRole(): bool
    {
        return $this->hasRole(UserRole::STAFF);
    }

    /**
     * Get NIP attribute.
     */
    public function getNipAttribute(): ?string
    {
        return $this->staffProfile?->nip;
    }

    /**
     * Get NUPTK attribute.
     */
    public function getNuptkAttribute(): ?string
    {
        return $this->staffProfile?->nuptk;
    }

    /**
     * Get employment position attribute.
     */
    public function getStaffPositionAttribute(): ?StaffPosition
    {
        return $this->staffProfile?->position;
    }

    /**
     * Get NISN attribute.
     */
    public function getNisnAttribute(): ?string
    {
        return $this->studentProfile?->nisn;
    }

    /**
     * Get NIS attribute.
     */
    public function getNisAttribute(): ?string
    {
        return $this->studentProfile?->nis;
    }
}
