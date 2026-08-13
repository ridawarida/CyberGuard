@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h2 class="mb-2">Report an Incident — Step 2 of 3</h2>
            <p class="text-muted mb-4">Describe what happened, in your own words.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('incident.wizard.postStep2') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">What happened?</label>
                    <textarea name="description" rows="6" class="form-control" placeholder="Describe the incident in detail..." required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Short overview (optional)</label>
                    <input type="text" name="overview" class="form-control" placeholder="A brief one-line summary" value="{{ old('overview') }}">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('incident.wizard.step1') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-danger">Next: Upload Evidence</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection