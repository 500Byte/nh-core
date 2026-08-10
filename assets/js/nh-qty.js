/**
 * NH Qty — Unified quantity selector module
 *
 * Auto-detects context by cart_item_key presence:
 *   - Has cart_item_key (cart page, widget, side cart) → AJAX update via nh_update_cart_item
 *   - No cart_item_key (PDP) → local change event only
 *
 * BEM classes: .nh-qty, .nh-qty__btn, .nh-qty__btn--minus, .nh-qty__btn--plus,
 *              .nh-qty__btn--disabled, .nh-qty__input, .nh-qty--loading
 */
(function () {
    'use strict';

    const SELECTORS = {
        wrapper: '.nh-qty',
        input: '.nh-qty__input',
        btnMinus: '.nh-qty__btn--minus',
        btnPlus: '.nh-qty__btn--plus',
    };

    /**
     * Read min/max/step from an input element.
     * Falls back to sensible defaults when attributes are missing.
     */
    function getLimits(input) {
        const min = parseFloat(input.getAttribute('min')) || 1;
        const max = parseFloat(input.getAttribute('max'));
        const step = parseFloat(input.getAttribute('step')) || 1;
        return { min, max: isNaN(max) ? Infinity : max, step };
    }

    /**
     * Clamp qty to [min, max] respecting step.
     */
    function clampQty(qty, limits) {
        const { min, max, step } = limits;
        let val = qty;
        if (val < min) val = min;
        if (val > max) val = max;
        // Snap to step
        val = Math.round((val - min) / step) * step + min;
        // Floating point guard
        val = parseFloat(val.toPrecision(12));
        return val;
    }

    /**
     * Update disabled state of ± buttons based on current value.
     */
    function updateDisabledState(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        const btnMinus = wrapper.querySelector(SELECTORS.btnMinus);
        const btnPlus = wrapper.querySelector(SELECTORS.btnPlus);
        if (!input) return;

        const val = parseFloat(input.value) || 0;
        const { min, max } = getLimits(input);

        if (val <= min) {
            btnMinus?.classList.add('nh-qty__btn--disabled');
            btnMinus?.setAttribute('aria-disabled', 'true');
        } else {
            btnMinus?.classList.remove('nh-qty__btn--disabled');
            btnMinus?.removeAttribute('aria-disabled');
        }

        if (val >= max) {
            btnPlus?.classList.add('nh-qty__btn--disabled');
            btnPlus?.setAttribute('aria-disabled', 'true');
        } else {
            btnPlus?.classList.remove('nh-qty__btn--disabled');
            btnPlus?.removeAttribute('aria-disabled');
        }
    }

    /**
     * Set loading state on the wrapper.
     */
    function setLoading(wrapper, loading) {
        wrapper.classList.toggle('nh-qty--loading', loading);
        const input = wrapper.querySelector(SELECTORS.input);
        if (input) input.disabled = loading;
    }

    /**
     * Get the cart item key from data attribute or input name (cart[KEY][qty]).
     */
    /** Pending AJAX requests per wrapper to prevent spam */
    const pending = new WeakMap();

    function getCartItemKey(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        let key = input?.getAttribute('data-key') || wrapper.getAttribute('data-key') || '';
        if (!key && input?.name) {
            const match = input.name.match(/^cart\[([^\]]+)\]\[qty\]$/);
            if (match) key = match[1];
        }
        return key;
    }

    /**
     * Refresco de nonce (páginas cacheadas por WP Rocket).
     * admin-ajax nunca se cachea: pedimos nonce fresco antes de cada update.
     */
    let _pendingNonce = null;

    function getFreshNonce() {
        if (_pendingNonce) return _pendingNonce;

        _pendingNonce = new Promise((resolve) => {
            const params = window.nh_cart_params || window.nhSideCartParams || {};
            const ajaxUrl = params.ajax_url || '/wp-admin/admin-ajax.php';

            if (typeof window.fetch !== 'function') {
                resolve(params.nonce || '');
                return;
            }

            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                credentials: 'same-origin',
                body: 'action=nh_get_cart_nonce',
            })
                .then((r) => r.json())
                .then((res) => {
                    _pendingNonce = null;
                    resolve((res && res.data && res.data.cart_nonce) || params.nonce || '');
                })
                .catch(() => {
                    _pendingNonce = null;
                    resolve(params.nonce || '');
                });
        });

        return _pendingNonce;
    }

    /**
     * Send AJAX qty update.
     */
    function ajaxUpdate(wrapper, newQty) {
        const key = getCartItemKey(wrapper);
        if (!key) return;

        setLoading(wrapper, true);
        pending.set(wrapper, true);

        const params = window.nh_cart_params || window.nhSideCartParams || {};
        const ajaxUrl = params.ajax_url || '/wp-admin/admin-ajax.php';

        getFreshNonce().then((nonce) => {
            return fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'nh_update_cart_item',
                    cart_item_key: key,
                    quantity: newQty,
                    nonce: nonce,
                }),
            });
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    if (res.data?.subtotal) {
                        const subtotalEl = wrapper.closest('.nh-cart-item')?.querySelector('.nh-cart-product-subtotal');
                        if (subtotalEl) subtotalEl.innerHTML = res.data.subtotal;
                    }
                    if (res.data?.totals_html) {
                        const summary = document.querySelector('.nh-cart-summary');
                        if (summary) summary.innerHTML = res.data.totals_html;
                    }
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                }
            })
            .catch(() => {})
            .finally(() => { pending.delete(wrapper); setLoading(wrapper, false); });
    }

    /**
     * Update qty value — AJAX when cart_item_key exists, local otherwise.
     */
    function updateQty(wrapper, delta) {
        const input = wrapper.querySelector(SELECTORS.input);
        if (!input || pending.get(wrapper)) return;

        const { min, max, step } = getLimits(input);
        const current = parseFloat(input.value) || min;
        let newVal = current + delta * step;
        newVal = clampQty(newVal, { min, max, step });

        if (newVal === current) return;

        input.value = newVal;
        updateDisabledState(wrapper);

        const key = getCartItemKey(wrapper);
        if (key) {
            ajaxUpdate(wrapper, newVal);
        }
    }

    /**
     * Handle direct input changes (typed value).
     */
    function handleInputChange(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        if (!input) return;

        let val = parseFloat(input.value);
        if (isNaN(val)) val = 1;
        const { min, max, step } = getLimits(input);
        val = clampQty(val, { min, max, step });

        input.value = val;
        updateDisabledState(wrapper);

        const key = getCartItemKey(wrapper);
        if (key) {
            ajaxUpdate(wrapper, val);
        }
    }

    // ── Event delegation ──────────────────────────────────────────────────
    function init() {
        // Click ± buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest(SELECTORS.btnMinus) || e.target.closest(SELECTORS.btnPlus);
            if (!btn) return;
            e.preventDefault();

            const wrapper = btn.closest(SELECTORS.wrapper);
            if (!wrapper || btn.classList.contains('nh-qty__btn--disabled')) return;

            const delta = btn.matches(SELECTORS.btnPlus) ? 1 : -1;
            updateQty(wrapper, delta);
        });

        // Direct input change (blur or Enter)
        document.addEventListener('change', (e) => {
            if (!e.target.matches(SELECTORS.input)) return;
            const wrapper = e.target.closest(SELECTORS.wrapper);
            if (wrapper) handleInputChange(wrapper);
        });

        // Initialize all existing wrappers
        refreshAll();

        // Re-initialize after WC fragment refresh (cart page, mini cart)
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on(
                'updated_wc_div updated_cart_totals wc_fragments_refreshed wc_fragments_loaded',
                refreshAll
            );
        }
    }

    /**
     * Refresh disabled states on all qty wrappers.
     */
    function refreshAll() {
        document.querySelectorAll(SELECTORS.wrapper).forEach(updateDisabledState);
    }

    // Expose for external use
    window.NHQty = { refreshAll };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
