let currentFilter = "Semua";

function statusBadge(status) {
  const map = {
    Menunggu: "badge-yellow",
    Disetujui: "badge-green",
    Ditolak: "badge-red"
  };
  return `<span class="badge ${map[status] || "badge-gray"}">${status}</span>`;
}

function countdownInfo(item) {
  if (!item.sedangDiluar) return null;
  const elapsedMs = Date.now() - item.mulaiTs;
  const remainMs = item.rencanaMenit * 6e4 - elapsedMs;
  const abs = Math.abs(remainMs);
  const mm = String(Math.floor(abs / 6e4)).padStart(2, "0");
  const ss = String(Math.floor(abs % 6e4 / 1e3)).padStart(2, "0");
  if (remainMs < 0) return {
    cls: "over",
    text: `Telat ${mm}:${ss}`
  };
  if (remainMs <= 5 * 6e4) return {
    cls: "soon",
    text: `Sisa ${mm}:${ss}`
  };
  return {
    cls: "ok",
    text: `Sisa ${mm}:${ss}`
  };
}

function renderMonitor() {
  const list = IZIN.filter(i => i.sedangDiluar && !i.selesai);
  const grid = document.getElementById("monitorGrid");
  if (!list.length) {
    grid.innerHTML = `<p class="text-sm text-bluedark/50 col-span-full">Tidak ada siswa yang sedang izin di luar sekolah.</p>`;
    return;
  }
  grid.innerHTML = list.map(i => {
    const s = siswaById(i.siswaId);
    const c = countdownInfo(i);
    return `\n    <div class="rounded-2xl border border-bluelight p-4">\n      <div class="flex items-start gap-3">\n        <div class="avatar-circle">${initials(s.nama)}</div>\n        <div class="min-w-0 flex-1">\n          <div class="font-semibold text-sm text-bluedark truncate">${escapeHtml(s.nama)}</div>\n          <div class="text-xs text-bluedark/50">${escapeHtml(s.kelas)}</div>\n        </div>\n      </div>\n      <p class="text-xs text-bluedark/60 mt-2 line-clamp-2">${escapeHtml(i.alasan)}</p>\n      <div class="flex items-center justify-between mt-3">\n        <span class="countdown-pill ${c.cls}" data-countdown="${i.id}"><span class="dot"></span>${c.text}</span>\n        <button class="btn btn-outline btn-sm" onclick="tandaiKembali(${i.id})">Sudah Kembali</button>\n      </div>\n    </div>`;
  }).join("");
}

function updateCountdowns() {
  IZIN.filter(i => i.sedangDiluar && !i.selesai).forEach(i => {
    const el = document.querySelector(`[data-countdown="${i.id}"]`);
    if (!el) return;
    const c = countdownInfo(i);
    el.className = `countdown-pill ${c.cls}`;
    el.innerHTML = `<span class="dot"></span>${c.text}`;
  });
}

function renderTable() {
  const rows = IZIN.filter(i => currentFilter === "Semua" || i.status === currentFilter);
  const tbody = document.getElementById("izinTableBody");
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-bluedark/40 py-8">Tidak ada data untuk filter ini.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(i => {
    const s = siswaById(i.siswaId);
    const aksi = i.status === "Menunggu" ? `<div class="flex gap-1.5">\n           <button class="btn btn-success btn-sm" onclick="setujuiIzin(${i.id})">Setujui</button>\n           <button class="btn btn-danger btn-sm" onclick="openDetail(${i.id})">Tolak</button>\n         </div>` : `<button class="btn btn-outline btn-sm" onclick="openDetail(${i.id})">Detail</button>`;
    return `<tr>\n      <td class="font-medium">${escapeHtml(s.nama)}</td>\n      <td>${escapeHtml(s.kelas)}</td>\n      <td class="max-w-[220px] truncate" title="${escapeHtml(i.alasan)}">${escapeHtml(i.alasan)}</td>\n      <td>${i.jamKeluar}</td>\n      <td>${statusBadge(i.status)}</td>\n      <td>${aksi}</td>\n    </tr>`;
  }).join("");
}

