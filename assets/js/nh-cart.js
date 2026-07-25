/**
 * NH Cart Widget - AJAX Operations
 */
(function($) {
    'use strict';

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
                $.ajax({
                    url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'nh_remove_cart_item',
                        cart_item_key: key,
                        nonce: nh_cart_params?.nonce || ''
                    },
                    success: (response) => {
                        if (response.success) {
                            this.refreshCartFragments();
                        }
                    }
                });
            });
        },

        clearCart: function(e) {
            e.preventDefault();
            if (!confirm('¿Estás seguro de que quieres vaciar el carrito?')) return;

            $.ajax({
                url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'nh_clear_cart',
                    nonce: nh_cart_params?.nonce || ''
                },
                success: (response) => {
                    if (response.success) {
                        window.location.reload();
                    }
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

            $.ajax({
                url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'nh_apply_coupon',
                    coupon_code: code,
                    nonce: nh_cart_params?.nonce || ''
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCartFragments();
                        $wrapper.find('.nh-cart-coupon-input').val('');
                    } else {
                        this.showError(response.data?.message || 'Cupón inválido');
                    }
                }
            });
        },

        removeCoupon: function(e) {
            e.preventDefault();
            const code = $(e.currentTarget).data('coupon');

            $.ajax({
                url: nh_cart_params?.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'nh_remove_coupon',
                    coupon_code: code,
                    nonce: nh_cart_params?.nonce || ''
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshCartFragments();
                    }
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
