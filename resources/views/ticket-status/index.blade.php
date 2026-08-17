@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-2 text-center">Check Your Report Status</h2>
            <p class="text-muted mb-4 text-center">
                Enter the tracking code you received when you submitted your report.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ticket.status.search') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" name="tracking_id" class="form-control form-control-lg text-center"
                           placeholder="e.g. incABC123XYZ0" value="{{ old('tracking_id') }}" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Check Status</button>
            </form>

            @if (!empty($result))
                <div class="alert alert-info mt-4 text-center">
                    <p class="mb-1 text-muted">Tracking Code: <strong>{{ $trackingId }}</strong></p>
                    <h4 class="mb-0">{{ $statusLabel }}</h4>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection