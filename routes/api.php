<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\StudentController;
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
        Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    // Authenticated Mobile Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        // Student Mobile App Endpoints (guarded with role:student ability)
        Route::middleware('ability:role:student')->prefix('student')->name('student.')->group(function () {
            Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
            Route::get('/schedules', [StudentController::class, 'schedules'])->name('schedules');
            Route::get('/materials', [StudentController::class, 'materials'])->name('materials');
            Route::get('/assignments', [StudentController::class, 'assignments'])->name('assignments');
            Route::get('/cbt', [StudentController::class, 'cbt'])->name('cbt');
            Route::post('/cbt/{exam}/start', [StudentController::class, 'startCbt'])->name('cbt.start');
            Route::post('/cbt/{exam}/save-answer', [StudentController::class, 'saveCbtAnswer'])->name('cbt.save_answer');
            Route::post('/cbt/{exam}/finish', [StudentController::class, 'finishCbt'])->name('cbt.finish');
        });

        // Parent Mobile App Endpoints (guarded with role:parent ability)
        Route::middleware('ability:role:parent')->prefix('parent')->name('parent.')->group(function () {
            Route::get('/children', [ParentController::class, 'children'])->name('children');
            Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
            Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
            Route::get('/finance', [ParentController::class, 'finance'])->name('finance');
        });
    });
});
