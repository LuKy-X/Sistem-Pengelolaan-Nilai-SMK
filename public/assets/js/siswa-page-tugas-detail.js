document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const id = parseInt(params.get("id"), 10);
  const tugas = SISWA_TUGAS[id] || SISWA_TUGAS[0];
  if (!tugas) return;
  document.getElementById("detailJudul").textContent = tugas.judul;
  document.getElementById("detailMapel").textContent = tugas.mapel;
  document.title = tugas.judul + " — Siswa — SMK Negeri 2 Karanganyar";
});
