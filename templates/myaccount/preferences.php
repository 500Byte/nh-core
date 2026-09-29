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
            <div class="nh-preferences-email-meta">
                <svg class="nh-pref-meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect width="20" height="16" x="2" y="4" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <span class="nh-pref-meta-label">Gestionando preferencias para:</span>
                <strong class="nh-pref-meta-value"><?php echo esc_html( $email ); ?></strong>
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

        <!-- FILA 1: RECORDATORIOS DE CARRITO Y CONFECCIÓN -->
        <div class="nh-pref-row" id="nh-card-cart-reminders">
            <div class="nh-pref-row-info">
                <h3 class="nh-pref-row-title">Recordatorios de Carrito y Confección</h3>
                <p class="nh-pref-row-desc">Avisos sobre prendas en reserva en el atelier o avances de tu pedido antes de que se liberen las telas.</p>
            </div>
            <div class="nh-pref-row-control">
                <label class="nh-switch" for="nh-pref-cart-reminders" aria-label="Recordatorios de Carrito y Confección">
                    <input type="checkbox" id="nh-pref-cart-reminders" name="cart_reminders" value="1" <?php checked( (int) $prefs['cart_reminders'], 1 ); ?>>
                    <span class="nh-slider"></span>
                </label>
            </div>
        </div>

        <!-- FILA 2: NOVEDADES Y COLECCIONES -->
        <div class="nh-pref-row" id="nh-card-atelier-news">
            <div class="nh-pref-row-info">
                <h3 class="nh-pref-row-title">Colecciones y Novedades del Atelier</h3>
                <p class="nh-pref-row-desc">Noticias sobre nuevas cápsulas de lino, piezas exclusivas y eventos privados en Santa Marta.</p>
            </div>
            <div class="nh-pref-row-control">
                <label class="nh-switch" for="nh-pref-atelier-news" aria-label="Colecciones y Novedades del Atelier">
                    <input type="checkbox" id="nh-pref-atelier-news" name="atelier_news" value="1" <?php checked( (int) $prefs['atelier_news'], 1 ); ?>>
                    <span class="nh-slider"></span>
                </label>
            </div>
        </div>

        <!-- FILA 3: CANAL PREFERIDO -->
        <div class="nh-pref-row nh-pref-row--vertical" id="nh-card-preferred-channel">
            <div class="nh-pref-row-info">
                <h3 class="nh-pref-row-title">Canal de Contacto Preferido</h3>
                <p class="nh-pref-row-desc">Elige por cuál vía prefieres que nuestro concierge del atelier se comunique contigo.</p>
            </div>
            <div class="nh-channel-options" role="radiogroup" aria-label="Canal de Contacto Preferido">
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="whatsapp" <?php checked( $prefs['preferred_channel'], 'whatsapp' ); ?>>
                    <span class="nh-channel-box">
                        <svg class="nh-channel-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                        </svg>
                        <span class="nh-channel-label">WhatsApp Concierge</span>
                    </span>
                </label>
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="email" <?php checked( $prefs['preferred_channel'], 'email' ); ?>>
                    <span class="nh-channel-box">
                        <svg class="nh-channel-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        <span class="nh-channel-label">Correo Electrónico</span>
                    </span>
                </label>
                <label class="nh-channel-option">
                    <input type="radio" name="preferred_channel" value="both" <?php checked( $prefs['preferred_channel'], 'both' ); ?>>
                    <span class="nh-channel-box">
                        <svg class="nh-channel-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3Z"/>
                        </svg>
                        <span class="nh-channel-label">Ambos Canales</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- FILA 4: HABEAS DATA & PROTECCIÓN DE DATOS -->
        <div class="nh-habeas-card" id="nh-card-habeas-data">
            <div class="nh-habeas-info">
                <h3 class="nh-habeas-title">Protección de Datos &amp; Habeas Data</h3>
                <p class="nh-habeas-desc">Conforme a la Ley 1581 de 2012 (Colombia), tus datos personales están protegidos. Puedes revocar en cualquier momento tu autorización para recibir comunicaciones comerciales del atelier.</p>
            </div>
            <label class="nh-checkbox-label" for="nh-pref-habeas-optout">
                <input type="checkbox" id="nh-pref-habeas-optout" name="habeas_data_optout" value="1" <?php checked( (int) $prefs['habeas_data_optout'], 1 ); ?>>
                <span class="nh-checkbox-text">Deseo revocar toda autorización de contacto comercial</span>
            </label>
        </div>

        <!-- ACCIONES -->
        <div class="nh-preferences-actions">
            <button type="submit" class="nh-btn-save-prefs" id="nh-save-prefs-btn">
                <span>Guardar Preferencias</span>
            </button>
            <div id="nh-prefs-feedback" class="nh-prefs-feedback" role="status" aria-live="polite"></div>
        </div>
    </form>
</div>
