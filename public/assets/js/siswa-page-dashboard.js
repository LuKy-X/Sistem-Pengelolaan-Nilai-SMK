document.addEventListener("DOMContentLoaded", () => {
  const list = document.getElementById("tugasTerbaruList");
  if (!list) return;
  list.innerHTML = SISWA_TUGAS.map((t, i) => `
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
  `).join("");
});
