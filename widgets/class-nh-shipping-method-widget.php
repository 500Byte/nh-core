<?php
/**
 * Widget Métodos de Envío Autónomos (Standalone Shipping Method)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Shipping_Method_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_shipping_method_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Métodos de Envío', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'shipping method', 'metodos de envio', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-checkout-widget', 'wc-checkout' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Configuración', 'nh-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label'   => esc_html__( 'Título', 'nh-core' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Métodos de Envío Disponibles', 'nh-core' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->cart->needs_shipping() ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $packages = WC()->shipping()->get_packages();
        ?>
        <div class="nh-checkout-widget nh-shipping-method-container">
            <div class="nh-checkout-card">
                <?php if ( ! empty( $settings['title'] ) ) : ?>
                    <h3><?php echo esc_html( $settings['title'] ); ?></h3>
                <?php endif; ?>

                <div class="nh-cart-shipping-block">
                    <?php foreach ( $packages as $i => $package ) : ?>
                        <?php wc_cart_totals_shipping_html( $package ); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
