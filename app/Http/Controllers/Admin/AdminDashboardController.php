<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'platform', 'region', 'status', 'q']);

        $incidents = Incident::query()
            ->with('assignedModerator')
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dashboard', [
            'incidents' => $incidents,
            'filters' => $filters,
            'platforms' => Incident::query()->select('platform')->distinct()->orderBy('platform')->pluck('platform'),
            'regions' => Incident::query()->select('region')->distinct()->orderBy('region')->pluck('region'),
            'statuses' => [
                'New' => 'New Submission',
                'Investigating' => 'Under Investigation',
                'Escalated' => 'Escalated',
                'Resolved' => 'Resolved',
                'Dismissed' => 'Dismissed',
            ],
            'newThisWeek' => Incident::where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'newThisMonth' => Incident::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'moderatorCount' => User::where('role', 'moderator')->count(),
            'unassignedCount' => Incident::unclaimed()->count(),
        ]);
    }
}
