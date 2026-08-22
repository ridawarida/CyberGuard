/**
 * CyberGuard - Interactive "Digital Safe Space"
 * Module 2 feature owner: Johra-E-Jannat Oishy
 *
 * Responsibilities:
 * 1. Run the timed breathing-circle sequence without reloading the page.
 * 2. Asynchronously request a ZenQuotes-backed quote from Laravel.
 *
 * The page still contains a local reassuring quote if the API/network fails.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-safe-space]');
    if (!root) return;

    var circle = root.querySelector('[data-breath-circle]');
    var phaseText = root.querySelector('[data-breath-phase]');
    var countdownText = root.querySelector('[data-breath-countdown]');
    var liveText = root.querySelector('[data-breath-live]');
    var toggleButton = root.querySelector('[data-breath-toggle]');
    var resetButton = root.querySelector('[data-breath-reset]');

    var quoteText = root.querySelector('[data-quote-text]');
    var quoteAuthor = root.querySelector('[data-quote-author]');
    var quoteButton = root.querySelector('[data-new-quote]');
    var quoteStatus = root.querySelector('[data-quote-status]');

    var prefersReducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var phases = [
        { label: 'Breathe in', seconds: 4, className: 'is-inhaling' },
        { label: 'Hold gently', seconds: 2, className: 'is-holding' },
        { label: 'Breathe out', seconds: 6, className: 'is-exhaling' }
    ];

    var phaseIndex = 0;
    var secondsLeft = phases[0].seconds;
    var timer = null;
    var paused = false;

    function removePhaseClasses() {
        circle.classList.remove('is-inhaling', 'is-holding', 'is-exhaling');
    }

    function paintPhase() {
        var phase = phases[phaseIndex];

        removePhaseClasses();

        if (!prefersReducedMotion) {
            // Force the browser to commit the previous transform before the
            // new class is added. This keeps expand/contract transitions clean.
            void circle.offsetWidth;
            circle.classList.add(phase.className);
        }

        phaseText.textContent = phase.label;
        countdownText.textContent = secondsLeft;
        liveText.textContent = phase.label + ' for ' + phase.seconds + ' seconds.';
    }

    function nextPhase() {
        phaseIndex = (phaseIndex + 1) % phases.length;
        secondsLeft = phases[phaseIndex].seconds;
        paintPhase();
    }

    function tick() {
        secondsLeft -= 1;

        if (secondsLeft <= 0) {
            nextPhase();
            return;
        }

        countdownText.textContent = secondsLeft;
    }

    function startBreathing() {
        if (timer) return;

        paused = false;
        toggleButton.textContent = 'Pause';
        paintPhase();
        timer = window.setInterval(tick, 1000);
    }

    function pauseBreathing() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }

        paused = true;
        toggleButton.textContent = 'Continue';
        liveText.textContent = 'Breathing exercise paused.';
    }

    function resetBreathing() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }

        phaseIndex = 0;
        secondsLeft = phases[0].seconds;
        paused = false;
        startBreathing();
    }

    function toggleBreathing() {
        if (paused || !timer) {
            startBreathing();
        } else {
            pauseBreathing();
        }
    }

    function showQuoteLoading(isLoading) {
        quoteButton.disabled = isLoading;
        quoteStatus.textContent = isLoading ? 'Finding a new quote…' : '';
    }

    function loadQuote() {
        if (!window.fetch) {
            quoteStatus.textContent = 'Your browser cannot load a new quote right now.';
            return;
        }

        showQuoteLoading(true);

        fetch('/safe-space/quote', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Quote request failed');
                }

                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.data || !payload.data.quote) {
                    throw new Error('Invalid quote response');
                }

                quoteText.textContent = '“' + payload.data.quote + '”';
                quoteAuthor.textContent = '— ' + (payload.data.author || 'Unknown');
                quoteStatus.textContent = 'New quote loaded.';
            })
            .catch(function () {
                // Do not replace the existing local quote. The dashboard still
                // provides the breathing exercise even when the network is down.
                quoteStatus.textContent = 'Could not reach the quote service. You can keep breathing with the current message.';
            })
            .finally(function () {
                quoteButton.disabled = false;
            });
    }

    toggleButton.addEventListener('click', toggleBreathing);
    resetButton.addEventListener('click', resetBreathing);
    quoteButton.addEventListener('click', loadQuote);

    startBreathing();
    loadQuote();

    // Small manual-test surface, similar to Module 1's CyberGuardPanic object.
    window.CyberGuardSafeSpace = {
        loadQuote: loadQuote,
        pause: pauseBreathing,
        start: startBreathing,
        reset: resetBreathing
    };
})();
