<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request and check if user has allowed role.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi diperlukan untuk mengakses sumber daya ini.',
                    'errors' => null,
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        if (! $user->isActive()) {
            auth()->logout();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda berstatus non-aktif. Silakan hubungi administrator.',
                    'errors' => null,
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // If no specific role argument was given, any authenticated active user passes
        if (empty($roles)) {
            return $next($request);
        }

        // Super Admin always has global access
        if ($user->hasRole(UserRole::SUPER_ADMIN)) {
            return $next($request);
        }

        // Check if user has any of the requested roles
        if (! $user->hasRole(...$roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki izin untuk tindakan ini.',
                    'errors' => null,
                ], 403);
            }

            abort(403, 'Akses ditolak: Peran Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
