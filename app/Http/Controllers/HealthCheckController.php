<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    use ApiResponse;

    /**
     * Return comprehensive application health status for monitoring & DevOps.
     */
    public function check(Request $request): JsonResponse
    {
        $dbStatus = 'healthy';
        $dbLatencyMs = 0;

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
        } catch (Throwable $e) {
            $dbStatus = 'unhealthy';
        }

        // Cache check
        $cacheStatus = 'healthy';
        try {
            $testKey = 'health_ping_'.microtime(true);
            Cache::put($testKey, 'ok', 10);
            $cacheStatus = Cache::get($testKey) === 'ok' ? 'healthy' : 'unhealthy';
            Cache::forget($testKey);
        } catch (Throwable $e) {
            $cacheStatus = 'unhealthy';
        }

        // Storage writeability check
        $storageStatus = 'healthy';
        try {
            $disk = Storage::disk('local');
            $testFile = 'health_check_'.microtime(true).'.txt';
            $disk->put($testFile, 'health-check');
            $storageStatus = $disk->exists($testFile) ? 'healthy' : 'unhealthy';
            $disk->delete($testFile);
        } catch (Throwable $e) {
            $storageStatus = 'unhealthy';
        }

        $activeSchoolsCount = 0;
        try {
            $activeSchoolsCount = School::where('is_active', true)->count();
        } catch (Throwable $e) {
            //
        }

        $isHealthy = ($dbStatus === 'healthy' && $cacheStatus === 'healthy' && $storageStatus === 'healthy');

        $payload = [
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'app' => [
                'name' => config('app.name', 'Eduvia'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'version' => '1.0.0-phase1',
                'framework' => 'Laravel '.app()->version(),
                'php' => PHP_VERSION,
            ],
            'services' => [
                'database' => [
                    'connection' => config('database.default'),
                    'status' => $dbStatus,
                    'latency_ms' => $dbLatencyMs,
                ],
                'cache' => [
                    'driver' => config('cache.default'),
                    'status' => $cacheStatus,
                ],
                'storage' => [
                    'disk' => 'local',
                    'status' => $storageStatus,
                ],
                'multi_tenancy' => [
                    'status' => 'operational',
                    'active_schools_count' => $activeSchoolsCount,
                ],
            ],
            'system' => [
                'timezone' => config('app.timezone', 'Asia/Jakarta'),
                'server_time' => now()->toIso8601String(),
                'uptime' => 'operational',
            ],
        ];

        return $this->apiSuccess(
            data: $payload,
            message: $isHealthy ? 'Seluruh layanan sistem Eduvia beroperasi normal.' : 'Sebagian layanan mengalami penurunan performa.',
            statusCode: $isHealthy ? 200 : 503
        );
    }
}
