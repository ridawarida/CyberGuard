<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Services\ToxicityScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidentReviewController extends Controller
{
    /**
     * Lifecycle states a case can move through.
     */
    public const STATUSES = [
        'New'           => 'New Submission',
        'Investigating' => 'Under Investigation',
        'Escalated'     => 'Escalated',
        'Resolved'      => 'Resolved',
        'Dismissed'     => 'Dismissed',
    ];

    /**
     * Severity levels.
     */
    public const SEVERITIES = [
        'Unassigned',
        'Low',
        'Medium',
        'High',
        'Critical',
    ];

    /**
     * Moderator incident list.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only([
            'date_from',
            'date_to',
            'platform',
            'region',
            'status',
            'q',
        ]);

        $scope = in_array(
            $request->query('scope'),
            ['pool', 'mine', 'all'],
            true
        )
            ? $request->query('scope')
            : 'pool';

        $query = Incident::query()
            ->with('assignedModerator')
            ->filter($filters);

        if ($scope === 'pool') {
            $query->unclaimed();
        } elseif ($scope === 'mine') {
            $query->claimedBy($user->id);
        }

        $incidents = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('moderator.incidents.index', [
            'incidents' => $incidents,
            'filters' => $filters,
            'scope' => $scope,

            'platforms' => Incident::query()
                ->select('platform')
                ->distinct()
                ->orderBy('platform')
                ->pluck('platform'),

            'regions' => Incident::query()
                ->select('region')
                ->distinct()
                ->orderBy('region')
                ->pluck('region'),

            'statuses' => self::STATUSES,

            'poolCount' => Incident::query()
                ->unclaimed()
                ->count(),

            'mineCount' => Incident::query()
                ->claimedBy($user->id)
                ->count(),
        ]);
    }

    /**
     * Show full incident case.
     */
    public function show(Incident $incident)
    {
        $user = Auth::user();

        if (!$incident->isClaimed()) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with(
                    'warning',
                    'Claim this incident first to open the full case file.'
                );
        }

        if (!$incident->isReviewableBy($user)) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with(
                    'error',
                    'This case is already assigned to ' .
                    $incident->assignedModerator?->name .
                    '.'
                );
        }

        return view('moderator.incidents.show', [
            'incident' => $incident->load('assignedModerator'),
            'statuses' => self::STATUSES,
            'severities' => self::SEVERITIES,
        ]);
    }

    /**
     * Claim an incident.
     */
    public function claim(Incident $incident)
    {
        $userId = Auth::id();

        $claimed = DB::transaction(
            function () use ($incident, $userId) {

                $locked = Incident::query()
                    ->whereKey($incident->id)
                    ->lockForUpdate()
                    ->first();

                if (
                    $locked === null ||
                    $locked->assigned_moderator_id !== null
                ) {
                    return false;
                }

                $locked->assigned_moderator_id = $userId;
                $locked->claimed_at = now();
                $locked->save();

                return true;
            }
        );

        if (!$claimed) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with(
                    'error',
                    'Someone else claimed this incident first.'
                );
        }

        return redirect()
            ->route(
                'moderator.incidents.show',
                $incident->id
            )
            ->with(
                'success',
                'Incident claimed. It is now assigned to you.'
            );
    }

    /**
     * Release incident.
     */
    public function release(Incident $incident)
    {
        if (!$incident->isReviewableBy(Auth::user())) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with(
                    'error',
                    'You can only release a case assigned to you.'
                );
        }

        $incident->update([
            'assigned_moderator_id' => null,
            'claimed_at' => null,
        ]);

        return redirect()
            ->route('moderator.incidents.index')
            ->with(
                'success',
                'Incident returned to the open pool.'
            );
    }

    /**
     * Save moderator assessment.
     */
    public function update(
        Request $request,
        Incident $incident
    ) {
        if (!$incident->isReviewableBy(Auth::user())) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with(
                    'error',
                    'You can only review a case assigned to you.'
                );
        }

        $validated = $request->validate([
            'moderator_notes' =>
                'nullable|string|max:5000',

            'severity' =>
                'required|in:' .
                implode(',', self::SEVERITIES),

            'status' =>
                'required|in:' .
                implode(
                    ',',
                    array_keys(self::STATUSES)
                ),
        ]);

        $incident->update([
            'moderator_notes' =>
                $validated['moderator_notes'],

            'severity' =>
                $validated['severity'],

            'status' =>
                $validated['status'],

            'reviewed_at' =>
                now(),
        ]);

        return redirect()
            ->route(
                'moderator.incidents.show',
                $incident->id
            )
            ->with(
                'success',
                'Assessment saved. Case status is now ' .
                self::STATUSES[$validated['status']] .
                '.'
            );
    }

    /**
     * Scan incident text and evidence separately.
     */
/**
 * Scan incident text and evidence separately.
 */
    public function scanThreats(
        Incident $incident,
        ToxicityScannerService $scanner) {
    // Check moderator permission.
    if (! $incident->isReviewableBy(Auth::user())) {
        return redirect()
            ->route('moderator.incidents.index')
            ->with(
                'error',
                'You can only scan a case assigned to you.'
            );
    }

    // Combine available report text.
    $text = trim(
        implode(
            "\n\n",
            array_filter([
                $incident->description,
                $incident->overview,
            ])
        )
    );

    // Find stored evidence image.
    $imagePath = null;

    if (! empty($incident->evidence_image)) {
        $candidatePath = Storage::disk('public')->path(
            ltrim(
                $incident->evidence_image,
                '/'
            )
        );

        if (file_exists($candidatePath)) {
            $imagePath = $candidatePath;
        }
    }

    // Require at least text or image.
    if ($text === '' && $imagePath === null) {
        return redirect()
            ->route(
                'moderator.incidents.show',
                $incident->id
            )
            ->with(
                'error',
                'There is no text or evidence image available to scan.'
            );
    }

    // Perform AI analysis.
    $result = $scanner->scanText(
        $text,
        $imagePath
    );

    /*
    |--------------------------------------------------------------------------
    | SAVE AI RESULTS
    |--------------------------------------------------------------------------
    |
    | Overall result -> existing AI fields
    | Detailed result -> new AI detailed fields
    |
    */

    $incident->update([
        // Existing fields
        'ai_risk_score' => $result['overall_risk_score'] ?? null,
        'ai_risk_level' => $result['overall_risk_level'] ?? null,
        'ai_reason' => $result['reason'] ?? null,
        'ai_scanned_at' => now(),

        // Detailed text analysis
        'ai_text_risk_score' => $result['text_risk_score'] ?? null,
        'ai_text_risk_level' => $result['text_risk_level'] ?? null,
        'ai_text_reason' => $result['text_reason'] ?? null,

        // Detailed image analysis
        'ai_image_risk_score' => $result['image_risk_score'] ?? null,
        'ai_image_risk_level' => $result['image_risk_level'] ?? null,
        'ai_image_reason' => $result['image_reason'] ?? null,
    ]);

    return redirect()
        ->route(
            'moderator.incidents.show',
            $incident->id
        )
        ->with(
            'threat_scan',
            $result
        );
}
}