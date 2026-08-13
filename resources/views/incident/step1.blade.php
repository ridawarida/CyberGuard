@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h2 class="mb-2">Report an Incident — Step 1 of 3</h2>
            <p class="text-muted mb-4">Tell us when and where it happened.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('incident.wizard.postStep1') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Date and time of incident</label>
                    <input type="datetime-local" name="incident_date" class="form-control" value="{{ old('incident_date') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Platform</label>
                    <input type="text" name="platform" class="form-control" placeholder="e.g. Instagram, Facebook, WhatsApp" value="{{ old('platform') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select name="behavior_type" class="form-select" required>
                        <option value="" disabled selected>Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}" {{ old('behavior_type') == $category->name ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-danger w-100">Next: Describe What Happened</button>
            </form>
        </div>
    </div>
</div>
@endsection