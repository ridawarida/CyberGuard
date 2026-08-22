<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CaseFileController extends Controller
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

    // POST /api/case-files
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'category' => 'required|string'
        ]);

        $trackingId = 'cf' . Str::random(12);
        
        DB::table('case_files')->insert([
            'tracking_id' => $trackingId,
            'description' => $validated['description'],
            'category' => $validated['category'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Case file created successfully',
            'data' => [
                'tracking_id' => $trackingId,
                'description' => $validated['description'],
                'category' => $validated['category']
            ]
        ], 201);
    }

    // GET /api/case-files/{tracking_id}
    public function show($tracking_id)
    {
        $caseFile = DB::table('case_files')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$caseFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Case file not found'
            ], 404);
        }

        $caseFileIncidents = DB::table('case_file_incidents')
            ->where('case_file_id', $caseFile->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tracking_id' => $caseFile->tracking_id,
                'description' => $caseFile->description,
                'category' => $caseFile->category,
                'incidents' => $caseFileIncidents,
            ]
        ]);
    }

    // POST /api/case-files/{tracking_id}/incidents
    public function addIncident(Request $request, $tracking_id)
    {
        $validated = $request->validate([
            'incident_tracking_id' => 'required|string'
        ]);

        $caseFile = DB::table('case_files')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$caseFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Case file not found'
            ], 404);
        }

        $incident = DB::table('incidents')
            ->where('tracking_id', $validated['incident_tracking_id'])
            ->first();

        if (!$incident) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incident not found'
            ], 404);
        }

        DB::table('case_file_incidents')->insert([
            'case_file_id' => $caseFile->id,
            'incident_tracking_id' => $incident->tracking_id,
            'incident_overview' => $incident->overview ?? $incident->description,
            'incident_date' => $incident->incident_date,
            'incident_platform' => $incident->platform,
            'incident_region' => $incident->region,
            'behavior_type' => $incident->behavior_type,
            'severity' => $incident->severity,
            'added_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Incident added to case file successfully'
        ]);
    }

    // PUT /api/case-files/{tracking_id}
    public function update(Request $request, $tracking_id)
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:500',
            'category' => 'sometimes|string'
        ]);

        $updated = DB::table('case_files')
            ->where('tracking_id', $tracking_id)
            ->update([
                'description' => $validated['description'] ?? DB::raw('description'),
                'category' => $validated['category'] ?? DB::raw('category'),
                'updated_at' => now()
            ]);

        if (!$updated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Case file not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Case file updated successfully'
        ]);
    }

    // DELETE /api/case-files/{tracking_id}/incidents/{incident_tracking_id}
    public function removeIncident($tracking_id, $incident_tracking_id)
    {
        $caseFile = DB::table('case_files')
            ->where('tracking_id', $tracking_id)
            ->first();

        if (!$caseFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Case file not found'
            ], 404);
        }

        $deleted = DB::table('case_file_incidents')
            ->where('case_file_id', $caseFile->id)
            ->where('incident_tracking_id', $incident_tracking_id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incident not found in this case file'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
                'message' => 'Incident removed from case file successfully'
        ]);
    }

    // DELETE (web) /case-files/delete - delete a case file by token (form submission)
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'case_file_token' => 'required|string'
        ]);

        $caseFile = DB::table('case_files')
            ->where('tracking_id', $validated['case_file_token'])
            ->first();

        if (!$caseFile) {
            return redirect()->route('case-file.create')->withErrors(['case_file_token' => 'Case file not found.']);
        }

        // Delete associated case_file_incidents
        DB::table('case_file_incidents')
            ->where('case_file_id', $caseFile->id)
            ->delete();

        // Delete the case file
        DB::table('case_files')
            ->where('id', $caseFile->id)
            ->delete();

        return redirect()->route('case-file.create')->with('success', 'Case file deleted successfully.');
    }

     /**
    * Remove multiple incidents from a case file.
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

        // Find the case file
        $caseFile = CaseFile::byTrackingId($tracking_id)->first();

        if (!$caseFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Case file not found.'
            ], 404);
        }

        $removedCount = 0;

        // Remove each incident
        foreach ($incidentTokens as $token) {
            if ($caseFile->removeIncident($token)) {
                $removedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully removed {$removedCount} incident(s) from the case file.",
            'data' => [
                'removed_count' => $removedCount,
                'remaining_count' => $caseFile->incidents()->count()
            ]
        ]);
    }

}