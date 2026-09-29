document.addEventListener("DOMContentLoaded", () => {
  const listPanel = document.querySelector('.step-panel[data-panel="list"]');
  const detailPanel = document.querySelector('.step-panel[data-panel="detail"]');
  const listEl = document.getElementById("rubrikList");

  function badgeClass(tipe) {
    return tipe === "Tugas" ? "badge-gray" : "badge-blue";
  }

  listEl.innerHTML = GURU_RUBRIK.map((r, i) => `
    <button type="button" class="flex items-center justify-between gap-3 rounded-xl border border-bluelight px-4 py-3 hover:border-blueprim transition-colors text-left w-full" data-index="${i}">
      <div class="flex items-center gap-3 min-w-0">
        <div class="crud-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg></div>
        <div class="min-w-0"><div class="font-semibold text-sm text-bluedark truncate">${escapeHtml(r.nama)}</div><div class="text-[11px] text-bluedark/50">${escapeHtml(r.tipe)}</div></div>
      </div>
      <div class="flex items-center gap-2">
        <span class="badge ${badgeClass(r.mapel)}">${escapeHtml(r.mapel)}</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
    </button>
  `).join("");

  function openDetail(index) {
    const r = GURU_RUBRIK[index];
    document.getElementById("rubrikDetailName").textContent = r.nama;
    document.getElementById("rubrikDetailTitle").textContent = r.nama;
    const total = r.kriteria.reduce((sum, k) => sum + k.nilai, 0);
    document.getElementById("rubrikKriteriaBody").innerHTML = r.kriteria.map(k => `
      <tr><td>${escapeHtml(k.nama)}</td><td><input type="number" class="f-input py-1.5" value="${k.nilai}"></td></tr>
    `).join("") + `
      <tr style="background:#0D47A1;color:#fff;"><td class="font-semibold">Total</td><td class="font-semibold">${total}</td></tr>
    `;
    listPanel.classList.remove("active");
    detailPanel.classList.add("active");
  }

  listEl.querySelectorAll("button[data-index]").forEach(btn => {
    btn.addEventListener("click", () => openDetail(Number(btn.dataset.index)));
  });

  document.getElementById("rubrikBack").addEventListener("click", () => {
    detailPanel.classList.remove("active");
    listPanel.classList.add("active");
  });
});
