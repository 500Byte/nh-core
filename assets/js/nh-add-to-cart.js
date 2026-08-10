/**
 * NH Add to Cart — Buy Now + AJAX ATC handler
 *
 * 1. Buy Now: intercepts .nh-add-to-cart__buy-now clicks
 * 2. AJAX ATC: intercepts form.cart submit on single product pages
 *    (Elementor breaks WC's native wc-add-to-cart.js event handlers)
 *
 * Fires standard WooCommerce/jQuery events so external layers
 * (GTM, GA4, Pixel, side cart) can listen via added_to_cart.
 */
(function () {
    'use strict';

    /* ── Refresco de nonce (páginas cacheadas por WP Rocket) ──────────────── */
    // El HTML cacheado lleva un nonce inline que caduca (~12h). admin-ajax
    // nunca se cachea: pedimos un nonce fresco antes de cada submit para que
    // el add-to-cart no falle silenciosamente por nonce expirado.
    var _pendingNonce = null;

    function getFreshNonce() {
        if (_pendingNonce) return _pendingNonce;

        _pendingNonce = new Promise(function (resolve) {
            if (typeof window.fetch !== 'function') {
                resolve((window.nh_cart_params || {}).nonce || '');
                return;
            }

            var ajaxUrl = (window.nh_cart_params || {}).ajax_url || '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                credentials: 'same-origin',
                body: 'action=nh_get_cart_nonce',
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    _pendingNonce = null;
                    resolve((res && res.data && res.data.cart_nonce) || (window.nh_cart_params || {}).nonce || '');
                })
                .catch(function () {
                    _pendingNonce = null;
                    resolve((window.nh_cart_params || {}).nonce || '');
                });
        });

        return _pendingNonce;
    }

    /* ── AJAX Add to Cart (reemplaza wc-add-to-cart.js roto por Elementor) ── */
    function initAjaxATC() {
        if (typeof jQuery === 'undefined') return;

        var $ = jQuery;

        // Solo en single product pages (con form.cart)
        $('body.product-template-default form.cart, form.cart[data-product_id]').on('submit', function (e) {
            // No interceptar si el form ya está siendo manejado por AJAX de NH ATC widget
            if (this.dataset.nhAtcAjax === 'true') return;

            var $form = $(this);
            var $btn  = $form.find('.single_add_to_cart_button');

            if (!$btn.length || $btn.hasClass('disabled')) return;

            e.preventDefault();

            var productId   = parseInt($form.find('input[name="product_id"], button[name="add-to-cart"]').val()) || 0;
            var variationId = parseInt($form.find('input[name="variation_id"]').val()) || 0;
            var quantity    = parseInt($form.find('input.qty').val()) || 1;

            // Para productos variables, recoger variaciones
            var variations = {};
            $form.find('.variations select').each(function () {
                if (this.value) variations[this.name] = this.value;
            });

            if (!productId) return;

            $btn.addClass('loading').prop('disabled', true);

            getFreshNonce().then(function (nonce) {
                return $.ajax({
                    type: 'POST',
                    url:  (window.nh_cart_params || {}).ajax_url || '/wp-admin/admin-ajax.php',
                    data: {
                        action:      'nh_add_to_cart',
                        nonce:       nonce,
                        product_id:  productId,
                        variation_id: variationId,
                        quantity:    quantity,
                        variations:  variations,
                    },
                });
            }).then(function (res) {
                $btn.removeClass('loading').prop('disabled', false);

                if (res && res.success) {
                    $(document.body).trigger('added_to_cart', [
                        res.data.fragments || {},
                        res.data.cart_hash || '',
                        $btn,
                    ]);
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    $(document.body).trigger('wc_fragment_refresh');
                }
            }).catch(function () {
                $btn.removeClass('loading').prop('disabled', false);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initAjaxATC);

    /* ── Sync variación → data-nh-* (tracking preciso) ─────────────────────── */
    function initVariationSync() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        // Cuando WooCommerce resuelve una variación, actualizar precio + id en botones
        $(document).on('found_variation', 'form.variations_form', function (e, variation) {
            if (!variation) return;

            var $form   = $(this);
            var price   = variation.display_price;
            var varId   = variation.variation_id;

            // Actualizar ATC button
            $form.find('.single_add_to_cart_button')
                .attr('data-nh-product-price', price)
                .attr('data-nh-product-id', varId);

            // Actualizar Buy Now button
            $form.closest('.nh-add-to-cart__layout')
                .find('.nh-add-to-cart__buy-now')
                .attr('data-nh-product-price', price)
                .attr('data-nh-product-id', varId);
        });

        // Cuando se resetea la variación, volver al precio del padre
        $(document).on('reset_data', 'form.variations_form', function () {
            var $form = $(this);
            var productId = $form.data('product_id');

            $form.find('.single_add_to_cart_button')
                .attr('data-nh-product-id', productId)
                .removeAttr('data-nh-product-price');

            $form.closest('.nh-add-to-cart__layout')
                .find('.nh-add-to-cart__buy-now')
                .attr('data-nh-product-id', productId)
                .removeAttr('data-nh-product-price');
        });
    }

    document.addEventListener('DOMContentLoaded', initVariationSync);

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

                getFreshNonce().then(function (nonce) {
                    var formData = new FormData();
                    formData.append('action', 'nh_buy_now');
                    formData.append('nonce', nonce);
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);
                    if (variationId) {
                        formData.append('variation_id', variationId);
                        Object.keys(variations).forEach(function (key) {
                            formData.append('variations[' + key + ']', variations[key]);
                        });
                    }

                    return fetch(window.nh_cart_params.ajax_url, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                    }).then(function (r) { return r.json(); });
                })
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

                        // Delay antes de redirect para que tracking pixels completen su push
                        setTimeout(function () {
                            window.location.href = res.data.checkout_url;
                        }, 150);
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

    /* ── RE-SYNC DE SWATCHES ─────────────────────────────────────────────── */

    /**
     * Re-inicializa WVS swatches después de re-renders de Elementor
     * o contextos AJAX externos (Quick View, etc.).
     *
     * Guard clause: WC marca los forms inicializados con jQuery.data() (no un
     * atributo HTML data-). Verificamos con jQuery.data() que depende de jQuery
     * estar cargado, lo cual garantizamos con la dependencia del enqueue.
     * Esto evita reset de selección y peticiones AJAX duplicadas
     * en modo AJAX (>30 variaciones).
     */
    function reinitSwatches() {
        document.querySelectorAll('.variations_form').forEach(function (form) {
            var alreadyInit = window.jQuery && jQuery.data(form, 'wc_variation_form');
            if (alreadyInit) return;

            if (window.jQuery && jQuery.fn.wc_variation_form) {
                try {
                    jQuery(form).wc_variation_form();
                } catch (err) {
                    console.error('[NH ATC] wc_variation_form() ERROR:', err);
                }
            }
        });

        if (window.jQuery) {
            jQuery(document).trigger('woo_variation_swatches_init');
        }
    }

    // Re-init en el editor de Elementor (cada vez que se guarda/preview)
    document.addEventListener('DOMContentLoaded', function () {
        if (window.elementorFrontend && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/nh-add-to-cart.default',
                reinitSwatches
            );
        }

        // Siempre intentar re-init en DOMContentLoaded:
        // WC's wc-add-to-cart-variation.js puede ejecutarse antes de que
        // Elementor renderice el widget, dejando forms sin inicializar.
        setTimeout(function () {
            reinitSwatches();
        }, 500);
    });

    // Re-init en contextos AJAX externos (Quick View, etc.)
    if (window.jQuery) {
        jQuery(document.body).on('wc_quick_view_open nh_atc_ajax_loaded', reinitSwatches);
    }

})();
