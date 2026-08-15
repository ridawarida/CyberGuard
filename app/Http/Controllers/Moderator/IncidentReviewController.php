<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Moderator Incident Assessment and Case Lifecycle Updates.
 *
 * Moderators filter the submitted reports, claim a case so nobody else works
 * on it in parallel, read the narrative plus evidence, then record their
 * internal notes, a severity level and the new tracking status.
 */
class IncidentReviewController extends Controller
{
    /** Lifecycle states a case can move through. */
    public const STATUSES = [
        'New'           => 'New Submission',
        'Investigating' => 'Under Investigation',
        'Escalated'     => 'Escalated',
        'Resolved'      => 'Resolved',
        'Dismissed'     => 'Dismissed',
    ];

    /** Severity levels a moderator can assign after reading the case. */
    public const SEVERITIES = ['Unassigned', 'Low', 'Medium', 'High', 'Critical'];

    /**
     * Filterable case list. Three views are available through ?scope=
     *   pool = unclaimed cases anyone can take
     *   mine = cases locked to the signed in moderator
     *   all  = every case, read only unless it belongs to you
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only(['date_from', 'date_to', 'platform', 'region', 'status', 'q']);
        $scope = in_array($request->query('scope'), ['pool', 'mine', 'all'], true)
            ? $request->query('scope')
            : 'pool';

        $query = Incident::query()->with('assignedModerator')->filter($filters);

        if ($scope === 'pool') {
            $query->unclaimed();
        } elseif ($scope === 'mine') {
            $query->claimedBy($user->id);
        }

        $incidents = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('moderator.incidents.index', [
            'incidents'  => $incidents,
            'filters'    => $filters,
            'scope'      => $scope,
            'platforms'  => Incident::query()->select('platform')->distinct()->orderBy('platform')->pluck('platform'),
            'regions'    => Incident::query()->select('region')->distinct()->orderBy('region')->pluck('region'),
            'statuses'   => self::STATUSES,
            'poolCount'  => Incident::query()->unclaimed()->count(),
            'mineCount'  => Incident::query()->claimedBy($user->id)->count(),
        ]);
    }

    /**
     * Full case file. Only the assigned moderator (or an admin) may open it,
     * so an unclaimed report never leaks its narrative to the whole team.
     */
    public function show(Incident $incident)
    {
        $user = Auth::user();

        if (! $incident->isClaimed()) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with('warning', 'Claim this incident first to open the full case file.');
        }

        if (! $incident->isReviewableBy($user)) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with('error', 'This case is already assigned to ' . $incident->assignedModerator?->name . '.');
        }

        return view('moderator.incidents.show', [
            'incident'   => $incident->load('assignedModerator'),
            'statuses'   => self::STATUSES,
            'severities' => self::SEVERITIES,
        ]);
    }

    /**
     * Lock a case to the current moderator. Wrapped in a transaction with a
     * row lock so two moderators clicking at the same moment cannot both win.
     */
    public function claim(Incident $incident)
    {
        $userId = Auth::id();

        $claimed = DB::transaction(function () use ($incident, $userId) {
            $locked = Incident::query()->whereKey($incident->id)->lockForUpdate()->first();

            if ($locked === null || $locked->assigned_moderator_id !== null) {
                return false;
            }

            $locked->assigned_moderator_id = $userId;
            $locked->claimed_at = now();
            $locked->save();

            return true;
        });

        if (! $claimed) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with('error', 'Someone else claimed this incident first.');
        }

        return redirect()
            ->route('moderator.incidents.show', $incident->id)
            ->with('success', 'Incident claimed. It is now assigned to you.');
    }

    /** Put a case back in the open pool. */
    public function release(Incident $incident)
    {
        if (! $incident->isReviewableBy(Auth::user())) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with('error', 'You can only release a case assigned to you.');
        }

        $incident->update([
            'assigned_moderator_id' => null,
            'claimed_at' => null,
        ]);

        return redirect()
            ->route('moderator.incidents.index')
            ->with('success', 'Incident returned to the open pool.');
    }

    /** Save internal notes, severity and the new lifecycle status. */
    public function update(Request $request, Incident $incident)
    {
        if (! $incident->isReviewableBy(Auth::user())) {
            return redirect()
                ->route('moderator.incidents.index')
                ->with('error', 'You can only review a case assigned to you.');
        }

        $validated = $request->validate([
            'moderator_notes' => 'nullable|string|max:5000',
            'severity'        => 'required|in:' . implode(',', self::SEVERITIES),
            'status'          => 'required|in:' . implode(',', array_keys(self::STATUSES)),
        ]);

        $incident->update([
            'moderator_notes' => $validated['moderator_notes'],
            'severity'        => $validated['severity'],
            'status'          => $validated['status'],
            'reviewed_at'     => now(),
        ]);

        return redirect()
            ->route('moderator.incidents.show', $incident->id)
            ->with('success', 'Assessment saved. Case status is now ' . self::STATUSES[$validated['status']] . '.');
    }
}
