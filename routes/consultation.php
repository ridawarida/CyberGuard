<?php

/**
 * Secure Consultation Workspace routes.
 * Module 3, feature owner: Johra-E-Jannat Oishy.
 *
 * Kept in its own file and pulled into routes/web.php with a single
 * require line, the same convention as routes/panic.php and
 * routes/calm.php.
 */

use App\Http\Controllers\ConsultationAccessController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\Moderator\ConsultationController as ModeratorConsultationController;
use Illuminate\Support\Facades\Route;

Route::prefix('consult')->name('consult.')->group(function () {

    // Public. Where a victim enters the access key they were given.
    Route::get('/', [ConsultationAccessController::class, 'show'])->name('access');
    Route::post('/', [ConsultationAccessController::class, 'authenticate'])
        ->middleware('throttle:10,1')
        ->name('access.submit');
    Route::post('/logout', [ConsultationAccessController::class, 'logout'])->name('access.logout');

    // The victim's own thread. Gated on the session set above, not on
    // anything in the URL or request body.
    Route::middleware('victim.session')->group(function () {
        Route::get('/session', [ConsultationController::class, 'show'])->name('session');
        Route::get('/session/messages', [ConsultationController::class, 'poll'])->name('session.poll');
        Route::post('/session/messages', [ConsultationController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('session.send');
    });
});

// Moderator side. auth:sanctum + role:moderator to match the guard/role
// middleware Module 1's admin routes already use in this app.
Route::middleware(['auth:sanctum', 'role:moderator'])
    ->prefix('moderator/consultations')
    ->name('moderator.consultations.')
    ->group(function () {
        Route::get('/', [ModeratorConsultationController::class, 'index'])->name('index');
        Route::get('/{consultation}', [ModeratorConsultationController::class, 'show'])->name('show');
        Route::get('/{consultation}/messages', [ModeratorConsultationController::class, 'poll'])->name('poll');
        Route::post('/{consultation}/messages', [ModeratorConsultationController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('send');
    });
