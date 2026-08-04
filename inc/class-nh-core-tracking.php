<?php
/**
 * NH Core — Tracking Class
 * GA4 Enhanced Ecommerce + Meta Pixel Standard Events
 * Consent Mode v2 handled by Pressidium Cookie Consent plugin
 *
 * @package NH_Core
 * @version 1.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Tracking {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Register all WordPress hooks and filters.
     */
    private function init_hooks() {
        // Page context — BEFORE Consent Mode (-10000) and GTM (-9999)
        add_action( 'wp_head', [ $this, 'inject_page_context' ], -10001 );

        // Google Tag Manager
        add_action( 'wp_head', [ $this, 'inject_gtm_head' ], -9999 );
        add_action( 'wp_body_open', [ $this, 'inject_gtm_body' ], -9999 );

        // Product page tracking
        add_action( 'wp_head', [ $this, 'datalayer_ver_producto' ] );

        // Cart tracking script
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_datalayer_cart_script' ] );

        // Non-AJAX add-to-cart fallback (session-based)
        add_action( 'woocommerce_add_to_cart', [ $this, 'capture_add_to_cart_in_session' ], 10, 6 );

        // Checkout & Purchase tracking
        add_action( 'wp_footer', [ $this, 'datalayer_iniciar_pago' ] );
        add_action( 'wp_footer', [ $this, 'datalayer_compra_exitosa' ] );

        // View Cart tracking
        add_action( 'wp_footer', [ $this, 'datalayer_ver_carrito' ] );

        // Remove from cart tracking
        add_action( 'woocommerce_cart_item_removed', [ $this, 'capture_remove_from_cart' ], 10, 2 );

        // Welcome form tracking (Elementor Pro AJAX submit_success → dataLayer)
        add_action( 'wp_footer', [ $this, 'datalayer_form_bienvenida' ] );

        // Meta Pixel domain verification
        add_action( 'wp_head', [ $this, 'fb_domain_verification' ], 1 );

        // Inject data-nh-* attributes on add-to-cart buttons (ALWAYS — not gated by tracking disable)
        add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'inject_tracking_data_attributes' ], 10, 2 );
        add_filter( 'woocommerce_product_add_to_cart_args', [ $this, 'inject_tracking_data_attributes' ], 10, 2 );
    }

    // ============================================================
    // ENVIRONMENT & DEBUG
    // ============================================================

    /**
     * Check if tracking debug mode is enabled via URL parameter.
     * Usage: ?nh_tracking_debug=1 forces tracking ON regardless of environment.
     */
    public function is_tracking_debug_mode() {
        return isset( $_GET['nh_tracking_debug'] ) && $_GET['nh_tracking_debug'] === '1';
    }

    /**
     * Determine if tracking scripts should be suppressed.
     * Returns true in local/development environments unless debug mode is active.
     */
    public function is_tracking_disabled() {
        if ( $this->is_tracking_debug_mode() ) {
            return false;
        }
        return (
            ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'local' )
            || ( isset( $_SERVER['HTTP_HOST'] ) && strpos( $_SERVER['HTTP_HOST'], '.ddev.site' ) !== false )
            || ( isset( $_SERVER['HTTP_HOST'] ) && preg_match( '/staging|dev\.|\.dev$/', $_SERVER['HTTP_HOST'] ) )
        );
    }

    /**
     * Legacy method — kept for backward compatibility.
     * @deprecated Use is_tracking_disabled() for tracking decisions.
     */
    public function is_local_env() {
        if ( isset( $_SERVER['HTTP_X_NH_TESTING'] ) && $_SERVER['HTTP_X_NH_TESTING'] === 'true' ) {
            return false;
        }
        return function_exists( 'wp_get_environment_type' )
            && in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
    }

    // ============================================================
    // GOOGLE TAG MANAGER
    // ============================================================

    public function inject_gtm_head() {
        if ( $this->is_tracking_disabled() ) return;
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-N5G49CWP');</script>
        <!-- End Google Tag Manager -->
        <?php
    }

    public function inject_gtm_body() {
        if ( $this->is_tracking_disabled() ) return;
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5G49CWP"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }

    // ============================================================
    // PAGE CONTEXT ENRICHMENT
    // ============================================================

    public function inject_page_context() {
        if ( $this->is_tracking_disabled() ) return;

        $page_type = 'other';
        if ( is_front_page() ) $page_type = 'home';
        elseif ( is_product() ) $page_type = 'product';
        elseif ( is_product_category() ) $page_type = 'product_category';
        elseif ( is_product_tag() ) $page_type = 'product_tag';
        elseif ( is_page( 'shop' ) || is_shop() ) $page_type = 'shop';
        elseif ( is_checkout() ) $page_type = 'checkout';
        elseif ( is_cart() ) $page_type = 'cart';
        elseif ( is_order_received_page() ) $page_type = 'order_received';
        elseif ( is_account_page() ) $page_type = 'account';
        elseif ( is_singular() ) $page_type = 'page';

        $user_status = 'guest';
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_status = in_array( 'customer', (array) $current_user->roles ) ? 'customer' : 'admin';
        }

        $product_category = '';
        if ( is_product() || is_product_category() ) {
            $terms = get_the_terms( get_the_ID(), 'product_cat' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $product_category = $terms[0]->name;
            }
        }
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'page_type': '<?php echo esc_js( $page_type ); ?>',
            'user_login_status': '<?php echo esc_js( $user_status ); ?>',
            'product_category': '<?php echo esc_js( $product_category ); ?>',
            'currency': 'COP'
        });
        </script>
        <?php
    }

    // ============================================================
    // DATA ATTRIBUTES (ALWAYS INJECTED — not gated by tracking)
    // ============================================================

    /**
     * Inject tracking data attributes into add-to-cart buttons.
     * These are inert HTML data attributes; they do not fire tracking by themselves.
     * MUST be injected regardless of tracking state so the frontend JS can read them.
     */
    public function inject_tracking_data_attributes( $args, $product ) {
        if ( ! is_a( $product, 'WC_Product' ) ) {
            return $args;
        }

        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        $category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

        $args['data-nh-product-id']   = (string) $product->get_id();
        $args['data-nh-product-name'] = sanitize_text_field( $product->get_name() );
        $args['data-nh-product-price'] = (string) $product->get_price();
        $args['data-nh-currency']     = 'COP';
        $args['data-nh-category']     = sanitize_text_field( $category );

        return $args;
    }

    // ============================================================
    // VIEW ITEM (Product Page)
    // ============================================================

    public function datalayer_ver_producto() {
        if ( $this->is_tracking_disabled() || ! is_product() ) return;

        $product = wc_get_product( get_the_ID() );
        if ( ! is_a( $product, 'WC_Product' ) ) return;

        $id       = (string) $product->get_id();
        $name     = sanitize_text_field( $product->get_name() );
        $price    = $product->get_price();
        $category = '';

        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $category = sanitize_text_field( $terms[0]->name );
        }
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        // GA4 Standard Event: view_item
        window.dataLayer.push({
            'event': 'view_item',
            'ecommerce': {
                'currency': 'COP',
                'value': <?php echo esc_js( $price ); ?>,
                'items': [{
                    'item_id': '<?php echo esc_js( $id ); ?>',
                    'item_name': '<?php echo esc_js( $name ); ?>',
                    'price': <?php echo esc_js( $price ); ?>,
                    'quantity': 1,
                    'item_category': '<?php echo esc_js( $category ); ?>'
                }]
            }
        });
        // Meta Pixel: ViewContent — fired via GTM (tag 19)
        </script>
        <?php
    }

    // ============================================================
    // VIEW CART
    // ============================================================

    public function datalayer_ver_carrito() {
        if ( $this->is_tracking_disabled() || ! is_cart() ) return;

        $cart = WC()->cart;
        if ( ! $cart ) return;

        $total = $cart->get_cart_contents_total();
        $items = [];

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( ! $_product ) continue;

            $item = [
                'item_id'   => (string) $_product->get_id(),
                'item_name' => sanitize_text_field( $_product->get_name() ),
                'price'     => $_product->get_price(),
                'quantity'  => $cart_item['quantity'],
            ];

            if ( $_product->is_type( 'variation' ) ) {
                $variant_str = $this->get_clean_variation_string( $_product, $cart_item['variation'] );
                if ( $variant_str ) {
                    $item['item_variant'] = $variant_str;
                }
            }

            $items[] = $item;
        }

        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        // GA4 Standard Event: view_cart
        window.dataLayer.push({
            'event': 'view_cart',
            'ecommerce': {
                'currency': '<?php echo esc_js( get_woocommerce_currency() ); ?>',
                'value': <?php echo esc_js( $total ); ?>,
                'items': <?php echo wp_json_encode( $items ); ?>
            }
        });
        // Meta Pixel: ViewCart — fired via GTM
        </script>
        <?php
    }

    // ============================================================
    // CART SCRIPT (Frontend JS for add_to_cart)
    // ============================================================

    public function enqueue_datalayer_cart_script() {
        if ( $this->is_tracking_disabled() ) return;

        wp_enqueue_script(
            'nh-datalayer-cart',
            NH_CORE_URL . 'assets/js/nh-datalayer-cart.js',
            [ 'jquery' ],
            '1.6.1',
            true
        );

        // Non-AJAX fallback: emit events from session if present
        if ( isset( WC()->session ) ) {
            // add_to_cart fallback
            $added_event = WC()->session->get( 'nh_added_to_cart_event' );
            if ( ! empty( $added_event ) ) {
                WC()->session->set( 'nh_added_to_cart_event', null );
                $ga4_event = [
                    'event'      => 'add_to_cart',
                    'ecommerce'  => [
                        'currency' => 'COP',
                        'value'    => $added_event['price'] * $added_event['quantity'],
                        'items'    => [[
                            'item_id'       => $added_event['product_id'],
                            'item_name'     => $added_event['item_name'],
                            'price'         => $added_event['price'],
                            'quantity'      => $added_event['quantity'],
                            'item_variant'  => $added_event['item_variant'] ?? null,
                        ]],
                    ],
                ];
                wp_add_inline_script(
                    'nh-datalayer-cart',
                    'window.dataLayer = window.dataLayer || []; window.dataLayer.push(' . wp_json_encode( $ga4_event ) . ');',
                    'before'
                );
            }

            // remove_from_cart fallback
            $removed_event = WC()->session->get( 'nh_removed_from_cart_event' );
            if ( ! empty( $removed_event ) ) {
                WC()->session->set( 'nh_removed_from_cart_event', null );
                $ga4_remove = [
                    'event'      => 'remove_from_cart',
                    'ecommerce'  => [
                        'currency' => 'COP',
                        'value'    => $removed_event['price'] * $removed_event['quantity'],
                        'items'    => [[
                            'item_id'       => $removed_event['product_id'],
                            'item_name'     => $removed_event['item_name'],
                            'price'         => $removed_event['price'],
                            'quantity'      => $removed_event['quantity'],
                        ]],
                    ],
                ];
                wp_add_inline_script(
                    'nh-datalayer-cart',
                    'window.dataLayer.push(' . wp_json_encode( $ga4_remove ) . ');',
                    'before'
                );
            }
        }
    }

    /**
     * Capture add-to-cart for non-AJAX requests (form submit fallback).
     * Stores a minimal event in session; the frontend script emits it on next page load.
     */
    public function capture_add_to_cart_in_session( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        if ( $this->is_tracking_disabled() || wp_doing_ajax() || isset( $_REQUEST['wc-ajax'] ) ) {
            return;
        }

        $product_id_to_track = $variation_id ? (string) $variation_id : (string) $product_id;
        $is_variant          = (bool) $variation_id;

        $product = wc_get_product( $variation_id ?: $product_id );
        $name    = $product ? sanitize_text_field( $product->get_name() ) : '';
        $price   = $product ? $product->get_price() : 0;

        $event_data = [
            'event'           => 'add_to_cart',
            'product_id'      => $product_id_to_track,
            'item_name'       => $name,
            'price'           => $price,
            'quantity'        => $quantity,
            'item_is_variant' => $is_variant,
        ];

        if ( $variation_id && ! empty( $variation ) ) {
            $variant_product = wc_get_product( $variation_id );
            if ( $variant_product ) {
                $variant_str = $this->get_clean_variation_string( $variant_product, $variation );
                if ( $variant_str ) {
                    $event_data['item_variant'] = $variant_str;
                }
            }
        }

        if ( isset( WC()->session ) ) {
            WC()->session->set( 'nh_added_to_cart_event', $event_data );
        }
    }

    /**
     * Capture remove-from-cart for tracking.
     * Stores event in session for frontend emission on next page load (non-AJAX).
     * For AJAX removals (side cart), the frontend JS handles it directly.
     */
    public function capture_remove_from_cart( $cart_item_key, $cart ) {
        if ( $this->is_tracking_disabled() ) {
            return;
        }

        $removed_items = $cart->get_removed_cart_contents();
        $cart_item = $removed_items[ $cart_item_key ] ?? null;
        if ( ! $cart_item ) {
            return;
        }

        $product_id   = $cart_item['variation_id'] ?: $cart_item['product_id'];
        $parent_id    = $cart_item['product_id'];
        $product      = wc_get_product( $product_id );
        if ( ! $product ) return;

        $event_data = [
            'event'      => 'remove_from_cart',
            'product_id' => (string) $parent_id,
            'item_name'  => sanitize_text_field( $product->get_name() ),
            'price'      => floatval( $product->get_price() ),
            'quantity'   => intval( $cart_item['quantity'] ),
        ];

        if ( $cart_item['variation_id'] ) {
            $variant_str = $this->get_clean_variation_string( $product, $cart_item['variation'] ?? [] );
            if ( $variant_str ) {
                $event_data['item_variant'] = $variant_str;
            }
        }

        // AJAX: emitir inline (el JS listener de removed_from_cart también lo maneja)
        if ( wp_doing_ajax() || isset( $_REQUEST['wc-ajax'] ) ) {
            // No store in session for AJAX — the JS handler fires removed_from_cart directly
            return;
        }

        // Non-AJAX: store in session for emission on next page load
        if ( isset( WC()->session ) ) {
            WC()->session->set( 'nh_removed_from_cart_event', $event_data );
        }
    }

    // ============================================================
    // BEGIN CHECKOUT
    // ============================================================

    public function datalayer_iniciar_pago() {
        if ( $this->is_tracking_disabled() || ! is_checkout() || is_order_received_page() ) return;

        $cart = WC()->cart;
        if ( ! $cart ) return;

        $total = $cart->get_cart_contents_total();
        $items = [];

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( ! $_product ) continue;

            $item = [
                'item_id'   => (string) $_product->get_id(),
                'item_name' => sanitize_text_field( $_product->get_name() ),
                'price'     => $_product->get_price(),
                'quantity'  => $cart_item['quantity'],
            ];

            if ( $_product->is_type( 'variation' ) ) {
                $variant_str = $this->get_clean_variation_string( $_product, $cart_item['variation'] );
                if ( $variant_str ) {
                    $item['item_variant'] = $variant_str;
                }
            }

            $items[] = $item;
        }

        $coupon_codes = $cart->get_applied_coupons();
        $item_ids     = wp_list_pluck( $items, 'item_id' );

        // Deterministic event_id based on cart hash (stable across browser/server)
        $checkout_event_id = 'begin_checkout_' . md5( $cart->get_cart_hash() );

        // Aggregate item names for Meta Pixel content_name
        $item_names   = wp_list_pluck( $items, 'item_name' );
        $content_name = implode( ', ', array_slice( $item_names, 0, 3 ) );
        if ( count( $item_names ) > 3 ) {
            $content_name .= ' ...';
        }
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        // GA4 Standard Event: begin_checkout
        window.dataLayer.push({
            'event': 'begin_checkout',
            'ecommerce': {
                'currency': 'COP',
                'value': <?php echo esc_js( $total ); ?>,
                'coupon': <?php echo wp_json_encode( $coupon_codes ); ?>,
                'items': <?php echo wp_json_encode( $items ); ?>
            }
        });
        // Meta Pixel: InitiateCheckout — fired via GTM (tag 21)
        </script>
        <?php
    }

    // ============================================================
    // PURCHASE (Order Received)
    // ============================================================

    public function datalayer_compra_exitosa() {
        if ( $this->is_tracking_disabled() || ! is_order_received_page() ) return;

        global $wp;
        $order_id = isset( $wp->query_vars['order-received'] ) ? intval( $wp->query_vars['order-received'] ) : 0;
        if ( ! $order_id ) return;

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_meta( '_nh_tracked_purchase' ) ) return;

        $total        = $order->get_total();
        $currency     = $order->get_currency();
        $coupon_codes = $order->get_coupon_codes();
        $items        = [];
        $item_ids     = [];

        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            if ( ! $product ) continue;

            $item_data = [
                'item_id'   => (string) $product->get_id(),
                'item_name' => sanitize_text_field( $product->get_name() ),
                'price'     => $product->get_price(),
                'quantity'  => $item->get_quantity(),
            ];

            if ( $product->is_type( 'variation' ) ) {
                $variant_str = $this->get_clean_variation_string( $product );
                if ( $variant_str ) {
                    $item_data['item_variant'] = $variant_str;
                }
            }

            $items[]    = $item_data;
            $item_ids[] = (string) $product->get_id();
        }

        $shipping = $order->get_shipping_total();
        $tax      = $order->get_total_tax();

        $event_id = 'purchase_' . $order_id;
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        // GA4 Standard Event: purchase
        window.dataLayer.push({
            'event': 'purchase',
            'ecommerce': {
                'transaction_id': '<?php echo esc_js( $order_id ); ?>',
                'currency': '<?php echo esc_js( $currency ); ?>',
                'value': <?php echo esc_js( $total ); ?>,
                'tax': <?php echo esc_js( $tax ); ?>,
                'shipping': <?php echo esc_js( $shipping ); ?>,
                'coupon': <?php echo wp_json_encode( $coupon_codes ); ?>,
                'items': <?php echo wp_json_encode( $items ); ?>
            }
        });
        // Meta Pixel: Purchase — fired via GTM (tag 22)
        </script>
        <?php
        // Mark AFTER script output — if JS is disabled, mark is never set,
        // so event retries on next page load (correct behavior).
        $order->update_meta_data( '_nh_tracked_purchase', 'yes' );
        $order->save();
    }

    /**
     * Welcome form tracking bridge.
     *
     * Elementor Pro submits the form via AJAX (preventDefault + fetch), so
     * GTM's native "Form Submission" auto-event does not fire. The only
     * reliable moment is Elementor's own `submit_success` jQuery event, which
     * fires on the <form> element ONLY after the AJAX request succeeded.
     *
     * Listens for it on #form-bienvenida and pushes to the dataLayer, which
     * GTM's custom event trigger (nh_form_bienvenida_submit) consumes.
     */
    public function datalayer_form_bienvenida() {
        if ( $this->is_tracking_disabled() ) {
            return;
        }
        ?>
        <script>
        (function() {
            var form = document.getElementById('form-bienvenida');
            if ( ! form ) {
                return;
            }
            form.addEventListener('submit_success', function() {
                var get = function( name ) {
                    var el = form.querySelector('[name="form_fields[' + name + ']"]');
                    return el ? el.value : '';
                };
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'nh_form_bienvenida_submit',
                    'nh_name': get('name'),
                    'nh_email': get('email')
                });
            });
        })();
        </script>
        <?php
    }

    // ============================================================
    // META PIXEL — DOMAIN VERIFICATION
    // ============================================================

    public function fb_domain_verification() {
        if ( $this->is_tracking_disabled() ) return;
        ?>
        <meta name="facebook-domain-verification" content="8z17ny54fvdte6y0uep5fkbxcnbww1" />
        <?php
    }

    // ============================================================
    // HELPER: CLEAN VARIATION STRING
    // ============================================================

    /**
     * Get a clean variation attribute string for tracking.
     *
     * @param WC_Product $product            Variation product object.
     * @param array      $variation_attrs    Optional variation attributes from cart item.
     * @return string                        Clean variation string, e.g. "Color: Negro / Talla: M".
     */
    private function get_clean_variation_string( $product, $variation_attrs = [] ) {
        if ( ! $product || ! $product->is_type( 'variation' ) ) {
            return '';
        }

        $attributes = ! empty( $variation_attrs ) ? $variation_attrs : $product->get_variation_attributes();
        $clean      = [];

        foreach ( $attributes as $attribute_key => $attribute_value ) {
            if ( empty( $attribute_value ) ) continue;

            $label = str_replace( 'pa_', '', wc_attribute_label( $attribute_key, $product ) );
            $label = sanitize_text_field( html_entity_decode( $label ) );
            $value = sanitize_text_field( html_entity_decode( $attribute_value ) );

            $clean[] = $label . ': ' . $value;
        }

        return implode( ' / ', $clean );
    }
}

// Initialize singleton
NH_Core_Tracking::get_instance();
