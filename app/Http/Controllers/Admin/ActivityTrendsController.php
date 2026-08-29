<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityTrendsController extends Controller
{
    public function index(Request $request)
    {
        // Optional date range filter, defaults to last 6 months
        $from = $request->input('date_from')
            ? \Carbon\Carbon::parse($request->input('date_from'))
            : now()->subMonths(6)->startOfMonth();

        $to = $request->input('date_to')
            ? \Carbon\Carbon::parse($request->input('date_to'))
            : now()->endOfMonth();

        // 1. Reports filed per month
        $monthlyVolume = Incident::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 2. Volume by platform (which social networks have highest abuse rates)
        $platformVolume = Incident::query()
            ->select('platform', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        // 3. Case status breakdown
        $statusBreakdown = Incident::query()
            ->select('status', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // 4. Behavior type / category breakdown (bonus insight)
        $categoryVolume = Incident::query()
            ->select('behavior_type', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('behavior_type')
            ->orderByDesc('total')
            ->get();

        // Summary totals
        $totalReports = Incident::whereBetween('created_at', [$from, $to])->count();

        return view('admin.activity-trends.index', [
            'monthlyVolume' => $monthlyVolume,
            'platformVolume' => $platformVolume,
            'statusBreakdown' => $statusBreakdown,
            'categoryVolume' => $categoryVolume,
            'totalReports' => $totalReports,
            'dateFrom' => $from->format('Y-m-d'),
            'dateTo' => $to->format('Y-m-d'),
        ]);
    }
}