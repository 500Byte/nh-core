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
        return [ 'nh-cart-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-cart-widget' ];
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

        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        $settings = $this->get_settings_for_display();
        $cart = WC()->cart;
        $cart_items = $cart->get_cart();
        $cart_total = floatval( $cart->get_subtotal() );
        $threshold = $this->get_free_shipping_threshold();
        $missing = max( 0, $threshold - $cart_total );
        $percentage = ( $threshold > 0 ) ? min( 100, ( $cart_total / $threshold ) * 100 ) : 100;

        $is_editor_preview = \Elementor\Plugin::$instance->editor->is_edit_mode() && 'yes' === $settings['preview_empty_state'];

        if ( empty( $cart_items ) || $is_editor_preview ) {
            $this->render_empty_cart();
            return;
        }

        ?>
        <div class="nh-cart-widget">
            <?php 
            if ( function_exists( 'woocommerce_output_all_notices' ) ) {
                woocommerce_output_all_notices();
            }
            do_action( 'woocommerce_before_cart' );
            ?>

            <?php if ( 'yes' === $settings['show_header_steps'] ) : ?>
            <div class="nh-cart-header">
                <h1 class="nh-cart-brand-title"><?php esc_html_e( 'NORMA HANA', 'nh-core' ); ?></h1>
                <div class="nh-cart-checkout-steps">
                    <span class="nh-cart-step active">1. <?php esc_html_e( 'Carrito', 'nh-core' ); ?></span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step">2. <?php esc_html_e( 'Envío', 'nh-core' ); ?></span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step">3. <?php esc_html_e( 'Pago', 'nh-core' ); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( 'yes' === $settings['show_free_shipping'] ) : ?>
            <div class="nh-cart-free-shipping">
                <div class="nh-cart-free-shipping-track"></div>
                <div class="nh-cart-free-shipping-bar" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
                <div class="nh-cart-free-shipping-text">
                    <?php if ( $missing > 0 ) : ?>
                        <?php 
                        $message = str_replace( 
                            [ '{missing}', '{threshold}' ], 
                            [ wc_price( $missing ), wc_price( $threshold ) ], 
                            $settings['free_shipping_progress'] 
                        );
                        echo wp_kses_post( $message );
                        ?>
                    <?php else : ?>
                        <?php echo esc_html( $settings['free_shipping_success'] ); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="nh-cart-layout">
                <!-- Columna Izquierda: Productos -->
                <div class="nh-cart-products">
                    <?php do_action( 'woocommerce_before_cart_table' ); ?>

                    <div class="nh-cart-table-header">
                        <span><?php esc_html_e( 'Producto', 'nh-core' ); ?></span>
                        <span><?php esc_html_e( 'Precio', 'nh-core' ); ?></span>
                        <span><?php esc_html_e( 'Cantidad', 'nh-core' ); ?></span>
                        <span><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
                        <span></span>
                    </div>
                    
                    <?php foreach ( $cart_items as $cart_item_key => $cart_item ) : 
                        $product = $cart_item['data'];
                        if ( ! $product || ! $product->exists() ) {
                            continue;
                        }
                        $quantity = $cart_item['quantity'];
                        $product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
                        $is_sold_individually = $product->is_sold_individually();
                    ?>
                    <div class="nh-cart-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                        <div class="nh-cart-product-info">
                            <?php if ( 'yes' === $settings['show_product_image'] ) : ?>
                                <?php if ( $product_permalink ) : ?>
                                    <a href="<?php echo esc_url( $product_permalink ); ?>">
                                        <?php echo wp_kses_post( $product->get_image( 'thumbnail', [ 'class' => 'nh-cart-product-img' ] ) ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo wp_kses_post( $product->get_image( 'thumbnail', [ 'class' => 'nh-cart-product-img' ] ) ); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="nh-cart-product-details">
                                <span class="nh-cart-product-name">
                                    <?php if ( $product_permalink ) : ?>
                                        <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html( $product->get_title() ); ?>
                                    <?php endif; ?>
                                </span>
                                <?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
                                <?php if ( 'yes' === $settings['show_variations'] && ! empty( $cart_item['variation'] ) ) : ?>
                                    <div class="nh-cart-product-variation-pills">
                                        <?php foreach ( $cart_item['variation'] as $attr_key => $attr_value ) : 
                                            if ( '' === $attr_value ) continue;
                                            $taxonomy = str_replace( 'attribute_', '', $attr_key );
                                            $label = wc_attribute_label( $taxonomy, $product );
                                            $term = get_term_by( 'slug', $attr_value, $taxonomy );
                                            $display_val = $term ? $term->name : ucfirst( $attr_value );
                                        ?>
                                            <span class="nh-cart-variation-pill">
                                                <span class="nh-variation-label"><?php echo esc_html( $label ); ?>:</span>
                                                <span class="nh-variation-val"><?php echo esc_html( $display_val ); ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ( 'yes' === $settings['show_variations'] ) : ?>
                                    <div class="nh-cart-product-variation">
                                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $product->backorders_require_notification() && $product->is_on_backorder( $quantity ) ) : ?>
                                    <p class="backorder_notification"><?php esc_html_e( 'Disponible en reserva', 'nh-core' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="nh-cart-product-price">
                            <?php echo wp_kses_post( $product->get_price_html() ); ?>
                        </div>
                        
                        <div class="nh-cart-product-qty">
                            <?php if ( $is_sold_individually ) : ?>
                                <input type="number" class="nh-cart-qty-input" value="1" min="1" max="1" readonly disabled data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                            <?php else : ?>
                                <button class="nh-cart-qty-btn nh-cart-qty-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">-</button>
                                <input type="number" class="nh-cart-qty-input" value="<?php echo esc_attr( $quantity ); ?>" min="1" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                                <button class="nh-cart-qty-btn nh-cart-qty-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nh-cart-product-subtotal">
                            <?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $quantity ) ); ?>
                        </div>
                        
                        <button class="nh-cart-remove" data-key="<?php echo esc_attr( $cart_item_key ); ?>" title="<?php esc_attr_e( 'Eliminar producto', 'nh-core' ); ?>">
                            <i class="eicon-close"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>

                    <?php do_action( 'woocommerce_after_cart_table' ); ?>

                    <?php if ( 'yes' === $settings['show_coupon_form'] ) : ?>
                    <div class="nh-cart-coupon-section">
                        <div class="nh-cart-coupon">
                            <input type="text" class="nh-cart-coupon-input" placeholder="<?php esc_attr_e( 'Código de cupón', 'nh-core' ); ?>">
                            <button class="nh-cart-coupon-btn"><?php esc_html_e( 'Aplicar', 'nh-core' ); ?></button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Columna Derecha: Tarjeta de Resumen Sticky -->
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

                    <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

                    <div class="nh-cart-total">
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

                    <?php if ( 'yes' === $settings['show_trust_badges'] && ! empty( $settings['trust_badges_list'] ) ) : ?>
                    <div class="nh-cart-trust-badges">
                        <?php foreach ( $settings['trust_badges_list'] as $badge ) : ?>
                        <div class="nh-cart-badge-item">
                            <?php if ( ! empty( $badge['badge_icon']['value'] ) ) : ?>
                                <?php \Elementor\Icons_Manager::render_icon( $badge['badge_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                            <?php endif; ?>
                            <span><?php echo esc_html( $badge['badge_text'] ); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php do_action( 'woocommerce_after_cart_totals' ); ?>
                </div>
            </div>

            <div class="nh-cart-cross-sells">
                <?php 
                if ( function_exists( 'woocommerce_cross_sell_display' ) ) {
                    woocommerce_cross_sell_display( 4, 4 );
                }
                ?>
            </div>
            
            <?php do_action( 'woocommerce_after_cart' ); ?>
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
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="nh-cart-empty-btn">
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
                <h1 class="nh-cart-brand-title">NORMA HANA</h1>
                <div class="nh-cart-checkout-steps">
                    <span class="nh-cart-step active">1. Carrito</span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step">2. Envío</span>
                    <span class="nh-cart-step-separator">→</span>
                    <span class="nh-cart-step">3. Pago</span>
                </div>
            </div>
            <# } #>

            <# if ( showFreeShipping ) { #>
            <div class="nh-cart-free-shipping">
                <div class="nh-cart-free-shipping-track"></div>
                <div class="nh-cart-free-shipping-bar" style="width: 70%;"></div>
                <div class="nh-cart-free-shipping-text">
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
                                    <div class="nh-cart-product-variation">Marfil, M</div>
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
                        <div class="nh-cart-coupon">
                            <input type="text" class="nh-cart-coupon-input" placeholder="Código de cupón">
                            <button class="nh-cart-coupon-btn">Aplicar</button>
                        </div>
                    </div>
                    <# } #>
                </div>

                <div class="nh-cart-summary">
                    <h3>Resumen de Compra</h3>
                    <div class="nh-cart-summary-row">
                        <span>Subtotal</span>
                        <span>$110.000</span>
                    </div>
                    <div class="nh-cart-summary-row">
                        <span>Envío</span>
                        <span>$12.000</span>
                    </div>
                    <div class="nh-cart-total">
                        <span>Total</span>
                        <span>$122.000</span>
                    </div>
                    <a href="#" class="nh-cart-checkout-btn">
                        <# if ( settings.checkout_button_icon && settings.checkout_button_icon.value ) { #>
                            <i class="{{ settings.checkout_button_icon.value }}"></i>
                        <# } #>
                        {{{ settings.checkout_button_text }}}
                    </a>
                    <# if ( showTrust && settings.trust_badges_list && settings.trust_badges_list.length ) { #>
                    <div class="nh-cart-trust-badges">
                        <# _.each( settings.trust_badges_list, function( badge ) { #>
                            <div class="nh-cart-badge-item">
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
        <?php
    }
}
