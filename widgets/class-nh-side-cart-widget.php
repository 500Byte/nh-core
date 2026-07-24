<?php
/**
 * NH Side Cart Widget
 *
 * Widget de Elementor 100% independiente de Elementor Pro.
 * Solo requiere: Elementor (gratis) + WooCommerce.
 *
 * Arquitectura BEM: nh-side-cart__*
 * Assets: nh-side-cart.css + nh-side-cart.js
 *
 * @package Normahana
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Side_Cart_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_side_cart';
    }

    public function get_title() {
        return esc_html__( 'NH Side Cart', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'cart', 'carrito', 'side cart', 'mini cart', 'drawer', 'woocommerce', 'norma hana' ];
    }

    public function get_style_depends() {
        return [ 'nh-side-cart' ];
    }

    public function get_script_depends() {
        return [ 'nh-side-cart' ];
    }

    // ─── Controles de Elementor ─────────────────────────────────────────────

    protected function register_controls() {

        // ── Sección: Trigger ─────────────────────────────────────────────────
        $this->start_controls_section( 'section_trigger', [
            'label' => esc_html__( 'Ícono del Carrito', 'nh-core' ),
        ] );

        $this->add_control( 'trigger_icon', [
            'label'   => esc_html__( 'Ícono', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'bag',
            'options' => [
                'bag'    => esc_html__( 'Bolsa de compra', 'nh-core' ),
                'cart'   => esc_html__( 'Carrito', 'nh-core' ),
                'basket' => esc_html__( 'Canasta', 'nh-core' ),
            ],
        ] );

        $this->add_control( 'show_subtotal_in_trigger', [
            'label'        => esc_html__( 'Mostrar subtotal junto al ícono', 'nh-core' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Sí', 'nh-core' ),
            'label_off'    => esc_html__( 'No', 'nh-core' ),
            'return_value' => 'yes',
            'default'      => '',
        ] );

        $this->end_controls_section();

        // ── Sección: Panel ──────────────────────────────────────────────────
        $this->start_controls_section( 'section_panel', [
            'label' => esc_html__( 'Panel del Carrito', 'nh-core' ),
        ] );

        $this->add_control( 'panel_title', [
            'label'   => esc_html__( 'Título del panel', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Tu carrito', 'nh-core' ),
        ] );

        $this->add_control( 'show_free_shipping_bar', [
            'label'        => esc_html__( 'Mostrar barra de envío gratis', 'nh-core' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Sí', 'nh-core' ),
            'label_off'    => esc_html__( 'No', 'nh-core' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'free_shipping_threshold', [
            'label'       => esc_html__( 'Umbral envío gratis (COP)', 'nh-core' ),
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'default'     => 280000,
            'min'         => 0,
            'description' => esc_html__( '0 = detectar automáticamente desde WooCommerce.', 'nh-core' ),
            'condition'   => [ 'show_free_shipping_bar' => 'yes' ],
        ] );

        $this->add_control( 'cart_button_text', [
            'label'   => esc_html__( 'Texto: Ver carrito', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Ver carrito', 'nh-core' ),
        ] );

        $this->add_control( 'checkout_button_text', [
            'label'   => esc_html__( 'Texto: Finalizar compra', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Finalizar compra', 'nh-core' ),
        ] );

        $this->add_control( 'empty_text', [
            'label'   => esc_html__( 'Texto carrito vacío', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Tu carrito está vacío', 'nh-core' ),
        ] );

        $this->add_control( 'empty_cta_text', [
            'label'   => esc_html__( 'CTA carrito vacío', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Explorar tienda', 'nh-core' ),
        ] );

        $this->end_controls_section();
    }

    // ─── Render ─────────────────────────────────────────────────────────────

    protected function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        $settings       = $this->get_settings_for_display();
        $panel_title    = sanitize_text_field( $settings['panel_title'] ?: esc_html__( 'Tu carrito', 'nh-core' ) );
        $cart_btn       = sanitize_text_field( $settings['cart_button_text'] ?: esc_html__( 'Ver carrito', 'nh-core' ) );
        $checkout_btn   = sanitize_text_field( $settings['checkout_button_text'] ?: esc_html__( 'Finalizar compra', 'nh-core' ) );
        $empty_text     = sanitize_text_field( $settings['empty_text'] ?: esc_html__( 'Tu carrito está vacío', 'nh-core' ) );
        $empty_cta      = sanitize_text_field( $settings['empty_cta_text'] ?: esc_html__( 'Explorar tienda', 'nh-core' ) );
        $show_bar       = 'yes' === ( $settings['show_free_shipping_bar'] ?? 'yes' );
        $show_subtotal  = 'yes' === ( $settings['show_subtotal_in_trigger'] ?? '' );
        $threshold      = $this->get_shipping_threshold( $settings );
        $icon           = sanitize_key( $settings['trigger_icon'] ?? 'bag' );

        $cart_count   = WC()->cart->get_cart_contents_count();
        $cart_url     = wc_get_cart_url();
        $checkout_url = wc_get_checkout_url();
        $subtotal_raw = WC()->cart->get_subtotal();
        $subtotal_fmt = WC()->cart->get_cart_subtotal();
        $shop_url     = wc_get_page_permalink( 'shop' );
        ?>
        <div class="nh-side-cart" data-widget>
            <?php /* ── Trigger ── */ ?>
            <button
                class="nh-side-cart__trigger"
                aria-label="<?php esc_attr_e( 'Abrir carrito', 'nh-core' ); ?>"
                aria-expanded="false"
                aria-controls="nh-side-cart-drawer"
            >
                <?php if ( $show_subtotal && ! WC()->cart->is_empty() ) : ?>
                    <span class="nh-side-cart__trigger-subtotal"><?php echo wp_kses_post( $subtotal_fmt ); ?></span>
                <?php endif; ?>

                <span class="nh-side-cart__trigger-icon-wrap">
                    <span
                        class="nh-side-cart__badge<?php echo 0 === $cart_count ? ' nh-side-cart__badge--hidden' : ''; ?>"
                        data-count="<?php echo esc_attr( $cart_count ); ?>"
                        aria-label="<?php printf( esc_attr__( '%d artículos en el carrito', 'nh-core' ), $cart_count ); ?>"
                    ><?php echo esc_html( $cart_count ); ?></span>
                    <?php echo $this->get_cart_icon_svg( $icon ); // phpcs:ignore ?>
                </span>
            </button>

            <?php /* ── Overlay ── */ ?>
            <div class="nh-side-cart__overlay" aria-hidden="true"></div>

            <?php /* ── Drawer ── */ ?>
            <aside
                id="nh-side-cart-drawer"
                class="nh-side-cart__drawer"
                aria-hidden="true"
                role="dialog"
                aria-label="<?php echo esc_attr( $panel_title ); ?>"
            >
                <?php /* Header */ ?>
                <header class="nh-side-cart__header">
                    <div class="nh-side-cart__header-left">
                        <h2 class="nh-side-cart__title"><?php echo esc_html( $panel_title ); ?></h2>
                        <span class="nh-side-cart__count-label">
                            <?php echo $cart_count === 1
                                ? esc_html__( '1 artículo', 'nh-core' )
                                : sprintf( esc_html__( '%d artículos', 'nh-core' ), $cart_count );
                            ?>
                        </span>
                    </div>
                    <button
                        class="nh-side-cart__close"
                        aria-label="<?php esc_attr_e( 'Cerrar carrito', 'nh-core' ); ?>"
                    >
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1 1L13 13M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>

                <?php /* Barra de Envío Gratis */ ?>
                <?php if ( $show_bar && $threshold > 0 ) :
                    $remaining = max( 0, $threshold - $subtotal_raw );
                    $progress  = min( 100, $subtotal_raw > 0 ? ( $subtotal_raw / $threshold ) * 100 : 0 );
                ?>
                <div class="nh-side-cart__shipping-bar<?php echo $remaining <= 0 ? ' nh-side-cart__shipping-bar--complete' : ''; ?>">
                    <?php if ( $remaining > 0 ) : ?>
                        <div class="nh-side-cart__shipping-bar-track">
                            <div class="nh-side-cart__shipping-bar-fill" style="width:<?php echo esc_attr( round( $progress, 1 ) ); ?>%"></div>
                        </div>
                        <p class="nh-side-cart__shipping-bar-text">
                            <?php printf(
                                wp_kses_post( __( 'Te faltan <strong>%s</strong> para envío gratis', 'nh-core' ) ),
                                wp_kses_post( wc_price( $remaining ) )
                            ); ?>
                        </p>
                    <?php else : ?>
                        <p class="nh-side-cart__shipping-bar-text nh-side-cart__shipping-bar-text--success">
                            <?php esc_html_e( '🎉 ¡Envío gratis desbloqueado!', 'nh-core' ); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php /* Body – Items */ ?>
                <div class="nh-side-cart__body">
                    <?php /* Estado vacío */ ?>
                    <div class="nh-side-cart__empty<?php echo ! WC()->cart->is_empty() ? ' nh-side-cart__empty--hidden' : ''; ?>">
                        <div class="nh-side-cart__empty-icon" aria-hidden="true">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                        </div>
                        <p class="nh-side-cart__empty-text"><?php echo esc_html( $empty_text ); ?></p>
                        <a class="nh-side-cart__empty-cta" href="<?php echo esc_url( $shop_url ); ?>">
                            <?php echo esc_html( $empty_cta ); ?>
                        </a>
                    </div>

                    <?php /* Lista de items (poblada vía AJAX/PHP inicial) */ ?>
                    <ul class="nh-side-cart__items<?php echo WC()->cart->is_empty() ? ' nh-side-cart__items--hidden' : ''; ?>">
                        <?php foreach ( WC()->cart->get_cart() as $key => $item ) :
                            $product   = $item['data'];
                            $qty       = $item['quantity'];
                            $line_tot  = $item['line_total'];
                            $img       = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
                            $img       = $img ?: wc_placeholder_img_src( 'woocommerce_thumbnail' );
                            $name      = $product->get_name();
                            // Variaciones como parte del nombre
                            $variation_str = '';
                            if ( ! empty( $item['variation'] ) ) {
                                $parts = [];
                                foreach ( $item['variation'] as $attr => $val ) {
                                    if ( $val ) {
                                        $parts[] = wc_attribute_label( str_replace( 'attribute_', '', $attr ) ) . ': ' . ucfirst( $val );
                                    }
                                }
                                if ( $parts ) {
                                    $variation_str = ' - ' . implode( ', ', $parts );
                                }
                            }
                            $full_name  = $name . $variation_str;
                            $product_url = $product->get_permalink();
                        ?>
                        <li class="nh-side-cart__item" data-key="<?php echo esc_attr( $key ); ?>">
                            <a class="nh-side-cart__item-image-wrap" href="<?php echo esc_url( $product_url ); ?>" tabindex="-1" aria-hidden="true">
                                <img
                                    class="nh-side-cart__item-image"
                                    src="<?php echo esc_url( $img ); ?>"
                                    alt="<?php echo esc_attr( $name ); ?>"
                                    width="72" height="72"
                                    loading="lazy"
                                >
                            </a>
                            <div class="nh-side-cart__item-info">
                                <a class="nh-side-cart__item-name" href="<?php echo esc_url( $product_url ); ?>">
                                    <?php echo esc_html( $full_name ); ?>
                                </a>
                                <div class="nh-side-cart__item-meta">
                                    <span class="nh-side-cart__item-qty"><?php echo esc_html( $qty ); ?> &times;</span>
                                    <span class="nh-side-cart__item-price"><?php echo wp_kses_post( wc_price( $line_tot / $qty ) ); ?></span>
                                </div>
                            </div>
                            <button
                                class="nh-side-cart__item-remove"
                                aria-label="<?php printf( esc_attr__( 'Eliminar %s', 'nh-core' ), esc_attr( $name ) ); ?>"
                                data-key="<?php echo esc_attr( $key ); ?>"
                            >&times;</button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php /* Footer */ ?>
                <footer class="nh-side-cart__footer">
                    <div class="nh-side-cart__subtotal">
                        <span class="nh-side-cart__subtotal-label"><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
                        <span class="nh-side-cart__subtotal-amount"><?php echo wp_kses_post( $subtotal_fmt ); ?></span>
                    </div>
                    <div class="nh-side-cart__actions">
                        <a class="nh-side-cart__btn nh-side-cart__btn--secondary" href="<?php echo esc_url( $cart_url ); ?>">
                            <?php echo esc_html( $cart_btn ); ?>
                        </a>
                        <a class="nh-side-cart__btn nh-side-cart__btn--primary" href="<?php echo esc_url( $checkout_url ); ?>">
                            <?php echo esc_html( $checkout_btn ); ?>
                        </a>
                    </div>
                </footer>
            </aside>
        </div>
        <?php
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Obtener umbral de envío gratis desde Elementor o desde WooCommerce.
     */
    private function get_shipping_threshold( $settings ) {
        $custom = floatval( $settings['free_shipping_threshold'] ?? 0 );
        if ( $custom > 0 ) {
            return $custom;
        }
        // Auto-detect desde WooCommerce shipping zones
        if ( class_exists( 'WC_Shipping_Zones' ) ) {
            foreach ( \WC_Shipping_Zones::get_zones() as $zone ) {
                foreach ( $zone['shipping_methods'] as $method ) {
                    if ( 'free_shipping' === $method->id && 'yes' === $method->enabled ) {
                        $min = floatval( $method->min_amount );
                        if ( $min > 0 ) return $min;
                    }
                }
            }
        }
        return 280000; // Fallback NH
    }

    /**
     * SVG icons para el trigger.
     */
    private function get_cart_icon_svg( string $type ): string {
        $svgs = [
            'bag' => '<svg class="nh-side-cart__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
            'cart' => '<svg class="nh-side-cart__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
            'basket' => '<svg class="nh-side-cart__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5.757 1.929l-1.414 1.414L8 7.172l-5 5V19a2 2 0 002 2h14a2 2 0 002-2v-6.828l-5-5 3.657-3.829-1.414-1.414L11 7.344 5.757 1.929z"/></svg>',
        ];
        return $svgs[ $type ] ?? $svgs['bag'];
    }

    /**
     * Elementor editor live preview (sin datos reales).
     */
    protected function content_template() {
        ?>
        <div class="nh-side-cart">
            <button class="nh-side-cart__trigger" aria-label="Abrir carrito">
                <span class="nh-side-cart__trigger-icon-wrap">
                    <span class="nh-side-cart__badge" data-count="3">3</span>
                    <svg class="nh-side-cart__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </span>
            </button>
            <p style="font-family: sans-serif; font-size: 12px; color: #888; margin-top: 8px; text-align: center;">
                NH Side Cart — el drawer se abre en el front-end
            </p>
        </div>
        <?php
    }
}
