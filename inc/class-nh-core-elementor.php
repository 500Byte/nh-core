<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Elementor {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_live_counter_modules();
    }

    private function init_hooks() {
        // Registrar categoría NH Widgets
        add_action( 'elementor/widgets/categories/register', [ $this, 'register_nh_widgets_category' ] );
        
        // Registrar todos los widgets de Elementor migrados al plugin
        add_action( 'elementor/widgets/register', [ $this, 'register_nh_widgets' ] );
        
        // Encolar assets de NH Menu Cart de forma nativa desde el plugin
        add_action( 'elementor/panel/enqueue_styles', [ $this, 'menu_cart_enqueue_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'menu_cart_enqueue_assets' ] );
        
        // AJAX fragments de NH Menu Cart
        add_action( 'wp_ajax_elementor_menu_cart_fragments', [ $this, 'menu_cart_fragments' ] );
        add_action( 'wp_ajax_nopriv_elementor_menu_cart_fragments', [ $this, 'menu_cart_fragments' ] );
        
        // Dropdown wrapper assets global
        add_action( 'wp_enqueue_scripts', [ $this, 'dropdown_wrapper_enqueue_assets' ] );
        
        // ===== Registrar assets de NH Marquee =====
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'marquee_register_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'marquee_register_styles' ] );

        // ===== Registrar assets de NH Cart =====
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'cart_register_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'cart_register_styles' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'cart_register_scripts' ] );

        // ===== Registrar assets de NH Checkout v2 =====
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'checkout_register_assets' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'checkout_register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'checkout_register_assets' ] );

        // ===== Registrar assets de NH Shopify Checkout =====
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'shopify_checkout_register_assets' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'shopify_checkout_register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'shopify_checkout_register_assets' ] );

        // ===== Filtro de Variaciones en Pills para Tabla de Checkout =====
        add_filter( 'woocommerce_cart_item_name', [ $this, 'checkout_variation_pills_filter' ], 10, 3 );

        // ===== Override Plantilla de Resumen de Pedido (Table-less) =====
        add_filter( 'woocommerce_locate_template', [ $this, 'locate_checkout_templates' ], 10, 3 );
        remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
    }

    private function load_live_counter_modules() {
        // Mapear inclusiones estáticas del live counter
        require_once NH_CORE_PATH . 'widgets/live-counter-assets.php';
        require_once NH_CORE_PATH . 'widgets/live-counter-ajax.php';
    }

    public function register_nh_widgets_category( $categories_manager ) {
        $categories_manager->register_category( 'nh-widgets', [
            'title' => 'NH Widgets',
            'icon'  => 'eicon-star',
            'active' => true,
        ] );
    }

    public function register_nh_widgets( $widgets_manager ) {
        // Verificar que Elementor esté cargado antes de incluir widgets
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }

        require_once NH_CORE_PATH . 'widgets/class-nh-price-filter-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-product-sorting-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-live-counter-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-menu-cart-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-add-to-cart-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-marquee-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-cart-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-cart-table-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-cart-totals-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-coupon-form-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-cross-sells-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-checkout-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-billing-form-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-shipping-form-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-order-review-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-checkout-payment-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-order-notes-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-checkout-login-form-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-checkout-coupon-form-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-shipping-method-widget.php';
        require_once NH_CORE_PATH . 'widgets/class-nh-shopify-checkout-widget.php';

        $widgets_manager->register( new \NH_Price_Filter_Widget() );
        $widgets_manager->register( new \NH_Product_Sorting_Widget() );
        $widgets_manager->register( new \Elementor_Live_Counter_Widget() );
        $widgets_manager->register( new \NH_Menu_Cart_Widget() );
        $widgets_manager->register( new \NH_Add_To_Cart_Widget() );
        $widgets_manager->register( new \NH_Marquee_Widget() );
        $widgets_manager->register( new \NH_Cart_Widget() );
        $widgets_manager->register( new \NH_Cart_Table_Widget() );
        $widgets_manager->register( new \NH_Cart_Totals_Widget() );
        $widgets_manager->register( new \NH_Coupon_Form_Widget() );
        $widgets_manager->register( new \NH_Cross_Sells_Widget() );
        $widgets_manager->register( new \NH_Checkout_Widget() );
        $widgets_manager->register( new \NH_Billing_Form_Widget() );
        $widgets_manager->register( new \NH_Shipping_Form_Widget() );
        $widgets_manager->register( new \NH_Order_Review_Widget() );
        $widgets_manager->register( new \NH_Checkout_Payment_Widget() );
        $widgets_manager->register( new \NH_Order_Notes_Widget() );
        $widgets_manager->register( new \NH_Checkout_Login_Form_Widget() );
        $widgets_manager->register( new \NH_Checkout_Coupon_Form_Widget() );
        $widgets_manager->register( new \NH_Shipping_Method_Widget() );
        $widgets_manager->register( new \NH_Shopify_Checkout_Widget() );
    }

    public function menu_cart_enqueue_assets() {
        wp_register_style(
            'nh-menu-cart',
            NH_CORE_URL . 'assets/css/nh-menu-cart.min.css',
            [],
            '1.0.0'
        );
        wp_register_script(
            'nh-menu-cart',
            NH_CORE_URL . 'assets/js/nh-menu-cart.js',
            [],
            '1.0.0',
            true
        );
    }

    public function marquee_register_styles() {
        wp_register_style(
            'nh-marquee-widget',
            NH_CORE_URL . 'assets/css/nh-marquee.css',
            [],
            '1.0.0'
        );
    }

    public function cart_register_styles() {
        wp_enqueue_style(
            'nh-google-fonts',
            'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'nh-cart-widget',
            NH_CORE_URL . 'assets/css/nh-cart.css',
            [ 'nh-google-fonts' ],
            '2.0.0'
        );
    }

    public function cart_register_scripts() {
        wp_enqueue_script(
            'nh-cart-widget',
            NH_CORE_URL . 'assets/js/nh-cart.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );

        wp_localize_script( 'nh-cart-widget', 'nh_cart_params', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'nh_cart_nonce' ),
        ] );
    }

    public function checkout_register_assets() {
        wp_enqueue_style(
            'nh-checkout-widget',
            NH_CORE_URL . 'assets/css/nh-checkout.css',
            [ 'nh-google-fonts' ],
            time()
        );
        wp_enqueue_script(
            'nh-checkout-widget',
            NH_CORE_URL . 'assets/js/nh-checkout.js',
            [ 'jquery' ],
            time(),
            true
        );
    }

    public function shopify_checkout_register_assets() {
        wp_enqueue_style(
            'nh-shopify-checkout-widget',
            NH_CORE_URL . 'assets/css/nh-shopify-checkout.css',
            [ 'nh-google-fonts' ],
            time()
        );
        wp_enqueue_script(
            'nh-shopify-checkout-widget',
            NH_CORE_URL . 'assets/js/nh-shopify-checkout.js',
            [ 'jquery' ],
            time(),
            true
        );
    }

    public function menu_cart_fragments() {
        if ( null === WC()->cart ) {
            wp_send_json_error();
        }
        $fragments = [];
        $product_count = WC()->cart->get_cart_contents_count();
        $sub_total = WC()->cart->get_cart_subtotal();
        $fragments['.elementor-menu-cart__toggle_button span.elementor-button-text'] = '<span class="elementor-button-text">' . $sub_total . '</span>';
        $fragments['.elementor-menu-cart__toggle_button span.elementor-button-icon-qty'] = '<span class="elementor-button-icon-qty" data-counter="' . $product_count . '">' . $product_count . '</span>';
        wp_send_json_success( $fragments );
    }

    public function dropdown_wrapper_enqueue_assets() {
        wp_enqueue_style(
            'nh-dropdown-wrapper',
            NH_CORE_URL . 'assets/css/nh-dropdown-wrapper.css',
            [],
            '1.0.0'
        );
        wp_enqueue_script(
            'nh-dropdown-wrapper',
            NH_CORE_URL . 'assets/js/nh-dropdown-wrapper.js',
            [],
            '1.0.0',
            true
        );
    }

    public function checkout_variation_pills_filter( $name, $cart_item, $cart_item_key ) {
        if ( is_checkout() ) {
            $_product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
            if ( $_product ) {
                $base_title = $_product->get_title();
                $thumbnail  = $_product->get_image( [ 56, 56 ], [ 'class' => 'nh-checkout-item-thumb-img' ] );
                $quantity   = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;

                $pills_html = '';
                if ( ! empty( $cart_item['variation'] ) ) {
                    $pills_html .= '<div class="nh-cart-product-variation-pills">';
                    foreach ( $cart_item['variation'] as $attr_key => $attr_value ) {
                        if ( '' === $attr_value ) continue;
                        $taxonomy = str_replace( 'attribute_', '', $attr_key );
                        $label = wc_attribute_label( $taxonomy, $_product );
                        $term = get_term_by( 'slug', $attr_value, $taxonomy );
                        $display_val = $term ? $term->name : ucfirst( $attr_value );
                        $pills_html .= sprintf(
                            '<span class="nh-cart-variation-pill"><span class="nh-variation-label">%s:</span> <span class="nh-variation-val">%s</span></span>',
                            esc_html( $label ),
                            esc_html( $display_val )
                        );
                    }
                    $pills_html .= '</div>';
                }

                $output = sprintf(
                    '<div class="nh-checkout-item-flex">
                        <div class="nh-checkout-thumb-wrap">
                            %s
                            <span class="nh-checkout-qty-badge">%d</span>
                        </div>
                        <div class="nh-checkout-item-info">
                            <span class="nh-checkout-item-title">%s</span>
                            %s
                        </div>
                    </div>',
                    $thumbnail,
                    (int) $quantity,
                    esc_html( $base_title ),
                    $pills_html
                );

                return $output;
            }
        }
        return $name;
    }

    public function locate_checkout_templates( $template, $template_name, $template_path ) {
        if ( 'checkout/review-order.php' === $template_name ) {
            $custom_template = NH_CORE_PATH . 'templates/checkout/review-order.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }
        return $template;
    }
}