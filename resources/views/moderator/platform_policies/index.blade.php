@extends('layouts.app')

@section('title', 'Platform Policies | CyberGuard')

@section('content')
<div class="container py-4">

    {{-- Breadcrumb & Back navigation --}}
    <div class="mb-3">
        <a href="{{ route('moderator.incidents.index') }}" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Moderation Workspace
        </a>
    </div>

    {{-- Header section --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #222;">
                <i class="fas fa-shield-alt me-2" style="color: #DC143C;"></i>Platform Safety Policies
            </h2>
            <p class="text-muted mb-0 small">
                Manage external platform reporting links, takedown instructions, and verify link freshness every 90 days.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('platform-policies.index') }}" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt me-1"></i> View Public Directory
            </a>
            <a href="{{ route('moderator.platform-policies.create') }}" class="btn btn-crimson">
                <i class="fas fa-plus me-1"></i> Add Platform Policy
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Quick status metrics --}}
    @php
        $needsReviewCount = $policies->filter(fn($p) => $p->needsReview())->count();
        $currentCount = $policies->count() - $needsReviewCount;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3" style="background-color: rgba(220, 20, 60, 0.1);">
                        <i class="fas fa-layer-group fa-lg" style="color: #DC143C;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Platforms</div>
                        <div class="fs-4 fw-bold">{{ $policies->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 bg-success-subtle text-success">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Current & Verified</div>
                        <div class="fs-4 fw-bold text-success">{{ $currentCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Needs Review (90+ Days)</div>
                        <div class="fs-4 fw-bold text-warning">{{ $needsReviewCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Policies Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            @if($policies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 ps-4" style="width: 18%;">Platform</th>
                                <th class="py-3" style="width: 22%;">Reporting Portal</th>
                                <th class="py-3" style="width: 32%;">Instructions / Steps</th>
                                <th class="py-3" style="width: 13%;">Verification</th>
                                <th class="py-3 pe-4 text-end" style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-2" style="background: #f8f9fa;">
                                                <i class="fas fa-globe text-muted"></i>
                                            </div>
                                            <span class="fw-bold">{{ $policy->platform }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ $policy->reporting_url }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-primary small d-inline-block text-truncate" style="max-width: 220px;">
                                            <i class="fas fa-external-link-alt me-1"></i>{{ $policy->reporting_url }}
                                        </a>
                                    </td>
                                    <td>
                                        <p class="text-muted small mb-0" style="max-height: 48px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $policy->instructions }}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            @if($policy->needsReview())
                                                <span class="badge bg-warning text-dark px-2 py-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Needs Review
                                                </span>
                                            @else
                                                <span class="badge bg-success px-2 py-1">
                                                    <i class="fas fa-check me-1"></i>Current
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            @if($policy->last_verified_at)
                                                {{ $policy->last_verified_at->format('d M Y') }}
                                            @else
                                                <span class="text-danger">Unverified</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('moderator.platform-policies.edit', $policy) }}" class="btn btn-outline-primary btn-sm px-2 py-1" title="Edit Policy">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('moderator.platform-policies.destroy', $policy) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete the reporting policy for {{ $policy->platform }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" title="Delete Policy">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No Platform Policies Registered</h5>
                    <p class="text-muted small mb-3">
                        There are currently no external platform takedown guides in the directory.
                    </p>
                    <a href="{{ route('moderator.platform-policies.create') }}" class="btn btn-crimson btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Your First Policy
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection