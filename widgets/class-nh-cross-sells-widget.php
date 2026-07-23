<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Cross_Sells_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_cross_sells_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Productos Cruzados (Cross-sells)', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-product-related';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'cross-sells', 'related', 'productos', 'sugeridos', 'cart', 'woocommerce' ];
    }

    public function get_style_depends() {
        return [ 'nh-cart-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-cart-widget' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Configuración', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => esc_html__( 'Límite de Productos', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 12,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => esc_html__( 'Columnas', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 6,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        $cart = WC()->cart;
        $settings = $this->get_settings_for_display();

        if ( $cart->is_empty() ) {
            return;
        }

        $limit = isset( $settings['limit'] ) ? intval( $settings['limit'] ) : 4;
        $columns = isset( $settings['columns'] ) ? intval( $settings['columns'] ) : 4;

        ?>
        <div class="nh-cart-widget">
            <div class="nh-cart-cross-sells">
                <?php 
                if ( function_exists( 'woocommerce_cross_sell_display' ) ) {
                    woocommerce_cross_sell_display( $limit, $columns );
                }
                ?>
            </div>
        </div>
        <?php
    }
}
