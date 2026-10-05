<?php
/**
 * Plugin Name: NH Core
 * Plugin URI: https://www.normahana.com
 * Description: Plugin site-specific que centraliza la lógica de negocio, tracking y widgets custom de Elementor para Norma Hana.
 * Version: 2.0.0
 * Author: Diego Navarro
 * Text Domain: nh-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version constant
define( 'NH_CORE_VERSION', '2.0.0' );

// Option key que respalda la ventana temporal de testing (ver inc/class-nh-core-cli.php
// y NH_Core_Woocommerce::restrict_test_coupons()).
define( 'NH_CORE_TEST_MODE_OPTION', 'nh_core_test_mode_expires' );

/**
 * Autoloader Nativo PSR-4 para el namespace NormaHana\Core\
 */
spl_autoload_register( function ( $class ) {
    $prefix   = 'NormaHana\\Core\\';
    $base_dir = plugin_dir_path( __FILE__ ) . 'src/';
    $len      = strlen( $prefix );

    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

/**
 * ¿Está activa la ventana temporal que habilita el bypass de cupones de testing?
 */
function nh_core_test_mode_is_active() {
    $expires = (int) get_option( NH_CORE_TEST_MODE_OPTION, 0 );
    return $expires > time();
}

// Cargar orquestador modular legacy (mientras concluye la migración completa)
require_once plugin_dir_path( __FILE__ ) . 'inc/class-nh-core-loader.php';

// Initialize updater (admin + WP-CLI contexts)
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-nh-core-updater.php';
    new NH_Core_Updater( __FILE__ );
}

// Comandos WP-CLI (armar/desarmar/consultar ventana de testing)
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-nh-core-cli.php';
}

// SEO y optimización de rendimiento (sesiones PHP y caché público)
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nh-seo-performance.php';
add_action( 'plugins_loaded', [ 'NH_SEO_Performance', 'init' ] );

// Inicializar Kernel PSR-4 Modular y Loader Legacy
add_action( 'plugins_loaded', function() {
    \NormaHana\Core\Plugin::get_instance()->boot();
    \NH_Core_Loader::get_instance();
} );
