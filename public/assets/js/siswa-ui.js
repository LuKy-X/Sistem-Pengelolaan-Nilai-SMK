function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, m => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  }[m]));
}

document.addEventListener("DOMContentLoaded", () => {
  // Off-canvas drawer
  const drawer = document.getElementById("appDrawer");
  const backdrop = document.getElementById("drawerBackdrop");
  const openBtn = document.getElementById("drawerOpenBtn");
  const closeBtn = document.getElementById("drawerCloseBtn");

  function openDrawer() { drawer.classList.add("show"); backdrop.classList.add("show"); }
  function closeDrawer() { drawer.classList.remove("show"); backdrop.classList.remove("show"); }

  if (openBtn) openBtn.addEventListener("click", openDrawer);
  if (closeBtn) closeBtn.addEventListener("click", closeDrawer);
  if (backdrop) backdrop.addEventListener("click", closeDrawer);

  // Generic tab-pill group: <div class="tab-pill-group"><button data-tab="a">..</button></div>
  // panels: [data-tab-panel="a"]
  document.querySelectorAll(".tab-pill-group").forEach(group => {
    const buttons = group.querySelectorAll("button[data-tab]");
    buttons.forEach(btn => {
      btn.addEventListener("click", () => {
        const key = btn.dataset.tab;
        buttons.forEach(b => b.classList.toggle("active", b === btn));
        document.querySelectorAll("[data-tab-panel]").forEach(p => {
          const show = p.dataset.tabPanel === key;
          p.classList.toggle("hidden", !show);
          if (show) p.dispatchEvent(new CustomEvent("tab:shown", { bubbles: true }));
        });
      });
    });
  });

  // Dropzone -> file input preview (filename only)
  document.querySelectorAll(".dropzone[data-input]").forEach(zone => {
    const input = document.getElementById(zone.dataset.input);
    if (!input) return;
    zone.addEventListener("click", () => input.click());
    input.addEventListener("change", () => {
      const label = zone.querySelector(".dropzone-label");
      if (input.files && input.files[0] && label) {
        label.textContent = input.files[0].name;
      }
    });
  });

  // Capture button (selfie + lokasi verification) — simulated
  document.querySelectorAll("[data-capture-btn]").forEach(btn => {
    btn.addEventListener("click", () => {
      const box = document.querySelector(btn.dataset.captureBtn);
      if (!box) return;
      box.classList.add("is-captured");
      const badge = box.querySelector(".capture-badge");
      if (badge) badge.innerHTML = '<span class="live-dot" style="background:#22C55E"></span> Foto Diambil';
      btn.innerHTML = btn.innerHTML.replace("Ambil Foto Selfie", "Ambil Ulang Foto");
      const simpanBtn = document.querySelector(`[data-simpan-btn="${btn.dataset.captureBtn}"]`);
      if (simpanBtn) simpanBtn.disabled = false;
    });
  });

  // Realtime clock display (verification blocks)
  const liveTimeEls = document.querySelectorAll("[data-livetime]");
  if (liveTimeEls.length) {
    function renderLiveTime() {
      const now = new Date();
      const t = now.toLocaleTimeString("id-ID", { hour12: false });
      liveTimeEls.forEach(el => { el.textContent = t; });
    }
    renderLiveTime();
    setInterval(renderLiveTime, 1000);
  }

  // Countdown timer (izin keluar) - purely visual demo, counts down from data-seconds
  document.querySelectorAll("[data-countdown]").forEach(el => {
    let remaining = parseInt(el.dataset.countdown, 10) || 0;
    function render() {
      const m = Math.floor(remaining / 60).toString().padStart(2, "0");
      const s = (remaining % 60).toString().padStart(2, "0");
      el.textContent = `00:${m}:${s}`;
    }
    render();
    setInterval(() => {
      if (remaining > 0) { remaining--; render(); }
    }, 1000);
  });

  // Staggered fade-in for the page's cards and list tiles on load.
  // Deferred to a frame after DOMContentLoaded so content injected by
  // page-specific scripts (riwayat lists, tugas terbaru, etc.) is caught too.
  if (!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches)) {
    requestAnimationFrame(() => {
      const main = document.querySelector("main");
      if (!main) return;

      Array.from(main.children).forEach((el, i) => {
        el.classList.add("db-anim");
        el.style.setProperty("--db-delay", Math.min(i * 0.07, 0.35) + "s");
      });

      const items = main.querySelectorAll(
        ".status-card, .izin-timer-card, .stat-mini, .list-tile"
      );
      items.forEach((el, i) => {
        el.classList.add("db-anim");
        el.style.setProperty("--db-delay", Math.min(i * 0.045, 0.5) + "s");
      });
    });
  }
});
