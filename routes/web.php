<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimelineWizardController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\IncidentWizardController;
use App\Models\Timeline;

// Guest/Anonymous routes for Timeline
Route::get('/timeline/create', function () {
    return view('timeline.create');
})->name('timeline.create');

// Timeline Wizard Routes
Route::prefix('timeline/wizard')->name('timeline.wizard.')->group(function () {
    Route::get('/step1', [TimelineWizardController::class, 'step1'])->name('step1');
    Route::post('/step1', [TimelineWizardController::class, 'postStep1'])->name('postStep1');
    
    Route::get('/step2', [TimelineWizardController::class, 'step2'])->name('step2');
    Route::post('/add-incident', [TimelineWizardController::class, 'addIncident'])->name('addIncident');
    Route::post('/remove-incident/{index}', [TimelineWizardController::class, 'removeIncident'])->name('removeIncident');
    Route::post('/step2', [TimelineWizardController::class, 'postStep2'])->name('postStep2');
    
    Route::get('/step3', [TimelineWizardController::class, 'step3'])->name('step3');
    Route::post('/step3', [TimelineWizardController::class, 'postStep3'])->name('postStep3');
    
    Route::get('/success', [TimelineWizardController::class, 'success'])->name('success');
    Route::get('/reset', [TimelineWizardController::class, 'reset'])->name('reset');
});

// Incident Wizard Routes
Route::prefix('incident/wizard')->name('incident.wizard.')->group(function () {
    Route::get('/step1', [IncidentWizardController::class, 'step1'])->name('step1');
    Route::post('/step1', [IncidentWizardController::class, 'postStep1'])->name('postStep1');
    Route::get('/step2', [IncidentWizardController::class, 'step2'])->name('step2');
    Route::post('/step2', [IncidentWizardController::class, 'postStep2'])->name('postStep2');
    Route::get('/step3', [IncidentWizardController::class, 'step3'])->name('step3');
    Route::post('/step3', [IncidentWizardController::class, 'postStep3'])->name('postStep3');
    Route::get('/success', [IncidentWizardController::class, 'success'])->name('success');
});

// View existing timeline
Route::get('/timeline/view/{tracking_id}', function ($tracking_id) {
    $timeline = Timeline::with('incidents')->byTrackingId($tracking_id)->first();
    
    if (!$timeline) {
        abort(404, 'Timeline not found');
    }
    return view('timeline.view', ['timeline' => $timeline]);
})->name('timeline.view');

Route::post('/timeline/{tracking_id}/remove-incidents', [TimelineController::class, 'removeIncidents'])
    ->name('timeline.removeIncidents');

//home default
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/timeline/delete', [TimelineController::class, 'destroy'])->name('timeline.delete');

// Johra - Module 1: Quick Escape Panic Button routes.
require __DIR__ . '/panic.php';
    
