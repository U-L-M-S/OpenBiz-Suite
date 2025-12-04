<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TwoFactorController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\TimesheetController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AssetCategoryController;
use App\Http\Controllers\Api\V1\AssetAssignmentController;
use App\Http\Controllers\Api\V1\AssetMaintenanceController;
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

    // HR Module
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);

    // Timesheets
    Route::apiResource('timesheets', TimesheetController::class);
    Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve']);
    Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject']);

    // Leave Requests
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);

    // Asset Module
    Route::apiResource('assets', AssetController::class);
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign']);

    Route::apiResource('asset-categories', AssetCategoryController::class);

    Route::get('/asset-assignments', [AssetAssignmentController::class, 'index']);
    Route::get('/asset-assignments/{assetAssignment}', [AssetAssignmentController::class, 'show']);
    Route::post('/asset-assignments/{assetAssignment}/return', [AssetAssignmentController::class, 'returnAsset']);

    Route::apiResource('asset-maintenances', AssetMaintenanceController::class);
    Route::post('/asset-maintenances/{assetMaintenance}/complete', [AssetMaintenanceController::class, 'complete']);
});
