document.addEventListener("DOMContentLoaded", () => {
  const chipRow = document.getElementById("mapelChipRow");
  const listWrap = document.getElementById("tugasListWrap");
  let active = SISWA_MAPEL[0];

  function renderChips() {
    chipRow.innerHTML = SISWA_MAPEL.map(m =>
      `<button type="button" class="chip${m === active ? " active" : ""}" data-mapel="${escapeHtml(m)}">${escapeHtml(m)}</button>`
    ).join("") + `<button type="button" class="chip" id="chipMore">+</button>`;

    chipRow.querySelectorAll("[data-mapel]").forEach(btn => {
      btn.addEventListener("click", () => {
        active = btn.dataset.mapel;
        renderChips();
        renderList();
      });
    });
  }

  function renderList() {
    const filtered = SISWA_TUGAS.filter(t => t.mapel === active);
    listWrap.innerHTML = filtered.length ? filtered.map((t, i) => `
      <a href="tugas-detail.html?id=${i}" class="list-tile">
        <div class="list-tile__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div class="list-tile__body">
          <div class="list-tile__title">${escapeHtml(t.judul)}</div>
          <div class="list-tile__sub">Jatuh Tempo: ${escapeHtml(t.jatuhTempo)}</div>
        </div>
        <svg class="list-tile__chev" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    `).join("") : `<div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      <span>Belum ada tugas untuk mata pelajaran ini</span>
    </div>`;
  }

  renderChips();
  renderList();
});
