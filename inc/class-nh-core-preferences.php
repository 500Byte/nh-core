<?php
/**
 * NH_Core_Preferences
 *
 * Modelo de datos y repositorio para el Centro de Preferencias de Comunicación y Privacidad.
 * Gestiona la tabla wp_nh_communication_preferences y sincroniza con wp_usermeta.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Preferences {

    /**
     * Instancia singleton.
     *
     * @var NH_Core_Preferences|null
     */
    private static $instance = null;

    /**
     * Nombre base de la tabla sin prefijo.
     */
    const TABLE_NAME = 'nh_communication_preferences';

    /**
     * Retorna la instancia singleton.
     *
     * @return NH_Core_Preferences
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado. Engancha hooks iniciales.
     */
    private function __construct() {
        add_action( 'init', [ $this, 'maybe_create_table' ], 5 );
        add_action( 'nh_preferences_saved', [ $this, 'dispatch_n8n_webhook' ], 10, 2 );
    }

    /**
     * Retorna el nombre de tabla completo con prefijo de WordPress.
     *
     * @return string
     */
    public static function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Verifica la versión de base de datos y crea la tabla si es necesario.
     */
    public function maybe_create_table() {
        if ( get_option( 'nh_preferences_db_version' ) === '1.0.0' ) {
            return;
        }
        $this->create_table();
    }

    /**
     * Crea o actualiza la tabla wp_nh_communication_preferences usando dbDelta.
     */
    public function create_table() {
        global $wpdb;
        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(190) NOT NULL,
            user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            cart_reminders TINYINT(1) NOT NULL DEFAULT 1,
            atelier_news TINYINT(1) NOT NULL DEFAULT 1,
            preferred_channel VARCHAR(20) NOT NULL DEFAULT 'both',
            habeas_data_optout TINYINT(1) NOT NULL DEFAULT 0,
            optout_reason VARCHAR(255) NULL DEFAULT NULL,
            source VARCHAR(50) NOT NULL DEFAULT 'email_link',
            ip_address VARCHAR(45) NULL DEFAULT NULL,
            user_agent VARCHAR(255) NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY uk_email (email),
            KEY idx_user_id (user_id),
            KEY idx_cart_reminders (cart_reminders)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        update_option( 'nh_preferences_db_version', '1.0.0' );
    }

    /**
     * Obtiene las preferencias de un correo electrónico.
     *
     * @param string $email Correo electrónico del cliente.
     * @return array Estructura de preferencias.
     */
    public function get_preferences( string $email ): array {
        global $wpdb;
        $clean_email = sanitize_email( strtolower( trim( $email ) ) );
        if ( empty( $clean_email ) ) {
            return $this->get_default_preferences();
        }

        $table = self::get_table_name();
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", $clean_email ), ARRAY_A );

        if ( ! $row ) {
            $user = get_user_by( 'email', $clean_email );
            if ( $user ) {
                $cart_meta    = get_user_meta( $user->ID, '_nh_pref_cart_reminders', true );
                $news_meta    = get_user_meta( $user->ID, '_nh_pref_atelier_news', true );
                $channel_meta = get_user_meta( $user->ID, '_nh_pref_preferred_channel', true );
                $optout_meta  = get_user_meta( $user->ID, '_nh_pref_habeas_data_optout', true );

                return [
                    'email'              => $clean_email,
                    'user_id'            => $user->ID,
                    'cart_reminders'     => ( '' !== $cart_meta && false !== $cart_meta ) ? (int) $cart_meta : 1,
                    'atelier_news'       => ( '' !== $news_meta && false !== $news_meta ) ? (int) $news_meta : 1,
                    'preferred_channel'  => ! empty( $channel_meta ) ? $channel_meta : 'both',
                    'habeas_data_optout' => ( '' !== $optout_meta && false !== $optout_meta ) ? (int) $optout_meta : 0,
                    'is_new'             => true,
                ];
            }
            return array_merge( $this->get_default_preferences(), [
                'email'  => $clean_email,
                'is_new' => true,
            ] );
        }

        return [
            'email'              => $row['email'],
            'user_id'            => ! empty( $row['user_id'] ) ? (int) $row['user_id'] : null,
            'cart_reminders'     => (int) $row['cart_reminders'],
            'atelier_news'       => (int) $row['atelier_news'],
            'preferred_channel'  => $row['preferred_channel'],
            'habeas_data_optout' => (int) $row['habeas_data_optout'],
            'is_new'             => false,
        ];
    }

    /**
     * Retorna las preferencias por defecto.
     *
     * @return array
     */
    public function get_default_preferences(): array {
        return [
            'email'              => '',
            'user_id'            => null,
            'cart_reminders'     => 1,
            'atelier_news'       => 1,
            'preferred_channel'  => 'both',
            'habeas_data_optout' => 0,
            'is_new'             => true,
        ];
    }

    /**
     * Guarda o actualiza las preferencias para un correo y sincroniza usermeta si existe usuario.
     *
     * @param string $email Correo electrónico.
     * @param array  $data Datos de preferencias recibidos.
     * @param string $source Origen del cambio ('web_account_page', 'email_link', 'admin', etc.).
     * @return bool True si la operación fue exitosa.
     */
    public function save_preferences( string $email, array $data, string $source = 'web_account_page' ): bool {
        global $wpdb;
        $clean_email = sanitize_email( strtolower( trim( $email ) ) );
        if ( empty( $clean_email ) ) {
            return false;
        }

        $user    = get_user_by( 'email', $clean_email );
        $user_id = $user ? $user->ID : ( ! empty( $data['user_id'] ) ? (int) $data['user_id'] : null );

        $cart_reminders     = isset( $data['cart_reminders'] ) ? (int) (bool) $data['cart_reminders'] : 1;
        $atelier_news       = isset( $data['atelier_news'] ) ? (int) (bool) $data['atelier_news'] : 1;
        $preferred_channel  = in_array( $data['preferred_channel'] ?? '', [ 'whatsapp', 'email', 'both' ], true ) ? $data['preferred_channel'] : 'both';
        $habeas_data_optout = isset( $data['habeas_data_optout'] ) ? (int) (bool) $data['habeas_data_optout'] : 0;
        $optout_reason      = isset( $data['optout_reason'] ) ? sanitize_text_field( substr( $data['optout_reason'], 0, 255 ) ) : null;

        if ( 1 === $habeas_data_optout ) {
            $cart_reminders = 0;
            $atelier_news   = 0;
        }

        $table      = self::get_table_name();
        $ip_address = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
        $user_agent = sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255 ) );

        $record = [
            'email'              => $clean_email,
            'user_id'            => $user_id,
            'cart_reminders'     => $cart_reminders,
            'atelier_news'       => $atelier_news,
            'preferred_channel'  => $preferred_channel,
            'habeas_data_optout' => $habeas_data_optout,
            'optout_reason'      => $optout_reason,
            'source'             => sanitize_key( $source ),
            'ip_address'         => $ip_address,
            'user_agent'         => $user_agent,
            'updated_at'         => current_time( 'mysql' ),
        ];

        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE email = %s", $clean_email ) );

        if ( $existing ) {
            $updated = $wpdb->update( $table, $record, [ 'id' => $existing ] );
        } else {
            $record['created_at'] = current_time( 'mysql' );
            $updated              = $wpdb->insert( $table, $record );
        }

        if ( $user_id ) {
            update_user_meta( $user_id, '_nh_pref_cart_reminders', $cart_reminders );
            update_user_meta( $user_id, '_nh_pref_atelier_news', $atelier_news );
            update_user_meta( $user_id, '_nh_pref_preferred_channel', $preferred_channel );
            update_user_meta( $user_id, '_nh_pref_habeas_data_optout', $habeas_data_optout );
            update_user_meta( $user_id, '_nh_pref_updated_at', current_time( 'mysql' ) );
        }

        do_action( 'nh_preferences_saved', $clean_email, $record );
        return false !== $updated;
    }

    /**
     * Despacha un webhook saliente asíncrono a n8n cuando se actualizan las preferencias de un cliente.
     *
     * @param string $email  Correo electrónico del cliente.
     * @param array  $record Registro de preferencias guardado.
     * @return void
     */
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

    // =========================================================================
    // Métodos criptográficos y arquitectura de tokens HMAC
    // =========================================================================

    /**
     * Genera un token criptográfico HMAC-SHA256 para autenticar accesos sin login.
     *
     * @param string $email Correo electrónico del cliente.
     * @param int    $expires Timestamp UNIX de expiración del enlace.
     * @return string Token hexadecimal HMAC-SHA256.
     */
    public static function generate_token( string $email, int $expires ): string {
        $normalized_email = strtolower( trim( $email ) );
        $salt             = function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : 'normahana_pref_fallback_salt';
        $data             = $normalized_email . '|' . $expires;
        return hash_hmac( 'sha256', $data, $salt );
    }

    /**
     * Valida un token criptográfico contra el correo y expiración provistos en tiempo constante.
     *
     * @param string $email Correo electrónico recibido.
     * @param int    $expires Timestamp de expiración recibido.
     * @param string $token Token recibido para validar.
     * @return bool True si el token es válido y no ha expirado.
     */
    public static function validate_token( string $email, int $expires, string $token ): bool {
        if ( empty( $email ) || empty( $expires ) || empty( $token ) ) {
            return false;
        }
        if ( time() > $expires ) {
            return false;
        }
        $expected = self::generate_token( $email, $expires );
        return hash_equals( $expected, $token );
    }

    /**
     * Construye la URL tokenizada firmada para acceso directo al centro de preferencias.
     *
     * @param string $email Correo electrónico del cliente.
     * @return string URL firmada con parámetros nh_email, nh_exp y nh_token.
     */
    public static function get_preference_url( string $email ): string {
        $clean_email    = function_exists( 'sanitize_email' ) ? sanitize_email( strtolower( trim( $email ) ) ) : strtolower( trim( $email ) );
        $day_in_seconds = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;
        $expires        = time() + ( 30 * $day_in_seconds );
        $token          = self::generate_token( $clean_email, $expires );

        if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
            $base = wc_get_account_endpoint_url( 'preferencias' );
        } elseif ( function_exists( 'home_url' ) ) {
            $base = home_url( '/mi-cuenta/preferencias/' );
        } else {
            $base = 'https://normahana.com/mi-cuenta/preferencias/';
        }

        $query_args = [
            'nh_email' => rawurlencode( $clean_email ),
            'nh_exp'   => $expires,
            'nh_token' => $token,
        ];

        if ( function_exists( 'add_query_arg' ) ) {
            return add_query_arg( $query_args, $base );
        }

        $query = http_build_query( $query_args, '', '&', PHP_QUERY_RFC3986 );
        return $base . ( strpos( $base, '?' ) !== false ? '&' : '?' ) . $query;
    }
}
