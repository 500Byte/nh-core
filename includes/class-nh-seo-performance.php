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

    public static function init() {
        add_action( 'init', [ __CLASS__, 'guard_php_sessions' ], 1 );
        add_action( 'template_redirect', [ __CLASS__, 'set_public_cache_headers' ], 10 );
    }

    /**
     * Prevents unwanted PHP session initialization for anonymous crawlers and guests.
     */
    public static function guard_php_sessions() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }

        $has_user_session = is_user_logged_in();
        $has_cart_items   = isset( $_COOKIE['woocommerce_items_in_cart'] ) && '1' === $_COOKIE['woocommerce_items_in_cart'];
        $has_wc_session   = isset( $_COOKIE['wp_woocommerce_session_' . COOKIEHASH] );

        // If guest has no active cart or session, ensure session_start is not invoked
        if ( ! $has_user_session && ! $has_cart_items && ! $has_wc_session ) {
            if ( session_status() === PHP_SESSION_ACTIVE ) {
                session_write_close();
            }
        }
    }

    /**
     * Sets public cache-control headers on static anonymous GET requests.
     */
    public static function set_public_cache_headers() {
        if ( is_user_logged_in() || is_cart() || is_checkout() || is_account_page() ) {
            return;
        }

        if ( isset( $_COOKIE['woocommerce_items_in_cart'] ) && '1' === $_COOKIE['woocommerce_items_in_cart'] ) {
            return;
        }

        if ( 'GET' === $_SERVER['REQUEST_METHOD'] && ! headers_sent() ) {
            header( 'Cache-Control: public, max-age=3600, s-maxage=86400, stale-while-revalidate=600' );
            header_remove( 'Pragma' );
        }
    }
}
