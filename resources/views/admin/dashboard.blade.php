@extends('layouts.app')

@section('title', 'Admin Dashboard | CyberGuard')

@section('content')
<div class="container-fluid admin-shell px-4 py-4">
    @include('partials.admin-menu')

    @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} alert-dismissible fade show">{{ session($key) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
    @endforeach

    <div class="dashboard-banner mb-4">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTEnh3DeOuaufLShnr2MWjKfOer3S1D0lNryO-9qiEtodjoHVsqxcUP8k&amp;s=10" alt="Crisis support administration" loading="eager">
        <div class="dashboard-banner-overlay">
            <p class="eyebrow mb-1">Operations control</p>
            <h1 class="workspace-title mb-2">Admin Dashboard</h1>
            <p class="dashboard-subtitle mb-0">Incident oversight, staff workload, and crisis support directory.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'New this week', 'value' => $newThisWeek, 'icon' => 'fa-calendar-week', 'tone' => 'red'],
            ['label' => 'New this month', 'value' => $newThisMonth, 'icon' => 'fa-chart-line', 'tone' => 'blue'],
            ['label' => 'Moderators', 'value' => $moderatorCount, 'icon' => 'fa-users', 'tone' => 'green'],
            ['label' => 'Awaiting assignment', 'value' => $unassignedCount, 'icon' => 'fa-inbox', 'tone' => 'gold'],
        ] as $stat)
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-icon {{ $stat['tone'] }}"><i class="fas {{ $stat['icon'] }}"></i></div>
                    <div><span class="stat-label">{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-panel mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-2"><label class="form-label small">Platform</label><select name="platform" class="form-select"><option value="">All platforms</option>@foreach($platforms as $platform)<option value="{{ $platform }}" @selected(($filters['platform'] ?? '') === $platform)>{{ $platform }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Region</label><select name="region" class="form-select"><option value="">All regions</option>@foreach($regions as $region)<option value="{{ $region }}" @selected(($filters['region'] ?? '') === $region)>{{ $region }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Status</label><select name="status" class="form-select"><option value="">Any status</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small">Tracking code</label><input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="inc..."></div>
        </div>
        <div class="mt-3"><button class="btn btn-dark btn-sm" type="submit"><i class="fas fa-filter me-1"></i>Apply filters</button><a class="btn btn-outline-secondary btn-sm ms-2" href="{{ route('admin.dashboard') }}">Reset</a></div>
    </form>

    <div class="table-panel">
        <div class="panel-heading"><div><p class="eyebrow mb-1">Live queue</p><h2 class="h5 mb-0">All submitted incidents</h2></div><span class="count-chip">{{ $incidents->total() }} total</span></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tracking code</th><th>Submitted</th><th>Platform</th><th>Region</th><th>Category</th><th>Severity</th><th>Status</th><th>Moderator</th><th></th></tr></thead>
                <tbody>
                @forelse($incidents as $incident)
                    @php
                        $statusClass = match($incident->status) {'New' => 'secondary', 'Investigating' => 'primary', 'Escalated' => 'danger', 'Resolved' => 'success', 'Dismissed' => 'dark', default => 'light text-dark'};
                        $severityClass = match($incident->severity) {'Critical' => 'danger', 'High' => 'warning text-dark', 'Medium' => 'info text-dark', 'Low' => 'success', default => 'light text-dark'};
                    @endphp
                    <tr>
                        <td><code>{{ $incident->tracking_id }}</code></td><td class="small">{{ $incident->created_at?->format('d M Y') }}</td><td>{{ $incident->platform }}</td><td>{{ $incident->region }}</td><td class="small">{{ $incident->behavior_type }}</td><td><span class="badge bg-{{ $severityClass }}">{{ $incident->severity }}</span></td><td><span class="badge bg-{{ $statusClass }}">{{ $statuses[$incident->status] ?? $incident->status }}</span></td><td>{{ $incident->assignedModerator?->name ?? 'Unassigned' }}</td><td class="text-end">@if($incident->isClaimed())<a href="{{ route('moderator.incidents.show', $incident->id) }}" class="btn btn-outline-danger btn-sm">Open</a>@else<span class="badge bg-light text-muted border">Awaiting claim</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">No incidents match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $incidents->links() }}</div>
</div>

<style>
.admin-shell{background:#f7f4f1;min-height:calc(100vh - 72px)}.eyebrow{color:#b21e3b;text-transform:uppercase;letter-spacing:.12em;font-size:11px;font-weight:800}.workspace-title{font-weight:800;color:#20242b}.btn-crimson{background:#b21e3b;color:#fff;border:0}.btn-crimson:hover{background:#8f1730;color:#fff}.stat-card{background:#fff;border:1px solid #e8e0db;padding:20px;display:flex;gap:15px;align-items:center;box-shadow:0 4px 16px #30231b0b}.stat-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:10px}.stat-icon.red{background:#f9dce2;color:#a71937}.stat-icon.blue{background:#dcebf4;color:#23627d}.stat-icon.green{background:#dcefe5;color:#267050}.stat-icon.gold{background:#f7ebc9;color:#8c6812}.stat-label{display:block;color:#777;font-size:12px}.stat-card strong{font-size:26px;color:#20242b}.filter-panel,.table-panel{background:#fff;border:1px solid #e8e0db;box-shadow:0 4px 16px #30231b0b}.filter-panel{padding:20px}.filter-panel .form-control,.filter-panel .form-select{border-radius:5px}.panel-heading{padding:18px 20px;border-bottom:1px solid #eee6e0;display:flex;justify-content:space-between;align-items:center}.count-chip{background:#f5e5e8;color:#9b1935;padding:6px 10px;font-size:12px;font-weight:700}.table thead th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#777;white-space:nowrap}.table td{font-size:13px}.table code{color:#9b1935}
.dashboard-banner{height:220px;overflow:hidden;background:#e8e0db;position:relative}.dashboard-banner img{width:100%;height:100%;display:block;object-fit:cover;object-position:center}.dashboard-banner::after{content:"";position:absolute;inset:0;background:rgba(20,24,29,.48)}.dashboard-banner-overlay{position:absolute;inset:0;z-index:1;display:flex;flex-direction:column;justify-content:center;padding:28px 34px;color:#fff}.dashboard-banner-overlay .eyebrow{color:#f2b4c0}.dashboard-banner-overlay .workspace-title{color:#fff;font-size:clamp(2rem,4vw,3.2rem)}.dashboard-subtitle{color:rgba(255,255,255,.9);font-size:16px;max-width:620px}
</style>
@endsection
