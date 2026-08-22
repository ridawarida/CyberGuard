<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaseFileController;

use App\Http\Controllers\Admin\HelpCenterController;
use App\Http\Controllers\Admin\HotlineController;
use App\Http\Controllers\AuthController;

Route::get('/case-files/categories', [CaseFileController::class, 'categories']);
Route::post('/case-files', [CaseFileController::class, 'store']);
Route::get('/case-files/{tracking_id}', [CaseFileController::class, 'show']);
Route::post('/case-files/{tracking_id}/incidents', [CaseFileController::class, 'addIncident']);
Route::delete('/case-files/{tracking_id}/incidents/{incident_tracking_id}', [CaseFileController::class, 'removeIncident']);
Route::put('/case-files/{tracking_id}', [CaseFileController::class, 'update']);

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Help Centers
        Route::get('/help-centers', [HelpCenterController::class, 'index']);
        Route::post('/help-centers', [HelpCenterController::class, 'store']);
        Route::get('/help-centers/{id}', [HelpCenterController::class, 'show']);
        Route::put('/help-centers/{id}', [HelpCenterController::class, 'update']);
        Route::delete('/help-centers/{id}', [HelpCenterController::class, 'destroy']);

        // Hotlines
        Route::get('/hotlines', [HotlineController::class, 'index']);
        Route::post('/hotlines', [HotlineController::class, 'store']);
        Route::put('/hotlines/{id}', [HotlineController::class, 'update']);
        Route::delete('/hotlines/{id}', [HotlineController::class, 'destroy']);
    });
});