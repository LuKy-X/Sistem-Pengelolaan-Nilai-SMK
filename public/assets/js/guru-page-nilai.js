document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#nilaiTabs button");
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

  const siswaSelect = document.getElementById("nilaiSiswaSelect");
  siswaSelect.innerHTML = GURU_SISWA.map(s => `<option>${escapeHtml(s)}</option>`).join("");

  document.getElementById("nilaiKelasGrid").querySelectorAll(".kelas-card").forEach(card => {
    card.addEventListener("click", () => {
      const kode = card.dataset.kode;
      document.getElementById("nilaiBreadcrumb2").textContent = kode;
      document.getElementById("nilaiKelasTitle").textContent = "Kelas " + kode;
      document.getElementById("nilaiKelasSub").textContent = card.dataset.jurusan + " \u00b7 " + card.dataset.siswa + " Siswa";
      document.getElementById("nilaiKelasName3").textContent = kode;
      tabs[1].disabled = false;
      goToStep(2);
    });
  });

  document.querySelectorAll("[data-buku]").forEach(row => {
    row.addEventListener("click", () => {
      const kelas = document.getElementById("nilaiBreadcrumb2").textContent;
      document.getElementById("nilaiBreadcrumb3").textContent = kelas + " - " + row.dataset.buku;
      renderNilaiTable();
      tabs[2].disabled = false;
      goToStep(3);
    });
  });

  function renderNilaiTable() {
    const body = document.getElementById("nilaiTableBody");
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
