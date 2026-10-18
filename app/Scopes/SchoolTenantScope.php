<?php

namespace App\Scopes;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolTenantScope implements Scope
{
    /**
     * Apply tenant isolation filter to Eloquent queries.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantManager = app(TenantManager::class);

        if ($tenantManager->isTenancyActive()) {
            $schoolId = $tenantManager->getSchoolId();
            if ($schoolId !== null) {
                $builder->where($model->qualifyColumn('school_id'), $schoolId);
            }
        }
    }
}
