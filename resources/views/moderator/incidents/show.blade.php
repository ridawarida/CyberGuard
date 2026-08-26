@extends('layouts.app')

@section('title', 'Case ' . $incident->tracking_id . ' | CyberGuard')

@section('content')

<div class="container py-4">

    {{-- Back + Header --}}
    <a href="{{ url()->previous() }}" class="text-decoration-none small">
        &larr; Back to workspace
    </a>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-4">
        <div>
            <h2 class="mb-0">
                Case <code>{{ $incident->tracking_id }}</code>
            </h2>

            <small class="text-muted">
                Claimed by {{ $incident->assignedModerator?->name }}
                on {{ $incident->claimed_at?->format('d M Y, h:i A') }}
            </small>
        </div>

        <form method="POST"
              action="{{ route('moderator.incidents.release', $incident->id) }}"
              onsubmit="return confirm('Release this case back to the open pool?');">
            @csrf

            <button type="submit" class="btn btn-outline-secondary btn-sm">
                Release Case
            </button>
        </form>
    </div>


    {{-- Success / Error Messages --}}
    @foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach


    {{-- Validation Errors --}}
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

        {{-- ========================================================= --}}
        {{-- LEFT COLUMN --}}
        {{-- ========================================================= --}}
        <div class="col-lg-7">

            {{-- Report Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="mb-3">
                        Report Details
                    </h5>

                    <dl class="row mb-0 small">

                        <dt class="col-4 text-muted fw-normal">
                            Incident date
                        </dt>

                        <dd class="col-8">
                            {{ $incident->incident_date?->format('d M Y, h:i A') }}
                        </dd>


                        <dt class="col-4 text-muted fw-normal">
                            Submitted on
                        </dt>

                        <dd class="col-8">
                            {{ $incident->created_at?->format('d M Y, h:i A') }}
                        </dd>


                        <dt class="col-4 text-muted fw-normal">
                            Platform
                        </dt>

                        <dd class="col-8">
                            {{ $incident->platform }}
                        </dd>


                        <dt class="col-4 text-muted fw-normal">
                            Region
                        </dt>

                        <dd class="col-8">
                            {{ $incident->region }}
                        </dd>


                        <dt class="col-4 text-muted fw-normal">
                            Category
                        </dt>

                        <dd class="col-8">
                            {{ $incident->behavior_type }}
                        </dd>

                    </dl>

                </div>
            </div>


            {{-- Overview --}}
            @if ($incident->overview)

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <h6 class="text-muted mb-2">
                            Short Overview
                        </h6>

                        <p class="mb-0">
                            {{ $incident->overview }}
                        </p>

                    </div>
                </div>

            @endif


            {{-- Victim Narrative --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h6 class="text-muted mb-2">
                        Victim Narrative
                    </h6>

                    <p class="mb-0" style="white-space: pre-line;">
                        {{ $incident->description }}
                    </p>

                </div>
            </div>


            {{-- Evidence --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h6 class="text-muted mb-3">
                        Evidence Screenshot
                    </h6>

                    @if ($incident->evidence_image)

                        <a href="{{ asset('storage/' . $incident->evidence_image) }}"
                           target="_blank">

                            <img src="{{ asset('storage/' . $incident->evidence_image) }}"
                                 alt="Evidence screenshot"
                                 class="img-fluid rounded border"
                                 onerror="this.hidden = true; document.getElementById('evidence-missing').hidden = false;">

                        </a>

                        <p id="evidence-missing"
                           class="text-danger small mt-2 mb-0"
                           hidden>

                            The evidence file is recorded in the database
                            but could not be loaded from storage.

                        </p>

                        <p class="text-muted small mt-2 mb-0">
                            Click the image to open it full size.
                        </p>

                    @else

                        <p class="text-muted small mb-0">
                            No screenshot was attached to this report.
                        </p>

                    @endif

                </div>
            </div>



            {{-- ========================================================= --}}
            {{-- AI THREAT SCANNER --}}
            {{-- ========================================================= --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <h5 class="mb-1">
                                AI Threat & Toxicity Assessment
                            </h5>

                            <p class="text-muted small mb-0">
                                Hive AI separately analyzes the incident
                                narrative and the attached evidence image.
                            </p>
                        </div>

                        <span class="badge bg-dark">
                            Hive V3
                        </span>

                    </div>


                    {{-- Scan Button --}}
                    <form method="POST"
                          action="{{ route('moderator.incidents.scan-threats', $incident->id) }}"
                          class="mt-4">

                        @csrf

                        <button type="submit"
                                class="btn btn-danger">

                            <i class="bi bi-shield-exclamation"></i>

                            Scan Text & Evidence

                        </button>

                    </form>



                    {{-- ================================================= --}}
                    {{-- GET RESULT FROM SESSION OR DATABASE --}}
                    {{-- ================================================= --}}

                    @php

                        /*
                         * If the scan was just performed, use the session result.
                         * Otherwise use the values saved in the database.
                         */

                        $scan = session('threat_scan');

                        if ($scan) {

                            $overallScore = $scan['overall_risk_score'] ?? 0;
                            $overallLevel = $scan['overall_risk_level'] ?? 'Not Scanned';
                            $overallReason = $scan['reason'] ?? null;

                            $textScore = $scan['text_risk_score'] ?? 0;
                            $textLevel = $scan['text_risk_level'] ?? 'Not Scanned';
                            $textReason = $scan['text_reason'] ?? 'No explanation available.';

                            $imageScore = $scan['image_risk_score'] ?? 0;
                            $imageLevel = $scan['image_risk_level'] ?? 'Not Scanned';
                            $imageReason = $scan['image_reason'] ?? 'No explanation available.';

                            $scanSource = $scan['source'] ?? 'Hive V3 API';

                        } elseif ($incident->ai_scanned_at) {

                            $overallScore = $incident->ai_risk_score ?? 0;
                            $overallLevel = $incident->ai_risk_level ?? 'Not Scanned';
                            $overallReason = $incident->ai_reason;

                            /*
                             * These are the detailed database fields added
                             * by your second migration.
                             */

                            $textScore = $incident->ai_text_risk_score ?? 0;
                            $textLevel = $incident->ai_text_risk_level ?? 'Not Scanned';
                            $textReason = $incident->ai_text_reason ?? 'No explanation available.';

                            $imageScore = $incident->ai_image_risk_score ?? 0;
                            $imageLevel = $incident->ai_image_risk_level ?? 'Not Scanned';
                            $imageReason = $incident->ai_image_reason ?? 'No explanation available.';

                            $scanSource = 'Hive V3 API';

                        } else {

                            $overallScore = 0;
                            $overallLevel = 'Not Scanned';
                            $overallReason = null;

                            $textScore = 0;
                            $textLevel = 'Not Scanned';
                            $textReason = 'Text has not been scanned yet.';

                            $imageScore = 0;
                            $imageLevel = 'Not Scanned';
                            $imageReason = 'Evidence image has not been scanned yet.';

                            $scanSource = 'Hive V3 API';

                        }

                    @endphp



                    {{-- ================================================= --}}
                    {{-- RESULTS --}}
                    {{-- ================================================= --}}

                    @if ($incident->ai_scanned_at || session('threat_scan'))

                        {{-- Overall Assessment --}}
                        <div class="card border mt-4">

                            <div class="card-body">

                                <div class="text-center">

                                    <div class="text-muted small text-uppercase fw-semibold">
                                        Overall AI Risk
                                    </div>

                                    <div class="display-4 fw-bold mt-1">

                                        {{ $overallScore }}

                                        <span class="fs-5 text-muted">
                                            / 100
                                        </span>

                                    </div>


                                    {{-- Overall Badge --}}
                                    @if ($overallLevel === 'High')

                                        <span class="badge bg-danger fs-6 px-3 py-2">
                                            High Risk
                                        </span>

                                    @elseif ($overallLevel === 'Medium')

                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                            Medium Risk
                                        </span>

                                    @elseif ($overallLevel === 'Low')

                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            Low Risk
                                        </span>

                                    @else

                                        <span class="badge bg-secondary fs-6 px-3 py-2">
                                            {{ $overallLevel }}
                                        </span>

                                    @endif

                                </div>


                                {{-- Overall Progress --}}
                                <div class="progress mt-4"
                                     style="height: 10px;">

                                    <div class="progress-bar
                                        @if ($overallLevel === 'High')
                                            bg-danger
                                        @elseif ($overallLevel === 'Medium')
                                            bg-warning
                                        @elseif ($overallLevel === 'Low')
                                            bg-success
                                        @else
                                            bg-secondary
                                        @endif"
                                        role="progressbar"
                                        style="width: {{ min(100, max(0, $overallScore)) }}%;">

                                    </div>

                                </div>


                                @if ($overallReason)

                                    <p class="text-muted small mt-3 mb-0">
                                        {{ $overallReason }}
                                    </p>

                                @else

                                    <p class="text-muted small text-center mt-2 mb-0">
                                        Overall risk is calculated from the
                                        available text and evidence analyses.
                                    </p>

                                @endif

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- TEXT + IMAGE RESULTS --}}
                        {{-- ================================================= --}}

                        <div class="row g-3 mt-1">


                            {{-- TEXT RISK --}}
                            <div class="col-md-6">

                                <div class="card border h-100">

                                    <div class="card-body">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <h6 class="mb-0">

                                                <i class="bi bi-chat-text"></i>

                                                Text Risk

                                            </h6>


                                            @if ($textLevel === 'High')

                                                <span class="badge bg-danger">
                                                    High
                                                </span>

                                            @elseif ($textLevel === 'Medium')

                                                <span class="badge bg-warning text-dark">
                                                    Medium
                                                </span>

                                            @elseif ($textLevel === 'Low')

                                                <span class="badge bg-success">
                                                    Low
                                                </span>

                                            @else

                                                <span class="badge bg-secondary">
                                                    {{ $textLevel }}
                                                </span>

                                            @endif

                                        </div>


                                        <div class="mt-3">

                                            <span class="fs-3 fw-bold">
                                                {{ $textScore }}
                                            </span>

                                            <span class="text-muted">
                                                / 100
                                            </span>

                                        </div>


                                        <div class="progress mt-2"
                                             style="height: 7px;">

                                            <div class="progress-bar
                                                @if ($textLevel === 'High')
                                                    bg-danger
                                                @elseif ($textLevel === 'Medium')
                                                    bg-warning
                                                @elseif ($textLevel === 'Low')
                                                    bg-success
                                                @else
                                                    bg-secondary
                                                @endif"
                                                style="width: {{ min(100, max(0, $textScore)) }}%;">

                                            </div>

                                        </div>


                                        <p class="small text-muted mt-3 mb-0">
                                            {{ $textReason }}
                                        </p>

                                    </div>

                                </div>

                            </div>



                            {{-- IMAGE RISK --}}
                            <div class="col-md-6">

                                <div class="card border h-100">

                                    <div class="card-body">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <h6 class="mb-0">

                                                <i class="bi bi-image"></i>

                                                Evidence Risk

                                            </h6>


                                            @if ($imageLevel === 'High')

                                                <span class="badge bg-danger">
                                                    High
                                                </span>

                                            @elseif ($imageLevel === 'Medium')

                                                <span class="badge bg-warning text-dark">
                                                    Medium
                                                </span>

                                            @elseif ($imageLevel === 'Low')

                                                <span class="badge bg-success">
                                                    Low
                                                </span>

                                            @else

                                                <span class="badge bg-secondary">
                                                    {{ $imageLevel }}
                                                </span>

                                            @endif

                                        </div>


                                        <div class="mt-3">

                                            <span class="fs-3 fw-bold">
                                                {{ $imageScore }}
                                            </span>

                                            <span class="text-muted">
                                                / 100
                                            </span>

                                        </div>


                                        <div class="progress mt-2"
                                             style="height: 7px;">

                                            <div class="progress-bar
                                                @if ($imageLevel === 'High')
                                                    bg-danger
                                                @elseif ($imageLevel === 'Medium')
                                                    bg-warning
                                                @elseif ($imageLevel === 'Low')
                                                    bg-success
                                                @else
                                                    bg-secondary
                                                @endif"
                                                style="width: {{ min(100, max(0, $imageScore)) }}%;">

                                            </div>

                                        </div>


                                        <p class="small text-muted mt-3 mb-0">
                                            {{ $imageReason }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- AI INFORMATION --}}
                        {{-- ================================================= --}}

                        <div class="alert alert-light border mt-3 mb-0">

                            <div class="small">

                                <strong>Analysis method:</strong>
                                Text + Evidence Image

                                <br>

                                <strong>AI provider:</strong>
                                {{ $scanSource }}

                                @if ($incident->ai_scanned_at)

                                    <br>

                                    <strong>Last scanned:</strong>
                                    {{ $incident->ai_scanned_at->format('d M Y, h:i A') }}

                                @endif

                            </div>

                        </div>

                    @else

                        {{-- Not scanned yet --}}
                        <div class="alert alert-secondary mt-4 mb-0">

                            <div class="d-flex align-items-center">

                                <i class="bi bi-info-circle me-2"></i>

                                <div>

                                    <strong>No AI assessment yet.</strong>

                                    <div class="small mt-1">
                                        Click
                                        <strong>Scan Text & Evidence</strong>
                                        to analyze this incident using Hive AI.
                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- RIGHT COLUMN --}}
        {{-- ========================================================= --}}

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm sticky-top"
                 style="top: 20px;">

                <div class="card-body">

                    <h5 class="mb-1">
                        Moderator Assessment
                    </h5>

                    <p class="text-muted small mb-4">
                        These notes are internal and are never shown
                        to the reporter.
                    </p>


                    <form method="POST"
                          action="{{ route('moderator.incidents.update', $incident->id) }}">

                        @csrf

                        @method('PUT')


                        {{-- Severity --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Severity level
                            </label>

                            <select name="severity"
                                    class="form-select"
                                    required>

                                @foreach ($severities as $severity)

                                    <option value="{{ $severity }}"
                                        {{ old('severity', $incident->severity) === $severity ? 'selected' : '' }}>

                                        {{ $severity }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Status --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Tracking status
                            </label>

                            <select name="status"
                                    class="form-select"
                                    required>

                                @foreach ($statuses as $value => $label)

                                    <option value="{{ $value }}"
                                        {{ old('status', $incident->status) === $value ? 'selected' : '' }}>

                                        {{ $label }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Notes --}}
                        <div class="mb-4">

                            <label class="form-label">
                                Internal evaluation notes
                            </label>

                            <textarea name="moderator_notes"
                                      rows="8"
                                      class="form-control"
                                      placeholder="What did you verify? What action is needed next?">{{ old('moderator_notes', $incident->moderator_notes) }}</textarea>

                        </div>


                        <button type="submit"
                                class="btn btn-danger w-100">

                            Save Assessment

                        </button>

                    </form>


                    @if ($incident->reviewed_at)

                        <p class="text-muted small text-center mt-3 mb-0">

                            Last reviewed
                            {{ $incident->reviewed_at->diffForHumans() }}

                        </p>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection