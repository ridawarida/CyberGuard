@extends('layouts.app')

@section('title', 'Your Consultation')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Secure Consultation</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('consult.export.form') }}" class="btn btn-crimson-outline btn-sm">
                        Export Case PDF
                    </a>
                    <form action="{{ route('consult.access.logout') }}" method="POST" class="m-0"
                          onsubmit="return confirm('End this session? You\'ll need your access key again to come back.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">End Session</button>
                    </form>
                </div>
            </div>

            @include('consult.partials.chat-thread', [
                'consultation' => $consultation,
                'viewerType' => 'victim',
                'pollUrl' => route('consult.session.poll'),
                'sendUrl' => route('consult.session.send'),
            ])
        </div>
    </div>
</div>
@endsection
