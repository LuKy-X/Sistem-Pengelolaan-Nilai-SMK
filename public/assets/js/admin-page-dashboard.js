document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById("mainChart");
  if (!ctx || typeof Chart === "undefined") return;
  const chart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: JURUSAN_LIST.map(j => j.kode),
      datasets: [ {
        label: "Jumlah Siswa",
        data: JURUSAN_LIST.map(j => j.siswa),
        backgroundColor: "#2196F3",
        borderRadius: 8,
        maxBarThickness: 46
      } ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: "#E3F2FD" } },
        x: { grid: { display: false } }
      }
    }
  });
  requestAnimationFrame(() => chart.resize());
});
