<?php

namespace App\Traits;

use App\Jobs\Middleware\SetTenantContext;
use App\Services\TenantManager;

trait TenantAwareJob
{
    /**
     * Serialized tenant school ID for the queued job.
     */
    public ?int $tenantSchoolId = null;

    /**
     * Initialize tenant context when constructing the job.
     */
    public function withTenantContext(?int $schoolId = null): static
    {
        $this->tenantSchoolId = $schoolId ?? app(TenantManager::class)->getSchoolId();

        return $this;
    }

    /**
     * Middleware array attached to this job to enforce tenant isolation during execution.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new SetTenantContext($this->tenantSchoolId),
        ];
    }
}
