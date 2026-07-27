jQuery(document).ready(function ($) {
    /**
     * NH Core — Frontend Cart Tracking
     * Emits GA4 add_to_cart, remove_from_cart events
     * and Meta Pixel AddToCart / RemoveFromCart.
     * Reads product data from data-nh-* HTML attributes via .attr() (NOT .data(),
     * because jQuery caches .data() on first read and ignores later .attr() changes).
     *
     * @version 1.6.0
     */

    var nhCurrency = (window.nh_cart_params && window.nh_cart_params.currency) || 'COP';

    /* ── Cart page: capture data before WC native remove processes it ──────── */
    var _pendingRemovedItem = null;

    // Restore from sessionStorage (for full page reload on cart remove)
    try {
        var stored = sessionStorage.getItem('nh_pending_remove');
        if (stored) {
            _pendingRemovedItem = JSON.parse(stored);
            sessionStorage.removeItem('nh_pending_remove');
            // Emit event immediately (page reloaded after remove)
            setTimeout(function() { $(document.body).trigger('removed_from_cart', [{}, '', null]); }, 100);
        }
    } catch(e) {}

    document.addEventListener('click', function (e) {
        var removeLink = e.target.closest('.cart .remove, .nh-cart-remove');
        if (!removeLink) return;

        var $item = $(removeLink).closest('.nh-cart-item, .cart_item');
        if (!$item.length) return;

        _pendingRemovedItem = {
            productId:  $item.attr('data-nh-product-id') || removeLink.getAttribute('data-product_id') || '',
            itemName:    $item.attr('data-nh-product-name') || '',
            itemPrice:   parseFloat($item.attr('data-nh-product-price')) || 0,
            quantity:    parseInt($item.attr('data-nh-quantity')) || 1,
        };

        // Persist for full page reload (cart page remove is not AJAX)
        try { sessionStorage.setItem('nh_pending_remove', JSON.stringify(_pendingRemovedItem)); } catch(e) {}
    }, true);

    /* ── added_to_cart (AJAX — ATC button) ─────────────────────────────────── */

    $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        if (!$button || !$button.length) {
            console.warn('[NH Tracking] added_to_cart fired without button reference');
            return;
        }

        // 1. Resolve product ID — .attr() reads live HTML, not jQuery cache
        var productId = $button.attr('data-nh-product-id')
            || $button.attr('data-variation-id')
            || $button.attr('data-product_id')
            || $button.val()
            || $button.closest('form.cart').find('input[name="add-to-cart"]').val();

        if (!productId) {
            console.warn('[NH Tracking] Could not resolve product_id from button:', $button);
            return;
        }
        productId = String(productId);

        // 2. Resolve variant flag
        var isVariant = $button.hasClass('wvs_ajax_add_to_cart')
            || $button.hasClass('product_type_variable')
            || !!$button.attr('data-variation-id');

        // 3. Resolve item name
        var itemName = $button.attr('data-nh-product-name') || '';
        if (!itemName) {
            var $product = $button.closest('.product, .summary, .elementor-widget-woocommerce-product-title');
            if ($product.length) {
                itemName = $product.find('.product_title, .woocommerce-loop-product__title, h1').first().text().trim();
            }
        }

        // 4. Resolve price
        var itemPrice = parseFloat($button.attr('data-nh-product-price')) || 0;
        if (!itemPrice) {
            var $product = $button.closest('.product, .summary, .elementor-widget-woocommerce-product-title');
            if ($product.length) {
                var priceText = $product.find('.price .amount, .price ins .amount, .woocommerce-Price-amount').first().text();
                itemPrice = parseFloat(
                    priceText.replace(/[^\d.,]/g, '').replace(/\./g, '').replace(',', '.')
                ) || 0;
            }
        }

        // 5. Resolve quantity
        var quantity = parseInt($button.closest('form.cart').find('input.qty').val()) || 1;

        // 6. Resolve category
        var category = $button.attr('data-nh-category') || '';

        // 7. Resolve variation attributes
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

        // 8. Build event metadata
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

        // 9. GA4 Standard Event: add_to_cart
        window.dataLayer.push({
            'event': 'add_to_cart',
            'ecommerce': {
                'currency': nhCurrency,
                'value': parseFloat((itemPrice * quantity).toFixed(2)),
                'items': [itemData]
            }
        });

        // 10. Backward-compatible custom event
        window.dataLayer.push({
            'event': 'agregar_carrito',
            'product_id': productId,
            'item_is_variant': isVariant,
            'item_variant': itemVariant
        });

        // 11. Meta Pixel: AddToCart (with event_id for CAPI)
        if (typeof fbq !== 'undefined') {
            var eventId = 'add_to_cart_' + productId + '_' + (cart_hash || 'no_hash');
            fbq('track', 'AddToCart', {
                content_ids: [productId],
                content_type: 'product',
                content_name: itemName,
                value: parseFloat((itemPrice * quantity).toFixed(2)),
                currency: nhCurrency,
                event_id: eventId
            });
        }
    });

    /* ── remove_from_cart (AJAX — side cart / cart page) ──────────────────── */
    var _lastRemoveEventId = null;
    var _lastRemoveTimestamp = 0;
    $(document.body).on('removed_from_cart', function (event, fragments, cart_hash, $button) {
        var productId = '';
        var itemName = '';
        var itemPrice = 0;
        var quantity = 1;

        // 1. Try from $button (side cart synthetic button with .attr())
        if ($button && $button.length) {
            productId = $button.attr('data-nh-product-id') || '';
            itemName = $button.attr('data-nh-product-name') || '';
            itemPrice = parseFloat($button.attr('data-nh-product-price')) || 0;
            quantity = parseInt($button.attr('data-nh-quantity')) || 1;
        }

        // 2. Fallback: data captured from cart page remove link (capturing phase)
        if (!productId && _pendingRemovedItem) {
            productId = _pendingRemovedItem.productId;
            itemName = _pendingRemovedItem.itemName;
            itemPrice = _pendingRemovedItem.itemPrice;
            quantity = _pendingRemovedItem.quantity;
            _pendingRemovedItem = null;
        }

        // 3. Last resort: WC's remove link data attributes
        if (!productId && $button && $button.length) {
            productId = $button.attr('data-product_id') || '';
        }

        if (!productId) return;

        // Dedup: WC fragments and side cart JS both fire removed_from_cart
        var now = Date.now();
        if (_lastRemoveEventId === productId && (now - _lastRemoveTimestamp) < 500) return;
        _lastRemoveEventId = productId;
        _lastRemoveTimestamp = now;

        window.dataLayer = window.dataLayer || [];

        // GA4 Standard Event: remove_from_cart
        window.dataLayer.push({
            'event': 'remove_from_cart',
            'ecommerce': {
                'currency': nhCurrency,
                'value': parseFloat((itemPrice * quantity).toFixed(2)),
                'items': [{
                    'item_id': String(productId),
                    'item_name': itemName,
                    'price': itemPrice,
                    'quantity': quantity
                }]
            }
        });

        // Meta Pixel: RemoveFromCart (with event_id for CAPI)
        if (typeof fbq !== 'undefined') {
            fbq('track', 'RemoveFromCart', {
                content_ids: [String(productId)],
                content_type: 'product',
                content_name: itemName,
                value: parseFloat((itemPrice * quantity).toFixed(2)),
                currency: nhCurrency,
                event_id: 'remove_' + productId + '_' + Date.now()
            });
        }
    });

    /* ── Session-based events (non-AJAX fallback) ─────────────────────────── */
    // These are injected by PHP via wp_add_inline_script before this file.
    // They are one-shot: PHP reads session, emits inline, clears session.
});
