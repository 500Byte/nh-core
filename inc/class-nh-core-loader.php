<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Loader {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
    }

    private function define_constants() {
        define( 'NH_CORE_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
        define( 'NH_CORE_URL', plugin_dir_url( dirname( __FILE__ ) ) );
    }

    private function load_dependencies() {
        // Carga de submódulo de tracking de forma segura
        require_once NH_CORE_PATH . 'inc/class-nh-core-tracking.php';
        \NH_Core_Tracking::get_instance();

        // Carga de submódulo de WooCommerce (si WooCommerce está activo)
        if ( class_exists( 'WooCommerce' ) ) {
            require_once NH_CORE_PATH . 'inc/class-nh-core-woocommerce.php';
            \NH_Core_Woocommerce::get_instance();
        }

        // Carga de submódulo de Elementor (si Elementor está activo)
        if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
            require_once NH_CORE_PATH . 'inc/class-nh-core-elementor.php';
            \NH_Core_Elementor::get_instance();
        }
    }
}
