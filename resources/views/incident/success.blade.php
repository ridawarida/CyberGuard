@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 text-center">
            <div class="mb-4">
                <span class="display-1 text-success">✓</span>
            </div>
            <h2 class="mb-3">Report Submitted Successfully</h2>
            <p class="text-muted mb-4">
                Your report has been received. Please save your tracking code below —
                you'll need it to check the status of your case or link it to a timeline later.
            </p>

            <div class="alert alert-danger">
                <h3 class="mb-0" style="letter-spacing: 2px;">{{ $tracking_id }}</h3>
            </div>

            <p class="small text-muted mb-4">
                We recommend writing this code down or taking a screenshot, since it is not stored anywhere else for your privacy.
            </p>

            <a href="{{ route('incident.wizard.step1') }}" class="btn btn-outline-danger">Submit Another Report</a>
        </div>
    </div>
</div>
@endsection