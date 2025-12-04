<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TwoFactorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Two Factor Authentication
    Route::prefix('2fa')->group(function () {
        Route::post('/enable', [TwoFactorController::class, 'enable']);
        Route::post('/confirm', [TwoFactorController::class, 'confirm']);
        Route::delete('/disable', [TwoFactorController::class, 'disable']);
        Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes']);
        Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes']);
    });
});
