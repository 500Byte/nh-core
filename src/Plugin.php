<?php
namespace NormaHana\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Plugin {

    private static ?self $instance = null;
    private array $services = [];

    public static function get_instance(): self {
        return self::$instance ??= new self();
    }

    public function boot(): void {
        $this->define_constants();
        $this->register_services();
    }

    private function define_constants(): void {
        if ( ! defined( 'NH_CORE_VERSION' ) ) {
            define( 'NH_CORE_VERSION', '2.0.0' );
        }
        if ( ! defined( 'NH_CORE_PATH' ) ) {
            define( 'NH_CORE_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
        }
        if ( ! defined( 'NH_CORE_URL' ) ) {
            define( 'NH_CORE_URL', plugin_dir_url( dirname( __FILE__ ) ) );
        }
    }

    private function register_services(): void {
        $providers = [
            Modules\Preferences\PreferencesServiceProvider::class,
        ];

        foreach ( $providers as $provider_class ) {
            if ( class_exists( $provider_class ) ) {
                $provider = new $provider_class();
                if ( method_exists( $provider, 'register' ) ) {
                    $provider->register();
                    $this->services[ $provider_class ] = $provider;
                }
            }
        }
    }

    public function get_service( string $class_name ) {
        return $this->services[ $class_name ] ?? null;
    }
}
