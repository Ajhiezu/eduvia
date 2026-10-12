<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Eduvia Platform
|--------------------------------------------------------------------------
*/

// Public Welcome / Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Direct Health Status
Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

// UI Foundation: Interactive Design System Showcase
Route::get('/design-system', function () {
    return view('design-system');
})->name('design-system');

// Authentication Foundation
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Shell (Phase 0 Foundation)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Foundation Verification Endpoint: Restricted to Administrative roles
    Route::get('/restricted-admin-demo', function () {
        return response()->json([
            'success' => true,
            'message' => 'Akses administrator berhasil diverifikasi.',
            'user' => [
                'name' => auth()->user()->name,
                'role' => auth()->user()->role->value,
            ],
        ]);
    })->middleware('role:super_admin,school_admin')->name('demo.admin');
});
