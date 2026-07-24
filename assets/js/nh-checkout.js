(function($) {
    'use strict';

    const NH_Checkout = {
        isMobileOpen: false,

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
            $(document).on('click', '.nh-checkout-mobile-summary-toggle', this.toggleMobileSummary.bind(this));
            $(document).on('keypress', '#nh_summary_coupon_code', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#nh_summary_apply_coupon_btn').trigger('click');
                }
            });
        },

        toggleMobileSummary: function(e) {
            e.preventDefault();
            const $content = $('.nh-checkout-collapsible-content');
            const $arrow = $('.nh-toggle-arrow');
            const $label = $('.nh-toggle-label');
            const self = this;

            $content.stop(true, true).slideToggle(200, function() {
                if ($content.is(':visible')) {
                    $arrow.text('▴');
                    $label.text('Ocultar resumen');
                    self.isMobileOpen = true;
                } else {
                    $arrow.text('▾');
                    $label.text('Mostrar resumen');
                    self.isMobileOpen = false;
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
            if (this.isMobileOpen && $(window).width() < 768) {
                $('.nh-checkout-collapsible-content').show();
                $('.nh-toggle-arrow').text('▴');
                $('.nh-toggle-label').text('Ocultar resumen');
            }
        },

        updatePaymentRadioCards: function() {
            $('.wc_payment_methods li.wc_payment_method').each(function() {
                const $radio = $(this).find('input[type="radio"]');
                if ($radio.is(':checked')) {
                    $(this).addClass('active').css({
                        'background-color': '#FAF9F6'
                    });
                } else {
                    $(this).removeClass('active').css({
                        'background-color': '#FFFFFF'
                    });
                }
            });
        },

        updateShippingRadioCards: function() {
            $('ul#shipping_method li').each(function() {
                const $radio = $(this).find('input[type="radio"]');
                if ($radio.is(':checked')) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }
    };

    $(document).ready(function() {
        NH_Checkout.init();
    });

})(jQuery);
