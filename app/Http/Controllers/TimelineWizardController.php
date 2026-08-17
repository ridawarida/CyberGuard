<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TimelineWizardController extends Controller
{
    /**
     * Show the wizard step 1
     */
    public function step1()
    {
        Session::forget('timeline_wizard');
        Session::forget('timeline_incidents');

        return view('timeline.wizard', [
            'step' => 1,
            'data' => Session::get('timeline_wizard', [])
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

        $data = Session::get('timeline_wizard', []);

        if ($request->action === 'existing') {
            $request->validate([
                'timeline_token' => 'required|string'
            ]);

            // Verify the timeline exists
            $timeline = Timeline::byTrackingId($request->timeline_token)->first();
            if (!$timeline) {
                return back()->withErrors([
                    'timeline_token' => 'Timeline not found. Please check your token.'
                ]);
            }

            $data['timeline_token'] = $request->timeline_token;
            $data['is_new'] = false;
            $data['timeline_id'] = $timeline->id;
        } else {
            $data['is_new'] = true;
        }

        Session::put('timeline_wizard', $data);

        return redirect()->route('timeline.wizard.step2');
    }

    /**
     * Show the wizard step 2
     */
    public function step2()
    {
        $data = Session::get('timeline_wizard', []);

        if (empty($data)) {
            return redirect()->route('timeline.wizard.step1');
        }

        return view('timeline.wizard', [
            'step' => 2,
            'data' => $data,
            'incidents' => Session::get('timeline_incidents', [])
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

    //Check if incident already belongs to a timeline
    if ($incident->hasTimeline()) {
        $currentTimeline = $incident->getCurrentTimeline();
        $currentToken = $currentTimeline ? $currentTimeline->tracking_id : 'unknown';
        
        return back()->withErrors([
            'incident_token' => "This incident already belongs to timeline '{$currentToken}'. Each incident can only be in one timeline."
        ]);
    }

    // Check if already added to current session
    $incidents = Session::get('timeline_incidents', []);
    foreach ($incidents as $existing) {
        if ($existing['token'] === $request->incident_token) {
            return back()->withErrors([
                'incident_token' => 'This incident has already been added to the current timeline.'
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

        Session::put('timeline_incidents', $incidents);

        return redirect()->route('timeline.wizard.step2')->with('success', 'Incident added successfully!');
    }

    /**
     * Remove an incident from the list
     */
    public function removeIncident($index)
    {
        $incidents = Session::get('timeline_incidents', []);

        if (isset($incidents[$index])) {
            unset($incidents[$index]);
            $incidents = array_values($incidents);
            Session::put('timeline_incidents', $incidents);
        }

        return redirect()->route('timeline.wizard.step2');
    }

    /**
     * Process step 2 and move to step 3
     */
    public function postStep2(Request $request)
    {
        $data = Session::get('timeline_wizard', []);

        if (empty($data)) {
            return redirect()->route('timeline.wizard.step1');
        }

        return redirect()->route('timeline.wizard.step3');
    }

    /**
     * Show the wizard step 3
     */
    public function step3()
    {
        $data = Session::get('timeline_wizard', []);

        if (empty($data)) {
            return redirect()->route('timeline.wizard.step1');
        }

        // Get categories from database
        $categories =\DB::table('behavior_categories')->pluck('name')->toArray();

        return view('timeline.wizard', [
            'step' => 3,
            'data' => $data,
            'categories' => $categories,
            'incidents' => Session::get('timeline_incidents', [])
        ]);
    }

    /**
     * Process step 3 and save the timeline
     */
    public function postStep3(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'category' => 'required|string'
        ]);

        $data = Session::get('timeline_wizard', []);
        $incidents = Session::get('timeline_incidents', []);

        if (empty($data)) {
            return redirect()->route('timeline.wizard.step1');
        }

        $data['description'] = $request->description;
        $data['category'] = $request->category;
        foreach ($incidents as $incidentData) {
            $incident = Incident::byTrackingId($incidentData['token'])->first();
            if ($incident && $incident->hasTimeline()) {
                $currentTimeline = $incident->getCurrentTimeline();
                $currentToken = $currentTimeline ? $currentTimeline->tracking_id : 'unknown';
                
                return back()->withErrors([
                    'category' => "Incident '{$incident->tracking_id}' already belongs to timeline '{$currentToken}'. Each incident can only be in one timeline."
                ]);
            }
        }
        if ($data['is_new']) {
            // Create new timeline
            $timeline = Timeline::create([
                'description' => $data['description'],
                'category' => $data['category'],
            ]);
            $data['timeline_token'] = $timeline->tracking_id;
            $data['timeline_id'] = $timeline->id;
        } else {
            // Update existing timeline
            $timeline = Timeline::find($data['timeline_id']);
            if ($timeline) {
                $timeline->update([
                    'description' => $data['description'],
                    'category' => $data['category'],
                ]);
            }
        }

        // Add incidents to timeline
        if ($timeline && !empty($incidents)) {
            foreach ($incidents as $incidentData) {
                $incident = Incident::byTrackingId($incidentData['token'])->first();
                if ($incident) {
                    $timeline->addIncident($incident);
                }
            }
        }

        Session::put('timeline_wizard', $data);

        return redirect()->route('timeline.wizard.success');
    }

    /**
     * Show success page
     */
    public function success()
    {
        $data = Session::get('timeline_wizard', []);
        $incidents = Session::get('timeline_incidents', []);

        if (empty($data)) {
            return redirect()->route('timeline.wizard.step1');
        }

        return view('timeline.success', [
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
        Session::forget('timeline_wizard');
        Session::forget('timeline_incidents');

        return redirect()->route('timeline.create');
    }
}