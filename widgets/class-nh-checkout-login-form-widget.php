<?php
/**
 * Widget Formulario de Login en Checkout (Checkout Login Form)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Checkout_Login_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_checkout_login_form_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Login de Checkout', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-lock-user';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'login', 'iniciar sesion', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-checkout-widget', 'wc-checkout' ];
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
            return;
        }

        ?>
        <div class="nh-checkout-widget nh-login-form-container">
            <?php woocommerce_login_form(); ?>
        </div>
        <?php
    }
}
