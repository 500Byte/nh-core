(function() {
    'use strict';

    if (!window.nhThankYouParams || !window.nhThankYouParams.status_url) {
        return;
    }

    const { order_id, order_key, status_url } = window.nhThankYouParams;
    let pollCount = 0;
    const maxPolls = 24; // 24 * 5s = 120s max
    const intervalTime = 5000;

    const pollStatus = () => {
        pollCount++;
        const separator = status_url.includes('?') ? '&' : '?';
        const targetUrl = `${status_url}${separator}key=${encodeURIComponent(order_key)}&t=${Date.now()}`;

        fetch(targetUrl, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response not ok');
            return response.json();
        })
        .then(data => {
            if (data.is_paid || data.status === 'processing' || data.status === 'completed') {
                // Update badge and reload smoothly
                const badge = document.getElementById('nh-order-badge-state');
                if (badge) {
                    badge.className = 'nh-order-badge nh-order-badge--confirmed';
                    badge.textContent = 'Pedido Confirmado';
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else if (data.status === 'failed' || data.status === 'cancelled') {
                // Update badge to failed state and reload
                const badge = document.getElementById('nh-order-badge-state');
                if (badge) {
                    badge.className = 'nh-order-badge nh-order-badge--failed';
                    badge.textContent = 'Pago no completado';
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else if (pollCount < maxPolls) {
                setTimeout(pollStatus, intervalTime);
            }
        })
        .catch(() => {
            if (pollCount < maxPolls) {
                setTimeout(pollStatus, intervalTime);
            }
        });
    };

    setTimeout(pollStatus, intervalTime);
})();
