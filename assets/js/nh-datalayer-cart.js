jQuery(document).ready(function ($) {
    /**
     * NH Core — Frontend Cart Tracking
     * Emits GA4 add_to_cart, remove_from_cart, view_cart events
     * and Meta Pixel AddToCart / RemoveFromCart.
     * Reads product data from backend-injected data-nh-* attributes (source of truth).
     *
     * @version 1.4.0
     */

    /* ── Cart page: capture data before WC native remove processes it ──────── */
    // WC's wc-cart-fragments intercepts .cart .remove clicks via wc-ajax.
    // We capture product data in a capturing-phase listener BEFORE WC fires.
    var _pendingRemovedItem = null;

    document.addEventListener('click', function (e) {
        var removeLink = e.target.closest('.cart .remove, .nh-cart-remove');
        if (!removeLink) return;

        var $item = $(removeLink).closest('.nh-cart-item, .cart_item');
        if (!$item.length) return;

        _pendingRemovedItem = {
            productId:  $item.data('nh-product-id') || removeLink.getAttribute('data-product_id') || '',
            itemName:    $item.data('nh-product-name') || '',
            itemPrice:   parseFloat($item.data('nh-product-price')) || 0,
            quantity:    parseInt($item.data('nh-quantity')) || 1,
        };
    }, true); // capturing phase — fires BEFORE WC's bubbled handler

    /* ── added_to_cart (AJAX — ATC button) ─────────────────────────────────── */

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

    /* ── remove_from_cart (AJAX — side cart / cart page) ──────────────────── */
    $(document.body).on('removed_from_cart', function (event, fragments, cart_hash, $button) {
        // Side cart passes synthetic $button with data-nh-* via .data()
        // Cart page: WC passes the <a> remove link — use _pendingRemovedItem fallback
        var productId = '';
        var itemName = '';
        var itemPrice = 0;
        var quantity = 1;

        // 1. Try from $button (side cart synthetic button)
        if ($button && $button.length) {
            productId = $button.data('nh-product-id') || '';
            itemName = $button.data('nh-product-name') || '';
            itemPrice = parseFloat($button.data('nh-product-price')) || 0;
            quantity = parseInt($button.data('nh-quantity')) || 1;
        }

        // 2. Fallback: data captured from cart page remove link (capturing phase)
        if (!productId && _pendingRemovedItem) {
            productId = _pendingRemovedItem.productId;
            itemName = _pendingRemovedItem.itemName;
            itemPrice = _pendingRemovedItem.itemPrice;
            quantity = _pendingRemovedItem.quantity;
            _pendingRemovedItem = null;
        }

        // 3. Last resort: try reading from WC's remove link data attributes
        if (!productId && $button && $button.length) {
            productId = $button.attr('data-product_id') || '';
        }

        if (!productId) return;

        window.dataLayer = window.dataLayer || [];

        // GA4 Standard Event: remove_from_cart
        window.dataLayer.push({
            'event': 'remove_from_cart',
            'ecommerce': {
                'currency': 'COP',
                'value': parseFloat((itemPrice * quantity).toFixed(2)),
                'items': [{
                    'item_id': String(productId),
                    'item_name': itemName,
                    'price': itemPrice,
                    'quantity': quantity
                }]
            }
        });

        // Meta Pixel: RemoveFromCart
        if (typeof fbq !== 'undefined') {
            fbq('track', 'RemoveFromCart', {
                content_ids: [String(productId)],
                content_type: 'product',
                content_name: itemName,
                value: parseFloat((itemPrice * quantity).toFixed(2)),
                currency: 'COP'
            });
        }
    });

    /* ── Session-based events (non-AJAX fallback) ─────────────────────────── */
    // These are injected by PHP via wp_add_inline_script before this file.
    // They are one-shot: PHP reads session, emits inline, clears session.
    // No additional JS needed — the inline script handles it.
});
