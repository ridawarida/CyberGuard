@extends('layouts.app')

@section('title', 'Case File Created - CyberGuard')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background-color: #DC143C; border-radius: 50%; 
                                    display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="fas fa-check" style="font-size: 36px; color: #ffffff;"></i>
                        </div>
                    </div>

                    <!-- Message -->
                    <h3 class="mb-3" style="font-weight: 700; color: #1a1a1a;">
                        @if($is_new)
                            Your Case File has been created!
                        @else
                            Your Case File has been edited!
                        @endif
                    </h3>

                    <p class="text-muted mb-4">
                        You may continue to access it via the unique token:
                    </p>

                    <!-- Token Display -->
                    <div class="mb-4">
                        <div class="bg-light p-3 rounded-3 d-inline-block" style="border: 2px dashed #DC143C;">
                            <code style="font-size: 24px; font-weight: 700; color: #DC143C; letter-spacing: 1px;">
                                {{ $data['case_file_token'] ?? 'cfXXXXXXXXXXXX' }}
                            </code>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToken()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <!-- Case File Summary -->
                    <div class="text-start bg-light p-3 rounded-3 mb-4" style="max-width: 500px; margin: 0 auto;">
                        <p class="mb-1"><strong>Description:</strong> {{ $data['description'] ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Category:</strong> {{ $data['category'] ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Incidents Added:</strong> {{ count($incidents) }}</p>
                    </div>

                    <!-- Return Button -->
                    <a href="{{ route('case-file.create') }}" class="btn btn-crimson">
                        <i class="fas fa-arrow-left me-1"></i> Return to Case File Page
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .btn-crimson {
        background-color: #DC143C;
        color: #ffffff;
        border: none;
        padding: 12px 35px;
        font-weight: 600;
        font-size: 16px;
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
    
    .card {
        border-radius: 12px;
    }
</style>

<script>
    function copyToken() {
        const token = '{{ $data['case_file_token'] ?? 'cfXXXXXXXXXXXX' }}';
        navigator.clipboard.writeText(token).then(() => {
            alert('Token copied to clipboard!');
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = token;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Token copied to clipboard!');
        });
    }
</script>
@endsection