@extends('layouts.app')

@section('title', 'View Case File - CyberGuard')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            
            <!-- Case File Header -->
            <div class="case-file-header mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="fw-bold" style="color: #1a1a1a; font-size: 28px;">
                            <i class="fas fa-folder-open me-2" style="color: #DC143C;"></i>
                            Case File Details
                        </h2>
                        <p class="text-muted" style="font-size: 15px;">
                            Viewing case file: <code style="background-color: #f5f5f5; padding: 4px 12px; border-radius: 4px; font-weight: 600; color: #DC143C;">{{ $caseFile->tracking_id }}</code>
                        </p>
                    </div>
                    <!-- Save Changes Button (hidden by default, shown when changes are made) -->
                    <div id="saveChangesContainer" style="display: none;">
                        <button class="btn btn-crimson" id="saveChangesBtn">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <button class="btn btn-outline-secondary" id="cancelChangesBtn">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Case Description & Tags -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <label class="text-muted small text-uppercase fw-semibold">Case Description</label>
                            <p class="fw-semibold mb-0" style="font-size: 18px; color: #1a1a1a;">
                                {{ $caseFile->description }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-semibold">Tagged with</label>
                            <div>
                                <span class="badge" style="background-color: #DC143C; font-size: 14px; padding: 6px 16px; border-radius: 20px;">
                                    <i class="fas fa-tag me-1"></i> {{ $caseFile->category }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incidents List Container -->
            <div class="incidents-container" style="background-color: #D9D9D9; border-radius: 12px; padding: 25px 20px;">
                
                @if($caseFile->incidents->count() > 0)
                    @foreach($caseFile->incidents as $incident)
                    <div class="incident-card bg-white p-4 mb-3 rounded-3 shadow-sm" data-incident-token="{{ $incident->tracking_id }}" style="border-left: 4px solid #DC143C;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h5 class="fw-bold mb-1" style="color: #1a1a1a; font-size: 17px;">
                                    Incident # {{ $incident->tracking_id }}
                                </h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-calendar-alt me-1" style="color: #DC143C;"></i>
                                    {{ $incident->incident_date ? $incident->incident_date->format('M d, Y') : 'Date not set' }}
                                </p>
                                <p class="mb-2" style="color: #444444; font-size: 15px;">
                                    {{ $incident->overview ?? $incident->description ?? 'No overview available' }}
                                </p>
                            </div>
                            <div class="mt-2 mt-sm-0 d-flex gap-2">
                                <button class="btn btn-crimson-outline btn-sm view-incident-btn" onclick="alert('View Incident functionality coming soon!')" style="white-space: nowrap;">
                                    <i class="fas fa-eye me-1"></i> View Incident
                                </button>
                                <button class="btn btn-outline-danger btn-sm remove-incident-btn" 
                                        data-incident-token="{{ $incident->tracking_id }}"
                                        data-incident-id="{{ $incident->id }}"
                                        style="white-space: nowrap;">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #b0b0b0;"></i>
                        <p class="text-muted mt-3" style="font-size: 16px;">No incidents have been added to this case file yet.</p>
                    </div>
                @endif

                <!-- Incident Count -->
                <div class="mt-3 text-muted small">
                    <i class="fas fa-file-alt me-1"></i> 
                    <span id="incidentCount">{{ $caseFile->incidents->count() }}</span> incident(s) in this case file
                </div>

            </div>

            <!-- Back Button -->
            <div class="mt-4 text-center">
                <a href="{{ route('case-file.create') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Case File Page
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ✅ Confirmation Modal -->
<div class="modal fade" id="removeConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #DC143C;">
                <h5 class="modal-title fw-bold" style="color: #1a1a1a;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #DC143C;"></i>
                    Remove Incident
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" style="font-size: 16px;">
                    Are you sure you want to remove this incident from the case file?
                </p>
                <div class="bg-light p-3 rounded-3">
                    <p class="mb-1"><strong>Incident Token:</strong> <code id="modalIncidentToken">-</code></p>
                    <p class="mb-0"><strong>Overview:</strong> <span id="modalIncidentOverview">-</span></p>
                </div>
                <p class="text-muted small mt-2">
                    <i class="fas fa-info-circle me-1"></i> This incident will be removed from the case file, but the original incident will remain in the system.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Save Changes Confirmation Modal -->
<div class="modal fade" id="saveChangesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #28a745;">
                <h5 class="modal-title fw-bold" style="color: #1a1a1a;">
                    <i class="fas fa-save me-2" style="color: #28a745;"></i>
                    Save Changes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size: 16px;">
                    You have removed <span id="removedCount">0</span> incident(s) from this case file.
                </p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Click "Save" to permanently remove these incidents, or "Cancel" to undo all changes.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancelSaveBtn">
                    <i class="fas fa-undo me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-crimson" id="confirmSaveBtn">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-crimson {
        background-color: #DC143C;
        color: #ffffff;
        border: none;
        padding: 10px 28px;
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
        padding: 4px 14px;
        font-weight: 600;
        font-size: 13px;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-family: 'Cairo', sans-serif;
    }
    
    .btn-crimson-outline:hover {
        background-color: #DC143C;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
    }
    
    .incident-card {
        transition: all 0.3s ease;
    }
    
    .incident-card:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    }
    
    .incident-card.removing {
        opacity: 0.5;
        border-left-color: #dc3545 !important;
        background-color: #fff5f5 !important;
    }
    
    .incidents-container {
        min-height: 150px;
        transition: all 0.3s ease;
    }
    
    .badge {
        font-weight: 600;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
    }
</style>

<!-- ✅ JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store removed incidents in an array
    let removedIncidents = [];
    let saveContainerVisible = false;

    // DOM Elements
    const saveContainer = document.getElementById('saveChangesContainer');
    const saveBtn = document.getElementById('saveChangesBtn');
    const cancelBtn = document.getElementById('cancelChangesBtn');
    const incidentCountSpan = document.getElementById('incidentCount');

    // Modal Elements
    const removeModal = new bootstrap.Modal(document.getElementById('removeConfirmationModal'));
    const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
    const modalIncidentToken = document.getElementById('modalIncidentToken');
    const modalIncidentOverview = document.getElementById('modalIncidentOverview');

    const saveModal = new bootstrap.Modal(document.getElementById('saveChangesModal'));
    const removedCountSpan = document.getElementById('removedCount');
    const confirmSaveBtn = document.getElementById('confirmSaveBtn');
    const cancelSaveBtn = document.getElementById('cancelSaveBtn');

    // Track current incident being removed
    let currentIncidentElement = null;
    let currentIncidentToken = null;

    // --- Remove Incident Button Click ---
    document.querySelectorAll('.remove-incident-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const token = this.dataset.incidentToken;
            const card = this.closest('.incident-card');
            const overview = card.querySelector('p.mb-2')?.textContent?.trim() || 'No overview available';

            // Store reference
            currentIncidentElement = card;
            currentIncidentToken = token;

            // Set modal data
            modalIncidentToken.textContent = token;
            modalIncidentOverview.textContent = overview;

            // Show the modal
            removeModal.show();
        });
    });

    // --- Confirm Remove ---
    confirmRemoveBtn.addEventListener('click', function() {
        if (currentIncidentElement && currentIncidentToken) {
            // Mark as removed visually
            currentIncidentElement.classList.add('removing');
            currentIncidentElement.style.display = 'none';
            
            // Add to removed list
            removedIncidents.push(currentIncidentToken);

            // Update count
            const totalCards = document.querySelectorAll('.incident-card:not([style*="display: none"])').length;
            incidentCountSpan.textContent = totalCards;

            // Show save container
            saveContainer.style.display = 'block';
            saveContainerVisible = true;

            // Hide the modal
            removeModal.hide();

            // Reset references
            currentIncidentElement = null;
            currentIncidentToken = null;
        }
    });

    // --- Cancel Save ---
    cancelBtn.addEventListener('click', function() {
        // Undo all removals
        document.querySelectorAll('.incident-card.removing').forEach(card => {
            card.style.display = '';
            card.classList.remove('removing');
        });

        // Clear removed list
        removedIncidents = [];

        // Update count
        const totalCards = document.querySelectorAll('.incident-card:not([style*="display: none"])').length;
        incidentCountSpan.textContent = totalCards;

        // Hide save container
        saveContainer.style.display = 'none';
        saveContainerVisible = false;
    });

    // --- Save Changes (Show confirmation modal) ---
    saveBtn.addEventListener('click', function() {
        removedCountSpan.textContent = removedIncidents.length;
        saveModal.show();
    });

    // --- Confirm Save (Submit to server) ---
    confirmSaveBtn.addEventListener('click', function() {
        if (removedIncidents.length === 0) {
            saveModal.hide();
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('incidents_to_remove', JSON.stringify(removedIncidents));

        // Send request
        fetch('{{ route("case-file.removeIncidents", $caseFile->tracking_id) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Hide save container
                saveContainer.style.display = 'none';
                saveContainerVisible = false;
                
                // Reset removed list
                removedIncidents = [];

                // Update count
                const remainingCards = document.querySelectorAll('.incident-card:not([style*="display: none"])').length;
                incidentCountSpan.textContent = remainingCards;

                // Close modal
                saveModal.hide();

                // Show success message
                alert(data.message || 'Case file updated successfully!');

                // Reload page to reflect changes
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update case file.');
            }
        })
        .catch(error => {
            alert('An error occurred while saving changes.');
            console.error(error);
        });
    });

    // --- Cancel Save (from confirmation modal) ---
    cancelSaveBtn.addEventListener('click', function() {
        saveModal.hide();
    });

    // Handle modal hidden events to clean up
    document.getElementById('removeConfirmationModal').addEventListener('hidden.bs.modal', function() {
        currentIncidentElement = null;
        currentIncidentToken = null;
    });
});
</script>
@endsection