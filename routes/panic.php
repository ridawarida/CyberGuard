<?php

/**
 * Quick Escape "Panic Button" routes.
 * Module 1, feature owner: Johra-E-Jannat Oishy.
 *
 * Kept in a separate file and pulled into routes/web.php with a single require
 * line, so this feature never fights with anybody else's route edits in git.
 */

use App\Http\Controllers\Admin\PanicSettingController;
use App\Http\Controllers\PanicController;
use Illuminate\Support\Facades\Route;

Route::prefix('panic')->name('panic.')->group(function () {

    // Public. The floating button reads this on page load.
    Route::get('/config', [PanicController::class, 'config'])->name('config');

    // Public. Fired by the button click or the double Escape hotkey.
    // Throttled so a script cannot hammer the session store.
    Route::post('/trigger', [PanicController::class, 'trigger'])
        ->middleware('throttle:60,1')
        ->name('trigger');

    // Public. No JavaScript fallback, posted from a plain Blade form.
    Route::post('/escape', [PanicController::class, 'escape'])
        ->middleware('throttle:60,1')
        ->name('escape');

    // Local demo page used for the lab evaluation. Never exposed in production.
    if (app()->environment('local')) {
        Route::get('/demo', function () {
            return view('panic.demo', [
                'draft' => session('incident_wizard.description'),
                'config' => \App\Models\PanicSetting::active()->toClientPayload(),
            ]);
        })->name('demo');

        Route::post('/demo', function (\Illuminate\Http\Request $request) {
            session(['incident_wizard.description' => $request->input('description')]);

            return redirect()->route('panic.demo');
        })->name('demo.store');
    }

    // Admin only configuration and metrics.
    Route::middleware(['auth:sanctum', 'role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/settings', [PanicSettingController::class, 'show'])->name('settings.show');
            Route::put('/settings', [PanicSettingController::class, 'update'])->name('settings.update');
            Route::get('/stats', [PanicSettingController::class, 'stats'])->name('stats');
        });
});
