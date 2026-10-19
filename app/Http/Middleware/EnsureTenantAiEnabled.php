<?php

namespace App\Http\Middleware;

use App\Services\TenantFeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAiEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: akun tidak terafiliasi dengan sekolah.',
                ], 403);
            }
            abort(403, 'Akses ditolak: akun tidak terafiliasi dengan sekolah.');
        }

        if (! TenantFeatureFlagService::isEnabled(TenantFeatureFlagService::AI_ASSISTANT, $user->school_id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur Asisten AI dinonaktifkan untuk sekolah ini. Hubungi administrator sekolah.',
                ], 403);
            }

            abort(403, 'Fitur Asisten AI sedang dinonaktifkan untuk sekolah Anda.');
        }

        return $next($request);
    }
}
