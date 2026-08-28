@extends('layouts.app')

@section('title', 'Export Case PDF')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Build Your Case Document</h4>
                <a href="{{ route('consult.session') }}" class="btn btn-outline-secondary btn-sm">Back to chat</a>
            </div>
            <p class="text-muted">
                Select what to include, then download a PDF you can print and hand to police,
                your school, or HR.
            </p>

            <form action="{{ route('consult.export.generate') }}" method="POST">
                @csrf

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_description"
                                   value="1" id="incDesc" checked>
                            <label class="form-check-label" for="incDesc">
                                Incident description and details
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Timeline events</h6>
                        @forelse ($timelineEvents as $event)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="timeline_event_ids[]"
                                       value="{{ $event->id }}" id="event{{ $event->id }}" checked>
                                <label class="form-check-label" for="event{{ $event->id }}">
                                    {{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M j, Y') }}
                                    &mdash; {{ $event->behavior_type }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No timeline events recorded yet.</p>
                        @endforelse
                    </div>
                </div>

                <button type="submit" class="btn btn-crimson">Download PDF</button>
            </form>
        </div>
    </div>
</div>
@endsection
