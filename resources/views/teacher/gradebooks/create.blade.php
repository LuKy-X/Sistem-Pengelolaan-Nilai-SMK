@extends('layouts.teacher')

@section('title', 'Buat Buku Nilai Baru — Guru')

@section('content')
<div class="space-y-6">

  <!-- Breadcrumb -->
  <p class="text-sm text-bluedark/60">
    <a href="{{ route('teacher.gradebooks.index') }}" class="hover:underline text-blueprim font-medium">Buku Nilai</a> / 
    <span class="font-semibold text-bluedark">Buat Buku Nilai Baru</span>
  </p>

  <!-- Title -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Buat Buku Nilai Baru</h1>
      <p class="text-sm text-bluedark/60 mt-1">Konfigurasi identitas lembar nilai dan susun kolom penilaian secara interaktif</p>
    </div>
    <a href="{{ route('teacher.gradebooks.index') }}" class="btn btn-outline btn-sm">
      &larr; Kembali ke Daftar
    </a>
  </div>

  <form action="{{ route('teacher.gradebooks.store') }}" method="POST" id="formCreateGradebook">
    @csrf

    <!-- Panel 1: Identitas Buku Nilai -->
    <div class="panel p-6 mb-6">
      <div class="flex items-center gap-2 mb-4 pb-3 border-b border-bluelight">
        <div class="w-8 h-8 rounded-lg bg-bluelight flex items-center justify-center text-blueprim font-bold text-sm">1</div>
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-base">Identitas Buku Nilai</h2>
          <p class="text-xs text-bluedark/50">Tentukan kelas mengajar, nama buku nilai, dan deskripsi</p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="f-label">Pilih Rombel / Kelas Mengajar <span class="text-red-500">*</span></label>
          <select name="teaching_assignment_id" class="f-select" id="assignmentSelect" required>
            <option value="">-- Pilih Kelas &amp; Mata Pelajaran --</option>
            @foreach($assignments as $assign)
              @php
                $className = $assign->schoolClass?->name ?? 'Kelas';
                $deptName = $assign->schoolClass?->department?->name ?? '';
                $subjectName = $assign->subject?->name ?? 'Matematika';
                $semName = $assign->semester?->name ?? 'Gasal';
                $yearName = $assign->semester?->academicYear?->name ?? '2026/2027';
              @endphp
              <option value="{{ $assign->id }}" 
                      data-class="{{ $className }}" 
                      data-subject="{{ $subjectName }}"
                      data-sem="{{ $semName }}"
                      data-year="{{ $yearName }}"
                      {{ (old('teaching_assignment_id', $selectedAssignmentId) == $assign->id) ? 'selected' : '' }}>
                {{ $className }} ({{ $deptName }}) &mdash; {{ $subjectName }} [{{ $semName }} {{ $yearName }}]
              </option>
            @endforeach
          </select>
          @error('teaching_assignment_id')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Nama Buku Nilai <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="gradebookNameInput" class="f-input" 
                   value="{{ old('name', 'Buku Nilai - Gasal 2026/2027') }}" 
                   placeholder="cth. Buku Nilai - Gasal 2026/2027" required>
            @error('name')
              <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label">Status Buku Nilai</label>
            <div class="field-toggle h-[44px]">
              <span>Buku nilai aktif</span>
              <label class="toggle-switch">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="slider"></span>
              </label>
            </div>
          </div>
        </div>

        <div>
          <label class="f-label">Deskripsi / Catatan Tambahan (Opsional)</label>
          <textarea name="description" class="f-textarea" rows="2" placeholder="Tuliskan keterangan mengenai lingkup materi atau standar kompetensi buku nilai ini...">{{ old('description') }}</textarea>
        </div>
      </div>
    </div>

    <!-- Panel 2: Lembar Buku Nilai Horizontal (Spreadsheet Builder) -->
    <div class="panel p-6 mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-3 border-b border-bluelight">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-bluelight flex items-center justify-center text-blueprim font-bold text-sm">2</div>
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-base">Lembar Buku Nilai &mdash; Struktur Kolom Penilaian</h2>
            <p class="text-xs text-bluedark/50">Klik pada salah satu kolom di bawah untuk mengedit pengaturannya secara langsung</p>
          </div>
        </div>

        <!-- Preset Buttons Toolbar (Icon + Tanpa Tanda Tambah Dobel) -->
        <div class="flex flex-wrap items-center gap-2">
          <button type="button" class="btn btn-outline btn-sm text-xs cursor-pointer" id="btnAddTugasCol" title="Tambah kolom Tugas ke samping">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tugas</span>
          </button>
          <button type="button" class="btn btn-outline btn-sm text-xs cursor-pointer" id="btnAddUHCol" title="Tambah kolom Ulangan Harian ke samping">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Ulangan Harian</span>
          </button>
          <button type="button" class="btn btn-outline btn-sm text-xs cursor-pointer" id="btnAddSummaryCol" title="Tambah kolom Rata-rata / Kalkulasi ke samping">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Rata-rata / Kalkulasi</span>
          </button>
          <button type="button" class="btn btn-primary btn-sm text-xs cursor-pointer" id="btnPresetSMK" title="Gunakan 10 kolom standar kurikulum SMK">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span>Preset Standar SMK</span>
          </button>
        </div>
      </div>

      <!-- Tampilan Spreadsheet Nyata: Kolom-kolom Horizontal Berjajar ke Samping -->
      <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl shadow-2xs mb-5 bg-white">
        <table class="tbl w-full text-left" id="spreadsheetPreviewTable">
          <thead>
            <tr id="spreadsheetHeaderRow">
              <th class="w-12 text-center bg-slate-100 border-r border-slate-200">No</th>
              <th class="min-w-[170px] bg-slate-100 border-r border-slate-200">Nama Siswa</th>
              <!-- Kolom-kolom akan dirender di sini sebagai <th> horizontal yang bisa diklik -->
            </tr>
          </thead>
          <tbody id="spreadsheetBodyRows">
            <!-- Contoh baris siswa dengan nilai preview -->
          </tbody>
        </table>
      </div>

      <!-- Panel Form Tambahan: Mengedit Kolom yang Sedang Dipilih -->
      <div class="panel p-5 bg-slate-50 border-2 border-blueprim/40 rounded-xl" id="columnEditorCard">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-2 border-b border-bluelight">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blueprim animate-pulse"></span>
            <h3 class="font-heading font-bold text-bluedark text-sm">
              Pengaturan Kolom: <span id="editorColNameTitle" class="text-blueprim">Ulangan Harian 1</span>
            </h3>
          </div>

          <div class="flex items-center gap-2">
            <!-- Geser Posisi Kolom -->
            <div class="flex items-center bg-white border border-bluelight rounded-lg p-0.5 shadow-2xs">
              <button type="button" id="btnMoveColLeft" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:text-blueprim hover:bg-slate-100 rounded disabled:opacity-30 disabled:pointer-events-none flex items-center gap-1 cursor-pointer transition-colors" title="Geser kolom terpilih satu posisi ke kiri">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Geser Kiri</span>
              </button>
              <div class="w-px h-4 bg-slate-200"></div>
              <button type="button" id="btnMoveColRight" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:text-blueprim hover:bg-slate-100 rounded disabled:opacity-30 disabled:pointer-events-none flex items-center gap-1 cursor-pointer transition-colors" title="Geser kolom terpilih satu posisi ke kanan">
                <span>Geser Kanan</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>

            <!-- Hapus Kolom -->
            <button type="button" id="btnDeleteCurrentCol" class="text-xs text-red-600 hover:text-red-700 font-semibold flex items-center gap-1 px-2.5 py-1 rounded bg-red-50 hover:bg-red-100/80 cursor-pointer transition-colors">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              <span>Hapus Kolom</span>
            </button>
          </div>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-3 mb-3">
          <div>
            <label class="f-label text-xs">Nama Kolom <span class="text-red-500">*</span></label>
            <input type="text" id="editorInputName" class="f-input py-1.5 text-xs font-semibold" placeholder="cth. Ulangan Harian 1">
          </div>
          <div>
            <label class="f-label text-xs">Kode Singkat <span class="text-red-500">*</span></label>
            <input type="text" id="editorInputCode" class="f-input py-1.5 text-xs uppercase font-mono font-bold" placeholder="UH1">
          </div>
          <div>
            <label class="f-label text-xs">Tipe Kolom</label>
            <select id="editorSelectType" class="f-select py-1.5 text-xs">
              <option value="SCORE">Nilai Langsung (SCORE)</option>
              <option value="SUMMARY">Kalkulasi Rata-rata (SUMMARY)</option>
            </select>
          </div>
          <div id="editorCalcGroup">
            <label class="f-label text-xs">Jenis Perhitungan</label>
            <select id="editorSelectCalc" class="f-select py-1.5 text-xs">
              <option value="AVERAGE">Rata-rata (Average)</option>
              <option value="WEIGHTED_AVERAGE">Rata-rata Berbobot</option>
              <option value="SUM">Penjumlahan (Sum)</option>
            </select>
          </div>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-3">
          <div>
            <label class="f-label text-xs">Bobot Nilai (%)</label>
            <input type="number" id="editorInputWeight" class="f-input py-1.5 text-xs" min="0" max="100" value="15">
          </div>
          <div>
            <label class="f-label text-xs">Nilai Maksimum</label>
            <input type="number" id="editorInputMax" class="f-input py-1.5 text-xs" min="1" max="100" value="100">
          </div>
          <div class="md:col-span-2 flex items-end">
            <p class="text-[11px] text-bluedark/60 leading-tight">
              Tip: Setiap perubahan pada formulir ini akan <strong>langsung terlihat secara live</strong> pada kolom buku nilai di atas.
            </p>
          </div>
        </div>

        <!-- Section Pilihan Sumber Kolom (Hanya Aktif untuk Kolom Tipe SUMMARY) -->
        <div id="editorSourcesContainer" class="mt-4 pt-3 border-t border-slate-200">
          <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <div>
              <label class="f-label text-xs font-bold text-bluedark mb-0.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blueprim" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Pilih Kolom Sumber Perhitungan Nilai</span>
              </label>
              <p class="text-[11px] text-bluedark/60">
                Centang kolom mana saja yang nilainya akan dikalkulasikan ke dalam kolom <span id="editorSourcesColCode" class="font-bold text-blueprim">RUH</span> ini.
              </p>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" id="btnSelectAllSources" class="text-[11px] text-blueprim hover:underline font-semibold cursor-pointer">
                Pilih Semua
              </button>
              <span class="text-slate-300 text-xs">|</span>
              <button type="button" id="btnResetSources" class="text-[11px] text-slate-500 hover:underline cursor-pointer">
                Kosongkan
              </button>
            </div>
          </div>

          <div id="sourcesCheckboxesList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 bg-white p-3 rounded-lg border border-slate-200 max-h-48 overflow-y-auto">
            <!-- Dynamic checkboxes generated in JS -->
          </div>
        </div>
      </div>

      <!-- Container Hidden Inputs untuk submit ke Controller -->
      <div id="hiddenInputsContainer"></div>

    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pb-8">
      <a href="{{ route('teacher.gradebooks.index') }}" class="btn btn-outline">
        Batal
      </a>
      <button type="submit" class="btn btn-primary px-6">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Simpan &amp; Buat Buku Nilai</span>
      </button>
    </div>
  </form>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const headerRow = document.getElementById("spreadsheetHeaderRow");
  const bodyRows = document.getElementById("spreadsheetBodyRows");
  const hiddenContainer = document.getElementById("hiddenInputsContainer");
  const assignmentSelect = document.getElementById("assignmentSelect");
  const gradebookNameInput = document.getElementById("gradebookNameInput");

  // Editor elements
  const editorCard = document.getElementById("columnEditorCard");
  const editorColTitle = document.getElementById("editorColNameTitle");
  const editorInputName = document.getElementById("editorInputName");
  const editorInputCode = document.getElementById("editorInputCode");
  const editorSelectType = document.getElementById("editorSelectType");
  const editorSelectCalc = document.getElementById("editorSelectCalc");
  const editorCalcGroup = document.getElementById("editorCalcGroup");
  const editorInputWeight = document.getElementById("editorInputWeight");
  const editorInputMax = document.getElementById("editorInputMax");
  const btnDeleteCurrentCol = document.getElementById("btnDeleteCurrentCol");
  const btnMoveColLeft = document.getElementById("btnMoveColLeft");
  const btnMoveColRight = document.getElementById("btnMoveColRight");

  // Sources elements
  const editorSourcesContainer = document.getElementById("editorSourcesContainer");
  const editorSourcesColCode = document.getElementById("editorSourcesColCode");
  const sourcesCheckboxesList = document.getElementById("sourcesCheckboxesList");
  const btnSelectAllSources = document.getElementById("btnSelectAllSources");
  const btnResetSources = document.getElementById("btnResetSources");

  // Sample siswa preview
  const sampleStudents = [
    'Ahya Rosadi',
    'Amalia Lestari',
    'Baiq Septia',
    'Dani Ansari'
  ];

  // Susunan 10 kolom standar kurikulum SMK
  const standardSmkColumns = [
    { name: 'Ulangan Harian 1', code: 'UH1', type: 'SCORE', calc: 'AVERAGE', weight: 15, max: 100, sources: [] },
    { name: 'Ulangan Harian 2', code: 'UH2', type: 'SCORE', calc: 'AVERAGE', weight: 15, max: 100, sources: [] },
    { name: 'Rata-rata Ulangan Harian', code: 'RUH', type: 'SUMMARY', calc: 'AVERAGE', weight: 0, max: 100, sources: ['UH1', 'UH2'] },
    { name: 'Tugas 1', code: 'T1', type: 'SCORE', calc: 'AVERAGE', weight: 10, max: 100, sources: [] },
    { name: 'Tugas 2', code: 'T2', type: 'SCORE', calc: 'AVERAGE', weight: 10, max: 100, sources: [] },
    { name: 'Tugas 3', code: 'T3', type: 'SCORE', calc: 'AVERAGE', weight: 10, max: 100, sources: [] },
    { name: 'Rata-rata Tugas', code: 'RTG', type: 'SUMMARY', calc: 'AVERAGE', weight: 0, max: 100, sources: ['T1', 'T2', 'T3'] },
    { name: 'Penilaian Tengah Semester', code: 'MID', type: 'SCORE', calc: 'AVERAGE', weight: 20, max: 100, sources: [] },
    { name: 'Penilaian Akhir Semester', code: 'SEM', type: 'SCORE', calc: 'AVERAGE', weight: 20, max: 100, sources: [] },
    { name: 'Nilai Akhir Bersih', code: 'NSB', type: 'SUMMARY', calc: 'WEIGHTED_AVERAGE', weight: 100, max: 100, sources: ['RUH', 'RTG', 'MID', 'SEM'] }
  ];

  let columns = JSON.parse(JSON.stringify(standardSmkColumns));
  let selectedIndex = 0; // Kolom yang sedang aktif diedit

  // Pindahkan urutan kolom (Geser Kiri / Kanan)
  function moveColumn(fromIndex, toIndex) {
    if (toIndex < 0 || toIndex >= columns.length || fromIndex === toIndex) return;
    const item = columns.splice(fromIndex, 1)[0];
    columns.splice(toIndex, 0, item);
    selectedIndex = toIndex;
    renderSpreadsheet();
    loadColumnToEditor();

    // Scroll kolom terpilih ke viewport
    setTimeout(() => {
      const ths = headerRow.querySelectorAll("th");
      // offset 2 karena index 0 = No, index 1 = Nama Siswa
      if (ths[toIndex + 2]) {
        ths[toIndex + 2].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      }
    }, 50);
  }

  // Render Horizontal Spreadsheet
  function renderSpreadsheet() {
    // 1. Reset Header
    headerRow.innerHTML = `
      <th class="w-12 text-center bg-slate-100 border-r border-slate-200">No</th>
      <th class="min-w-[170px] bg-slate-100 border-r border-slate-200">Nama Siswa</th>
    `;

    columns.forEach((col, idx) => {
      const isSelected = (idx === selectedIndex);
      const isSummary = col.type === 'SUMMARY';
      const th = document.createElement("th");
      th.className = `p-2 min-w-[125px] text-center cursor-pointer transition-all border-r border-slate-200 select-none ${
        isSelected 
          ? 'bg-blue-100 text-bluedark ring-2 ring-blueprim ring-inset shadow-xs' 
          : 'bg-white hover:bg-bluelight/50 text-slate-700'
      }`;
      th.title = isSummary 
        ? `Kalkulasi dari: ${(col.sources && col.sources.length) ? col.sources.join(', ') : 'Semua kolom nilai sebelumnya'}`
        : 'Klik untuk mengedit kolom ini';

      const sourcesCount = (col.sources && Array.isArray(col.sources)) ? col.sources.length : 0;
      const sourcesText = isSummary ? (sourcesCount > 0 ? `${sourcesCount} Sumber` : 'Semua') : `${col.weight}%`;

      th.innerHTML = `
        <div class="flex flex-col items-center gap-1">
          <div class="flex items-center justify-between w-full text-[10px] opacity-75">
            <span class="badge ${isSummary ? 'badge-yellow' : 'badge-blue'} px-1 py-0 text-[9px] font-bold">${isSummary ? 'RATA' : 'NILAI'}</span>
            <span class="font-mono text-[10px] text-slate-600">${sourcesText}</span>
          </div>
          <div class="font-heading font-bold text-xs truncate max-w-[110px] text-bluedark">${escapeHtml(col.code || 'COL')}</div>
          <div class="text-[10px] opacity-80 truncate max-w-[110px]">${escapeHtml(col.name)}</div>
          ${isSelected ? `
            <div class="flex items-center justify-between w-full mt-1 pt-1 border-t border-blue-200">
              <button type="button" class="btn-th-left p-0.5 text-blue-700 hover:text-white hover:bg-blueprim rounded ${idx === 0 ? 'opacity-20 pointer-events-none' : ''}" title="Geser ke kiri">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <span class="text-[9px] font-bold text-blueprim">Aktif</span>
              <button type="button" class="btn-th-right p-0.5 text-blue-700 hover:text-white hover:bg-blueprim rounded ${idx === columns.length - 1 ? 'opacity-20 pointer-events-none' : ''}" title="Geser ke kanan">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          ` : ''}
        </div>
      `;

      th.addEventListener("click", (e) => {
        if (e.target.closest(".btn-th-left") || e.target.closest(".btn-th-right")) return;
        selectedIndex = idx;
        renderSpreadsheet();
        loadColumnToEditor();
      });

      // Quick move buttons inside TH
      th.querySelector(".btn-th-left")?.addEventListener("click", (e) => {
        e.stopPropagation();
        moveColumn(idx, idx - 1);
      });
      th.querySelector(".btn-th-right")?.addEventListener("click", (e) => {
        e.stopPropagation();
        moveColumn(idx, idx + 1);
      });

      headerRow.appendChild(th);
    });

    // Tombol Tambah Kolom di ujung kanan tabel
    const thAdd = document.createElement("th");
    thAdd.className = "w-24 text-center bg-slate-50 border-r border-dashed border-slate-300 p-2";
    thAdd.innerHTML = `
      <button type="button" class="btn btn-outline btn-sm text-[11px] py-1 px-2 whitespace-nowrap cursor-pointer hover:bg-bluelight flex items-center justify-center gap-1 mx-auto" id="btnQuickAddRight">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Kolom</span>
      </button>
    `;
    headerRow.appendChild(thAdd);

    document.getElementById("btnQuickAddRight")?.addEventListener("click", () => {
      addNewColumn('Tugas Baru', 'T' + (columns.length + 1), 'SCORE');
    });

    // 2. Render Sample Rows
    bodyRows.innerHTML = "";
    sampleStudents.forEach((st, i) => {
      const tr = document.createElement("tr");
      tr.className = "hover:bg-slate-50/60";
      let colsHtml = `
        <td class="text-center font-semibold text-slate-500 text-xs border-r border-slate-100">${i + 1}</td>
        <td class="font-medium text-bluedark text-xs border-r border-slate-100">${st}</td>
      `;

      columns.forEach((col, idx) => {
        const isSelected = (idx === selectedIndex);
        const isSummary = col.type === 'SUMMARY';
        const sampleVal = isSummary ? (85 + (i * 2)) : (80 + ((i + idx) * 3) % 18);
        colsHtml += `
          <td class="text-center text-xs border-r border-slate-100 py-1.5 ${isSelected ? 'bg-blue-50/50 font-bold' : ''}">
            <span class="${isSummary ? 'font-mono font-bold text-blueprim' : 'text-slate-600'}">${sampleVal}</span>
          </td>
        `;
      });

      colsHtml += `<td class="text-center text-slate-300 text-xs">-</td>`;
      tr.innerHTML = colsHtml;
      bodyRows.appendChild(tr);
    });

    // 3. Render Hidden Inputs untuk dikirimkan melalui form POST
    renderHiddenInputs();
  }

  // Render pilihan checkbox sumber kolom untuk tipe SUMMARY
  function renderSourcesCheckboxes() {
    if (columns.length === 0 || selectedIndex < 0 || selectedIndex >= columns.length) {
      return;
    }

    const col = columns[selectedIndex];
    if (col.type !== 'SUMMARY') {
      editorSourcesContainer.classList.add("hidden");
      return;
    }

    editorSourcesContainer.classList.remove("hidden");
    editorSourcesColCode.textContent = col.code || 'SUMMARY';
    sourcesCheckboxesList.innerHTML = "";

    if (!Array.isArray(col.sources)) {
      col.sources = [];
    }

    // Kolom-kolom kandidat adalah semua kolom lain selain kolom ini
    const candidateColumns = columns.filter((_, i) => i !== selectedIndex);

    if (candidateColumns.length === 0) {
      sourcesCheckboxesList.innerHTML = `
        <p class="text-xs text-slate-400 italic col-span-full py-2 text-center">
          Belum ada kolom lain untuk dijadikan sumber perhitungan. Tambahkan kolom nilai terlebih dahulu.
        </p>
      `;
      return;
    }

    candidateColumns.forEach(cand => {
      const isChecked = col.sources.includes(cand.code);
      const isCandSummary = cand.type === 'SUMMARY';

      const label = document.createElement("label");
      label.className = `flex items-center gap-2 p-2 rounded-lg border cursor-pointer select-none text-xs transition-colors ${
        isChecked 
          ? 'bg-blue-50/70 border-blueprim/40 text-bluedark shadow-2xs' 
          : 'bg-slate-50/50 border-slate-200 text-slate-600 hover:bg-slate-100'
      }`;

      label.innerHTML = `
        <input type="checkbox" class="source-checkbox rounded text-blueprim focus:ring-blueprim" value="${escapeHtml(cand.code)}" ${isChecked ? 'checked' : ''}>
        <div class="truncate flex-1">
          <div class="flex items-center justify-between gap-1">
            <span class="font-bold font-mono text-[11px] text-bluedark">${escapeHtml(cand.code)}</span>
            <span class="text-[9px] px-1 py-0 rounded ${isCandSummary ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-600'}">${isCandSummary ? 'Rata' : 'Nilai'}</span>
          </div>
          <span class="text-[10px] text-slate-500 block truncate" title="${escapeHtml(cand.name)}">${escapeHtml(cand.name)}</span>
        </div>
      `;

      const cb = label.querySelector(".source-checkbox");
      cb.addEventListener("change", () => {
        if (cb.checked) {
          if (!col.sources.includes(cand.code)) {
            col.sources.push(cand.code);
          }
        } else {
          col.sources = col.sources.filter(s => s !== cand.code);
        }
        renderSourcesCheckboxes();
        renderSpreadsheet();
      });

      sourcesCheckboxesList.appendChild(label);
    });
  }

  // Load kolom terpilih ke Panel Editor
  function loadColumnToEditor() {
    if (columns.length === 0 || selectedIndex < 0 || selectedIndex >= columns.length) {
      editorCard.classList.add("hidden");
      return;
    }

    editorCard.classList.remove("hidden");
    const col = columns[selectedIndex];
    editorColTitle.textContent = `${col.name} (${col.code})`;
    editorInputName.value = col.name;
    editorInputCode.value = col.code;
    editorSelectType.value = col.type;
    editorSelectCalc.value = col.calc || 'AVERAGE';
    editorInputWeight.value = col.weight;
    editorInputMax.value = col.max;

    // Tombol Geser Status
    btnMoveColLeft.disabled = (selectedIndex <= 0);
    btnMoveColRight.disabled = (selectedIndex >= columns.length - 1);

    if (col.type === 'SUMMARY') {
      editorCalcGroup.classList.remove("opacity-50", "pointer-events-none");
    } else {
      editorCalcGroup.classList.add("opacity-50", "pointer-events-none");
    }

    renderSourcesCheckboxes();
  }

  // Update Live saat mengetik di Panel Editor
  function updateCurrentColumnFromEditor() {
    if (!columns[selectedIndex]) return;
    const col = columns[selectedIndex];
    const oldCode = col.code;
    const newCode = (editorInputCode.value || 'COL').toUpperCase();

    // Jika kode berubah, update referensi di kolom-kolom summary lainnya
    if (oldCode !== newCode) {
      columns.forEach(c => {
        if (c.sources && Array.isArray(c.sources)) {
          c.sources = c.sources.map(s => s === oldCode ? newCode : s);
        }
      });
    }

    col.name = editorInputName.value || 'Kolom';
    col.code = newCode;
    col.type = editorSelectType.value;
    col.calc = editorSelectCalc.value;
    col.weight = parseFloat(editorInputWeight.value) || 0;
    col.max = parseFloat(editorInputMax.value) || 100;

    editorColTitle.textContent = `${col.name} (${col.code})`;
    renderSpreadsheet();
    renderSourcesCheckboxes();
  }

  editorInputName.addEventListener("input", updateCurrentColumnFromEditor);
  editorInputCode.addEventListener("input", updateCurrentColumnFromEditor);
  editorSelectType.addEventListener("change", () => {
    updateCurrentColumnFromEditor();
    loadColumnToEditor();
  });
  editorSelectCalc.addEventListener("change", updateCurrentColumnFromEditor);
  editorInputWeight.addEventListener("input", updateCurrentColumnFromEditor);
  editorInputMax.addEventListener("input", updateCurrentColumnFromEditor);

  // Button Geser Kiri / Kanan di Editor
  btnMoveColLeft.addEventListener("click", () => {
    moveColumn(selectedIndex, selectedIndex - 1);
  });

  btnMoveColRight.addEventListener("click", () => {
    moveColumn(selectedIndex, selectedIndex + 1);
  });

  // Pilih Semua Sumber
  btnSelectAllSources.addEventListener("click", () => {
    if (!columns[selectedIndex] || columns[selectedIndex].type !== 'SUMMARY') return;
    columns[selectedIndex].sources = columns.filter((_, i) => i !== selectedIndex).map(c => c.code);
    renderSourcesCheckboxes();
    renderSpreadsheet();
  });

  // Kosongkan Sumber
  btnResetSources.addEventListener("click", () => {
    if (!columns[selectedIndex] || columns[selectedIndex].type !== 'SUMMARY') return;
    columns[selectedIndex].sources = [];
    renderSourcesCheckboxes();
    renderSpreadsheet();
  });

  // Hapus Kolom yang Sedang Diedit
  btnDeleteCurrentCol.addEventListener("click", () => {
    if (columns.length <= 1) {
      alert("Buku nilai minimal harus memiliki 1 kolom penilaian.");
      return;
    }
    const delCol = columns[selectedIndex];
    if (confirm(`Hapus kolom "${delCol.name}"?`)) {
      const delCode = delCol.code;
      columns.splice(selectedIndex, 1);
      // Hapus referensi dari kolom summary lain
      columns.forEach(c => {
        if (c.sources && Array.isArray(c.sources)) {
          c.sources = c.sources.filter(s => s !== delCode);
        }
      });

      selectedIndex = Math.max(0, selectedIndex - 1);
      renderSpreadsheet();
      loadColumnToEditor();
    }
  });

  // Tambah Kolom Baru ke Samping
  function addNewColumn(name, code, type, calc = 'AVERAGE', weight = 10, max = 100, sources = []) {
    columns.push({
      name: name,
      code: code,
      type: type,
      calc: calc,
      weight: weight,
      max: max,
      sources: sources
    });
    selectedIndex = columns.length - 1; // Otomatis pilih kolom yang baru ditambahkan
    renderSpreadsheet();
    loadColumnToEditor();
    // Scroll spreadsheet ke kanan
    const scrollContainer = document.querySelector("#spreadsheetPreviewTable").parentElement;
    scrollContainer.scrollTo({ left: scrollContainer.scrollWidth, behavior: 'smooth' });
  }

  // Tombol Toolbar Preset
  document.getElementById("btnAddTugasCol").addEventListener("click", () => {
    const num = columns.filter(c => c.code.startsWith("T")).length + 1;
    addNewColumn(`Tugas ${num}`, `T${num}`, 'SCORE', '', 10, 100, []);
  });

  document.getElementById("btnAddUHCol").addEventListener("click", () => {
    const num = columns.filter(c => c.code.startsWith("UH")).length + 1;
    addNewColumn(`Ulangan Harian ${num}`, `UH${num}`, 'SCORE', '', 15, 100, []);
  });

  document.getElementById("btnAddSummaryCol").addEventListener("click", () => {
    // Sumber default adalah semua kolom SCORE sebelumnya
    const prevScoreCodes = columns.filter(c => c.type === 'SCORE').map(c => c.code);
    addNewColumn('Rata-rata Nilai', 'R' + (columns.filter(c => c.type === 'SUMMARY').length + 1), 'SUMMARY', 'AVERAGE', 0, 100, prevScoreCodes);
  });

  document.getElementById("btnPresetSMK").addEventListener("click", () => {
    if (confirm("Gunakan susunan 10 kolom standar kurikulum SMK? Kolom saat ini akan digantikan dengan preset standar.")) {
      columns = JSON.parse(JSON.stringify(standardSmkColumns));
      selectedIndex = 0;
      renderSpreadsheet();
      loadColumnToEditor();
    }
  });

  // Render Hidden Inputs
  function renderHiddenInputs() {
    hiddenContainer.innerHTML = "";
    columns.forEach((col, idx) => {
      hiddenContainer.innerHTML += `
        <input type="hidden" name="columns[${idx}][name]" value="${escapeHtml(col.name)}">
        <input type="hidden" name="columns[${idx}][code]" value="${escapeHtml(col.code)}">
        <input type="hidden" name="columns[${idx}][column_type]" value="${col.type}">
        <input type="hidden" name="columns[${idx}][calculation_type]" value="${col.calc}">
        <input type="hidden" name="columns[${idx}][weight]" value="${col.weight}">
        <input type="hidden" name="columns[${idx}][max_score]" value="${col.max}">
      `;

      if (col.type === 'SUMMARY' && Array.isArray(col.sources)) {
        col.sources.forEach(src => {
          hiddenContainer.innerHTML += `
            <input type="hidden" name="columns[${idx}][sources][]" value="${escapeHtml(src)}">
          `;
        });
      }
    });
  }

  // Auto-fill nama buku nilai saat assignment dipilih
  if (assignmentSelect) {
    assignmentSelect.addEventListener("change", function() {
      const opt = this.options[this.selectedIndex];
      if (opt && opt.value) {
        const sem = opt.getAttribute("data-sem") || "Gasal";
        const year = opt.getAttribute("data-year") || "2026/2027";
        gradebookNameInput.value = `Buku Nilai - ${sem} ${year}`;
      }
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Initial render
  renderSpreadsheet();
  loadColumnToEditor();
});
</script>
@endsection
