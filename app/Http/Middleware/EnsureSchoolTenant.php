<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
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
        $user = $request->user();

        // If no user or super admin, tenant boundary check is bypassed
        if (! $user || $user->hasRole(UserRole::SUPER_ADMIN)) {
            return $next($request);
        }

        // Check if multi-school tenancy is enforced via configuration
        $isMultiSchoolEnabled = config('app.multi_school_mode', false);

        if ($isMultiSchoolEnabled) {
            $schoolId = $request->header('X-School-ID') ?? session('current_school_id');

            if (! $schoolId) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Konteks sekolah (tenant) tidak ditemukan atau belum dipilih.',
                        'errors' => null,
                    ], 400);
                }

                // In web mode, prompt to select school if required in future phase
            }
        }

        return $next($request);
    }
}
