document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#tugasTabs button");
  const panels = document.querySelectorAll('.step-panel[data-panel]');

  function goToStep(step) {
    tabs.forEach(t => t.classList.toggle("active", t.dataset.step === String(step)));
    panels.forEach(p => p.classList.toggle("active", p.dataset.panel === String(step)));
  }

  tabs.forEach(t => {
    t.addEventListener("click", () => {
      if (t.disabled) return;
      goToStep(t.dataset.step);
    });
  });

  document.getElementById("tugasKelasGrid").querySelectorAll(".kelas-card").forEach(card => {
    card.addEventListener("click", () => {
      const kode = card.dataset.kode;
      document.getElementById("tugasBreadcrumb2").textContent = kode;
      document.getElementById("tugasKelasTitle").textContent = "Kelas " + kode;
      document.getElementById("tugasKelasSub").textContent = card.dataset.jurusan + " \u00b7 " + card.dataset.siswa + " Siswa";
      document.getElementById("tugasKelasName3").textContent = kode;
      tabs[1].disabled = false;
      goToStep(2);
    });
  });

  document.querySelectorAll("[data-buku]").forEach(row => {
    row.addEventListener("click", () => {
      const kelas = document.getElementById("tugasBreadcrumb2").textContent;
      document.getElementById("tugasBreadcrumb3").textContent = kelas + " - " + row.dataset.buku;
      renderNilaiTable();
      tabs[2].disabled = false;
      goToStep(3);
    });
  });

  function renderNilaiTable() {
    const body = document.getElementById("tugasNilaiTableBody");
    body.innerHTML = GURU_SISWA.map((nama, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${escapeHtml(nama)}</td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
        <td><input type="number" class="f-input py-1"></td>
      </tr>
    `).join("");
  }
});
