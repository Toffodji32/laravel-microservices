<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ── Routes publiques (pas besoin de token) ────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── Routes protégées (token JWT obligatoire) ──────────────────────
Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/me',       [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// ── Health check ──────────────────────────────────────────────────
Route::get('/health', fn () => response()->json([
    'service' => 'auth-service',
    'status'  => 'ok',
    'time'    => now()->toISOString(),
]));