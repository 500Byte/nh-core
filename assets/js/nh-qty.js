/**
 * NH Qty — Unified quantity selector module
 *
 * Auto-detects context:
 *   - Inside .nh-cart-widget or .nh-cart-table-widget → AJAX update via wc_fragment_refresh
 *   - Otherwise (PDP, cart page native) → local update, triggers WooCommerce update event
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

    const AJAX_CONTEXT = '.nh-cart-widget, .nh-cart-table-widget';

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
     * Determine if this wrapper should use AJAX.
     */
    function isAjaxContext(wrapper) {
        return wrapper.closest(AJAX_CONTEXT) !== null;
    }

    /**
     * Get the cart item key from data attribute.
     */
    function getCartItemKey(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        return input?.getAttribute('data-key') || wrapper.getAttribute('data-key') || '';
    }

    /**
     * Send AJAX qty update.
     */
    function ajaxUpdate(wrapper, newQty) {
        const key = getCartItemKey(wrapper);
        if (!key) return;

        setLoading(wrapper, true);

        const params = window.nhSideCartParams || window.nh_cart_params || {};
        const ajaxUrl = params.ajax_url || '/wp-admin/admin-ajax.php';
        const nonce = params.nonce || params.cart_nonce || '';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'nh_update_cart_item',
                cart_item_key: key,
                quantity: newQty,
                nonce: nonce,
            }),
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    // Refresh WC fragments to update cart totals, mini cart, etc.
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    console.warn('[NH Qty]', res.data?.message || 'Update failed');
                }
            })
            .catch((err) => console.error('[NH Qty] AJAX error:', err))
            .finally(() => setLoading(wrapper, false));
    }

    /**
     * Update qty value (local or AJAX depending on context).
     */
    function updateQty(wrapper, delta) {
        const input = wrapper.querySelector(SELECTORS.input);
        if (!input) return;

        const { min, max, step } = getLimits(input);
        const current = parseFloat(input.value) || min;
        let newVal = current + delta * step;
        newVal = clampQty(newVal, { min, max, step });

        if (newVal === current) return;

        input.value = newVal;
        updateDisabledState(wrapper);

        if (isAjaxContext(wrapper)) {
            ajaxUpdate(wrapper, newVal);
        } else {
            // Local context: trigger change so WooCommerce handlers pick it up
            input.dispatchEvent(new Event('change', { bubbles: true }));
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

        if (isAjaxContext(wrapper)) {
            ajaxUpdate(wrapper, val);
        } else {
            input.dispatchEvent(new Event('change', { bubbles: true }));
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
