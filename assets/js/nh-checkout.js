(function($) {
    'use strict';

    const NH_Checkout = {
        isMobileOpen: false,

        init: function() {
            this.bindEvents();
            this.updatePaymentRadioCards();
            this.updateShippingRadioCards();
            this.initReservationTimer();
        },

        initReservationTimer: function() {
            const $timer = $('#nh_checkout_timer_count');
            if (!$timer.length) return;

            let endTime = sessionStorage.getItem('nh_checkout_timer_end');
            if (!endTime) {
                endTime = Date.now() + (15 * 60 * 1000);
                sessionStorage.setItem('nh_checkout_timer_end', endTime);
            }

            const updateTimer = function() {
                const now = Date.now();
                const diff = Math.max(0, Math.floor((endTime - now) / 1000));
                const mins = String(Math.floor(diff / 60)).padStart(2, '0');
                const secs = String(diff % 60).padStart(2, '0');
                $timer.text(mins + ':' + secs);
            };

            updateTimer();
            setInterval(updateTimer, 1000);
        },

        bindEvents: function() {
            $(document.body).on('updated_checkout', this.onCheckoutUpdated.bind(this));
            $(document).on('change', '.wc_payment_methods input[type="radio"]', this.updatePaymentRadioCards.bind(this));
            $(document).on('change', 'ul#shipping_method input[type="radio"]', this.updateShippingRadioCards.bind(this));
            $(document).on('click', '#nh_summary_apply_coupon_btn', this.applySummaryCoupon.bind(this));
            $(document).on('click', '.woocommerce-remove-coupon', this.removeSummaryCoupon.bind(this));
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
            const $input = $('#nh_summary_coupon_code');
            const code = $input.val().trim();
            if (!code) return;

            const $btn = $('#nh_summary_apply_coupon_btn');
            $btn.prop('disabled', true).text('Aplicando...');

            $.ajax({
                type: 'POST',
                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                data: {
                    security: wc_checkout_params.apply_coupon_nonce,
                    coupon_code: code
                },
                success: function(response) {
                    $btn.prop('disabled', false).text('Aplicar');
                    $('.nh-checkout-coupon-notice').remove();
                    
                    if (response) {
                        const $couponCard = $('.nh-checkout-coupon-card');
                        if ($couponCard.length) {
                            $couponCard.after('<div class="nh-checkout-coupon-notice">' + response + '</div>');
                        } else {
                            $('.woocommerce-notices-wrapper').html(response).show();
                        }
                        if (response.indexOf('woocommerce-error') === -1) {
                            $input.val('');
                        }
                        $(document.body).trigger('update_checkout', { update_shipping_method: false });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Aplicar');
                }
            });
        },

        removeSummaryCoupon: function(e) {
            e.preventDefault();
            const $link = $(e.currentTarget);
            const couponCode = $link.data('coupon');
            if (!couponCode) return;

            $('.nh-checkout-coupon-notice').remove();

            $.ajax({
                type: 'POST',
                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_coupon'),
                data: {
                    security: wc_checkout_params.remove_coupon_nonce,
                    coupon: couponCode
                },
                success: function(response) {
                    if (response) {
                        const $couponCard = $('.nh-checkout-coupon-card');
                        if ($couponCard.length) {
                            $couponCard.after('<div class="nh-checkout-coupon-notice">' + response + '</div>');
                        }
                        $(document.body).trigger('update_checkout', { update_shipping_method: false });
                    }
                }
            });
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
                    const customBtnText = $radio.data('order_button_text');
                    if (customBtnText) {
                        $('#place_order').text(customBtnText).val(customBtnText);
                    } else {
                        $('#place_order').text('Realizar el pedido').val('Realizar el pedido');
                    }
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
