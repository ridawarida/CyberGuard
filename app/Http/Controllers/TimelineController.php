<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimelineController extends Controller
{
    // GET 
    public function categories()
    {
        $categories = DB::table('behavior_categories')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    // POST /api/timeline
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'category' => 'required|string'
        ]);

        $trackingId = 'tl' . Str::random(12);
        
        DB::table('timelines')->insert([
            'tracking_id' => $trackingId,
            'description' => $validated['description'],
            'category' => $validated['category'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Timeline created successfully',
            'data' => [
                'tracking_id' => $trackingId,
                'description' => $validated['description'],
                'category' => $validated['category']
            ]
        ], 201);
    }

    // GET /api/timeline/{tracking_id}
    public function show($tracking_id)
    {
        $timeline = DB::table('timelines')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$timeline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timeline not found'
            ], 404);
        }

        $timelineReports = DB::table('timeline_reports')
            ->where('timeline_id', $timeline->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tracking_id' => $timeline->tracking_id,
                'description' => $timeline->description,
                'category' => $timeline->category,
                'reports' => $timelineReports,
                'incidents' => $timelineReports,
            ]
        ]);
    }

    // POST /api/timeline/{tracking_id}/report
    public function addReport(Request $request, $tracking_id)
    {
        $validated = $request->validate([
            'report_tracking_id' => 'required|string'
        ]);

        $timeline = DB::table('timelines')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$timeline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timeline not found'
            ], 404);
        }

        $incident = DB::table('incidents')
            ->where('tracking_id', $validated['report_tracking_id'])
            ->first();

        if (!$incident) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incident not found'
            ], 404);
        }

        DB::table('timeline_reports')->insert([
            'timeline_id' => $timeline->id,
            'report_tracking_id' => $incident->tracking_id,
            'report_overview' => $incident->overview ?? $incident->description,
            'report_incident_date' => $incident->incident_date,
            'report_platform' => $incident->platform,
            'report_region' => $incident->region,
            'behavior_type' => $incident->behavior_type,
            'severity' => $incident->severity,
            'added_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Report added to timeline successfully'
        ]);
    }

    // PUT /api/timeline/{tracking_id}
    public function update(Request $request, $tracking_id)
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:500',
            'category' => 'sometimes|string'
        ]);

        $updated = DB::table('timelines')
            ->where('tracking_id', $tracking_id)
            ->update([
                'description' => $validated['description'] ?? DB::raw('description'),
                'category' => $validated['category'] ?? DB::raw('category'),
                'updated_at' => now()
            ]);

        if (!$updated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timeline not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Timeline updated successfully'
        ]);
    }

    // DELETE /api/timeline/{tracking_id}/report/{report_tracking_id}
    public function removeReport($tracking_id, $report_tracking_id)
    {
        $timeline = DB::table('timelines')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$timeline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timeline not found'
            ], 404);
        }

        $deleted = DB::table('timeline_reports')
            ->where('timeline_id', $timeline->id)
            ->where('report_tracking_id', $report_tracking_id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incident not found in this timeline'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Incident removed from timeline successfully'
        ]);
    }

    // DELETE (web) /timeline/delete - delete a timeline by token (form submission)
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'timeline_token' => 'required|string'
        ]);

        $timeline = DB::table('timelines')
            ->where('tracking_id', $validated['timeline_token'])
            ->first();

        if (!$timeline) {
            return redirect()->route('timeline.create')->withErrors(['timeline_token' => 'Timeline not found.']);
        }

        // Delete associated timeline_reports
        DB::table('timeline_reports')
            ->where('timeline_id', $timeline->id)
            ->delete();

        // Delete the timeline
        DB::table('timelines')
            ->where('id', $timeline->id)
            ->delete();

        return redirect()->route('timeline.create')->with('success', 'Timeline deleted successfully.');
    }

     /**
     * Remove multiple incidents from a timeline.
     */
    public function removeIncidents(Request $request, $tracking_id)
    {
        $request->validate([
            'incidents_to_remove' => 'required|json'
        ]);

        // Decode the JSON array
        $incidentTokens = json_decode($request->incidents_to_remove, true);

        if (!is_array($incidentTokens) || empty($incidentTokens)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No incidents specified to remove.'
            ], 400);
        }

        // Find the timeline
        $timeline = Timeline::byTrackingId($tracking_id)->first();

        if (!$timeline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timeline not found.'
            ], 404);
        }

        $removedCount = 0;

        // Remove each incident
        foreach ($incidentTokens as $token) {
            if ($timeline->removeIncident($token)) {
                $removedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully removed {$removedCount} incident(s) from the timeline.",
            'data' => [
                'removed_count' => $removedCount,
                'remaining_count' => $timeline->incidents()->count()
            ]
        ]);
    }

}