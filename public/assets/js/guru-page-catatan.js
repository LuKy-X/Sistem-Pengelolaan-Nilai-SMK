document.addEventListener("DOMContentLoaded", () => {
  const panel1 = document.querySelector('.step-panel[data-panel="1"]');
  const panel2 = document.querySelector('.step-panel[data-panel="2"]');

  function renderTable() {
    const body = document.getElementById("catatanTableBody");
    body.innerHTML = GURU_SISWA.map((nama, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${escapeHtml(nama)}</td>
        <td><input type="text" class="f-input py-1.5" placeholder="Tulis catatan..."></td>
        <td>
          <div class="flex items-center gap-1">
            <button type="button" class="icon-btn icon-btn--edit"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg></button>
            <button type="button" class="icon-btn icon-btn--delete"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
          </div>
        </td>
      </tr>
    `).join("");
  }

  document.getElementById("catatanKelasGrid").querySelectorAll(".kelas-card").forEach(card => {
    card.addEventListener("click", () => {
      document.getElementById("catatanBreadcrumb").textContent = card.dataset.kode;
      document.getElementById("catatanKelasName").textContent = card.dataset.kode;
      renderTable();
      panel1.classList.remove("active");
      panel2.classList.add("active");
    });
  });

  document.getElementById("catatanBack").addEventListener("click", () => {
    panel2.classList.remove("active");
    panel1.classList.add("active");
  });
});
