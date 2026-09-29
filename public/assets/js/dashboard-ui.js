function initSidebarToggle() {
  const btn = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("dbSidebar");
  const backdrop = document.getElementById("dbBackdrop");
  if (!btn || !sidebar || !backdrop) return;
  const open = () => {
    sidebar.classList.add("open");
    backdrop.classList.add("show");
  };
  const close = () => {
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
  };
  btn.addEventListener("click", open);
  backdrop.addEventListener("click", close);
}

function initClock() {
  const el = document.getElementById("todayLabel");
  if (!el) return;
  const hariMap = [ "Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu" ];
  const bulanMap = [ "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember" ];
  const now = new Date;
  el.textContent = `${hariMap[now.getDay()]}, ${now.getDate()} ${bulanMap[now.getMonth()]} ${now.getFullYear()}`;
}

function openModal(id) {
  const m = document.getElementById(id);
  if (!m) return;
  m.classList.add("show");
  document.body.style.overflow = "hidden";
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (!m) return;
  m.classList.remove("show");
  document.body.style.overflow = "";
}

document.addEventListener("click", e => {
  if (e.target.classList && e.target.classList.contains("modal-overlay")) {
    e.target.classList.remove("show");
    document.body.style.overflow = "";
  }
});

document.addEventListener("keydown", e => {
  if (e.key === "Escape") {
    document.querySelectorAll(".modal-overlay.show").forEach(m => m.classList.remove("show"));
    document.body.style.overflow = "";
  }
});

function initials(name) {
  return name.split(" ").filter(Boolean).slice(0, 2).map(w => w[0]).join("").toUpperCase();
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, s => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  }[s]));
}

function initProfileMenu() {
  const trigger = document.getElementById("profileTrigger");
  const dropdown = document.getElementById("profileDropdown");
  const wrap = document.getElementById("profileMenuWrap");
  if (!trigger || !dropdown || !wrap) return;
  const close = () => {
    dropdown.classList.remove("show");
    trigger.setAttribute("aria-expanded", "false");
  };
  const toggle = () => {
    const willShow = !dropdown.classList.contains("show");
    dropdown.classList.toggle("show", willShow);
    trigger.setAttribute("aria-expanded", String(willShow));
  };
  trigger.addEventListener("click", e => {
    e.stopPropagation();
    toggle();
  });
  document.addEventListener("click", e => {
    if (!wrap.contains(e.target)) close();
  });
  document.addEventListener("keydown", e => {
    if (e.key === "Escape") close();
  });
}

function initNavGroups() {
  document.querySelectorAll(".db-nav-group").forEach(group => {
    const trigger = group.querySelector(":scope > .db-nav-item");
    if (!trigger) return;
    trigger.addEventListener("click", e => {
      e.preventDefault();
      const willOpen = !group.classList.contains("open");
      document.querySelectorAll(".db-nav-group.open").forEach(g => { if (g !== group) g.classList.remove("open"); });
      group.classList.toggle("open", willOpen);
    });
  });
}

function initSegmented() {
  document.querySelectorAll("[data-segmented]").forEach(wrap => {
    const buttons = wrap.querySelectorAll("button[data-segment]");
    buttons.forEach(btn => {
      btn.addEventListener("click", () => {
        buttons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        const target = btn.getAttribute("data-segment");
        document.querySelectorAll("[data-segment-panel]").forEach(p => {
          p.classList.toggle("hidden", p.getAttribute("data-segment-panel") !== target);
        });
      });
    });
  });
}

// Staggered fade-in for the page's cards, panels and table rows on load.
// Deferred to a frame after DOMContentLoaded so content injected by
// page-specific scripts (KPI cards, table rows, etc.) is caught too.
function initLoadReveal() {
  if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  requestAnimationFrame(() => {
    const main = document.querySelector("main");
    if (!main) return;

    Array.from(main.children).forEach((el, i) => {
      el.classList.add("db-anim");
      el.style.setProperty("--db-delay", Math.min(i * 0.07, 0.35) + "s");
    });

    const items = main.querySelectorAll(
      ".kpi-card, .panel, .crud-card, .kelas-card, .realtime-card, table tbody tr"
    );
    items.forEach((el, i) => {
      el.classList.add("db-anim");
      el.style.setProperty("--db-delay", Math.min(i * 0.045, 0.5) + "s");
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initSidebarToggle();
  initClock();
  initProfileMenu();
  initNavGroups();
  initSegmented();
  initLoadReveal();
});

