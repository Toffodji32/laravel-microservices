<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// ── Routes publiques (sans token) ────────────────────────────────
Route::get('/products',      [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ── Routes protégées (token JWT obligatoire) ──────────────────────
Route::middleware('jwt.verify')->group(function () {
    Route::post('/products',             [ProductController::class, 'store']);
    Route::put('/products/{id}',         [ProductController::class, 'update']);
    Route::delete('/products/{id}',      [ProductController::class, 'destroy']);
    Route::patch('/products/{id}/stock', [ProductController::class, 'updateStock']);
});

// ── Health check ──────────────────────────────────────────────────
Route::get('/health', fn () => response()->json([
    'service' => 'product-service',
    'status'  => 'ok',
    'time'    => now()->toISOString(),
]));