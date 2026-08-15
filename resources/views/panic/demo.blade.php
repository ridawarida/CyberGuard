@extends('layouts.app')

@section('title', 'Panic Button Demo')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <h2 class="mb-2">Quick Escape Panic Button</h2>
            <p class="text-muted mb-4">
                Local demo page for Module 1. Type something below, put a value in the
                session, then trigger the escape and watch both disappear.
            </p>

            <div class="tutorial-box mb-4">
                <div class="tutorial-step mb-3">
                    <div class="step-number">STEP 1</div>
                    <div class="step-text">Type a fake report in the box below and press "Save to session".</div>
                </div>
                <div class="tutorial-step mb-3">
                    <div class="step-number">STEP 2</div>
                    <div class="step-text">Press the <strong>Escape</strong> key twice, or click the red Quick Escape button.</div>
                </div>
                <div class="tutorial-step">
                    <div class="step-number">STEP 3</div>
                    <div class="step-text">Come back to this page. The session value and the typed text are both gone.</div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Current session value</h5>
                    @if ($draft)
                        <div class="alert alert-warning mb-0">
                            <strong>incident_wizard.description:</strong> {{ $draft }}
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            Session is empty. Nothing survived.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Write a report</h5>
                    <form action="{{ route('panic.demo.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="description"
                                      class="form-control"
                                      rows="4"
                                      placeholder="He keeps messaging me from new accounts..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-crimson">Save to session</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Live configuration</h5>
                    <pre class="bg-light p-3 mb-0" style="font-size:13px;">{{ json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    <p class="text-muted mt-3 mb-0" style="font-size:13px;">
                        Served by <code>GET /panic/config</code>. An admin can change these
                        values without anybody editing JavaScript.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
