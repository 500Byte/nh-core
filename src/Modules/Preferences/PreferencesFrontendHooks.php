<?php
namespace NormaHana\Core\Modules\Preferences;

use NormaHana\Core\Common\HookSubscriberInterface;

if (! defined('ABSPATH')) {
    exit;
}

class PreferencesFrontendHooks implements HookSubscriberInterface
{
    private PreferencesRepository $repository;
    private PreferencesTokenService $token_service;

    public function __construct(PreferencesRepository $repository, PreferencesTokenService $token_service)
    {
        $this->repository    = $repository;
        $this->token_service = $token_service;
    }

    public function register_hooks(): void
    {
        add_action('init', [ $this, 'register_endpoint' ]);
        add_filter('query_vars', [ $this, 'add_query_vars' ]);
        add_filter('woocommerce_account_menu_items', [ $this, 'add_account_menu_item' ]);
        add_action('woocommerce_account_preferencias_endpoint', [ $this, 'render_preferences_template' ]);
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);

        // Auth bypass para accesos tokenizados
        add_filter('woocommerce_account_endpoint_page_title', [ $this, 'filter_endpoint_title' ], 10, 2);
        add_action('template_redirect', [ $this, 'maybe_bypass_auth' ], 1);

        // AJAX update listener
        add_action('wp_ajax_nh_save_customer_preferences', [ $this, 'handle_ajax_save' ]);
        add_action('wp_ajax_nopriv_nh_save_customer_preferences', [ $this, 'handle_ajax_save' ]);
    }

    public function register_endpoint(): void
    {
        add_rewrite_endpoint('preferencias', EP_PAGES);
    }

    /**
     * @param array<int|string, mixed> $vars
     * @return array<int|string, mixed>
     */
    public function add_query_vars(array $vars): array
    {
        $vars[] = 'preferencias';
        return $vars;
    }

    /**
     * @param array<string, string> $items
     * @return array<string, string>
     */
    public function add_account_menu_item(array $items): array
    {
        $new_items = [];
        foreach ($items as $key => $val) {
            if ('customer-logout' === $key) {
                $new_items['preferencias'] = 'Comunicaciones y Privacidad';
            }
            $new_items[ $key ] = $val;
        }
        if (! isset($new_items['preferencias'])) {
            $new_items['preferencias'] = 'Comunicaciones y Privacidad';
        }
        return $new_items;
    }

    public function filter_endpoint_title(string $title, string $endpoint): string
    {
        if ('preferencias' === $endpoint) {
            return 'Comunicaciones y Privacidad';
        }
        return $title;
    }

    public function maybe_bypass_auth(): void
    {
        if (! is_account_page()) {
            return;
        }

        global $wp_query;
        if (! isset($wp_query->query_vars['preferencias'])) {
            return;
        }

        if (is_user_logged_in()) {
            return;
        }

        $email = isset($_GET['nh_email']) ? rawurldecode(sanitize_text_field($_GET['nh_email'])) : '';
        $exp   = isset($_GET['nh_exp']) ? (int) $_GET['nh_exp'] : 0;
        $token = isset($_GET['nh_token']) ? sanitize_text_field($_GET['nh_token']) : '';

        if (! empty($email) && ! empty($exp) && ! empty($token)) {
            if ($this->token_service->validate_token($email, $exp, $token)) {
                remove_action('template_redirect', 'wc_account_page_redirect_logged_out_user', 10);
            }
        }
    }

    public function enqueue_assets(): void
    {
        if (! is_account_page()) {
            return;
        }

        global $wp_query;
        if (! isset($wp_query->query_vars['preferencias'])) {
            return;
        }

        $css_url = defined('NH_CORE_URL') ? NH_CORE_URL . 'assets/css/nh-preferences.css' : plugin_dir_url(dirname(__DIR__, 2)) . 'assets/css/nh-preferences.css';
        $js_url  = defined('NH_CORE_URL') ? NH_CORE_URL . 'assets/js/nh-preferences.js' : plugin_dir_url(dirname(__DIR__, 2)) . 'assets/js/nh-preferences.js';

        wp_enqueue_style('nh-preferences-css', $css_url, [], '2.0.0');
        wp_enqueue_script('nh-preferences-js', $js_url, [ 'jquery' ], '2.0.0', true);

        $email = '';
        $exp   = 0;
        $token = '';

        if (is_user_logged_in()) {
            $user  = wp_get_current_user();
            $email = $user->user_email;
        } else {
            $email = isset($_GET['nh_email']) ? rawurldecode(sanitize_text_field($_GET['nh_email'])) : '';
            $exp   = isset($_GET['nh_exp']) ? (int) $_GET['nh_exp'] : 0;
            $token = isset($_GET['nh_token']) ? sanitize_text_field($_GET['nh_token']) : '';
        }

        wp_localize_script('nh-preferences-js', 'nhPreferencesData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nh_preferences_nonce'),
            'email'    => $email,
            'exp'      => $exp,
            'token'    => $token,
        ]);
    }

    public function render_preferences_template(): void
    {
        $email = '';
        $exp   = 0;
        $token = '';

        if (is_user_logged_in()) {
            $user  = wp_get_current_user();
            $email = $user->user_email;
        } else {
            $email = isset($_GET['nh_email']) ? rawurldecode(sanitize_text_field($_GET['nh_email'])) : '';
            $exp   = isset($_GET['nh_exp']) ? (int) $_GET['nh_exp'] : 0;
            $token = isset($_GET['nh_token']) ? sanitize_text_field($_GET['nh_token']) : '';
        }

        if (empty($email)) {
            echo '<div class="woocommerce-error" role="alert">Para gestionar tus preferencias, inicia sesión o accede mediante el enlace personalizado enviado a tu correo electrónico.</div>';
            return;
        }

        if (! is_user_logged_in()) {
            if (! $this->token_service->validate_token($email, $exp, $token)) {
                echo '<div class="woocommerce-error" role="alert">El enlace de acceso ha expirado o no es válido. Solicita un nuevo enlace o inicia sesión en tu cuenta.</div>';
                return;
            }
        }

        $preferences = $this->repository->get_preferences($email);
        $template_path = defined('NH_CORE_PATH') ? NH_CORE_PATH . 'templates/myaccount/preferences.php' : plugin_dir_path(dirname(__DIR__, 2)) . 'templates/myaccount/preferences.php';

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Error al cargar la plantilla de preferencias.</p>';
        }
    }

    public function handle_ajax_save(): void
    {
        check_ajax_referer('nh_preferences_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email((string) $_POST['email']) : '';
        $exp   = isset($_POST['exp']) ? (int) $_POST['exp'] : 0;
        $token = isset($_POST['token']) ? sanitize_text_field((string) $_POST['token']) : '';

        if (empty($email) || ! is_email($email)) {
            wp_send_json_error([ 'message' => 'Correo electrónico inválido.' ]);
        }

        if (! is_user_logged_in()) {
            if (! $this->token_service->validate_token($email, $exp, $token)) {
                wp_send_json_error([ 'message' => 'Token de seguridad inválido o expirado.' ]);
            }
        }

        $params = [
            'cart_reminders'     => isset($_POST['cart_reminders']) ? (int) $_POST['cart_reminders'] : 0,
            'atelier_news'       => isset($_POST['atelier_news']) ? (int) $_POST['atelier_news'] : 0,
            'preferred_channel'  => isset($_POST['preferred_channel']) ? sanitize_text_field((string) $_POST['preferred_channel']) : 'both',
            'habeas_data_optout' => isset($_POST['habeas_data_optout']) ? (int) $_POST['habeas_data_optout'] : 0,
            'optout_reason'      => isset($_POST['optout_reason']) ? sanitize_text_field((string) $_POST['optout_reason']) : '',
        ];

        $source = is_user_logged_in() ? 'web_account_page' : 'email_token_link';
        $saved  = $this->repository->save_preferences($email, $params, $source);

        if ($saved) {
            wp_send_json_success([ 'message' => 'Preferencias guardadas exitosamente.' ]);
        } else {
            wp_send_json_error([ 'message' => 'No se pudieron guardar las preferencias en la base de datos.' ]);
        }
    }
}
