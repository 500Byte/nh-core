<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NH_Add_To_Cart_Widget extends \Elementor\Widget_Base {

    public static $custom_backorder_text = '';

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

        $this->end_controls_section();
    }

    protected function render() {
        global $product;

        $product = wc_get_product();
        if ( ! $product ) {
            return;
        }

        $settings = $this->get_settings_for_display();

        // Guardar el texto de reserva personalizado en la base de datos y propiedad estática
        $backorder_text = isset( $settings['backorder_text'] ) ? $settings['backorder_text'] : '';
        self::$custom_backorder_text = $backorder_text;
        if ( ! empty( $backorder_text ) ) {
            update_option( 'nh_custom_backorder_text', $backorder_text );
        }

        echo '<div class="nh-custom-add-to-cart-wrapper">';
        
        // Render WooCommerce native add to cart form
        woocommerce_template_single_add_to_cart();

        // Render promo block
        if ( ! empty( $settings['promo_text'] ) ) {
            $icon = ( 'yes' === $settings['show_icon'] ) ? '<span class="nh-icon">✓</span>' : '';
            echo '
            <div class="nh-injected-snippet nh-atc-snippet">
                ' . $icon . '
                <span>' . esc_html( $settings['promo_text'] ) . '</span>
            </div>
            ';
        }

        echo '</div>';
    }
}
