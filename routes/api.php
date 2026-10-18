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
            Route::get('/materials/{material}/attachments/{attachment}/download', [StudentController::class, 'downloadMaterialAttachment'])->name('materials.attachment.download');
            Route::get('/assignments', [StudentController::class, 'assignments'])->name('assignments');
            Route::post('/assignments/{assignment}/submit', [StudentController::class, 'submitAssignment'])->name('assignments.submit');
            Route::get('/reports', [StudentController::class, 'reports'])->name('reports');
            Route::get('/conversations', [StudentController::class, 'conversations'])->name('conversations');
            Route::get('/conversations/{conversation}/messages', [StudentController::class, 'conversationMessages'])->name('conversations.messages');
            Route::post('/conversations/{conversation}/messages', [StudentController::class, 'sendConversationMessage'])->name('conversations.messages.send');
            Route::get('/cbt', [StudentController::class, 'cbt'])->name('cbt');
            Route::post('/cbt/{exam}/start', [StudentController::class, 'startCbt'])->name('cbt.start');
            Route::post('/cbt/{exam}/save-answer', [StudentController::class, 'saveCbtAnswer'])->name('cbt.save_answer');
            Route::post('/cbt/{exam}/finish', [StudentController::class, 'finishCbt'])->name('cbt.finish');
        });

        // Parent Mobile App Endpoints (guarded with role:parent ability)
        Route::middleware('ability:role:parent')->prefix('parent')->name('parent.')->group(function () {
            Route::get('/children', [ParentController::class, 'children'])->name('children');
            Route::post('/claim-code', [ParentController::class, 'claimCode'])->name('claim_code')->middleware('throttle:5,1');
            Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
            Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
            Route::get('/finance', [ParentController::class, 'finance'])->name('finance');
            Route::post('/invoices/{invoice}/pay', [ParentController::class, 'submitInvoicePayment'])->name('invoices.pay');
            Route::get('/reports', [ParentController::class, 'reports'])->name('reports');
            Route::get('/conversations', [ParentController::class, 'conversations'])->name('conversations');
            Route::get('/conversations/{conversation}/messages', [ParentController::class, 'conversationMessages'])->name('conversations.messages');
            Route::post('/conversations/{conversation}/messages', [ParentController::class, 'sendConversationMessage'])->name('conversations.messages.send');
        });
    });
});

