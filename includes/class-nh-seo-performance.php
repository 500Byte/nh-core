<?php
/**
 * SEO and Performance Optimization Module for Norma Hana.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_SEO_Performance {

    /**
     * Tracks whether output buffer for drawer headings has been initiated.
     *
     * @var bool
     */
    private static $heading_buffer_started = false;

    /**
     * Initializes hooks for session management, cache headers, and output buffering.
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'guard_php_sessions' ], 1 );
        add_action( 'send_headers', [ __CLASS__, 'cleanup_session_headers' ], 999 );
        add_action( 'template_redirect', [ __CLASS__, 'cleanup_session_headers' ], 1 );
        add_action( 'template_redirect', [ __CLASS__, 'start_drawer_heading_buffer' ], 5 );
        add_action( 'template_redirect', [ __CLASS__, 'set_public_cache_headers' ], 10 );
        add_filter( 'aioseo_sitemap_exclude_posts', [ __CLASS__, 'exclude_utility_pages_from_sitemap' ], 10, 2 );
        add_action( 'template_redirect', [ __CLASS__, 'apply_noindex_to_utility_pages' ] );
        add_filter( 'robots_txt', [ __CLASS__, 'append_robots_parameter_rules' ], 20, 2 );
    }

    /**
     * Starts output buffering for drawer heading sanitization on frontend HTML pages.
     * Hooked to template_redirect.
     *
     * @return bool True if output buffer was started, false otherwise.
     */
    public static function start_drawer_heading_buffer() {
        $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : false;
        $is_cron = function_exists( 'wp_doing_cron' ) ? wp_doing_cron() : false;
        $is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

        if ( is_admin() || $is_ajax || $is_cron || $is_rest ) {
            return false;
        }

        if ( ! self::$heading_buffer_started ) {
            self::$heading_buffer_started = true;
            ob_start( [ __CLASS__, 'sanitize_drawer_headings' ] );
            return true;
        }

        return false;
    }

    /**
     * Checks if current request is from an anonymous visitor without active cart or session.
     *
     * @return bool True if visitor is anonymous with no cart or WooCommerce session.
     */
    public static function is_anonymous_visitor() {
        if ( is_user_logged_in() ) {
            return false;
        }

        if ( isset( $_COOKIE['woocommerce_items_in_cart'] ) && '1' === (string) $_COOKIE['woocommerce_items_in_cart'] ) {
            return false;
        }

        if ( self::has_wc_session_cookie() ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if WooCommerce session cookie is present.
     *
     * @return bool True if WooCommerce session cookie exists.
     */
    public static function has_wc_session_cookie() {
        if ( defined( 'COOKIEHASH' ) && isset( $_COOKIE[ 'wp_woocommerce_session_' . COOKIEHASH ] ) ) {
            return true;
        }

        foreach ( array_keys( $_COOKIE ) as $key ) {
            if ( strpos( $key, 'wp_woocommerce_session_' ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prevents unwanted PHP session initialization for anonymous crawlers and guests at init:1.
     * Configures PHP session ini directives early so subsequent session_start calls
     * (e.g. from JetEngine or JetCompareWishlist at parse_request) do not transmit Set-Cookie or cache limiters.
     *
     * @return bool True if guard applied for anonymous visitor, false otherwise.
     */
    public static function guard_php_sessions() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return false;
        }

        if ( ! self::is_anonymous_visitor() ) {
            return false;
        }

        if ( session_status() === PHP_SESSION_ACTIVE ) {
            session_write_close();
        }

        // Configure PHP early to disable session cookies and cache limiter for anonymous visitors
        ini_set( 'session.use_cookies', '0' );
        ini_set( 'session.cache_limiter', '' );

        return true;
    }

    /**
     * Closes active sessions and strips PHPSESSID Set-Cookie headers for anonymous visitors.
     * Hooks to send_headers (priority 999) and template_redirect (priority 1) to catch
     * any sessions opened during parse_request or later hooks.
     *
     * @return bool True if cleanup ran for anonymous visitor, false otherwise.
     */
    public static function cleanup_session_headers() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return false;
        }

        if ( ! self::is_anonymous_visitor() ) {
            return false;
        }

        if ( session_status() === PHP_SESSION_ACTIVE ) {
            session_write_close();
        }

        if ( ! headers_sent() ) {
            $filtered = self::filter_phpsessid_cookies( headers_list() );
            if ( $filtered['has_phpsessid'] ) {
                header_remove( 'Set-Cookie' );
                foreach ( $filtered['cookies_to_keep'] as $cookie_header ) {
                    header( $cookie_header, false );
                }
            }
        }

        return true;
    }

    /**
     * Filters out PHPSESSID from cookie headers list while preserving other cookies.
     *
     * @param array $headers List of headers (e.g. from headers_list()).
     * @return array Array with 'has_phpsessid' bool and 'cookies_to_keep' array.
     */
    public static function filter_phpsessid_cookies( array $headers ) {
        $cookies_to_keep = [];
        $has_phpsessid   = false;

        foreach ( $headers as $header ) {
            if ( stripos( $header, 'Set-Cookie:' ) === 0 ) {
                if ( stripos( $header, 'PHPSESSID' ) !== false ) {
                    $has_phpsessid = true;
                } else {
                    $cookies_to_keep[] = $header;
                }
            }
        }

        return [
            'has_phpsessid'   => $has_phpsessid,
            'cookies_to_keep' => $cookies_to_keep,
        ];
    }

    /**
     * Sets public cache-control headers on static anonymous GET and HEAD requests.
     *
     * @return bool True if public cache headers were set, false otherwise.
     */
    public static function set_public_cache_headers() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return false;
        }

        if ( is_user_logged_in() || is_cart() || is_checkout() || is_account_page() ) {
            return false;
        }

        if ( isset( $_COOKIE['woocommerce_items_in_cart'] ) && '1' === (string) $_COOKIE['woocommerce_items_in_cart'] ) {
            return false;
        }

        if ( self::has_wc_session_cookie() ) {
            return false;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if ( in_array( $method, [ 'GET', 'HEAD' ], true ) && ! headers_sent() ) {
            header( 'Cache-Control: public, max-age=3600, s-maxage=86400, stale-while-revalidate=600' );
            header_remove( 'Pragma' );
            return true;
        }

        return false;
    }

    /**
     * Excludes utility pages from AIOSEO sitemap generation.
     *
     * @param array  $ids  List of excluded post IDs.
     * @param string $type Sitemap type (e.g. 'general').
     * @return array Modified array of post IDs to exclude.
     */
    public static function exclude_utility_pages_from_sitemap( $ids = [], $type = 'general' ) {
        $excluded_slugs = [
            'yith-compare',
            'communication-preferences',
            'proximamente',
            'landing',
            'ingresar',
            'lista-de-deseos',
        ];

        $excluded_ids = [];
        foreach ( $excluded_slugs as $slug ) {
            if ( function_exists( 'get_page_by_path' ) ) {
                $page = get_page_by_path( $slug );
                if ( $page ) {
                    $excluded_ids[] = is_object( $page ) ? (int) $page->ID : (int) $page;
                }
            }
        }

        return array_values( array_unique( array_merge( (array) $ids, $excluded_ids ) ) );
    }

    /**
     * Enforces noindex, follow headers on utility pages.
     *
     * @return bool True if noindex was applied to utility page, false otherwise.
     */
    public static function apply_noindex_to_utility_pages() {
        $utility_paths = [
            '/yith-compare',
            '/communication-preferences',
            '/proximamente',
            '/landing',
            '/ingresar',
            '/lista-de-deseos',
            '/c/sin-categorizar',
        ];

        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
        $req_path    = parse_url( $current_uri, PHP_URL_PATH );
        $req_path    = '/' . trim( (string) $req_path, '/' );

        foreach ( $utility_paths as $path ) {
            $clean_path = '/' . trim( $path, '/' );
            if ( $req_path === $clean_path || str_starts_with( $req_path, $clean_path . '/' ) ) {
                if ( ! headers_sent() ) {
                    header( 'X-Robots-Tag: noindex, follow', true );
                }
                if ( function_exists( 'add_filter' ) ) {
                    add_filter( 'aioseo_robots_meta', function( $meta = [] ) {
                        if ( is_array( $meta ) ) {
                            $meta['noindex']  = 'noindex';
                            $meta['nofollow'] = 'follow';
                            return $meta;
                        }
                        return [ 'noindex' => 'noindex', 'nofollow' => 'follow' ];
                    } );
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Protects crawl budget against faceted query spam.
     *
     * @param string $output Existing robots.txt output.
     * @param bool   $public Whether the site is public.
     * @return string Modified robots.txt output.
     */
    public static function append_robots_parameter_rules( $output, $public = true ) {
        $rules  = "\n# Crawl Budget Faceted Filter Protection\n";
        $rules .= "Disallow: /*?filter_*\n";
        $rules .= "Disallow: /*?min_price=*\n";
        $rules .= "Disallow: /*?max_price=*\n";
        return (string) $output . $rules;
    }

    /**
     * Sanitizes off-canvas drawer headings from <h2> to <span class="... drawer-title">.
     * Replaces drawer title headings (Tu carrito, Buscar, Lista de deseos, Menú)
     * with a styled span tag to eliminate semantic heading pollution while preserving
     * all original HTML attributes (classes, IDs, ARIA, data-*).
     *
     * @param string $buffer Raw HTML output buffer content.
     * @return string Sanitized HTML content.
     */
    public static function sanitize_drawer_headings( $buffer ) {
        if ( ! is_string( $buffer ) || '' === trim( $buffer ) ) {
            return $buffer;
        }

        return preg_replace_callback(
            '/<h2([^>]*)>\s*(Tu carrito|Buscar|Lista de deseos|Menú)\s*<\/h2>/iu',
            function ( $matches ) {
                $attrs = $matches[1];
                $text  = $matches[2];
                if ( preg_match( '/\bclass\s*=\s*["\']([^"\']*)["\']/i', $attrs, $class_match ) ) {
                    $merged_classes = trim( $class_match[1] . ' drawer-title' );
                    $escaped_class  = function_exists( 'esc_attr' ) ? esc_attr( $merged_classes ) : htmlspecialchars( $merged_classes, ENT_QUOTES, 'UTF-8' );
                    $attrs = preg_replace( '/\bclass\s*=\s*["\']([^"\']*)["\']/i', 'class="' . $escaped_class . '"', $attrs, 1 );
                } else {
                    $attrs .= ' class="drawer-title"';
                }
                return '<span' . $attrs . '>' . $text . '</span>';
            },
            $buffer
        );
    }

    /**
     * Resets heading buffer flag (primarily for testing).
     */
    public static function reset_heading_buffer_flag() {
        self::$heading_buffer_started = false;
    }
}
