jQuery(document).ready(function ($) {
    /**
     * NH Core — Frontend Cart Tracking
     * Emits GA4 add_to_cart, custom agregar_carrito, and Meta Pixel AddToCart.
     * Reads product data from backend-injected data-nh-* attributes (source of truth).
     *
     * @version 1.1.0
     */

    $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        if (!$button || !$button.length) {
            console.warn('[NH Tracking] added_to_cart fired without button reference');
            return;
        }

        // ─────────────────────────────────────────────
        // 1. Resolve product ID (string everywhere)
        // ─────────────────────────────────────────────
        var productId = $button.data('nh-product-id')
            || $button.data('variation_id')
            || $button.attr('data-product_id')
            || $button.attr('data-product-id')
            || $button.val()
            || $button.closest('form.cart').find('input[name="add-to-cart"]').val();

        if (!productId) {
            console.warn('[NH Tracking] Could not resolve product_id from button:', $button);
            return;
        }
        productId = String(productId);

        // ─────────────────────────────────────────────
        // 2. Resolve variant flag
        // ─────────────────────────────────────────────
        var isVariant = $button.hasClass('wvs_ajax_add_to_cart')
            || $button.hasClass('product_type_variable')
            || !!$button.data('variation_id')
            || !!$button.attr('data-variation-id');

        // ─────────────────────────────────────────────
        // 3. Resolve item name (data-nh-* = source of truth)
        // ─────────────────────────────────────────────
        var itemName = $button.data('nh-product-name') || '';
        if (!itemName) {
            var $product = $button.closest('.product, .summary, .elementor-widget-woocommerce-product-title');
            if ($product.length) {
                itemName = $product.find('.product_title, .woocommerce-loop-product__title, h1').first().text().trim();
            }
        }

        // ─────────────────────────────────────────────
        // 4. Resolve price (data-nh-* = source of truth)
        // ─────────────────────────────────────────────
        var itemPrice = parseFloat($button.data('nh-product-price')) || 0;
        if (!itemPrice) {
            var $product = $button.closest('.product, .summary, .elementor-widget-woocommerce-product-title');
            if ($product.length) {
                var priceText = $product.find('.price .amount, .price ins .amount, .woocommerce-Price-amount').first().text();
                // Colombian format: $ 45.000,00 or $45,000 → normalize to float
                itemPrice = parseFloat(
                    priceText
                        .replace(/[^\d.,]/g, '')
                        .replace(/\./g, '')
                        .replace(',', '.')
                ) || 0;
            }
        }

        // ─────────────────────────────────────────────
        // 5. Resolve quantity
        // ─────────────────────────────────────────────
        var quantity = parseInt($button.closest('form.cart').find('input.qty').val()) || 1;

        // ─────────────────────────────────────────────
        // 6. Resolve category
        // ─────────────────────────────────────────────
        var category = $button.data('nh-category') || '';

        // ─────────────────────────────────────────────
        // 7. Resolve variation attributes
        // ─────────────────────────────────────────────
        var itemVariant = undefined;
        if (isVariant) {
            var $form = $button.closest('form.variations_form');
            if ($form.length) {
                var selected = [];
                $form.find('select').each(function () {
                    var val = $(this).val();
                    if (val) {
                        var name = $(this).find('option[value="' + val + '"]').text() || val;
                        selected.push(name.trim());
                    }
                });
                if (selected.length > 0) {
                    itemVariant = selected.join(' / ');
                }
            }
            if (!itemVariant) {
                var activeSwatches = [];
                $button.closest('.product').find('.variable-item.selected, .color-variable-item.selected, .button-variable-item.selected, .image-variable-item.selected, .radio-variable-item.selected').each(function () {
                    var val = $(this).attr('data-value') || $(this).text();
                    if (val) activeSwatches.push(val.trim());
                });
                if (activeSwatches.length > 0) {
                    itemVariant = activeSwatches.join(' / ');
                }
            }
        }

        // ─────────────────────────────────────────────
        // 8. Build event metadata
        // ─────────────────────────────────────────────
        var itemData = {
            'item_id': productId,
            'item_name': itemName,
            'price': itemPrice,
            'quantity': quantity,
            'item_category': category || undefined,
            'item_variant': itemVariant || undefined,
            'item_is_variant': isVariant
        };

        window.dataLayer = window.dataLayer || [];

        // ─────────────────────────────────────────────
        // 9. GA4 Standard Event: add_to_cart
        // ─────────────────────────────────────────────
        window.dataLayer.push({
            'event': 'add_to_cart',
            'ecommerce': {
                'currency': 'COP',
                'value': parseFloat((itemPrice * quantity).toFixed(2)),
                'items': [itemData]
            }
        });

        // ─────────────────────────────────────────────
        // 10. Backward-compatible custom event
        // ─────────────────────────────────────────────
        window.dataLayer.push({
            'event': 'agregar_carrito',
            'product_id': productId,
            'item_is_variant': isVariant,
            'item_variant': itemVariant
        });

        // ─────────────────────────────────────────────
        // 11. Meta Pixel: AddToCart (with event_id for CAPI)
        // ─────────────────────────────────────────────
        if (typeof fbq !== 'undefined') {
            var eventId = 'add_to_cart_' + productId + '_' + (cart_hash || 'no_hash');
            fbq('track', 'AddToCart', {
                content_ids: [productId],
                content_type: 'product',
                content_name: itemName,
                value: parseFloat((itemPrice * quantity).toFixed(2)),
                currency: 'COP',
                event_id: eventId
            });
        }
    });
});
