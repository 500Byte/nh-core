<?php
/**
 * Widget Resumen de Pedido (Order Review Table)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Order_Review_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_order_review_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Resumen de Pedido', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'order review', 'resumen', 'norma hana' ];
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
                'default' => esc_html__( 'Tu Pedido', 'nh-core' ),
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
        <div class="nh-checkout-widget nh-order-review-container">
            <div class="nh-checkout-card">
                <?php if ( ! empty( $settings['title'] ) ) : ?>
                    <h3><?php echo esc_html( $settings['title'] ); ?></h3>
                <?php endif; ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php woocommerce_order_review(); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
