<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'NH_Core_Icons' ) ) {

    class NH_Core_Icons {

        private static $instance = null;

        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'register_phosphor_tabs' ] );
        }

        public function register_phosphor_tabs( $tabs = [] ) {

            $weights = [
                'thin'    => 'Phosphor Thin',
                'light'   => 'Phosphor Light',
                'regular' => 'Phosphor Regular',
                'bold'    => 'Phosphor Bold',
                'fill'    => 'Phosphor Fill',
                'duotone' => 'Phosphor Duotone',
            ];

            foreach ( $weights as $weight => $label ) {
                $is_regular = ( 'regular' === $weight );
                $css_file   = $is_regular ? 'phosphor.css' : "phosphor-$weight.css";

                $tabs[ "phosphor-$weight" ] = [
                    'name'          => "phosphor-$weight",
                    'label'         => esc_html( $label, 'nh-core' ),
                    'labelIcon'     => $is_regular ? 'ph ph-acorn' : "ph-$weight ph-acorn",
                    'prefix'        => $is_regular ? 'ph ' : "ph-$weight ph-",
                    'url'           => NH_CORE_URL . "assets/phosphor/$css_file",
                    'fetchJson'     => NH_CORE_URL . "assets/phosphor/$weight/fonts/$weight.json",
                    'ver'           => '2.1.1',
                ];
            }

            return $tabs;
        }
    }

}
