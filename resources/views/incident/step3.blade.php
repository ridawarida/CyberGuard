@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <h2 class="mb-2">
                Report an Incident — Step 3 of 3
            </h2>

            <p class="text-muted mb-4">
                Upload a screenshot as evidence (optional but recommended).
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


            {{-- Redacted image --}}

            @if (session('incident_wizard.redacted_image'))

                <div class="card mb-4">

                    <div class="card-body">

                        <h5 class="mb-3">
                            Redacted Evidence
                        </h5>

                        <div class="alert alert-success">
                            Your evidence has been redacted successfully.
                        </div>

                        <img
                            src="{{ session('incident_wizard.redacted_image') }}"
                            alt="Redacted evidence"
                            class="img-fluid rounded border"
                            style="max-height: 500px;"
                        >

                        <div class="mt-3">

                            <a
                                href="{{ route('incident.wizard.redact') }}"
                                class="btn btn-outline-secondary"
                                onclick="prepareRedactedImageForEditing()"
                            >
                                Edit Again
                            </a>

                        </div>

                    </div>

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('incident.wizard.postStep3') }}"
                enctype="multipart/form-data"
            >

                @csrf


                {{-- Upload only when there is no redacted image --}}

                @if (!session('incident_wizard.redacted_image'))

                    <div class="mb-4">

                        <label class="form-label">
                            Screenshot (JPG, PNG - max 5MB)
                        </label>

                        <input
                            type="file"
                            name="evidence_image"
                            id="evidence_image"
                            class="form-control"
                            accept="image/*"
                        >

                        <button
                            type="button"
                            id="redactButton"
                            class="btn btn-outline-danger mt-2"
                        >
                            Edit / Redact Evidence
                        </button>

                        <div
                            id="redactError"
                            class="text-danger small mt-2"
                        ></div>

                    </div>

                @endif


                <div class="mb-4">

                    <label class="form-label">
                        Email (optional — to receive your tracking code)
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="you@example.com"
                    >

                    <small class="text-muted">
                        This is only used to send your tracking code
                        and is never stored.
                    </small>

                </div>


                <div class="alert alert-info small">
                    Your report is anonymous.
                    This field is optional and not required to submit.
                </div>


                <div class="d-flex justify-content-between">

                    <a
                        href="{{ route('incident.wizard.step2') }}"
                        class="btn btn-outline-secondary"
                    >
                        Back
                    </a>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Submit Report
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const imageInput = document.getElementById('evidence_image');
    const redactButton = document.getElementById('redactButton');
    const redactError = document.getElementById('redactError');

    redactButton.addEventListener('click', function () {

        redactError.textContent = '';

        const file = imageInput.files[0];

        if (!file) {
            redactError.textContent =
                'Please select an image before editing it.';
            return;
        }

        if (!file.type.startsWith('image/')) {
            redactError.textContent =
                'Please select a valid image file.';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            redactError.textContent =
                'Image must be smaller than 5MB.';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            sessionStorage.setItem(
                'incident_evidence_image',
                event.target.result
            );

            window.location.href =
                "{{ route('incident.wizard.redact') }}";
        };

        reader.readAsDataURL(file);
    });

});
</script>
@endsection