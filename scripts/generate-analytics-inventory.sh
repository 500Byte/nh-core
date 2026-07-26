#!/usr/bin/env bash
set -euo pipefail

# ── NH Core — Analytics & Purchase Inventory ─────────────────────────────────
# Generates a txt listing all files involved in the purchase funnel and/or analytics.
#
# Usage:
#   ./scripts/generate-analytics-inventory.sh
#   ./scripts/generate-analytics-inventory.sh -o custom-output.txt
# ──────────────────────────────────────────────────────────────────────────────

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUTPUT="${PLUGIN_DIR}/docs/analytics-inventory.txt"

while [[ $# -gt 0 ]]; do
    case "$1" in
        -o|--output) OUTPUT="$2"; shift 2 ;;
        *) echo "Unknown flag: $1"; exit 1 ;;
    esac
done

mkdir -p "$(dirname "$OUTPUT")"

cat > "$OUTPUT" << 'HEADER'
================================================================================
 NH Core — Analytics & Purchase Funnel Inventory
================================================================================
 Auto-generated file. Do not edit manually.
 Run: ./scripts/generate-analytics-inventory.sh
================================================================================

TRACKING SYSTEM
───────────────────────────────────────────────────────────────────────────────
  inc/class-nh-core-tracking.php
    • GA4 consent mode v2 (gtag consent denied + wait_for_update)
    • GTM container injection (wp_head)
    • GA4 Standard Events: view_item, add_to_cart, view_cart, remove_from_cart,
      begin_checkout, purchase
    • Legacy custom events: ver_producto, agregar_carrito, compra_exitosa
    • Meta Pixel: ViewContent, AddToCart, ViewCart, RemoveFromCart,
      InitiateCheckout, Purchase (with event_id for CAPI dedup)
    • Session fallback: capture_add_to_cart_in_session / capture_remove_from_cart
    • _nh_tracked_purchase meta to prevent duplicate purchase events
    • is_tracking_disabled(): local, ddev, staging/dev domains
    • datalayer_ver_carrito(): view_cart on is_cart()
    • datalayer_ver_producto(): view_item on singular products
    • datalayer_iniciar_pago(): begin_checkout on checkout page
    • datalayer_compra_exitosa(): purchase on order-received page
    • fb_domain_verification(): Meta domain verification meta tag

AJAX ADD-TO-CART SYSTEM
───────────────────────────────────────────────────────────────────────────────
  inc/class-nh-core-woocommerce.php
    • nh_add_to_cart(): AJAX handler for add-to-cart
    • nh_buy_now(): AJAX handler for buy-now (checkout redirect)
    • nh_remove_cart_item(): AJAX handler for side-cart item removal
    • nh_cart_params: localized JS config (ajax_url, nonce, currency)
    • wc_cart_fragments refresh after ATC

  inc/nh-atc-helpers.php
    • render_nh_atc_block(): block renderer for simple/variable/external
    • data-nh-* tracking attributes on ATC buttons
    • data-nh-category attribute on Buy Now button
    • get_product_categories(): extracts category hierarchy
    • $category variable fix for external products

ATC TEMPLATES (Elementor Widget Overrides)
───────────────────────────────────────────────────────────────────────────────
  templates/nh-add-to-cart/simple.php
    • Simple product ATC form with data-nh-* attributes on button
    • Loading spinner (ph-light ph-spinner-gap) + check-circle (ph-fill)
    • "Ver carrito" link hidden (display: none)

  templates/nh-add-to-cart/variable.php
    • Variable product ATC with Variation Swatches compatibility
    • Contracts C1-C8 for WVS integration
    • found_variation / reset_data listeners sync price + variation_id

  templates/nh-add-to-cart/external.php
    • External/affiliate product ATC
    • data-nh-* attributes for tracking

FRONTEND JS
───────────────────────────────────────────────────────────────────────────────
  assets/js/nh-datalayer-cart.js
    • GA4 add_to_cart / remove_from_cart events (dataLayer push)
    • Meta Pixel AddToCart / RemoveFromCart (fbq track)
    • .attr() reads for data-nh-* (not .data() — avoids jQuery cache)
    • Capturing-phase listener on .cart .remove for WC native removals
    • 3-tier fallback: custom endpoint → WC AJAX → capturing listener
    • event_id for Meta CAPI deduplication

  assets/js/nh-add-to-cart.js
    • AJAX form submit intercept (prevents page reload)
    • Buy Now handler with setTimeout(150ms) for pixel completion
    • found_variation / reset_data listeners for variable products
    • Phosphor loading spinner on .loading state
    • Phosphor check-circle on .added state

  assets/js/nh-side-cart.js
    • Side cart open/close/drag animations
    • removed_from_cart trigger with synthetic button (.attr())
    • Error handler for failed removals

  assets/js/nh-menu-cart.js
    • Menu cart widget (Elementor editor guards)

