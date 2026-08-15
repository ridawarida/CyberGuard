{{--
    Quick Escape "Panic Button" partial.
    Module 1 feature owner: Johra-E-Jannat Oishy.

    Included once at the bottom of resources/views/layouts/app.blade.php,
    so it appears on every victim facing page automatically.
--}}

<style>
    .cg-panic-wrap {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        font-family: 'Cairo', sans-serif;
    }

    .cg-panic-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #DC143C;
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 999px;
        padding: 12px 22px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.3px;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(220, 20, 60, 0.45);
        transition: transform 0.15s ease, background-color 0.15s ease;
    }

    .cg-panic-btn:hover,
    .cg-panic-btn:focus {
        background-color: #a50f2d;
        transform: translateY(-2px);
        outline: none;
    }

    .cg-panic-btn:focus-visible {
        box-shadow: 0 0 0 4px rgba(220, 20, 60, 0.35);
    }

    .cg-panic-hint {
        background-color: rgba(0, 0, 0, 0.72);
        color: #ffffff;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* Emergency use, so it must stay legible for everyone. */
    @media (prefers-reduced-motion: reduce) {
        .cg-panic-btn { transition: none; }
    }

    @media (max-width: 576px) {
        .cg-panic-wrap { right: 12px; bottom: 12px; }
        .cg-panic-btn { padding: 10px 18px; font-size: 14px; }
    }

    @media print {
        .cg-panic-wrap { display: none; }
    }
</style>

<div class="cg-panic-wrap" role="region" aria-label="Quick escape">
    <span class="cg-panic-hint" data-panic-hint>Press Esc 2 times</span>

    {{-- Plain form so the escape still works with JavaScript disabled.
         The JS handler intercepts the click and takes over when available. --}}
    <form action="{{ route('panic.escape') }}" method="POST" style="margin:0;">
        @csrf
        <button type="submit"
                class="cg-panic-btn"
                data-panic-button
                aria-label="Quick escape. Leaves this site immediately and clears the page.">
            <i class="fas fa-door-open" aria-hidden="true"></i>
            Quick Escape
        </button>
    </form>
</div>

<script src="{{ asset('js/panic.js') }}?v=1" defer></script>
