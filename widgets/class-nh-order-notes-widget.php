<?php
/**
 * Widget Notas del Pedido (Order Notes)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Order_Notes_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_order_notes_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Notas del Pedido', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-file-download';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'order notes', 'notas', 'norma hana' ];
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
                'default' => esc_html__( 'Información Adicional', 'nh-core' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->checkout ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $checkout = WC()->checkout();
        ?>
        <div class="nh-checkout-widget nh-order-notes-container">
            <div class="nh-checkout-card">
                <?php if ( ! empty( $settings['title'] ) ) : ?>
                    <h3><?php echo esc_html( $settings['title'] ); ?></h3>
                <?php endif; ?>

                <?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
                    <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
