<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Cart_Table_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_cart_table_widget';
    }

    public function get_title() {
        return esc_html__( 'NH Tabla del Carrito', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'cart', 'table', 'carrito', 'tabla', 'woocommerce', 'products' ];
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
            'show_product_image',
            [
                'label' => esc_html__( 'Mostrar Imagen', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_variations',
            [
                'label' => esc_html__( 'Mostrar Variaciones / Metadata', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_clear_cart',
            [
                'label' => esc_html__( 'Botón Vaciar Carrito', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        // Pestaña Estilo
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__( 'Estilos de Tabla', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'header_bg',
            [
                'label' => esc_html__( 'Fondo Cabecera', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-table-header' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'remove_button_color',
            [
                'label' => esc_html__( 'Color Botón Eliminar', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff4444',
                'selectors' => [
                    '{{WRAPPER}} .nh-cart-remove' => 'color: {{VALUE}};',
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
        $cart_items = $cart->get_cart();
        $settings = $this->get_settings_for_display();

        if ( empty( $cart_items ) ) {
            echo '<p class="nh-cart-table-empty">' . esc_html__( 'Tu carrito está vacío.', 'nh-core' ) . '</p>';
            return;
        }

        ?>
        <div class="nh-cart-widget">
            <div class="nh-cart-products">
                <div class="nh-cart-table-header">
                    <span><?php esc_html_e( 'Producto', 'nh-core' ); ?></span>
                    <span><?php esc_html_e( 'Precio', 'nh-core' ); ?></span>
                    <span><?php esc_html_e( 'Cantidad', 'nh-core' ); ?></span>
                    <span><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
                    <span></span>
                </div>
                
                <?php foreach ( $cart_items as $cart_item_key => $cart_item ) : 
                    $product = $cart_item['data'];
                    if ( ! $product || ! $product->exists() ) continue;
                    $quantity = $cart_item['quantity'];
                    $line_subtotal = WC()->cart->get_product_subtotal( $product, $quantity );
                ?>
                <div class="nh-cart-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                    <div class="nh-cart-product-info">
                        <?php if ( 'yes' === $settings['show_product_image'] ) : ?>
                            <?php echo wp_kses_post( $product->get_image( 'thumbnail', [ 'class' => 'nh-cart-product-img' ] ) ); ?>
                        <?php endif; ?>
                        <div class="nh-cart-product-details">
                            <span class="nh-cart-product-name"><?php echo esc_html( $product->get_name() ); ?></span>
                            <?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
                            <?php if ( 'yes' === $settings['show_variations'] ) : ?>
                                <div class="nh-cart-product-variation">
                                    <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nh-cart-product-price">
                        <?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </div>
                    
                    <div class="nh-cart-product-qty">
                        <button class="nh-cart-qty-btn nh-cart-qty-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">-</button>
                        <input type="number" class="nh-cart-qty-input" value="<?php echo esc_attr( $quantity ); ?>" min="1" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                        <button class="nh-cart-qty-btn nh-cart-qty-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
                    </div>
                    
                    <div class="nh-cart-product-subtotal">
                        <?php echo wp_kses_post( $line_subtotal ); ?>
                    </div>
                    
                    <button class="nh-cart-remove" data-key="<?php echo esc_attr( $cart_item_key ); ?>" title="<?php esc_attr_e( 'Eliminar', 'nh-core' ); ?>">
                        <i class="eicon-close"></i>
                    </button>
                </div>
                <?php endforeach; ?>

                <?php if ( 'yes' === $settings['show_clear_cart'] ) : ?>
                <button class="nh-cart-clear"><?php esc_html_e( 'Vaciar Carrito', 'nh-core' ); ?></button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
