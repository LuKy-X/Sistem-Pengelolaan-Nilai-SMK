(function () {
  var DEFAULT_CENTER = [-7.5666, 110.9317];
  var maps = {};

  var markerIcon = (typeof L !== "undefined") ? L.icon({
    iconUrl: "../assets/css/vendor/images/marker-icon.png",
    iconRetinaUrl: "../assets/css/vendor/images/marker-icon-2x.png",
    shadowUrl: "../assets/css/vendor/images/marker-shadow.png",
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  }) : null;

  function setBadge(id, state, text) {
    var badge = document.querySelector('[data-map-badge="' + id + '"]');
    var status = document.querySelector('[data-map-status="' + id + '"]');
    if (!badge || !status) return;
    badge.classList.remove("is-loading", "is-error");
    if (state === "loading" || state === "error") badge.classList.add("is-" + state);
    status.textContent = text;
  }

  function showCoords(id, lat, lng, accuracy) {
    var el = document.querySelector('[data-map-coords="' + id + '"]');
    if (!el) return;
    el.hidden = false;
    el.textContent = lat.toFixed(5) + ", " + lng.toFixed(5) + (accuracy ? " (\u00B1" + Math.round(accuracy) + "m)" : "");
  }

  function showRetry(id, show) {
    var btn = document.querySelector('[data-map-retry="' + id + '"]');
    if (!btn) return;
    btn.hidden = !show;
  }

  function requestLocation(id) {
    var entry = maps[id];
    if (!entry) return;

    showRetry(id, false);
    setBadge(id, "loading", "Meminta izin lokasi\u2026");

    if (!("geolocation" in navigator)) {
      setBadge(id, "error", "Perangkat tidak mendukung lokasi");
      showRetry(id, true);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        var acc = pos.coords.accuracy;

        entry.map.setView([lat, lng], 17);
        if (entry.marker) entry.map.removeLayer(entry.marker);
        if (entry.circle) entry.map.removeLayer(entry.circle);

        entry.marker = markerIcon
          ? L.marker([lat, lng], { icon: markerIcon }).addTo(entry.map)
          : L.marker([lat, lng]).addTo(entry.map);
        entry.circle = L.circle([lat, lng], {
          radius: acc,
          color: "#2196F3",
          fillColor: "#2196F3",
          fillOpacity: .15,
          weight: 1.5
        }).addTo(entry.map);

        setBadge(id, "ok", "Lokasi Terverifikasi");
        showCoords(id, lat, lng, acc);
        showRetry(id, false);
      },
      function (err) {
        var msg = "Gagal mengambil lokasi";
        if (err.code === err.PERMISSION_DENIED) msg = "Izin lokasi ditolak";
        else if (err.code === err.POSITION_UNAVAILABLE) msg = "Lokasi tidak tersedia";
        else if (err.code === err.TIMEOUT) msg = "Waktu permintaan lokasi habis";
        setBadge(id, "error", msg);
        showRetry(id, true);
      },
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 30000 }
    );
  }

  function initMap(container) {
    var id = container.id;
    if (!id || maps[id] || typeof L === "undefined") return;

    var map = L.map(container, { zoomControl: true, attributionControl: true }).setView(DEFAULT_CENTER, 15);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    maps[id] = { map: map, marker: null, circle: null };
    requestLocation(id);
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-location-map]").forEach(function (container) {
      if (container.offsetParent !== null) initMap(container);
    });
  });

  document.addEventListener("tab:shown", function (e) {
    var panel = e.target;
    if (!panel.querySelectorAll) return;
    panel.querySelectorAll("[data-location-map]").forEach(function (container) {
      if (!maps[container.id]) {
        initMap(container);
      } else {
        window.setTimeout(function () { maps[container.id].map.invalidateSize(); }, 50);
      }
    });
  });

  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-map-retry]");
    if (!btn) return;
    requestLocation(btn.dataset.mapRetry);
  });
})();
