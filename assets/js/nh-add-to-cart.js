/**
 * NH Add to Cart — Buy Now handler
 *
 * Agnostic of analytics. Fires standard WooCommerce/jQuery events
 * so external layers (GTM, GA4, Pixel) can listen via added_to_cart.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var buttons = document.querySelectorAll('.nh-add-to-cart__buy-now');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            var form = btn.closest('form.cart');
            if (!form) return;

            // ── Sync disabled state with ATC button ──────────────────────
            var atcBtn = form.querySelector('.single_add_to_cart_button');
            if (atcBtn) {
                var syncDisabled = function () {
                    var isDisabled = atcBtn.disabled || atcBtn.classList.contains('disabled');
                    btn.disabled = isDisabled;
                };

                // Initial sync
                syncDisabled();

                // Watch for WC class/attribute changes on the ATC button
                var observer = new MutationObserver(syncDisabled);
                observer.observe(atcBtn, {
                    attributes: true,
                    attributeFilter: ['class', 'disabled'],
                });
            }

            // ── Click handler ────────────────────────────────────────────
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                if (btn.disabled) return;

                var qtyInput = form.querySelector('.nh-qty__input, input.qty');
                var quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

                var productId   = parseInt(btn.dataset.nhProductId) || 0;
                var isVariable  = btn.dataset.isVariable === 'true';
                var variationId = 0;
                var variations  = {};

                if (isVariable) {
                    var variationInput = form.querySelector('input[name="variation_id"]');
                    variationId = variationInput ? parseInt(variationInput.value) || 0 : 0;

                    form.querySelectorAll('.variations select').forEach(function (select) {
                        if (select.value) variations[select.name] = select.value;
                    });

                    if (!variationId) return;
                }

                btn.classList.add('nh-add-to-cart__buy-now--loading');
                btn.disabled = true;

                var formData = new FormData();
                formData.append('action', 'nh_buy_now');
                formData.append('nonce', window.nh_cart_params.nonce);
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                if (variationId) {
                    formData.append('variation_id', variationId);
                    Object.keys(variations).forEach(function (key) {
                        formData.append('variations[' + key + ']', variations[key]);
                    });
                }

                fetch(window.nh_cart_params.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res.success) {
                            throw new Error(res.data && res.data.message ? res.data.message : 'Error');
                        }

                        if (typeof jQuery !== 'undefined') {
                            var $btn = jQuery(btn);
                            jQuery(document.body).trigger('added_to_cart', [
                                res.data.fragments || {},
                                res.data.cart_hash || '',
                                $btn,
                            ]);
                            jQuery(document.body).trigger('nh_buy_now_initiated', [$btn]);
                        }

                        window.location.href = res.data.checkout_url;
                    })
                    .catch(function (err) {
                        console.error('[NH Buy Now]', err);
                        btn.classList.remove('nh-add-to-cart__buy-now--loading');
                        // Re-sync with ATC state instead of hard-enabling
                        if (atcBtn) {
                            syncDisabled();
                        } else {
                            btn.disabled = false;
                        }
                    });
            });
        });
    });
})();
