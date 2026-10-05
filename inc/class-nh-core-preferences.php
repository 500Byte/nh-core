<?php
/**
 * NH_Core_Preferences (Wrapper de Compatibilidad Legacy)
 *
 * Enlaza llamadas legadas a la nueva arquitectura PSR-4 NormaHana\Core\Modules\Preferences.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NormaHana\Core\Modules\Preferences\PreferencesRepository;
use NormaHana\Core\Modules\Preferences\PreferencesTokenService;

class NH_Core_Preferences {

    private static ?self $instance = null;
    private PreferencesRepository $repository;
    private PreferencesTokenService $token_service;

    public const TABLE_NAME = 'nh_communication_preferences';

    public static function get_instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        $this->repository    = new PreferencesRepository();
        $this->token_service = new PreferencesTokenService();
        add_action( 'nh_preferences_saved', [ $this, 'dispatch_n8n_webhook' ], 10, 2 );
    }

    public static function get_table_name(): string {
        return PreferencesRepository::get_table_name();
    }

    public function maybe_create_table(): void {
        $this->repository->maybe_create_table();
    }

    public function create_table(): void {
        $this->repository->create_table();
    }

    public function get_preferences( string $email ): array {
        return $this->repository->get_preferences( $email );
    }

    public function get_default_preferences(): array {
        return $this->repository->get_default_preferences();
    }

    public function save_preferences( string $email, array $data, string $source = 'web_account_page' ): bool {
        return $this->repository->save_preferences( $email, $data, $source );
    }

    public function dispatch_n8n_webhook( string $email, array $record ): void {
        $webhook_url = defined( 'NH_N8N_PREFERENCES_WEBHOOK_URL' )
            ? NH_N8N_PREFERENCES_WEBHOOK_URL
            : 'http://normahana-n8n:5678/webhook/nh-preferences-updated';

        $payload = [
            'event'       => 'customer.preferences_updated',
            'timestamp'   => time(),
            'email'       => $email,
            'preferences' => [
                'cart_reminders'     => (bool) ( $record['cart_reminders'] ?? false ),
                'atelier_news'       => (bool) ( $record['atelier_news'] ?? false ),
                'preferred_channel'  => $record['preferred_channel'] ?? 'both',
                'habeas_data_optout' => (bool) ( $record['habeas_data_optout'] ?? false ),
            ],
            'source'      => $record['source'] ?? 'unknown',
        ];

        $secret = defined( 'NH_N8N_WEBHOOK_SECRET' )
            ? NH_N8N_WEBHOOK_SECRET
            : ( function_exists( 'wp_salt' ) ? wp_salt( 'nonce' ) : 'nh_n8n_secret' );

        $body = function_exists( 'wp_json_encode' ) ? wp_json_encode( $payload ) : json_encode( $payload );
        $sig  = hash_hmac( 'sha256', $body, $secret );

        if ( function_exists( 'wp_remote_post' ) ) {
            wp_remote_post( $webhook_url, [
                'method'   => 'POST',
                'timeout'  => 3,
                'blocking' => false,
                'headers'  => [
                    'Content-Type'   => 'application/json',
                    'X-NH-Signature' => $sig,
                ],
                'body'     => $body,
            ] );
        }
    }

    public static function generate_token( string $email, int $expires ): string {
        return ( new PreferencesTokenService() )->generate_token( $email, $expires );
    }

    public static function validate_token( string $email, int $expires, string $token ): bool {
        return ( new PreferencesTokenService() )->validate_token( $email, $expires, $token );
    }

    public static function get_preference_url( string $email ): string {
        return ( new PreferencesTokenService() )->get_preference_url( $email );
    }
}
