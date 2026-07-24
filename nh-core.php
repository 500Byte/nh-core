<?php
/**
 * Plugin Name: NH Core
 * Plugin URI: https://www.normahana.com
 * Description: Plugin site-specific que centraliza la lógica de negocio, tracking y widgets custom de Elementor para Norma Hana.
 * Version: 1.1.0
 * Author: Diego Navarro
 * Text Domain: nh-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version constant
define( 'NH_CORE_VERSION', '1.1.0' );

// Cargar orquestador modular del plugin
require_once plugin_dir_path( __FILE__ ) . 'inc/class-nh-core-loader.php';

// Initialize updater (admin + WP-CLI contexts)
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-nh-core-updater.php';
    new NH_Core_Updater( __FILE__ );
}

// Inicializar orquestador
add_action( 'plugins_loaded', function() {
    \NH_Core_Loader::get_instance();
} );
