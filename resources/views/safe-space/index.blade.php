@extends('layouts.app')

@section('title', 'Digital Safe Space')

@section('content')
<style>
    /*
     * CyberGuard - Digital Safe Space
     * Module 2 feature owner: Johra-E-Jannat Oishy
     * Prefix every class with cg-safe so this page does not collide with
     * Bootstrap, the base layout, or Module 1's cg-panic styles.
     */
    .cg-safe-page {
        min-height: calc(100vh - 72px);
        padding: 42px 16px 60px;
        background:
            radial-gradient(circle at 15% 15%, rgba(76, 175, 159, 0.12), transparent 32%),
            radial-gradient(circle at 85% 25%, rgba(220, 20, 60, 0.07), transparent 28%),
            #f7fbfa;
    }

    .cg-safe-shell {
        max-width: 980px;
        margin: 0 auto;
    }

    .cg-safe-heading {
        text-align: center;
        margin-bottom: 28px;
    }

    .cg-safe-heading .cg-safe-kicker {
        color: #DC143C;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .cg-safe-heading h1 {
        color: #173d3a;
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .cg-safe-heading p {
        max-width: 700px;
        margin: 0 auto;
        color: #5d6f6d;
        line-height: 1.7;
    }

    .cg-safe-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        gap: 24px;
        align-items: stretch;
    }

    .cg-safe-card {
        background: #ffffff;
        border: 1px solid #e2eeec;
        border-radius: 22px;
        box-shadow: 0 12px 35px rgba(24, 66, 62, 0.09);
        padding: 28px;
    }

    .cg-safe-breathe-card {
        text-align: center;
        min-height: 510px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .cg-safe-circle-stage {
        width: 280px;
        height: 280px;
        display: grid;
        place-items: center;
        margin: 8px auto 24px;
        position: relative;
    }

    .cg-safe-ring {
        position: absolute;
        inset: 20px;
        border-radius: 50%;
        border: 1px solid rgba(39, 125, 113, 0.18);
    }

    .cg-safe-circle {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #79cbbb, #3b9d8d);
        color: #ffffff;
        box-shadow: 0 18px 38px rgba(51, 145, 131, 0.28);
        transform: scale(0.88);
        transition: transform 4s ease-in-out, box-shadow 0.35s ease;
        will-change: transform;
    }

    .cg-safe-circle.is-inhaling {
        transform: scale(1.35);
    }

    .cg-safe-circle.is-holding {
        transform: scale(1.35);
    }

    .cg-safe-circle.is-exhaling {
        transform: scale(0.88);
        transition-duration: 6s;
    }

    .cg-safe-phase {
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
    }

    .cg-safe-countdown {
        font-size: 38px;
        font-weight: 300;
        line-height: 1;
        margin-top: 8px;
    }

    .cg-safe-instruction {
        color: #607471;
        margin-bottom: 18px;
    }

    .cg-safe-controls {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cg-safe-control {
        border: 0;
        border-radius: 999px;
        padding: 10px 20px;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        cursor: pointer;
    }

    .cg-safe-control-primary {
        background: #173d3a;
        color: #ffffff;
    }

    .cg-safe-control-secondary {
        background: #edf6f4;
        color: #245f57;
    }

    .cg-safe-quote-card {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 510px;
    }

    .cg-safe-quote-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        color: #DC143C;
        background: rgba(220, 20, 60, 0.08);
        font-size: 22px;
        margin-bottom: 22px;
    }

    .cg-safe-quote-label {
        color: #2c6c63;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .cg-safe-quote-text {
        color: #1e2d2b;
        font-size: 25px;
        line-height: 1.55;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .cg-safe-quote-author {
        color: #71827f;
        font-size: 15px;
        margin-bottom: 24px;
    }

    .cg-safe-new-quote {
        align-self: flex-start;
        border: 2px solid #DC143C;
        background: #ffffff;
        color: #DC143C;
        border-radius: 8px;
        padding: 9px 18px;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .cg-safe-new-quote:hover,
    .cg-safe-new-quote:focus-visible {
        background: #DC143C;
        color: #ffffff;
    }

    .cg-safe-new-quote:disabled {
        opacity: 0.6;
        cursor: wait;
    }

    .cg-safe-status {
        min-height: 20px;
        color: #7b8c89;
        font-size: 13px;
        margin-top: 12px;
    }

    .cg-safe-attribution {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #edf1f0;
        color: #8a9896;
        font-size: 12px;
    }

    .cg-safe-attribution a {
        color: #517a74;
    }

    @media (max-width: 820px) {
        .cg-safe-grid {
            grid-template-columns: 1fr;
        }

        .cg-safe-breathe-card,
        .cg-safe-quote-card {
            min-height: auto;
        }
    }

    @media (max-width: 576px) {
        .cg-safe-page {
            padding: 28px 12px 90px;
        }

        .cg-safe-card {
            padding: 22px 18px;
            border-radius: 18px;
        }

        .cg-safe-heading h1 {
            font-size: 28px;
        }

        .cg-safe-circle-stage {
            width: 235px;
            height: 235px;
        }

        .cg-safe-circle {
            width: 135px;
            height: 135px;
        }

        .cg-safe-quote-text {
            font-size: 21px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .cg-safe-circle,
        .cg-safe-circle.is-inhaling,
        .cg-safe-circle.is-holding,
        .cg-safe-circle.is-exhaling {
            transition: none;
            transform: scale(1);
        }
    }
</style>

<div class="cg-safe-page" data-safe-space>
    <div class="cg-safe-shell">
        <div class="cg-safe-heading">
            <div class="cg-safe-kicker">CyberGuard Support</div>
            <h1>Digital Safe Space</h1>
            <p>
                You can stay here for a moment. Follow the breathing circle slowly,
                then read one reassuring thought at a time.
            </p>
        </div>

        <div class="cg-safe-grid">
            <section class="cg-safe-card cg-safe-breathe-card" aria-labelledby="breathing-title">
                <h2 id="breathing-title" class="h4 fw-bold mb-2">Guided breathing</h2>
                <p class="cg-safe-instruction">Follow the circle. There is no need to rush.</p>

                <div class="cg-safe-circle-stage" aria-hidden="true">
                    <div class="cg-safe-ring"></div>
                    <div class="cg-safe-circle" data-breath-circle>
                        <div class="cg-safe-phase" data-breath-phase>Breathe in</div>
                        <div class="cg-safe-countdown" data-breath-countdown>4</div>
                    </div>
                </div>

                <div class="visually-hidden" aria-live="polite" data-breath-live>
                    Breathe in for 4 seconds.
                </div>

                <div class="cg-safe-controls">
                    <button type="button"
                            class="cg-safe-control cg-safe-control-primary"
                            data-breath-toggle>
                        Pause
                    </button>
                    <button type="button"
                            class="cg-safe-control cg-safe-control-secondary"
                            data-breath-reset>
                        Restart
                    </button>
                </div>
            </section>

            <aside class="cg-safe-card cg-safe-quote-card" aria-labelledby="quote-title">
                <div class="cg-safe-quote-icon" aria-hidden="true">
                    <i class="fas fa-heart"></i>
                </div>

                <div class="cg-safe-quote-label" id="quote-title">A thought for this moment</div>

                <blockquote class="mb-0">
                    <p class="cg-safe-quote-text" data-quote-text>
                        “You do not have to solve everything in this moment. Take one slow breath at a time.”
                    </p>
                    <footer class="cg-safe-quote-author" data-quote-author>— CyberGuard</footer>
                </blockquote>

                <button type="button" class="cg-safe-new-quote" data-new-quote>
                    <i class="fas fa-rotate me-1" aria-hidden="true"></i>
                    New quote
                </button>

                <div class="cg-safe-status" role="status" aria-live="polite" data-quote-status></div>

                <div class="cg-safe-attribution">
                    Inspirational quotes provided by
                    <a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer">ZenQuotes API</a>.
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/safe-space.js') }}?v=1" defer></script>
@endpush
