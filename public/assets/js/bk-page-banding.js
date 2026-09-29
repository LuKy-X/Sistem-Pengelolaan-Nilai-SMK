let bandingFilter = "Semua";

const SANKSI_OPTIONS = [ "Teguran lisan & catatan BK", "Surat pernyataan bermaterai", "Poin pelanggaran +5", "Pemanggilan orang tua/wali" ];

function bandingBadge(status) {
  const map = {
    "Menunggu Validasi": "badge-yellow",
    Diterima: "badge-green",
    Ditolak: "badge-red",
    "Sanksi Diberikan": "badge-blue"
  };
  return `<span class="badge ${map[status] || "badge-gray"}">${status}</span>`;
}

function renderBandingKpi() {
  const menunggu = BANDING.filter(b => b.status === "Menunggu Validasi").length;
  const ditolak = BANDING.filter(b => b.status === "Ditolak").length;
  const diterima = BANDING.filter(b => b.status === "Diterima").length;
  const items = [ {
    label: "Menunggu Validasi",
    value: menunggu,
    color: "bg-amber-100 text-amber-600"
  }, {
    label: "Alasan Diterima",
    value: diterima,
    color: "bg-green-100 text-green-600"
  }, {
    label: "Perlu Sanksi",
    value: ditolak,
    color: "bg-red-100 text-red-600"
  } ];
  document.getElementById("bandingKpi").innerHTML = items.map(k => `\n    <div class="kpi-card">\n      <div class="kpi-icon ${k.color}">\n        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M5 7h5l-2.5 6.5A3 3 0 0 0 10 15a3 3 0 0 0 4.9 0 3 3 0 0 0 2.5-1.5L14.9 7H19"/></svg>\n      </div>\n      <div>\n        <div class="font-heading text-xl font-bold text-bluedark leading-none">${k.value}</div>\n        <div class="text-[11px] text-bluedark/55 mt-1">${k.label}</div>\n      </div>\n    </div>\n  `).join("");
}

function renderBandingTable() {
  const rows = BANDING.filter(b => bandingFilter === "Semua" || b.status === bandingFilter);
  const tbody = document.getElementById("bandingTableBody");
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-bluedark/40 py-8">Tidak ada data untuk filter ini.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(b => {
    const s = siswaById(b.siswaId);
    return `<tr>\n      <td class="font-medium">${escapeHtml(s.nama)}</td>\n      <td>${escapeHtml(s.kelas)}</td>\n      <td>${b.tanggal}</td>\n      <td class="max-w-[220px] truncate" title="${escapeHtml(b.alasan)}">${escapeHtml(b.alasan)}</td>\n      <td>${bandingBadge(b.status)}</td>\n      <td><button class="btn btn-outline btn-sm" onclick="openBandingDetail(${b.id})">Detail</button></td>\n    </tr>`;
  }).join("");
}

function validasiBanding(id, hasil) {
  const item = BANDING.find(x => x.id === id);
  item.status = hasil;
  renderBandingTable();
  renderBandingKpi();
  openBandingDetail(id);
}

function beriSanksi(id) {
  const select = document.getElementById("sanksiSelect");
  const item = BANDING.find(x => x.id === id);
  item.sanksi = select.value;
  item.status = "Sanksi Diberikan";
  renderBandingTable();
  renderBandingKpi();
  openBandingDetail(id);
}

function openBandingDetail(id) {
  const item = BANDING.find(x => x.id === id);
  const s = siswaById(item.siswaId);
  let actionsHtml = "";
  if (item.status === "Menunggu Validasi") {
    actionsHtml = `\n      <div class="flex gap-2">\n        <button class="btn btn-success flex-1" onclick="validasiBanding(${item.id}, 'Diterima')">Terima Alasan</button>\n        <button class="btn btn-danger flex-1" onclick="validasiBanding(${item.id}, 'Ditolak')">Tolak Alasan</button>\n      </div>`;
  } else if (item.status === "Ditolak") {
    actionsHtml = `\n      <div class="rounded-xl bg-red-50 text-red-700 text-xs p-3 mb-3">Alasan ditolak. Silakan tentukan sanksi untuk siswa ini.</div>\n      <label class="f-label">Jenis Sanksi</label>\n      <select id="sanksiSelect" class="f-select mb-3">\n        ${SANKSI_OPTIONS.map(o => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join("")}\n      </select>\n      <button class="btn btn-primary w-full" onclick="beriSanksi(${item.id})">Beri Sanksi</button>\n      <button class="btn btn-outline w-full mt-2" onclick="validasiBanding(${item.id}, 'Diterima')">Validasi Ulang &rarr; Terima</button>`;
  } else if (item.status === "Sanksi Diberikan") {
    actionsHtml = `<div class="rounded-xl bg-bluelight/60 text-bluedark text-xs p-3">Sanksi diberikan: <strong>${escapeHtml(item.sanksi)}</strong></div>`;
  } else if (item.status === "Diterima") {
    actionsHtml = `<div class="rounded-xl bg-green-50 text-green-700 text-xs p-3">Alasan keterlambatan diterima. Tidak ada sanksi yang diberikan.</div>`;
  }
  document.getElementById("bandingModalBody").innerHTML = `\n    <div class="flex items-start justify-between mb-4">\n      <div class="flex items-center gap-3">\n        <div class="avatar-circle">${initials(s.nama)}</div>\n        <div>\n          <div class="font-heading font-bold text-bluedark">${escapeHtml(s.nama)}</div>\n          <div class="text-xs text-bluedark/50">${escapeHtml(s.kelas)} &middot; ${item.tanggal}</div>\n        </div>\n      </div>\n      <button onclick="closeModal('bandingModal')" class="text-bluedark/40 hover:text-bluedark">\n        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>\n      </button>\n    </div>\n\n    <div class="space-y-3 text-sm mb-4">\n      <div class="flex justify-between"><span class="text-bluedark/50">Status</span>${bandingBadge(item.status)}</div>\n      <div>\n        <div class="text-bluedark/50 mb-1">Alasan Keterlambatan</div>\n        <div class="bg-bluelight/50 rounded-xl p-3 text-bluedark">${escapeHtml(item.alasan)}</div>\n      </div>\n    </div>\n\n    <div>${actionsHtml}</div>\n  `;
  openModal("bandingModal");
}

document.addEventListener("DOMContentLoaded", () => {
  renderBandingKpi();
  renderBandingTable();
  document.getElementById("bandingFilter").addEventListener("change", e => {
    bandingFilter = e.target.value;
    renderBandingTable();
  });
});
