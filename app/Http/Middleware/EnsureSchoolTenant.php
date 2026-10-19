<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\School;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Multi-School Tenant Readiness Middleware.
 *
 * Enforces data isolation boundary when multi-school mode is active.
 * In Phase 0, this provides the hook and contract without premature coupling.
 */
class EnsureSchoolTenant
{
    /**
     * Handle tenant identification and isolation boundary.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantManager = app(TenantManager::class);
        $user = $request->user();

        if ($user) {
            if ($user->hasRole(UserRole::SUPER_ADMIN)) {
                // Super Admin can optionally focus on a specific school via header or session
                $targetSchoolId = $request->header('X-School-ID') ?? session('active_school_id');
                if ($targetSchoolId) {
                    $tenantManager->setSchoolId((int) $targetSchoolId);
                }
            } else {
                // Non-super admins are strictly locked to their assigned school
                if ($user->school_id) {
                    $tenantManager->setSchoolId((int) $user->school_id);
                } elseif (session()->has('current_school_id')) {
                    $tenantManager->setSchoolId((int) session('current_school_id'));
                }
            }
        } else {
            // Guest or public route
            $routeSchool = $request->route('school');
            if ($routeSchool instanceof School) {
                $tenantManager->setSchool($routeSchool);
            }
        }

        return $next($request);
    }
}
