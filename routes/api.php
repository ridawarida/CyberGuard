<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimelineController;

use App\Http\Controllers\Admin\HelpCenterController;
use App\Http\Controllers\Admin\HotlineController;
use App\Http\Controllers\AuthController;

Route::get('/timeline/categories', [TimelineController::class, 'categories']);
Route::post('/timeline', [TimelineController::class, 'store']);
Route::get('/timeline/{tracking_id}', [TimelineController::class, 'show']);
Route::post('/timeline/{tracking_id}/report', [TimelineController::class, 'addReport']);
Route::delete('/timeline/{tracking_id}/report/{report_tracking_id}', [TimelineController::class, 'removeReport']);
Route::put('/timeline/{tracking_id}', [TimelineController::class, 'update']);

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