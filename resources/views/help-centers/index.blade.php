@extends('layouts.app')

@section('title', 'Nearby Help Centers | CyberGuard')

@section('content')
<style>
    .directory-shell { background: #f7f4ef; min-height: calc(100vh - 80px); padding: 64px 0 80px; }
    .directory-kicker { color: #b51e3d; font-size: .75rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
    .directory-title { color: #172229; font-size: clamp(2.2rem, 5vw, 4.5rem); font-weight: 800; line-height: 1.05; max-width: 760px; }
    .directory-lead { color: #526166; font-size: 1.05rem; line-height: 1.7; max-width: 650px; }
    .directory-panel { background: #fff; border: 1px solid #d9e1de; box-shadow: 0 18px 45px rgba(23, 34, 41, .08); }
    .location-banner { background: #e7f0ed; border-left: 4px solid #1e5557; color: #173b3d; }
    .center-card { height: 100%; border: 1px solid #d9e1de; background: #fff; }
    .center-type { color: #b51e3d; font-size: .72rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .center-distance { color: #1e5557; font-weight: 800; white-space: nowrap; }
    .hotline-link { color: #1e5557; font-weight: 700; text-decoration: none; }
    .hotline-link:hover { color: #b51e3d; }
    .working-hours { color: #526166; font-size: .9rem; }
</style>

<main class="directory-shell">
    <div class="container">
        <div class="mb-5">
            <p class="directory-kicker mb-3"><i class="fas fa-location-dot me-2"></i>Immediate support directory</p>
            <h1 class="directory-title mb-3">Find help close to where you are.</h1>
            <p class="directory-lead mb-4">We can use your network location to show active crisis centers, clinics, hospitals, and hotlines in your city. Your exact address is never requested.</p>
            <button id="find-nearby" class="btn btn-crimson px-4 py-3" type="button">
                <i class="fas fa-compass me-2"></i>Find Nearby Help-centers
            </button>
        </div>

        <section class="directory-panel p-4 p-md-5" aria-live="polite">
            <div id="location-status" class="location-banner d-none p-3 mb-4"></div>
            <div id="directory-alert" class="alert alert-warning d-none" role="alert"></div>

            <div id="directory-empty" class="text-center py-5">
                <i class="fas fa-map-location-dot text-secondary fs-1 mb-3"></i>
                <h2 class="h4">Your local support options are one click away.</h2>
                <p class="text-secondary mb-0">Press the button above to search by your approximate city.</p>
            </div>

            <div id="directory-loading" class="d-none text-center py-5">
                <div class="spinner-border text-danger mb-3" role="status"><span class="visually-hidden">Loading</span></div>
                <p class="text-secondary mb-0">Checking your approximate location...</p>
            </div>

            <div id="directory-results" class="d-none">
                <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
                    <div>
                        <p class="directory-kicker mb-2">Nearby results</p>
                        <h2 class="h3 mb-0">Support in your area</h2>
                    </div>
                    <span id="result-count" class="text-secondary"></span>
                </div>
                <div id="center-list" class="row g-4"></div>
            </div>

            <div id="manual-search" class="border-top mt-4 pt-4">
                <h2 class="h5">Search another city</h2>
                <p class="small text-secondary">Use this if the approximate location is unavailable or not where you need help.</p>
                <form id="manual-search-form" class="row g-2" novalidate>
                    <div class="col-sm-8 col-md-9"><label class="visually-hidden" for="city">City</label><input id="city" name="city" class="form-control" maxlength="100" placeholder="Enter city name" required></div>
                    <div class="col-sm-4 col-md-3"><button class="btn btn-outline-danger w-100" type="submit"><i class="fas fa-magnifying-glass me-2"></i>Search city</button></div>
                </form>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/help-centers.js') }}" defer></script>
@endpush
