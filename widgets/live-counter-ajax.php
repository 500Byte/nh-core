<?php
/**
 * NH Live Counter — Real-time visitor tracking via AJAX.
 *
 * Endpoint: wp-admin/admin-ajax.php?action=nh_track_visitor
 *
 * How it works:
 *   1. Frontend sends visitor_id (random, stored in sessionStorage) every 30 s.
 *   2. Server upserts the visitor's timestamp in wp_options (serialized array).
 *   3. Expired entries (>60 s old) are pruned on every request.
 *   4. Current active count is returned as JSON.
 *
 * wp_options row: nh_active_visitors  (autoload = no)
 * Structure: [ 'abc123' => 1721000000, 'def456' => 1721000030, ... ]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_nh_track_visitor', 'nh_track_visitor' );
add_action( 'wp_ajax_nopriv_nh_track_visitor', 'nh_track_visitor' );

function nh_track_visitor() {
    // Verify nonce
    if ( ! check_ajax_referer( 'nh_live_counter', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
    }

    $visitor_id = isset( $_POST['visitor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['visitor_id'] ) ) : '';
    if ( empty( $visitor_id ) ) {
        wp_send_json_error( [ 'message' => 'No visitor ID' ], 400 );
    }

    $now            = time();
    $expiry         = 60;   // seconds — after this, visitor is considered inactive
    $min_write_gap  = 20;   // don't write to DB if last write was < 20 s ago

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $visitors = get_option( 'nh_active_visitors', [] );
    if ( ! is_array( $visitors ) ) {
        $visitors = [];
    }

    // --- Fast path: if this visitor was updated recently, just return count ---
    if ( isset( $visitors[ $visitor_id ] ) && ( $now - intval( $visitors[ $visitor_id ] ) ) < $min_write_gap ) {
        $active = array_filter( $visitors, function ( $ts ) use ( $now, $expiry ) {
            return ( $now - intval( $ts ) ) < $expiry;
        });
        wp_send_json_success( [ 'count' => count( $active ) ] );
    }

    // --- Slow path: prune expired + upsert ---
    foreach ( $visitors as $id => $ts ) {
        if ( ( $now - intval( $ts ) ) >= $expiry ) {
            unset( $visitors[ $id ] );
        }
    }

    $visitors[ $visitor_id ] = $now;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    update_option( 'nh_active_visitors', $visitors, false );

    wp_send_json_success( [ 'count' => count( $visitors ) ] );
}
