<?php
namespace NormaHana\Core\Modules\Preferences;

if (! defined('ABSPATH')) {
    exit;
}

class PreferencesTokenService
{
    public function generate_token(string $email, int $expires): string
    {
        $normalized_email = strtolower(trim($email));
        $salt             = function_exists('wp_salt') ? wp_salt('auth') : 'normahana_pref_fallback_salt';
        $data             = $normalized_email . '|' . $expires;
        return hash_hmac('sha256', $data, $salt);
    }

    public function validate_token(string $email, int $expires, string $token): bool
    {
        if (empty($email) || empty($expires) || empty($token)) {
            return false;
        }
        if (time() > $expires) {
            return false;
        }
        $expected = $this->generate_token($email, $expires);
        return hash_equals($expected, $token);
    }

    public function get_preference_url(string $email): string
    {
        $clean_email    = function_exists('sanitize_email') ? sanitize_email(strtolower(trim($email))) : strtolower(trim($email));
        $day_in_seconds = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS : 86400;
        $expires        = time() + ( 30 * $day_in_seconds );
        $token          = $this->generate_token($clean_email, $expires);

        if (function_exists('wc_get_account_endpoint_url')) {
            $base = wc_get_account_endpoint_url('preferencias');
        } elseif (function_exists('home_url')) {
            $base = home_url('/mi-cuenta/preferencias/');
        } else {
            $base = 'https://normahana.com/mi-cuenta/preferencias/';
        }

        $query_args = [
            'nh_email' => rawurlencode($clean_email),
            'nh_exp'   => $expires,
            'nh_token' => $token,
        ];

        if (function_exists('add_query_arg')) {
            return add_query_arg($query_args, $base);
        }

        $query = http_build_query($query_args, '', '&', PHP_QUERY_RFC3986);
        return $base . ( strpos($base, '?') !== false ? '&' : '?' ) . $query;
    }
}
