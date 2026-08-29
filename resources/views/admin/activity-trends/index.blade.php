@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>System Activity & Trends</h2>
    </div>

    <!-- Date filter -->
    <form method="GET" action="{{ route('admin.activity-trends.index') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="form-label small mb-0">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="col-auto d-flex align-items-end">
            <button type="submit" class="btn btn-danger">Filter</button>
        </div>
    </form>

    <!-- Summary -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-0">Total Reports in Range: {{ $totalReports }}</h5>
        </div>
    </div>

    <div class="row">
        <!-- Monthly volume -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Reports Filed Per Month</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Month</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($monthlyVolume as $row)
                                <tr><td>{{ $row->month }}</td><td>{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">No data in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Platform volume -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Volume by Platform</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Platform</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($platformVolume as $row)
                                <tr><td>{{ $row->platform }}</td><td>{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">No data in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status breakdown -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Case Status Breakdown</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($statusBreakdown as $row)
                                <tr><td>{{ $row->status }}</td><td>{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">No data in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Category breakdown -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Volume by Category</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Category</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($categoryVolume as $row)
                                <tr><td>{{ $row->behavior_type }}</td><td>{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">No data in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection