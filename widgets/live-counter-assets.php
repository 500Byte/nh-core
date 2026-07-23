<?php
/**
 * NH Live Counter — Assets registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Registrar assets para que Elementor los encole bajo demanda
add_action( 'wp_enqueue_scripts', 'nh_live_counter_register_assets' );
function nh_live_counter_register_assets() {
    wp_register_style(
        'nh-live-counter',
        NH_CORE_URL . 'assets/css/nh-live-counter.css',
        [],
        '1.0.0'
    );
    wp_register_script(
        'nh-live-counter',
        NH_CORE_URL . 'assets/js/nh-live-counter.js',
        [],
        '1.0.0',
        true
    );

    // Localizar configuración del AJAX de tracking
    wp_localize_script(
        'nh-live-counter',
        'nhLiveCounter',
        [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'nh_live_counter' ),
        ]
    );
}
