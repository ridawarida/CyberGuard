@extends('layouts.app')

@section('title', 'Moderation Workspace | CyberGuard')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Workspace bar --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-0">Moderation Workspace</h2>
            <small class="text-muted">
                Signed in as {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
            </small>
        </div>
        <form method="POST" action="{{ route('staff.logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
        </form>
    </div>

    @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} alert-dismissible fade show">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Scope tabs --}}
    <ul class="nav nav-tabs mb-3">
        @php
            
$tabs = [
                'pool' => 'Open Pool (' . $poolCount . ')',
                'mine' => 'My Cases (' . $mineCount . ')',
                'all'  => 'All Reports',
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $scope === $key ? 'active' : '' }}"
                   href="{{ route('moderator.incidents.index', array_merge(request()->except('page', 'scope'), ['scope' => $key])) }}">
                    {{ $label }}
                </a>
                <a href="{{ route('moderator.platform-policies.index') }}" class="btn btn-outline-danger">
    <i class="fas fa-shield-alt"></i> Manage Platform Policies</a>
            </li>
        @endforeach
        <li class="nav-item">
            <a class="nav-link" href="{{ route('moderator.consultations.index') }}">
                Secure Consultations
            </a>
        </li>
    </ul>

    {{-- Filters --}}
    <form method="GET" action="{{ route('moderator.incidents.index') }}" class="card border-0 shadow-sm mb-4">
        <input type="hidden" name="scope" value="{{ $scope }}">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Submitted from</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Submitted to</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Platform</label>
                    <select name="platform" class="form-select form-select-sm">
                        <option value="">All platforms</option>
                        @foreach ($platforms as $platform)
                            <option value="{{ $platform }}" {{ ($filters['platform'] ?? '') === $platform ? 'selected' : '' }}>
                                {{ $platform }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Region</label>
                    <select name="region" class="form-select form-select-sm">
                        <option value="">All regions</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region }}" {{ ($filters['region'] ?? '') === $region ? 'selected' : '' }}>
                                {{ $region }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Any status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Tracking code</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="inc..." value="{{ $filters['q'] ?? '' }}">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-danger btn-sm">Apply Filters</button>
                <a href="{{ route('moderator.incidents.index', ['scope' => $scope]) }}"
                   class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </div>
    </form>

    {{-- Case table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tracking Code</th>
                        <th>Submitted</th>
                        <th>Platform</th>
                        <th>Region</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        @php
                            $statusClass = match ($incident->status) {
                                'New' => 'secondary',
                                'Investigating' => 'primary',
                                'Escalated' => 'danger',
                                'Resolved' => 'success',
                                'Dismissed' => 'dark',
                                default => 'secondary',
                            };
                            $severityClass = match ($incident->severity) {
                                'Critical' => 'danger',
                                'High' => 'warning text-dark',
                                'Medium' => 'info text-dark',
                                'Low' => 'success',
                                default => 'light text-dark',
                            };
                        @endphp
                        <tr>
                            <td><code>{{ $incident->tracking_id }}</code></td>
                            <td class="small">{{ $incident->created_at?->format('d M Y') }}</td>
                            <td>{{ $incident->platform }}</td>
                            <td>{{ $incident->region }}</td>
                            <td class="small">{{ $incident->behavior_type }}</td>
                            <td><span class="badge bg-{{ $severityClass }}">{{ $incident->severity }}</span></td>
                            <td><span class="badge bg-{{ $statusClass }}">{{ $statuses[$incident->status] ?? $incident->status }}</span></td>
                            <td class="small">
                                {{ $incident->assignedModerator?->name ?? 'Unassigned' }}
                            </td>
                            <td class="text-end">
                                @if (! $incident->isClaimed())
                                    <form method="POST" action="{{ route('moderator.incidents.claim', $incident->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Claim Incident</button>
                                    </form>
                                @elseif ($incident->isReviewableBy(auth()->user()))
                                    <a href="{{ route('moderator.incidents.show', $incident->id) }}"
                                       class="btn btn-outline-danger btn-sm">Open Case</a>
                                @else
                                    <span class="badge bg-light text-muted border">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No incidents match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $incidents->links() }}
    </div>
</div>
@endsection
