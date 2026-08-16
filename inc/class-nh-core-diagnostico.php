<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Diagnostico {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'nh_diagnostico_estilo', [ $this, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public function register_assets() {
        $css_path = NH_CORE_PATH . 'assets/css/nh-diagnostico.css';
        $js_path  = NH_CORE_PATH . 'assets/js/nh-diagnostico.js';

        wp_register_style(
            'nh-diagnostico',
            NH_CORE_URL . 'assets/css/nh-diagnostico.css',
            [],
            file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
        );

        // GSAP desde CDN — solo se carga cuando el shortcode se usa
        wp_register_script(
            'nh-gsap',
            'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
            [],
            '3.12.5',
            true
        );
        wp_script_add_data( 'nh-gsap', 'rocket-no-delay', true );

        wp_register_script(
            'nh-diagnostico',
            NH_CORE_URL . 'assets/js/nh-diagnostico.js',
            [ 'nh-gsap' ],
            file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
            true
        );
        wp_script_add_data( 'nh-diagnostico', 'rocket-no-delay', true );
    }

    public function render() {
        // Encolar assets solo cuando el shortcode se renderiza
        wp_enqueue_style( 'nh-diagnostico' );
        wp_enqueue_script( 'nh-gsap' );
        wp_enqueue_script( 'nh-diagnostico' );

        // Pasar configuración al JS
        wp_localize_script( 'nh-diagnostico', 'nhDiagnostico', [
            'webhookUrl' => apply_filters( 'nh_diagnostico_webhook_url', '' ),
            'quizUrl'    => 'https://www.normahana.com/diagnostico-estilo/',
            'img'        => $this->get_image_urls(),
        ] );

        return '<div id="nh-diagnostico"></div>';
    }

    private function get_image_urls() {
        $base = NH_CORE_URL . 'assets/images/diagnostico/';
        return [
            'cover'          => $base . 'cover.webp',
            'act1'           => $base . 'act1.webp',
            'act2'           => $base . 'act2.webp',
            'act3'           => $base . 'act3.webp',
            'q1'             => $base . 'q1.webp',
            'q2'             => $base . 'q2.webp',
            'q3'             => $base . 'q3.webp',
            'q4'             => $base . 'q4.webp',
            'q5'             => $base . 'q5.webp',
            'q6'             => $base . 'q6.webp',
            'q7'             => $base . 'q7.webp',
            'q8'             => $base . 'q8.webp',
            'q9'             => $base . 'q9.webp',
            'q10'            => $base . 'q10.webp',
            'q11'            => $base . 'q11.webp',
            'q12'            => $base . 'q12.webp',
            'arch_clasico'   => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=900&q=80&auto=format&fit=crop',
            'arch_romantico' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=900&q=80&auto=format&fit=crop',
            'arch_creativo'  => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=900&q=80&auto=format&fit=crop',
            'arch_natural'   => 'https://images.unsplash.com/photo-1505562130589-9879683e72da?w=900&q=80&auto=format&fit=crop',
            'arch_elegante'  => 'https://images.unsplash.com/photo-1543087903-1ac2ec7aa8c5?w=900&q=80&auto=format&fit=crop',
        ];
    }
}
