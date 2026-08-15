/**
 * CyberGuard - Quick Escape "Panic Button"
 * Module 1 feature owner: Johra-E-Jannat Oishy
 *
 * Loaded on every page through resources/views/partials/panic-button.blade.php.
 *
 * Design rule for this file: the escape must never depend on the network.
 * Configuration is fetched in the background, but a hardcoded fallback is
 * always ready, and the redirect fires without waiting for the server.
 */
(function () {
    'use strict';

    var FALLBACK = {
        decoy_url: 'https://www.wikipedia.org',
        decoy_label: 'Wikipedia',
        hotkey_enabled: true,
        hotkey_press_count: 2,
        hotkey_window_ms: 800,
        clear_form_fields: true,
        clear_local_storage: true,
        replace_history_entry: true
    };

    var settings = Object.assign({}, FALLBACK);
    var escapePresses = [];
    var alreadyFired = false;

    /**
     * Which part of the site the user is on. Coarse only, the server never
     * receives the full path.
     */
    function detectContext() {
        var path = window.location.pathname;

        if (path.indexOf('/incident') === 0) return 'wizard';
        if (path.indexOf('/timeline') === 0) return 'timeline';
        if (path.indexOf('/admin') === 0 || path.indexOf('/dashboard') === 0) return 'dashboard';
        if (path === '/' || path.indexOf('/panic') === 0) return 'public';

        return 'unknown';
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Paint an opaque white screen immediately. The redirect itself takes a
     * few hundred milliseconds, and that is long enough for somebody standing
     * behind the victim to read the screen.
     */
    function blankScreen() {
        var shield = document.createElement('div');
        shield.setAttribute('id', 'cg-panic-shield');
        shield.style.cssText = [
            'position:fixed',
            'inset:0',
            'background:#ffffff',
            'z-index:2147483647',
            'margin:0',
            'padding:0'
        ].join(';');
        document.documentElement.appendChild(shield);
    }

    /**
     * Empty every input on the page before leaving, so a restored form or a
     * browser autofill prompt cannot leak the narrative.
     */
    function clearFormFields() {
        // Reset first, then explicitly blank fields. Resetting after blanking
        // could restore server-rendered default values on sensitive forms.
        var forms = document.querySelectorAll('form');
        for (var f = 0; f < forms.length; f++) {
            try { forms[f].reset(); } catch (e) { /* ignore */ }
        }

        var fields = document.querySelectorAll('input, textarea, select');

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var type = (field.type || '').toLowerCase();

            if (type === 'checkbox' || type === 'radio') {
                field.checked = false;
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = -1;
            } else if (type !== 'hidden') {
                try { field.value = ''; } catch (e) { /* file inputs on old browsers */ }
            }
        }

        var editables = document.querySelectorAll('[contenteditable="true"]');
        for (var e2 = 0; e2 < editables.length; e2++) {
            editables[e2].textContent = '';
        }
    }

    function clearBrowserStorage() {
        try { window.localStorage.clear(); } catch (e) { /* private mode */ }
        try { window.sessionStorage.clear(); } catch (e) { /* private mode */ }
    }

    /**
     * Best effort notice to the server. keepalive lets the request survive the
     * page unload, and no promise is awaited.
     */
    function notifyServer(source) {
        try {
            fetch('/panic/trigger', {
                method: 'POST',
                keepalive: true,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    source: source,
                    context: detectContext()
                })
            }).catch(function () { /* the escape continues regardless */ });
        } catch (e) {
            /* the escape continues regardless */
        }
    }

    /**
     * The whole feature in execution order.
     */
    function panic(source) {
        if (alreadyFired) return;
        alreadyFired = true;

        blankScreen();

        if (settings.clear_form_fields) clearFormFields();
        if (settings.clear_local_storage) clearBrowserStorage();

        notifyServer(source || 'click');

        var target = settings.decoy_url || FALLBACK.decoy_url;

        // replace() overwrites the current history entry, so pressing Back
        // from the decoy site does not return to CyberGuard.
        if (settings.replace_history_entry !== false) {
            window.location.replace(target);
        } else {
            window.location.href = target;
        }

        // If the browser blocks the redirect for any reason, do not leave the
        // victim staring at a blank white page with their report behind it.
        window.setTimeout(function () {
            window.location.href = target;
        }, 1200);
    }

    /**
     * Escape pressed N times inside the configured window.
     */
    function handleKeydown(event) {
        if (!settings.hotkey_enabled) return;
        if (event.key !== 'Escape' && event.keyCode !== 27) return;

        var now = Date.now();
        var windowMs = settings.hotkey_window_ms || FALLBACK.hotkey_window_ms;
        var needed = settings.hotkey_press_count || FALLBACK.hotkey_press_count;

        escapePresses.push(now);
        escapePresses = escapePresses.filter(function (stamp) {
            return now - stamp <= windowMs;
        });

        if (escapePresses.length >= needed) {
            escapePresses = [];
            panic('hotkey');
        }
    }

    function loadSettings() {
        if (!window.fetch) return;

        fetch('/panic/config', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(function (payload) {
                if (payload && payload.data) {
                    settings = Object.assign({}, FALLBACK, payload.data);
                    updateLabel();
                }
            })
            .catch(function () {
                // Keep the fallback settings. The button still works.
            });
    }

    function updateLabel() {
        var hint = document.querySelector('[data-panic-hint]');
        if (!hint) return;

        if (settings.hotkey_enabled) {
            hint.textContent = 'Press Esc ' + settings.hotkey_press_count + ' times';
        } else {
            hint.textContent = 'Leave this site';
        }
    }

    function bind() {
        var button = document.querySelector('[data-panic-button]');

        if (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                panic('click');
            });
        }

        document.addEventListener('keydown', handleKeydown, true);

        loadSettings();
        updateLabel();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }

    // Exposed for the demo page and for manual testing in the console.
    window.CyberGuardPanic = {
        trigger: panic,
        settings: function () { return settings; }
    };
})();
