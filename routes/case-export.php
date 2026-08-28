<?php

/**
 * Automated Legal and Institutional PDF Case Exporter routes.
 * Module 3, feature owner: Johra-E-Jannat Oishy.
 */

use App\Http\Controllers\CaseExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('consult/session/export')
    ->name('consult.export.')
    ->middleware('victim.session')
    ->group(function () {
        Route::get('/', [CaseExportController::class, 'form'])->name('form');
        Route::post('/', [CaseExportController::class, 'generate'])->name('generate');
    });
