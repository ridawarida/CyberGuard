@extends('layouts.app')

@section('title', 'Recovery Journal | CyberGuard')

@section('content')
<style>
    .cg-journal-page { min-height: calc(100vh - 80px); padding: 58px 16px 80px; background: radial-gradient(circle at 12% 8%, rgba(30,85,87,.12), transparent 30%), #f7f4ef; }
    .cg-journal-shell { max-width: 980px; margin: 0 auto; }
    .cg-journal-kicker { color: #b51e3d; font-size: .72rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
    .cg-journal-title { color: #172229; font-size: clamp(2.3rem, 5vw, 4.6rem); font-weight: 800; line-height: 1.02; max-width: 740px; }
    .cg-journal-lead { color: #526166; line-height: 1.7; max-width: 650px; }
    .cg-journal-panel { background: #fff; border: 1px solid #d9e1de; box-shadow: 0 18px 45px rgba(23,34,41,.08); }
    .cg-journal-key { background: #172229; color: #fff; letter-spacing: .12em; font-weight: 800; font-size: clamp(1.15rem, 3vw, 1.7rem); word-break: break-word; }
    .cg-journal-entry { border: 1px solid #d9e1de; background: #fff; }
    .cg-journal-stress { color: #b51e3d; font-weight: 800; white-space: nowrap; }
    .cg-journal-summary { white-space: pre-wrap; color: #33454a; line-height: 1.65; }
    .cg-journal-form { background: #f7faf9; border: 1px solid #e0ebe8; }
    .cg-journal-range { accent-color: #b51e3d; height: 1.5rem; }
    .cg-journal-range::-webkit-slider-runnable-track { height: .55rem; border-radius: .5rem; background: linear-gradient(90deg, #1e5557, #d79a35 55%, #b51e3d); }
    .cg-journal-range::-moz-range-track { height: .55rem; border-radius: .5rem; background: linear-gradient(90deg, #1e5557, #d79a35 55%, #b51e3d); }
    .cg-journal-range::-webkit-slider-thumb { margin-top: -.3rem; width: 1.35rem; height: 1.35rem; background: #fff; border: .2rem solid #b51e3d; }
    .cg-journal-range::-moz-range-thumb { width: 1.1rem; height: 1.1rem; background: #fff; border: .2rem solid #b51e3d; }
    .cg-journal-range-labels { display: flex; justify-content: space-between; color: #526166; font-size: .78rem; font-weight: 700; }
    .cg-journal-range-value { color: #b51e3d; font-size: 1.05rem; font-weight: 800; }
</style>

<main class="cg-journal-page">
    <div class="cg-journal-shell">
        <header class="mb-5">
            <p class="cg-journal-kicker mb-3"><i class="fas fa-seedling me-2"></i>Private recovery journal</p>
            <h1 class="cg-journal-title mb-3">Notice your progress, one honest moment at a time.</h1>
            <p class="cg-journal-lead mb-0">Write a short note about how you feel and give your stress a number from 1 to 10. Your journal is private and is not visible to CyberGuard staff.</p>
        </header>

        @if ($createdCode)
            <section class="cg-journal-panel p-4 p-md-5 mb-4" aria-labelledby="key-title">
                <p class="cg-journal-kicker mb-2">Your journal key</p>
                <h2 id="key-title" class="h4">Save this key somewhere private.</h2>
                <p class="text-secondary">This is the only way to unlock this journal on another visit. It will not be shown again after you leave this page.</p>
                <div class="cg-journal-key p-3 mb-3" id="journal-key">{{ $createdCode }}</div>
                <button type="button" class="btn btn-outline-dark" id="copy-key"><i class="fas fa-copy me-2"></i>Copy key</button>
            </section>
        @endif

        @if (!$journal)
            <section class="cg-journal-panel p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <p class="cg-journal-kicker mb-2">Start fresh</p>
                        <h2 class="h3">Create a private journal</h2>
                        <p class="text-secondary">We will create a random key for you. No account, name, email, or personal details are needed.</p>
                        <form method="POST" action="{{ route('recovery-journal.start') }}">
                            @csrf
                            <button class="btn btn-crimson px-4 py-3" type="submit"><i class="fas fa-plus me-2"></i>Start my recovery journal</button>
                        </form>
                    </div>
                    <div class="col-lg-6 border-start-lg">
                        <p class="cg-journal-kicker mb-2">Return to a journal</p>
                        <h2 class="h3">Unlock with your key</h2>
                        <form method="POST" action="{{ route('recovery-journal.unlock') }}">
                            @csrf
                            <label class="form-label" for="access_code">Journal key</label>
                            <input class="form-control form-control-lg mb-3" id="access_code" name="access_code" placeholder="CJ-XXXXXXXXXXXXXXXX" maxlength="19" autocomplete="off" required>
                            <button class="btn btn-outline-danger px-4 py-3" type="submit"><i class="fas fa-key me-2"></i>Unlock journal</button>
                        </form>
                    </div>
                </div>
            </section>
        @else
            <section class="cg-journal-panel p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                    <div><p class="cg-journal-kicker mb-2">Unlocked journal</p><h2 class="h3 mb-1">Your recovery notes</h2><p class="text-secondary mb-0">Only this unlocked session can view or change these entries.</p></div>
                    <form method="POST" action="{{ route('recovery-journal.forget') }}">@csrf<button class="btn btn-outline-secondary" type="submit"><i class="fas fa-lock me-2"></i>Lock journal</button></form>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('recovery-journal.entries.store') }}" class="cg-journal-form p-3 p-md-4 mb-4">
                    @csrf
                    <h3 class="h5 mb-3">How are you feeling today?</h3>
                    <div class="mb-3"><label class="form-label" for="summary">Short summary</label><textarea class="form-control" id="summary" name="summary" rows="4" maxlength="1000" required>{{ old('summary') }}</textarea></div>
                    <div class="row align-items-end g-3"><div class="col-sm-5"><label class="form-label d-flex justify-content-between align-items-center" for="stress_level"><span>Stress level</span><output class="cg-journal-range-value" id="stress-output">{{ old('stress_level', 5) }}/10</output></label><input class="form-range cg-journal-range" type="range" id="stress_level" name="stress_level" min="1" max="10" value="{{ old('stress_level', 5) }}" aria-label="Stress level from 1 to 10"><div class="cg-journal-range-labels"><span>1&nbsp; Low</span><span>10&nbsp; High</span></div></div><div class="col-sm-7 text-sm-end"><button class="btn btn-crimson" type="submit"><i class="fas fa-pen me-2"></i>Save entry</button></div></div>
                </form>

                @forelse ($journal->entries as $entry)
                    <article class="cg-journal-entry p-3 p-md-4 mb-3">
                        <div class="d-flex justify-content-between gap-3 flex-wrap mb-3"><time class="small text-secondary" datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->format('M j, Y g:i A') }}</time><span class="cg-journal-stress">Stress {{ $entry->stress_level }}/10</span></div>
                        <p class="cg-journal-summary mb-3">{{ $entry->summary }}</p>
                        <div class="d-flex gap-2"><button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-entry-{{ $entry->id }}"><i class="fas fa-pen me-1"></i>Edit</button><form method="POST" action="{{ route('recovery-journal.entries.destroy', $entry) }}" onsubmit="return confirm('Delete this journal entry?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-trash me-1"></i>Delete</button></form></div>
                        <div class="collapse mt-3" id="edit-entry-{{ $entry->id }}"><form method="POST" action="{{ route('recovery-journal.entries.update', $entry) }}" class="cg-journal-form p-3">@csrf @method('PUT')<textarea class="form-control mb-2" name="summary" rows="3" maxlength="1000" required>{{ $entry->summary }}</textarea><label class="form-label d-flex justify-content-between" for="stress-{{ $entry->id }}"><span>Stress level</span><output class="cg-journal-range-value" id="stress-output-{{ $entry->id }}">{{ $entry->stress_level }}/10</output></label><input class="form-range cg-journal-range" id="stress-{{ $entry->id }}" type="range" name="stress_level" min="1" max="10" value="{{ $entry->stress_level }}" aria-label="Stress level from 1 to 10"><div class="cg-journal-range-labels mb-3"><span>1&nbsp; Low</span><span>10&nbsp; High</span></div><button class="btn btn-sm btn-crimson" type="submit">Save changes</button></form></div>
                    </article>
                @empty
                    <div class="text-center py-4"><i class="fas fa-book-open text-secondary fs-2 mb-3"></i><p class="text-secondary mb-0">Your first note can begin whenever you are ready.</p></div>
                @endforelse
            </section>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/recovery-journal.js') }}" defer></script>
@endpush
