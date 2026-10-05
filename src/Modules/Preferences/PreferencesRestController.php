<?php
namespace NormaHana\Core\Modules\Preferences;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (! defined('ABSPATH')) {
    exit;
}

class PreferencesRestController
{
    private PreferencesRepository $repository;
    private PreferencesTokenService $token_service;

    public function __construct(PreferencesRepository $repository, PreferencesTokenService $token_service)
    {
        $this->repository    = $repository;
        $this->token_service = $token_service;
    }

    public function register_routes(): void
    {
        add_action('rest_api_init', function () {
            register_rest_route('nh/v1', '/preferences', [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'rest_get_preferences' ],
                    'permission_callback' => [ $this, 'verify_api_key' ],
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'rest_save_preferences' ],
                    'permission_callback' => [ $this, 'verify_api_key' ],
                ],
            ]);
        });
    }

    public function verify_api_key(WP_REST_Request $request): bool
    {
        $key = $request->get_header('x_nh_api_key') ?? $request->get_header('x-nh-api-key');
        if (empty($key)) {
            $key = $request->get_param('api_key');
        }

        $expected = defined('NH_TELEGRAM_BOT_SECRET') ? NH_TELEGRAM_BOT_SECRET : 'nh_telegram_bot_sec_2026_x871a';
        return ! empty($key) && hash_equals($expected, (string) $key);
    }

    public function get_token_service(): PreferencesTokenService
    {
        return $this->token_service;
    }

    public function rest_get_preferences(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $raw_email = $request->get_param('email');
        $email     = function_exists('sanitize_email') ? sanitize_email((string) $raw_email) : strtolower(trim((string) $raw_email));

        if (empty($email) || ( function_exists('is_email') ? ! is_email($email) : ! filter_var($email, FILTER_VALIDATE_EMAIL) )) {
            return new WP_Error('rest_invalid_param', 'Correo electrónico inválido o requerido.', [ 'status' => 400 ]);
        }

        $prefs = $this->repository->get_preferences($email);

        return new WP_REST_Response([
            'success' => true,
            'data'    => $prefs,
        ], 200);
    }

    public function rest_save_preferences(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $raw_email = $request->get_param('email');
        $email     = function_exists('sanitize_email') ? sanitize_email((string) $raw_email) : strtolower(trim((string) $raw_email));

        if (empty($email) || ( function_exists('is_email') ? ! is_email($email) : ! filter_var($email, FILTER_VALIDATE_EMAIL) )) {
            return new WP_Error('rest_invalid_param', 'Correo electrónico inválido o requerido.', [ 'status' => 400 ]);
        }

        $source_param = $request->get_param('source');
        $source       = ! empty($source_param) ? ( function_exists('sanitize_key') ? sanitize_key($source_param) : trim((string) $source_param) ) : 'rest_api';

        $params = [];
        if (null !== $request->get_param('cart_reminders')) {
            $params['cart_reminders'] = $request->get_param('cart_reminders');
        }
        if (null !== $request->get_param('atelier_news')) {
            $params['atelier_news'] = $request->get_param('atelier_news');
        }
        if (null !== $request->get_param('preferred_channel')) {
            $params['preferred_channel'] = $request->get_param('preferred_channel');
        }
        if (null !== $request->get_param('habeas_data_optout')) {
            $params['habeas_data_optout'] = $request->get_param('habeas_data_optout');
        }
        if (null !== $request->get_param('optout_reason')) {
            $params['optout_reason'] = $request->get_param('optout_reason');
        }

        $current_prefs = $this->repository->get_preferences($email);
        if (! empty($current_prefs) && empty($current_prefs['is_new'])) {
            $params = array_merge([
                'cart_reminders'     => $current_prefs['cart_reminders'],
                'atelier_news'       => $current_prefs['atelier_news'],
                'preferred_channel'  => $current_prefs['preferred_channel'],
                'habeas_data_optout' => $current_prefs['habeas_data_optout'],
            ], $params);
        }

        $saved = $this->repository->save_preferences($email, $params, $source);

        if (! $saved) {
            return new WP_Error('rest_save_failed', 'No se pudieron guardar las preferencias.', [ 'status' => 500 ]);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Preferences updated.',
        ], 200);
    }
}
