<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaseFileWizardController;
use App\Http\Controllers\CaseFileController;
use App\Http\Controllers\IncidentWizardController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HelpDirectoryController;
use App\Http\Controllers\Moderator\IncidentReviewController;
use App\Models\CaseFile;
use App\Http\Controllers\TicketStatusController;
use App\Http\Controllers\HelpCenterDirectoryController;
use App\Http\Controllers\RecoveryJournalController;
use App\Http\Middleware\PreventSensitiveCaching;
use App\Http\Controllers\PlatformPolicyController;

// Guest/Anonymous routes for case files
Route::get('/case-files/create', function () {
    return view('case-file.create');
})->name('case-file.create');

// Case File Wizard Routes
Route::prefix('case-files/wizard')->name('case-file.wizard.')->group(function () {
    Route::get('/step1', [CaseFileWizardController::class, 'step1'])->name('step1');
    Route::post('/step1', [CaseFileWizardController::class, 'postStep1'])->name('postStep1');
    
    Route::get('/step2', [CaseFileWizardController::class, 'step2'])->name('step2');
    Route::post('/add-incident', [CaseFileWizardController::class, 'addIncident'])->name('addIncident');
    Route::post('/remove-incident/{index}', [CaseFileWizardController::class, 'removeIncident'])->name('removeIncident');
    Route::post('/step2', [CaseFileWizardController::class, 'postStep2'])->name('postStep2');
    
    Route::get('/step3', [CaseFileWizardController::class, 'step3'])->name('step3');
    Route::post('/step3', [CaseFileWizardController::class, 'postStep3'])->name('postStep3');
    
    Route::get('/success', [CaseFileWizardController::class, 'success'])->name('success');
    Route::get('/reset', [CaseFileWizardController::class, 'reset'])->name('reset');
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
    Route::get('/redact', [IncidentWizardController::class, 'redact'])->name('redact');
    Route::post('/redact', [IncidentWizardController::class, 'postRedact'])->name('postRedact');
});

// Anonymous Ticket Status Tracking Portal
Route::get('/ticket-status', [TicketStatusController::class, 'index'])->name('ticket.status.index');
Route::post('/ticket-status', [TicketStatusController::class, 'search'])->name('ticket.status.search');

// View existing case file
Route::get('/case-files/view/{tracking_id}', function ($tracking_id) {
    $caseFile = CaseFile::with('incidents')->byTrackingId($tracking_id)->first();
    
    if (!$caseFile) {
        abort(404, 'Case file not found');
    }
    return view('case-file.view', ['caseFile' => $caseFile]);
})->name('case-file.view');

Route::post('/case-files/{tracking_id}/remove-incidents', [CaseFileController::class, 'removeIncidents'])
    ->name('case-file.removeIncidents');

//home default
Route::get('/help-centers', [HelpCenterDirectoryController::class, 'index'])->name('help-centers.index');

Route::prefix('recovery-journal')->name('recovery-journal.')->group(function () {
    Route::get('/', [RecoveryJournalController::class, 'index'])->name('index')->middleware(PreventSensitiveCaching::class);
    Route::post('/start', [RecoveryJournalController::class, 'start'])->name('start')->middleware('throttle:10,1', PreventSensitiveCaching::class);
    Route::post('/unlock', [RecoveryJournalController::class, 'unlock'])->name('unlock')->middleware('throttle:10,1', PreventSensitiveCaching::class);
    Route::post('/entries', [RecoveryJournalController::class, 'storeEntry'])->name('entries.store')->middleware(PreventSensitiveCaching::class);
    Route::put('/entries/{entry}', [RecoveryJournalController::class, 'updateEntry'])->name('entries.update')->middleware(PreventSensitiveCaching::class);
    Route::delete('/entries/{entry}', [RecoveryJournalController::class, 'destroyEntry'])->name('entries.destroy')->middleware(PreventSensitiveCaching::class);
    Route::post('/forget', [RecoveryJournalController::class, 'forget'])->name('forget')->middleware(PreventSensitiveCaching::class);
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/case-files/delete', [CaseFileController::class, 'destroy'])->name('case-file.delete');

// Johra - Module 1: Quick Escape Panic Button routes.
require __DIR__ . '/panic.php';
/*
|--------------------------------------------------------------------------
| Staff session login (Anika, Module 2)
|--------------------------------------------------------------------------
| The API in routes/api.php uses Sanctum tokens. Browser pages need a normal
| session, so staff sign in here.
*/
Route::get('/staff/login', [StaffAuthController::class, 'showLogin'])->name('staff.login');
Route::post('/staff/login', [StaffAuthController::class, 'login'])->name('staff.login.submit');
Route::post('/staff/logout', [StaffAuthController::class, 'logout'])->name('staff.logout');

Route::middleware('staff:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/help-directory', [HelpDirectoryController::class, 'index'])->name('help-directory.index');
    Route::post('/help-directory', [HelpDirectoryController::class, 'store'])->name('help-directory.store');
    Route::put('/help-directory/{helpCenter}', [HelpDirectoryController::class, 'update'])->name('help-directory.update');
    Route::delete('/help-directory/{helpCenter}', [HelpDirectoryController::class, 'destroy'])->name('help-directory.destroy');
});

/*
|--------------------------------------------------------------------------
| Moderator Incident Assessment and Case Lifecycle Updates (Anika, Module 2)
|--------------------------------------------------------------------------
*/
Route::middleware('staff:moderator,admin')
    ->prefix('moderator/incidents')
    ->name('moderator.incidents.')
    ->group(function () {
        Route::get('/', [IncidentReviewController::class, 'index'])->name('index');
        Route::get('/{incident}', [IncidentReviewController::class, 'show'])->name('show');
        Route::post('/{incident}/claim', [IncidentReviewController::class, 'claim'])->name('claim');
        Route::post('/{incident}/release', [IncidentReviewController::class, 'release'])->name('release');
        Route::put('/{incident}', [IncidentReviewController::class, 'update'])->name('update');
        Route::post('/{incident}/scan-threats', [IncidentReviewController::class, 'scanThreats'])
            ->name('scan-threats');
    });
/*
|--------------------------------------------------------------------------
| Moderator Platform Policy Management
|--------------------------------------------------------------------------
*/
Route::middleware('staff:moderator,admin')
    ->prefix('moderator/platform-policies')
    ->name('moderator.platform-policies.')
    ->group(function () {

        Route::get('/', [PlatformPolicyController::class, 'index'])
            ->name('index');

        Route::get('/create', [PlatformPolicyController::class, 'create'])
            ->name('create');

        Route::post('/', [PlatformPolicyController::class, 'store'])
            ->name('store');

        Route::get('/{platformPolicy}/edit', [PlatformPolicyController::class, 'edit'])
            ->name('edit');

        Route::put('/{platformPolicy}', [PlatformPolicyController::class, 'update'])
            ->name('update');

        Route::delete('/{platformPolicy}', [PlatformPolicyController::class, 'destroy'])
            ->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Public Platform Policies
|--------------------------------------------------------------------------
*/
Route::get('/platform-policies', [PlatformPolicyController::class, 'userIndex'])
    ->name('platform-policies.index');


// Johra - Module 2: Digital Safe Space routes.
require __DIR__ . '/safe-space.php';
require __DIR__.'/consultation.php';
require __DIR__.'/case-export.php';
