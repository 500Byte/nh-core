# Quantity System Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Unify two parallel quantity systems into a single `nh-qty` module with consistent BEM classes, shared CSS, min/max/step support, AJAX context detection, and no full page reloads.

**Architecture:** A new `nh-qty.js` replaces both `nh-quantity-buttons.js` (PDP + cart table, non-AJAX) and the qty logic inside `nh-cart.js` (Elementor cart widget, AJAX). The module auto-detects context: if the input is inside `.nh-cart-widget` or `.nh-cart-table-widget`, it fires AJAX; otherwise it updates locally. CSS is consolidated into a single `nh-qty.css` replacing both `nh-quantity-buttons.css` and the duplicated rules in `nh-woocommerce.css:2322-2356`.

**Tech Stack:** Vanilla JS (no jQuery dependency for new module), CSS custom properties (NH DS tokens), WooCommerce AJAX (`wc_fragment_refresh`).

---

## File Structure

| Action | File | Responsibility |
|--------|------|----------------|
| Create | `assets/js/nh-qty.js` | Unified qty logic — ± clicks, input change, disabled states, AJAX vs local context |
| Create | `assets/css/nh-qty.css` | All qty input + button styles (PDP, cart table, Elementor cart widget) |
| Modify | `inc/class-nh-core-woocommerce.php:148-161` | Replace `nh-quantity-buttons` enqueue with `nh-qty` |
| Modify | `inc/class-nh-core-elementor.php:180-193` | Add `nh-qty` script dependency to `nh-cart-widget` |
| Modify | `templates/quantity-input.php:32,59` | Update classes: `nh-qty-btn` → `nh-qty__btn` |
| Modify | `widgets/class-nh-cart-table-widget.php:172-176` | Update classes to `nh-qty__btn`, `nh-qty__input` |
| Modify | `widgets/class-nh-cart-widget.php:571-575` | Update content_template classes to `nh-qty__btn`, `nh-qty__input` |
| Modify | `assets/js/nh-cart.js:22-61` | Remove `updateQuantity` method, keep rest (remove/coupon/clear/toast) |
| Modify | `assets/css/nh-woocommerce.css:2322-2356` | Remove duplicated qty styles (now in `nh-qty.css`) |
| Delete | `assets/js/nh-quantity-buttons.js` | Replaced by `nh-qty.js` |
| Delete | `assets/css/nh-quantity-buttons.css` | Replaced by `nh-qty.css` |

---

### Task 1: Create `nh-qty.css` — Consolidated Qty Styles

**Files:**
- Create: `assets/css/nh-qty.css`
- Modify: `assets/css/nh-woocommerce.css:2322-2356` (remove later in Task 8)

- [ ] **Step 1: Create `assets/css/nh-qty.css`**

Merge all qty styles from `nh-quantity-buttons.css` and `nh-woocommerce.css:2322-2356` into a single BEM-structured file. Use `.nh-qty` as the block. Key selectors:

