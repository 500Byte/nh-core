/**
 * NH Side Cart — nh-side-cart.js
 * Widget: NH_Side_Cart_Widget
 *
 * Completamente independiente de Elementor Pro.
 * Solo requiere: jQuery (WP core) + wc-cart-fragments (WooCommerce core)
 *
 * API pública: window.NHSideCart.open() / .close() / .toggle() / .refresh()
 */
(function ($) {
    'use strict';

    /* ─── Selectores BEM propios ──────────────────────────────────────────── */
    const S = {
        root:       '.nh-side-cart',
        trigger:    '.nh-side-cart__trigger',
        overlay:    '.nh-side-cart__overlay',
        drawer:     '.nh-side-cart__drawer',
        close:      '.nh-side-cart__close',
        body:       '.nh-side-cart__body',
        items:      '.nh-side-cart__items',
        badge:      '.nh-side-cart__badge',
        countLabel: '.nh-side-cart__count-label',
        subtotal:   '.nh-side-cart__subtotal-amount',
        removeBtn:  '.nh-side-cart__item-remove',
        shippingBar:      '.nh-side-cart__shipping-bar',
        shippingBarFill:  '.nh-side-cart__shipping-bar-fill',
        shippingBarText:  '.nh-side-cart__shipping-bar-text',
        emptyState: '.nh-side-cart__empty',
        openClass:  'nh-side-cart--open',
        bodyLock:   'nh-side-cart-open',
    };

    /* ─── Config (inyectado desde PHP via wp_localize_script) ────────────── */
    const cfg = window.nhSideCartParams || {
        ajax_url:          '/wp-admin/admin-ajax.php',
        nonce:             '',
        cart_url:          '/carrito/',
        checkout_url:      '/finalizar-compra/',
        free_shipping_threshold: 280000,
        currency_symbol:   '$',
    };

    /* ─── Estado ─────────────────────────────────────────────────────────── */
    let isOpen   = false;
    let isLoading = false;

    /* ─── Utils ──────────────────────────────────────────────────────────── */
    function formatPrice(amount) {
        return cfg.currency_symbol + '\u00a0' + Math.round(amount).toLocaleString('es-CO');
    }

    /* ─── Open / Close ───────────────────────────────────────────────────── */
    function open() {
        if (isOpen) return;
        isOpen = true;
        $(S.root).addClass(S.openClass);
        $('body').addClass(S.bodyLock);
        $(S.drawer).attr('aria-hidden', 'false');
        $(S.overlay).attr('aria-hidden', 'false');
        // Focus trap mínimo
        setTimeout(function () { $(S.close).focus(); }, 340);
    }

    function close() {
        if (!isOpen) return;
        isOpen = false;
        $(S.root).removeClass(S.openClass);
        $('body').removeClass(S.bodyLock);
        $(S.drawer).attr('aria-hidden', 'true');
        $(S.overlay).attr('aria-hidden', 'true');
        $(S.trigger).focus();
    }

    function toggle() {
        isOpen ? close() : open();
    }

    /* ─── Actualizar Contador del Badge ──────────────────────────────────── */
    function updateBadge(count) {
        const n = parseInt(count, 10) || 0;
        $(S.badge).text(n).attr('data-count', n);
        $(S.countLabel).text(n === 1 ? '1 artículo' : n + ' artículos');
        // Mostrar/ocultar badge
        if (n > 0) {
            $(S.badge).show();
        } else {
            $(S.badge).hide();
        }
    }

    /* ─── Barra de Envío Gratis ──────────────────────────────────────────── */
    function updateShippingBar(subtotalRaw) {
        const threshold = parseFloat(cfg.free_shipping_threshold) || 0;
        if (threshold <= 0) {
            $(S.shippingBar).hide();
            return;
        }
        const amount   = parseFloat(subtotalRaw) || 0;
        const $bar     = $(S.root).find(S.shippingBar);
        const $complete = $bar.filter('.nh-side-cart__shipping-bar--complete');
        const $progress = $bar.not('.nh-side-cart__shipping-bar--complete');

        if (amount >= threshold) {
            $complete.show();
            $progress.hide();
        } else {
            const pct = Math.min(100, (amount / threshold) * 100).toFixed(1);
            const remaining = formatPrice(threshold - amount);
            $progress.show().find(S.shippingBarFill).css('width', pct + '%');
            $progress.find(S.shippingBarText).html(
                'Te faltan <strong>' + remaining + '</strong> para envío gratis'
            );
            $complete.hide();
        }
    }

    /* ─── Render Items ───────────────────────────────────────────────────── */
    function renderItems(items) {
        const $items   = $(S.items);
        const $empty   = $(S.emptyState);
        const $subtotal = $(S.subtotal);

        if (!items || items.length === 0) {
            $items.empty().hide();
            $empty.show();
            $subtotal.text(formatPrice(0));
            updateBadge(0);
            updateShippingBar(0);
            return;
        }

        $empty.hide();
        $items.show().empty();

        let totalQty = 0;
        let totalRaw = 0;

        items.forEach(function (item) {
            totalQty += parseInt(item.quantity, 10) || 0;
            totalRaw += parseFloat(item.line_total) || 0;

            // Build variation pills HTML
            var pillsHtml = '';
            if (item.variations && item.variations.length > 0) {
                pillsHtml = '<div class="nh-pill-group">';
                item.variations.forEach(function (v) {
                    pillsHtml += '<span class="nh-pill">' +
                        '<span class="nh-pill-label">' + v.label + ':</span> ' +
                        '<span class="nh-pill-value">' + v.value + '</span>' +
                    '</span>';
                });
                pillsHtml += '</div>';
            }

            const $li = $(
                '<li class="nh-side-cart__item" data-key="' + item.key + '"' +
                ' data-nh-product-id="' + (item.product_id || '') + '"' +
                ' data-nh-product-name="' + (item.name || '').replace(/"/g, '&quot;') + '"' +
                ' data-nh-product-price="' + (item.unit_price || 0) + '"' +
                ' data-nh-quantity="' + (item.quantity || 1) + '">' +
                '<a class="nh-side-cart__item-image-wrap" href="' + item.url + '">' +
                    '<img class="nh-side-cart__item-image" src="' + item.image + '" alt="' + item.name + '" loading="lazy">' +
                '</a>' +
                '<div class="nh-side-cart__item-info">' +
                    '<a class="nh-side-cart__item-name" href="' + item.url + '">' + item.name + '</a>' +
                    pillsHtml +
                    '<div class="nh-side-cart__item-meta">' +
                        '<span class="nh-side-cart__item-qty">' + item.quantity + ' &times;</span>' +
                        '<span class="nh-side-cart__item-price">' + item.price_formatted + '</span>' +
                    '</div>' +
                '</div>' +
                '<button class="nh-side-cart__item-remove" aria-label="Eliminar ' + item.name + '" data-key="' + item.key + '">&times;</button>' +
            '</li>'
            );

            $items.append($li);
        });

        $subtotal.text(formatPrice(totalRaw));
        updateBadge(totalQty);
        updateShippingBar(totalRaw);
    }

    /* ─── Cargar Items desde el servidor ─────────────────────────────────── */
    function refresh(andOpen) {
        if (isLoading) return;
        isLoading = true;

        $(S.body).addClass('nh-side-cart__body--loading');

        $.ajax({
            url:    cfg.ajax_url,
            method: 'POST',
            data: {
                action: 'nh_side_cart_get_items',
                nonce:  cfg.nonce,
            },
            success: function (res) {
                if (res.success) {
                    renderItems(res.data.items);
                    if (andOpen) open();
                }
            },
            error: function () {
                console.warn('[NHSideCart] Error al obtener items del carrito.');
            },
            complete: function () {
                isLoading = false;
                $(S.body).removeClass('nh-side-cart__body--loading');
            },
        });
    }

    /* ─── Eliminar Item ──────────────────────────────────────────────────── */
    function removeItem(cartItemKey) {
        const $item = $(S.items).find('[data-key="' + cartItemKey + '"]');
        $item.addClass('nh-side-cart__item--removing');

        // Capture product data for tracking BEFORE removal
        var trackData = {
            'nh-product-id':    $item.attr('data-nh-product-id') || '',
            'nh-product-name':  $item.attr('data-nh-product-name') || '',
            'nh-product-price': parseFloat($item.attr('data-nh-product-price')) || 0,
            'nh-quantity':      parseInt($item.attr('data-nh-quantity')) || 1,
        };

        $.ajax({
            url:    cfg.ajax_url,
            method: 'POST',
            data: {
                action:        'nh_remove_cart_item',
                nonce:         cfg.cart_nonce,   // ← nh_cart_nonce (handler existente)
                cart_item_key: cartItemKey,       // ← campo que espera el handler PHP
            },
            success: function (res) {
                if (res.success) {
                    // Animar salida y luego refrescar
                    $item.animate({ opacity: 0, height: 0 }, 220, function () {
                        $item.remove();
                        refresh(false);
                        // Notificar al ecosistema WooCommerce para actualizar otros widgets
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('wc_fragments_refreshed');
                        // Fire removed_from_cart for tracking (nh-datalayer-cart.js listens)
                        $(document.body).trigger('removed_from_cart', [
                            {},               // fragments (not needed for tracking)
                            '',               // cart_hash
                            $('<button>')
                                .attr('data-nh-product-id',    trackData['nh-product-id'])
                                .attr('data-nh-product-name',  trackData['nh-product-name'])
                                .attr('data-nh-product-price', trackData['nh-product-price'])
                                .attr('data-nh-quantity',      trackData['nh-quantity'])
                        ]);
                    });
                }
            },
            error: function () {
                $item.removeClass('nh-side-cart__item--removing');
            },
        });
    }

    /* ─── Sincronización con wc-cart-fragments ───────────────────────────── */
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
        refresh(false);
    });

    /* ─── Apertura automática tras "Añadir al carrito" ───────────────────── */
    $(document.body).on('added_to_cart', function (e, fragments, cart_hash, $button) {
        if (window.elementor) return;
        refresh(true);
    });

    /* ─── Event Listeners ────────────────────────────────────────────────── */
    $(document).ready(function () {

        // Carga inicial silenciosa
        refresh(false);

        // Abrir drawer
        $(document).on('click', S.trigger, function (e) {
            e.preventDefault();
            toggle();
        });

        // Cerrar con botón X
        $(document).on('click', S.close, function (e) {
            e.preventDefault();
            close();
        });

        // Cerrar al hacer click en overlay
        $(document).on('click', S.overlay, function () {
            close();
        });

        // Cerrar con Escape
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) close();
        });

        // Eliminar item
        $(document).on('click', S.removeBtn, function (e) {
            e.preventDefault();
            const key = $(this).data('key');
            if (key) removeItem(key);
        });

    });

    /* ─── API Global ─────────────────────────────────────────────────────── */
    window.NHSideCart = {
        open:    open,
        close:   close,
        toggle:  toggle,
        refresh: refresh,
        isOpen:  function () { return isOpen; },
    };

})(jQuery);
