<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CaseFileWizardController extends Controller
{
    /**
     * Show the wizard step 1
     */
    public function step1()
    {
        Session::forget('case_file_wizard');
        Session::forget('case_file_incidents');

        return view('case-file.wizard', [
            'step' => 1,
            'data' => Session::get('case_file_wizard', [])
        ]);
    }

    /**
     * Process step 1 and move to step 2
     */
    public function postStep1(Request $request)
    {
        $request->validate([
            'action' => 'required|in:existing,new'
        ]);

        $data = Session::get('case_file_wizard', []);

        if ($request->action === 'existing') {
            $request->validate([
                'case_file_token' => 'required|string'
            ]);

            // Verify the case file exists
            $caseFile = CaseFile::byTrackingId($request->case_file_token)->first();
            if (!$caseFile) {
                return back()->withErrors([
                    'case_file_token' => 'Case file not found. Please check your token.'
                ]);
            }

            $data['case_file_token'] = $request->case_file_token;
            $data['is_new'] = false;
            $data['case_file_id'] = $caseFile->id;
        } else {
            $data['is_new'] = true;
        }

        Session::put('case_file_wizard', $data);

        return redirect()->route('case-file.wizard.step2');
    }

    /**
     * Show the wizard step 2
     */
    public function step2()
    {
        $data = Session::get('case_file_wizard', []);

        if (empty($data)) {
            return redirect()->route('case-file.wizard.step1');
        }

        return view('case-file.wizard', [
            'step' => 2,
            'data' => $data,
            'incidents' => Session::get('case_file_incidents', [])
        ]);
    }

    /**
     * Process step 2 - add an incident
     */
    public function addIncident(Request $request)
    {
        $request->validate([
        'incident_token' => 'required|string'
    ]);

    // Verify the incident exists in the database
    $incident = Incident::byTrackingId($request->incident_token)->first();
    if (!$incident) {
        return back()->withErrors([
            'incident_token' => 'Incident not found. Please check your token.'
        ]);
    }

    // Check if incident already belongs to a case file.
    if ($incident->hasCaseFile()) {
        $currentCaseFile = $incident->getCurrentCaseFile();
        $currentToken = $currentCaseFile ? $currentCaseFile->tracking_id : 'unknown';
        
        return back()->withErrors([
            'incident_token' => "This incident already belongs to case file '{$currentToken}'. Each incident can only be in one case file."
        ]);
    }

    // Check if already added to current session
    $incidents = Session::get('case_file_incidents', []);
    foreach ($incidents as $existing) {
        if ($existing['token'] === $request->incident_token) {
            return back()->withErrors([
                'incident_token' => 'This incident has already been added to the current case file.'
            ]);
        }
    }

        $incidents[] = [
            'token' => $request->incident_token,
            'overview' => $incident->overview ?? $incident->description,
            'incident_date' => $incident->incident_date->format('Y-m-d H:i:s'),
            'platform' => $incident->platform,
            'region' => $incident->region,
            'behavior_type' => $incident->behavior_type,
            'added_at' => now()->toDateTimeString()
        ];

        Session::put('case_file_incidents', $incidents);

        return redirect()->route('case-file.wizard.step2')->with('success', 'Incident added successfully!');
    }

    /**
     * Remove an incident from the list
     */
    public function removeIncident($index)
    {
        $incidents = Session::get('case_file_incidents', []);

        if (isset($incidents[$index])) {
            unset($incidents[$index]);
            $incidents = array_values($incidents);
            Session::put('case_file_incidents', $incidents);
        }

        return redirect()->route('case-file.wizard.step2');
    }

    /**
     * Process step 2 and move to step 3
     */
    public function postStep2(Request $request)
    {
        $data = Session::get('case_file_wizard', []);

        if (empty($data)) {
            return redirect()->route('case-file.wizard.step1');
        }

        return redirect()->route('case-file.wizard.step3');
    }

    /**
     * Show the wizard step 3
     */
    public function step3()
    {
        $data = Session::get('case_file_wizard', []);

        if (empty($data)) {
            return redirect()->route('case-file.wizard.step1');
        }

        // Get categories from database
        $categories =\DB::table('behavior_categories')->pluck('name')->toArray();

        return view('case-file.wizard', [
            'step' => 3,
            'data' => $data,
            'categories' => $categories,
            'incidents' => Session::get('case_file_incidents', [])
        ]);
    }

    /**
    * Process step 3 and save the case file
     */
    public function postStep3(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'category' => 'required|string'
        ]);

        $data = Session::get('case_file_wizard', []);
        $incidents = Session::get('case_file_incidents', []);

        if (empty($data)) {
            return redirect()->route('case-file.wizard.step1');
        }

        $data['description'] = $request->description;
        $data['category'] = $request->category;
        foreach ($incidents as $incidentData) {
            $incident = Incident::byTrackingId($incidentData['token'])->first();
            if ($incident && $incident->hasCaseFile()) {
                $currentCaseFile = $incident->getCurrentCaseFile();
                $currentToken = $currentCaseFile ? $currentCaseFile->tracking_id : 'unknown';
                
                return back()->withErrors([
                    'category' => "Incident '{$incident->tracking_id}' already belongs to case file '{$currentToken}'. Each incident can only be in one case file."
                ]);
            }
        }
        if ($data['is_new']) {
            // Create new case file
            $caseFile = CaseFile::create([
                'description' => $data['description'],
                'category' => $data['category'],
            ]);
            $data['case_file_token'] = $caseFile->tracking_id;
            $data['case_file_id'] = $caseFile->id;
        } else {
            // Update existing case file
            $caseFile = CaseFile::find($data['case_file_id']);
            if ($caseFile) {
                $caseFile->update([
                    'description' => $data['description'],
                    'category' => $data['category'],
                ]);
            }
        }

        // Add incidents to case file
        if ($caseFile && !empty($incidents)) {
            foreach ($incidents as $incidentData) {
                $incident = Incident::byTrackingId($incidentData['token'])->first();
                if ($incident) {
                    $caseFile->addIncident($incident);
                }
            }
        }

        Session::put('case_file_wizard', $data);

        return redirect()->route('case-file.wizard.success');
    }

    /**
     * Show success page
     */
    public function success()
    {
        $data = Session::get('case_file_wizard', []);
        $incidents = Session::get('case_file_incidents', []);

        if (empty($data)) {
            return redirect()->route('case-file.wizard.step1');
        }

        return view('case-file.success', [
            'data' => $data,
            'incidents' => $incidents,
            'is_new' => $data['is_new'] ?? true
        ]);
    }

    /**
     * Reset wizard and go back to create page
     */
    public function reset()
    {
        Session::forget('case_file_wizard');
        Session::forget('case_file_incidents');

        return redirect()->route('case-file.create');
    }
}