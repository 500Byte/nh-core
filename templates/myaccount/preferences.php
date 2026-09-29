<?php
/**
 * Template: Centro de Preferencias de Comunicación y Privacidad
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$email          = '';
$is_guest_token = false;

if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $email        = $current_user->user_email;
} elseif ( ! empty( $_GET['nh_email'] ) ) {
    $raw_email      = function_exists( 'wp_unslash' ) ? wp_unslash( $_GET['nh_email'] ) : $_GET['nh_email'];
    $decoded        = rawurldecode( $raw_email );
    $email          = function_exists( 'sanitize_email' ) ? sanitize_email( $decoded ) : $decoded;
    $is_guest_token = true;
}

$prefs = class_exists( 'NH_Core_Preferences' )
    ? NH_Core_Preferences::get_instance()->get_preferences( $email )
    : [
        'cart_reminders'     => 1,
        'atelier_news'       => 1,
        'preferred_channel'  => 'both',
        'habeas_data_optout' => 0,
    ];

$nonce = function_exists( 'wp_create_nonce' ) ? wp_create_nonce( 'nh_preferences_nonce' ) : '';

// Asegurar encolado de estilos y scripts
if ( function_exists( 'wp_enqueue_style' ) ) {
    wp_enqueue_style( 'nh-preferences' );
}
if ( function_exists( 'wp_enqueue_script' ) ) {
    wp_enqueue_script( 'nh-preferences' );
}
?>
<div class="nh-preferences-wrapper">
    <div class="nh-preferences-header">
        <span class="nh-preferences-tag">Atelier Norma Hana &bull; Privacidad Consciente</span>
        <h2 class="nh-preferences-title">Tus Preferencias de Comunicación</h2>
        <p class="nh-preferences-subtitle">
            En el atelier respetamos tu tiempo y tu espacio. Tú decides qué mensajes recibir y a través de qué canales.
        </p>
        <?php if ( ! empty( $email ) ) : ?>
            <div class="nh-preferences-email-pill">
                <span>Gestionando para:</span> <strong><?php echo esc_html( $email ); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <form id="nh-preferences-form" class="nh-preferences-form" method="post" action="">
        <input type="hidden" name="email" value="<?php echo esc_attr( $email ); ?>">
        <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
        <?php if ( $is_guest_token && isset( $_GET['nh_exp'], $_GET['nh_token'] ) ) : ?>
            <input type="hidden" name="nh_exp" value="<?php echo esc_attr( (int) $_GET['nh_exp'] ); ?>">
            <input type="hidden" name="nh_token" value="<?php echo esc_attr( function_exists( 'sanitize_text_field' ) ? sanitize_text_field( wp_unslash( $_GET['nh_token'] ) ) : $_GET['nh_token'] ); ?>">
        <?php endif; ?>

        <!-- TARJETA 1: CARRITO Y TALLER -->
        <div class="nh-pref-card" id="nh-card-cart-reminders">
            <div class="nh-pref-card-info">
                <h3>Recordatorios de Carrito y Confección</h3>
                <p>Avisos sobre prendas en reserva en el atelier o avances de tu pedido antes de que se liberen las telas.</p>
            </div>
            <label class="nh-switch" for="nh-pref-cart-reminders">
                <input type="checkbox" id="nh-pref-cart-reminders" name="cart_reminders" value="1" <?php checked( (int) $prefs['cart_reminders'], 1 ); ?>>
                <span class="nh-slider"></span>
            </label>
        </div>

        <!-- TARJETA 2: NOVEDADES Y COLECCIONES -->
        <div class="nh-pref-card" id="nh-card-atelier-news">
            <div class="nh-pref-card-info">
                <h3>Colecciones y Novedades del Atelier</h3>
                <p>Noticias sobre nuevas cápsulas de lino, piezas exclusivas y eventos privados en Santa Marta.</p>
            </div>
            <label class="nh-switch" for="nh-pref-atelier-news">
                <input type="checkbox" id="nh-pref-atelier-news" name="atelier_news" value="1" <?php checked( (int) $prefs['atelier_news'], 1 ); ?>>
                <span class="nh-slider"></span>
            </label>
        </div>

        <!-- TARJETA 3: CANAL PREFERIDO -->
        <div class="nh-pref-card nh-pref-card--vertical" id="nh-card-preferred-channel">
            <div class="nh-pref-card-info">
                <h3>Canal de Contacto Preferido</h3>
                <p>Elige por cuál vía prefieres que nuestro concierge del atelier se comunique contigo.</p>
            </div>
            <div class="nh-channel-options">
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="whatsapp" <?php checked( $prefs['preferred_channel'], 'whatsapp' ); ?>>
                    <span class="nh-channel-box">
                        <span class="nh-channel-icon" aria-hidden="true">💬</span>
                        <span class="nh-channel-label">WhatsApp Concierge</span>
                    </span>
                </label>
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="email" <?php checked( $prefs['preferred_channel'], 'email' ); ?>>
                    <span class="nh-channel-box">
                        <span class="nh-channel-icon" aria-hidden="true">✉️</span>
                        <span class="nh-channel-label">Correo Electrónico</span>
                    </span>
                </label>
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="both" <?php checked( $prefs['preferred_channel'], 'both' ); ?>>
                    <span class="nh-channel-box">
                        <span class="nh-channel-icon" aria-hidden="true">✨</span>
                        <span class="nh-channel-label">Ambos Canales</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- TARJETA 4: HABEAS DATA -->
        <div class="nh-pref-card nh-pref-card--subtle" id="nh-card-habeas-data">
            <div class="nh-pref-card-info">
                <h3>Protección de Datos &amp; Habeas Data</h3>
                <p>Ley 1581 de 2012 (Colombia). Puedes revocar en cualquier momento tu autorización para recibir mensajes comerciales.</p>
            </div>
            <label class="nh-checkbox-label" for="nh-pref-habeas-optout">
                <input type="checkbox" id="nh-pref-habeas-optout" name="habeas_data_optout" value="1" <?php checked( (int) $prefs['habeas_data_optout'], 1 ); ?>>
                <span>Deseo revocar toda autorización de contacto comercial</span>
            </label>
        </div>

        <div class="nh-preferences-actions">
            <button type="submit" class="nh-btn-save-prefs" id="nh-save-prefs-btn">
                <span>Guardar Preferencias</span>
            </button>
            <div id="nh-prefs-feedback" class="nh-prefs-feedback" role="status" aria-live="polite"></div>
        </div>
    </form>
</div>