CART & CHECKOUT TEMPLATES
───────────────────────────────────────────────────────────────────────────────
  templates/cart/cart.php
    • Cart page with data-nh-* on .nh-cart-item div
    • jQuery capturing-phase listener for WC native remove clicks

  widgets/class-nh-side-cart-widget.php
    • Side cart with data-nh-* on <li> elements
    • Cart item rendering with tracking attributes

  widgets/class-nh-cart-widget.php
    • Cart widget (Elementor)

  widgets/class-nh-checkout-widget.php
    • Checkout widget (Elementor)

CSS & FONTS
───────────────────────────────────────────────────────────────────────────────
  assets/css/nh-add-to-cart.css
    • ATC layout, Phosphor-Fill spinner/check-circle overrides
    • added_to_cart hidden (replaced by Phosphor check-circle)

  inc/class-nh-core-elementor.php
    • Phosphor-Fill + Phosphor-Light CSS enqueued globally
    • Elementor widget content_template() removed (prevents editor deformation)

  inc/class-nh-core-icons.php
    • Phosphor icon picker (6 weights: thin, light, regular, bold, fill, duotone)
    • Icon JSON files for Elementor editor

PLUGIN CORE
───────────────────────────────────────────────────────────────────────────────
  nh-core.php
    • Plugin header, NH_CORE_VERSION constant
    • Loader initialization

  inc/class-nh-core-loader.php
    • Module loader (hooks all classes into WP)

DATA FLOW
───────────────────────────────────────────────────────────────────────────────
  Product Page:
    datalayer_ver_producto() → view_item (GA4 + Meta ViewContent)

  Add to Cart:
    AJAX nh_add_to_cart() → session capture → nh-datalayer-cart.js
    → add_to_cart (GA4) + AddToCart (Meta Pixel)
    → event_id: add_to_cart_{id}_{hash}

  Cart Page:
    datalayer_ver_carrito() → view_cart (GA4 + Meta ViewCart)
    → event_id: view_cart_{md5(items)}

  Remove from Cart:
    WC AJAX → nh-datalayer-cart.js → remove_from_cart (GA4 + Meta RemoveFromCat)
    → event_id: remove_{id}_{timestamp}

  Checkout Page:
    datalayer_iniciar_pago() → begin_checkout (GA4 + Meta InitiateCheckout)
    → event_id: begin_checkout_{md5(hash)}

  Order Received:
    datalayer_compra_exitosa() → purchase (GA4 + Meta Purchase)
    → event_id: purchase_{order_id}
    → _nh_tracked_purchase meta set AFTER script render

  Consent Mode:
    gtag('consent','default',{analytics_storage:'denied',...})
    wait_for_update: 500ms → external CMP calls gtag('consent','update',...)

CONSENT & PRIVACY
───────────────────────────────────────────────────────────────────────────────
  • Consent Mode v2: denied by default, requires external CMP
  • _nh_tracked_purchase prevents duplicate purchase events on refresh
  • is_tracking_disabled() blocks tracking on local/ddev/staging
  • Staging regex: /staging|dev\.|\.dev$/

BACKLOG (Not Yet Implemented)
───────────────────────────────────────────────────────────────────────────────
  • view_item_list, select_item, add_payment_info, view_shipping_info
  • apply_coupon, quantity_change, search events
  • External/affiliate products don't fire added_to_cart (plain <a target="_blank">)
  • item_id always resolves to parent product, never variation (by design)
  • Dual source of truth: nh-atc-helpers.php manual vs inject_tracking_data_attributes()
  • view_item fires in wp_head before Pixel loaded via GTM
  • begin_checkout event_id depends on cart_hash (non-reproducible server-side)

================================================================================
 Generated: $(date '+%Y-%m-%d %H:%M:%S %Z')
================================================================================
HEADER

# Replace the placeholder date
sed -i "s/\$(date.*)/$(date '+%Y-%m-%d %H:%M:%S %Z')/" "$OUTPUT"

echo "Generated: $OUTPUT"