```css
/* ── NH Qty — Unified Quantity Selector ────────────────────────────────────── */

/* Block: .nh-qty (wrapper, replaces div.quantity and .nh-cart-product-qty) */
.nh-qty {
    display: inline-flex;
    align-items: center;
    border: 2px solid var(--nh-gray-200);
    border-radius: var(--radius-sm);
    overflow: visible;
    background: transparent;
    width: fit-content;
    min-width: 130px;
    height: 42px;
    padding: 0;
    transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.nh-qty:hover {
    border-color: var(--nh-gray-300);
}

.nh-qty:focus-within {
    border-color: var(--nh-primary);
    box-shadow: 0 0 0 3px rgba(103, 103, 101, 0.12);
}

/* Element: .nh-qty__input */
.nh-qty__input {
    flex: 1 1 auto;
    width: 46px;
    height: 100%;
    padding: 0;
    text-align: center;
    border: none;
    background: transparent;
    color: var(--nh-text);
    font-family: var(--font-body);
    font-size: 15px;
    font-weight: 500;
    line-height: 42px;
    outline: none;
    box-shadow: none;
    -moz-appearance: textfield;
}

.nh-qty__input::-webkit-outer-spin-button,
.nh-qty__input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.nh-qty__input:focus {
    background: transparent;
}

/* Element: .nh-qty__btn */
.nh-qty__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 42px;
    height: 42px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 18px;
    font-weight: 500;
    color: var(--nh-text);
    transition: background var(--transition-fast), color var(--transition-fast),
                opacity var(--transition-fast), transform var(--transition-fast);
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    box-shadow: none;
    border-radius: 0;
    -webkit-user-select: none;
    user-select: none;
}

/* Modifier: minus has right border, plus has left border */
.nh-qty__btn--minus {
    border-right: 2px solid var(--nh-gray-200);
}

.nh-qty__btn--plus {
    border-left: 2px solid var(--nh-gray-200);
}

.nh-qty__btn:hover {
    background: var(--nh-text);
    color: var(--nh-white);
}

.nh-qty__btn:active {
    background: var(--nh-text);
    color: var(--nh-white);
    transform: scale(0.95);
}

.nh-qty__btn:focus-visible {
    outline: 2px solid var(--nh-primary);
    outline-offset: -2px;
}

/* Modifier: disabled state */
.nh-qty__btn--disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

/* ── Cart Widget variant (smaller, compact) ────────────────────────────────── */
.nh-cart-widget .nh-qty,
.nh-cart-table-widget .nh-qty {
    min-width: 100px;
    height: 34px;
    border-width: 1px;
}

.nh-cart-widget .nh-qty__input,
.nh-cart-table-widget .nh-qty__input {
    width: 34px;
    font-size: 13px;
    font-weight: 600;
    line-height: 34px;
}

.nh-cart-widget .nh-qty__btn,
.nh-cart-table-widget .nh-qty__btn {
    width: 32px;
    height: 34px;
    font-size: 14px;
}

.nh-cart-widget .nh-qty__btn--minus,
.nh-cart-table-widget .nh-qty__btn--minus {
    border-right-width: 1px;
}

.nh-cart-widget .nh-qty__btn--plus,
.nh-cart-table-widget .nh-qty__btn--plus {
    border-left-width: 1px;
}

/* ── Loading state ─────────────────────────────────────────────────────────── */
.nh-qty--loading {
    opacity: 0.5;
    pointer-events: none;
}

/* ── Reduced motion ────────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .nh-qty,
    .nh-qty__btn {
        transition: none;
    }
}
```

- [ ] **Step 2: Verify CSS compiles without syntax errors**

Run: `cat assets/css/nh-qty.css | head -5`
Expected: File starts with the comment block.

- [ ] **Step 3: Commit**

```bash
git add assets/css/nh-qty.css
git commit -m "feat(qty): create unified nh-qty.css consolidating qty styles"
```

---

### Task 2: Create `nh-qty.js` — Unified Qty Module

**Files:**
- Create: `assets/js/nh-qty.js`

- [ ] **Step 1: Create `assets/js/nh-qty.js`**

```javascript
/**
 * NH Qty — Unified quantity selector module
 *
 * Auto-detects context:
 *   - Inside .nh-cart-widget or .nh-cart-table-widget → AJAX update via wc_fragment_refresh
 *   - Otherwise (PDP, cart page native) → local update, triggers WooCommerce update event
 *
 * BEM classes: .nh-qty, .nh-qty__btn, .nh-qty__btn--minus, .nh-qty__btn--plus,
 *              .nh-qty__btn--disabled, .nh-qty__input, .nh-qty--loading
 */
(function () {
    'use strict';

    const SELECTORS = {
        wrapper: '.nh-qty',
        input: '.nh-qty__input',
        btnMinus: '.nh-qty__btn--minus',
        btnPlus: '.nh-qty__btn--plus',
    };

    const AJAX_CONTEXT = '.nh-cart-widget, .nh-cart-table-widget';

    /**
     * Read min/max/step from an input element.
     * Falls back to sensible defaults when attributes are missing.
     */
    function getLimits(input) {
        const min = parseFloat(input.getAttribute('min')) || 1;
        const max = parseFloat(input.getAttribute('max'));
        const step = parseFloat(input.getAttribute('step')) || 1;
        return { min, max: isNaN(max) ? Infinity : max, step };
    }

    /**
     * Clamp qty to [min, max] respecting step.
     */
    function clampQty(qty, limits) {
        const { min, max, step } = limits;
        let val = qty;
        if (val < min) val = min;
        if (val > max) val = max;
        // Snap to step
        val = Math.round((val - min) / step) * step + min;
        // Floating point guard
        val = parseFloat(val.toPrecision(12));
        return val;
    }

    /**
     * Update disabled state of ± buttons based on current value.
     */
    function updateDisabledState(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        const btnMinus = wrapper.querySelector(SELECTORS.btnMinus);
        const btnPlus = wrapper.querySelector(SELECTORS.btnPlus);
        if (!input) return;

        const val = parseFloat(input.value) || 0;
        const { min, max } = getLimits(input);

        if (val <= min) {
            btnMinus?.classList.add('nh-qty__btn--disabled');
            btnMinus?.setAttribute('aria-disabled', 'true');
        } else {
            btnMinus?.classList.remove('nh-qty__btn--disabled');
            btnMinus?.removeAttribute('aria-disabled');
        }

        if (val >= max) {
            btnPlus?.classList.add('nh-qty__btn--disabled');
            btnPlus?.setAttribute('aria-disabled', 'true');
        } else {
            btnPlus?.classList.remove('nh-qty__btn--disabled');
            btnPlus?.removeAttribute('aria-disabled');
        }
    }

    /**
     * Set loading state on the wrapper.
     */
    function setLoading(wrapper, loading) {
        wrapper.classList.toggle('nh-qty--loading', loading);
        const input = wrapper.querySelector(SELECTORS.input);
        if (input) input.disabled = loading;
    }

    /**
     * Determine if this wrapper should use AJAX.
     */
    function isAjaxContext(wrapper) {
        return wrapper.closest(AJAX_CONTEXT) !== null;
    }

    /**
     * Get the cart item key from data attribute.
     */
    function getCartItemKey(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        return input?.getAttribute('data-key') || wrapper.getAttribute('data-key') || '';
    }

    /**
     * Send AJAX qty update.
     */
    function ajaxUpdate(wrapper, newQty) {
        const key = getCartItemKey(wrapper);
        if (!key) return;

        setLoading(wrapper, true);

        const params = window.nhSideCartParams || window.nh_cart_params || {};
        const ajaxUrl = params.ajax_url || '/wp-admin/admin-ajax.php';
        const nonce = params.nonce || params.cart_nonce || '';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'nh_update_cart_item',
                cart_item_key: key,
                quantity: newQty,
                nonce: nonce,
            }),
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    // Refresh WC fragments to update cart totals, mini cart, etc.
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    console.warn('[NH Qty]', res.data?.message || 'Update failed');
                }
            })
            .catch((err) => console.error('[NH Qty] AJAX error:', err))
            .finally(() => setLoading(wrapper, false));
    }

    /**
     * Update qty value (local or AJAX depending on context).
     */
    function updateQty(wrapper, delta) {
        const input = wrapper.querySelector(SELECTORS.input);
        if (!input) return;

        const { min, max, step } = getLimits(input);
        const current = parseFloat(input.value) || min;
        let newVal = current + delta * step;
        newVal = clampQty(newVal, { min, max, step });

        if (newVal === current) return;

        input.value = newVal;
        updateDisabledState(wrapper);

        if (isAjaxContext(wrapper)) {
            ajaxUpdate(wrapper, newVal);
        } else {
            // Local context: trigger change so WooCommerce handlers pick it up
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /**
     * Handle direct input changes (typed value).
     */
    function handleInputChange(wrapper) {
        const input = wrapper.querySelector(SELECTORS.input);
        if (!input) return;

        let val = parseFloat(input.value);
        if (isNaN(val)) val = 1;
        const { min, max, step } = getLimits(input);
        val = clampQty(val, { min, max, step });

        input.value = val;
        updateDisabledState(wrapper);

        if (isAjaxContext(wrapper)) {
            ajaxUpdate(wrapper, val);
        } else {
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // ── Event delegation ──────────────────────────────────────────────────
    function init() {
        // Click ± buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest(SELECTORS.btnMinus) || e.target.closest(SELECTORS.btnPlus);
            if (!btn) return;
            e.preventDefault();

            const wrapper = btn.closest(SELECTORS.wrapper);
            if (!wrapper || btn.classList.contains('nh-qty__btn--disabled')) return;

            const delta = btn.matches(SELECTORS.btnPlus) ? 1 : -1;
            updateQty(wrapper, delta);
        });

        // Direct input change (blur or Enter)
        document.addEventListener('change', (e) => {
            if (!e.target.matches(SELECTORS.input)) return;
            const wrapper = e.target.closest(SELECTORS.wrapper);
            if (wrapper) handleInputChange(wrapper);
        });

        // Initialize all existing wrappers
        refreshAll();

        // Re-initialize after WC fragment refresh (cart page, mini cart)
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on(
                'updated_wc_div updated_cart_totals wc_fragments_refreshed wc_fragments_loaded',
                refreshAll
            );
        }
    }

    /**
     * Refresh disabled states on allqty wrappers.
     */
    function refreshAll() {
        document.querySelectorAll(SELECTORS.wrapper).forEach(updateDisabledState);
    }

    // Expose for external use
    window.NHQty = { refreshAll };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
```

