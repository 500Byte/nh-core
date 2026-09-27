<?php
/**
 * Lightweight Auto-Consent Snippet for Norma Hana
 * Inyecta Consent Mode v2 en 'granted', auto-persiste nh_consent y auto-inicializa
 * pressidium_cookie_consent para evitar bloqueos en Meta Ads y navegación móvil.
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
    function gtag(){ dataLayer.push(arguments); }
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
