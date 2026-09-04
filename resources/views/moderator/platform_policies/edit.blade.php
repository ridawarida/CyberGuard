@extends('layouts.app')

@section('title', 'Edit ' . $platformPolicy->platform . ' Policy | CyberGuard')

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <div class="mb-3">
        <a href="{{ route('moderator.platform-policies.index') }}" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Platform Policies
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle p-2 me-3" style="background-color: rgba(220, 20, 60, 0.1);">
                                <i class="fas fa-edit fa-lg" style="color: #DC143C;"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0">Edit Platform Policy</h4>
                                <p class="text-muted small mb-0">
                                    Updating guidelines for <strong>{{ $platformPolicy->platform }}</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Quick status badge --}}
                        <div>
                            @if($platformPolicy->needsReview())
                                <span class="badge bg-warning text-dark px-2 py-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Needs Review
                                </span>
                            @else
                                <span class="badge bg-success px-2 py-1">
                                    <i class="fas fa-check me-1"></i> Current
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-exclamation-circle me-1"></i> Please correct the following errors:
                            </h6>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('moderator.platform-policies.update', $platformPolicy) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Platform Name --}}
                        <div class="mb-3">
                            <label for="platform" class="form-label fw-bold small text-secondary">
                                Platform Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-globe text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 @error('platform') is-invalid @enderror"
                                    id="platform"
                                    name="platform"
                                    value="{{ old('platform', $platformPolicy->platform) }}"
                                    required
                                >
                                @error('platform')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Reporting URL --}}
                        <div class="mb-3">
                            <label for="reporting_url" class="form-label fw-bold small text-secondary">
                                Official Reporting URL <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-link text-muted"></i>
                                </span>
                                <input
                                    type="url"
                                    class="form-control border-start-0 @error('reporting_url') is-invalid @enderror"
                                    id="reporting_url"
                                    name="reporting_url"
                                    value="{{ old('reporting_url', $platformPolicy->reporting_url) }}"
                                    required
                                >
                                <a href="{{ $platformPolicy->reporting_url }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Test Link in New Tab">
                                    <i class="fas fa-external-link-alt"></i> Test
                                </a>
                                @error('reporting_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Reporting Instructions --}}
                        <div class="mb-3">
                            <label for="instructions" class="form-label fw-bold small text-secondary">
                                Step-by-Step Reporting Instructions <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control @error('instructions') is-invalid @enderror"
                                id="instructions"
                                name="instructions"
                                rows="5"
                                required
                            >{{ old('instructions', $platformPolicy->instructions) }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Last Verified Date --}}
                        <div class="mb-4">
                            <label for="last_verified_at" class="form-label fw-bold small text-secondary">
                                Verification Audit Date
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar-alt text-muted"></i>
                                </span>
                                <input
                                    type="date"
                                    class="form-control border-start-0 @error('last_verified_at') is-invalid @enderror"
                                    id="last_verified_at"
                                    name="last_verified_at"
                                    value="{{ old('last_verified_at', $platformPolicy->last_verified_at?->format('Y-m-d')) }}"
                                >
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('last_verified_at').value = new Date().toISOString().split('T')[0];">
                                    <i class="fas fa-check me-1"></i> Verify Today
                                </button>
                                @error('last_verified_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">
                                Click <strong>Verify Today</strong> to reset the 90-day verification counter if you have tested that this link and instructions are functioning.
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('moderator.platform-policies.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-crimson">
                                <i class="fas fa-save me-1"></i> Update Platform Policy
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection