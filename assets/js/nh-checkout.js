(function($) {
    'use strict';

    const NH_Checkout = {
        init: function() {
            this.bindEvents();
            this.updatePaymentRadioCards();
            this.updateShippingRadioCards();
        },

        bindEvents: function() {
            $(document.body).on('updated_checkout', this.onCheckoutUpdated.bind(this));
            $(document).on('change', '.wc_payment_methods input[type="radio"]', this.updatePaymentRadioCards.bind(this));
            $(document).on('change', 'ul#shipping_method input[type="radio"]', this.updateShippingRadioCards.bind(this));
            $(document).on('click', '#nh_summary_apply_coupon_btn', this.applySummaryCoupon.bind(this));
            $(document).on('keypress', '#nh_summary_coupon_code', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#nh_summary_apply_coupon_btn').trigger('click');
                }
            });
        },

        applySummaryCoupon: function(e) {
            e.preventDefault();
            const code = $('#nh_summary_coupon_code').val().trim();
            if (!code) return;

            const $nativeInput = $('#coupon_code');
            const $nativeForm = $('form.checkout_coupon');

            if ($nativeInput.length && $nativeForm.length) {
                $nativeInput.val(code);
                $nativeForm.submit();
            } else {
                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                    data: {
                        security: wc_checkout_params.apply_coupon_nonce,
                        coupon_code: code
                    },
                    success: function(response) {
                        $('.woocommerce-error, .woocommerce-message').remove();
                        if (response) {
                            $('form.checkout').before(response);
                            $(document.body).trigger('update_checkout', { update_shipping_method: false });
                        }
                    }
                });
            }
        },

        onCheckoutUpdated: function() {
            this.updatePaymentRadioCards();
            this.updateShippingRadioCards();
        },

        updatePaymentRadioCards: function() {
            $('.wc_payment_methods li.wc_payment_method').each(function() {
                const $radio = $(this).find('input[type="radio"]');
                if ($radio.is(':checked')) {
                    $(this).css({
                        'border-color': 'var(--nh-headings, #1A1918)',
                        'box-shadow': '0 2px 10px rgba(0,0,0,0.06)',
                        'background-color': '#FFFFFF'
                    });
                } else {
                    $(this).css({
                        'border-color': 'var(--nh-border, #E5E3DF)',
                        'box-shadow': 'none',
                        'background-color': '#FFFFFF'
                    });
                }
            });
        },

        updateShippingRadioCards: function() {
            $('ul#shipping_method li').each(function() {
                const $radio = $(this).find('input[type="radio"]');
                if ($radio.is(':checked')) {
                    $(this).css({
                        'border-color': 'var(--nh-headings, #1A1918)',
                        'background-color': '#FAF9F6'
                    });
                } else {
                    $(this).css({
                        'border-color': 'var(--nh-border, #E5E3DF)',
                        'background-color': '#F8F7F5'
                    });
                }
            });
        }
    };

    $(document).ready(function() {
        NH_Checkout.init();
    });

})(jQuery);
