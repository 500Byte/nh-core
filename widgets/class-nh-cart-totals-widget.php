<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Cart_Totals_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_cart_totals_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Totales del Carrito', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-cart-light';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'cart', 'totals', 'subtotal', 'checkout', 'woocommerce' ];
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
            'checkout_button_text',
            [
                'label' => esc_html__( 'Texto del Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Finalizar Compra', 'nh-core' ),
            ]
        );

        $this->add_control(
            'checkout_button_icon',
            [
                'label' => esc_html__( 'Icono del Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'eicon-arrow-right',
                    'library' => 'eicons',
                ],
            ]
        );

        $this->add_control(
            'custom_checkout_url',
            [
                'label' => esc_html__( 'URL de Checkout Personalizada', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://tusitio.com/checkout', 'nh-core' ),
            ]
        );

        $this->end_controls_section();

        // Estilos
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__( 'Tarjeta de Resumen', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'summary_bg',
            [
                'label' => esc_html__( 'Fondo Tarjeta', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-summary' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'checkout_btn_bg',
            [
                'label' => esc_html__( 'Fondo Botón Checkout', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-checkout-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        $cart = WC()->cart;
        $settings = $this->get_settings_for_display();

        if ( $cart->is_empty() ) {
            return;
        }

        ?>
        <div class="nh-cart-widget">
            <div class="nh-cart-summary">
                <?php do_action( 'woocommerce_before_cart_totals' ); ?>

                <h3><?php esc_html_e( 'Resumen de Compra', 'nh-core' ); ?></h3>
                
                <div class="nh-cart-summary-row">
                    <span><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
                    <span><?php wc_cart_totals_subtotal_html(); ?></span>
                </div>
                
                <?php if ( $cart->has_discount() ) : ?>
                <div class="nh-cart-summary-row nh-cart-discount">
                    <span><?php esc_html_e( 'Descuento', 'nh-core' ); ?></span>
                    <span>-<?php echo wp_kses_post( wc_price( $cart->get_discount_total() ) ); ?></span>
                </div>
                <?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
                <div class="nh-cart-coupon-applied">
                    <span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
                    <span><?php echo wp_kses_post( $coupon->get_amount_html() ); ?></span>
                    <button class="nh-cart-remove-coupon" data-coupon="<?php echo esc_attr( $code ); ?>" title="<?php esc_attr_e( 'Eliminar cupón', 'nh-core' ); ?>">×</button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if ( $cart->needs_shipping() ) : ?>
                    <?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

                    <div class="nh-cart-shipping-block">
                        <table class="nh-cart-shipping-table">
                            <tbody>
                                <?php if ( $cart->show_shipping() ) : ?>
                                    <?php wc_cart_totals_shipping_html(); ?>
                                <?php else : ?>
                                    <tr class="shipping">
                                        <th><?php esc_html_e( 'Envío', 'nh-core' ); ?></th>
                                        <td data-title="<?php esc_attr_e( 'Envío', 'nh-core' ); ?>">
                                            <?php woocommerce_shipping_calculator(); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
                <?php endif; ?>

                <?php foreach ( $cart->get_fees() as $fee ) : ?>
                <div class="nh-cart-summary-row nh-cart-fee">
                    <span><?php echo esc_html( $fee->name ); ?></span>
                    <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
                </div>
                <?php endforeach; ?>

                <?php
                if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) {
                    if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                        foreach ( $cart->get_tax_totals() as $code => $tax ) {
                            ?>
                            <div class="nh-cart-summary-row nh-cart-tax">
                                <span><?php echo esc_html( $tax->label ); ?></span>
                                <span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="nh-cart-summary-row nh-cart-tax">
                            <span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
                            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
                        </div>
                        <?php
                    }
                }
                ?>

                <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

                <div class="nh-cart-summary-row nh-cart-total">
                    <span><?php esc_html_e( 'Total', 'nh-core' ); ?></span>
                    <span><?php wc_cart_totals_order_total_html(); ?></span>
                </div>

                <?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

                <?php 
                $checkout_url = ! empty( $settings['custom_checkout_url']['url'] ) 
                    ? $settings['custom_checkout_url']['url'] 
                    : wc_get_checkout_url();
                ?>
                <a href="<?php echo esc_url( $checkout_url ); ?>" class="nh-cart-checkout-btn">
                    <?php if ( ! empty( $settings['checkout_button_icon']['value'] ) ) : ?>
                        <span class="nh-cart-checkout-icon">
                            <?php \Elementor\Icons_Manager::render_icon( $settings['checkout_button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php echo esc_html( $settings['checkout_button_text'] ); ?>
                </a>

                <?php do_action( 'woocommerce_after_cart_totals' ); ?>
            </div>
        </div>
        <?php
    }
}