- [ ] **Step 2: Verify JS syntax**

Run: `node --check assets/js/nh-qty.js`
Expected: No output (no syntax errors).

- [ ] **Step 3: Commit**

```bash
git add assets/js/nh-qty.js
git commit -m "feat(qty): create unified nh-qty.js replacing two parallel qty scripts"
```

---

### Task 3: Update PHP Template — `quantity-input.php`

**Files:**
- Modify: `templates/quantity-input.php:32,59`

- [ ] **Step 1: Update button classes from `nh-qty-btn nh-qty-minus`/`nh-qty-plus` to BEM**

In `templates/quantity-input.php`, replace:
```php
<button type="button" class="nh-qty-btn nh-qty-minus" aria-label="Disminuir cantidad">—</button>
```
with:
```php
<button type="button" class="nh-qty__btn nh-qty__btn--minus" aria-label="Disminuir cantidad">—</button>
```

And replace:
```php
<button type="button" class="nh-qty-btn nh-qty-plus" aria-label="Aumentar cantidad">+</button>
```
with:
```php
<button type="button" class="nh-qty__btn nh-qty__btn--plus" aria-label="Aumentar cantidad">+</button>
```

Also wrap the whole block in a `.nh-qty` div and add `nh-qty__input` class to the input. The template becomes:

```php
<div class="nh-qty">
	<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>

	<?php if ( 'hidden' !== $type ) : ?>
		<button type="button" class="nh-qty__btn nh-qty__btn--minus" aria-label="Disminuir cantidad">—</button>
	<?php endif; ?>

	<input
		type="<?php echo esc_attr( $type ); ?>"
		<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
		id="<?php echo esc_attr( $input_id ); ?>"
		class="nh-qty__input <?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		aria-label="<?php esc_attr_e( 'Product quantity', 'woocommerce' ); ?>"
		<?php if ( in_array( $type, array( 'text', 'search', 'tel', 'url', 'email', 'password' ), true ) ) : ?>
			size="4"
		<?php endif; ?>
		min="<?php echo esc_attr( $min_value ); ?>"
		<?php if ( 0 < $max_value ) : ?>
			max="<?php echo esc_attr( $max_value ); ?>"
		<?php endif; ?>
		<?php if ( ! $readonly ) : ?>
			step="<?php echo esc_attr( $step ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
		<?php endif; ?>
	/>

	<?php if ( 'hidden' !== $type ) : ?>
		<button type="button" class="nh-qty__btn nh-qty__btn--plus" aria-label="Aumentar cantidad">+</button>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
</div>
```

Note: the outer `<div class="quantity">` is replaced by `<div class="nh-qty">`. The `quantity-input.php` template is rendered inside WooCommerce's own `.quantity` wrapper, so we need to check if WooCommerce adds its own wrapper. Looking at the original: it renders `<div class="quantity">` as its own root. So replacing with `.nh-qty` is correct — the JS selects `.nh-qty` as the wrapper.

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l templates/quantity-input.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add templates/quantity-input.php
git commit -m "feat(qty): update quantity-input.php template to use nh-qty BEM classes"
```

---

### Task 4: Update Cart Table Widget — `class-nh-cart-table-widget.php`

**Files:**
- Modify: `widgets/class-nh-cart-table-widget.php:172-176`

- [ ] **Step 1: Replace qty markup at lines 172-176**

Replace:
```php
<div class="nh-cart-product-qty">
    <button class="nh-cart-qty-btn nh-cart-qty-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">-</button>
    <input type="number" class="nh-cart-qty-input" value="<?php echo esc_attr( $quantity ); ?>" min="1" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
    <button class="nh-cart-qty-btn nh-cart-qty-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
</div>
```

With:
```php
<div class="nh-qty" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
    <button class="nh-qty__btn nh-qty__btn--minus" aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'nh-core' ); ?>">-</button>
    <input type="number" class="nh-qty__input" value="<?php echo esc_attr( $quantity ); ?>" min="1" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
    <button class="nh-qty__btn nh-qty__btn--plus" aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'nh-core' ); ?>">+</button>
