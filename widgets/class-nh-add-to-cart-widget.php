<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NH_Add_To_Cart_Widget extends \Elementor\Widget_Base {

    /** @var int Product ID for Buy Now hook */
    private $_buy_now_product_id = 0;
    /** @var bool Whether product is variable */
    private $_buy_now_is_variable = false;
    /** @var string Primary category name */
    private $_buy_now_category = '';
    /** @var string Button label */
    private $_buy_now_text = '';

    public function get_name() {
        return 'nh-add-to-cart';
    }

    public function get_title() {
        return esc_html__( 'NH Add to Cart', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-product-add-to-cart';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    /**
     * Force Elementor editor to always use PHP render() instead of the
     * JavaScript content_template(). This ensures the editor preview always
     * matches the frontend for widgets with dynamic WooCommerce content.
     *
     * @return bool
     */
    protected function is_dynamic_content(): bool {
        return true;
    }


    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Contenido', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'promo_text',
            [
                'label' => esc_html__( 'Texto Promocional', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Envío nacional gratis para compras superiores a $150.000 COP', 'nh-core' ),
                'placeholder' => esc_html__( 'Escribe la promoción aquí', 'nh-core' ),
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => esc_html__( 'Mostrar Icono (✓)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'backorder_text',
            [
                'label' => esc_html__( 'Texto de Reserva (WYSIWYG)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Disponible para reserva', 'nh-core' ),
                'placeholder' => esc_html__( 'Escribe el texto de reserva aquí', 'nh-core' ),
            ]
        );

        $this->add_control(
            'show_buy_now',
            [
                'label' => esc_html__( 'Mostrar Botón Compra Rápida', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'buy_now_text',
            [
                'label' => esc_html__( 'Texto del Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Comprar Ahora', 'nh-core' ),
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $product;

        $product = wc_get_product();
        if ( ! $product ) {
            return;
        }

        $settings = $this->get_settings_for_display();

        // Filtro persistente para texto de backorder por producto
        $backorder_text = isset( $settings['backorder_text'] ) ? $settings['backorder_text'] : '';
        if ( ! empty( $backorder_text ) && $product ) {
            $product_id = $product->get_id();
            add_filter( 'woocommerce_get_stock_html', function( $html ) use ( $backorder_text, $product_id ) {
                if ( strpos( $html, 'available-on-backorder' ) === false ) {
                    return $html;
                }
                return '<p class="stock available-on-backorder">' . wp_kses_post( $backorder_text ) . '</p>';
            }, 20 );
        }

        echo '<div class="nh-add-to-cart">';

        // If Buy Now is enabled, inject it inside form.cart via WC hook
        if ( 'yes' === $settings['show_buy_now'] && $product ) {
            $this->_buy_now_product_id  = $product->get_id();
            $this->_buy_now_is_variable = $product->is_type( 'variable' );
            $this->_buy_now_category    = $this->get_primary_category( $this->_buy_now_product_id );
            $this->_buy_now_text        = $settings['buy_now_text'];

            add_action( 'woocommerce_after_add_to_cart_button', [ $this, '_render_buy_now_button' ] );
        }

        // Render the WC form (hook injects Buy Now inside form.cart)
        woocommerce_template_single_add_to_cart();

        // Remove hook after render to prevent leaking
        if ( 'yes' === $settings['show_buy_now'] ) {
            remove_action( 'woocommerce_after_add_to_cart_button', [ $this, '_render_buy_now_button' ] );
        }

        // Render promo block
        if ( ! empty( $settings['promo_text'] ) ) {
            $icon = ( 'yes' === $settings['show_icon'] ) ? '<span class="nh-add-to-cart__promo-icon">✓</span>' : '';
            echo '
            <div class="nh-add-to-cart__promo">
                ' . $icon . '
                <span>' . esc_html( $settings['promo_text'] ) . '</span>
            </div>
            ';
        }

        echo '</div>';
    }

    /**
     * Render Buy Now button — hooked into woocommerce_after_add_to_cart_button.
     * Called during woocommerce_template_single_add_to_cart(), removed after.
     */
    public function _render_buy_now_button() {
        if ( ! $this->_buy_now_product_id ) {
            return;
        }

        printf(
            '<button type="button" class="nh-btn nh-btn-primary nh-add-to-cart__buy-now" data-nh-product-id="%s" data-nh-product-name="%s" data-nh-product-price="%s" data-nh-category="%s" data-is-variable="%s">%s</button>',
            esc_attr( $this->_buy_now_product_id ),
            esc_attr( get_the_title( $this->_buy_now_product_id ) ),
            esc_attr( wc_get_product( $this->_buy_now_product_id )->get_price() ),
            esc_attr( $this->_buy_now_category ),
            $this->_buy_now_is_variable ? 'true' : 'false',
            esc_html( $this->_buy_now_text )
        );
    }

    /**
     * Get the first product category name for a given product ID.
     */
    private function get_primary_category( $product_id ) {
        $terms = get_the_terms( $product_id, 'product_cat' );
        return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
    }
}
