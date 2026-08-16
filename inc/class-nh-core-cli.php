<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comandos WP-CLI de NH Core.
 *
 * Gestiona la ventana temporal (time-boxed) que habilita el bypass de cupones
 * de testing (freetesting / freetesting-noemail) fuera de entornos locales.
 * Ver NH_Core_Woocommerce::restrict_test_coupons() para el consumo de esta
 * ventana — requiere ADEMÁS el token real (NH_TESTING_BYPASS_TOKEN en
 * wp-config.php) enviado en el header X-NH-Testing. Defensa en profundidad:
 * ambas condiciones deben cumplirse.
 */
class NH_Core_CLI {

    /**
     * Activa la ventana de testing por N minutos (por defecto 15, máx 60).
     * Mientras esté activa Y el request incluya el token real, el bypass de
     * cupones de testing funciona en producción. Pasado ese tiempo se
     * desactiva sola sin acción manual.
     *
     * ## OPTIONS
     *
     * [--minutes=<minutes>]
     * : Duración de la ventana en minutos (1-60).
     * ---
     * default: 15
     * ---
     *
     * ## EXAMPLES
     *
     *     wp nh-core enable-test-mode --minutes=15
     *
     * @when after_wp_load
     * @subcommand enable-test-mode
     */
    public function enable_test_mode( $args, $assoc_args ) {
        $minutes = isset( $assoc_args['minutes'] ) ? (int) $assoc_args['minutes'] : 15;
        $minutes = max( 1, min( 60, $minutes ) );
        $expires = time() + ( $minutes * 60 );

        update_option( NH_CORE_TEST_MODE_OPTION, $expires, false );

        error_log( sprintf(
            '[NH_CORE_TEST_MODE] Ventana ARMADA por %d min, expira %s UTC.',
            $minutes,
            gmdate( 'Y-m-d H:i:s', $expires )
        ) );

        WP_CLI::success( sprintf(
            'Ventana de testing armada por %d minuto(s). Expira: %s UTC.',
            $minutes,
            gmdate( 'Y-m-d H:i:s', $expires )
        ) );
    }

    /**
     * Desactiva inmediatamente la ventana de testing (si estaba activa).
     *
     * ## EXAMPLES
     *
     *     wp nh-core disable-test-mode
     *
     * @when after_wp_load
     * @subcommand disable-test-mode
     */
    public function disable_test_mode( $args, $assoc_args ) {
        delete_option( NH_CORE_TEST_MODE_OPTION );
        error_log( '[NH_CORE_TEST_MODE] Ventana DESARMADA manualmente.' );
        WP_CLI::success( 'Ventana de testing desarmada.' );
    }

    /**
     * Muestra el estado actual de la ventana de testing.
     *
     * ## EXAMPLES
     *
     *     wp nh-core test-mode-status
     *
     * @when after_wp_load
     * @subcommand test-mode-status
     */
    public function test_mode_status( $args, $assoc_args ) {
        if ( nh_core_test_mode_is_active() ) {
            $expires = (int) get_option( NH_CORE_TEST_MODE_OPTION, 0 );
            WP_CLI::success( sprintf(
                'ACTIVA — expira en %d segundo(s) (%s UTC).',
                $expires - time(),
                gmdate( 'Y-m-d H:i:s', $expires )
            ) );
        } else {
            WP_CLI::log( 'INACTIVA.' );
        }
    }
}

WP_CLI::add_command( 'nh-core', 'NH_Core_CLI' );
