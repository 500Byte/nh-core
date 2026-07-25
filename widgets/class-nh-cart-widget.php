<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * NH Cart Widget — Design System v1 Norma Hana
 */
class NH_Cart_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_cart_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Carrito (Norma Hana v2)', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'cart', 'carrito', 'woocommerce', 'checkout', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-cart-widget' ];
    }

    /**
     * Force Elementor editor to always use PHP render() instead of the
     * JavaScript content_template(). This ensures the editor preview always
     * matches the frontend for widgets with dynamic WooCommerce content.
     *
     * @return bool
     */
    protected function is_dynamic_content(): bool {
        return true;
    }


    protected function register_controls() {

        // ─── CONTENIDO: GENERAL & EDITOR ───────────────────────
        $this->start_controls_section(
            'general_section',
            [
                'label' => esc_html__( 'General / Vista Previa', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_header_steps',
            [
                'label' => esc_html__( 'Mostrar Encabezado & Pasos', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'preview_empty_state',
            [
                'label' => esc_html__( 'Vista Previa Estado Vacío', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => '',
                'description' => esc_html__( 'Fuerza la visualización del estado del carrito vacío en el editor de Elementor.', 'nh-core' ),
            ]
        );

        $this->end_controls_section();

        // ─── CONTENIDO: BARRA DE ENVÍO GRATIS ──────────────────
        $this->start_controls_section(
            'free_shipping_section',
            [
                'label' => esc_html__( 'Barra de Envío Gratis', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_free_shipping',
            [
                'label' => esc_html__( 'Mostrar Barra de Envío Gratis', 'nh-core' ),
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
                'label' => esc_html__( 'Umbral de Envío Gratis ($)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 150000,
                'min' => 0,
                'step' => 1000,
                'condition' => [
                    'show_free_shipping' => 'yes',
                ],
                'description' => esc_html__( 'Deja en 0 para usar el valor configurado en WooCommerce.', 'nh-core' ),
            ]
        );

        $this->add_control(
            'free_shipping_progress',
            [
                'label' => esc_html__( 'Mensaje de Progreso', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Te faltan {missing} para envío gratis', 'nh-core' ),
                'placeholder' => esc_html__( 'Usa {missing} y {threshold}', 'nh-core' ),
                'condition' => [
                    'show_free_shipping' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'free_shipping_success',
            [
                'label' => esc_html__( 'Mensaje de Éxito', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( '¡Felicidades! Tienes envío gratis', 'nh-core' ),
                'condition' => [
                    'show_free_shipping' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ─── CONTENIDO: TABLA DE PRODUCTOS ─────────────────────
        $this->start_controls_section(
            'products_section',
            [
                'label' => esc_html__( 'Tabla de Productos', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_product_image',
            [
                'label' => esc_html__( 'Mostrar Imagen', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_variations',
            [
                'label' => esc_html__( 'Mostrar Variaciones', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_coupon_form',
            [
                'label' => esc_html__( 'Mostrar Sección de Cupón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'backorder_text',
            [
                'label' => esc_html__( 'Texto de Reserva / Backorder', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Disponible en reserva', 'nh-core' ),
                'placeholder' => esc_html__( 'Ej: Disponible en reserva', 'nh-core' ),
                'description' => esc_html__( 'Texto que se muestra en productos con backorder habilitado.', 'nh-core' ),
            ]
        );

        $this->end_controls_section();

        // ─── CONTENIDO: BOTÓN DE CHECKOUT & RESUMEN ────────────
        $this->start_controls_section(
            'checkout_section',
            [
                'label' => esc_html__( 'Resumen & Finalizar Compra', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'checkout_button_text',
            [
                'label' => esc_html__( 'Texto del Botón Checkout', 'nh-core' ),
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

        $this->add_control(
            'show_trust_badges',
            [
                'label' => esc_html__( 'Mostrar Sellos de Confianza', 'nh-core' ),
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
                    'value' => 'eicon-truck',
                    'library' => 'eicons',
                ],
            ]
        );

        $repeater->add_control(
            'badge_text',
            [
                'label' => esc_html__( 'Texto', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Envío seguro', 'nh-core' ),
            ]
        );

        $this->add_control(
            'trust_badges_list',
            [
                'label' => esc_html__( 'Lista de Sellos de Confianza', 'nh-core' ),
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

        // ─── ESTILOS: NORMAS HANA TOKENS ───────────────────────
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__( 'Colores & Estilos DS', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'sand_bar_bg',
            [
                'label' => esc_html__( 'Fondo Barra Envío Gratis', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F5F0D8',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-free-shipping' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sand_bar_fill',
            [
                'label' => esc_html__( 'Relleno Envío Gratis', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#C2B280',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-free-shipping-bar' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'checkout_btn_bg',
            [
                'label' => esc_html__( 'Fondo Botón Checkout', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#202020',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-checkout-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_free_shipping_threshold() {
        $settings = $this->get_settings_for_display();
        $custom_threshold = floatval( $settings['free_shipping_threshold'] );
        if ( $custom_threshold > 0 ) {
            return $custom_threshold;
        }

        if ( ! function_exists( 'WC' ) ) {
            return 150000;
        }

        $zones = \WC_Shipping_Zones::get_zones();
        foreach ( $zones as $zone ) {
            foreach ( $zone['shipping_methods'] as $method ) {
                if ( 'free_shipping' === $method->id && 'yes' === $method->enabled ) {
                    $min_amount = floatval( $method->min_amount );
                    if ( $min_amount > 0 ) {
                        return $min_amount;
                    }
                }
            }
        }
        return 150000;
    }

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $is_editor_preview = 'yes' === $settings['preview_empty_state'];

        if ( ! is_admin() && ( WC()->cart->is_empty() || $is_editor_preview ) ) {
            wc_get_template( 'cart/cart-empty.php' );
            return;
        }

        // Registrar filtros dinámicos del widget para las plantillas de carrito
        add_filter( 'nh_cart_payment_pills', function() use ( $settings ) {
            return [ 'Visa', 'Mastercard', 'PSE', 'Wompi', 'Addi', 'Efectivo' ];
        } );

        ?>
        <div class="nh-cart-widget">
            <?php 
            if ( function_exists( 'woocommerce_output_all_notices' ) ) {
                woocommerce_output_all_notices();
            }
            ?>

            <?php if ( 'yes' === $settings['show_header_steps'] ) : ?>
            <div class="nh-cart-header">
                <div class="nh-cart-checkout-steps">
                    <span class="nh-cart-step active"><span class="nh-cart-step-num">1</span> <?php esc_html_e( 'Carrito', 'nh-core' ); ?></span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step"><span class="nh-cart-step-num">2</span> <?php esc_html_e( 'Envío', 'nh-core' ); ?></span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step"><span class="nh-cart-step-num">3</span> <?php esc_html_e( 'Pago', 'nh-core' ); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // Barra de envío gratis
            $threshold = $this->get_free_shipping_threshold();
            $cart_total = WC()->cart->get_subtotal();
            if ( $threshold > 0 && $cart_total < $threshold ) {
                $remaining = $threshold - $cart_total;
                $progress = min( 100, ( $cart_total / $threshold ) * 100 );
                ?>
                <div class="nh-shipping-bar">
                    <div class="nh-shipping-bar__track">
                        <div class="nh-shipping-bar__fill" style="width: <?php echo esc_attr( $progress ); ?>%;"></div>
                    </div>
                    <div class="nh-shipping-bar__text">
                        <?php
                        printf(
                           /* translators: %s: remaining amount */
                            esc_html__( 'Te faltan %s para envío gratis', 'nh-core' ),
                            '<span class="amount">' . wp_strip_all_tags( wc_price( $remaining ) ) . '</span>'
                        );
                        ?>
                    </div>
                </div>
                <?php
            } elseif ( $threshold > 0 && $cart_total >= $threshold ) {
                ?>
                <div class="nh-shipping-bar nh-shipping-bar--complete">
                    <div class="nh-shipping-bar__track">
                        <div class="nh-shipping-bar__fill" style="width: 100%;"></div>
                    </div>
                    <div class="nh-shipping-bar__text">
                        <?php esc_html_e( '¡Envío gratis desbloqueado!', 'nh-core' ); ?>
                    </div>
                </div>
                <?php
            }
            ?>

            <?php 
            // Backorder text from Elementor settings
            $backorder_text = $settings['backorder_text'] ?? '';
            if ( ! empty( $backorder_text ) ) {
                add_filter( 'woocommerce_cart_item_backorder_notification', function( $html, $product_id ) use ( $backorder_text ) {
                    return '<p class="backorder_notification">' . esc_html( $backorder_text ) . '</p>';
                }, 10, 2 );
            }

            // Delegar a la plantilla modular del carrito
            wc_get_template( 'cart/cart.php' ); 
            ?>

        </div>
        <?php
    }

    private function render_empty_cart() {
        ?>
        <div class="nh-cart-widget">
            <div class="nh-cart-empty">
                <div class="nh-cart-empty-icon">
                    <i class="eicon-cart-medium"></i>
                </div>
                <h2><?php esc_html_e( 'Tu carrito está vacío', 'nh-core' ); ?></h2>
                <p><?php esc_html_e( 'Explora nuestra colección y descubre piezas exclusivas.', 'nh-core' ); ?></p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="nh-btn nh-btn-primary nh-cart-empty-btn">
                    <?php esc_html_e( 'Explorar la Tienda', 'nh-core' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Live Preview Template for Elementor Editor
     */
    protected function content_template() {
        ?>
        <#
        var showHeader = settings.show_header_steps === 'yes';
        var showFreeShipping = settings.show_free_shipping === 'yes';
        var showProductImage = settings.show_product_image === 'yes';
        var showVariations = settings.show_variations === 'yes';
        var showCoupon = settings.show_coupon_form === 'yes';
        var showTrust = settings.show_trust_badges === 'yes';
        var previewEmpty = settings.preview_empty_state === 'yes';

        if ( previewEmpty ) {
        #>
            <div class="nh-cart-widget">
                <div class="nh-cart-empty">
                    <div class="nh-cart-empty-icon"><i class="eicon-cart-medium"></i></div>
                    <h2>Tu carrito está vacío</h2>
                    <p>Explora nuestra colección y descubre piezas exclusivas.</p>
                    <a href="#" class="nh-cart-empty-btn">Explorar la Tienda</a>
                </div>
            </div>
        <# return; } #>

        <div class="nh-cart-widget">
            <# if ( showHeader ) { #>
            <div class="nh-cart-header">
                <div class="nh-cart-checkout-steps">
                    <span class="nh-cart-step active"><span class="nh-cart-step-num">1</span> Carrito</span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step"><span class="nh-cart-step-num">2</span> Envío</span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step"><span class="nh-cart-step-num">3</span> Pago</span>
                </div>
            </div>
            <# } #>

            <# if ( showFreeShipping ) { #>
            <div class="nh-shipping-bar">
                <div class="nh-shipping-bar__track">
                    <div class="nh-shipping-bar__fill" style="width: 70%;"></div>
                </div>
                <div class="nh-shipping-bar__text">
                    Te faltan <span class="amount">$45.000</span> para envío gratis
                </div>
            </div>
            <# } #>

            <div class="nh-cart-layout">
                <div class="nh-cart-products">
                    <div class="nh-cart-table-header">
                        <span>Producto</span>
                        <span>Precio</span>
                        <span>Cantidad</span>
                        <span>Subtotal</span>
                        <span></span>
                    </div>

                    <div class="nh-cart-item">
                        <div class="nh-cart-product-info">
                            <# if ( showProductImage ) { #>
                                <div class="nh-cart-product-img" style="background:#e0e0e0; display:flex; align-items:center; justify-content:center; color:#999;"><i class="eicon-image-bold"></i></div>
                            <# } #>
                            <div class="nh-cart-product-details">
                                <span class="nh-cart-product-name">Venture Bermuda</span>
                                <# if ( showVariations ) { #>
                                    <div class="nh-pill-group">
                                        <span class="nh-pill"><span class="nh-pill-label">Color:</span> <span class="nh-pill-value">Marfil</span></span>
                                        <span class="nh-pill"><span class="nh-pill-label">Talla:</span> <span class="nh-pill-value">M</span></span>
                                    </div>
                                <# } #>
                            </div>
                        </div>
                        <div class="nh-cart-product-price">$110.000</div>
                        <div class="nh-cart-product-qty">
                            <button class="nh-cart-qty-btn">-</button>
                            <input type="number" class="nh-cart-qty-input" value="1" readonly>
                            <button class="nh-cart-qty-btn">+</button>
                        </div>
                        <div class="nh-cart-product-subtotal">$110.000</div>
                        <button class="nh-cart-remove"><i class="eicon-close"></i></button>
                    </div>

                    <# if ( showCoupon ) { #>
                    <div class="nh-cart-coupon-section">
                        <div class="nh-coupon-box">
                            <input type="text" class="input-text" placeholder="Código de cupón">
                            <button class="button">Aplicar</button>
                        </div>
                    </div>
                    <# } #>
                </div>

                <div class="nh-cart-summary">
                    <h3>Resumen de Compra</h3>
                    <div class="nh-summary-row">
                        <span class="nh-summary-row__label">Subtotal</span>
                        <span class="nh-summary-row__value">$110.000</span>
                    </div>
                    <div class="nh-summary-row">
                        <span class="nh-summary-row__label">Envío</span>
                        <span class="nh-summary-row__value">$12.000</span>
                    </div>
                    <div class="nh-summary-row nh-summary-row--total">
                        <span class="nh-summary-row__label">Total</span>
                        <span class="nh-summary-row__value">$122.000</span>
                    </div>
                    <a href="#" class="nh-cart-checkout-btn">
                        <# if ( settings.checkout_button_icon && settings.checkout_button_icon.value ) { #>
                            <i class="{{ settings.checkout_button_icon.value }}"></i>
                        <# } #>
                        {{{ settings.checkout_button_text }}}
                    </a>
                    <# if ( showTrust && settings.trust_badges_list && settings.trust_badges_list.length ) { #>
                    <div class="nh-trust-box">
                        <div class="nh-trust-box__pills">
                            <# _.each( settings.trust_badges_list, function( badge ) { #>
                                <span class="nh-trust-box__pill">{{{ badge.badge_text }}}</span>
                            <# }); #>
                        </div>
                    </div>
                    <# } #>
                </div>
            </div>
        </div>
        <?php
    }
}
