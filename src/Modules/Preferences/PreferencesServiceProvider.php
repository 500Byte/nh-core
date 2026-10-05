<?php
namespace NormaHana\Core\Modules\Preferences;

use NormaHana\Core\Common\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PreferencesServiceProvider implements ServiceProviderInterface {

    private PreferencesRepository $repository;
    private PreferencesTokenService $token_service;

    public function register(): void {
        $this->repository    = new PreferencesRepository();
        $this->token_service = $token_service ?? new PreferencesTokenService();

        // 1. Iniciar creación / verificación de tabla de base de datos
        add_action( 'init', [ $this->repository, 'maybe_create_table' ], 5 );

        // 2. Registrar Hooks de Frontend (WooCommerce Account, Token Auth Bypass & AJAX)
        $frontend_hooks = new PreferencesFrontendHooks( $this->repository, $this->token_service );
        $frontend_hooks->register_hooks();

        // 3. Registrar Controladores REST API (/wp-json/nh/v1/preferences)
        $rest_controller = new PreferencesRestController( $this->repository, $this->token_service );
        $rest_controller->register_routes();
    }

    public function get_repository(): PreferencesRepository {
        return $this->repository;
    }

    public function get_token_service(): PreferencesTokenService {
        return $this->token_service;
    }
}
