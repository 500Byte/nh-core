<?php
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

    private function init_hooks() {
        add_action( 'wp_head', [ $this, 'inject_gtm_head' ], -9999 );
        add_action( 'wp_body_open', [ $this, 'inject_gtm_body' ], -9999 );
        add_action( 'wp_head', [ $this, 'datalayer_ver_producto' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_datalayer_cart_script' ] );
        add_action( 'woocommerce_add_to_cart', [ $this, 'capture_add_to_cart_in_session' ], 10, 6 );
        add_action( 'wp_footer', [ $this, 'datalayer_iniciar_pago' ] );
        add_action( 'wp_footer', [ $this, 'datalayer_compra_exitosa' ] );
        add_action( 'wp_head', [ $this, 'fb_domain_verification' ], 1 );
    }

    public function is_local_env() {
        if ( isset( $_SERVER['HTTP_X_NH_TESTING'] ) && $_SERVER['HTTP_X_NH_TESTING'] === 'true' ) {
            return false;
        }
        return function_exists( 'wp_get_environment_type' ) && in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
    }

    public function inject_gtm_head() {
        if ( $this->is_local_env() ) return;
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
        if ( $this->is_local_env() ) return;
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5G49CWP"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }

    public function datalayer_ver_producto() {
        if ( $this->is_local_env() || ! is_product() ) return;
        $product = wc_get_product( get_the_ID() );
        if ( is_a( $product, 'WC_Product' ) ) {
            $id = $product->get_id();
            $name = $product->get_name();
            $price = $product->get_price();
            ?>
            <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'ver_producto',
                'ecommerce': {
                    'currency': 'COP',
                    'value': <?php echo esc_js($price); ?>,
                    'items': [{
                        'item_id': '<?php echo esc_js($id); ?>',
                        'item_name': '<?php echo esc_js($name); ?>',
                        'price': <?php echo esc_js($price); ?>,
                        'quantity': 1
                    }]
                }
            });
            </script>
            <?php
        }
    }

    public function enqueue_datalayer_cart_script() {
        if ( $this->is_local_env() ) return;
        wp_enqueue_script(
            'nh-datalayer-cart',
            NH_CORE_URL . 'assets/js/nh-datalayer-cart.js',
            [ 'jquery' ], // Garantizar orden de carga correcto
            '1.0.0',
            true
        );

        if ( isset( WC()->session ) ) {
            $added_event = WC()->session->get('nh_added_to_cart_event');
            if ( ! empty( $added_event ) ) {
                WC()->session->set('nh_added_to_cart_event', null);
                wp_add_inline_script(
                    'nh-datalayer-cart',
                    'window.dataLayer = window.dataLayer || []; window.dataLayer.push(' . json_encode($added_event) . ');',
                    'before'
                );
            }
        }
    }

    public function capture_add_to_cart_in_session( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        if ( $this->is_local_env() || wp_doing_ajax() || isset( $_REQUEST['wc-ajax'] ) ) {
            return;
        }
        $event_data = [
            'event'           => 'agregar_carrito',
            'product_id'      => $variation_id ? $variation_id : $product_id,
            'item_is_variant' => $variation_id ? true : false,
        ];
        if ( $variation_id && ! empty( $variation ) ) {
            $product = wc_get_product( $variation_id );
            if ( $product ) {
                $variant_str = $this->get_clean_variation_string( $product, $variation );
                if ( $variant_str ) {
                    $event_data['item_variant'] = $variant_str;
                }
            }
        }
        if ( isset( WC()->session ) ) {
            WC()->session->set('nh_added_to_cart_event', $event_data);
        }
    }

    public function datalayer_iniciar_pago() {
        if ( $this->is_local_env() || ! is_checkout() || is_order_received_page() ) return;
        $cart = WC()->cart;
        if ( ! $cart ) return;
        $total = $cart->get_cart_contents_total();
        $items = [];
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( $_product ) {
                $item = [
                    'item_id' => $_product->get_id(),
                    'item_name' => $_product->get_name(),
                    'price' => $_product->get_price(),
                    'quantity' => $cart_item['quantity'],
                    'item_is_variant' => $_product->is_type('variation'),
                ];
                if ( $_product->is_type('variation') ) {
                    $variant_str = $this->get_clean_variation_string( $_product, $cart_item['variation'] );
                    if ( $variant_str ) {
                        $item['item_variant'] = $variant_str;
                    }
                }
                $items[] = $item;
            }
        }
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'iniciar_pago',
            'ecommerce': {
                'currency': 'COP',
                'value': <?php echo esc_js($total); ?>,
                'items': <?php echo json_encode($items); ?>
            }
        });
        </script>
        <?php
    }

    public function datalayer_compra_exitosa() {
        if ( $this->is_local_env() || ! is_order_received_page() ) return;
        global $wp;
        $order_id = isset( $wp->query_vars['order-received'] ) ? intval( $wp->query_vars['order-received'] ) : 0;
        if ( ! $order_id ) return;
        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_meta( '_nh_tracked_purchase' ) ) return;
        $order->update_meta_data( '_nh_tracked_purchase', 'yes' );
        $order->save();
        $total = $order->get_total();
        $currency = $order->get_currency();
        $items = [];
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            if ( $product ) {
                $item_data = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'quantity' => $item->get_quantity(),
                    'item_is_variant' => $product->is_type('variation'),
                ];
                if ( $product->is_type('variation') ) {
                    $variant_str = $this->get_clean_variation_string( $product );
                    if ( $variant_str ) {
                        $item_data['item_variant'] = $variant_str;
                    }
                }
                $items[] = $item_data;
            }
        }
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'compra_exitosa',
            'ecommerce': {
                'transaction_id': '<?php echo esc_js($order_id); ?>',
                'currency': '<?php echo esc_js($currency); ?>',
                'value': <?php echo esc_js($total); ?>,
                'items': <?php echo json_encode($items); ?>
            }
        });
        // Meta Pixel Fallback
        if ( typeof fbq !== 'undefined' ) {
            fbq('track', 'Purchase', {
                value: <?php echo esc_js($total); ?>,
                currency: 'COP',
                content_type: 'product',
                content_ids: <?php echo json_encode( wp_list_pluck($items, 'item_id') ); ?>
            });
        }
        </script>
        <?php
    }

    public function fb_domain_verification() {
        if ( $this->is_local_env() ) return;
        ?>
        <meta name="facebook-domain-verification" content="8z17ny54fvdte6y0uep5fkbxcnbww1" />
        <?php
    }

    public function get_clean_variation_string( $product, $variation_attributes = null ) {
        if ( ! $product || ! $product->is_type('variation') ) return '';
        $attributes = $variation_attributes ? $variation_attributes : $product->get_variation_attributes();
        $clean = [];
        foreach ( $attributes as $key => $value ) {
            if ( ! $value ) continue;
            $taxonomy = str_replace( 'attribute_', '', $key );
            $term = get_term_by( 'slug', $value, $taxonomy );
            $clean[] = $term ? $term->name : $value;
        }
        return implode( ' / ', $clean );
    }
}
