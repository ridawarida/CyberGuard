/**
 * CyberGuard - Secure Consultation Workspace chat.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Loaded on both the victim's chat page and the moderator's chat page via
 * resources/views/consult/partials/chat-thread.blade.php. Which endpoints
 * to poll and post to are read from data attributes rather than hardcoded,
 * so one file serves both sides instead of two near-duplicates.
 *
 * Design rule for this file: plain polling, no websockets. The project's
 * declared stack is Laravel + vanilla JS with no broadcasting package, so
 * this checks for new messages on an interval instead of assuming a
 * socket connection nobody installed.
 */
(function () {
    'use strict';

    var POLL_INTERVAL_MS = 4000;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function bind() {
        var thread = document.querySelector('[data-consultation-thread]');
        if (!thread) return;

        var pollUrl = thread.getAttribute('data-poll-url');
        var sendUrl = thread.getAttribute('data-send-url');
        var viewerType = thread.getAttribute('data-viewer-type');
        var list = thread.querySelector('[data-consultation-messages]');
        var form = thread.querySelector('[data-consultation-form]');
        var input = thread.querySelector('[data-consultation-input]');
        var lastId = Number(thread.getAttribute('data-last-id')) || 0;
        var pollTimer = null;

        function scrollToBottom() {
            list.scrollTop = list.scrollHeight;
        }

        function formatTime(isoString) {
            try {
                // created_at arrives as UTC ISO 8601; Date converts it to
                // the viewer's own local time automatically.
                return new Date(isoString).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            } catch (e) {
                return '';
            }
        }

        function renderMessage(message) {
            var mine = message.sender_type === viewerType;

            var bubble = document.createElement('div');
            bubble.className = 'cg-consult-bubble ' + (mine ? 'cg-consult-bubble--mine' : 'cg-consult-bubble--theirs');

            var body = document.createElement('p');
            body.className = 'cg-consult-bubble-body';
            body.textContent = message.body;
            bubble.appendChild(body);

            if (message.created_at) {
                var time = document.createElement('span');
                time.className = 'cg-consult-bubble-time';
                time.textContent = formatTime(message.created_at);
                bubble.appendChild(time);
            }

            list.appendChild(bubble);
        }

        function poll() {
            fetch(pollUrl + '?after=' + lastId, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (payload) {
                    if (!payload || !payload.data || !payload.data.length) return;

                    payload.data.forEach(function (message) {
                        renderMessage(message);
                        lastId = Math.max(lastId, message.id);
                    });

                    scrollToBottom();
                })
                .catch(function () {
                    // Missed one tick. The next interval tries again.
                });
        }

        function send(event) {
            event.preventDefault();

            var body = (input.value || '').trim();
            if (!body) return;

            input.disabled = true;

            fetch(sendUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({ body: body })
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (payload) {
                    if (payload && payload.data) {
                        renderMessage(payload.data);
                        lastId = Math.max(lastId, payload.data.id);
                        scrollToBottom();
                        input.value = '';
                    }
                })
                .catch(function () {
                    // Leave whatever was typed in the box - nothing said
                    // during a hard conversation should just vanish
                    // because one request failed.
                })
                .finally(function () {
                    input.disabled = false;
                    input.focus();
                });
        }

        if (form) {
            form.addEventListener('submit', send);
        }

        scrollToBottom();
        pollTimer = window.setInterval(poll, POLL_INTERVAL_MS);

        window.addEventListener('beforeunload', function () {
            window.clearInterval(pollTimer);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
