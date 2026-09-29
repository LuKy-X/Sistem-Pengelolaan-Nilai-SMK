document.addEventListener("DOMContentLoaded", () => {
  const panel1 = document.querySelector('.step-panel[data-panel="1"]');
  const panel2 = document.querySelector('.step-panel[data-panel="2"]');

  function renderJournal() {
    const body = document.getElementById("journalTableBody");
    body.innerHTML = `
      <tr>
        <td>Senin<br><span class="text-[11px] text-bluedark/50">17/08/2026</span></td>
        <td><input type="text" class="f-input py-1" style="width:5rem"></td>
        <td><input type="text" class="f-input py-1" placeholder="Matematika"></td>
        <td><input type="text" class="f-input py-1" placeholder="Nama Guru"></td>
        <td><input type="number" class="f-input py-1" style="width:4rem"></td>
        <td><input type="number" class="f-input py-1" style="width:3.5rem"></td>
        <td><input type="number" class="f-input py-1" style="width:3.5rem"></td>
        <td><input type="number" class="f-input py-1" style="width:3.5rem"></td>
        <td><input type="text" class="f-input py-1"></td>
      </tr>
    `;
  }

  function openKelas(card) {
    const kode = card.dataset.kode;
    document.getElementById("absensiBreadcrumb").textContent = kode;
    document.getElementById("absensiKelasName").textContent = kode;
    renderJournal();
    panel1.classList.remove("active");
    panel2.classList.add("active");
  }

  document.getElementById("absensiKelasGrid").querySelectorAll(".kelas-card").forEach(card => {
    card.addEventListener("click", () => openKelas(card));
  });

  document.getElementById("absensiBack").addEventListener("click", () => {
    panel2.classList.remove("active");
    panel1.classList.add("active");
  });

  document.getElementById("tambahSiswaBtn").addEventListener("click", () => {
    const wrap = document.getElementById("keteranganRows");
    const row = document.createElement("div");
    row.className = "form-row cols-2";
    row.style.marginBottom = "0";
    row.innerHTML = `
      <select class="f-select"><option>Bukti Signatur</option><option>Surat Dokter</option><option>Surat Wali</option></select>
      <select class="f-select"><option>Izin</option><option>Sakit</option><option>Alpha</option></select>
    `;
    wrap.appendChild(row);
  });
});
