@extends('layouts.app')

@section('title', 'Create Case File - CyberGuard')

@section('content')
<div class="container-fluid px-4 px-md-5">
    
    <!-- Tagline Section -->
    <div class="tagline-section">
        <h1>Create a Case File</h1>
        <p class="subtitle">
            Build a chronological case file of incidents involving the same perpetrator(s)
            in order to make your case stronger on an ongoing pattern of harassment or stalking.
        </p>
        <div class="action-row">
            <button class="btn btn-crimson" id="createCaseFileBtn" onclick="window.location.href='{{ route('case-file.wizard.step1') }}'">
                <i class="fas fa-plus-circle me-2"></i> Create Case File
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
                        Select an existing case file using its exclusive token or create a new one.
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
                        Add a short overview of the case file you are building. Choose a category that best describes your case.
                    </div>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="tutorial-step">
                    <div class="step-number">STEP 4</div>
                    <div class="step-text">
                        Press 'Save' and you're all done! When creating a new case file, you will receive a unique token to access it later.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Existing Case File Section -->
    <div class="view-case-file-section">
        <p style="font-size: 15px; color: #444444; margin-bottom: 12px; font-weight: 500;">
            View an existing case file using its exclusive token:
        </p>
        <div class="input-group">
                 <input type="text" class="form-control" id="caseFileTokenInput"
                     placeholder="Enter your case file token (e.g., cf7X9b2K1pQ4z8w3M)"
                     aria-label="Case file token">
            <button class="btn btn-crimson" id="viewCaseFileBtn" type="button">
                <i class="fas fa-eye me-1"></i> View
            </button>
            <button class="btn btn-danger ms-2" id="deleteCaseFileBtn" type="button">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
        <div id="viewCaseFileError" class="text-danger mt-2" style="display: none; font-size: 14px;">
            <i class="fas fa-exclamation-circle me-1"></i> Please enter a valid case file token.
        </div>
        @if(session('success'))
            <div class="text-success mt-2" style="font-size:14px">{{ session('success') }}</div>
        @endif
        @if($errors->has('case_file_token'))
            <div class="text-danger mt-2" style="font-size:14px">{{ $errors->first('case_file_token') }}</div>
        @endif

        <form id="deleteCaseFileForm" action="{{ route('case-file.delete') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="case_file_token" id="deleteCaseFileToken">
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
        

        // View Case File Button
        const viewBtn = document.getElementById('viewCaseFileBtn');
        const tokenInput = document.getElementById('caseFileTokenInput');
        const errorDiv = document.getElementById('viewCaseFileError');

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

            // Redirect to view case file page
            window.location.href = `/case-files/view/${token}`;
        });

        // Delete Case File Button
        const deleteBtn = document.getElementById('deleteCaseFileBtn');
        const deleteForm = document.getElementById('deleteCaseFileForm');
        const deleteTokenInput = document.getElementById('deleteCaseFileToken');

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

            if (!confirm('Are you sure you want to delete this case file? This action cannot be undone.')) {
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