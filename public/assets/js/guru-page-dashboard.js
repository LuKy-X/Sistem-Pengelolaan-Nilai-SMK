document.addEventListener("DOMContentLoaded", () => {
  const totalSiswa = GURU_KELAS.reduce((sum, k) => sum + k.siswa, 0);

  const kpis = [
    {
      label: "Total Siswa Diampu",
      value: totalSiswa,
      color: "bg-bluelight text-bluedark",
      icon: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'
    },
    {
      label: "Kelas Diampu",
      value: GURU_KELAS.length,
      color: "bg-blueprim/10 text-blueprim",
      icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>'
    },
    {
      label: "Tugas Belum Dinilai",
      value: 12,
      color: "bg-amber-100 text-amber-600",
      icon: '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>'
    },
    {
      label: "Rata-rata Nilai Kelas",
      value: "82.4",
      color: "bg-red-100 text-red-600",
      icon: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/>'
    }
  ];

  document.getElementById("kpiRow").innerHTML = kpis.map(k => `
    <div class="kpi-card">
      <div class="kpi-icon ${k.color}">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${k.icon}</svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">${k.value}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">${k.label}</div>
      </div>
    </div>
  `).join("");

  const tugasBelumDinilai = [
    { judul: "Tugas Persamaan Linear", kelas: "XII RA", jumlah: 6 },
    { judul: "Ulangan Harian 2", kelas: "XII TA", jumlah: 4 },
    { judul: "Tugas Trigonometri", kelas: "XII OA", jumlah: 2 }
  ];
  document.getElementById("tugasBelumDinilaiList").innerHTML = tugasBelumDinilai.map(t => `
    <div class="hl-row hl-amber">
      <div class="min-w-0">
        <div class="font-semibold truncate">${escapeHtml(t.judul)}</div>
        <div class="hl-sub truncate">${escapeHtml(t.kelas)}</div>
      </div>
      <span class="text-xs font-semibold flex-shrink-0 ml-2">${t.jumlah} siswa</span>
    </div>
  `).join("");

  const jadwalHariIni = [
    { jam: "07.00 - 08.30", kelas: "XII RA", materi: "Persamaan Linear" },
    { jam: "09.00 - 10.30", kelas: "XII TA", materi: "Trigonometri" }
  ];
  document.getElementById("jadwalHariIniList").innerHTML = jadwalHariIni.length ? jadwalHariIni.map(j => `
    <div class="hl-row">
      <div class="min-w-0">
        <div class="font-semibold truncate">${escapeHtml(j.kelas)} &middot; ${escapeHtml(j.materi)}</div>
        <div class="hl-sub truncate">${escapeHtml(j.jam)}</div>
      </div>
    </div>
  `).join("") : `<p class="text-sm text-bluedark/50">Tidak ada jadwal hari ini.</p>`;

  const ctx = document.getElementById("mainChart");
  if (ctx && window.Chart) {
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: GURU_KELAS.map(k => k.kode),
        datasets: [{
          label: "Rata-rata Nilai",
          data: [82, 79, 85, 77],
          backgroundColor: "#90CAF9",
          borderRadius: 8,
          maxBarThickness: 42
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 100 } }
      }
    });
  }
});
