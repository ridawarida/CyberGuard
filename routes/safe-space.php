<?php

/**
 * Interactive "Digital Safe Space" routes.
 * Module 2, feature owner: Johra-E-Jannat Oishy.
 *
 * Kept separate just like routes/panic.php from Module 1 so this feature is
 * easy to review and less likely to conflict with teammates' route changes.
 */

use App\Http\Controllers\SafeSpaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('safe-space')->name('safe-space.')->group(function () {

    // Public standalone calming dashboard.
    Route::get('/', [SafeSpaceController::class, 'index'])->name('index');

    // Browser requests a fresh displayed quote asynchronously.
    // The controller caches the external ZenQuotes batch for one hour.
    Route::get('/quote', [SafeSpaceController::class, 'quote'])
        ->middleware('throttle:30,1')
        ->name('quote');
});
