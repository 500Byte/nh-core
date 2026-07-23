(function () {
    'use strict';

    var SIM_INTERVAL_MS = 8000;
    var REAL_POLL_MS    = 30000;

    function initViewers() {
        var els = document.querySelectorAll('.livecounter');
        if (!els.length) return;

        els.forEach(function (el) {
            var mode  = el.getAttribute('data-mode') || 'simulated';
            var min   = parseInt(el.getAttribute('data-min'), 10) || 5;
            var max   = parseInt(el.getAttribute('data-max'), 10) || 18;
            var fixed = parseInt(el.getAttribute('data-fixed'), 10) || 12;
            var fallback = el.getAttribute('data-fallback') || '';

            // Store original descriptive text (only once)
            if (!el.dataset.originalText) {
                el.dataset.originalText = el.textContent;
            }
            var desc = el.dataset.originalText;

            // ── FIXED MODE ──────────────────────────────
            if (mode === 'fixed') {
                el.innerHTML = '<strong>' + fixed + '</strong> ' + desc;
                return;
            }

            // ── SIMULATED MODE ──────────────────────────
            if (mode === 'simulated') {
                var key   = 'nh_live_viewers_' + location.pathname;
                var count = parseInt(sessionStorage.getItem(key), 10);
                if (isNaN(count) || count < min || count > max) {
                    count = Math.floor(Math.random() * (max - min + 1)) + min;
                    sessionStorage.setItem(key, count);
                }

                el.innerHTML = '<strong>' + count + '</strong> ' + desc;

                var simInterval = setInterval(function () {
                    if (!document.body.contains(el)) {
                        clearInterval(simInterval);
                        return;
                    }
                    var delta = Math.floor(Math.random() * 3) - 1; // -1, 0, +1
                    count = Math.min(max, Math.max(min, count + delta));
                    sessionStorage.setItem(key, count);
                    el.innerHTML = '<strong>' + count + '</strong> ' + desc;
                }, SIM_INTERVAL_MS);
                return;
            }

            // ── REAL MODE ───────────────────────────────
            if (mode === 'real') {
                var cfg = window.nhLiveCounter || {};
                if (!cfg.ajaxUrl || !cfg.nonce) {
                    // Fallback: no AJAX config, show fallback text
                    el.innerHTML = desc;
                    return;
                }

                // Generate or retrieve unique visitor ID (per tab, 30 min TTL)
                var visitorKey = 'nh_visitor_id';
                var visitorId  = sessionStorage.getItem(visitorKey);
                if (!visitorId) {
                    visitorId = 'v_' + Math.random().toString(36).substring(2, 15);
                    sessionStorage.setItem(visitorKey, visitorId);
                }

                // Show initial state with fallback text
                el.innerHTML = '<strong>1</strong> ' + (fallback || desc);

                function pollServer() {
                    if (!document.body.contains(el)) {
                        clearInterval(pollInterval);
                        return;
                    }
                    var body = new FormData();
                    body.append('action', 'nh_track_visitor');
                    body.append('nonce', cfg.nonce);
                    body.append('visitor_id', visitorId);

                    fetch(cfg.ajaxUrl, {
                        method: 'POST',
                        body: body,
                        credentials: 'same-origin'
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success && data.data && typeof data.data.count === 'number') {
                            var c = Math.max(1, data.data.count);
                            el.innerHTML = '<strong>' + c + '</strong> ' + desc;
                        }
                    })
                    .catch(function () {
                        // Silently keep last count on network error
                    });
                }

                // First poll immediately, then every REAL_POLL_MS
                pollServer();
                var pollInterval = setInterval(pollServer, REAL_POLL_MS);
            }
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initViewers);
    } else {
        initViewers();
    }
    window.addEventListener('nh-ajax-filtered', initViewers);
})();
