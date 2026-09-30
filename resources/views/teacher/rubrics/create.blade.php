@extends('layouts.teacher')

@section('title', 'Buat Rubrik Baru — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Buat Rubrik Penilaian</h1>
    <p class="text-sm text-bluedark/60 mt-1">Definisikan Rubrik Penilaian Multi-Kriteria untuk Pembelajaran</p>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.rubrics.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Rubrik Nilai</a> / 
    <span class="font-semibold text-bluedark">Rubrik Baru</span>
  </p>

  <div class="form-block w-full">
    <div class="form-block__header">
      <h3>Buat Rubrik Penilaian Baru</h3>
      <p>Isi form berikut untuk mendefinisikan kriteria dan pembobotan skor</p>
    </div>

    <form action="{{ route('teacher.rubrics.store') }}" method="POST" class="form-block__body space-y-5">
      @csrf

      <div>
        <label class="f-label">Nama Rubrik Penilaian <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="cth. Rubrik Evaluasi Praktik Web Backend" required class="f-input">
      </div>

      <div>
        <label class="f-label">Deskripsi / Tujuan Rubrik</label>
        <textarea name="description" rows="2" placeholder="Tuliskan tujuan evaluasi atau petunjuk penskoran..." class="f-textarea">{{ old('description') }}</textarea>
      </div>

      <div class="pt-2">
        <div class="flex items-center justify-between mb-3">
          <div>
            <label class="f-label mb-0.5">Daftar Kriteria Penskoran <span class="text-red-500">*</span></label>
            <p class="text-xs text-bluedark/50">Tentukan kriteria, deskripsi capaian kompetensi, dan batas skor per kriteria</p>
          </div>
          <button type="button" onclick="addCriterionRow()" class="btn btn-outline btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tambah Kriteria</span>
          </button>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl mb-4">
          <table class="tbl w-full" id="criteriaTable">
            <thead>
              <tr>
                <th class="w-12 text-center">#</th>
                <th class="w-1/3 min-w-[220px]">Kriteria Penilaian <span class="text-red-500">*</span></th>
                <th class="min-w-[280px]">Deskripsi Indikator</th>
                <th style="width:11rem" class="text-center">Bobot Maksimal <span class="text-red-500">*</span></th>
                <th style="width:5rem" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="criteriaTableBody">
              <tr>
                <td class="text-center font-semibold text-slate-500 row-number">1</td>
                <td>
                  <input type="text" name="criteria[0][criterion]" placeholder="cth. Kualitas Arsitektur MVC" required class="f-input py-2 px-3 text-sm">
                </td>
                <td>
                  <input type="text" name="criteria[0][description]" placeholder="Pemisahan logic controller dan model" class="f-input py-2 px-3 text-sm">
                </td>
                <td>
                  <input type="number" name="criteria[0][max_points]" value="50" min="1" max="100" required class="f-input py-2 px-3 text-center font-mono text-sm max-point-input">
                </td>
                <td class="text-center">
                  <button type="button" onclick="removeCriterionRow(this)" class="icon-btn icon-btn--delete cursor-pointer" title="Hapus">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </td>
              </tr>
              <tr>
                <td class="text-center font-semibold text-slate-500 row-number">2</td>
                <td>
                  <input type="text" name="criteria[1][criterion]" placeholder="cth. Validasi & Sanitasi Data" required class="f-input py-2 px-3 text-sm">
                </td>
                <td>
                  <input type="text" name="criteria[1][description]" placeholder="Pencegahan celah keamanan input" class="f-input py-2 px-3 text-sm">
                </td>
                <td>
                  <input type="number" name="criteria[1][max_points]" value="50" min="1" max="100" required class="f-input py-2 px-3 text-center font-mono text-sm max-point-input">
                </td>
                <td class="text-center">
                  <button type="button" onclick="removeCriterionRow(this)" class="icon-btn icon-btn--delete cursor-pointer" title="Hapus">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('teacher.rubrics.index') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary px-5">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Simpan Rubrik</span>
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
let criteriaIndex = 2;

function addCriterionRow() {
  const tbody = document.getElementById('criteriaTableBody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="text-center font-semibold text-slate-500 row-number">${tbody.children.length + 1}</td>
    <td>
      <input type="text" name="criteria[${criteriaIndex}][criterion]" placeholder="Kriteria penilaian..." required class="f-input py-2 px-3 text-sm">
    </td>
    <td>
      <input type="text" name="criteria[${criteriaIndex}][description]" placeholder="Indikator capaian..." class="f-input py-2 px-3 text-sm">
    </td>
    <td>
      <input type="number" name="criteria[${criteriaIndex}][max_points]" value="25" min="1" max="100" required class="f-input py-2 px-3 text-center font-mono text-sm max-point-input">
    </td>
    <td class="text-center">
      <button type="button" onclick="removeCriterionRow(this)" class="icon-btn icon-btn--delete cursor-pointer" title="Hapus">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
  criteriaIndex++;
  updateRowNumbers();
}

function removeCriterionRow(btn) {
  const tbody = document.getElementById('criteriaTableBody');
  if (tbody.children.length > 1) {
    btn.closest('tr').remove();
    updateRowNumbers();
  } else {
    alert('Minimal harus ada 1 kriteria penilaian.');
  }
}

function updateRowNumbers() {
  document.querySelectorAll('#criteriaTableBody .row-number').forEach((td, i) => {
    td.textContent = i + 1;
  });
}
</script>
@endpush
