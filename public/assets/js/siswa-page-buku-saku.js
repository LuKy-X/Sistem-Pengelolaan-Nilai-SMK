document.addEventListener("DOMContentLoaded", () => {
  const list = document.getElementById("pelanggaranList");
  if (!list) return;
  list.innerHTML = SISWA_RIWAYAT_PELANGGARAN.map(p => `
    <div class="panel p-3.5">
      <div class="flex items-center justify-between mb-1.5">
        <span class="text-[11px] text-bluedark/50">${escapeHtml(p.tanggal)}</span>
        <span class="badge" style="background:#FEE2E2;color:#DC2626;">Poin Pelanggaran +${p.poin}</span>
      </div>
      <div class="text-sm font-bold text-bluedark">${escapeHtml(p.judul)}</div>
      <div class="text-xs text-bluedark/55 mt-0.5">${escapeHtml(p.ket)}</div>
    </div>
  `).join("");
});
