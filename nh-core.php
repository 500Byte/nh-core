<?php
/**
 * Plugin Name: NH Core
 * Plugin URI: https://www.normahana.com
 * Description: Plugin site-specific que centraliza la lógica de negocio, tracking y widgets custom de Elementor para Norma Hana.
 * Version: 1.6.6
 * Author: Diego Navarro
 * Text Domain: nh-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version constant
define( 'NH_CORE_VERSION', '1.6.5' );

// Option key que respalda la ventana temporal de testing (ver inc/class-nh-core-cli.php
// y NH_Core_Woocommerce::restrict_test_coupons()).
define( 'NH_CORE_TEST_MODE_OPTION', 'nh_core_test_mode_expires' );

/**
 * ¿Está activa la ventana temporal que habilita el bypass de cupones de testing
 * (freetesting / freetesting-noemail) fuera de entornos locales?
 * Se arma manualmente con `wp nh-core enable-test-mode --minutes=<n>` antes de
 * correr el E2E suite contra producción; expira sola.
 */
function nh_core_test_mode_is_active() {
    $expires = (int) get_option( NH_CORE_TEST_MODE_OPTION, 0 );
    return $expires > time();
}

// Cargar orquestador modular del plugin
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

// Inicializar orquestador
add_action( 'plugins_loaded', function() {
    \NH_Core_Loader::get_instance();
} );
