@extends('layouts.app')

@section('title', 'Create Timeline - CyberGuard')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <!-- Progress Indicator -->
            <div class="progress mb-4" style="height: 8px; border-radius: 4px;">
                <div class="progress-bar bg-danger" role="progressbar" 
                     style="width: {{ ($step / 3) * 100 }}%;" 
                     aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="3">
                </div>
            </div>
            
            <div class="d-flex justify-content-between mb-4">
                <span class="text-muted small">Step {{ $step }} of 3</span>
                <span class="text-muted small">
                    @if($step == 1)
                        Timeline Selection
                    @elseif($step == 2)
                        Add Incidents
                    @else
                        Case Details
                    @endif
                </span>
            </div>

            <!-- Wizard Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    
                    <!-- Step 1: Timeline Selection -->
                    @if($step == 1)
                    <h3 class="card-title mb-3" style="font-weight: 700; color: #1a1a1a;">
                        <i class="fas fa-timeline me-2" style="color: #DC143C;"></i>
                        Step 1: Timeline Selection
                    </h3>
                    <p class="text-muted mb-4">
                        Paste your Timeline-exclusive token and continue to build your case.
                    </p>

                    <form action="{{ route('timeline.wizard.postStep1') }}" method="POST">
                        @csrf
                        
                        <!-- Existing Timeline Option -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Existing Timeline</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-key text-muted"></i>
                                </span>
                                <input type="text" name="timeline_token" class="form-control @error('timeline_token') is-invalid @enderror" 
                                       placeholder="Paste your timeline-exclusive token (e.g., tl7X9b2K1pQ4z8w3M)"
                                       value="{{ old('timeline_token') }}">
                            </div>
                            @error('timeline_token')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <button type="submit" name="action" value="existing" class="btn btn-crimson mt-3">
                                <i class="fas fa-arrow-right me-1"></i> Next Step
                            </button>
                        </div>

                        <hr class="my-4">

                        <!-- New Timeline Option -->
                        <div>
                            <label class="form-label fw-semibold">No timeline created yet?</label>
                            <p class="text-muted small">Start a new timeline from scratch.</p>
                            <button type="submit" name="action" value="new" class="btn btn-crimson-outline">
                                <i class="fas fa-plus-circle me-1"></i> New Timeline
                            </button>
                        </div>
                    </form>

                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Step 2: Add Incidents -->
@elseif($step == 2)
<h3 class="card-title mb-3" style="font-weight: 700; color: #1a1a1a;">
    <i class="fas fa-file-alt me-2" style="color: #DC143C;"></i>
    Step 2: Add Incidents
</h3>
<p class="text-muted mb-4">
    Paste your incident-exclusive token to add the incident to the timeline.
</p>

<!-- Add Incident Form -->
<form action="{{ route('timeline.wizard.addIncident') }}" method="POST" class="mb-4">
    @csrf
    <div class="input-group">
        <span class="input-group-text bg-light border-end-0">
            <i class="fas fa-tag text-muted"></i>
        </span>
        <input type="text" name="incident_token" class="form-control @error('incident_token') is-invalid @enderror" 
               placeholder="Paste incident-exclusive token (e.g., rp4X9b2K1pQ4z8w3M)"
               value="{{ old('incident_token') }}">
        <button type="submit" class="btn btn-crimson">
            <i class="fas fa-plus me-1"></i> Add Incident
        </button>
    </div>
    @error('incident_token')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @if(session('success'))
        <div class="text-success small mt-1">{{ session('success') }}</div>
    @endif
</form>

<!-- Added Incidents List -->
@if(!empty($incidents))
    <div class="mb-4">
        <p class="fw-semibold mb-2">Added Incidents ({{ count($incidents) }}):</p>
        <div class="list-group">
            @foreach($incidents as $index => $incident)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <code>{{ $incident['token'] }}</code>
                        <span class="text-muted small ms-2">{{ $incident['overview'] }}</span>
                        <span class="text-muted small ms-2">Added: {{ $incident['added_at'] }}</span>
                    </div>
                    <form action="{{ route('timeline.wizard.removeIncident', $index) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="alert alert-light border text-center py-3 mb-4">
        <i class="fas fa-info-circle text-muted me-2"></i>
        No incidents added yet. Add your first incident above.
    </div>
@endif

<!-- Navigation -->
<div class="d-flex justify-content-between">
    <a href="{{ route('timeline.wizard.step1') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
    <form action="{{ route('timeline.wizard.postStep2') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-crimson">
            Next Step <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </form>
</div>

                    <!-- Step 3: Case Details -->
                    @elseif($step == 3)
                    <h3 class="card-title mb-3" style="font-weight: 700; color: #1a1a1a;">
                        <i class="fas fa-pen-fancy me-2" style="color: #DC143C;"></i>
                        Step 3: Case Details
                    </h3>
                    <p class="text-muted mb-4">
                        Add a description of your timeline case and choose a category.
                    </p>

                    <form action="{{ route('timeline.wizard.postStep3') }}" method="POST">
                        @csrf
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Add a short overview of the case you are building the timeline for...">{{ old('description') }}</textarea>
                            <div class="form-text">Max 500 characters.</div>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label fw-semibold">Category</label>
                            <select name="category" id="category" class="form-select @error('category') is-invalid @enderror">
                                <option value="">Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Summary of Added Incidents -->
                        @if(!empty($incidents))
                            <div class="alert alert-light border mb-4">
                                <p class="fw-semibold mb-1"><i class="fas fa-file-alt me-2"></i>Incidents to be added:</p>
                                <ul class="mb-0">
                                    @foreach($incidents as $incident)
                                        <li><code>{{ $incident['token'] }}</code></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Navigation -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('timeline.wizard.step2') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-crimson">
                                <i class="fas fa-save me-1"></i> Save Timeline
                            </button>
                        </div>
                    </form>
                    @endif

                </div>
            </div>

            <!-- Cancel Link -->
            <div class="text-center mt-4">
                <a href="{{ route('timeline.create') }}" class="text-muted text-decoration-none small">
                    <i class="fas fa-times me-1"></i> Cancel and return
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    .btn-crimson {
        background-color: #DC143C;
        color: #ffffff;
        border: none;
        padding: 10px 30px;
        font-weight: 600;
        font-size: 15px;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-family: 'Cairo', sans-serif;
    }
    
    .btn-crimson:hover {
        background-color: #b01030;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 20, 60, 0.4);
    }
    
    .btn-crimson-outline {
        background-color: transparent;
        color: #DC143C;
        border: 2px solid #DC143C;
        padding: 10px 30px;
        font-weight: 600;
        font-size: 15px;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-family: 'Cairo', sans-serif;
    }
    
    .btn-crimson-outline:hover {
        background-color: #DC143C;
        color: #ffffff;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .card-body {
        border-radius: 12px;
    }
    
    .input-group-text {
        border-radius: 6px 0 0 6px;
        background-color: #f8f9fa;
    }
    
    .form-control, .form-select {
        border-radius: 0 6px 6px 0;
        font-family: 'Cairo', sans-serif;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #DC143C;
        box-shadow: 0 0 0 0.2rem rgba(220, 20, 60, 0.15);
    }
    
    .progress-bar {
        background-color: #DC143C !important;
    }
    
    .list-group-item {
        border-radius: 6px !important;
        margin-bottom: 6px;
        border: 1px solid #e9ecef;
    }
</style>
@endsection