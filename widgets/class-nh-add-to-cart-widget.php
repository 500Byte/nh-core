<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NH_Add_To_Cart_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh-add-to-cart';
    }

    public function get_title() {
        return esc_html__( 'NH Add to Cart', 'nh-core' );
    }

    public function get_icon() {
        return 'eicon-product-add-to-cart';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
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
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Contenido', 'nh-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'promo_text',
            [
                'label' => esc_html__( 'Texto Promocional', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Envío nacional gratis para compras superiores a $150.000 COP', 'nh-core' ),
                'placeholder' => esc_html__( 'Escribe la promoción aquí', 'nh-core' ),
            ]
        );

        $this->add_control(
            'promo_icon',
            [
                'label' => esc_html__( 'Icono de Promo', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'ph-light ph-check-circle',
                    'library' => 'phosphor-light',
                ],
                'exclude_inline_options' => [],
            ]
        );

        $this->add_control(
            'backorder_text',
            [
                'label' => esc_html__( 'Texto de Reserva (WYSIWYG)', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Disponible para reserva', 'nh-core' ),
                'placeholder' => esc_html__( 'Escribe el texto de reserva aquí', 'nh-core' ),
            ]
        );

        $this->add_control(
            'show_buy_now',
            [
                'label' => esc_html__( 'Mostrar Botón Compra Rápida', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'nh-core' ),
                'label_off' => esc_html__( 'No', 'nh-core' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'buy_now_text',
            [
                'label' => esc_html__( 'Texto del Botón', 'nh-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Comprar Ahora', 'nh-core' ),
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $product;

        $previous_product = $product;
        $product = wc_get_product();
        if ( ! $product ) {
            $product = $previous_product;
            return;
        }

        $settings = $this->get_settings_for_display();
        $type     = $product->get_type(); // 'simple', 'variable', 'external'

        $template_file = NH_CORE_PATH . "templates/nh-add-to-cart/{$type}.php";

        add_filter( 'woocommerce_locate_template', [ $this, 'locate_nh_template' ], 10, 3 );

        wc_get_template(
            "nh-add-to-cart/{$type}.php",
            [ 'settings' => $settings, 'product' => $product ],
            '',
            NH_CORE_PATH . 'templates/'
        );

        remove_filter( 'woocommerce_locate_template', [ $this, 'locate_nh_template' ] );
        $GLOBALS['product'] = $previous_product;
    }

    /**
     * Permite que temas hijos sobreescriban estos templates.
     *
     * @param string $template      Template path encontrado.
     * @param string $template_name Nombre del template solicitado.
     * @param string $template_path Ruta base de templates.
     * @return string Template path final.
     */
    public function locate_nh_template( $template, $template_name, $template_path ) {
        if ( strpos( $template_name, 'nh-add-to-cart/' ) === 0 ) {
            $plugin_template = NH_CORE_PATH . 'templates/' . $template_name;
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        return $template;
    }
}
