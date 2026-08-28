@extends('layouts.app')

@section('title', 'Consultation')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Case {{ $consultation->incident->tracking_id ?? '#'.$consultation->incident_id }}</h4>
                <a href="{{ route('moderator.consultations.index') }}" class="btn btn-crimson-outline btn-sm">
                    Back to list
                </a>
            </div>

            @include('consult.partials.chat-thread', [
                'consultation' => $consultation,
                'viewerType' => 'moderator',
                'pollUrl' => route('moderator.consultations.poll', $consultation),
                'sendUrl' => route('moderator.consultations.send', $consultation),
            ])
        </div>
    </div>
</div>
@endsection
