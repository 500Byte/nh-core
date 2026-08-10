/**
 * NH Cart Widget - AJAX Operations
 */
(function($) {
    'use strict';

    /* ── Refresco de nonce (páginas cacheadas por WP Rocket) ──────────────── */
    // El HTML cacheado lleva un nonce inline que caduca (~12h). admin-ajax
    // nunca se cachea: pedimos un nonce fresco antes de cada operación.
    let _pendingNonce = null;

    function getFreshNonce() {
        if (_pendingNonce) return _pendingNonce;

        _pendingNonce = new Promise(function(resolve) {
            if (typeof window.fetch !== 'function') {
                resolve(nh_cart_params?.nonce || '');
                return;
            }

            fetch(nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                credentials: 'same-origin',
                body: 'action=nh_get_cart_nonce',
            })
                .then(r => r.json())
                .then(res => {
                    _pendingNonce = null;
                    resolve((res && res.data && res.data.cart_nonce) || nh_cart_params?.nonce || '');
                })
                .catch(() => {
                    _pendingNonce = null;
                    resolve(nh_cart_params?.nonce || '');
                });
        });

        return _pendingNonce;
    }

    const NH_Cart = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('click', '.nh-cart-remove', this.removeItem.bind(this));
            $(document).on('click', '.nh-cart-clear', this.clearCart.bind(this));
            $(document).on('click', '.nh-cart-coupon-btn', this.applyCoupon.bind(this));
            $(document).on('click', '.nh-cart-remove-coupon', this.removeCoupon.bind(this));
        },

        removeItem: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const $item = $btn.closest('.nh-cart-item');
            const key = $btn.data('key') || $item.data('key');

            $item.fadeOut(300, () => {
                getFreshNonce().then((nonce) => {
                    return $.ajax({
                        url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        data: {
                            action: 'nh_remove_cart_item',
                            cart_item_key: key,
                            nonce: nonce
                        }
                    });
                }).then((response) => {
                    if (response && response.success) {
                        this.refreshCartFragments();
                    }
                });
            });
        },

        clearCart: function(e) {
            e.preventDefault();
            if (!confirm('¿Estás seguro de que quieres vaciar el carrito?')) return;

            getFreshNonce().then((nonce) => {
                return $.ajax({
                    url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'nh_clear_cart',
                        nonce: nonce
                    }
                });
            }).then((response) => {
                if (response && response.success) {
                    window.location.reload();
                }
            });
        },

        applyCoupon: function(e) {
            e.preventDefault();
            const $wrapper = $(e.currentTarget).closest('.nh-cart-coupon');
            const code = $wrapper.find('.nh-cart-coupon-input').val().trim();

            if (!code) {
                this.showError('Ingresa un código de cupón');
                return;
            }

            getFreshNonce().then((nonce) => {
                return $.ajax({
                    url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'nh_apply_coupon',
                        coupon_code: code,
                        nonce: nonce
                    }
                });
            }).then((response) => {
                if (response && response.success) {
                    this.refreshCartFragments();
                    $wrapper.find('.nh-cart-coupon-input').val('');
                } else {
                    this.showError(response?.data?.message || 'Cupón inválido');
                }
            });
        },

        removeCoupon: function(e) {
            e.preventDefault();
            const code = $(e.currentTarget).data('coupon');

            getFreshNonce().then((nonce) => {
                return $.ajax({
                    url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'nh_remove_coupon',
                        coupon_code: code,
                        nonce: nonce
                    }
                });
            }).then((response) => {
                if (response && response.success) {
                    this.refreshCartFragments();
                }
            });
        },

        refreshCartFragments: function() {
            $(document.body).trigger('wc_fragment_refresh');
            setTimeout(() => window.location.reload(), 400);
        },

        showLoading: function($element) {
            $element.addClass('nh-cart-loading');
        },

        hideLoading: function($element) {
            $element.removeClass('nh-cart-loading');
        },

        showError: function(message) {
            this.showToast(message, 'error');
        },

        showSuccess: function(message) {
            this.showToast(message, 'success');
        },

        showToast: function(message, type) {
            $('.nh-cart-toast').remove();
            const $toast = $('<div class="nh-cart-toast nh-cart-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            setTimeout(() => {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3500);
        }
    };

    $(document).ready(() => {
        NH_Cart.init();
    });

})(jQuery);
