<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// ── Toutes les routes commandes nécessitent JWT ───────────────
Route::middleware('jwt.verify')->group(function () {
    Route::get('/orders',                [OrderController::class, 'index']);
    Route::post('/orders',               [OrderController::class, 'store']);
    Route::get('/orders/{id}',           [OrderController::class, 'show']);
    Route::delete('/orders/{id}',        [OrderController::class, 'destroy']);
    Route::patch('/orders/{id}/status',  [OrderController::class, 'updateStatus']);
});

// ── Health check ──────────────────────────────────────────────
Route::get('/health', fn () => response()->json([
    'service' => 'order-service',
    'status'  => 'ok',
    'time'    => now()->toISOString(),
]));