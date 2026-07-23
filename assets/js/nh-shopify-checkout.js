(function($) {
    'use strict';

    const NH_Shopify_Checkout = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('click', '.nh-shopify-coupon-btn', this.applyCoupon.bind(this));
            $(document.body).on('updated_checkout', this.onCheckoutUpdated.bind(this));
        },

        applyCoupon: function(e) {
            e.preventDefault();
            const code = $('.nh-shopify-coupon-input').val();
            if (!code) return;
            
            // Set code in hidden woocommerce coupon input and trigger apply
            if ($('#coupon_code').length) {
                $('#coupon_code').val(code);
                $('button[name="apply_coupon"]').trigger('click');
            } else {
                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                    data: {
                        security: wc_checkout_params.apply_coupon_nonce,
                        coupon_code: code
                    },
                    success: function() {
                        $(document.body).trigger('update_checkout');
                    }
                });
            }
        },

        onCheckoutUpdated: function() {
            // Smoothly sync checkout state
        }
    };

    $(document).ready(function() {
        NH_Shopify_Checkout.init();
    });

})(jQuery);
