<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Woocommerce {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->maybe_schedule_daily_briefing();
    }

    private function init_hooks() {
        add_shortcode( 'nh_price_filter', [ $this, 'price_filter_shortcode' ] );
        add_shortcode( 'addi_widget', [ $this, 'addi_widget_shortcode' ] );
        add_action( 'pre_get_posts', [ $this, 'apply_price_filter_to_all_queries' ], 99 );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_conflicting_styles' ], 999 );
        add_filter( 'njt_whatsapp_hide_widget', [ $this, 'maybe_hide_whatsapp_widget' ], 10, 5 );
        add_filter( 'woocommerce_locate_template', [ $this, 'locate_quantity_input_template' ], 10, 3 );

        // Hooks de invalidación de transients al modificar productos
        add_action( 'woocommerce_update_product', [ $this, 'invalidate_price_transients' ] );
        add_action( 'woocommerce_new_product', [ $this, 'invalidate_price_transients' ] );
        add_action( 'woocommerce_trash_product', [ $this, 'invalidate_price_transients' ] );
        
        // Hook general para pedidos test
        add_filter( 'woocommerce_email_recipient_new_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );
        add_filter( 'woocommerce_email_recipient_customer_processing_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );
        add_filter( 'woocommerce_email_recipient_customer_completed_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );
        add_filter( 'woocommerce_coupon_is_valid', [ $this, 'restrict_test_coupons' ], 10, 3 );

        // Hook de sanitización de eventos de PixelYourSite (valor numérico y moneda ISO para GA4 / Meta)
        add_filter( 'pys_event_data', [ $this, 'sanitize_pys_event_data' ], 20, 3 );

        // AJAX endpoints para el widget NH Cart
        add_action( 'wp_ajax_nh_update_cart_item', [ $this, 'ajax_update_cart_item' ] );
        add_action( 'wp_ajax_nopriv_nh_update_cart_item', [ $this, 'ajax_update_cart_item' ] );
        add_action( 'wp_ajax_nh_remove_cart_item', [ $this, 'ajax_remove_cart_item' ] );
        add_action( 'wp_ajax_nopriv_nh_remove_cart_item', [ $this, 'ajax_remove_cart_item' ] );
        add_action( 'wp_ajax_nh_clear_cart', [ $this, 'ajax_clear_cart' ] );
        add_action( 'wp_ajax_nopriv_nh_clear_cart', [ $this, 'ajax_clear_cart' ] );
        add_action( 'wp_ajax_nh_apply_coupon', [ $this, 'ajax_apply_coupon' ] );
        add_action( 'wp_ajax_nopriv_nh_apply_coupon', [ $this, 'ajax_apply_coupon' ] );
        add_action( 'wp_ajax_nh_remove_coupon', [ $this, 'ajax_remove_coupon' ] );
        add_action( 'wp_ajax_nopriv_nh_remove_coupon', [ $this, 'ajax_remove_coupon' ] );

        // AJAX endpoint para NH Side Cart (widget independiente de Elementor Pro)
        add_action( 'wp_ajax_nh_side_cart_get_items', [ $this, 'ajax_side_cart_get_items' ] );
        add_action( 'wp_ajax_nopriv_nh_side_cart_get_items', [ $this, 'ajax_side_cart_get_items' ] );

        // AJAX endpoint para Buy Now (Compra Rápida)
        add_action( 'wp_ajax_nh_buy_now', [ $this, 'ajax_buy_now' ] );
        add_action( 'wp_ajax_nopriv_nh_buy_now', [ $this, 'ajax_buy_now' ] );

        // AJAX endpoint para Add to Cart (reemplaza wc-add-to-cart.js en Elementor)
        add_action( 'wp_ajax_nh_add_to_cart', [ $this, 'ajax_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_nh_add_to_cart', [ $this, 'ajax_add_to_cart' ] );

        // AJAX endpoint para refrescar nonces (páginas cacheadas por WP Rocket)
        add_action( 'wp_ajax_nh_get_cart_nonce', [ $this, 'ajax_get_cart_nonce' ] );
        add_action( 'wp_ajax_nopriv_nh_get_cart_nonce', [ $this, 'ajax_get_cart_nonce' ] );

        // REST API: Order status endpoint para Thank-You page polling
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // Recuperación agnóstica de pedidos fallidos (P1 - Venta en Riesgo)
        add_action( 'woocommerce_order_status_failed', [ $this, 'schedule_failed_order_recovery' ], 10, 1 );
        add_action( 'nh_check_and_notify_failed_order', [ $this, 'process_failed_order_alert' ], 10, 1 );

        // Telemetría de excepciones críticas de servidor en Checkout (P0)
        add_action( 'woocommerce_checkout_order_exception', [ $this, 'notify_checkout_exception' ], 10, 2 );

        // Recuperación de carritos abandonados (CartFlows Abandonment Recovery)
        add_action( 'wcf_ca_process_abandoned_order', [ $this, 'notify_abandoned_cart' ], 10, 1 );

        // Recordatorio de pago por transferencia bancaria (BACS) a las 4 horas
        add_action( 'woocommerce_order_status_on-hold', [ $this, 'schedule_bacs_pending_reminder' ], 10, 1 );
        add_action( 'nh_check_bacs_pending_order', [ $this, 'process_bacs_pending_reminder' ], 10, 1 );

        // Monitoreo de pedidos en taller sin despachar (>48 horas)
        add_action( 'woocommerce_order_status_processing', [ $this, 'schedule_delayed_processing_alert' ], 10, 1 );
        add_action( 'nh_check_delayed_processing_order', [ $this, 'process_delayed_processing_alert' ], 10, 1 );

        // Notificaciones operativas a Telegram (Venta confirmada, Inventario crítico y Reporte nocturno)
        add_action( 'woocommerce_order_status_processing', [ $this, 'notify_new_confirmed_sale' ], 10, 1 );
        add_action( 'woocommerce_order_status_completed', [ $this, 'notify_new_confirmed_sale' ], 10, 1 );
        add_action( 'woocommerce_no_stock', [ $this, 'notify_no_stock' ], 10, 1 );
        add_action( 'init', [ $this, 'maybe_schedule_daily_briefing' ] );
        add_action( 'nh_daily_nightly_recap', [ $this, 'send_daily_nightly_recap' ] );
        add_action( 'nh_daily_morning_briefing', [ $this, 'send_daily_nightly_recap' ] );

        // Optimización CRO y ergonomía móvil para Checkout (Fase 1)
        add_filter( 'woocommerce_checkout_fields', [ $this, 'optimize_checkout_fields_cro' ], 9999 );
        add_filter( 'woocommerce_default_address_fields', [ $this, 'optimize_default_address_fields' ], 9999 );
        add_filter( 'woocommerce_checkout_posted_data', [ $this, 'sanitize_checkout_posted_data' ], 9999 );
    }

    /**
     * AJAX: Devuelve nonces frescos para el carrito.
     * Las páginas cacheadas (WP Rocket) sirven un nonce inline que caduca
     * (~12h), rompiendo silenciosamente el add-to-cart AJAX. admin-ajax.php
     * nunca se cachea, así que refrescamos el nonce justo antes del submit.
     */
    public function ajax_get_cart_nonce() {
        wp_send_json_success( [
            'cart_nonce'    => wp_create_nonce( 'nh_cart_nonce' ),
            'side_cart_nonce' => wp_create_nonce( 'nh_side_cart_nonce' ),
        ] );
    }

    /**
     * AJAX: Devuelve los items del carrito para el NH Side Cart Widget.
     * Nonce: nh_side_cart_nonce
     */
    public function ajax_side_cart_get_items() {
        check_ajax_referer( 'nh_side_cart_nonce', 'nonce' );

        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => 'Cart not available.' ] );
            return;
        }

        WC()->cart->calculate_totals();

        $items = [];

        foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
            /** @var WC_Product $product */
            $product = $cart_item['data'];
            if ( ! $product || ! $product->exists() ) {
                continue;
            }

            // Imagen
            $img_id  = $product->get_image_id();
            $img_src = $img_id
                ? wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' )
                : wc_placeholder_img_src( 'woocommerce_thumbnail' );

            // Nombre sin variaciones (las pills se encargan de mostrarlas)
            $name = $product->get_title();
            $variations = [];
            if ( ! empty( $cart_item['variation'] ) ) {
                foreach ( $cart_item['variation'] as $attr => $val ) {
                    if ( $val ) {
                        $variations[] = [
                            'label' => wc_attribute_label( str_replace( 'attribute_', '', $attr ) ),
                            'value' => ucfirst( $val ),
                        ];
                    }
                }
            }

            $line_total = (float) $cart_item['line_total'];
            $qty        = (int) $cart_item['quantity'];
            $unit_price = $qty > 0 ? $line_total / $qty : 0;

            $items[] = [
                'key'             => $key,
                'product_id'      => $product->get_id(),
                'name'            => $name,
                'variations'      => $variations,
                'url'             => $product->get_permalink(),
                'image'           => $img_src,
                'quantity'        => $qty,
                'unit_price'      => $unit_price,
                'line_total'      => $line_total,
            ];
        }

        wp_send_json_success( [
            'items'           => $items,
            'subtotal'        => WC()->cart->get_subtotal(),
            'subtotal_html'   => wp_strip_all_tags( WC()->cart->get_cart_subtotal() ),
            'count'           => WC()->cart->get_cart_contents_count(),
        ] );
    }


    public function register_assets() {
        // Phosphor Icons (local, light weight)
        wp_enqueue_style(
            'phosphor-icons',
            NH_CORE_URL . 'assets/phosphor/phosphor-light.css',
            [],
            '2.1.1'
        );

        wp_register_style(
            'nh-price-filter',
            NH_CORE_URL . 'assets/css/nh-price-filter.css',
            [],
            '1.0.0'
        );
        wp_register_script(
            'nh-price-filter',
            NH_CORE_URL . 'assets/js/nh-price-filter.js',
            [],
            '1.0.0',
            true
        );

        // Enqueue unified quantity selector (nh-qty)
        $qty_css = NH_CORE_PATH . 'assets/css/nh-qty.css';
        $qty_js  = NH_CORE_PATH . 'assets/js/nh-qty.js';
        wp_enqueue_style(
            'nh-qty',
            NH_CORE_URL . 'assets/css/nh-qty.css',
            [],
            file_exists( $qty_css ) ? filemtime( $qty_css ) : '1.0.0'
        );
        $qty_deps = [];
        if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
            $qty_deps[] = 'wc-cart-fragments';
        }
        wp_enqueue_script(
            'nh-qty',
            NH_CORE_URL . 'assets/js/nh-qty.js',
            $qty_deps,
            file_exists( $qty_js ) ? filemtime( $qty_js ) : '1.0.0',
            true
        );
        wp_localize_script( 'nh-qty', 'nh_cart_params', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'nh_cart_nonce' ),
            'currency' => get_woocommerce_currency(),
        ] );

        // Enqueue Add to Cart widget styles
        $atc_css = NH_CORE_PATH . 'assets/css/nh-add-to-cart.css';
        wp_enqueue_style(
            'nh-add-to-cart',
            NH_CORE_URL . 'assets/css/nh-add-to-cart.css',
            [ 'nh-qty' ],
            file_exists( $atc_css ) ? filemtime( $atc_css ) : '1.0.0'
        );

        // Enqueue Buy Now handler (solo en páginas de producto)
        if ( is_product() || is_singular( 'product' ) ) {
            $atc_js = NH_CORE_PATH . 'assets/js/nh-add-to-cart.js';
            if ( file_exists( $atc_js ) ) {
                wp_enqueue_script(
                    'nh-add-to-cart',
                    NH_CORE_URL . 'assets/js/nh-add-to-cart.js',
                    [ 'jquery', 'nh-qty', 'wc-add-to-cart-variation' ],
                    filemtime( $atc_js ),
                    true
                );
            }
        }
    }

    /**
     * Dequeue WooCommerce core CSS on cart/checkout pages.
     * Our nh-woocommerce.css replaces these entirely.
     * Priority 999 to run after all other enqueues.
     */
    public function dequeue_conflicting_styles() {
        if ( ! is_cart() && ! is_checkout() && ! is_wc_endpoint_url() ) {
            return;
        }

        // WooCommerce core styles — our BEM CSS replaces them
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );

        // Variation Swatches plugin — conflicts with our product image sizing
        wp_dequeue_style( 'wc-swatches-style' );
        wp_dequeue_style( 'wc-swatches-front' );

        // NinjaTeam WhatsApp for WordPress — suprimir scripts y estilos en checkout/cart/order-received
        wp_dequeue_style( 'nta-css-popup' );
        wp_dequeue_script( 'nta-js-global' );
        wp_dequeue_script( 'nta-js-popup' );
        wp_dequeue_script( 'nta-wa-libs' );
    }

    /**
     * Oculta el widget flotante de WhatsApp en checkout, carrito y páginas de confirmación de pedido.
     * Hook filter: njt_whatsapp_hide_widget
     *
     * @param bool $hide
     * @return bool
     */
    public function maybe_hide_whatsapp_widget( $hide ) {
        if ( is_cart() || is_checkout() || is_wc_endpoint_url() ) {
            return true;
        }
        return $hide;
    }


    public function locate_quantity_input_template( $template, $template_name, $template_path ) {
        if ( 'global/quantity-input.php' === $template_name ) {
            $plugin_template = NH_CORE_PATH . 'templates/quantity-input.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        return $template;
    }


    public function price_filter_shortcode() {
        wp_enqueue_style( 'nh-price-filter' );
        wp_enqueue_script( 'nh-price-filter' );

        ob_start();
        ?>
        <div class="nh-price-filter-container">
            <h4 class="nh-filter-title">Filtrar por Precio</h4>
            
            <div class="nh-price-inputs">
                <div class="nh-price-field">
                    <span>Mín ($)</span>
                    <input type="number" id="nh-min-price" placeholder="0" min="0">
                </div>
                <div class="nh-price-separator">—</div>
                <div class="nh-price-field">
                    <span>Máx ($)</span>
                    <input type="number" id="nh-max-price" placeholder="Max" min="0">
                </div>
            </div>
            
            <div class="nh-filter-actions">
                <button type="button" id="nh-submit-price-filter" class="nh-filter-btn">Filtrar</button>
                <button type="button" id="nh-clear-price-filter" class="nh-filter-clear-btn" style="display: none;">Limpiar</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function addi_widget_shortcode() {
        $debug = current_user_can('manage_options');

        if ( ! function_exists( 'addi_render_widget' ) ) {
            return $debug ? '<!-- ADDI DEBUG: addi_render_widget() no existe, el plugin no está activo -->' : '';
        }

        global $product;

        if ( ! $product instanceof WC_Product ) {
            $queried = get_queried_object();
            if ( $queried instanceof WP_Post && $queried->post_type === 'product' ) {
                $product = wc_get_product( $queried->ID );
            } elseif ( is_singular( 'product' ) ) {
                $product = wc_get_product( get_the_ID() );
            }
        }

        if ( ! $product instanceof WC_Product ) {
            return $debug ? '<!-- ADDI DEBUG: no se pudo resolver $product en este contexto -->' : '';
        }

        if ( ! function_exists('WC') || ! WC()->payment_gateways ) {
            return $debug ? '<!-- ADDI DEBUG: WC()->payment_gateways no disponible todavía -->' : '';
        }

        $gateways = WC()->payment_gateways->payment_gateways();
        $addi_gateway = isset($gateways['addi']) ? $gateways['addi'] : null;

        if ( ! $addi_gateway ) {
            return $debug ? '<!-- ADDI DEBUG: gateway "addi" no encontrado -->' : '';
        }

        if ( $addi_gateway->get_option('widget_enabled') !== 'yes' ) {
            return $debug ? '<!-- ADDI DEBUG: widget_enabled != yes en Ajustes > Addi -->' : '';
        }

        $position = $addi_gateway->getConfWidgetPosition();

        ob_start();
        addi_render_widget( $position );
        $output = ob_get_clean();

        if ( '' === trim( $output ) && $debug ) {
            $output = '<!-- ADDI DEBUG: addi_render_widget() se ejecutó sin errores pero no produjo salida -->';
        }

        return $output;
    }

    public function apply_price_filter_to_all_queries( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $post_type = $query->get( 'post_type' );
        $is_product_query = ( $post_type === 'product' || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) );

        if ( $is_product_query ) {
            $min = null;
            $max = null;
            $orderby = null;

            if ( isset( $_GET['min_price'] ) ) {
                $min = floatval( $_GET['min_price'] );
            }
            if ( isset( $_GET['max_price'] ) ) {
                $max = floatval( $_GET['max_price'] );
            }
            if ( isset( $_GET['orderby'] ) ) {
                $orderby = sanitize_text_field( $_GET['orderby'] );
            }

            if ( ( null === $min || null === $max || null === $orderby ) && get_query_var( 'pagination_base_url' ) ) {
                $pagination_url = get_query_var( 'pagination_base_url' );
                $parsed_url = wp_parse_url( $pagination_url );
                if ( isset( $parsed_url['query'] ) ) {
                    wp_parse_str( $parsed_url['query'], $query_params );
                    if ( null === $min && isset( $query_params['min_price'] ) ) {
                        $min = floatval( $query_params['min_price'] );
                    }
                    if ( null === $max && isset( $query_params['max_price'] ) ) {
                        $max = floatval( $query_params['max_price'] );
                    }
                    if ( null === $orderby && isset( $query_params['orderby'] ) ) {
                        $orderby = sanitize_text_field( $query_params['orderby'] );
                    }
                }
            }

            if ( $orderby ) {
                switch ( $orderby ) {
                    case 'price':
                        $query->set( 'meta_key', '_price' );
                        $query->set( 'orderby', 'meta_value_num' );
                        $query->set( 'order', 'ASC' );
                        break;
                    case 'price-desc':
                        $query->set( 'meta_key', '_price' );
                        $query->set( 'orderby', 'meta_value_num' );
                        $query->set( 'order', 'DESC' );
                        break;
                    case 'date':
                        $query->set( 'orderby', 'date' );
                        $query->set( 'order', 'DESC' );
                        break;
                    case 'popularity':
                        $query->set( 'meta_key', 'total_sales' );
                        $query->set( 'orderby', 'meta_value_num' );
                        $query->set( 'order', 'DESC' );
                        break;
                    case 'rating':
                        $query->set( 'meta_key', '_wc_average_rating' );
                        $query->set( 'orderby', 'meta_value_num' );
                        $query->set( 'order', 'DESC' );
                        break;
                    case 'menu_order':
                    default:
                        $query->set( 'orderby', 'menu_order title' );
                        $query->set( 'order', 'ASC' );
                        break;
                }
            }

            if ( null !== $min || null !== $max ) {
                $min_val = ( null !== $min ) ? $min : 0;
                $max_val = ( null !== $max ) ? $max : 999999999;

                // Generar llave de transient única indexada por hash MD5 del rango
                $transient_key = 'nh_pf_' . md5( $min_val . '_' . $max_val );
                $matched_ids = get_transient( $transient_key );

                if ( false === $matched_ids ) {
                    global $wpdb;
                    $matched_ids = $wpdb->get_col( $wpdb->prepare(
                        "SELECT product_id FROM {$wpdb->prefix}wc_product_meta_lookup WHERE min_price >= %f AND max_price <= %f",
                        $min_val,
                        $max_val
                    ) );
                    
                    // Si el resultado está vacío, guardar array vacío en vez de false para evitar falsos fallos en caché
                    if ( empty( $matched_ids ) ) {
                        $matched_ids = array( 0 );
                    }

                    // Cache por 1 hora
                    set_transient( $transient_key, $matched_ids, HOUR_IN_SECONDS );

                    // Track transient keys in an option to clean them up properly later (Redis/Memcached friendly)
                    $tracked_keys = get_option( 'nh_pf_keys', [] );
                    if ( ! is_array( $tracked_keys ) ) {
                        $tracked_keys = [];
                    }
                    if ( ! in_array( $transient_key, $tracked_keys, true ) ) {
                        $tracked_keys[] = $transient_key;
                        update_option( 'nh_pf_keys', $tracked_keys, false );
                    }
                }

                if ( empty( $matched_ids ) || ( count($matched_ids) === 1 && $matched_ids[0] === 0 ) ) {
                    $query->set( 'post__in', array( 0 ) );
                } else {
                    $existing_post_in = $query->get( 'post__in' );
                    if ( ! empty( $existing_post_in ) && is_array( $existing_post_in ) ) {
                        $query->set( 'post__in', array_intersect( $existing_post_in, $matched_ids ) );
                    } else {
                        $query->set( 'post__in', $matched_ids );
                    }
                }
            }

            // Aplicar filtros de taxonomía dinámicamente desde URL o REST API
            $taxonomies = get_object_taxonomies( 'product' );
            $tax_query = array( 'relation' => 'AND' );
            $has_tax_filter = false;

            foreach ( $taxonomies as $tax ) {
                $val = null;
                if ( isset( $_GET[ $tax ] ) ) {
                    $val = sanitize_text_field( $_GET[ $tax ] );
                } elseif ( get_query_var( 'pagination_base_url' ) ) {
                    $pagination_url = get_query_var( 'pagination_base_url' );
                    $parsed_url = wp_parse_url( $pagination_url );
                    if ( isset( $parsed_url['query'] ) ) {
                        wp_parse_str( $parsed_url['query'], $query_params );
                        if ( isset( $query_params[ $tax ] ) ) {
                            $val = sanitize_text_field( $query_params[ $tax ] );
                        }
                    }
                }

                if ( $val ) {
                    $tax_query[] = array(
                        'taxonomy' => $tax,
                        'field'    => 'slug',
                        'terms'    => explode( ',', $val ),
                        'operator' => 'IN',
                    );
                    $has_tax_filter = true;
                }
            }

            if ( $has_tax_filter ) {
                $existing_tax_query = $query->get( 'tax_query' );
                if ( ! empty( $existing_tax_query ) && is_array( $existing_tax_query ) ) {
                    $query->set( 'tax_query', array_merge( $existing_tax_query, $tax_query ) );
                } else {
                    $query->set( 'tax_query', $tax_query );
                }
            }
        }
    }

    public function invalidate_price_transients() {
        $tracked_keys = get_option( 'nh_pf_keys', [] );
        if ( is_array( $tracked_keys ) && ! empty( $tracked_keys ) ) {
            foreach ( $tracked_keys as $key ) {
                delete_transient( $key );
            }
            update_option( 'nh_pf_keys', [], false );
        }

        global $wpdb;
        // Fallback para limpiar remanentes directamente de la base de datos
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nh_pf_%' OR option_name LIKE '_transient_timeout_nh_pf_%'" );
    }

    public function disable_email_for_test_coupon( $recipient, $order ) {
        if ( is_a( $order, 'WC_Order' ) && in_array( 'freetesting', $order->get_coupon_codes() ) ) {
            error_log( sprintf(
                '[NH_CORE_TEST_MODE] Email de notificación suprimido para order #%d (cupón freetesting).',
                $order->get_id()
            ) );
            return '';
        }
        return $recipient;
    }

    /**
     * Restringe los cupones de testing (freetesting / freetesting-noemail) fuera de
     * entornos locales (DDEV/local no aplica restricción — no requieren nada).
     *
     * En producción se exigen DOS condiciones independientes (defensa en profundidad):
     * 1) Token real (NH_TESTING_BYPASS_TOKEN, definido en wp-config.php, nunca en el
     *    repo) enviado en el header X-NH-Testing, comparado con hash_equals().
     * 2) Ventana temporal armada manualmente vía `wp nh-core enable-test-mode`
     *    (nh_core_test_mode_is_active(), inc/class-nh-core-cli.php) — el bypass no
     *    funciona 24/7 aunque el token se filtrara.
     */
    public function restrict_test_coupons( $valid, $coupon, $discount ) {
        if ( ! $valid || ! is_a( $coupon, 'WC_Coupon' ) ) {
            return $valid;
        }
        $code = strtolower( $coupon->get_code() );
        if ( ! in_array( $code, [ 'freetesting', 'freetesting-noemail' ], true ) ) {
            return $valid;
        }
        $is_local = ( defined( 'WP_ENVIRONMENT_TYPE' ) && in_array( WP_ENVIRONMENT_TYPE, [ 'local', 'development' ], true ) )
            || ( isset( $_SERVER['HTTP_HOST'] ) && strpos( $_SERVER['HTTP_HOST'], '.ddev.site' ) !== false );
        if ( $is_local ) {
            return $valid;
        }

        // 1. Permitir a administradores de la tienda logueados (para pruebas manuales en navegador)
        if ( current_user_can( 'manage_woocommerce' ) || current_user_can( 'administrator' ) ) {
            return $valid;
        }

        // 2. Permitir si la ventana temporal de testing fue armada vía CLI (wp nh-core enable-test-mode)
        $window_ok = function_exists( 'nh_core_test_mode_is_active' ) && nh_core_test_mode_is_active();
        if ( $window_ok ) {
            return true;
        }

        if ( ! defined( 'NH_TESTING_BYPASS_TOKEN' ) || NH_TESTING_BYPASS_TOKEN === '' ) {
            return false;
        }

        $header    = isset( $_SERVER['HTTP_X_NH_TESTING'] ) ? (string) $_SERVER['HTTP_X_NH_TESTING'] : '';
        $token_ok  = $header !== '' && hash_equals( NH_TESTING_BYPASS_TOKEN, $header );

        if ( $token_ok ) {
            error_log( sprintf(
                '[NH_CORE_TEST_MODE] Cupón "%s" aplicado fuera de local (token de cabecera) — IP=%s',
                $code,
                isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown'
            ) );
            return true;
        }

        if ( $header !== '' ) {
            error_log( sprintf(
                '[NH_CORE_TEST_MODE] Intento de bypass rechazado para cupón "%s" — token_ok=%s window_ok=%s IP=%s',
                $code,
                $token_ok ? 'true' : 'false',
                $window_ok ? 'true' : 'false',
                isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown'
            ) );
        }

        return false;
    }


    public function ajax_update_cart_item() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        
        $key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
        $qty = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
        
        if ( $key && function_exists( 'WC' ) && WC()->cart ) {
            WC()->cart->set_quantity( $key, $qty );
            WC()->cart->calculate_totals();

            $cart_item = WC()->cart->get_cart_item( $key );
            $subtotal = '';
            if ( $cart_item && isset( $cart_item['data'] ) ) {
                $subtotal = WC()->cart->get_product_subtotal( $cart_item['data'], $cart_item['quantity'] );
            }

            ob_start();
            if ( function_exists( 'woocommerce_cart_totals' ) ) {
                woocommerce_cart_totals();
            }
            $totals_html = ob_get_clean();

            wp_send_json_success( [
                'subtotal' => $subtotal,
                'totals_html' => $totals_html,
            ] );
        }
        
        wp_send_json_error( [ 'message' => __( 'No se pudo actualizar la cantidad.', 'nh-core' ) ] );
    }

    public function ajax_remove_cart_item() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        
        $key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
        if ( $key && function_exists( 'WC' ) && WC()->cart ) {
            WC()->cart->remove_cart_item( $key );
            wp_send_json_success();
        }
        
        wp_send_json_error( [ 'message' => __( 'No se pudo eliminar el producto.', 'nh-core' ) ] );
    }

    public function ajax_clear_cart() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        if ( function_exists( 'WC' ) && WC()->cart ) {
            WC()->cart->empty_cart();
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function ajax_apply_coupon() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        
        $code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        if ( $code && function_exists( 'WC' ) && WC()->cart ) {
            $result = WC()->cart->apply_coupon( $code );
            if ( $result ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( [ 'message' => __( 'Cupón inválido o no aplicable.', 'nh-core' ) ] );
            }
        }
        
        wp_send_json_error( [ 'message' => __( 'Ingresa un código válido.', 'nh-core' ) ] );
    }

    public function ajax_remove_coupon() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        
        $code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        if ( $code && function_exists( 'WC' ) && WC()->cart ) {
            WC()->cart->remove_coupon( $code );
            wp_send_json_success();
        }
        
        wp_send_json_error();
    }

    public function ajax_buy_now() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );

        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $variations   = isset( $_POST['variations'] ) ? (array) $_POST['variations'] : [];

        if ( ! $product_id || ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => __( 'Producto no válido.', 'nh-core' ) ] );
        }

        $product = wc_get_product( $variation_id ? $variation_id : $product_id );
        if ( ! $product || ! $product->is_purchasable() ) {
            wp_send_json_error( [ 'message' => __( 'Producto no disponible para compra.', 'nh-core' ) ] );
        }

        WC()->cart->empty_cart();

        $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );

        if ( ! $cart_item_key ) {
            wp_send_json_error( [ 'message' => __( 'No se pudo añadir el producto.', 'nh-core' ) ] );
        }

        WC()->cart->calculate_totals();

        wp_send_json_success( [
            'checkout_url' => wc_get_checkout_url(),
            'cart_item_key' => $cart_item_key,
        ] );
    }

    /**
     * AJAX: Add to Cart — reemplaza wc-add-to-cart.js nativo de WC.
     * Elementor rompe los event handlers de WC al re-renderizar widgets,
     * así que interceptamos el form submit y lo manejamos vía AJAX propio.
     */
    public function ajax_add_to_cart() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );

        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => __( 'WooCommerce no disponible.', 'nh-core' ) ] );
        }

        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $variations   = isset( $_POST['variations'] ) ? (array) $_POST['variations'] : [];

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => __( 'Producto no válido.', 'nh-core' ) ] );
        }

        $product = wc_get_product( $variation_id ? $variation_id : $product_id );
        if ( ! $product || ! $product->is_purchasable() ) {
            wp_send_json_error( [ 'message' => __( 'Producto no disponible para compra.', 'nh-core' ) ] );
        }

        if ( $product->managing_stock() && ! $product->backorders_allowed() && ! $product->is_in_stock() ) {
            wp_send_json_error( [ 'message' => __( 'Agotado.', 'nh-core' ) ] );
        }

        $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );

        if ( ! $cart_item_key ) {
            wp_send_json_error( [ 'message' => __( 'No se pudo añadir al carrito.', 'nh-core' ) ] );
        }

        WC()->cart->calculate_totals();

        // Fragmentos para que wc-cart-fragments actualice mini cart / badges
        $fragments = [];
        if ( did_action( 'woocommerce_after_cart_table' ) ) {
            // En cart page, devolver fragmentos del widget
        }

        wp_send_json_success( [
            'fragments'  => $fragments,
            'cart_hash'  => WC()->cart->get_cart_hash(),
        ] );
    }

    /**
     * Registra rutas de la API REST para nh-core.
     */
    public function register_rest_routes() {
        register_rest_route( 'nh/v1', '/order-status/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_order_status_api' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'id'  => [ 'validate_callback' => function( $param ) { return is_numeric( $param ); } ],
                'key' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );
    }

    /**
     * Endpoint REST: Obtiene el estado del pedido verificando la clave de orden (order_key).
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_order_status_api( WP_REST_Request $request ) {
        $order_id  = absint( $request->get_param( 'id' ) );
        $order_key = sanitize_text_field( $request->get_param( 'key' ) );
        $order     = wc_get_order( $order_id );

        if ( ! $order || ! hash_equals( (string) $order->get_order_key(), (string) $order_key ) ) {
            return new WP_Error( 'rest_forbidden', 'Acceso no autorizado al pedido.', [ 'status' => 403 ] );
        }

        $status  = $order->get_status();
        $is_paid = $order->is_paid() || in_array( $status, [ 'processing', 'completed' ], true );

        $response = rest_ensure_response( [
            'order_id'    => $order_id,
            'status'      => $status,
            'is_paid'     => $is_paid,
            'badge_text'  => $is_paid ? 'Pedido Confirmado' : ( in_array( $status, [ 'pending', 'on-hold' ], true ) ? 'Validación Bancaria en Curso' : 'Pago no completado' ),
            'badge_class' => $is_paid ? 'nh-order-badge--confirmed' : ( in_array( $status, [ 'pending', 'on-hold' ], true ) ? 'nh-order-badge--pending' : 'nh-order-badge--failed' ),
        ] );
        $response->header( 'Cache-Control', 'no-cache, must-revalidate, max-age=0' );

        return $response;
    }

    /**
     * Sanitiza los parámetros de eventos emitidos por PixelYourSite para asegurar compatibilidad
     * estricta con el esquema de Google Analytics 4 (GA4) y Meta CAPI.
     *
     * 1. 'value': Debe ser numérico (float/int), no string, para que GA4 no compute $0.00 en ingresos.
     * 2. 'currency': Código ISO 4217 en mayúsculas (ej. 'COP').
     * 3. 'tax' y 'shipping': Cast numérico float.
     * 4. 'items': Cada item['price'] se convierte a float y item['quantity'] a int.
     *
     * @param array  $data    Array de datos del evento (contiene 'params').
     * @param string $slug    Categoría/slug del evento (ej. 'woo_purchase').
     * @param array  $context Contexto con 'pixel' (ej. 'google_analytics', 'facebook') y 'event_id'.
     * @return array Datos sanitizados del evento.
     */
    public function sanitize_pys_event_data( $data, $slug = '', $context = [] ) {
        if ( empty( $data['params'] ) || ! is_array( $data['params'] ) ) {
            return $data;
        }

        // 1. Sanitizar 'value' principal
        if ( isset( $data['params']['value'] ) ) {
            $val = $data['params']['value'];
            if ( is_numeric( $val ) ) {
                $data['params']['value'] = (float) $val;
            } elseif ( is_string( $val ) ) {
                $cleaned = preg_replace( '/[^\d.]/', '', str_replace( ',', '.', $val ) );
                $data['params']['value'] = (float) $cleaned;
            }
        }

        // 2. Sanitizar 'currency'
        if ( empty( $data['params']['currency'] ) && function_exists( 'get_woocommerce_currency' ) ) {
            $data['params']['currency'] = get_woocommerce_currency();
        }
        if ( ! empty( $data['params']['currency'] ) ) {
            $data['params']['currency'] = strtoupper( trim( (string) $data['params']['currency'] ) );
        }

        // 3. Sanitizar 'tax' y 'shipping'
        if ( isset( $data['params']['tax'] ) && is_numeric( $data['params']['tax'] ) ) {
            $data['params']['tax'] = (float) $data['params']['tax'];
        }
        if ( isset( $data['params']['shipping'] ) && is_numeric( $data['params']['shipping'] ) ) {
            $data['params']['shipping'] = (float) $data['params']['shipping'];
        }

        // 4. Sanitizar items de e-commerce (item.price debe ser float para GA4)
        if ( ! empty( $data['params']['items'] ) && is_array( $data['params']['items'] ) ) {
            foreach ( $data['params']['items'] as &$item ) {
                if ( isset( $item['price'] ) && is_numeric( $item['price'] ) ) {
                    $item['price'] = (float) $item['price'];
                }
                if ( isset( $item['quantity'] ) && is_numeric( $item['quantity'] ) ) {
                    $item['quantity'] = (int) $item['quantity'];
                }
            }
            unset( $item );
        }

        return $data;
    }

    /**
     * Programa la verificación de recuperación de pago fallido tras 12 minutos.
     * Hook: woocommerce_order_status_failed
     *
     * @param int $order_id ID del pedido en estado failed.
     */
    public function schedule_failed_order_recovery( $order_id ) {
        $order_id = absint( $order_id );
        if ( ! $order_id ) {
            return;
        }

        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            error_log( sprintf( '[NH Recuperación] Action Scheduler no disponible para programar orden #%d', $order_id ) );
            return;
        }

        $args  = [ 'order_id' => $order_id ];
        $group = 'nh-recovery';

        if ( function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'nh_check_and_notify_failed_order', $args, $group ) ) {
            return;
        }

        $scheduled_time = time() + ( 12 * MINUTE_IN_SECONDS );
        as_schedule_single_action( $scheduled_time, 'nh_check_and_notify_failed_order', $args, $group );

        $order = wc_get_order( $order_id );
        if ( $order ) {
            $order->add_order_note( __( '[NH Recuperación] Tarea programada en 12 min para verificar recuperación de la clienta.', 'nh-core' ) );
        }
    }

    /**
     * Procesa la alerta de pedido fallido tras el periodo de espera (12 min).
     * Aplica chequeo anti-autorrecuperación y emite alerta a Telegram para conserjería comercial.
     * Hook: nh_check_and_notify_failed_order
     *
     * @param int|array $order_id ID de la orden o array de argumentos de Action Scheduler.
     */
    public function process_failed_order_alert( $order_id ) {
        if ( is_array( $order_id ) && isset( $order_id['order_id'] ) ) {
            $order_id = $order_id['order_id'];
        }
        $order_id = absint( $order_id );
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || 'failed' !== $order->get_status() ) {
            return;
        }

        // Chequeo anti-autorrecuperación: verificar si la clienta ya completó una orden posterior
        $email        = $order->get_billing_email();
        $customer_id  = $order->get_customer_id();
        $date_created = $order->get_date_created();
        $timestamp    = $date_created ? $date_created->getTimestamp() : time();

        if ( ! empty( $email ) ) {
            $recent_paid = wc_get_orders( [
                'billing_email' => $email,
                'status'        => [ 'processing', 'completed' ],
                'date_created'  => '>=' . ( $timestamp - 60 ),
                'exclude'       => [ $order->get_id() ],
                'limit'         => 1,
            ] );
            if ( ! empty( $recent_paid ) ) {
                $order->add_order_note( sprintf(
                    /* translators: %d: Order ID */
                    __( '[NH Recuperación] Alerta omitida: la clienta ya completó con éxito una orden posterior #%d', 'nh-core' ),
                    $recent_paid[0]->get_id()
                ) );
                return;
            }
        }

        if ( $customer_id > 0 ) {
            $recent_paid_customer = wc_get_orders( [
                'customer_id'  => $customer_id,
                'status'       => [ 'processing', 'completed' ],
                'date_created' => '>=' . ( $timestamp - 60 ),
                'exclude'      => [ $order->get_id() ],
                'limit'        => 1,
            ] );
            if ( ! empty( $recent_paid_customer ) ) {
                $order->add_order_note( sprintf(
                    /* translators: %d: Order ID */
                    __( '[NH Recuperación] Alerta omitida: la clienta ya completó con éxito una orden posterior #%d', 'nh-core' ),
                    $recent_paid_customer[0]->get_id()
                ) );
                return;
            }
        }

        // Extracción de datos dinámicos de pasarela y orden
        $gateway_name = $order->get_payment_method_title();
        if ( empty( $gateway_name ) ) {
            $gateway_name = 'Pasarela de Pago';
        }

        $order_total = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ), ENT_QUOTES, 'UTF-8' );
        $full_name   = trim( $order->get_formatted_billing_full_name() );
        $first_name  = trim( (string) $order->get_billing_first_name() );
        if ( empty( $full_name ) ) {
            $full_name = 'Clienta';
        }
        if ( empty( $first_name ) ) {
            $first_name = $full_name;
        }

        $customer_email = $order->get_billing_email();
        $customer_phone = $order->get_billing_phone();
        $customer_city  = $order->get_billing_city();
        $customer_state = $order->get_billing_state();

        // Resumen de prendas y variaciones
        $items_summary = [];
        $item_names    = [];
        foreach ( $order->get_items() as $item ) {
            $item_name = $item->get_name();
            $item_qty  = $item->get_quantity();
            $attrs     = [];
            if ( is_callable( [ $item, 'get_meta_data' ] ) ) {
                foreach ( $item->get_meta_data() as $meta ) {
                    $key = (string) $meta->key;
                    if ( str_starts_with( $key, 'pa_' ) || in_array( strtolower( $key ), [ 'talla', 'color', 'size' ], true ) ) {
                        $label   = wc_attribute_label( $key );
                        $attrs[] = $label . ': ' . $meta->value;
                    }
                }
            }
            $line = '• ' . $item_qty . 'x ' . esc_html( $item_name );
            if ( ! empty( $attrs ) ) {
                $line .= ' (' . esc_html( implode( ', ', $attrs ) ) . ')';
            }
            $items_summary[] = $line;
            $item_names[]    = $item_name;
        }
        $items_text  = ! empty( $items_summary ) ? implode( "\n", $items_summary ) : '• Sin detalles de productos';
        $prendas_str = ! empty( $item_names ) ? implode( ', ', $item_names ) : 'tus prendas';

        // Normalización de teléfono para WhatsApp (formato colombiano +57)
        $clean_phone = preg_replace( '/\D+/', '', (string) $customer_phone );
        if ( ! empty( $clean_phone ) ) {
            if ( str_starts_with( $clean_phone, '57' ) ) {
                // Ya cuenta con código de país
            } elseif ( strlen( $clean_phone ) === 10 && str_starts_with( $clean_phone, '3' ) ) {
                $clean_phone = '57' . $clean_phone;
            }
        }

        $wa_msg = sprintf(
            'Hola %s ✨ Te escribimos del taller de Norma Hana en Santa Marta. Notamos una interrupción en el pago de tu orden #%d (%s). ¿Te gustaría que te asistamos para completarlo por transferencia Bancolombia, Nequi o con un nuevo enlace de pago? Estamos a tu disposición.',
            $first_name,
            $order->get_id(),
            $prendas_str
        );

        $wa_url    = ! empty( $clean_phone ) ? 'https://wa.me/' . $clean_phone . '?text=' . rawurlencode( $wa_msg ) : '';
        $retry_url = $order->get_checkout_payment_url();
        $admin_url = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );

        $msg_lines = [];
        $msg_lines[] = '<b>Orden:</b> #' . $order->get_id() . ' (' . esc_html( $order_total ) . ')';
        $msg_lines[] = '<b>Pasarela:</b> ' . esc_html( $gateway_name );
        $msg_lines[] = '<b>Cliente:</b> ' . esc_html( $full_name );
        if ( ! empty( $customer_email ) ) {
            $msg_lines[] = '<b>Email:</b> ' . esc_html( $customer_email );
        }
        if ( ! empty( $customer_phone ) ) {
            $msg_lines[] = '<b>Teléfono:</b> ' . esc_html( $customer_phone );
        }
        if ( ! empty( $customer_city ) ) {
            $location = esc_html( $customer_city );
            if ( ! empty( $customer_state ) ) {
                $location .= ', ' . esc_html( $customer_state );
            }
            $msg_lines[] = '<b>Ciudad:</b> ' . $location;
        }
        $msg_lines[] = '';
        $msg_lines[] = '<b>Prendas en el pedido:</b>';
        $msg_lines[] = $items_text;
        $buttons = [];
        if ( ! empty( $wa_url ) ) {
            $buttons[] = [
                [ 'text' => '💬 Abrir WhatsApp con la Clienta', 'url' => $wa_url ]
            ];
        }
        $actions_row = [];
        if ( ! empty( $retry_url ) ) {
            $actions_row[] = [ 'text' => '💳 Reintentar Pago', 'url' => $retry_url ];
        }
        $actions_row[] = [ 'text' => '📋 Ver Orden en WP Admin', 'url' => $admin_url ];
        $buttons[] = $actions_row;

        $payload = [
            'channel' => 'marketing',
            'level'   => 'error',
            'title'   => '🚨 Recuperación de Venta: Pago Fallido (' . $gateway_name . ')',
            'message' => implode( "\n", $msg_lines ),
            'chat_id' => '-5244885992',
            'buttons' => $buttons,
        ];

        $sent = $this->send_telegram_notification( $payload );
        if ( $sent ) {
            $order->add_order_note( __( '[NH Recuperación] Alerta enviada a Telegram para conserjería comercial.', 'nh-core' ) );
        }
    }

    /**
     * Telemetría de excepciones de servidor durante el Checkout (P0).
     * Hook: woocommerce_checkout_order_exception
     *
     * @param WC_Order|int|null   $order     Instancia del pedido si se llegó a crear o int.
     * @param Throwable|Exception $exception Excepción capturada en checkout.
     */
    public function notify_checkout_exception( $order, $exception ) {
        $order_id = 0;
        $email    = '';

        if ( $order instanceof WC_Order ) {
            $order_id = $order->get_id();
            $email    = $order->get_billing_email();
        } elseif ( is_numeric( $order ) ) {
            $order_id = absint( $order );
            $loaded   = wc_get_order( $order_id );
            if ( $loaded ) {
                $email = $loaded->get_billing_email();
            }
        }

        if ( empty( $email ) && isset( $_POST['billing_email'] ) ) {
            $email = sanitize_email( wp_unslash( $_POST['billing_email'] ) );
        }

        $exc_message = $exception instanceof Throwable ? $exception->getMessage() : (string) $exception;
        $exc_file    = $exception instanceof Throwable ? $exception->getFile() : 'N/A';
        $exc_line    = $exception instanceof Throwable ? $exception->getLine() : 'N/A';

        $lines = [];
        $lines[] = '<b>Severidad:</b> P0 - Checkout Caído / Excepción en Servidor';
        $lines[] = '<b>Orden ID:</b> ' . ( $order_id ? '#' . $order_id : 'No generada / Fallo previo' );
        $lines[] = '<b>Cliente Email:</b> ' . esc_html( ! empty( $email ) ? $email : 'N/A' );
        $lines[] = '<b>Excepción:</b> <code>' . esc_html( $exc_message ) . '</code>';
        $lines[] = '<b>Archivo:</b> ' . esc_html( $exc_file ) . ':' . $exc_line;
        $lines[] = '<b>Hora:</b> ' . current_time( 'mysql' );

        $payload = [
            'channel' => 'system',
            'level'   => 'error',
            'title'   => '🚨 [P0] Error Crítico de Servidor en Checkout',
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
        ];

        $this->send_telegram_notification( $payload );
    }

    /**
     * Notificación de nueva venta confirmada hacia Telegram.
     * Hooks: woocommerce_order_status_processing, woocommerce_order_status_completed
     *
     * @param int|WC_Order $order_or_id Instancia o ID de la orden.
     */
    public function notify_new_confirmed_sale( $order_or_id ) {
        $order = $order_or_id instanceof WC_Order ? $order_or_id : wc_get_order( $order_or_id );
        if ( ! $order ) {
            return;
        }

        $order_id = $order->get_id();

        // Chequeo anti-duplicación: evitar alertas dobles entre processing y completed
        if ( $order->get_meta( '_nh_telegram_sale_notified' ) ) {
            return;
        }

        // Extracción de datos
        $order_total   = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ), ENT_QUOTES, 'UTF-8' );
        $customer_name = trim( $order->get_formatted_billing_full_name() );
        if ( empty( $customer_name ) ) {
            $customer_name = 'Clienta';
        }

        $city     = $order->get_billing_city();
        $state    = $order->get_billing_state();
        $location = $city . ( $state ? ', ' . $state : '' );

        $payment_method  = $order->get_payment_method_title() ?: 'Pasarela';
        $shipping_method = $order->get_shipping_method() ?: 'Estándar';

        // Detalle de items con variaciones
        $items_summary = [];
        foreach ( $order->get_items() as $item ) {
            $item_name = $item->get_name();
            $item_qty  = $item->get_quantity();
            $attrs     = [];
            if ( is_callable( [ $item, 'get_meta_data' ] ) ) {
                foreach ( $item->get_meta_data() as $meta ) {
                    $key = (string) $meta->key;
                    if ( str_starts_with( $key, 'pa_' ) || in_array( strtolower( $key ), [ 'talla', 'color', 'size' ], true ) ) {
                        $label   = wc_attribute_label( $key );
                        $attrs[] = $label . ': ' . $meta->value;
                    }
                }
            }
            $line = '• ' . $item_qty . 'x ' . esc_html( $item_name );
            if ( ! empty( $attrs ) ) {
                $line .= ' (' . esc_html( implode( ', ', $attrs ) ) . ')';
            }
            $items_summary[] = $line;
        }
        $items_text = ! empty( $items_summary ) ? implode( "\n", $items_summary ) : '• Sin detalles de productos';

        $admin_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        // Detección de entrega o recogida local en Santa Marta / Magdalena
        $shipping_city  = (string) ( $order->get_shipping_city() ?: $city );
        $shipping_state = (string) ( $order->get_shipping_state() ?: $state );
        $geo_haystack   = mb_strtolower( $shipping_city . ' ' . $shipping_state . ' ' . $city . ' ' . $state . ' ' . $shipping_method );
        $is_santa_marta = str_contains( $geo_haystack, 'santa marta' )
            || str_contains( $geo_haystack, 'magdalena' )
            || str_contains( $geo_haystack, 'recogida' )
            || str_contains( $geo_haystack, 'local' );

        $lines   = [];
        $lines[] = '<b>Pedido:</b> #' . $order_id . ' (' . esc_html( $order_total ) . ')';
        $lines[] = '<b>Cliente:</b> ' . esc_html( $customer_name );
        if ( ! empty( $location ) ) {
            $lines[] = '<b>Destino:</b> ' . esc_html( $location );
        }
        if ( $is_santa_marta ) {
            $lines[] = '📍 <b>¡Entrega Local / Recogida en Santa Marta!</b>';
        }
        $lines[] = '<b>Medio de Pago:</b> ' . esc_html( $payment_method );
        $lines[] = '<b>Envío:</b> ' . esc_html( $shipping_method );

        $coupons = $order->get_coupon_codes();
        if ( ! empty( $coupons ) ) {
            $discount_display = html_entity_decode( wp_strip_all_tags( $order->get_discount_to_display() ), ENT_QUOTES, 'UTF-8' );
            $lines[] = '🏷️ <b>Cupón Aplicado:</b> ' . esc_html( implode( ', ', $coupons ) ) . ' (Descuento: ' . esc_html( $discount_display ) . ')';
        }

        $lines[] = '';
        $lines[] = '<b>Prendas adquiridas:</b>';
        $lines[] = $items_text;
        $payload = [
            'channel' => 'marketing',
            'level'   => 'success',
            'title'   => '🎉 ¡Nueva Venta Confirmada en Norma Hana!',
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
            'buttons' => [
                [
                    [ 'text' => '📋 Ver Pedido en WP Admin', 'url' => $admin_url ]
                ]
            ],
        ];

        $sent = $this->send_telegram_notification( $payload );

        // Persistir metadato para bloquear notificaciones duplicadas
        $order->update_meta_data( '_nh_telegram_sale_notified', time() );
        $order->save();

        if ( $sent ) {
            $order->add_order_note( __( '[NH Telegram] Notificación de nueva venta confirmada enviada a Telegram.', 'nh-core' ) );
        }
    }

    /**
     * Notificación de carrito abandonado procesado por CartFlows Recovery hacia Telegram.
     * Hook: wcf_ca_process_abandoned_order
     *
     * @param object|array $checkout_details Datos del registro en wp_cartflows_ca_cart_abandonment.
     */
    public function notify_abandoned_cart( $checkout_details ) {
        if ( ! is_object( $checkout_details ) ) {
            if ( is_array( $checkout_details ) ) {
                $checkout_details = (object) $checkout_details;
            } else {
                return;
            }
        }

        // Anti-duplicación por sesión
        $session_id = ! empty( $checkout_details->session_id ) ? (string) $checkout_details->session_id : '';
        if ( ! empty( $session_id ) ) {
            $lock_key = 'nh_ca_notified_' . md5( $session_id );
            if ( get_transient( $lock_key ) ) {
                return;
            }
            set_transient( $lock_key, 1, 7 * DAY_IN_SECONDS );
        }

        $email = ! empty( $checkout_details->email ) ? sanitize_email( $checkout_details->email ) : '';

        // Formateo de total en COP
        $raw_total       = isset( $checkout_details->cart_total ) ? (float) $checkout_details->cart_total : 0.0;
        $formatted_total = html_entity_decode( wp_strip_all_tags( wc_price( $raw_total ) ), ENT_QUOTES, 'UTF-8' );
        if ( ! str_contains( $formatted_total, 'COP' ) ) {
            $formatted_total .= ' COP';
        }

        // Campos adicionales de cliente
        $other = ! empty( $checkout_details->other_fields ) ? maybe_unserialize( $checkout_details->other_fields ) : [];
        if ( ! is_array( $other ) ) {
            $other = [];
        }

        $first_name = ! empty( $other['wcf_first_name'] ) ? trim( (string) $other['wcf_first_name'] ) : 'Clienta';
        $last_name  = ! empty( $other['wcf_last_name'] ) ? trim( (string) $other['wcf_last_name'] ) : '';
        $phone      = ! empty( $other['wcf_phone_number'] ) ? trim( (string) $other['wcf_phone_number'] ) : '';
        $location   = ! empty( $other['wcf_location'] ) ? trim( (string) $other['wcf_location'] ) : '';

        // Detalle de prendas del carrito
        $cart_contents = ! empty( $checkout_details->cart_contents ) ? maybe_unserialize( $checkout_details->cart_contents ) : [];
        $items_summary = [];
        $item_names    = [];

        if ( is_array( $cart_contents ) ) {
            foreach ( $cart_contents as $cart_item ) {
                $product_id   = ! empty( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;
                $variation_id = ! empty( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : 0;
                $qty          = ! empty( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

                $product = wc_get_product( $variation_id ?: $product_id );
                $item_name = '';
                if ( $product ) {
                    $item_name = $product->get_name();
                } elseif ( ! empty( $cart_item['data'] ) && is_object( $cart_item['data'] ) && method_exists( $cart_item['data'], 'get_name' ) ) {
                    $item_name = $cart_item['data']->get_name();
                } else {
                    $item_name = __( 'Prenda', 'nh-core' );
                }

                $attrs = [];
                if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
                    foreach ( $cart_item['variation'] as $attr_key => $attr_val ) {
                        if ( ! empty( $attr_val ) ) {
                            $clean_attr = str_replace( 'attribute_', '', $attr_key );
                            $label      = wc_attribute_label( $clean_attr );
                            $attrs[]    = $label . ': ' . ucfirst( $attr_val );
                        }
                    }
                }

                $line = '• ' . $qty . 'x ' . esc_html( $item_name );
                if ( ! empty( $attrs ) ) {
                    $line .= ' (' . esc_html( implode( ', ', $attrs ) ) . ')';
                }
                $items_summary[] = $line;
                $item_names[]    = $item_name;
            }
        }

        $items_text = ! empty( $items_summary ) ? implode( "\n", $items_summary ) : '• Sin detalles de productos';
        $items_str  = ! empty( $item_names ) ? implode( ', ', array_unique( $item_names ) ) : 'tus prendas seleccionadas';

        // Normalización y enlace directo de WhatsApp
        $clean_phone = preg_replace( '/\D+/', '', (string) $phone );
        if ( ! empty( $clean_phone ) ) {
            if ( str_starts_with( $clean_phone, '57' ) ) {
                // Ya cuenta con código de país
            } elseif ( strlen( $clean_phone ) === 10 && str_starts_with( $clean_phone, '3' ) ) {
                $clean_phone = '57' . $clean_phone;
            }
        }

        $wa_msg = sprintf(
            'Hola %s ✨ Te escribimos del taller de Norma Hana en Santa Marta. Notamos que estuviste a punto de completar tu pedido de (%s) en nuestra tienda online. ¿Tuviste alguna duda con la talla, los tiempos de confección o las opciones de pago? Estamos a tu disposición para ayudarte con todo el gusto.',
            $first_name,
            $items_str
        );

        $wa_url    = ! empty( $clean_phone ) ? 'https://wa.me/' . $clean_phone . '?text=' . rawurlencode( $wa_msg ) : '';
        $admin_url = admin_url( 'admin.php?page=cartflows_ca' );

        $buttons = [];
        if ( ! empty( $wa_url ) ) {
            $buttons[] = [
                [ 'text' => '💬 Escribir por WhatsApp a ' . $first_name, 'url' => $wa_url ],
            ];
            $buttons[] = [
                [ 'text' => '📋 Ver Carrito en WP Admin', 'url' => $admin_url ],
            ];
        } else {
            $buttons[] = [
                [ 'text' => '📋 Ver Carrito en WP Admin', 'url' => $admin_url ],
            ];
        }

        $customer_display = trim( $first_name . ' ' . $last_name );
        if ( empty( $customer_display ) ) {
            $customer_display = 'Clienta';
        }

        $lines   = [];
        $lines[] = '<b>Cliente:</b> ' . esc_html( $customer_display );
        $lines[] = '<b>Total Carrito:</b> ' . esc_html( $formatted_total );
        if ( ! empty( $email ) ) {
            $lines[] = '<b>Email:</b> ' . esc_html( $email );
        }
        if ( ! empty( $phone ) ) {
            $lines[] = '<b>Teléfono:</b> ' . esc_html( $phone );
        }
        if ( ! empty( $location ) ) {
            $lines[] = '<b>Ubicación:</b> ' . esc_html( $location );
        }
        $lines[] = '';
        $lines[] = '<b>Prendas en el carrito:</b>';
        $lines[] = $items_text;

        $payload = [
            'channel' => 'marketing',
            'level'   => 'warning',
            'title'   => '🛒 Carrito Abandonado de Alto Valor',
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
            'buttons' => $buttons,
        ];

        $this->send_telegram_notification( $payload );
    }

    /**
     * Programa recordatorio de pago para pedidos por transferencia bancaria (BACS) a las 4 horas.
     * Hook: woocommerce_order_status_on-hold
     *
     * @param int|WC_Order $order_id Instancia o ID de la orden.
     */
    public function schedule_bacs_pending_reminder( $order_id ) {
        $order = $order_id instanceof WC_Order ? $order_id : wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        if ( 'bacs' !== $order->get_payment_method() ) {
            return;
        }

        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return;
        }

        $order_id = $order->get_id();
        $args     = [ 'order_id' => $order_id ];
        $group    = 'nh-recovery';

        if ( function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'nh_check_bacs_pending_order', $args, $group ) ) {
            return;
        }

        $scheduled_time = time() + ( 4 * HOUR_IN_SECONDS );
        as_schedule_single_action( $scheduled_time, 'nh_check_bacs_pending_order', $args, $group );

        $order->add_order_note( __( '[NH Transferencia] Tarea programada en 4 horas para verificar pago pendiente por transferencia.', 'nh-core' ) );
    }

    /**
     * Procesa la alerta de pago pendiente por transferencia (BACS) tras 4 horas si la orden sigue on-hold.
     * Hook: nh_check_bacs_pending_order
     *
     * @param int|array $order_id ID de la orden o array de argumentos.
     */
    public function process_bacs_pending_reminder( $order_id ) {
        if ( is_array( $order_id ) && isset( $order_id['order_id'] ) ) {
            $order_id = $order_id['order_id'];
        }
        $order_id = absint( $order_id );
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || 'on-hold' !== $order->get_status() ) {
            return;
        }

        // Evitar notificaciones duplicadas
        if ( $order->get_meta( '_nh_bacs_reminder_notified' ) ) {
            return;
        }

        $first_name = trim( (string) $order->get_billing_first_name() );
        if ( empty( $first_name ) ) {
            $first_name = trim( (string) $order->get_formatted_billing_full_name() );
        }
        if ( empty( $first_name ) ) {
            $first_name = 'Clienta';
        }

        $order_total = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ), ENT_QUOTES, 'UTF-8' );
        if ( ! str_contains( $order_total, 'COP' ) ) {
            $order_total .= ' COP';
        }

        $customer_phone = $order->get_billing_phone();
        $clean_phone    = preg_replace( '/\D+/', '', (string) $customer_phone );
        if ( ! empty( $clean_phone ) ) {
            if ( str_starts_with( $clean_phone, '57' ) ) {
                // Ya cuenta con código de país
            } elseif ( strlen( $clean_phone ) === 10 && str_starts_with( $clean_phone, '3' ) ) {
                $clean_phone = '57' . $clean_phone;
            }
        }

        $wa_msg = sprintf(
            'Hola %s ✨ Te escribimos de Norma Hana para compartirte los datos de transferencia para tu orden #%d (%s): Bancolombia Cuenta de Ahorros o Nequi. ¿Deseas que te enviemos los números de cuenta para completar tu pedido?',
            $first_name,
            $order_id,
            $order_total
        );

        $wa_url    = ! empty( $clean_phone ) ? 'https://wa.me/' . $clean_phone . '?text=' . rawurlencode( $wa_msg ) : '';
        $admin_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        $buttons = [];
        if ( ! empty( $wa_url ) ) {
            $buttons[] = [
                [ 'text' => '💬 Enviar Datos Bancarios por WhatsApp', 'url' => $wa_url ],
            ];
            $buttons[] = [
                [ 'text' => '📋 Ver Orden en WP Admin', 'url' => $admin_url ],
            ];
        } else {
            $buttons[] = [
                [ 'text' => '📋 Ver Orden en WP Admin', 'url' => $admin_url ],
            ];
        }

        $lines   = [];
        $lines[] = '<b>Orden:</b> #' . $order_id . ' (' . esc_html( $order_total ) . ')';
        $lines[] = '<b>Estado:</b> En espera de transferencia bancaria (4 horas)';
        $lines[] = '<b>Cliente:</b> ' . esc_html( trim( $order->get_formatted_billing_full_name() ) ?: $first_name );
        if ( ! empty( $order->get_billing_email() ) ) {
            $lines[] = '<b>Email:</b> ' . esc_html( $order->get_billing_email() );
        }
        if ( ! empty( $customer_phone ) ) {
            $lines[] = '<b>Teléfono:</b> ' . esc_html( $customer_phone );
        }

        $payload = [
            'channel' => 'marketing',
            'level'   => 'warning',
            'title'   => '🏦 Transferencia Pendiente: Recordatorio 4h (BACS)',
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
            'buttons' => $buttons,
        ];

        $sent = $this->send_telegram_notification( $payload );
        $order->update_meta_data( '_nh_bacs_reminder_notified', time() );
        $order->save();

        if ( $sent ) {
            $order->add_order_note( __( '[NH Transferencia] Alerta de pago pendiente por transferencia (4h) enviada a Telegram.', 'nh-core' ) );
        }
    }

    /**
     * Programa alerta para pedidos en taller que lleven más de 48 horas en procesamiento sin despachar.
     * Hook: woocommerce_order_status_processing
     *
     * @param int|WC_Order $order_id Instancia o ID de la orden.
     */
    public function schedule_delayed_processing_alert( $order_id ) {
        $order = $order_id instanceof WC_Order ? $order_id : wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return;
        }

        $order_id = $order->get_id();
        $args     = [ 'order_id' => $order_id ];
        $group    = 'nh-operations';

        if ( function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'nh_check_delayed_processing_order', $args, $group ) ) {
            return;
        }

        $scheduled_time = time() + ( 48 * HOUR_IN_SECONDS );
        as_schedule_single_action( $scheduled_time, 'nh_check_delayed_processing_order', $args, $group );

        $order->add_order_note( __( '[NH Operaciones] Tarea programada en 48 horas para monitorear despacho en taller.', 'nh-core' ) );
    }

    /**
     * Procesa la alerta de pedido demorado en taller (>48h) si sigue en estado processing.
     * Hook: nh_check_delayed_processing_order
     *
     * @param int|array $order_id ID de la orden o array de argumentos.
     */
    public function process_delayed_processing_alert( $order_id ) {
        if ( is_array( $order_id ) && isset( $order_id['order_id'] ) ) {
            $order_id = $order_id['order_id'];
        }
        $order_id = absint( $order_id );
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || 'processing' !== $order->get_status() ) {
            return;
        }

        if ( $order->get_meta( '_nh_delayed_processing_notified' ) ) {
            return;
        }

        $admin_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        $customer_name = trim( (string) $order->get_formatted_billing_full_name() );
        $order_total   = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ), ENT_QUOTES, 'UTF-8' );

        $msg = 'El pedido #' . $order_id . ' lleva 48 horas en estado Procesando en el taller de Santa Marta sin marcarse como completado.';
        if ( ! empty( $customer_name ) ) {
            $msg .= "\n" . '<b>Cliente:</b> ' . esc_html( $customer_name );
        }
        if ( ! empty( $order_total ) ) {
            $msg .= "\n" . '<b>Total:</b> ' . esc_html( $order_total );
        }

        $payload = [
            'channel' => 'marketing',
            'level'   => 'warning',
            'title'   => '⏳ Pedido en Taller sin Despachar (>48h)',
            'message' => $msg,
            'chat_id' => '-5244885992',
            'buttons' => [
                [
                    [ 'text' => '📦 Despachar / Ver Orden', 'url' => $admin_url ],
                ],
            ],
        ];

        $sent = $this->send_telegram_notification( $payload );
        $order->update_meta_data( '_nh_delayed_processing_notified', time() );
        $order->save();

        if ( $sent ) {
            $order->add_order_note( __( '[NH Operaciones] Alerta de pedido sin despachar (>48h) enviada a Telegram.', 'nh-core' ) );
        }
    }

    /**
     * Notificación de stock bajo en inventario.
     * Hook: woocommerce_low_stock
     *
     * @param WC_Product $product Producto o variación con stock bajo.
     */
    public function notify_low_stock( $product ) {
        $this->send_stock_alert( $product, 'low' );
    }

    /**
     * Notificación de producto agotado en tienda.
     * Hook: woocommerce_no_stock
     *
     * @param WC_Product $product Producto o variación sin stock.
     */
    public function notify_no_stock( $product ) {
        $this->send_stock_alert( $product, 'no' );
    }

    /**
     * Enrutador central de alertas de inventario crítico hacia Telegram.
     *
     * @param WC_Product $product Instancia del producto o variación.
     * @param string     $type    Tipo de alerta ('low'|'no').
     */
    private function send_stock_alert( $product, string $type = 'low' ) {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $is_variation  = $product->is_type( 'variation' );
        $admin_post_id = $is_variation ? $product->get_parent_id() : $product->get_id();

        if ( $is_variation ) {
            $parent       = wc_get_product( $product->get_parent_id() );
            $parent_title = $parent ? $parent->get_name() : $product->get_name();

            $attrs = [];
            foreach ( $product->get_variation_attributes() as $attr_name => $attr_value ) {
                if ( ! empty( $attr_value ) ) {
                    $taxonomy = str_replace( 'attribute_', '', $attr_name );
                    $label    = wc_attribute_label( $taxonomy );
                    $term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $attr_value, $taxonomy ) : false;
                    $val_name = $term ? $term->name : ucfirst( $attr_value );
                    $attrs[]  = $label . ': ' . $val_name;
                }
            }
            $product_title = ! empty( $attrs ) ? $parent_title . ' — ' . implode( ', ', $attrs ) : $product->get_formatted_name();
        } else {
            $product_title = $product->get_name();
        }

        $sku       = $product->get_sku() ?: 'Sin SKU';
        $stock_qty = $product->get_stock_quantity();
        $admin_url = admin_url( 'post.php?post=' . $admin_post_id . '&action=edit' );

        $lines   = [];
        $lines[] = '<b>Prenda:</b> ' . esc_html( $product_title );
        $lines[] = '<b>SKU:</b> <code>' . esc_html( $sku ) . '</code>';

        if ( 'no' === $type ) {
            $level   = 'error';
            $title   = '🚨 Prenda Agotada en Tienda';
            $lines[] = '<b>Estado:</b> 0 unidades (Agotado)';
            $btn_text = '📋 Reabastecer en WP Admin';
        } else {
            $level   = 'warning';
            $title   = '⚠️ Stock Bajo en Inventario';
            $lines[] = '<b>Unidades restantes:</b> ' . ( null !== $stock_qty ? (int) $stock_qty : 0 );
            $btn_text = '📋 Editar Inventario en WP Admin';
        }

        $payload = [
            'channel' => 'marketing',
            'level'   => $level,
            'title'   => $title,
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
            'buttons' => [
                [
                    [ 'text' => $btn_text, 'url' => $admin_url ]
                ]
            ],
        ];

        $this->send_telegram_notification( $payload );
    }

    /**
     * Asegura la programación del resumen nocturno a las 10:00 PM (COT / 22:00 America/Bogota) en Action Scheduler.
     * Hook: init / constructor
     */
    public function maybe_schedule_daily_briefing() {
        if ( ! function_exists( 'as_has_scheduled_action' ) || ! function_exists( 'as_schedule_recurring_action' ) ) {
            return;
        }

        // Desprogramar acción matutina legacy si existía
        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( 'nh_daily_morning_briefing' );
        }

        if ( ! as_has_scheduled_action( 'nh_daily_nightly_recap' ) ) {
            $tz     = new DateTimeZone( 'America/Bogota' );
            $now    = new DateTime( 'now', $tz );
            $target = new DateTime( 'today 22:00:00', $tz );
            if ( $now >= $target ) {
                $target->modify( '+1 day' );
            }
            $next_10pm_timestamp = $target->getTimestamp();
            as_schedule_recurring_action( $next_10pm_timestamp, DAY_IN_SECONDS, 'nh_daily_nightly_recap', [], 'nh-reports' );
        }
    }

    /**
     * Genera y envía el reporte nocturno de ventas y estadísticas a las 10:00 PM COT (22:00 America/Bogota).
     * Hook: nh_daily_nightly_recap
     *
     * @return bool True si la notificación se envió con éxito.
     */
    public function send_daily_nightly_recap() {
        $tz          = new DateTimeZone( 'America/Bogota' );
        $today_start = new DateTime( 'today 00:00:00', $tz );
        $now         = new DateTime( 'now', $tz );

        // 1. Pedidos confirmados en la ventana de hoy (processing y completed)
        $today_orders = wc_get_orders( [
            'status'        => [ 'processing', 'completed' ],
            'date_created'  => $today_start->getTimestamp() . '...' . $now->getTimestamp(),
            'limit'         => -1,
        ] );

        $order_count   = count( $today_orders );
        $total_sum     = 0.0;
        $product_sales = [];

        foreach ( $today_orders as $order ) {
            $total_sum += (float) $order->get_total();
            foreach ( $order->get_items() as $item ) {
                $name = $item->get_name();
                $qty  = $item->get_quantity();
                if ( ! isset( $product_sales[ $name ] ) ) {
                    $product_sales[ $name ] = 0;
                }
                $product_sales[ $name ] += $qty;
            }
        }

        // 2. Pedidos pendientes por despachar en taller (actualmente en 'processing')
        $processing_orders = wc_get_orders( [
            'status' => 'processing',
            'limit'  => -1,
            'return' => 'ids',
        ] );
        $pending_dispatch_count = count( $processing_orders );

        // 3. Formateo de estadísticas de venta
        $lines = [];

        if ( $order_count > 0 ) {
            $formatted_total = html_entity_decode( wp_strip_all_tags( wc_price( $total_sum ) ), ENT_QUOTES, 'UTF-8' );
            if ( ! str_contains( $formatted_total, 'COP' ) ) {
                $formatted_total .= ' COP';
            }
            $lines[] = '• <b>Ventas de hoy:</b> ' . $order_count . ' pedido' . ( 1 === $order_count ? '' : 's' ) . ' (' . esc_html( $formatted_total ) . ')';

            if ( ! empty( $product_sales ) ) {
                arsort( $product_sales );
                $top_name = array_key_first( $product_sales );
                $top_qty  = $product_sales[ $top_name ];
                $lines[]  = '• <b>Prenda destacada hoy:</b> ' . esc_html( $top_name ) . ' (' . $top_qty . ' ud' . ( $top_qty > 1 ? 's' : '' ) . ')';
            }
        } else {
            $lines[] = '• <b>Ventas de hoy:</b> Sin compras directas hoy • Día enfocado en descubrimiento.';
        }

        $lines[] = '• <b>En taller (confección/despacho):</b> ' . $pending_dispatch_count . ' pedido' . ( 1 === $pending_dispatch_count ? '' : 's' ) . ' en proceso';

        $admin_orders_url = admin_url( 'edit.php?post_type=shop_order' );

        $payload = [
            'channel' => 'marketing',
            'level'   => 'info',
            'title'   => '🌙 Cierre del Día — Atelier Norma Hana (10:00 PM)',
            'message' => implode( "\n", $lines ),
            'chat_id' => '-5244885992',
            'buttons' => [
                [
                    [ 'text' => '📋 Ver Pedidos en WP Admin', 'url' => $admin_orders_url ]
                ]
            ],
        ];

        return $this->send_telegram_notification( $payload );
    }

    /**
     * Alias de compatibilidad hacia el nuevo reporte nocturno.
     */
    public function send_daily_morning_briefing() {
        return $this->send_daily_nightly_recap();
    }

    /**
     * Envía una notificación al bot interno de Telegram.
     * Diseñado para ser no bloqueante y a prueba de fallos silenciosos para no afectar la UX de checkout.
     *
     * @param array $payload Datos de notificación (channel, level, title, message, chat_id, buttons).
     * @return bool True si se envió correctamente, false si falló.
     */
    public function send_telegram_notification( array $payload ) {
        $endpoint = apply_filters( 'nh_telegram_notify_endpoint', 'http://normahana-telegram-bot:3000/api/v1/notify' );
        $api_key  = defined( 'NH_TELEGRAM_BOT_API_KEY' ) ? NH_TELEGRAM_BOT_API_KEY : 'nh_telegram_bot_sec_2026_x871a';

        $body = wp_parse_args( $payload, [
            'channel' => 'system',
            'level'   => 'info',
            'title'   => 'Notificación Norma Hana',
            'message' => '',
            'chat_id' => '-5244885992',
        ] );

        // Truncar para respetar límites del validador de Telegram bot
        $body['title']   = mb_substr( (string) $body['title'], 0, 256 );
        $body['message'] = mb_substr( (string) $body['message'], 0, 4000 );

        if ( ! empty( $payload['buttons'] ) && is_array( $payload['buttons'] ) ) {
            $body['buttons'] = $payload['buttons'];
        }

        $args = [
            'headers'     => [
                'Content-Type' => 'application/json; charset=utf-8',
                'x-api-key'    => $api_key,
            ],
            'body'        => wp_json_encode( $body ),
            'timeout'     => 5,
            'redirection' => 2,
            'httpversion' => '1.1',
            'blocking'    => true,
            'data_format' => 'body',
        ];

        $response = wp_remote_post( $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            error_log( sprintf( '[NH Core Telegram] Fallo al enviar notificación: %s', $response->get_error_message() ) );
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            $resp_body = wp_remote_retrieve_body( $response );
            error_log( sprintf( '[NH Core Telegram] Error HTTP %d al notificar Telegram: %s', $code, $resp_body ) );
            return false;
        }

        return true;
    }

    /**
     * Optimiza y reordena los campos del checkout según la estrategia CRO (Fase 1).
     * Contact-First (Email y WhatsApp primero para capturar leads temprano),
     * lógica geográfica colombiana (Departamento -> Ciudad -> Dirección),
     * y remoción de campos redundantes como código postal y compañía.
     *
     * @param array $fields Campos del checkout de WooCommerce.
     * @return array
     */
    public function optimize_checkout_fields_cro( $fields ) {
        // Remover campos innecesarios en Colombia
        unset( $fields['billing']['billing_postcode'] );
        unset( $fields['shipping']['shipping_postcode'] );
        unset( $fields['billing']['billing_company'] );
        unset( $fields['shipping']['shipping_company'] );

        // Contact First (Captura temprana de lead para CartFlows / Recuperación Telegram)
        if ( isset( $fields['billing']['billing_email'] ) ) {
            $fields['billing']['billing_email']['priority']     = 5;
            $fields['billing']['billing_email']['label']        = 'Correo electrónico';
            $fields['billing']['billing_email']['placeholder']  = 'tucorreo@ejemplo.com';
            $fields['billing']['billing_email']['autocomplete'] = 'email';
            $fields['billing']['billing_email']['class']        = [ 'form-row-wide' ];
        }

        if ( isset( $fields['billing']['billing_phone'] ) ) {
            $fields['billing']['billing_phone']['priority']     = 10;
            $fields['billing']['billing_phone']['required']     = true;
            $fields['billing']['billing_phone']['label']        = 'Teléfono móvil / WhatsApp';
            $fields['billing']['billing_phone']['placeholder']  = '300 123 4567';
            $fields['billing']['billing_phone']['type']         = 'tel';
            $fields['billing']['billing_phone']['autocomplete'] = 'tel';
            $fields['billing']['billing_phone']['class']        = [ 'form-row-wide' ];
        }

        if ( isset( $fields['billing']['billing_first_name'] ) ) {
            $fields['billing']['billing_first_name']['priority']     = 15;
            $fields['billing']['billing_first_name']['label']        = 'Nombre';
            $fields['billing']['billing_first_name']['autocomplete'] = 'given-name';
            $fields['billing']['billing_first_name']['class']        = [ 'form-row-first' ];
        }

        if ( isset( $fields['billing']['billing_last_name'] ) ) {
            $fields['billing']['billing_last_name']['priority']     = 20;
            $fields['billing']['billing_last_name']['label']        = 'Apellidos';
            $fields['billing']['billing_last_name']['autocomplete'] = 'family-name';
            $fields['billing']['billing_last_name']['class']        = [ 'form-row-last' ];
        }

        // Cédula / Documento (Addi & Facturación)
        if ( isset( $fields['billing']['billing_id'] ) ) {
            $fields['billing']['billing_id']['priority']          = 25;
            $fields['billing']['billing_id']['label']             = 'Cédula de Ciudadanía';
            $fields['billing']['billing_id']['placeholder']       = 'Ej. 1082123456';
            $fields['billing']['billing_id']['class']             = [ 'form-row-wide' ];
            $fields['billing']['billing_id']['custom_attributes'] = [
                'inputmode' => 'numeric',
                'pattern'   => '[0-9]*',
            ];
        }

        // Lógica geográfica colombiana (Departamento -> Ciudad -> Dirección)
        if ( isset( $fields['billing']['billing_state'] ) ) {
            $fields['billing']['billing_state']['priority'] = 30;
            $fields['billing']['billing_state']['label']    = 'Departamento';
            $fields['billing']['billing_state']['class']    = [ 'form-row-first' ];
        }

        if ( isset( $fields['billing']['billing_city'] ) ) {
            $fields['billing']['billing_city']['priority']    = 35;
            $fields['billing']['billing_city']['label']       = 'Ciudad o Municipio';
            $fields['billing']['billing_city']['placeholder'] = 'Ej. Santa Marta, Barranquilla, Bogotá';
            $fields['billing']['billing_city']['class']       = [ 'form-row-last' ];
        }

        if ( isset( $fields['billing']['billing_address_1'] ) ) {
            $fields['billing']['billing_address_1']['priority']    = 40;
            $fields['billing']['billing_address_1']['label']       = 'Dirección de entrega';
            $fields['billing']['billing_address_1']['placeholder'] = 'Calle, Carrera, Avenida y Número';
            $fields['billing']['billing_address_1']['class']       = [ 'form-row-wide' ];
        }

        if ( isset( $fields['billing']['billing_address_2'] ) ) {
            $fields['billing']['billing_address_2']['priority']    = 45;
            $fields['billing']['billing_address_2']['label']       = 'Detalle de entrega (opcional)';
            $fields['billing']['billing_address_2']['placeholder'] = 'Apto, Casa, Conjunto, Interior, Barrio';
            $fields['billing']['billing_address_2']['class']       = [ 'form-row-wide' ];
        }

        // Indicaciones especiales de entrega
        if ( isset( $fields['order']['order_comments'] ) ) {
            $fields['order']['order_comments']['placeholder'] = 'Indicaciones especiales para la entrega (opcional)';
        }

        return $fields;
    }

    /**
     * Ajusta los campos predeterminados de dirección de WooCommerce para deshabilitar código postal y empresa.
     *
     * @param array $address_fields Campos de dirección predeterminados.
     * @return array
     */
    public function optimize_default_address_fields( $address_fields ) {
        if ( isset( $address_fields['postcode'] ) ) {
            $address_fields['postcode']['required'] = false;
            $address_fields['postcode']['hidden']   = true;
        }

        if ( isset( $address_fields['company'] ) ) {
            $address_fields['company']['hidden']   = true;
            $address_fields['company']['required'] = false;
        }

        return $address_fields;
    }

    /**
     * Sanitiza y completa datos de checkout antes del procesamiento del pedido.
     * Garantiza un código postal por defecto (110111) para evitar que pasarelas
     * estrictas (p. ej. Wompi) rechacen la transacción si esperan un string de código postal.
     *
     * @param array $data Datos posteados del checkout.
     * @return array
     */
    public function sanitize_checkout_posted_data( $data ) {
        if ( empty( $data['billing_postcode'] ) ) {
            $data['billing_postcode'] = '110111';
        }

        if ( empty( $data['shipping_postcode'] ) ) {
            $data['shipping_postcode'] = '110111';
        }

        return $data;
    }
}

/**
 * Función global de compatibilidad para el endpoint REST de estado de pedido.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
if ( ! function_exists( 'nh_get_order_status_api' ) ) {
    function nh_get_order_status_api( WP_REST_Request $request ) {
        return NH_Core_Woocommerce::get_instance()->get_order_status_api( $request );
    }
}

