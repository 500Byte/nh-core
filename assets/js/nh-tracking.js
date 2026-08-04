/**
 * nh-core · NH Tracking (Odoo task #18 — dedup Meta / event_id)
 * Captura las cookies _fbp y _fbc de Meta, las publica de forma temprana en el
 * dataLayer y expone la API global window.nhTracking para sincronización.
 */
(function () {
  'use strict';

  if (window.nhTracking) { return; }

  function readCookie(name) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  }

  var fbp = readCookie('_fbp');
  var fbc = readCookie('_fbc');

  window.nhTracking = {
    fbp: fbp,
    fbc: fbc,
    getEventId: function (prefix, value) {
      return prefix + '_' + (value ? String(value) : Date.now());
    }
  };

  // Push temprano de fbp/fbc al dataLayer para que GTM los capture
  if (window.dataLayer) {
    if (fbp) { window.dataLayer.push({ nh_fbp: fbp }); }
    if (fbc) { window.dataLayer.push({ nh_fbc: fbc }); }
  }
})();
