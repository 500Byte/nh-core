<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Coupon_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_coupon_form_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Formulario de Cupón', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-price-tag';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'coupon', 'cupón', 'descuento', 'cart', 'woocommerce' ];
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
            'placeholder_text',
            [
                'label' => esc_html__( 'Texto Placeholder', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Código de cupón', 'nh-core' ),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => esc_html__( 'Texto del Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Aplicar', 'nh-core' ),
            ]
        );

        $this->end_controls_section();

        // Estilos
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__( 'Estilos del Formulario', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_bg',
            [
                'label' => esc_html__( 'Fondo Input', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-coupon-input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg',
            [
                'label' => esc_html__( 'Fondo Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-coupon-btn' => 'background-color: {{VALUE}};',
                ],
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

        ?>
        <div class="nh-cart-widget">
            <div class="nh-cart-coupon">
                <input type="text" class="nh-cart-coupon-input" placeholder="<?php echo esc_attr( $settings['placeholder_text'] ); ?>">
                <button class="nh-cart-coupon-btn"><?php echo esc_html( $settings['button_text'] ); ?></button>
            </div>
        </div>
        <?php
    }
}
