<?php
/**
 * Widget Formulario de Envío (Shipping Form)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Shipping_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_shipping_form_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Formulario de Envío', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-shipping';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'shipping', 'envio', 'norma hana' ];
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
                'default' => esc_html__( 'Enviar a una dirección diferente', 'nh-core' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->checkout ) {
            return;
        }

        $checkout = WC()->checkout();
        if ( ! WC()->cart->needs_shipping_address() ) {
            return;
        }
        ?>
        <div class="nh-checkout-widget nh-shipping-form-container">
            <div class="nh-checkout-card">
                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
            </div>
        </div>
        <?php
    }
}