function setujuiIzin(id) {
  const item = IZIN.find(x => x.id === id);
  item.status = "Disetujui";
  item.sedangDiluar = true;
  item.mulaiTs = Date.now();
  renderTable();
  renderMonitor();
  closeModal("izinModal");
}

function tolakIzin(id) {
  const catatanEl = document.getElementById("tolakCatatan");
  const item = IZIN.find(x => x.id === id);
  item.status = "Ditolak";
  item.catatan = catatanEl && catatanEl.value.trim() || "Tidak ada catatan tambahan.";
  renderTable();
  renderMonitor();
  closeModal("izinModal");
}

function tandaiKembali(id) {
  const item = IZIN.find(x => x.id === id);
  item.sedangDiluar = false;
  item.selesai = true;
  renderMonitor();
}

function openDetail(id) {
  const item = IZIN.find(x => x.id === id);
  const s = siswaById(item.siswaId);
  const c = countdownInfo(item);
  let actionsHtml = "";
  if (item.status === "Menunggu") {
    actionsHtml = `\n      <div>\n        <label class="f-label">Catatan (opsional, untuk penolakan)</label>\n        <textarea id="tolakCatatan" rows="2" class="f-textarea" placeholder="Contoh: tidak melampirkan surat orang tua"></textarea>\n      </div>\n      <div class="flex gap-2 pt-1">\n        <button class="btn btn-success flex-1" onclick="setujuiIzin(${item.id})">Setujui Izin</button>\n        <button class="btn btn-danger flex-1" onclick="tolakIzin(${item.id})">Tolak Izin</button>\n      </div>`;
  } else if (item.status === "Disetujui" && item.sedangDiluar && !item.selesai) {
    actionsHtml = `<button class="btn btn-outline w-full" onclick="tandaiKembali(${item.id}); openDetail(${item.id});">Tandai Sudah Kembali</button>`;
  } else if (item.status === "Ditolak" && item.catatan) {
    actionsHtml = `<div class="rounded-xl bg-red-50 text-red-700 text-xs p-3">Catatan penolakan: ${escapeHtml(item.catatan)}</div>`;
  }
  document.getElementById("izinModalBody").innerHTML = `\n    <div class="flex items-start justify-between mb-4">\n      <div class="flex items-center gap-3">\n        <div class="avatar-circle">${initials(s.nama)}</div>\n        <div>\n          <div class="font-heading font-bold text-bluedark">${escapeHtml(s.nama)}</div>\n          <div class="text-xs text-bluedark/50">${escapeHtml(s.kelas)}</div>\n        </div>\n      </div>\n      <button onclick="closeModal('izinModal')" class="text-bluedark/40 hover:text-bluedark">\n        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>\n      </button>\n    </div>\n\n    <div class="space-y-3 text-sm">\n      <div class="flex justify-between"><span class="text-bluedark/50">Status</span>${statusBadge(item.status)}</div>\n      <div class="flex justify-between"><span class="text-bluedark/50">Jam Pengajuan</span><span class="font-medium">${item.jamKeluar}</span></div>\n      <div class="flex justify-between"><span class="text-bluedark/50">Durasi Rencana</span><span class="font-medium">${item.rencanaMenit} menit</span></div>\n      ${item.sedangDiluar && !item.selesai ? `<div class="flex justify-between items-center"><span class="text-bluedark/50">Countdown</span><span class="countdown-pill ${c.cls}" data-countdown="${item.id}"><span class="dot"></span>${c.text}</span></div>` : ""}\n      <div>\n        <div class="text-bluedark/50 mb-1">Alasan</div>\n        <div class="bg-bluelight/50 rounded-xl p-3 text-bluedark">${escapeHtml(item.alasan)}</div>\n      </div>\n    </div>\n\n    <div class="mt-4 space-y-3">${actionsHtml}</div>\n  `;
  openModal("izinModal");
}

document.addEventListener("DOMContentLoaded", () => {
  renderMonitor();
  renderTable();
  document.getElementById("statusFilter").addEventListener("change", e => {
    currentFilter = e.target.value;
    renderTable();
  });
  setInterval(updateCountdowns, 1e3);
});
