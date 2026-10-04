<?php
/**
 * Global Consent Mode v2 Snippet for Norma Hana
 * Inyecta Consent Mode v2 en estado 'granted' por defecto en el inicio de <head> (-10002),
 * garantizando atribución limpia en Meta Pixel + CAPI y GA4 (gcd=13r).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

    // Persistir aceptación en cookie propia nh_consent (30 días)
    try {
        var date = new Date();
        date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = 'nh_consent=granted; expires=' + date.toUTCString() + '; path=/; secure; samesite=strict';
    } catch (e) {}
    </script>
    <!-- End NH Auto-Consent Mode -->
    <?php
}
