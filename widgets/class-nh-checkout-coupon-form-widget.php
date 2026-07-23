<?php
/**
 * Widget Formulario de Cupón en Checkout (Checkout Coupon Form)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Checkout_Coupon_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_checkout_coupon_form_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Cupón de Checkout', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-ticket';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'coupon', 'cupon', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-checkout-widget', 'wc-checkout' ];
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! wc_coupons_enabled() ) {
            return;
        }

        ?>
        <div class="nh-checkout-widget nh-coupon-form-container">
            <?php woocommerce_checkout_coupon_form(); ?>
        </div>
        <?php
    }
}
