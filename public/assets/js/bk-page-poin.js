let riwayatFilterId = "Semua";

let spCounter = 4;

function renderPoinKpi() {
  const total = PELANGGARAN.length;
  const totalPoinAll = PELANGGARAN.reduce((s, p) => s + p.poin, 0);
  const perluSp = [ ...new Set(PELANGGARAN.map(p => p.siswaId)) ].filter(id => totalPoin(id) >= 40).length;
  const items = [ {
    label: "Total Pelanggaran Tercatat",
    value: total,
    color: "bg-bluelight text-bluedark"
  }, {
    label: "Total Poin Terkumpul",
    value: totalPoinAll,
    color: "bg-blueprim/10 text-blueprim"
  }, {
    label: "Siswa Perlu Surat Peringatan",
    value: perluSp,
    color: "bg-red-100 text-red-600"
  } ];
  document.getElementById("poinKpi").innerHTML = items.map(k => `\n    <div class="kpi-card">\n      <div class="kpi-icon ${k.color}">\n        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>\n      </div>\n      <div>\n        <div class="font-heading text-xl font-bold text-bluedark leading-none">${k.value}</div>\n        <div class="text-[11px] text-bluedark/55 mt-1">${k.label}</div>\n      </div>\n    </div>\n  `).join("");
}

function renderRingkasan() {
  const ids = [ ...new Set(PELANGGARAN.map(p => p.siswaId)) ].map(id => ({
    id: id,
    total: totalPoin(id)
  })).sort((a, b) => b.total - a.total);
  document.getElementById("ringkasanPoin").innerHTML = ids.map(row => {
    const s = siswaById(row.id);
    return `<div class="flex items-center justify-between gap-2 rounded-xl border border-bluelight px-3 py-2.5">\n      <div class="min-w-0">\n        <div class="text-sm font-semibold text-bluedark truncate">${escapeHtml(s.nama)}</div>\n        <div class="text-[11px] text-bluedark/50">${escapeHtml(s.kelas)}</div>\n      </div>\n      <div class="flex items-center gap-2 flex-shrink-0">\n        <span class="badge ${poinBadgeClass(row.total)}">${row.total} &middot; ${poinLabel(row.total)}</span>\n        <button class="btn btn-outline btn-sm" onclick="openSP(${row.id})">Buat SP</button>\n      </div>\n    </div>`;
  }).join("");
}

function renderRiwayatFilterOptions() {
  const sel = document.getElementById("riwayatFilter");
  const ids = [ ...new Set(PELANGGARAN.map(p => p.siswaId)) ];
  sel.innerHTML = `<option value="Semua">Semua Siswa</option>` + ids.map(id => `<option value="${id}">${escapeHtml(siswaById(id).nama)}</option>`).join("");
  sel.value = riwayatFilterId;
}

function renderRiwayatTable() {
  let rows = [ ...PELANGGARAN ].sort((a, b) => b.tanggal.localeCompare(a.tanggal));
  if (riwayatFilterId !== "Semua") rows = rows.filter(p => String(p.siswaId) === String(riwayatFilterId));
  const tbody = document.getElementById("riwayatTableBody");
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-bluedark/40 py-8">Belum ada riwayat pelanggaran.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(p => {
    const s = siswaById(p.siswaId);
    return `<tr>\n      <td>${p.tanggal}</td>\n      <td class="font-medium">${escapeHtml(s.nama)}</td>\n      <td>${escapeHtml(s.kelas)}</td>\n      <td>${escapeHtml(p.jenis)}</td>\n      <td><span class="badge badge-gray">+${p.poin}</span></td>\n      <td class="max-w-[200px] truncate" title="${escapeHtml(p.catatan)}">${escapeHtml(p.catatan)}</td>\n    </tr>`;
  }).join("");
}

function refreshAll() {
  renderPoinKpi();
  renderRingkasan();
  renderRiwayatFilterOptions();
  renderRiwayatTable();
}

