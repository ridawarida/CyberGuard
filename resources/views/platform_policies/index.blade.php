@extends('layouts.app')

@section('title', 'Platform Safety Guidelines | CyberGuard')

@section('content')
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2">Platform Reporting Guidelines</h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">
            Find verified, step-by-step instructions and official direct reporting portals to request takedowns of harassment and cyberbullying on major social networks.
        </p>
    </div>

    @if($policies->count() > 0)
        <div class="row g-4">
            @foreach($policies as $policy)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3 d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="rounded-circle p-3" style="background-color: rgba(220, 20, 60, 0.1);">
                                    <i class="fas fa-globe fa-2x" style="color: #DC143C;"></i>
                                </div>
                                @if($policy->needsReview())
                                    <span class="badge bg-warning text-dark px-2 py-1 small">
                                        <i class="fas fa-clock me-1"></i> Review Pending
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                        <i class="fas fa-check me-1"></i> Verified
                                    </span>
                                @endif
                            </div>

                            <h4 class="fw-bold mb-3">{{ $policy->platform }}</h4>

                            <h6 class="fw-bold text-secondary small text-uppercase mb-2">
                                Takedown & Reporting Instructions
                            </h6>

                            <p class="text-muted small flex-grow-1" style="white-space: pre-line;">
                                {{ $policy->instructions }}
                            </p>

                            <div class="mt-3 pt-3 border-top">
                                <a href="{{ $policy->reporting_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-crimson w-100">
                                    <i class="fas fa-external-link-alt me-2"></i>
                                    Report on {{ $policy->platform }}
                                </a>

                                @if($policy->last_verified_at)
                                    <p class="small text-muted mt-2 mb-0 text-center" style="font-size: 0.75rem;">
                                        Last verified: {{ $policy->last_verified_at->format('F d, Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="rounded-circle d-inline-flex p-4 mb-3" style="background-color: rgba(220, 20, 60, 0.1);">
                <i class="fas fa-file-alt fa-3x" style="color: #DC143C;"></i>
            </div>
            <h3 class="fw-bold">No Platform Policies Available</h3>
            <p class="text-muted">
                Official platform reporting policies have not been published yet. Please check back later.
            </p>
        </div>
    @endif

</div>
@endsection