</div>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l widgets/class-nh-cart-table-widget.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add widgets/class-nh-cart-table-widget.php
git commit -m "feat(qty): update cart-table widget qty markup to nh-qty BEM classes"
```

---

### Task 5: Update Cart Widget Content Template — `class-nh-cart-widget.php`

**Files:**
- Modify: `widgets/class-nh-cart-widget.php:571-575`

- [ ] **Step 1: Update content_template() qty markup at lines 571-575**

Replace:
```php
<div class="nh-cart-product-qty">
    <button class="nh-cart-qty-btn">-</button>
    <input type="number" class="nh-cart-qty-input" value="1" readonly>
    <button class="nh-cart-qty-btn">+</button>
</div>
```

With:
```php
<div class="nh-qty">
    <button class="nh-qty__btn nh-qty__btn--minus" aria-label="Disminuir cantidad">-</button>
    <input type="number" class="nh-qty__input" value="1" readonly>
    <button class="nh-qty__btn nh-qty__btn--plus" aria-label="Aumentar cantidad">+</button>
</div>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l widgets/class-nh-cart-widget.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add widgets/class-nh-cart-widget.php
git commit -m "feat(qty): update cart widget content_template qty to nh-qty BEM"
```

---

### Task 6: Remove Qty Logic from `nh-cart.js`

**Files:**
- Modify: `assets/js/nh-cart.js:13-15,22-61`

- [ ] **Step 1: Remove qty-related event bindings and updateQuantity method**

In `nh-cart.js`, delete lines 13-15 (qty event bindings):
```javascript
$(document).on('click', '.nh-cart-qty-plus', this.updateQuantity.bind(this, 'increase'));
$(document).on('click', '.nh-cart-qty-minus', this.updateQuantity.bind(this, 'decrease'));
$(document).on('change', '.nh-cart-qty-input', this.updateQuantity.bind(this, 'set'));
```

And delete the entire `updateQuantity` method (lines 22-61).

The `bindEvents` method becomes:
```javascript
bindEvents: function() {
    $(document).on('click', '.nh-cart-remove', this.removeItem.bind(this));
    $(document).on('click', '.nh-cart-clear', this.clearCart.bind(this));
    $(document).on('click', '.nh-cart-coupon-btn', this.applyCoupon.bind(this));
    $(document).on('click', '.nh-cart-remove-coupon', this.removeCoupon.bind(this));
},
```

- [ ] **Step 2: Verify JS syntax**

Run: `node --check assets/js/nh-cart.js`
Expected: No output (no syntax errors).

- [ ] **Step 3: Commit**

```bash
git add assets/js/nh-cart.js
git commit -m "refactor(qty): remove duplicate qty logic from nh-cart.js"
```

---

### Task 7: Update Asset Enqueues

**Files:**
- Modify: `inc/class-nh-core-woocommerce.php:148-161`
- Modify: `inc/class-nh-core-elementor.php:180-193`

- [ ] **Step 1: Replace `nh-quantity-buttons` enqueue with `nh-qty` in woocommerce.php**

In `inc/class-nh-core-woocommerce.php`, replace lines 148-161:

```php
// Enqueue premium quantity buttons styles and scripts
wp_enqueue_style(
    'nh-quantity-buttons',
    NH_CORE_URL . 'assets/css/nh-quantity-buttons.css',
    [],
    '1.0.0'
);
wp_enqueue_script(
    'nh-quantity-buttons',
    NH_CORE_URL . 'assets/js/nh-quantity-buttons.js',
    [ 'jquery' ],
    '1.0.0',
    true
);
```

With:
```php
// Enqueue unified quantity selector (nh-qty)
$qty_css = NH_CORE_PATH . 'assets/css/nh-qty.css';
$qty_js  = NH_CORE_PATH . 'assets/js/nh-qty.js';
wp_enqueue_style(
    'nh-qty',
    NH_CORE_URL . 'assets/css/nh-qty.css',
    [],
    file_exists( $qty_css ) ? filemtime( $qty_css ) : '1.0.0'
);
wp_enqueue_script(
    'nh-qty',
    NH_CORE_URL . 'assets/js/nh-qty.js',
    [],
    file_exists( $qty_js ) ? filemtime( $qty_js ) : '1.0.0',
    true
);
```

- [ ] **Step 2: Add `nh-qty` as script dependency in Elementor cart widget**

In `inc/class-nh-core-elementor.php`, in the `cart_register_scripts()` method (line 180), add `nh-qty` as a dependency:

```php
public function cart_register_scripts() {
    wp_enqueue_script(
        'nh-cart-widget',
        NH_CORE_URL . 'assets/js/nh-cart.js',
        [ 'jquery', 'nh-qty' ],
        '1.0.0',
        true
    );

    wp_localize_script( 'nh-cart-widget', 'nh_cart_params', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'nh_cart_nonce' ),
    ] );
}
```

Note: `nh-qty` is registered by `nh-quantity-buttons` handle in woocommerce.php. Since we renamed the handle to `nh-qty`, the dependency resolves correctly. The Elementor cart widget now depends on `nh-qty` being loaded first.

- [ ] **Step 3: Verify PHP syntax for both files**

Run: `php -l inc/class-nh-core-woocommerce.php && php -l inc/class-nh-core-elementor.php`
Expected: `No syntax errors detected` for both.

- [ ] **Step 4: Commit**

```bash
git add inc/class-nh-core-woocommerce.php inc/class-nh-core-elementor.php
git commit -m "refactor(qty): update asset enqueues to use nh-qty handle"
```

---

### Task 8: Remove Duplicated CSS and Old Files

**Files:**
- Modify: `assets/css/nh-woocommerce.css:2322-2356`
- Delete: `assets/js/nh-quantity-buttons.js`
- Delete: `assets/css/nh-quantity-buttons.css`

- [ ] **Step 1: Remove qty styles from nh-woocommerce.css**

In `assets/css/nh-woocommerce.css`, delete lines 2322-2356 (the `.nh-cart-product-qty`, `.nh-cart-qty-btn`, `.nh-cart-qty-input` rules). These are now in `nh-qty.css`.

- [ ] **Step 2: Delete old files**

```bash
rm assets/js/nh-quantity-buttons.js assets/css/nh-quantity-buttons.css
```

- [ ] **Step 3: Verify no remaining references to old classes**

Run: `grep -rn 'nh-qty-btn\|nh-qty-minus\|nh-qty-plus\|nh-qty-disabled\|nh-cart-qty-btn\|nh-cart-qty-minus\|nh-cart-qty-plus\|nh-cart-qty-input\|nh-cart-product-qty\|nh-quantity-buttons' --include='*.php' --include='*.js' --include='*.css' .`
Expected: No output (all old references removed).

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "refactor(qty): remove old quantity files and duplicated CSS"
```

