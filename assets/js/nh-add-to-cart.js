/**
 * NH Add to Cart — Buy Now + AJAX ATC handler with Self-Healing UX
 *
 * 1. Buy Now: intercepts .nh-add-to-cart__buy-now clicks
 * 2. AJAX ATC: intercepts form.cart submit on single product pages
 *    (Elementor breaks WC's native wc-add-to-cart.js event handlers)
 * 3. Self-Healing UX: prevents silent failures when variations are unselected;
 *    triggers gentle shake animation, smooth viewport scroll, and luxury toast feedback.
 * 4. Error & Out-of-Stock resilience: full toast feedback on AJAX errors.
 *
 * Fires standard WooCommerce/jQuery events so external layers
 * (GTM, GA4, Pixel, side cart) can listen via added_to_cart.
 */
(function () {
    'use strict';

    /* ── Refresco de nonce (páginas cacheadas por WP Rocket) ──────────────── */
    // El HTML cacheado lleva un nonce inline que caduca (~12h). admin-ajax
    // nunca se cachea: pedimos un nonce fresco antes de cada submit para que
    // el add-to-cart no falle silenciosamente por nonce expirado.
    var _pendingNonce = null;

    function getFreshNonce() {
        if (_pendingNonce) return _pendingNonce;

        _pendingNonce = new Promise(function (resolve) {
            if (typeof window.fetch !== 'function') {
                resolve((window.nh_cart_params || {}).nonce || '');
                return;
            }

            var ajaxUrl = (window.nh_cart_params || {}).ajax_url || '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                credentials: 'same-origin',
                body: 'action=nh_get_cart_nonce',
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    _pendingNonce = null;
                    resolve((res && res.data && res.data.cart_nonce) || (window.nh_cart_params || {}).nonce || '');
                })
                .catch(function () {
                    _pendingNonce = null;
                    resolve((window.nh_cart_params || {}).nonce || '');
                });
        });

        return _pendingNonce;
    }

    /* ── Luxury Toast Notification System (Zero-Dependency) ─────────────── */
    function showNhToast(message, type) {
        type = type || 'warning';

        var container = document.getElementById('nh-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'nh-toast-container';
            container.className = 'nh-toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        // Evitar duplicados consecutivos con el mismo mensaje; reactivar atención con shake
        var existingToasts = container.querySelectorAll('.nh-toast');
        for (var i = 0; i < existingToasts.length; i++) {
            var msgEl = existingToasts[i].querySelector('.nh-toast__message');
            if (msgEl && msgEl.textContent === message) {
                triggerShake(existingToasts[i]);
                return existingToasts[i];
            }
        }

        var toast = document.createElement('div');
        toast.className = 'nh-toast nh-toast--' + type;
        toast.setAttribute('role', 'alert');

        var iconSvg = '';
        if (type === 'warning') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
        } else if (type === 'error') {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
        } else {
            iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        }

        toast.innerHTML =
            '<span class="nh-toast__icon" aria-hidden="true">' + iconSvg + '</span>' +
            '<span class="nh-toast__message">' + message + '</span>' +
            '<button type="button" class="nh-toast__close" aria-label="Cerrar">&times;</button>';

        var dismissTimeout = null;
        var isDismissed = false;

        function dismiss() {
            if (isDismissed) return;
            isDismissed = true;
            clearTimeout(dismissTimeout);
            toast.classList.add('nh-toast--hiding');
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 260);
        }

        var closeBtn = toast.querySelector('.nh-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dismiss();
            });
        }

        // Auto-dismiss tras 4 segundos
        dismissTimeout = setTimeout(dismiss, 4000);

        // Pausar auto-dismiss al hacer hover
        toast.addEventListener('mouseenter', function () {
            clearTimeout(dismissTimeout);
        });
        toast.addEventListener('mouseleave', function () {
            if (!isDismissed) {
                dismissTimeout = setTimeout(dismiss, 2500);
            }
        });

        container.appendChild(toast);
        return toast;
    }

    // Exponer globalmente para interoperabilidad
    window.showNhToast = showNhToast;

    /* ── Animación y helpers de viewport ─────────────────────────────────── */
    function triggerShake(el) {
        if (!el) return;
        el.classList.remove('nh-atc-shake');
        void el.offsetWidth; // Forzar reflow para reiniciar keyframes
        el.classList.add('nh-atc-shake');
        setTimeout(function () {
            el.classList.remove('nh-atc-shake');
        }, 450);
    }

    function isElementInViewport(el) {
        if (!el) return true;
        var rect = el.getBoundingClientRect();
        var windowHeight = window.innerHeight || document.documentElement.clientHeight;
        return (rect.top >= 70 && rect.bottom <= windowHeight - 70);
    }

    /* ── Validación y Self-Healing de Variaciones ─────────────────────────── */
    function validateVariableForm(form) {
        if (!form || !form.classList.contains('variations_form')) {
            return { valid: true, missing: [] };
        }

        var variationInput = form.querySelector('input[name="variation_id"]');
        var variationId = variationInput ? parseInt(variationInput.value, 10) || 0 : 0;
        var selects = form.querySelectorAll('.variations select');
        var missing = [];

        selects.forEach(function (select) {
            if (!select.value) {
                var row = select.closest('tr') || select.closest('.nh-variation-row') || select.parentElement;
                var labelEl = row ? row.querySelector('th.label label, .label label, label') : null;
                var labelText = labelEl ? labelEl.textContent.trim().replace(/:$/, '') : '';
                if (!labelText) {
                    var rawName = select.name.replace(/^attribute_pa_/, '').replace(/^attribute_/, '');
                    labelText = rawName.charAt(0).toUpperCase() + rawName.slice(1);
                }
                var wrapper = select.closest('.woo-variation-items-wrapper') ||
                              (row ? row.querySelector('.woo-variation-items-wrapper') : null) ||
                              select.parentElement;

                missing.push({
                    select: select,
                    row: row,
                    wrapper: wrapper,
                    label: labelText
                });
            }
        });

        var atcBtn = form.querySelector('.single_add_to_cart_button');
        var isSelectionNeeded = atcBtn && (
            atcBtn.classList.contains('wc-variation-selection-needed') ||
            atcBtn.classList.contains('disabled')
        );

        if (missing.length > 0 || variationId === 0 || isSelectionNeeded) {
            return { valid: false, missing: missing, variationId: variationId };
        }

        return { valid: true, missing: [], variationId: variationId };
    }

    function initHighlightCleanup(form) {
        if (form._nhHighlightCleanupInit) return;
        form._nhHighlightCleanupInit = true;

        form.addEventListener('change', function (e) {
            if (e.target && e.target.matches('.variations select')) {
                var row = e.target.closest('tr') || e.target.parentElement;
                var wrapper = e.target.closest('.woo-variation-items-wrapper') ||
                              (row ? row.querySelector('.woo-variation-items-wrapper') : null);
                if (wrapper) wrapper.classList.remove('nh-variation-highlight');
                if (row) row.classList.remove('nh-variation-highlight');
            }
        });

        form.addEventListener('click', function (e) {
            var swatch = e.target.closest('.variable-item, .woo-variation-raw-variable-item');
            if (swatch) {
                var wrapper = swatch.closest('.woo-variation-items-wrapper') || swatch.closest('td') || swatch.closest('tr');
                if (wrapper) wrapper.classList.remove('nh-variation-highlight');
            }
        });
    }

    function handleMissingAttributes(form, missing) {
        missing = missing || [];
        var toastMsg = '';

        if (missing.length === 1) {
            var label = missing[0].label.trim();
            var lower = label.toLowerCase();
            if (lower.indexOf('talla') !== -1) {
                toastMsg = 'Por favor selecciona tu talla antes de continuar.';
            } else if (lower.indexOf('color') !== -1) {
                toastMsg = 'Por favor selecciona tu color antes de continuar.';
            } else {
                toastMsg = 'Por favor selecciona ' + lower + ' antes de continuar.';
            }
        } else if (missing.length > 1) {
            var labels = missing.map(function (m) { return m.label.toLowerCase(); });
            toastMsg = 'Por favor selecciona tu ' + labels.join(' y ') + ' antes de continuar.';
        } else {
            toastMsg = 'Por favor selecciona tu talla antes de continuar.';
        }

        var varContainer = form.querySelector('.nh-add-to-cart__variations') || form.querySelector('.variations');

        var scrollTarget = null;
        if (missing.length > 0) {
            missing.forEach(function (item) {
                var target = item.wrapper || item.row;
                if (target) {
                    target.classList.add('nh-variation-highlight');
                    triggerShake(target);
                    if (!scrollTarget) scrollTarget = target;
                }
            });
        }

        if (varContainer) {
            triggerShake(varContainer);
            if (!scrollTarget) scrollTarget = varContainer;
        }

        // Scroll suave si la sección de variaciones o swatch está fuera de viewport
        if (scrollTarget && !isElementInViewport(scrollTarget)) {
            scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Toast de lujo editorial
        showNhToast(toastMsg, 'warning');

        // Cleanup listener para quitar el highlight al interactuar
        initHighlightCleanup(form);
    }

    /* ── Click Interceptor en Fase de Captura ─────────────────────────────── */
    // Garantiza que los clics en botones de ATC / Buy Now cuando faltan opciones
    // nunca fallen silenciosamente, ni muestren el window.alert nativo de WC.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.single_add_to_cart_button, .nh-add-to-cart__buy-now, .nh-add-to-cart__button, .nh-add-to-cart__buy-now-wrapper');
        if (!btn) return;

        var form = btn.closest('form.cart');
        if (!form || !form.classList.contains('variations_form')) return;

        // Si ya está en proceso de carga, no interferir
        if (btn.classList.contains('loading') || btn.classList.contains('nh-add-to-cart__buy-now--loading')) return;

        var validation = validateVariableForm(form);
        if (!validation.valid) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            handleMissingAttributes(form, validation.missing);
            return false;
        }
    }, true);

    /* ── Mantener interactividad de botones (evitar disabled nativo de navegador) ── */
    function initButtonInteractivity() {
        document.querySelectorAll('form.variations_form').forEach(function (form) {
            var atcBtn = form.querySelector('.single_add_to_cart_button');
            if (!atcBtn) return;

            var _syncing = false;
            var ensureClickable = function () {
                if (_syncing) return;
                _syncing = true;
                try {
                    var isLoading = atcBtn.classList.contains('loading');
                    if (atcBtn.hasAttribute('disabled') && !isLoading) {
                        atcBtn.removeAttribute('disabled');
                        atcBtn.setAttribute('aria-disabled', 'true');
                    }
                } finally {
                    _syncing = false;
                }
            };

            ensureClickable();

            var observer = new MutationObserver(ensureClickable);
            observer.observe(atcBtn, {
                attributes: true,
                attributeFilter: ['class', 'disabled'],
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initButtonInteractivity);

    /* ── AJAX Add to Cart (reemplaza wc-add-to-cart.js roto por Elementor) ── */
    function initAjaxATC() {
        if (typeof jQuery === 'undefined') return;

        var $ = jQuery;

        // Solo en single product pages (con form.cart)
        $('body.product-template-default form.cart, form.cart[data-product_id]').on('submit', function (e) {
            // No interceptar si el form ya está siendo manejado por AJAX de NH ATC widget
            if (this.dataset.nhAtcAjax === 'true') return;

            var $form = $(this);
            var formEl = this;
            var $btn  = $form.find('.single_add_to_cart_button');

            if (!$btn.length) return;

            // Validación defensiva en submit para productos variables
            if (formEl.classList.contains('variations_form')) {
                var validation = validateVariableForm(formEl);
                if (!validation.valid) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    handleMissingAttributes(formEl, validation.missing);
                    return false;
                }
            }

            e.preventDefault();

            var productId   = parseInt($form.find('input[name="product_id"], button[name="add-to-cart"]').val()) || 0;
            var variationId = parseInt($form.find('input[name="variation_id"]').val()) || 0;
            var quantity    = parseInt($form.find('input.qty, .nh-qty__input').val()) || 1;

            // Para productos variables, recoger variaciones
            var variations = {};
            $form.find('.variations select').each(function () {
                if (this.value) variations[this.name] = this.value;
            });

            if (!productId) return;

            $btn.addClass('loading').prop('disabled', true);

            getFreshNonce().then(function (nonce) {
                return $.ajax({
                    type: 'POST',
                    url:  (window.nh_cart_params || {}).ajax_url || '/wp-admin/admin-ajax.php',
                    data: {
                        action:       'nh_add_to_cart',
                        nonce:        nonce,
                        product_id:   productId,
                        variation_id: variationId,
                        quantity:     quantity,
                        variations:   variations,
                    },
                });
            }).then(function (res) {
                $btn.removeClass('loading').prop('disabled', false);

                if (res && res.success) {
                    $(document.body).trigger('added_to_cart', [
                        res.data.fragments || {},
                        res.data.cart_hash || '',
                        $btn,
                    ]);
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    var errorMsg = (res && res.data && res.data.message)
                        ? res.data.message
                        : 'Lo sentimos, esta combinación se encuentra agotada temporalmente.';
                    showNhToast(errorMsg, 'error');
                    $(document.body).trigger('wc_fragment_refresh');
                }
            }).catch(function (err) {
                $btn.removeClass('loading').prop('disabled', false);
                console.warn('[NH ATC Failure]', {
                    error: err,
                    productId: productId,
                    variationId: variationId,
                    context: 'initAjaxATC'
                });
                showNhToast('Hubo un inconveniente al conectar con el servidor. Por favor intenta de nuevo.', 'error');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initAjaxATC);

    /* ── Sync variación → data-nh-* (tracking preciso) ─────────────────────── */
    function initVariationSync() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        // Cuando WooCommerce resuelve una variación, actualizar precio + id en botones
        $(document).on('found_variation', 'form.variations_form', function (e, variation) {
            if (!variation) return;

            var $form   = $(this);
            var price   = variation.display_price;
            var varId   = variation.variation_id;

            // Limpiar highlights de advertencia
            $form.find('.nh-variation-highlight').removeClass('nh-variation-highlight');

            // Actualizar ATC button
            $form.find('.single_add_to_cart_button')
                .attr('data-nh-product-price', price)
                .attr('data-nh-product-id', varId);

            // Actualizar Buy Now button
            $form.closest('.nh-add-to-cart__layout')
                .find('.nh-add-to-cart__buy-now')
                .attr('data-nh-product-price', price)
                .attr('data-nh-product-id', varId);
        });

        // Cuando se resetea la variación, volver al precio del padre
        $(document).on('reset_data', 'form.variations_form', function () {
            var $form = $(this);
            var productId = $form.data('product_id');

            $form.find('.single_add_to_cart_button')
                .attr('data-nh-product-id', productId)
                .removeAttr('data-nh-product-price');

            $form.closest('.nh-add-to-cart__layout')
                .find('.nh-add-to-cart__buy-now')
                .attr('data-nh-product-id', productId)
                .removeAttr('data-nh-product-price');
        });
    }

    document.addEventListener('DOMContentLoaded', initVariationSync);

    /* ── Buy Now Button Handler ──────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var buttons = document.querySelectorAll('.nh-add-to-cart__buy-now');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            var form = btn.closest('form.cart') || document.querySelector('form.variations_form.cart');
            if (!form) return;

            // ── Sync disabled/aria-disabled state with ATC button ────────
            var atcBtn = form.querySelector('.single_add_to_cart_button');
            if (atcBtn) {
                var _isSyncing = false;
                var syncDisabled = function () {
                    if (_isSyncing) return;
                    _isSyncing = true;
                    try {
                        var isNeeded = atcBtn.classList.contains('wc-variation-selection-needed') || atcBtn.classList.contains('disabled');
                        var isLoading = atcBtn.classList.contains('loading');

                        if (atcBtn.hasAttribute('disabled') && !isLoading) {
                            atcBtn.removeAttribute('disabled');
                            atcBtn.setAttribute('aria-disabled', 'true');
                        }

                        if (isLoading) {
                            btn.disabled = true;
                        } else {
                            btn.disabled = false;
                            btn.setAttribute('aria-disabled', isNeeded ? 'true' : 'false');
                            btn.classList.toggle('disabled', isNeeded);
                        }
                    } finally {
                        _isSyncing = false;
                    }
                };

                // Initial sync
                syncDisabled();

                // Watch for WC class/attribute changes on the ATC button
                var observer = new MutationObserver(syncDisabled);
                observer.observe(atcBtn, {
                    attributes: true,
                    attributeFilter: ['class', 'disabled'],
                });
            }

            // ── Click handler ────────────────────────────────────────────
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                if (btn.classList.contains('nh-add-to-cart__buy-now--loading')) return;

                var isVariable = btn.dataset.isVariable === 'true' || form.classList.contains('variations_form');

                if (isVariable) {
                    var validation = validateVariableForm(form);
                    if (!validation.valid) {
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        handleMissingAttributes(form, validation.missing);
                        return;
                    }
                }

                var qtyInput = form.querySelector('.nh-qty__input, input.qty');
                var quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

                var productId   = parseInt(btn.dataset.nhProductId) || parseInt((form.querySelector('input[name="product_id"]') || {}).value) || 0;
                var variationId = 0;
                var variations  = {};

                if (isVariable) {
                    var variationInput = form.querySelector('input[name="variation_id"]');
                    variationId = variationInput ? parseInt(variationInput.value) || 0 : 0;

                    form.querySelectorAll('.variations select').forEach(function (select) {
                        if (select.value) variations[select.name] = select.value;
                    });

                    if (!variationId) {
                        showNhToast('Lo sentimos, esta combinación no se encuentra disponible.', 'warning');
                        return;
                    }
                }

                btn.classList.add('nh-add-to-cart__buy-now--loading');
                btn.disabled = true;

                getFreshNonce().then(function (nonce) {
                    var formData = new FormData();
                    formData.append('action', 'nh_buy_now');
                    formData.append('nonce', nonce);
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);
                    if (variationId) {
                        formData.append('variation_id', variationId);
                        Object.keys(variations).forEach(function (key) {
                            formData.append('variations[' + key + ']', variations[key]);
                        });
                    }

                    var ajaxUrl = (window.nh_cart_params && window.nh_cart_params.ajax_url) || '/wp-admin/admin-ajax.php';
                    return fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                    }).then(function (r) { return r.json(); });
                })
                    .then(function (res) {
                        if (!res.success) {
                            var errMsg = (res.data && res.data.message)
                                ? res.data.message
                                : 'Lo sentimos, esta combinación se encuentra agotada temporalmente.';
                            showNhToast(errMsg, 'error');
                            btn.classList.remove('nh-add-to-cart__buy-now--loading');
                            btn.disabled = false;
                            return;
                        }

                        if (typeof jQuery !== 'undefined') {
                            var $btn = jQuery(btn);
                            jQuery(document.body).trigger('added_to_cart', [
                                res.data.fragments || {},
                                res.data.cart_hash || '',
                                $btn,
                            ]);
                            jQuery(document.body).trigger('nh_buy_now_initiated', [$btn]);
                        }

                        // Delay antes de redirect para que tracking pixels completen su push
                        setTimeout(function () {
                            window.location.href = res.data.checkout_url;
                        }, 150);
                    })
                    .catch(function (err) {
                        console.warn('[NH ATC Failure]', {
                            error: err,
                            productId: productId,
                            variationId: variationId,
                            context: 'BuyNow'
                        });
                        showNhToast('Hubo un inconveniente al conectar con el servidor. Por favor intenta de nuevo.', 'error');
                        btn.classList.remove('nh-add-to-cart__buy-now--loading');
                        btn.disabled = false;
                    });
            });
        });
    });

    /* ── RE-SYNC DE SWATCHES ─────────────────────────────────────────────── */

    /**
     * Re-inicializa WVS swatches después de re-renders de Elementor
     * o contextos AJAX externos (Quick View, etc.).
     *
     * Guard clause: WC marca los forms inicializados con jQuery.data() (no un
     * atributo HTML data-). Verificamos con jQuery.data() que depende de jQuery
     * estar cargado, lo cual garantizamos con la dependencia del enqueue.
     * Esto evita reset de selección y peticiones AJAX duplicadas
     * en modo AJAX (>30 variaciones).
     */
    function reinitSwatches() {
        document.querySelectorAll('.variations_form').forEach(function (form) {
            var alreadyInit = window.jQuery && jQuery.data(form, 'wc_variation_form');
            if (alreadyInit) return;

            if (window.jQuery && jQuery.fn.wc_variation_form) {
                try {
                    jQuery(form).wc_variation_form();
                } catch (err) {
                    console.error('[NH ATC] wc_variation_form() ERROR:', err);
                }
            }
        });

        if (window.jQuery) {
            jQuery(document).trigger('woo_variation_swatches_init');
        }
    }

    // Re-init en el editor de Elementor (cada vez que se guarda/preview)
    document.addEventListener('DOMContentLoaded', function () {
        if (window.elementorFrontend && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/nh-add-to-cart.default',
                reinitSwatches
            );
        }

        // Siempre intentar re-init en DOMContentLoaded:
        // WC's wc-add-to-cart-variation.js puede ejecutarse antes de que
        // Elementor renderice el widget, dejando forms sin inicializar.
        setTimeout(function () {
            reinitSwatches();
        }, 500);
    });

    // Re-init en contextos AJAX externos (Quick View, etc.)
    if (window.jQuery) {
        jQuery(document.body).on('wc_quick_view_open nh_atc_ajax_loaded', reinitSwatches);
    }

})();
