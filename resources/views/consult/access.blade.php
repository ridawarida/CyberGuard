{{--
    Secure Consultation Workspace - access key entry.
    Module 3 feature owner: Johra-E-Jannat Oishy.

    Extends the shared layout (not a bare page) so the panic button is
    still available here too - a victim can be on this page in a stressful
    moment just as easily as anywhere else on the site.
--}}
@extends('layouts.app')

@section('title', 'Secure Consultation Access')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="mb-2">Access Your Consultation</h4>
                    <p class="text-muted small mb-4">
                        Enter the access key you were given when you submitted your report.
                        No account or personal details are needed.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">{{ $errors->first('access_key') }}</div>
                    @endif

                    <form action="{{ route('consult.access.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="access_key" class="form-label small">Access key</label>
                            <input type="text"
                                   name="access_key"
                                   id="access_key"
                                   class="form-control"
                                   placeholder="e.g. 4f9a2c1e..."
                                   autocomplete="off"
                                   autofocus
                                   required>
                        </div>
                        <button type="submit" class="btn btn-crimson w-100">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
