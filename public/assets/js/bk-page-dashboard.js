document.addEventListener("DOMContentLoaded", () => {
  const siswaAktifIzin = IZIN.filter(i => i.sedangDiluar).length;
  const pengajuanMenunggu = IZIN.filter(i => i.status === "Menunggu").length;
  const pelanggaranBulanIni = PELANGGARAN.length;
  const siswaKritis = [ ...new Set(PELANGGARAN.map(p => p.siswaId)) ].filter(id => totalPoin(id) >= 75).length;
  const kpis = [ {
    label: "Siswa Izin Keluar Aktif",
    value: siswaAktifIzin,
    color: "bg-bluelight text-bluedark",
    icon: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>'
  }, {
    label: "Pengajuan Izin Menunggu",
    value: pengajuanMenunggu,
    color: "bg-amber-100 text-amber-600",
    icon: '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/>'
  }, {
    label: "Pelanggaran Bulan Ini",
    value: pelanggaranBulanIni,
    color: "bg-red-100 text-red-600",
    icon: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'
  }, {
    label: "Siswa Poin Kritis",
    value: siswaKritis,
    color: "bg-blueprim/10 text-blueprim",
    icon: '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>'
  } ];
  document.getElementById("kpiRow").innerHTML = kpis.map(k => `\n    <div class="kpi-card">\n      <div class="kpi-icon ${k.color}">\n        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${k.icon}</svg>\n      </div>\n      <div>\n        <div class="font-heading text-xl font-bold text-bluedark leading-none">${k.value}</div>\n        <div class="text-[11px] text-bluedark/55 mt-1">${k.label}</div>\n      </div>\n    </div>\n  `).join("");
  const izinTerbaru = IZIN.filter(i => i.status === "Menunggu").slice(0, 3);
  document.getElementById("izinTerbaruList").innerHTML = izinTerbaru.length ? izinTerbaru.map(i => {
    const s = siswaById(i.siswaId);
    return `<div class="hl-row hl-amber">\n      <div class="min-w-0">\n        <div class="font-semibold truncate">${escapeHtml(s.nama)}</div>\n        <div class="hl-sub truncate">${escapeHtml(s.kelas)} &middot; ${escapeHtml(i.alasan)}</div>\n      </div>\n      <span class="text-xs font-semibold flex-shrink-0 ml-2">${i.jamKeluar}</span>\n    </div>`;
  }).join("") : `<p class="text-sm text-bluedark/50">Tidak ada pengajuan yang menunggu.</p>`;
  const idsWithPoin = [ ...new Set(PELANGGARAN.map(p => p.siswaId)) ].map(id => ({
    id: id,
    total: totalPoin(id)
  })).sort((a, b) => b.total - a.total).slice(0, 3);
  document.getElementById("poinKritisList").innerHTML = idsWithPoin.map(row => {
    const s = siswaById(row.id);
    const variant = row.total >= 75 ? "hl-danger" : row.total >= 40 ? "hl-amber" : "";
    return `<div class="hl-row ${variant}">\n      <div class="min-w-0">\n        <div class="font-semibold truncate">${escapeHtml(s.nama)}</div>\n        <div class="hl-sub truncate">${escapeHtml(s.kelas)}</div>\n      </div>\n      <span class="text-xs font-bold flex-shrink-0 ml-2">${row.total} poin</span>\n    </div>`;
  }).join("");
  const ctx = document.getElementById("mainChart");
  const izinLabels = [ "Senin", "Selasa", "Rabu", "Kamis", "Jum'at" ];
  const izinData = [ 3, 5, 2, 6, 4 ];
  const jenisCount = {};
  PELANGGARAN.forEach(p => {
    jenisCount[p.jenis] = (jenisCount[p.jenis] || 0) + 1;
  });
  const pelanggaranLabels = Object.keys(jenisCount);
  const pelanggaranData = Object.values(jenisCount);
  let chart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: izinLabels,
      datasets: [ {
        label: "Pengajuan Izin",
        data: izinData,
        backgroundColor: "#90CAF9",
        borderRadius: 8,
        maxBarThickness: 42
      } ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "#F0F7FF"
          }
        },
        x: {
          grid: {
            display: false
          }
        }
      }
    }
  });
  document.getElementById("chartSelect").addEventListener("change", e => {
    if (e.target.value === "izin") {
      chart.data.labels = izinLabels;
      chart.data.datasets[0] = {
        label: "Pengajuan Izin",
        data: izinData,
        backgroundColor: "#90CAF9",
        borderRadius: 8,
        maxBarThickness: 42
      };
    } else {
      chart.data.labels = pelanggaranLabels;
      chart.data.datasets[0] = {
        label: "Jumlah Kasus",
        data: pelanggaranData,
        backgroundColor: "#EF4444",
        borderRadius: 8,
        maxBarThickness: 42
      };
    }
    chart.update();
  });
  requestAnimationFrame(() => chart.resize());
});
