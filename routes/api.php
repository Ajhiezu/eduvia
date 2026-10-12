<?php

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
});
