@extends('layouts.app')

@section('title', 'Platform Policies - CyberGuard')

@section('content')

<div class="container py-5">

```
<div class="text-center mb-5">
    <h1 class="fw-bold">Platform Policies</h1>

    <p class="text-muted">
        Learn how to report harmful or inappropriate content
        on different platforms.
    </p>
</div>

@if($policies->count() > 0)

    <div class="row g-4">

        @foreach($policies as $policy)

            <div class="col-md-6 col-lg-4">

                <div class="card h-100 shadow-sm border-0">

                    <div class="card-body p-4">

                        <div class="mb-3">
                            <i class="fas fa-globe fa-2x"
                               style="color: #DC143C;"></i>
                        </div>

                        <h4 class="fw-bold mb-3">
                            {{ $policy->platform }}
                        </h4>

                        <h6 class="fw-bold">
                            Reporting Instructions
                        </h6>

                        <p class="text-muted">
                            {{ $policy->instructions }}
                        </p>

                        <a href="{{ $policy->reporting_url }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-crimson w-100 mt-3">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Report on {{ $policy->platform }}
                        </a>

                        @if($policy->last_verified_at)

                            <p class="small text-muted mt-3 mb-0 text-center">
                                Last verified:
                                {{ \Carbon\Carbon::parse($policy->last_verified_at)->format('F d, Y') }}
                            </p>

                        @endif

                    </div>

                </div>

            </div>

        @endforeach

    </div>

@else

    <div class="text-center py-5">

        <i class="fas fa-file-alt fa-3x mb-3"
           style="color: #DC143C;"></i>

        <h3>No Platform Policies Available</h3>

        <p class="text-muted">
            Platform reporting policies have not been added yet.
        </p>

    </div>

@endif
```

</div>

@endsection
