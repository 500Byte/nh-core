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


    // ─── Controles de Elementor ─────────────────────────────────────────────

    protected function register_controls() {

        // ── Sección: Trigger ─────────────────────────────────────────────────
        $this->start_controls_section( 'section_trigger', [
            'label' => esc_html__( 'Ícono del Carrito', 'nh-core' ),
        ] );

        $this->add_control( 'trigger_icon', [
            'label'   => esc_html__( 'Ícono', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::ICONS,
            'default' => [
                'value'   => 'ph-light ph-shopping-bag',
                'library' => 'phosphor-light',
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

        // ════════════════════════════════════════════════════════════════════
        // STYLE TAB
        // ════════════════════════════════════════════════════════════════════

        // ── Sección: Trigger ─────────────────────────────────────────────────
        $this->start_controls_section( 'style_trigger', [
            'label' => esc_html__( 'Trigger (Ícono)', 'nh-core' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'trigger_padding', [
            'label'      => esc_html__( 'Padding', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'default'    => [
                'unit'     => 'px',
                'top'      => '',
                'right'    => '',
                'bottom'   => '',
                'left'     => '',
                'isLinked' => true,
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'trigger_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [
                'unit'     => 'px',
                'top'      => '6',
                'right'    => '6',
                'bottom'   => '6',
                'left'     => '6',
                'isLinked' => true,
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'trigger_color_heading', [
            'label' => esc_html__( 'Colores', 'nh-core' ),
            'type'  => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'trigger_color', [
            'label'     => esc_html__( 'Color del ícono', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__trigger' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'trigger_bg', [
            'label'     => esc_html__( 'Color de fondo', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__trigger' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'trigger_hover_color', [
            'label'     => esc_html__( 'Color hover', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__trigger:hover' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'trigger_hover_bg', [
            'label'     => esc_html__( 'Fondo hover', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__trigger:hover' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'trigger_icon_size_heading', [
            'label'     => esc_html__( 'Ícono', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'trigger_icon_size', [
            'label'       => esc_html__( 'Tamaño del ícono', 'nh-core' ),
            'type'        => \Elementor\Controls_Manager::SLIDER,
            'size_units'  => [ 'px' ],
            'default'     => [
                'unit' => 'px',
                'size' => 22,
            ],
            'range'       => [
                'px' => [ 'min' => 14, 'max' => 40, 'step' => 1 ],
            ],
            'selectors'   => [
                '{{WRAPPER}} .nh-side-cart__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
            ],
        ] );

        // Badge
        $this->add_control( 'badge_heading', [
            'label'     => esc_html__( 'Badge', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'badge_bg', [
            'label'     => esc_html__( 'Fondo badge', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__badge' => 'background: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'badge_color', [
            'label'     => esc_html__( 'Color texto badge', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__badge' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'badge_size', [
            'label'      => esc_html__( 'Tamaño badge', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [
                'unit' => 'px',
                'size' => 18,
            ],
            'range'      => [
                'px' => [ 'min' => 12, 'max' => 28, 'step' => 1 ],
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__badge' => 'min-width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // ── Sección: Panel ──────────────────────────────────────────────────
        $this->start_controls_section( 'style_panel', [
            'label' => esc_html__( 'Panel (Drawer)', 'nh-core' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'panel_width', [
            'label'      => esc_html__( 'Ancho del panel', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'default'    => [
                'unit' => 'px',
                'size' => 420,
            ],
            'range'      => [
                'px' => [ 'min' => 320, 'max' => 600, 'step' => 10 ],
                '%'  => [ 'min' => 50, 'max' => 100, 'step' => 5 ],
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__drawer' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'panel_bg', [
            'label'     => esc_html__( 'Fondo del panel', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__drawer' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'panel_overlay_opacity', [
            'label'   => esc_html__( 'Opacidad del overlay', 'nh-core' ),
            'type'    => \Elementor\Controls_Manager::SLIDER,
            'default' => [
                'unit' => '',
                'size' => 0.45,
            ],
            'range'   => [
                '' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ],
            ],
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__overlay' => 'background: rgba(32,32,32,{{SIZE}});',
            ],
        ] );

        $this->add_control( 'panel_padding_heading', [
            'label'     => esc_html__( 'Padding interno', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'panel_padding', [
            'label'      => esc_html__( 'Padding', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px' ],
            'default'    => [
                'unit'     => 'px',
                'top'      => '24',
                'right'    => '24',
                'bottom'   => '24',
                'left'     => '24',
                'isLinked' => true,
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__drawer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // ── Sección: Botón cerrar ───────────────────────────────────────────
        $this->start_controls_section( 'style_close', [
            'label' => esc_html__( 'Botón Cerrar', 'nh-core' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'close_size', [
            'label'       => esc_html__( 'Tamaño', 'nh-core' ),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'default'     => '36',
            'options'     => [
                '28' => '28 px',
                '32' => '32 px',
                '36' => '36 px',
                '40' => '40 px',
                '44' => '44 px',
            ],
            'selectors'   => [
                '{{WRAPPER}} .nh-side-cart__close' => 'width: {{VALUE}}px; height: {{VALUE}}px;',
            ],
        ] );

        $this->add_control( 'close_color', [
            'label'     => esc_html__( 'Color', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__close' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'close_bg', [
            'label'     => esc_html__( 'Fondo', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__close' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'close_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'nh-core' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'default'    => [
                'unit' => 'px',
                'size' => 6,
            ],
            'range'      => [
                'px' => [ 'min' => 0, 'max' => 50, 'step' => 1 ],
            ],
            'selectors'  => [
                '{{WRAPPER}} .nh-side-cart__close' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // ── Sección: Header del panel ───────────────────────────────────────
        $this->start_controls_section( 'style_header', [
            'label' => esc_html__( 'Header del Panel', 'nh-core' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'header_title_color', [
            'label'     => esc_html__( 'Color título', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__title' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'header_count_color', [
            'label'     => esc_html__( 'Color contador', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__count-label' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'header_border_color', [
            'label'     => esc_html__( 'Color borde inferior', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__header' => 'border-bottom-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();

        // ── Sección: Botones footer ─────────────────────────────────────────
        $this->start_controls_section( 'style_footer_buttons', [
            'label' => esc_html__( 'Botones Footer', 'nh-core' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'btn_primary_bg', [
            'label'     => esc_html__( 'Fondo botón primario', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__btn--primary' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'btn_primary_color', [
            'label'     => esc_html__( 'Color texto primario', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__btn--primary' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'btn_secondary_border', [
            'label'     => esc_html__( 'Borde botón secundario', 'nh-core' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .nh-side-cart__btn--secondary' => 'border-color: {{VALUE}}; color: {{VALUE}};',
            ],
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
        $trigger_icon   = $settings['trigger_icon'] ?? [];

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
                    <?php echo $this->get_cart_icon_html( $trigger_icon ); // phpcs:ignore ?>
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
                        <i class="ph-light ph-x" aria-hidden="true" style="font-size: 14px;"></i>
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
                            <i class="ph-light ph-shopping-bag" style="font-size: 48px;"></i>
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
                            $variation_pills = [];
                            if ( ! empty( $item['variation'] ) ) {
                                foreach ( $item['variation'] as $attr => $val ) {
                                    if ( $val ) {
                                        $variation_pills[] = [
                                            'label' => wc_attribute_label( str_replace( 'attribute_', '', $attr ) ),
                                            'value' => ucfirst( $val ),
                                        ];
                                    }
                                }
                            }
                        ?>
                        <li class="nh-side-cart__item" data-key="<?php echo esc_attr( $key ); ?>"
                            data-nh-product-id="<?php echo esc_attr( $product_id ); ?>"
                            data-nh-product-name="<?php echo esc_attr( $name ); ?>"
                            data-nh-product-price="<?php echo esc_attr( $product ? $product->get_price() : '' ); ?>"
                            data-nh-quantity="<?php echo esc_attr( $qty ); ?>"
                        >
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
                                    <?php echo esc_html( $name ); ?>
                                </a>
                                <?php if ( $variation_pills ) : ?>
                                <div class="nh-pill-group">
                                    <?php foreach ( $variation_pills as $pill ) : ?>
                                    <span class="nh-pill">
                                        <span class="nh-pill-label"><?php echo esc_html( $pill['label'] ); ?>:</span>
                                        <span class="nh-pill-value"><?php echo esc_html( $pill['value'] ); ?></span>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
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
     * Renderiza el ícono del trigger usando Icons_Manager.
     */
    private function get_cart_icon_html( array $icon_data ): string {
        if ( ! class_exists( '\Elementor\Icons_Manager' ) || empty( $icon_data['value'] ) ) {
            return '<i class="ph-light ph-shopping-bag nh-side-cart__icon" aria-hidden="true"></i>';
        }
        ob_start();
        \Elementor\Icons_Manager::render_icon( $icon_data, [ 'aria-hidden' => 'true' ] );
        $rendered = ob_get_clean();
        return '<span class="nh-side-cart__icon" aria-hidden="true">' . $rendered . '</span>';
    }


}
