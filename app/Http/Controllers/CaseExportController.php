<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Automated Legal and Institutional PDF Case Exporter.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Requires: composer require barryvdh/laravel-dompdf
 *
 * ASSUMPTIONS (see chat summary for the full list):
 * - App\Models\Incident exists with id, tracking_code, platform,
 *   incident_date, description, status (Ishrat's Module 1/2).
 * - A `timeline_events` table exists with incident_id, event_date,
 *   event_time, behavior_type, summary (Nahin's Module 1). Queried with
 *   the query builder directly rather than an Eloquent relationship, so
 *   this does not require Incident to already define timelineEvents().
 * Only the two DB::table('timeline_events') calls below need editing if
 * her real table/columns are named differently.
 */
class CaseExportController extends Controller
{
    public function form(Request $request): View
    {
        $incident = $this->currentIncident($request);
        $timelineEvents = $this->timelineEventsFor($incident->id);

        return view('consult.export', compact('incident', 'timelineEvents'));
    }

    public function generate(Request $request): Response
    {
        $incident = $this->currentIncident($request);

        $validator = validator($request->all(), [
            'include_description' => ['sometimes', 'boolean'],
            'timeline_event_ids' => ['sometimes', 'array'],
            'timeline_event_ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $selectedEvents = collect();

        if (! empty($validated['timeline_event_ids'])) {
            $selectedEvents = $this->timelineEventsFor($incident->id)
                ->whereIn('id', $validated['timeline_event_ids']);
        }

        $pdf = Pdf::loadView('pdf.case-report', [
            'incident' => $incident,
            'includeDescription' => $request->boolean('include_description'),
            'timelineEvents' => $selectedEvents,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $trackingCode = $incident->tracking_id ?? $incident->id;
        $filename = 'cyberguard-case-'.$trackingCode.'-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }

    private function currentIncident(Request $request): Incident
    {
        $incidentId = $request->session()->get('incident_id');

        abort_unless($incidentId, 403);

        return Incident::findOrFail($incidentId);
    }

    private function timelineEventsFor(int $incidentId): Collection
    {
        return DB::table('timeline_events')
            ->where('incident_id', $incidentId)
            ->orderBy('event_date')
            ->get();
    }
}
