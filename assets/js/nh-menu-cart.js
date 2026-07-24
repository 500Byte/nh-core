/**
 * NH Side Cart — nh-menu-cart.js
 * Widget: NH_Menu_Cart_Widget (class-nh-menu-cart-widget.php)
 *
 * Responsabilidades:
 *   1. Apertura / cierre del drawer vía clic en el toggle y overlay
 *   2. Inyectar cabecera BEM con título "Tu carrito" + contador dinámico
 *   3. Bloquear scroll del body cuando el drawer está abierto
 *   4. Sincronización con wc-cart-fragments (actualización reactiva)
 *   5. Cierre con tecla Escape
 *   6. Animación de entrada desde la derecha
 */
(function ($) {
    'use strict';

    // ─── Selectores ──────────────────────────────────────────────────────────
    const SEL = {
        wrapper:    '.elementor-menu-cart__wrapper',
        container:  '.elementor-menu-cart__container',
        main:       '.elementor-menu-cart__main',
        toggle:     '#elementor-menu-cart__toggle_button',
        closeBtn:   '.elementor-menu-cart__close-button, .elementor-menu-cart__close-button-custom',
        products:   '.elementor-menu-cart__products',
        subtotal:   '.elementor-menu-cart__subtotal',
        footer:     '.elementor-menu-cart__footer-buttons',
        qtyBadge:   '.elementor-button-icon-qty',
        shownClass: 'elementor-menu-cart--shown',
    };

    // ─── Estado ───────────────────────────────────────────────────────────────
    let isOpen = false;

    // ─── Inyectar Cabecera BEM ────────────────────────────────────────────────
    function injectHeader() {
        const $main = $(SEL.main);
        if (!$main.length || $main.find('.nh-side-cart-header').length) return;

        const count = parseInt($(SEL.qtyBadge).attr('data-counter') || '0', 10);
        const countLabel = count === 1 ? '1 artículo' : count + ' artículos';

        const $header = $('<div class="nh-side-cart-header">' +
            '<h2 class="nh-side-cart-header__title">Tu carrito ' +
                '<span class="nh-side-cart-header__count">(' + countLabel + ')</span>' +
            '</h2>' +
        '</div>');

        // Mover el botón de cerrar nativo de Elementor dentro de nuestro header
        const $closeBtn = $main.find(SEL.closeBtn).first();
        if ($closeBtn.length) {
            $header.append($closeBtn);
        }

        $main.prepend($header);
    }

    // ─── Actualizar contador en cabecera ──────────────────────────────────────
    function updateHeaderCount() {
        const count = parseInt($(SEL.qtyBadge).attr('data-counter') || '0', 10);
        const countLabel = count === 1 ? '1 artículo' : count + ' artículos';
        $('.nh-side-cart-header__count').text('(' + countLabel + ')');
    }

    // ─── Abrir Drawer ─────────────────────────────────────────────────────────
    function openCart() {
        if (isOpen) return;
        isOpen = true;

        const $main      = $(SEL.main);
        const $container = $(SEL.container);
        const $widget    = $main.closest('.elementor-widget-woocommerce-menu-cart, ' + SEL.wrapper);

        // Actualizar aria
        $main.attr('aria-hidden', 'false');
        $container.attr('aria-hidden', 'false');
        $(SEL.toggle).attr('aria-expanded', 'true');

        // Activar clase para animación CSS
        $widget.addClass(SEL.shownClass);
        $('body').addClass('nh-side-cart-open');

        // Focus trap mínimo: foco al botón de cerrar
        setTimeout(function () {
            $(SEL.closeBtn).first().focus();
        }, 320);
    }

    // ─── Cerrar Drawer ────────────────────────────────────────────────────────
    function closeCart() {
        if (!isOpen) return;
        isOpen = false;

        const $main      = $(SEL.main);
        const $container = $(SEL.container);
        const $widget    = $main.closest('.elementor-widget-woocommerce-menu-cart, ' + SEL.wrapper);

        $main.attr('aria-hidden', 'true');
        $container.attr('aria-hidden', 'true');
        $(SEL.toggle).attr('aria-expanded', 'false');

        $widget.removeClass(SEL.shownClass);
        $('body').removeClass('nh-side-cart-open');

        // Restaurar foco al toggle
        $(SEL.toggle).focus();
    }

    // ─── Toggle ───────────────────────────────────────────────────────────────
    function toggleCart(e) {
        e.preventDefault();
        e.stopPropagation();
        isOpen ? closeCart() : openCart();
    }

    // ─── Sincronización con wc-cart-fragments ─────────────────────────────────
    function onFragmentsRefreshed() {
        updateHeaderCount();

        // Si Elementor abrió el drawer automáticamente tras "add to cart"
        const $container = $(SEL.container);
        if ($container.attr('aria-hidden') === 'false' && !isOpen) {
            isOpen = true;
            $('body').addClass('nh-side-cart-open');
        }
    }

    // ─── Inicialización ───────────────────────────────────────────────────────
    function init() {
        if (!$(SEL.main).length) return;

        // Normalizar estado inicial: asegurarse de que el panel empieza cerrado
        $(SEL.main).attr('aria-hidden', 'true');
        $(SEL.container).attr('aria-hidden', 'true');

        // Inyectar cabecera
        injectHeader();

        // Verificar si Elementor ya lo tiene como "shown" (auto-open on add to cart)
        if ($(SEL.wrapper).closest('.elementor-menu-cart--shown').length ||
            $(SEL.container).attr('aria-hidden') === 'false') {
            openCart();
        }
    }

    // ─── Event Listeners ─────────────────────────────────────────────────────
    $(document).ready(function () {
        init();

        // Toggle button
        $(document).on('click', SEL.toggle, toggleCart);

        // Close button dentro del panel
        $(document).on('click', SEL.closeBtn, function (e) {
            e.preventDefault();
            closeCart();
        });

        // Clic en overlay (fondo oscuro fuera del panel)
        $(document).on('click', SEL.container, function (e) {
            if ($(e.target).is(SEL.container)) {
                closeCart();
            }
        });

        // Escape
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                closeCart();
            }
        });

        // WooCommerce fragments updated
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', onFragmentsRefreshed);

        // Elementor abre el drawer automáticamente (after add to cart)
        $(document.body).on('added_to_cart', function () {
            // Pequeño delay para que el fragment se refresque
            setTimeout(function () {
                updateHeaderCount();
                if (!isOpen) openCart();
            }, 400);
        });
    });

    // ─── Expose API global (para integración con otros scripts) ──────────────
    window.NHSideCart = {
        open:  openCart,
        close: closeCart,
        toggle: function () { isOpen ? closeCart() : openCart(); },
        isOpen: function () { return isOpen; },
    };

})(jQuery);
