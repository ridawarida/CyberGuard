@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h2 class="mb-2">Report an Incident — Step 3 of 3</h2>
            <p class="text-muted mb-4">Upload a screenshot as evidence (optional but recommended).</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('incident.wizard.postStep3') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Screenshot (JPG, PNG — max 5MB)</label>
                    <input type="file" name="evidence_image" class="form-control" accept="image/*">
                </div>

                <div class="alert alert-info small">
                    Your report is anonymous. No personal information is required to submit.
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('incident.wizard.step2') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection