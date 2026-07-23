<?php
/**
 * nh-core GitHub-based updater.
 *
 * Checks GitHub Releases for new versions and integrates with
 * WordPress native plugin update system.
 *
 * @package    NH_Core
 * @subpackage Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NH_Core_Updater {

    /**
     * GitHub repository (owner/repo).
     *
     * @var string
     */
    private $github_repo;

    /**
     * Plugin main file path.
     *
     * @var string
     */
    private $plugin_file;

    /**
     * Plugin slug.
     *
     * @var string
     */
    private $slug;

    /**
     * Transient cache key for remote version.
     *
     * @var string
     */
    private $cache_key = 'nh_core_remote_version';

    /**
     * Cache duration (12 hours).
     *
     * @var int
     */
    private $cache_duration = 43200;

    /**
     * Constructor.
     *
     * @param string $plugin_file Path to main plugin file.
     */
    public function __construct( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->slug        = basename( dirname( $plugin_file ) );
        $this->github_repo = '500Byte/nh-core';

        // WordPress update hooks
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 10, 3 );
        add_filter( 'upgrader_source_selection', array( $this, 'fix_plugin_directory' ), 10, 4 );

        // Admin notice for update availability
        add_action( 'admin_notices', array( $this, 'update_notice' ) );
    }

    /**
     * Check for updates and modify the update transient.
     *
     * @param object $transient WordPress update transient.
     * @return object Modified transient.
     */
    public function check_for_update( $transient ) {
        if ( empty( $transient->response ) ) {
            $transient->response = array();
        }

        $remote_version = $this->get_remote_version();
        if ( ! $remote_version ) {
            return $transient;
        }

        $current_version = defined( 'NH_CORE_VERSION' ) ? NH_CORE_VERSION : '1.0.0';
        if ( version_compare( $remote_version, $current_version, '>' ) ) {
            $transient->response[ $this->plugin_file ] = (object) array(
                'new_version' => $remote_version,
                'url'         => "https://github.com/{$this->github_repo}",
                'package'     => $this->get_download_url( $remote_version ),
                'slug'        => $this->slug,
                'requires'    => '6.0',
                'tested'      => '6.7',
                'requires_php' => '8.1',
            );
        }

        return $transient;
    }

    /**
     * Return plugin information for the update modal.
     *
     * @param object|false $result Plugin data object.
     * @param string       $action API action.
     * @param object       $args   Plugin API arguments.
     * @return object|false Plugin information.
     */
    public function get_plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
            return $result;
        }

        $remote_version = $this->get_remote_version();
        if ( ! $remote_version ) {
            return $result;
        }

        $plugin_data = get_plugin_data( $this->plugin_file );

        return (object) array(
            'name'            => $plugin_data['Name'],
            'slug'            => $this->slug,
            'version'         => $remote_version,
            'author'          => $plugin_data['Author'],
            'author_homepage' => $plugin_data['AuthorURI'],
            'homepage'        => $plugin_data['PluginURI'],
            'download'        => $this->get_download_url( $remote_version ),
            'short_description' => $plugin_data['Description'],
            'sections'        => array(
                'description' => $plugin_data['Description'],
                'changelog'   => $this->get_changelog(),
            ),
            'requires'     => '6.0',
            'tested'       => '6.7',
            'requires_php' => '8.1',
            'donate_link'  => '',
            'banners'      => array(),
        );
    }

    /**
     * Fix the plugin directory name after extraction.
     *
     * GitHub ZIPs contain files in a subdirectory (e.g., "nh-core-1.2.0/nh-core/").
     * WordPress expects files directly in the plugin directory.
     *
     * @param string       $source        Source directory path.
     * @param string       $destination   Destination directory path.
     * @param string       $remote_source Remote source URL.
     * @param WP_Upgrader  $upgrader      WP_Upgrader instance.
     * @return string|WP_Error Source path or error.
     */
    public function fix_plugin_directory( $source, $destination, $remote_source, $upgrader ) {
        // Only fix for nh-core updates
        if ( ! isset( $upgrader->plugin_info ) || $upgrader->plugin_info->slug !== $this->slug ) {
            return $source;
        }

        // Find the actual plugin directory inside the extracted ZIP
        $directories = glob( $source . '*/' );
        if ( count( $directories ) === 1 ) {
            $plugin_dir = $directories[0];
            // Check if it contains the main plugin file
            if ( file_exists( $plugin_dir . $this->slug . '.php' ) ) {
                return $plugin_dir;
            }
        }

        return $source;
    }

    /**
     * Display admin notice when update is available.
     */
    public function update_notice() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'plugins' ) {
            return;
        }

        $remote_version = $this->get_remote_version();
        if ( ! $remote_version ) {
            return;
        }

        $current_version = defined( 'NH_CORE_VERSION' ) ? NH_CORE_VERSION : '1.0.0';
        if ( version_compare( $remote_version, $current_version, '<=' ) ) {
            return;
        }

        $update_url = admin_url( 'update-core.php' );
        printf(
            '<div class="notice notice-info"><p><strong>nh-core %s</strong> está disponible. <a href="%s">Actualizar ahora</a></p></div>',
            esc_html( $remote_version ),
            esc_url( $update_url )
        );
    }

    /**
     * Get the remote version from GitHub Releases (cached).
     *
     * @return string|false Version string or false on failure.
     */
    private function get_remote_version() {
        // Check cache first
        $cached = get_transient( $this->cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $release = $this->get_latest_release();
        if ( ! $release ) {
            return false;
        }

        $version = ltrim( $release->tag_name, 'v' );

        // Cache for 12 hours
        set_transient( $this->cache_key, $version, $this->cache_duration );

        return $version;
    }

    /**
     * Fetch the latest release from GitHub API.
     *
     * @return object|null Release object or null on failure.
     */
    private function get_latest_release() {
        $url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'nh-core-updater',
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'nh-core updater: Failed to fetch release - ' . $response->get_error_message() );
            return null;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            error_log( "nh-core updater: GitHub API returned {$code}" );
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ) );
        if ( ! $body || ! isset( $body->tag_name ) ) {
            error_log( 'nh-core updater: Invalid GitHub API response' );
            return null;
        }

        return $body;
    }

    /**
     * Get the download URL for a specific version.
     *
     * @param string $version Version string.
     * @return string Download URL.
     */
    private function get_download_url( $version ) {
        return "https://github.com/{$this->github_repo}/releases/download/v{$version}/nh-core-{$version}.zip";
    }

    /**
     * Get changelog from latest release.
     *
     * @return string Changelog HTML.
     */
    private function get_changelog() {
        $release = $this->get_latest_release();
        if ( ! $release || ! isset( $release->body ) ) {
            return '<p>No changelog available.</p>';
        }

        return wp_kses_post( nl2br( esc_html( $release->body ) ) );
    }
}
