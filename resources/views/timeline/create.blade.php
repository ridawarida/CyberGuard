@extends('layouts.app')

@section('title', 'Create Timeline - CyberGuard')

@section('content')
<div class="container-fluid px-4 px-md-5">
    
    <!-- Tagline Section -->
    <div class="tagline-section">
        <h1>Create a Timeline For Your Case</h1>
        <p class="subtitle">
            Build a chronological timeline of your reported incidents involving the same perpetrator(s) 
            in order to make your case stronger on an ongoing pattern of harassment or stalking.
        </p>
        <div class="action-row">
            <button class="btn btn-crimson" id="createTimelineBtn" onclick="window.location.href='{{ route('timeline.wizard.step1') }}'">
                <i class="fas fa-plus-circle me-2"></i> Create Timeline
            </button>
        </div>
    </div>

    <!-- Tutorial Box -->
    <div class="tutorial-box">
        <div class="row">
            <!-- Step 1 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="tutorial-step">
                    <div class="step-number">STEP 1</div>
                    <div class="step-text">
                        Select an existing timeline using your exclusive timeline token or create a new one.
                    </div>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="tutorial-step">
                    <div class="step-number">STEP 2</div>
                    <div class="step-text">
                        Select a submitted report using the report specific token. Continue to add more reports to your case, or move on to the next step.
                    </div>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="tutorial-step">
                    <div class="step-number">STEP 3</div>
                    <div class="step-text">
                        Add a short overview of the case you are building the timeline for. Choose a category that best describes your case.
                    </div>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="tutorial-step">
                    <div class="step-number">STEP 4</div>
                    <div class="step-text">
                        Press 'Save' and you're all done! When creating a new case, you will receive a unique token to access the timeline later.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Existing Timeline Section -->
    <div class="view-timeline-section">
        <p style="font-size: 15px; color: #444444; margin-bottom: 12px; font-weight: 500;">
            View an existing Timeline using its exclusive token:
        </p>
        <div class="input-group">
            <input type="text" class="form-control" id="timelineTokenInput" 
                   placeholder="Enter your timeline token (e.g., tl7X9b2K1pQ4z8w3M)" 
                   aria-label="Timeline token">
            <button class="btn btn-crimson" id="viewTimelineBtn" type="button">
                <i class="fas fa-eye me-1"></i> View
            </button>
            <button class="btn btn-danger ms-2" id="deleteTimelineBtn" type="button">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
        <div id="viewTimelineError" class="text-danger mt-2" style="display: none; font-size: 14px;">
            <i class="fas fa-exclamation-circle me-1"></i> Please enter a valid timeline token.
        </div>
        @if(session('success'))
            <div class="text-success mt-2" style="font-size:14px">{{ session('success') }}</div>
        @endif
        @if($errors->has('timeline_token'))
            <div class="text-danger mt-2" style="font-size:14px">{{ $errors->first('timeline_token') }}</div>
        @endif

        <form id="deleteTimelineForm" action="{{ route('timeline.delete') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="timeline_token" id="deleteTimelineToken">
        </form>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        <i class="fas fa-lock me-1"></i> Your privacy is protected. All data is encrypted and stored securely.
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        

        // View Timeline Button
        const viewBtn = document.getElementById('viewTimelineBtn');
        const tokenInput = document.getElementById('timelineTokenInput');
        const errorDiv = document.getElementById('viewTimelineError');

        viewBtn.addEventListener('click', function() {
            const token = tokenInput.value.trim();
            
            if (!token) {
                errorDiv.style.display = 'block';
                tokenInput.classList.add('is-invalid');
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                    tokenInput.classList.remove('is-invalid');
                }, 3000);
                return;
            }

            errorDiv.style.display = 'none';
            tokenInput.classList.remove('is-invalid');

            // Redirect to view timeline page
            window.location.href = `/timeline/view/${token}`;
        });

        // Delete Timeline Button
        const deleteBtn = document.getElementById('deleteTimelineBtn');
        const deleteForm = document.getElementById('deleteTimelineForm');
        const deleteTokenInput = document.getElementById('deleteTimelineToken');

        deleteBtn.addEventListener('click', function() {
            const token = tokenInput.value.trim();
            if (!token) {
                errorDiv.style.display = 'block';
                tokenInput.classList.add('is-invalid');
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                    tokenInput.classList.remove('is-invalid');
                }, 3000);
                return;
            }

            if (!confirm('Are you sure you want to delete this timeline? This action cannot be undone.')) {
                return;
            }

            deleteTokenInput.value = token;
            deleteForm.submit();
        });

        // Allow Enter key to trigger View
        tokenInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                viewBtn.click();
            }
        });

        // Clear error on input
        tokenInput.addEventListener('input', function() {
            errorDiv.style.display = 'none';
            tokenInput.classList.remove('is-invalid');
        });
    });
</script>
@endpush