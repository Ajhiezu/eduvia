<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Eduvia Platform
|--------------------------------------------------------------------------
|
| Versioned endpoints adhering to Phase 0 API Architecture conventions:
| - Consistent JSON responses via ApiResponse trait
| - Standard HTTP status codes
| - Defense-in-depth rate limiting
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public Health Check & Observability Endpoint
    Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

    // Authentication Endpoints
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });
});