function openSP(siswaId) {
  const s = siswaById(siswaId);
  const records = PELANGGARAN.filter(p => p.siswaId === siswaId).sort((a, b) => a.tanggal.localeCompare(b.tanggal));
  const total = totalPoin(siswaId);
  spCounter += 1;
  const nomor = `${String(spCounter).padStart(3, "0")}/SP-BK/IX/2026`;
  const today = new Date;
  const tglIndo = today.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric"
  });
  document.getElementById("spModalBody").innerHTML = `\n    <div class="flex items-center justify-between mb-4">\n      <h3 class="font-heading font-bold text-bluedark">Surat Peringatan — Wali Murid</h3>\n      <button onclick="closeModal('spModal')" class="text-bluedark/40 hover:text-bluedark">\n        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>\n      </button>\n    </div>\n\n    <div id="spPrintArea" class="border border-bluelight rounded-xl p-5 text-sm text-bluedark space-y-3">\n      <div class="flex items-center gap-3 border-b border-bluelight pb-3">\n        <img src="../assets/images/logo/logo.png" class="w-10 h-10 object-contain" alt="Logo">\n        <div>\n          <div class="font-heading font-bold text-bluedark leading-tight">SMK Negeri 2 Karanganyar</div>\n          <div class="text-[11px] text-bluedark/50">Bimbingan Konseling &mdash; Surat Peringatan Kedisiplinan</div>\n        </div>\n      </div>\n\n      <div class="flex justify-between text-xs text-bluedark/60">\n        <span>No: ${nomor}</span>\n        <span>Karanganyar, ${tglIndo}</span>\n      </div>\n\n      <p>Kepada Yth. Bapak/Ibu Wali Murid dari:</p>\n      <div class="bg-bluelight/50 rounded-xl p-3">\n        <div class="font-semibold">${escapeHtml(s.nama)}</div>\n        <div class="text-xs text-bluedark/60">Kelas ${escapeHtml(s.kelas)}</div>\n      </div>\n\n      <p>Berdasarkan catatan Bimbingan Konseling, ananda telah mengumpulkan total <strong>${total} poin pelanggaran</strong> (kategori: ${poinLabel(total)}), dengan rincian sebagai berikut:</p>\n\n      <table class="w-full text-xs border-collapse">\n        <thead><tr class="text-left text-bluedark/50"><th class="pb-1">Tanggal</th><th class="pb-1">Pelanggaran</th><th class="pb-1 text-right">Poin</th></tr></thead>\n        <tbody>\n          ${records.map(r => `<tr class="border-t border-bluelight/70"><td class="py-1">${r.tanggal}</td><td class="py-1">${escapeHtml(r.jenis)}</td><td class="py-1 text-right">+${r.poin}</td></tr>`).join("")}\n        </tbody>\n      </table>\n\n      <p>Kami mohon kerja sama Bapak/Ibu untuk membimbing ananda agar tidak mengulangi pelanggaran serupa. Terima kasih atas perhatian dan kerja samanya.</p>\n\n      <div class="flex justify-end pt-4">\n        <div class="text-center text-xs">\n          <div>Guru BK,</div>\n          <div class="h-10"></div>\n          <div class="font-semibold">Dra. Siti Rahma, M.Pd.</div>\n        </div>\n      </div>\n    </div>\n\n    <div class="flex gap-2 mt-4">\n      <button class="btn btn-primary flex-1" onclick="window.print()">Cetak Surat</button>\n      <button class="btn btn-outline flex-1" onclick="closeModal('spModal')">Tutup</button>\n    </div>\n  `;
  openModal("spModal");
}

document.addEventListener("DOMContentLoaded", () => {
  const fSiswa = document.getElementById("fSiswa");
  const fJenis = document.getElementById("fJenis");
  const fPoin = document.getElementById("fPoin");
  const fTanggal = document.getElementById("fTanggal");
  fSiswa.innerHTML = SISWA.map(s => `<option value="${s.id}">${escapeHtml(s.nama)} &mdash; ${escapeHtml(s.kelas)}</option>`).join("");
  fJenis.innerHTML = JENIS_PELANGGARAN.map((j, i) => `<option value="${i}">${escapeHtml(j.label)} (+${j.poin})</option>`).join("");
  fPoin.value = JENIS_PELANGGARAN[0].poin;
  fTanggal.value = (new Date).toISOString().slice(0, 10);
  fJenis.addEventListener("change", () => {
    fPoin.value = JENIS_PELANGGARAN[fJenis.value].poin;
  });
  document.getElementById("pelanggaranForm").addEventListener("submit", e => {
    e.preventDefault();
    const jenis = JENIS_PELANGGARAN[fJenis.value];
    const newId = Math.max(...PELANGGARAN.map(p => p.id)) + 1;
    PELANGGARAN.push({
      id: newId,
      siswaId: Number(fSiswa.value),
      tanggal: fTanggal.value,
      jenis: jenis.label,
      poin: jenis.poin,
      catatan: document.getElementById("fCatatan").value.trim() || "-"
    });
    e.target.reset();
    fPoin.value = JENIS_PELANGGARAN[0].poin;
    fTanggal.value = (new Date).toISOString().slice(0, 10);
    refreshAll();
    const ok = document.getElementById("formSuccess");
    ok.classList.remove("hidden");
    setTimeout(() => ok.classList.add("hidden"), 2e3);
  });
  document.getElementById("riwayatFilter").addEventListener("change", e => {
    riwayatFilterId = e.target.value;
    renderRiwayatTable();
  });
  refreshAll();
});
