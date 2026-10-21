<?php

namespace App\Jobs\Middleware;

use App\Services\TenantManager;
use Closure;

class SetTenantContext
{
    public function __construct(protected ?int $schoolId) {}

    /**
     * Process the queued job with isolated tenant context.
     */
    public function handle(object $job, Closure $next): mixed
    {
        $tenantManager = app(TenantManager::class);
        $previousSchoolId = $tenantManager->getSchoolId();

        if ($this->schoolId !== null) {
            $tenantManager->setSchoolId($this->schoolId);
        }

        try {
            return $next($job);
        } finally {
            if ($previousSchoolId !== null) {
                $tenantManager->setSchoolId($previousSchoolId);
            } else {
                $tenantManager->reset();
            }
        }
    }
}
