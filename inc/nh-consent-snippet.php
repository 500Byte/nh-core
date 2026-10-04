<?php
/**
 * Lightweight Auto-Consent Snippet for Norma Hana
 * Inyecta Consent Mode v2 en 'granted', auto-persiste nh_consent y auto-inicializa
 * pressidium_cookie_consent para evitar bloqueos en Meta Ads y navegación móvil.
 * Desactiva explícitamente el Consent Mode de Pressidium para prevenir sobreescrituras en 'denied'.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Desactivar opción GCM de Pressidium programáticamente si está activa
add_action( 'init', function() {
    $opts = get_option( 'pressidium_cookie_consent_settings' );
    if ( is_array( $opts ) && ! empty( $opts['pressidium_options']['gcm']['enabled'] ) ) {
        $opts['pressidium_options']['gcm']['enabled'] = false;
        update_option( 'pressidium_cookie_consent_settings', $opts );
    }

    // Desenganchar el listener early_enqueue_scripts de Pressidium que corre a prioridad -10
    remove_all_actions( 'wp_enqueue_scripts', -10 );
}, 5 );

// Dequeue y deregister del script consent-mode.js de Pressidium en wp_enqueue_scripts (prioridad 9999)
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_script( 'consent-mode-script' );
    wp_deregister_script( 'consent-mode-script' );
    wp_dequeue_script( 'consent-mode-script-js' );
    wp_deregister_script( 'consent-mode-script-js' );
}, 9999 );

add_action( 'wp_print_scripts', function() {
    wp_dequeue_script( 'consent-mode-script' );
    wp_deregister_script( 'consent-mode-script' );
    wp_dequeue_script( 'consent-mode-script-js' );
    wp_deregister_script( 'consent-mode-script-js' );
}, 9999 );

// Excluir consent-mode de la minificación y diferimiento de WP Rocket
add_filter( 'rocket_exclude_js', function( $excluded ) {
    $excluded[] = 'consent-mode.js';
    $excluded[] = 'consent-mode-script';
    return $excluded;
} );

// Bloquear cualquier intento de renderizado de consent-mode.js a nivel de tag HTML
add_filter( 'script_loader_src', function( $src, $handle ) {
    if ( strpos( (string) $handle, 'consent-mode' ) !== false || strpos( (string) $src, 'consent-mode.js' ) !== false ) {
        return false;
    }
    return $src;
}, 9999, 2 );

add_action( 'wp_head', 'nh_inject_auto_consent_mode', -10002 );
function nh_inject_auto_consent_mode() {
    ?>
    <!-- NH Auto-Consent Mode (Global Granted) -->
    <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayerPYS = window.dataLayerPYS || [];
    function gtag(){ 
        window.dataLayer.push(arguments); 
        window.dataLayerPYS.push(arguments); 
    }
    gtag('consent', 'default', {
        'ad_storage': 'granted',
        'ad_user_data': 'granted',
        'ad_personalization': 'granted',
        'analytics_storage': 'granted',
        'functionality_storage': 'granted',
        'personalization_storage': 'granted',
        'security_storage': 'granted'
    });
    gtag('set', 'url_passthrough', true);
    gtag('set', 'ads_data_redaction', false);

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    }

    // Persistir aceptación en cookie propia nh_consent (30 días)
    function persistConsentGranted() {
        try {
            var date = new Date();
            date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
            document.cookie = 'nh_consent=granted; expires=' + date.toUTCString() + '; path=/; secure; samesite=strict';
        } catch (e) {}
    }

    persistConsentGranted();

    // Auto-inicializar cookie de Pressidium para aceptar en segundo plano y ocultar el banner
    try {
        var ccVal = getCookie('pressidium_cookie_consent');
        if (!ccVal) {
            var initialCC = {
                categories: ['necessary', 'analytics', 'targeting', 'preferences'],
                level: ['necessary', 'analytics', 'targeting', 'preferences'],
                revision: 4,
                data: null,
                rfc_cookie: false
            };
            var d = new Date();
            d.setTime(d.getTime() + (182 * 24 * 60 * 60 * 1000));
            document.cookie = 'pressidium_cookie_consent=' + encodeURIComponent(JSON.stringify(initialCC)) +
                '; expires=' + d.toUTCString() + '; path=/; secure; samesite=lax';
        } else {
            var decodedCC = JSON.parse(decodeURIComponent(ccVal));
            if (decodedCC && (!decodedCC.categories || decodedCC.categories.indexOf('targeting') === -1)) {
                decodedCC.categories = ['necessary', 'analytics', 'targeting', 'preferences'];
                var d2 = new Date();
                d2.setTime(d2.getTime() + (182 * 24 * 60 * 60 * 1000));
                document.cookie = 'pressidium_cookie_consent=' + encodeURIComponent(JSON.stringify(decodedCC)) +
                    '; expires=' + d2.toUTCString() + '; path=/; secure; samesite=lax';
            }
        }
    } catch(e) {}

    // Resiliencia ante eventos del banner
    function applyPressidiumGranted() {
        gtag('consent', 'update', {
            'ad_storage': 'granted',
            'ad_user_data': 'granted',
            'ad_personalization': 'granted',
            'analytics_storage': 'granted',
            'personalization_storage': 'granted',
            'functionality_storage': 'granted',
            'security_storage': 'granted'
        });
        persistConsentGranted();
    }
    window.addEventListener('pressidium-cookie-consent-accepted', applyPressidiumGranted);
    window.addEventListener('pressidium-cookie-consent-changed', applyPressidiumGranted);
    </script>
    <!-- End NH Auto-Consent Mode -->
    <?php
}
