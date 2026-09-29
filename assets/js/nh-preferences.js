/**
 * Norma Hana - Centro de Preferencias de Comunicación y Privacidad
 * Client-side Controller & Asynchronous AJAX Form Handler
 *
 * @package NH_Core
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('nh-preferences-form');
    if (!form) {
        return;
    }

    const feedback = document.getElementById('nh-prefs-feedback');
    const submitBtn = document.getElementById('nh-save-prefs-btn');
    const cartToggle = document.getElementById('nh-pref-cart-reminders');
    const newsToggle = document.getElementById('nh-pref-atelier-news');
    const habeasOptout = document.getElementById('nh-pref-habeas-optout');
    const cardCart = document.getElementById('nh-card-cart-reminders');
    const cardNews = document.getElementById('nh-card-atelier-news');

    // Sincronización visual entre Habeas Data y switches de comunicación
    if (habeasOptout && cartToggle && newsToggle) {
        function updateHabeasState() {
            if (habeasOptout.checked) {
                cartToggle.checked = false;
                newsToggle.checked = false;
                if (cardCart) {
                    cardCart.classList.add('nh-pref-row--dimmed');
                    cardCart.classList.add('nh-pref-card--dimmed');
                }
                if (cardNews) {
                    cardNews.classList.add('nh-pref-row--dimmed');
                    cardNews.classList.add('nh-pref-card--dimmed');
                }
            } else {
                if (cardCart) {
                    cardCart.classList.remove('nh-pref-row--dimmed');
                    cardCart.classList.remove('nh-pref-card--dimmed');
                }
                if (cardNews) {
                    cardNews.classList.remove('nh-pref-row--dimmed');
                    cardNews.classList.remove('nh-pref-card--dimmed');
                }
            }
        }

        habeasOptout.addEventListener('change', updateHabeasState);

        // Si el usuario reactiva algún switch de comunicación, desmarcar revocación total
        [cartToggle, newsToggle].forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                if (toggle.checked && habeasOptout.checked) {
                    habeasOptout.checked = false;
                    updateHabeasState();
                }
            });
        });

        // Inicializar estado visual si ya venía con optout activado
        if (habeasOptout.checked) {
            updateHabeasState();
        }
    }

    // Manejador de envío asíncrono
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Guardando...</span>';
        }

        if (feedback) {
            feedback.className = 'nh-prefs-feedback';
            feedback.innerText = '';
        }

        const formData = new FormData(form);
        formData.append('action', 'nh_save_preferences');

        const ajaxUrl = (typeof nh_ajax !== 'undefined' && nh_ajax.ajax_url)
            ? nh_ajax.ajax_url
            : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            return res.json();
        })
        .then(function(data) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Guardar Preferencias</span>';
            }

            if (data && data.success) {
                if (feedback) {
                    feedback.className = 'nh-prefs-feedback nh-prefs-feedback--success';
                    feedback.innerText = '✨ ' + (data.data && data.data.message ? data.data.message : 'Tus preferencias han sido actualizadas en el atelier.');
                }
            } else {
                if (feedback) {
                    feedback.className = 'nh-prefs-feedback nh-prefs-feedback--error';
                    feedback.innerText = '⚠️ ' + (data && data.data && data.data.message ? data.data.message : 'Hubo un inconveniente al actualizar las preferencias.');
                }
            }
        })
        .catch(function(err) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Guardar Preferencias</span>';
            }

            if (feedback) {
                feedback.className = 'nh-prefs-feedback nh-prefs-feedback--error';
                feedback.innerText = '⚠️ Error de conexión con el atelier. Por favor verifica tu red e inténtalo de nuevo.';
            }
        });
    });
});
