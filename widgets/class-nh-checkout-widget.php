<?php
/**
 * Widget de Finalización de Compra (Checkout) Norma Hana v2
 *
 * Clasificación Elementor: NH Core / Ecommerce
 * Soporta layout de 2 columnas, cabecera con pasos, resumen de pedido,
 * pasarelas de pago y sellos de confianza configurables.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NH_Checkout_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_checkout_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Checkout v2', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-checkout';
    }

    public function get_categories() {
        return [ 'nh-core-category' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'pago', 'finalizar compra', 'norma hana', 'woocommerce' ];
    }

    public function get_style_depends() {
        return [ 'nh-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-checkout-widget', 'wc-checkout' ];
    }

    protected function register_controls() {

        // ─── SECCIÓN: CABECERA & PASOS ────────────────────────
        $this->start_controls_section(
            'section_header_steps',
            [
                'label' => esc_html__( 'Cabecera & Pasos de Compra', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_header_steps',
            [
                'label' => esc_html__( 'Mostrar Pasos de Compra', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'brand_title',
            [
                'label' => esc_html__( 'Título de Marca', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'condition' => [
                    'show_header_steps' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'step_1_text',
            [
                'label' => esc_html__( 'Texto Paso 1', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '1 Carrito',
                'condition' => [
                    'show_header_steps' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'step_2_text',
            [
                'label' => esc_html__( 'Texto Paso 2', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '2 Datos y Envío',
                'condition' => [
                    'show_header_steps' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'step_3_text',
            [
                'label' => esc_html__( 'Texto Paso 3', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '3 Confirmación',
                'condition' => [
                    'show_header_steps' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ─── SECCIÓN: CRO & CONVERSIÓN (RESERVA & ENVÍO GRATIS) ─
        $this->start_controls_section(
            'section_cro_features',
            [
                'label' => esc_html__( 'Funcionalidades CRO (Conversión)', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_reservation_timer',
            [
                'label' => esc_html__( 'Mostrar Banner Reserva de Stock', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'reservation_minutes',
            [
                'label' => esc_html__( 'Minutos de Reserva', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 1,
                'max' => 60,
                'condition' => [
                    'show_reservation_timer' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_free_shipping_bar',
            [
                'label' => esc_html__( 'Mostrar Barra Progreso Envío Gratis', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'free_shipping_threshold',
            [
                'label' => esc_html__( 'Monto Mínimo Envío Gratis ($)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 280000,
                'condition' => [
                    'show_free_shipping_bar' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ─── SECCIÓN: SOPORTE POR WHATSAPP ─────────────────────
        $this->start_controls_section(
            'section_whatsapp_support',
            [
                'label' => esc_html__( 'Soporte Directo WhatsApp', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_whatsapp_support',
            [
                'label' => esc_html__( 'Mostrar Tarjeta WhatsApp', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'whatsapp_number',
            [
                'label' => esc_html__( 'Número WhatsApp', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '573043510019',
                'condition' => [
                    'show_whatsapp_support' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'whatsapp_text',
            [
                'label' => esc_html__( 'Texto de Soporte', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( '¿Tienes dudas sobre tu compra o el pago? Habla con una asesora por WhatsApp.', 'nh-core' ),
                'condition' => [
                    'show_whatsapp_support' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ─── SECCIÓN: TITULOS DE FORMULARIO ────────────────────
        $this->start_controls_section(
            'section_form_titles',
            [
                'label' => esc_html__( 'Títulos de Secciones', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'billing_title',
            [
                'label' => esc_html__( 'Título Detalles de Facturación', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Detalles de Facturación', 'nh-core' ),
            ]
        );

        $this->add_control(
            'shipping_title',
            [
                'label' => esc_html__( 'Título Dirección de Envío', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Enviar a una dirección diferente', 'nh-core' ),
            ]
        );

        $this->add_control(
            'order_review_title',
            [
                'label' => esc_html__( 'Título Resumen de Pedido', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Tu Pedido', 'nh-core' ),
            ]
        );

        $this->end_controls_section();

        // ─── SECCIÓN: SELLOS DE CONFIANZA ──────────────────────
        $this->start_controls_section(
            'section_trust_badges',
            [
                'label' => esc_html__( 'Sellos de Confianza & Seguridad SSL', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'ssl_trust_text',
            [
                'label' => esc_html__( 'Texto Seguridad SSL', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Pago 100% Seguro con Cifrado SSL', 'nh-core' ),
            ]
        );

        $this->add_control(
            'payment_methods_pills',
            [
                'label' => esc_html__( 'Badges de Pasarelas (Separados por coma)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Visa, Mastercard, PSE, Wompi, Addi, Efectivo',
            ]
        );

        $this->add_control(
            'show_trust_badges',
            [
                'label' => esc_html__( 'Mostrar Garantías Adicionales', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'badge_icon',
            [
                'label' => esc_html__( 'Icono', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'eicon-lock-user',
                    'library' => 'eicons',
                ],
            ]
        );

        $repeater->add_control(
            'badge_text',
            [
                'label' => esc_html__( 'Texto', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Pago 100% Seguro', 'nh-core' ),
            ]
        );

        $this->add_control(
            'trust_badges_list',
            [
                'label' => esc_html__( 'Lista de Garantías', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'badge_icon' => [ 'value' => 'eicon-truck', 'library' => 'eicons' ],
                        'badge_text' => esc_html__( 'Envío seguro', 'nh-core' ),
                    ],
                    [
                        'badge_icon' => [ 'value' => 'eicon-sync', 'library' => 'eicons' ],
                        'badge_text' => esc_html__( 'Devolución fácil', 'nh-core' ),
                    ],
                    [
                        'badge_icon' => [ 'value' => 'eicon-lock-user', 'library' => 'eicons' ],
                        'badge_text' => esc_html__( 'Pago protegido', 'nh-core' ),
                    ],
                ],
                'title_field' => '{{{ badge_text }}}',
                'condition' => [
                    'show_trust_badges' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ─── ESTILOS ──────────────────────────────────────────
        $this->start_controls_section(
            'section_style_checkout',
            [
                'label' => esc_html__( 'Colores & Estilos DS', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'place_order_btn_bg',
            [
                'label' => esc_html__( 'Fondo Botón Realizar Pedido', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#18181B',
                'selectors' => [
                    '{{WRAPPER}} #place_order, {{WRAPPER}} .nh-checkout-place-order-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'place_order_btn_hover_bg',
            [
                'label' => esc_html__( 'Fondo Hover Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#27272A',
                'selectors' => [
                    '{{WRAPPER}} #place_order:hover, {{WRAPPER}} .nh-checkout-place-order-btn:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'accent_color',
            [
                'label' => esc_html__( 'Color de Acento (Bordes & Selección)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#18181B',
                'selectors' => [
                    '{{WRAPPER}} .wc_payment_methods li.wc_payment_method.active' => 'border-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .nh-checkout-step.active' => 'color: {{VALUE}} !important; font-weight: 700;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->checkout ) {
            return;
        }

        $checkout = WC()->checkout();
        $settings = $this->get_settings_for_display();

        // Registrar filtros dinámicos del widget para las plantillas de checkout
        add_filter( 'nh_checkout_ssl_trust_text', function() use ( $settings ) {
            return ! empty( $settings['ssl_trust_text'] ) ? $settings['ssl_trust_text'] : __( 'Pago 100% Seguro con Cifrado SSL', 'woocommerce' );
        } );

        add_filter( 'nh_checkout_payment_pills', function() use ( $settings ) {
            if ( ! empty( $settings['payment_methods_pills'] ) ) {
                return array_map( 'trim', explode( ',', $settings['payment_methods_pills'] ) );
            }
            return [ 'Visa', 'Mastercard', 'PSE', 'Wompi', 'Addi', 'Efectivo' ];
        } );

        add_filter( 'nh_checkout_free_shipping_threshold', function() use ( $settings ) {
            if ( isset( $settings['show_free_shipping_bar'] ) && 'yes' === $settings['show_free_shipping_bar'] ) {
                return ! empty( $settings['free_shipping_threshold'] ) ? (float) $settings['free_shipping_threshold'] : 280000;
            }
            return 0;
        } );

        // Si el carrito está vacío en la página pública
        if ( ! is_admin() && WC()->cart->is_empty() ) {
            ?>
            <div class="nh-checkout-widget">
                <div class="woocommerce-info">
                    <?php esc_html_e( 'Tu carrito está vacío. Añade productos antes de finalizar la compra.', 'nh-core' ); ?>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button wc-backward">
                        <?php esc_html_e( 'Ir a la tienda', 'nh-core' ); ?>
                    </a>
                </div>
            </div>
            <?php
            return;
        }

        // Asegurar que el checkout calcule totales
        WC()->cart->calculate_totals();
        ?>
        <div class="nh-checkout-widget">
            
            <?php if ( 'yes' === $settings['show_header_steps'] ) : ?>
            <div class="nh-checkout-header">
                <div class="nh-checkout-steps">
                    <span class="nh-checkout-step"><span class="nh-checkout-step-num">1</span> <?php echo esc_html( ! empty( $settings['step_1_text'] ) ? $settings['step_1_text'] : 'Carrito' ); ?></span>
                    <span class="nh-checkout-step-separator">→</span>
                    <span class="nh-checkout-step active"><span class="nh-checkout-step-num">2</span> <?php echo esc_html( ! empty( $settings['step_2_text'] ) ? $settings['step_2_text'] : 'Datos y Envío' ); ?></span>
                    <span class="nh-checkout-step-separator">→</span>
                    <span class="nh-checkout-step"><span class="nh-checkout-step-num">3</span> <?php echo esc_html( ! empty( $settings['step_3_text'] ) ? $settings['step_3_text'] : 'Confirmación' ); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( 'yes' === $settings['show_reservation_timer'] ) : ?>
            <div class="nh-checkout-reservation-timer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span><?php esc_html_e( '¡Tus productos están reservados por', 'nh-core' ); ?> <strong id="nh_checkout_timer_count"><?php echo (int) ( ! empty( $settings['reservation_minutes'] ) ? $settings['reservation_minutes'] : 15 ); ?>:00</strong> <?php esc_html_e( 'minutos!', 'nh-core' ); ?></span>
            </div>
            <?php endif; ?>

            <?php
            // Formularios de login y cupón de WooCommerce
            do_action( 'woocommerce_before_checkout_form', $checkout );

            // Si se requiere registro o inicio de sesión
            if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
                echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Debes iniciar sesión para finalizar la compra.', 'woocommerce' ) ) );
                return;
            }
            ?>

            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

                <div class="nh-checkout-layout">
                    
                    <!-- Columna Izquierda: Datos del Cliente (Billing & Shipping) -->
                    <div class="nh-checkout-form-column">
                        
                        <?php if ( $checkout->get_checkout_fields() ) : ?>

                            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                            <div class="nh-checkout-card" id="customer_details">
                                <h3><span class="nh-checkout-step-num">1</span> <?php echo esc_html( $settings['billing_title'] ); ?></h3>
                                <?php do_action( 'woocommerce_checkout_billing' ); ?>

                                <?php if ( WC()->cart->needs_shipping_address() ) : ?>
                                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--nh-border);">
                                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

                        <?php endif; ?>

                    </div>

                    <!-- Columna Derecha: Resumen de Pedido & Pasarelas de Pago -->
                    <div class="nh-checkout-order-column">

                        <div class="nh-checkout-card">
                            <h3><span class="nh-checkout-step-num">2</span> <?php echo esc_html( $settings['order_review_title'] ); ?></h3>

                            <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
                            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

                            <?php if ( 'yes' === $settings['show_whatsapp_support'] && ! empty( $settings['whatsapp_number'] ) ) : ?>
                            <div class="nh-checkout-whatsapp-card">
                                <div class="nh-wa-card-left">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                    <span><?php echo esc_html( $settings['whatsapp_text'] ); ?></span>
                                </div>
                                <a href="https://wa.me/<?php echo esc_attr( preg_replace('/[^0-9]/', '', $settings['whatsapp_number']) ); ?>" target="_blank" rel="noopener noreferrer" class="nh-wa-card-btn">
                                    <?php esc_html_e( 'Escribir', 'nh-core' ); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if ( 'yes' === $settings['show_trust_badges'] && ! empty( $settings['trust_badges_list'] ) ) : ?>
                            <div class="nh-checkout-trust-badges">
                                <?php foreach ( $settings['trust_badges_list'] as $badge ) : ?>
                                <div class="nh-checkout-badge-item">
                                    <?php if ( ! empty( $badge['badge_icon']['value'] ) ) : ?>
                                        <?php \Elementor\Icons_Manager::render_icon( $badge['badge_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                    <?php endif; ?>
                                    <span><?php echo esc_html( $badge['badge_text'] ); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </form>

            <?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var showHeader = settings.show_header_steps === 'yes';
        var showTrust  = settings.show_trust_badges === 'yes';
        #>
        <div class="nh-checkout-widget">
            <# if ( showHeader ) { #>
            <div class="nh-checkout-header">
                <h1 class="nh-checkout-brand-title">{{{ settings.brand_title }}}</h1>
                <div class="nh-checkout-steps">
                    <span class="nh-checkout-step"><span class="nh-checkout-step-num">1</span> Carrito</span>
                    <span class="nh-checkout-step-separator">→</span>
                    <span class="nh-checkout-step active"><span class="nh-checkout-step-num">2</span> Datos y Envío</span>
                    <span class="nh-checkout-step-separator">→</span>
                    <span class="nh-checkout-step"><span class="nh-checkout-step-num">3</span> Confirmación</span>
                </div>
            </div>
            <# } #>

            <div class="nh-checkout-layout">
                <!-- Columna Izquierda: Vista Previa Formulario -->
                <div class="nh-checkout-form-column">
                    <div class="nh-checkout-card">
                        <h3>{{{ settings.billing_title }}}</h3>
                        <div class="form-row">
                            <label>Nombre <span class="required">*</span></label>
                            <input type="text" class="input-text" placeholder="Tu nombre">
                        </div>
                        <div class="form-row">
                            <label>Apellidos <span class="required">*</span></label>
                            <input type="text" class="input-text" placeholder="Tus apellidos">
                        </div>
                        <div class="form-row">
                            <label>Dirección de la calle <span class="required">*</span></label>
                            <input type="text" class="input-text" placeholder="Número y nombre de la calle">
                        </div>
                        <div class="form-row">
                            <label>Teléfono <span class="required">*</span></label>
                            <input type="text" class="input-text" placeholder="Teléfono de contacto">
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Vista Previa Resumen -->
                <div class="nh-checkout-order-column">
                    <div class="nh-checkout-card">
                        <h3>{{{ settings.order_review_title }}}</h3>
                        <table class="nh-checkout-order-review-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="product-name">Venture <strong class="product-quantity">× 2</strong></td>
                                    <td class="product-total">$ 220.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Subtotal</th>
                                    <td>$ 220.000</td>
                                </tr>
                                <tr>
                                    <th>Envío</th>
                                    <td>Recogida local: Gratis</td>
                                </tr>
                                <tr class="order-total">
                                    <th>Total</th>
                                    <td>$ 220.000</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="woocommerce-checkout-payment">
                            <ul class="wc_payment_methods">
                                <li>
                                    <label><input type="radio" checked> Transferencia bancaria directa / Nequi</label>
                                    <div class="payment_box">Realiza tu pago directamente en nuestra cuenta bancaria.</div>
                                </li>
                            </ul>
                            <button class="nh-checkout-place-order-btn">REALIZAR EL PEDIDO</button>
                        </div>

                        <# if ( showTrust && settings.trust_badges_list && settings.trust_badges_list.length ) { #>
                        <div class="nh-checkout-trust-badges">
                            <# _.each( settings.trust_badges_list, function( badge ) { #>
                                <div class="nh-checkout-badge-item">
                                    <# if ( badge.badge_icon && badge.badge_icon.value ) { #>
                                        <i class="{{ badge.badge_icon.value }}"></i>
                                    <# } #>
                                    <span>{{{ badge.badge_text }}}</span>
                                </div>
                            <# }); #>
                        </div>
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
