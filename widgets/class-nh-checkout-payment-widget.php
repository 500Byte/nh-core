<?php
/**
 * Widget Pasarelas de Pago & Botón Pedido (Checkout Payment)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Checkout_Payment_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_checkout_payment_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Pasarelas de Pago', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-credit-card';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'payment', 'pago', 'pasarela', 'norma hana' ];
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
                'default' => esc_html__( 'Métodos de Pago', 'nh-core' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        ?>
        <div class="nh-checkout-widget nh-payment-container">
            <div class="nh-checkout-card">
                <?php if ( ! empty( $settings['title'] ) ) : ?>
                    <h3><?php echo esc_html( $settings['title'] ); ?></h3>
                <?php endif; ?>

                <?php woocommerce_checkout_payment(); ?>
            </div>
        </div>
        <?php
    }
}
