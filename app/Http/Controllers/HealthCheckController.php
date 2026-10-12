<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $isHealthy = ($dbStatus === 'healthy');

        $payload = [
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'app' => [
                'name' => config('app.name', 'Eduvia'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'version' => '1.0.0-phase0',
                'framework' => 'Laravel '.app()->version(),
                'php' => PHP_VERSION,
            ],
            'services' => [
                'database' => [
                    'connection' => config('database.default'),
                    'status' => $dbStatus,
                    'latency_ms' => $dbLatencyMs,
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
