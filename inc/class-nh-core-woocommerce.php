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
    }

    private function init_hooks() {
        add_shortcode( 'nh_price_filter', [ $this, 'price_filter_shortcode' ] );
        add_shortcode( 'addi_widget', [ $this, 'addi_widget_shortcode' ] );
        add_action( 'pre_get_posts', [ $this, 'apply_price_filter_to_all_queries' ], 99 );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_filter( 'woocommerce_locate_template', [ $this, 'locate_quantity_input_template' ], 10, 3 );
        add_filter( 'woocommerce_get_stock_html', [ $this, 'custom_backorder_stock_html' ], 10, 2 );
        
        // Hooks de invalidación de transients al modificar productos
        add_action( 'woocommerce_update_product', [ $this, 'invalidate_price_transients' ] );
        add_action( 'woocommerce_new_product', [ $this, 'invalidate_price_transients' ] );
        add_action( 'woocommerce_trash_product', [ $this, 'invalidate_price_transients' ] );
        
        // Hook general para pedidos test
        add_filter( 'woocommerce_email_recipient_new_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );
        add_filter( 'woocommerce_email_recipient_customer_processing_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );
        add_filter( 'woocommerce_email_recipient_customer_completed_order', [ $this, 'disable_email_for_test_coupon' ], 10, 2 );

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
    }

    public function register_assets() {
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
        if ( is_admin() ) {
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
            return '';
        }
        return $recipient;
    }

    public function custom_backorder_stock_html( $html, $product ) {
        if ( strpos( $html, 'available-on-backorder' ) !== false ) {
            $custom_text = '';

            // 1. Intentar desde la variable estática del widget en memoria
            if ( class_exists( '\NH_Add_To_Cart_Widget' ) && ! empty( \NH_Add_To_Cart_Widget::$custom_backorder_text ) ) {
                $custom_text = \NH_Add_To_Cart_Widget::$custom_backorder_text;
            }

            // 2. Si está vacía, usar el fallback persistido en la base de datos (por ejemplo, para cargas tempranas o AJAX)
            if ( empty( $custom_text ) ) {
                $custom_text = get_option( 'nh_custom_backorder_text' );
            }

            if ( ! empty( $custom_text ) ) {
                $html = '<p class="stock available-on-backorder">' . wp_kses_post( $custom_text ) . '</p>';
            }
        }
        return $html;
    }

    public function ajax_update_cart_item() {
        check_ajax_referer( 'nh_cart_nonce', 'nonce' );
        
        $key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
        $qty = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
        
        if ( $key && function_exists( 'WC' ) && WC()->cart ) {
            WC()->cart->set_quantity( $key, $qty );
            wp_send_json_success();
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
}
