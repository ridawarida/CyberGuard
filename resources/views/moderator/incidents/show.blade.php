@extends('layouts.app')

@section('title', 'Case ' . $incident->tracking_id . ' | CyberGuard')

@section('content')
<div class="container py-4">

    <a href="{{ url()->previous() }}" class="text-decoration-none small">&larr; Back to workspace</a>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-4">
        <div>
            <h2 class="mb-0">Case <code>{{ $incident->tracking_id }}</code></h2>
            <small class="text-muted">
                Claimed by {{ $incident->assignedModerator?->name }}
                on {{ $incident->claimed_at?->format('d M Y, h:i A') }}
            </small>
        </div>
        <form method="POST" action="{{ route('moderator.incidents.release', $incident->id) }}"
              onsubmit="return confirm('Release this case back to the open pool?');">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Release Case</button>
        </form>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }}">{{ session($key) }}</div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- Left: the report itself --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Report Details</h5>

                    <dl class="row mb-0 small">
                        <dt class="col-4 text-muted fw-normal">Incident date</dt>
                        <dd class="col-8">{{ $incident->incident_date?->format('d M Y, h:i A') }}</dd>

                        <dt class="col-4 text-muted fw-normal">Submitted on</dt>
                        <dd class="col-8">{{ $incident->created_at?->format('d M Y, h:i A') }}</dd>

                        <dt class="col-4 text-muted fw-normal">Platform</dt>
                        <dd class="col-8">{{ $incident->platform }}</dd>

                        <dt class="col-4 text-muted fw-normal">Region</dt>
                        <dd class="col-8">{{ $incident->region }}</dd>

                        <dt class="col-4 text-muted fw-normal">Category</dt>
                        <dd class="col-8">{{ $incident->behavior_type }}</dd>
                    </dl>
                </div>
            </div>

            @if ($incident->overview)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Short Overview</h6>
                        <p class="mb-0">{{ $incident->overview }}</p>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Victim Narrative</h6>
                    <p class="mb-0" style="white-space: pre-line;">{{ $incident->description }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Evidence Screenshot</h6>
                    @if ($incident->evidence_image)
                        <a href="{{ asset('storage/' . $incident->evidence_image) }}" target="_blank">
                            <img src="{{ asset('storage/' . $incident->evidence_image) }}"
                                 alt="Evidence screenshot"
                                 class="img-fluid rounded border"
                                 onerror="this.hidden = true; document.getElementById('evidence-missing').hidden = false;">
                        </a>
                        <p id="evidence-missing" class="text-danger small mt-2 mb-0" hidden>
                            The evidence file is recorded in the database but could not be loaded from storage.
                        </p>
                        <p class="text-muted small mt-2 mb-0">Click the image to open it full size.</p>
                    @else
                        <p class="text-muted small mb-0">No screenshot was attached to this report.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: the assessment form --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="mb-1">Moderator Assessment</h5>
                    <p class="text-muted small mb-4">
                        These notes are internal and are never shown to the reporter.
                    </p>

                    <form method="POST" action="{{ route('moderator.incidents.update', $incident->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Severity level</label>
                            <select name="severity" class="form-select" required>
                                @foreach ($severities as $severity)
                                    <option value="{{ $severity }}"
                                        {{ old('severity', $incident->severity) === $severity ? 'selected' : '' }}>
                                        {{ $severity }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tracking status</label>
                            <select name="status" class="form-select" required>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('status', $incident->status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Internal evaluation notes</label>
                            <textarea name="moderator_notes" rows="8" class="form-control"
                                      placeholder="What did you verify? What action is needed next?">{{ old('moderator_notes', $incident->moderator_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">Save Assessment</button>
                    </form>

                    @if ($incident->reviewed_at)
                        <p class="text-muted small text-center mt-3 mb-0">
                            Last reviewed {{ $incident->reviewed_at->diffForHumans() }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
