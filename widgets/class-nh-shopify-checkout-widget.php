<?php
/**
 * Widget Checkout Estilo Shopify (Shopify-Style One Page Checkout)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Shopify_Checkout_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_shopify_checkout_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Shopify Checkout', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-shopping-cart';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'checkout', 'shopify', 'one page checkout', 'pago rapido', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-shopify-checkout-widget' ];
    }

    public function get_script_depends() {
        return [ 'nh-shopify-checkout-widget', 'wc-checkout' ];
    }

    protected function register_controls() {

        // ─── CABECERA & MARCA ─────────────────────────────────
        $this->start_controls_section(
            'section_brand_header',
            [
                'label' => esc_html__( 'Marca & Cabecera', 'nh-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'brand_title',
            [
                'label'   => esc_html__( 'Nombre de Marca', 'nh-core' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'NORMA HANA',
            ]
        );

        $this->add_control(
            'show_express_checkout',
            [
                'label'        => esc_html__( 'Mostrar Express Checkout', 'nh-core' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Sí', 'nh-core' ),
                'label_off'    => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();

        // ─── ESTILOS ──────────────────────────────────────────
        $this->start_controls_section(
            'section_style_shopify',
            [
                'label' => esc_html__( 'Colores & Estilos', 'nh-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'submit_btn_bg',
            [
                'label'     => esc_html__( 'Fondo Botón Pagar', 'nh-core' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#202020',
                'selectors' => [
                    '{{WRAPPER}} .nh-shopify-submit-btn' => 'background-color: {{VALUE}};',
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
        $cart = WC()->cart;

        // Si el carrito está vacío en frontend público
        if ( ! is_admin() && $cart->is_empty() ) {
            ?>
            <div class="nh-shopify-checkout-wrapper">
                <div class="woocommerce-info" style="margin: 40px; padding: 20px;">
                    <?php esc_html_e( 'Tu carrito está vacío. Añade productos antes de finalizar la compra.', 'nh-core' ); ?>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button wc-backward">
                        <?php esc_html_e( 'Ir a la tienda', 'nh-core' ); ?>
                    </a>
                </div>
            </div>
            <?php
            return;
        }

        $cart->calculate_totals();
        ?>
        <div class="nh-shopify-checkout-wrapper">
            
            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
                
                <div class="nh-shopify-checkout-grid">
                    
                    <!-- Columna Izquierda: Información de Compra -->
                    <div class="nh-shopify-main-col">
                        
                        <div class="nh-shopify-logo-bar">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nh-shopify-brand-name">
                                <?php echo esc_html( $settings['brand_title'] ); ?>
                            </a>
                        </div>

                        <div class="nh-shopify-breadcrumbs">
                            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Carrito</a>
                            <span>›</span>
                            <span class="current">Información y Pago</span>
                        </div>

                        <?php if ( 'yes' === $settings['show_express_checkout'] ) : ?>
                        <div class="nh-shopify-express-checkout">
                            <div class="nh-shopify-express-title"><?php esc_html_e( 'Pago Rápido / Express', 'nh-core' ); ?></div>
                            <div class="nh-shopify-express-btns">
                                <button type="button" class="nh-shopify-express-btn nequi">Nequi</button>
                                <button type="button" class="nh-shopify-express-btn bancolombia">Bancolombia</button>
                                <button type="button" class="nh-shopify-express-btn wompi">Wompi</button>
                            </div>
                        </div>

                        <div class="nh-shopify-divider">
                            <span><?php esc_html_e( 'O completa los datos a continuación', 'nh-core' ); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>

                        <!-- Información de Contacto & Facturación -->
                        <div class="nh-shopify-section">
                            <div class="nh-shopify-section-title">
                                <span><?php esc_html_e( 'Información de Contacto & Envío', 'nh-core' ); ?></span>
                                <?php if ( ! is_user_logged_in() ) : ?>
                                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( '¿Ya tienes cuenta? Iniciar sesión', 'nh-core' ); ?></a>
                                <?php endif; ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_billing' ); ?>

                            <?php if ( $cart->needs_shipping_address() ) : ?>
                                <div style="margin-top: 20px;">
                                    <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pasarelas de Pago -->
                        <div class="nh-shopify-section" style="margin-top: 16px;">
                            <div class="nh-shopify-section-title">
                                <span><?php esc_html_e( 'Método de Pago', 'nh-core' ); ?></span>
                            </div>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <?php woocommerce_checkout_payment(); ?>
                            </div>
                        </div>

                    </div>

                    <!-- Columna Derecha: Sidebar Resumen Estilo Shopify (Gris Claro) -->
                    <div class="nh-shopify-sidebar-col">
                        
                        <!-- Lista de Productos -->
                        <div class="nh-shopify-product-list">
                            <?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) : 
                                $_product = $cart_item['data'];
                                if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
                            ?>
                            <div class="nh-shopify-product-item">
                                <div class="nh-shopify-thumb-wrapper">
                                    <?php echo $_product->get_image( 'thumbnail' ); ?>
                                    <span class="nh-shopify-qty-badge"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
                                </div>
                                <div class="nh-shopify-product-info">
                                    <span class="nh-shopify-product-title"><?php echo esc_html( $_product->get_title() ); ?></span>
                                    <?php if ( ! empty( $cart_item['variation'] ) ) : ?>
                                        <span class="nh-shopify-product-meta">
                                            <?php echo esc_html( wc_get_formatted_cart_item_data( $cart_item, true ) ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="nh-shopify-product-price">
                                    <?php echo WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Cupón de Descuento -->
                        <?php if ( wc_coupons_enabled() ) : ?>
                        <div class="nh-shopify-coupon-box">
                            <input type="text" class="nh-shopify-coupon-input" placeholder="<?php esc_attr_e( 'Código de descuento', 'nh-core' ); ?>">
                            <button type="button" class="nh-shopify-coupon-btn"><?php esc_html_e( 'Usar', 'nh-core' ); ?></button>
                        </div>
                        <?php endif; ?>

                        <!-- Subtotales & Total -->
                        <div class="nh-shopify-summary-rows">
                            <div class="nh-shopify-summary-row">
                                <span><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
                                <span><?php wc_cart_totals_subtotal_html(); ?></span>
                            </div>

                            <div class="nh-shopify-summary-row">
                                <span><?php esc_html_e( 'Envío', 'nh-core' ); ?></span>
                                <span><?php wc_cart_totals_shipping_html(); ?></span>
                            </div>

                            <?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
                            <div class="nh-shopify-summary-row">
                                <span><?php esc_html_e( 'Descuento', 'nh-core' ); ?> (<?php echo esc_html( $code ); ?>)</span>
                                <span>-<?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                            </div>
                            <?php endforeach; ?>

                            <div class="nh-shopify-total-row">
                                <span><?php esc_html_e( 'Total', 'nh-core' ); ?></span>
                                <span class="nh-shopify-total-price"><?php wc_cart_totals_order_total_html(); ?></span>
                            </div>
                        </div>

                    </div>

                </div>

            </form>

        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="nh-shopify-checkout-wrapper">
            <div class="nh-shopify-checkout-grid">
                <div class="nh-shopify-main-col">
                    <div class="nh-shopify-logo-bar">
                        <span class="nh-shopify-brand-name">{{{ settings.brand_title }}}</span>
                    </div>
                    <div class="nh-shopify-breadcrumbs">
                        <span>Carrito</span> <span>›</span> <span class="current">Información y Pago</span>
                    </div>
                    <div class="nh-shopify-express-checkout">
                        <div class="nh-shopify-express-title">Pago Rápido / Express</div>
                        <div class="nh-shopify-express-btns">
                            <button type="button" class="nh-shopify-express-btn nequi">Nequi</button>
                            <button type="button" class="nh-shopify-express-btn bancolombia">Bancolombia</button>
                        </div>
                    </div>
                    <div class="nh-shopify-section">
                        <div class="nh-shopify-section-title"><span>Información de Contacto & Envío</span></div>
                        <div class="form-row"><label>Email / Teléfono *</label><input type="text" class="input-text" placeholder="correo@ejemplo.com"></div>
                        <div class="form-row"><label>Dirección de Envío *</label><input type="text" class="input-text" placeholder="Dirección completa"></div>
                    </div>
                </div>

                <div class="nh-shopify-sidebar-col">
                    <div class="nh-shopify-product-list">
                        <div class="nh-shopify-product-item">
                            <div class="nh-shopify-thumb-wrapper">
                                <div style="width:100%;height:100%;background:#eee;border-radius:6px;"></div>
                                <span class="nh-shopify-qty-badge">2</span>
                            </div>
                            <div class="nh-shopify-product-info">
                                <span class="nh-shopify-product-title">Venture</span>
                                <span class="nh-shopify-product-meta">Color: Marfil, Talla: M</span>
                            </div>
                            <div class="nh-shopify-product-price">$ 220.000</div>
                        </div>
                    </div>

                    <div class="nh-shopify-coupon-box">
                        <input type="text" class="nh-shopify-coupon-input" placeholder="Código de descuento">
                        <button type="button" class="nh-shopify-coupon-btn">Usar</button>
                    </div>

                    <div class="nh-shopify-summary-rows">
                        <div class="nh-shopify-summary-row"><span>Subtotal</span><span>$ 220.000</span></div>
                        <div class="nh-shopify-summary-row"><span>Envío</span><span>Gratis</span></div>
                        <div class="nh-shopify-total-row"><span>Total</span><span class="nh-shopify-total-price">$ 220.000</span></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
