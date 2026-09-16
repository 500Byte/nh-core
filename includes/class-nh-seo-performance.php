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
        add_action( 'init', [ __CLASS__, 'register_llms_txt_rewrite' ] );
        add_filter( 'query_vars', [ __CLASS__, 'register_llms_txt_query_var' ] );
        // accepted_args = 0: WordPress passes '' to 1-arg action callbacks fired with
        // no arguments, which would make serve_llms_txt()'s $echo flag falsy and serve
        // an empty body. Register with 0 args so its own defaults (true, true) apply.
        add_action( 'template_redirect', [ __CLASS__, 'serve_llms_txt' ], 0, 0 );
        add_action( 'send_headers', [ __CLASS__, 'cleanup_session_headers' ], 999 );
        add_action( 'template_redirect', [ __CLASS__, 'cleanup_session_headers' ], 1 );
        add_action( 'template_redirect', [ __CLASS__, 'start_drawer_heading_buffer' ], 5 );
        add_action( 'template_redirect', [ __CLASS__, 'set_public_cache_headers' ], 10 );
        add_filter( 'aioseo_sitemap_exclude_posts', [ __CLASS__, 'exclude_utility_pages_from_sitemap' ], 10, 2 );
        add_action( 'template_redirect', [ __CLASS__, 'apply_noindex_to_utility_pages' ] );
        add_filter( 'robots_txt', [ __CLASS__, 'append_robots_parameter_rules' ], 20, 2 );
        add_filter( 'aioseo_schema_output', [ __CLASS__, 'filter_aioseo_schema' ] );
        add_filter( 'aioseo_description', [ __CLASS__, 'filter_aioseo_description' ] );
        add_filter( 'term_description', [ __CLASS__, 'filter_term_description' ], 10, 3 );
        add_action( 'wp_head', [ __CLASS__, 'inject_responsive_lcp_preload' ], 1 );
        add_action( 'init', [ __CLASS__, 'enforce_single_llms_txt_source' ], 20 );
    }

    /**
     * Guarantees this module is the single source of truth for /llms.txt.
     *
     * AIOSEO Pro (>= 4.9) generates a physical llms.txt in ABSPATH from a scheduled
     * action. That file is served by nginx before WordPress runs, silently overriding
     * the rewrite registered here. Disabling AIOSEO's "sitemap.llms.enable" option is
     * NOT enough: its generateLlmsTxt() guard uses isset() against a magic property,
     * which returns false for the boolean false value, so already-scheduled actions
     * keep regenerating the file. Detach the generator callbacks so nh-core wins.
     *
     * Because the embedded manifest is now the single source, ANY root-level llms.txt
     * is stale by definition (it would be served statically and bypass this handler),
     * so a plain unlink is applied rather than only removing AIOSEO-marked files.
     *
     * @return void
     */
    public static function enforce_single_llms_txt_source() {
        if ( function_exists( 'aioseo' ) ) {
            if ( ! empty( aioseo()->options->sitemap->llms->enable ) ) {
                aioseo()->options->sitemap->llms->enable = false;
                aioseo()->options->save();
            }

            if ( isset( aioseo()->llms ) ) {
                remove_action( 'aioseo_generate_llms_txt', [ aioseo()->llms, 'generateLlmsTxt' ] );
                remove_action( 'aioseo_generate_llms_txt_single', [ aioseo()->llms, 'generateLlmsTxt' ] );
            }
        }

        if ( ! defined( 'ABSPATH' ) ) {
            return;
        }

        $file = ABSPATH . 'llms.txt';
        if ( file_exists( $file ) ) {
            @unlink( $file );
        }
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

        if ( is_admin() || $is_ajax || $is_cron || $is_rest || is_feed() ) {
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

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
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
     * Fallback meta descriptions for primary product categories.
     *
     * @var array<string, string>
     */
    private static $category_meta_descriptions = [
        'vestidos'  => 'Descubre vestidos de autor en lino caribeño premium con siluetas fluidas y confección artesanal. Diseños sostenibles hechos en Colombia.',
        'conjuntos' => 'Sets y conjuntos de lino para mujer con elegancia atemporal. Piezas versátiles de moda sostenible inspiradas en el Caribe para toda ocasión.',
        'pantalon'  => 'Pantalones de lino para mujer de tiro alto y bota recta. Comodidad, frescura y caída impecable confeccionados éticamente en Colombia.',
        'falda'     => 'Faldas de lino con movimiento y diseño artesanal caribeño. Siluetas envolventes y sofisticadas para un estilo fresco y sostenible.',
        'top'       => 'Tops y blusas de lino con amarres y lazos adaptables. Diseño consciente para acompañar cualquier ocasión cálida, en lino y sus mezclas.',
        'bermudas'  => 'Bermudas de lino con calce cómodo y diseño estructurado. La prenda esencial de clima cálido para estilismos frescos, elegantes y atemporales.',
    ];

    /**
     * Returns the array of configured category meta descriptions.
     *
     * @return array<string, string> Map of category slug => description.
     */
    public static function get_category_meta_descriptions() {
        return self::$category_meta_descriptions;
    }

    /**
     * Enriches AIOSEO schema output with OnlineStore, Santa Marta address, and Merchant specs.
     *
     * @param array $graphs Array of Schema.org graph items.
     * @return array Enriched Schema.org graph items.
     */
    public static function filter_aioseo_schema( $graphs ) {
        if ( ! is_array( $graphs ) ) {
            return $graphs;
        }

        foreach ( $graphs as &$graph ) {
            if ( ! is_array( $graph ) || ! isset( $graph['@type'] ) ) {
                continue;
            }

            $types = (array) $graph['@type'];
            if ( in_array( 'Organization', $types, true ) || in_array( 'LocalBusiness', $types, true ) ) {
                $graph['@type'] = [ 'Organization', 'OnlineStore', 'LocalBusiness' ];
                $graph['address'] = [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => 'Santa Marta',
                    'addressLocality' => 'Santa Marta',
                    'addressRegion'   => 'Magdalena',
                    'postalCode'      => '470001',
                    'addressCountry'  => 'CO',
                ];
                $graph['hasMerchantReturnPolicy'] = [
                    '@type'                  => 'MerchantReturnPolicy',
                    'applicableCountry'      => 'CO',
                    'returnPolicyCategory'   => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays'     => 30,
                    'returnMethod'           => 'https://schema.org/ReturnByMail',
                    'returnFees'             => 'https://schema.org/FreeReturn',
                ];
            }
        }
        unset( $graph );

        return $graphs;
    }

    /**
     * Resolves the current category slug from query object or request URI.
     *
     * @return string Category slug if on a category archive, empty string otherwise.
     */
    public static function get_current_category_slug() {
        if ( function_exists( 'is_product_category' ) && is_product_category() ) {
            $term = function_exists( 'get_queried_object' ) ? get_queried_object() : null;
            if ( $term && isset( $term->slug ) ) {
                return (string) $term->slug;
            }
        }

        if ( function_exists( 'is_tax' ) && is_tax( 'product_cat' ) ) {
            $term = function_exists( 'get_queried_object' ) ? get_queried_object() : null;
            if ( $term && isset( $term->slug ) ) {
                return (string) $term->slug;
            }
        }

        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            if ( preg_match( '#/c/([^/]+)/?#', (string) $path, $matches ) ) {
                return (string) $matches[1];
            }
        }

        return '';
    }

    /**
     * Filters AIOSEO meta description, injecting fallback category description if empty.
     *
     * @param string $description Current meta description.
     * @return string Filtered meta description.
     */
    public static function filter_aioseo_description( $description ) {
        if ( is_string( $description ) && '' !== trim( $description ) ) {
            return $description;
        }

        $slug = self::get_current_category_slug();
        if ( $slug && isset( self::$category_meta_descriptions[ $slug ] ) ) {
            return self::$category_meta_descriptions[ $slug ];
        }

        return (string) $description;
    }

    /**
     * Filters WordPress term description, injecting fallback category description if empty.
     *
     * @param string $description Current term description.
     * @param int    $term_id     Term ID.
     * @param string $taxonomy    Taxonomy name.
     * @return string Filtered term description.
     */
    public static function filter_term_description( $description, $term_id = 0, $taxonomy = 'product_cat' ) {
        if ( is_string( $description ) && '' !== trim( $description ) ) {
            return $description;
        }

        if ( $taxonomy && 'product_cat' !== $taxonomy ) {
            return $description;
        }

        $slug = '';
        if ( $term_id && function_exists( 'get_term' ) ) {
            $term = get_term( $term_id, 'product_cat' );
            if ( $term && ! is_wp_error( $term ) && isset( $term->slug ) ) {
                $slug = $term->slug;
            }
        }

        if ( ! $slug ) {
            $slug = self::get_current_category_slug();
        }

        if ( $slug && isset( self::$category_meta_descriptions[ $slug ] ) ) {
            return self::$category_meta_descriptions[ $slug ];
        }

        return $description;
    }

    /**
     * Injects responsive image preload tags for Hero banner to optimize LCP without duplicate downloads.
     * Hooked to wp_head at priority 1 on the front page.
     *
     * @return bool True if preloads were emitted, false otherwise.
     */
    public static function inject_responsive_lcp_preload() {
        $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : false;
        $is_cron = function_exists( 'wp_doing_cron' ) ? wp_doing_cron() : false;
        $is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

        if ( is_admin() || $is_ajax || $is_cron || $is_rest ) {
            return false;
        }

        if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
            return false;
        }

        $default_hero = 'https://www.normahana.com/wp-content/uploads/2026/07/fashionmodelstrikinghautecou202607051620.avif';

        $hero_mobile  = apply_filters( 'nh_lcp_hero_mobile_url', $default_hero );
        $default_mobile_type = self::mime_type_for_url( (string) $hero_mobile );
        $mobile_type  = apply_filters( 'nh_lcp_hero_mobile_type', $default_mobile_type );

        $hero_desktop = apply_filters( 'nh_lcp_hero_desktop_url', $default_hero );
        $default_desktop_type = self::mime_type_for_url( (string) $hero_desktop );
        $desktop_type = apply_filters( 'nh_lcp_hero_desktop_type', $default_desktop_type );

        $mobile_url  = function_exists( 'esc_url' ) ? esc_url( $hero_mobile ) : filter_var( (string) $hero_mobile, FILTER_SANITIZE_URL );
        $desktop_url = function_exists( 'esc_url' ) ? esc_url( $hero_desktop ) : filter_var( (string) $hero_desktop, FILTER_SANITIZE_URL );
        $mobile_type_attr  = function_exists( 'esc_attr' ) ? esc_attr( $mobile_type ) : htmlspecialchars( (string) $mobile_type, ENT_QUOTES, 'UTF-8' );
        $desktop_type_attr = function_exists( 'esc_attr' ) ? esc_attr( $desktop_type ) : htmlspecialchars( (string) $desktop_type, ENT_QUOTES, 'UTF-8' );

        if ( ! empty( $mobile_url ) ) {
            echo '<link rel="preload" as="image" href="' . $mobile_url . '" type="' . $mobile_type_attr . '" fetchpriority="high" media="(max-width: 767px)">' . "\n";
        }

        if ( ! empty( $desktop_url ) ) {
            echo '<link rel="preload" as="image" href="' . $desktop_url . '" type="' . $desktop_type_attr . '" fetchpriority="high" media="(min-width: 768px)">' . "\n";
        }

        return true;
    }

    /**
     * Resolves the MIME type for an image URL based on its file extension.
     * Supports avif, webp, jpg, jpeg, png, and gif; falls back to image/avif.
     *
     * @param string $url Image URL to inspect.
     * @return string MIME type string (e.g. 'image/avif').
     */
    private static function mime_type_for_url( $url ) {
        $ext_mime_map = [
            'avif' => 'image/avif',
            'webp' => 'image/webp',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
        ];
        $ext = strtolower( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
        return $ext_mime_map[ $ext ] ?? 'image/avif';
    }

    /**
     * Default standard llms.txt manifest content.
     *
     * @var string
     */
    private static $default_llms_manifest = <<<TEXT
# Norma Hana

> Marca colombiana de moda de autor, diseño consciente y sastrería femenina en lino caribeño. Confección artesanal desde Santa Marta, Colombia.

## Catálogo y Colecciones
- Vestidos de Lino: https://www.normahana.com/c/vestidos/
- Conjuntos y Sets de Dos Piezas: https://www.normahana.com/c/conjuntos/
- Pantalones y Bermudas: https://www.normahana.com/c/pantalon/
- Blusas y Tops Adaptables: https://www.normahana.com/c/top/

## Filosofía de Marca y Materiales
- Confección en lino caribeño transpirable de alta densidad.
- Siluetas acogedoras con sistemas de amarre ajustables que acompañan los cambios del cuerpo femenino.
- Sostenibilidad, producción justa y comercio ético en el Caribe colombiano.

## Políticas de Servicio
- Envíos a todo el territorio nacional en Colombia (2 a 5 días hábiles).
- Envíos internacionales disponibles.
- Cambios y garantías: 30 días calendario para prendas sin uso.
TEXT;

    /**
     * Registers custom rewrite rule for /llms.txt endpoint.
     */
    public static function register_llms_txt_rewrite() {
        if ( function_exists( 'add_rewrite_rule' ) ) {
            add_rewrite_rule( '^llms\.txt$', 'index.php?nh_llms_txt=1', 'top' );
        }
    }

    /**
     * Registers query variable for llms.txt request detection.
     *
     * @param array $vars Public query variables.
     * @return array Modified query variables.
     */
    public static function register_llms_txt_query_var( $vars ) {
        if ( is_array( $vars ) ) {
            $vars[] = 'nh_llms_txt';
        }
        return $vars;
    }

    /**
     * Checks if current request is for /llms.txt.
     *
     * @return bool True if requesting /llms.txt, false otherwise.
     */
    public static function is_llms_txt_request() {
        if ( function_exists( 'get_query_var' ) && (bool) get_query_var( 'nh_llms_txt' ) ) {
            return true;
        }

        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
        $req_path    = parse_url( $current_uri, PHP_URL_PATH );
        $clean_path  = '/' . trim( (string) $req_path, '/' );

        return ( '/llms.txt' === $clean_path );
    }

    /**
     * Returns the llms.txt manifest.
     *
     * The embedded manifest is the SINGLE source of truth. No external file is ever
     * read: a root-level llms.txt would be mapped to ABSPATH and served directly by
     * nginx, silently bypassing this module (and any WP-level guard). Keeping the
     * content in code makes this handler the only possible source.
     *
     * @return string Manifest content.
     */
    public static function get_llms_txt_content() {
        return self::$default_llms_manifest;
    }

    /**
     * Serves standard /llms.txt manifest with text/plain header and HTTP 200.
     *
     * @param bool $echo      Whether to echo the content.
     * @param bool $terminate Whether to terminate execution (exit) after serving.
     * @return bool True if served, false if not an llms.txt request.
     */
    public static function serve_llms_txt( $echo = true, $terminate = true ) {
        if ( ! self::is_llms_txt_request() ) {
            return false;
        }

        if ( ! headers_sent() ) {
            if ( function_exists( 'status_header' ) ) {
                status_header( 200 );
            } elseif ( function_exists( 'http_response_code' ) ) {
                http_response_code( 200 );
            }
            header( 'Content-Type: text/plain; charset=utf-8' );
            header( 'Cache-Control: public, max-age=3600, s-maxage=86400, stale-while-revalidate=600' );
            header( 'X-Robots-Tag: all', true );
        }

        $content = self::get_llms_txt_content();
        if ( $echo ) {
            echo $content;
        }

        if ( $terminate ) {
            exit;
        }

        return true;
    }

    /**
     * Resets heading buffer flag (primarily for testing).
     */
    public static function reset_heading_buffer_flag() {
        self::$heading_buffer_started = false;
    }
}