---

### Task 9: Final Verification & Manual Test

- [ ] **Step 1: Run PHP lint on all modified PHP files**

```bash
php -l templates/quantity-input.php && \
php -l widgets/class-nh-cart-table-widget.php && \
php -l widgets/class-nh-cart-widget.php && \
php -l inc/class-nh-core-woocommerce.php && \
php -l inc/class-nh-core-elementor.php
```
Expected: All pass with no syntax errors.

- [ ] **Step 2: Run JS syntax check on all JS files**

```bash
node --check assets/js/nh-qty.js && \
node --check assets/js/nh-cart.js
```
Expected: No output (no syntax errors).

- [ ] **Step 3: Search for orphaned old class references**

```bash
grep -rn 'nh-qty-btn\|nh-qty-minus\|nh-qty-plus\|nh-cart-qty-btn\|nh-cart-qty-minus\|nh-cart-qty-plus\|nh-cart-qty-input\|nh-cart-product-qty\|nh-quantity-buttons' --include='*.php' --include='*.js' --include='*.css' .
```
Expected: No output.

- [ ] **Step 4: Verify new files exist and old files are deleted**

```bash
ls -la assets/js/nh-qty.js assets/css/nh-qty.css && \
! ls assets/js/nh-quantity-buttons.js 2>/dev/null && \
! ls assets/css/nh-quantity-buttons.css 2>/dev/null
```
Expected: First `ls` shows both files; `! ls` commands produce no output (files don't exist).

- [ ] **Step 5: Manual test on DDEV**

1. Open `https://ecommerce.ddev.site:8443/product/` — click any product
2. On PDP: click ± buttons → qty updates locally, disabled states work at min/max
3. Add to cart → go to cart page (`/carrito/`)
4. On cart page: click ± → qty updates locally, no full page reload
5. Go to Home → open side cart → click ± in side cart drawer → verify AJAX works
6. Check browser console for `[NH Qty]` errors — should be none

- [ ] **Step 6: Final commit (if any fixes needed)**

```bash
git add -A && git commit -m "fix(qty): final adjustments after manual testing"
```
