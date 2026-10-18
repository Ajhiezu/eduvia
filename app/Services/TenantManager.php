<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    protected ?School $currentSchool = null;

    protected ?int $currentSchoolId = null;

    protected bool $bypass = false;

    /**
     * Set the active school tenant explicitly.
     */
    public function setSchool(?School $school): self
    {
        $this->currentSchool = $school;
        $this->currentSchoolId = $school?->id;

        return $this;
    }

    /**
     * Set the active school tenant ID explicitly.
     */
    public function setSchoolId(?int $schoolId): self
    {
        $this->currentSchoolId = $schoolId;

        if ($schoolId && (! $this->currentSchool || $this->currentSchool->id !== $schoolId)) {
            $this->currentSchool = School::find($schoolId);
        } elseif (! $schoolId) {
            $this->currentSchool = null;
        }

        return $this;
    }

    /**
     * Get the resolved active school ID.
     */
    public function getSchoolId(): ?int
    {
        if ($this->bypass) {
            return null;
        }

        if ($this->currentSchoolId !== null) {
            return $this->currentSchoolId;
        }

        // 1. Authenticated user's school context
        $user = auth()->user();
        if ($user) {
            if ($user->hasRole(UserRole::SUPER_ADMIN)) {
                // If super admin has switched to a specific school via session/header
                if (session()->has('active_school_id')) {
                    $this->currentSchoolId = (int) session('active_school_id');

                    return $this->currentSchoolId;
                }

                // Super Admin has global scope unless explicit tenant selected
                return null;
            }

            if ($user->school_id) {
                $this->currentSchoolId = (int) $user->school_id;

                return $this->currentSchoolId;
            }
        }

        // 2. Active school selected in session (e.g. for multi-school switchers)
        if (session()->has('current_school_id')) {
            $this->currentSchoolId = (int) session('current_school_id');

            return $this->currentSchoolId;
        }

        return null;
    }

    /**
     * Get the resolved active School model instance.
     */
    public function getSchool(): ?School
    {
        if ($this->bypass) {
            return null;
        }

        $schoolId = $this->getSchoolId();
        if ($schoolId) {
            if (! $this->currentSchool || $this->currentSchool->id !== $schoolId) {
                $this->currentSchool = School::find($schoolId);
            }

            return $this->currentSchool;
        }

        // Unauthenticated public route fallback (e.g. Nusantara Utama homepage)
        return $this->currentSchool ?? School::where('is_active', true)->first();
    }

    /**
     * Determine if tenant isolation is currently active.
     */
    public function isTenancyActive(): bool
    {
        return ! $this->bypass && $this->getSchoolId() !== null;
    }

    /**
     * Execute a callback with tenant scoping temporarily bypassed.
     * Every bypass is logged for security auditing.
     */
    public function withoutTenancy(callable $callback, string $reason = 'System global operation'): mixed
    {
        $previousBypass = $this->bypass;
        $this->bypass = true;

        Log::info("Tenant isolation bypassed: {$reason}", [
            'user_id' => auth()->id(),
            'role' => auth()->user()?->role?->value ?? 'cli/system',
            'timestamp' => now()->toIso8601String(),
        ]);

        try {
            return $callback();
        } finally {
            $this->bypass = $previousBypass;
        }
    }

    /**
     * Execute a callback in the context of a specific school.
     */
    public function forSchool(School $school, callable $callback): mixed
    {
        $previousSchool = $this->currentSchool;
        $previousSchoolId = $this->currentSchoolId;
        $previousBypass = $this->bypass;

        $this->setSchool($school);
        $this->bypass = false;

        try {
            return $callback();
        } finally {
            $this->currentSchool = $previousSchool;
            $this->currentSchoolId = $previousSchoolId;
            $this->bypass = $previousBypass;
        }
    }
}
