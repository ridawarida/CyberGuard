<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class TicketStatusController extends Controller
{
    /** Human-readable labels, matching Anika's moderator dashboard exactly. */
    public const STATUS_LABELS = [
        'New'           => 'New Submission',
        'Investigating' => 'Under Investigation',
        'Escalated'     => 'Escalated',
        'Resolved'      => 'Resolved',
        'Dismissed'     => 'Dismissed',
    ];

    // Show the search form
    public function index()
    {
        return view('ticket-status.index');
    }

    // Handle the search
    public function search(Request $request)
    {
        $validated = $request->validate([
            'tracking_id' => 'required|string|max:50',
        ]);

        $incident = Incident::where('tracking_id', trim($validated['tracking_id']))->first();

        if (!$incident) {
            return back()
                ->withInput()
                ->withErrors(['tracking_id' => 'No report found with that tracking code. Please check and try again.']);
        }

        $statusLabel = self::STATUS_LABELS[$incident->status] ?? $incident->status;

        return view('ticket-status.index', [
            'result' => true,
            'trackingId' => $incident->tracking_id,
            'statusLabel' => $statusLabel,
        ]);
    }
}