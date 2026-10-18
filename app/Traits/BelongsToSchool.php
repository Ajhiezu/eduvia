<?php

namespace App\Traits;

use App\Models\School;
use App\Scopes\SchoolTenantScope;
use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    /**
     * Boot the trait to attach global scope and auto-assign active tenant on creation.
     */
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolTenantScope);

        static::creating(function ($model) {
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->isTenancyActive()) {
                $currentSchoolId = $tenantManager->getSchoolId();
                if ($currentSchoolId !== null) {
                    // Force the active tenant school_id, preventing ID spoofing
                    $model->school_id = $currentSchoolId;
                }
            }
        });
    }

    /**
     * Get the school that owns this entity.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope a query to bypass the school tenant isolation.
     */
    public function scopeWithoutTenancy(Builder $query): Builder
    {
        return $query->withoutGlobalScope(SchoolTenantScope::class);
    }
}
