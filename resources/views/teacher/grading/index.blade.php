@extends('layouts.teacher')

@section('title', 'Penilaian Siswa — Guru')

@push('styles')
<style>
.active-col-header {
  background-color: #0D47A1 !important;
  color: #ffffff !important;
  border-color: #0D47A1 !important;
  box-shadow: inset 0 0 0 2px #2196F3, 0 3px 10px rgba(13, 71, 161, 0.25) !important;
}
.active-col-header * {
  color: #ffffff !important;
}
.active-col-cell {
  background-color: #F0F7FF !important;
}
.active-row-student {
  background-color: #F8FAFC !important;
}
.active-score-cell {
  background-color: #DBEAFE !important;
  border: 2px solid #2563EB !important;
  box-shadow: inset 0 0 0 1px #2563EB, 0 2px 8px rgba(37, 99, 235, 0.25) !important;
  font-weight: 700 !important;
  color: #1E3A8A !important;
  position: relative;
  z-index: 5;
}
.score-cell-interactive {
  cursor: pointer;
  transition: all 0.15s ease-in-out;
  user-select: none;
}
.score-cell-interactive:hover {
  background-color: #E0E7FF !important;
  border-color: #93C5FD !important;
}
.quick-col-btn {
  font-family: inherit;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}
.quick-col-btn.active-quick-btn {
  background-color: #0D47A1 !important;
  color: #ffffff !important;
  border-color: #0D47A1 !important;
  box-shadow: 0 2px 8px rgba(13, 71, 161, 0.28) !important;
}
.quick-col-btn.active-quick-btn .quick-col-name {
  color: #ffffff !important;
}
.quick-col-btn .quick-task-badge {
  background-color: #d1fae5 !important;
  color: #065f46 !important;
  font-weight: 600 !important;
}
.quick-col-btn.active-quick-btn .quick-task-badge {
  background-color: #ffffff !important;
  color: #065f46 !important;
  font-weight: 700 !important;
}
.quick-col-btn .quick-rubric-badge {
  background-color: #ede9fe !important;
  color: #5b21b6 !important;
  font-weight: 600 !important;
}
.quick-col-btn.active-quick-btn .quick-rubric-badge {
  background-color: #ffffff !important;
  color: #5b21b6 !important;
  font-weight: 700 !important;
}
.cell-score-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.25rem;
  padding: 0.2rem 0.4rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #E2E8F0;
  background-color: #FFFFFF;
  color: #1E293B;
  transition: all 0.15s ease;
}
.active-score-cell .cell-score-pill {
  border-color: #2563EB;
  background-color: #2563EB;
  color: #FFFFFF;
}
.cell-score-empty {
  color: #94A3B8;
  font-weight: 400;
}
.cell-score-direct-input {
  width: 3.25rem;
  padding: 0.18rem 0.25rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-align: center;
  border-radius: 0.375rem;
  border: 1px solid #CBD5E1;
  background-color: #FFFFFF;
  color: #0F172A;
  transition: all 0.15s ease;
  -moz-appearance: textfield !important;
  appearance: textfield !important;
}
.cell-score-direct-input:focus {
  outline: none;
  border-color: #2563EB;
  background-color: #EFF6FF;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}
.active-score-cell .cell-score-direct-input {
  border-color: #2563EB;
  background-color: #FFFFFF;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3);
}
/* Sembunyikan panah stepper / spin buttons pada input angka */
.cell-score-direct-input::-webkit-outer-spin-button,
.cell-score-direct-input::-webkit-inner-spin-button,
.rubric-point::-webkit-outer-spin-button,
.rubric-point::-webkit-inner-spin-button,
#inputDirectScore::-webkit-outer-spin-button,
#inputDirectScore::-webkit-inner-spin-button {
  -webkit-appearance: none !important;
  margin: 0 !important;
  display: none !important;
}
.rubric-point,
#inputDirectScore {
  -moz-appearance: textfield !important;
  appearance: textfield !important;
}
.cell-disabled-task {
  background-color: #F8FAFC !important;
  cursor: not-allowed !important;
  opacity: 0.6;
  user-select: none;
}
.col-disabled-header {
  cursor: not-allowed !important;
  opacity: 0.55 !important;
  filter: grayscale(40%);
}
.badge-rubric-cell {
  font-size: 8px;
  font-weight: 600;
  color: #4338CA;
  background: #EEF2FF;
  border-radius: 3px;
  padding: 1px 4px;
}
.badge-direct-cell {
  font-size: 8px;
  font-weight: 600;
  color: #0284C7;
  background: #E0F2FE;
  border-radius: 3px;
  padding: 1px 4px;
}
</style>
@endpush

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-medium">{{ session('success') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
  @endif

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penilaian Siswa</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola &amp; Input Nilai Siswa pada Tugas dan Ulangan yang Telah Diatur</p>
  </div>

  <!-- Arrow Tabs: 100% In-Page Navigation -->
  <div class="arrow-tabs" id="nilaiTabs">
    <button type="button" class="active" data-step="1"><span class="step-num">1</span>Daftar Kelas</button>
    <button type="button" data-step="2" disabled><span class="step-num">2</span>Buku Nilai</button>
    <button type="button" data-step="3" disabled><span class="step-num">3</span>Penilaian</button>
  </div>

  <!-- ========================================== -->
  <!-- TAB 1: DAFTAR KELAS                        -->
  <!-- ========================================== -->
  <div class="step-panel active" data-panel="1">
    <p class="text-sm text-bluedark/60 mb-4">Pilih kelas untuk mengelola buku nilai dan input penilaian siswa</p>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4" id="nilaiKelasGrid">
      @forelse($assignments as $assign)
        @php
          $classModel = $assign->schoolClass;
          $className = $classModel?->name ?? 'XII RA';
          $deptName = $classModel?->department?->name ?? 'Rekayasa Perangkat Lunak';
          $studentCount = $assign->gradebooks->first()?->students->count() ?? $classModel?->enrollments->count() ?? 36;
          $days = $assign->schedules->pluck('day_name')->filter()->unique()->join(' & ') ?: 'Senin & Rabu';
          $semesterName = $assign->semester?->name ?? 'Gasal';
          $academicYear = $assign->semester?->academicYear?->name ?? '2026/2027';
          $subjectName = $assign->subject?->name ?? 'Matematika';
        @endphp

        <button type="button" class="kelas-card w-full text-left"
          data-id="{{ $assign->id }}"
          data-kode="{{ $className }}"
          data-jurusan="{{ $deptName }}"
          data-siswa="{{ $studentCount }}"
          data-hari="{{ $days }}"
          data-semester="{{ $semesterName }}"
          data-tahun="{{ $academicYear }}"
          data-mapel="{{ $subjectName }}">
          <div class="flex items-center justify-between mb-2">
            <div class="crud-card__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
          <div class="font-heading font-bold text-bluedark text-sm">{{ $className }}</div>
          <div class="text-xs text-bluedark/50 truncate">{{ $deptName }}</div>
          <div class="kelas-card__meta">
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              {{ $studentCount }} Siswa
            </span>
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              {{ $days }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <span class="badge badge-blue">{{ $semesterName }}</span>
            <span class="badge badge-gray">{{ $academicYear }}</span>
          </div>
        </button>
      @empty
        <div class="col-span-full py-10 text-center text-xs text-bluedark/50 panel">
          Belum ada kelas yang diajar.
        </div>
      @endforelse
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB 2: BUKU NILAI                          -->
  <!-- ========================================== -->
  <div class="step-panel" data-panel="2">
    <p class="text-sm text-bluedark/60 mb-4">
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.nilaiGoToStep(1)">Daftar Kelas</button> / 
      <span class="font-semibold text-bluedark" id="nilaiBreadcrumb2">XII RA</span>
    </p>

    <!-- Banner Biru Kelas Aktif -->
    <div class="panel p-4 flex items-center justify-between gap-3 mb-5" style="background:#2196F3;color:#fff;">
      <div class="flex items-center gap-3 min-w-0">
        <div class="crud-card__icon" style="background:rgba(255,255,255,.2);color:#fff;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div class="min-w-0">
          <div class="font-heading font-bold truncate text-base md:text-lg" id="nilaiKelasTitle">Kelas XII RA</div>
          <div class="text-[11px] md:text-xs opacity-85" id="nilaiKelasSub">Rekayasa Perangkat Lunak &middot; 36 Siswa</div>
        </div>
      </div>
      <span class="badge" id="nilaiKelasBadge" style="background:rgba(255,255,255,.2);color:#fff;">Gasal &middot; 2026/2027</span>
    </div>

    <div class="flex items-center justify-between mb-3">
      <h2 class="font-heading font-semibold text-bluedark text-[15px]">Pilih Buku Nilai untuk Penilaian</h2>
    </div>

    <!-- List Buku Nilai untuk kelas terpilih -->
    <div class="flex flex-col gap-3" id="nilaiBukuNilaiContainer"></div>
  </div>

  <!-- ========================================== -->
  <!-- TAB 3: PENILAIAN / TUGAS & REMIDI          -->
  <!-- ========================================== -->
  <div class="step-panel" data-panel="3">
    <p class="text-sm text-bluedark/60 mb-4">
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.nilaiGoToStep(1)">Daftar Kelas</button> / 
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.nilaiGoToStep(2)" id="nilaiBreadcrumb3Kelas">XII RA</button> - 
      <span class="font-semibold text-bluedark" id="nilaiBreadcrumb3">Gasal 2026/2027</span>
    </p>

    <!-- Spreadsheet Nilai Siswa & Pemilihan Sel / Kolom -->
    <div class="panel p-4 sm:p-5 mb-5 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-bluelight">
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-sm sm:text-base">
            Daftar Nilai - Kelas <span id="nilaiKelasName3">XII RA</span>
          </h2>
          <p class="text-xs text-bluedark/50" id="nilaiSub3">Matematika - Gasal 2026/2027</p>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" class="btn btn-outline btn-sm" id="btnExportNilai">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export
          </button>
        </div>
      </div>

      <!-- Panduan & Pilihan Kolom Cepat -->
      <div class="bg-bluelight/40 p-3 rounded-xl border border-bluelight/80">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-bluedark">Pilih Cepat Kolom Asesmen:</span>
            <span class="text-[11px] text-bluedark/60 hidden sm:inline">(Klik tombol kolom atau klik langsung sel nilai siswa di tabel)</span>
          </div>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1 db-scroll" id="quickColumnSelector">
          <!-- Diisi dinamis oleh JavaScript -->
        </div>
      </div>

      <!-- Banner Sel & Siswa Terpilih Interaktif -->
      <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-200 flex items-center justify-between flex-wrap gap-2" id="selectedCellBanner">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-blueprim text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div>
            <div class="text-xs font-bold text-bluedark" id="bannerSelectedTitle">
              Siswa Terpilih: <span id="bannerStudentName">-</span> &middot; Kolom: <span id="bannerColumnName">-</span>
            </div>
            <div class="text-[11px] text-bluedark/60" id="bannerSelectedSubtitle">
              Klik pada sel nilai siswa di tabel untuk memilih siswa dan mengisi nilai secara cepat.
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="badge badge-blue text-xs font-bold px-2.5 py-1" id="bannerScoreBadge">Nilai: -</span>
        </div>
      </div>

      <!-- Spreadsheet Table -->
      <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl max-h-[460px]">
        <table class="tbl w-full text-xs">
          <thead class="sticky top-0 z-10" id="nilaiTableHead">
            <!-- Diisi secara dinamis oleh JavaScript dengan grouping kategori -->
          </thead>
          <tbody id="nilaiTableBody">
            <!-- Diisi secara dinamis oleh JavaScript -->
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between text-[11px] text-bluedark/60 pt-1 flex-wrap gap-2">
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-500 inline-block"></span> Sel Terpilih</span>
          <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-100 border border-slate-300 inline-block"></span> Rata-rata / Ringkasan</span>
        </div>
        <span class="italic text-bluedark/50">*Nilai rata-rata dan nilai akhir dihitung secara otomatis.</span>
      </div>
    </div>

    <!-- Form: Manajemen Nilai (Matching media_1790821692377.png & tugas tab design) -->
    <div class="form-block">
      <div class="form-block__header" style="background:#0D47A1 !important; color:#ffffff !important;">
        <h3 id="formPenilaianHeaderTitle" class="!text-white font-heading font-bold text-sm md:text-base">Manajemen Nilai</h3>
        <p class="!text-blue-100 text-xs mt-0.5">Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai</p>
      </div>

      <form action="{{ route('teacher.grading.store') }}" method="POST" class="form-block__body space-y-4" id="formPenilaianSiswa">
        @csrf

        <input type="hidden" name="teaching_assignment_id" id="formAssignmentId">
        <input type="hidden" name="gradebook_id" id="formGradebookId">

        <div class="form-row cols-2">
          <div>
            <label class="f-label font-bold text-xs text-bluedark">Judul Tugas</label>
            <input type="text" name="task_title" id="inputJudulTugas" class="f-input font-medium" placeholder="cth. Persamaan Linear" value="Persamaan Linear">
          </div>
          <div>
            <label class="f-label font-bold text-xs text-bluedark">Kolom pada Buku Nilai <span class="text-red-500">*</span></label>
            <select name="gradebook_column_id" class="f-select font-medium" id="selectKolomPenilaian" required>
              <!-- Diisi opsi kolom dari gradebook aktif -->
            </select>
          </div>
        </div>

        <div>
          <label class="f-label font-bold text-xs text-bluedark">Nama Siswa <span class="text-red-500">*</span></label>
          <select name="student_id" class="f-select font-medium" id="selectSiswaPenilaian" required>
            <!-- Diisi daftar siswa dari rombel aktif -->
          </select>
        </div>

        <!-- Section: Bukti Pengiriman Siswa (Tampil jika tugas wajib mengirimkan bukti) -->
        <div id="sectionBuktiPengiriman" class="hidden p-3.5 rounded-xl border border-blue-200 bg-blue-50/70 space-y-2.5">
          <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-blue-200/80">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded bg-blueprim text-white flex items-center justify-center shrink-0">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              </div>
              <div>
                <span class="font-bold text-xs text-bluedark block">Bukti Pengiriman Tugas Siswa</span>
                <span class="text-[10px] text-bluedark/60" id="submissionDateText">Menunggu data pengiriman...</span>
              </div>
            </div>
            <div id="badgeStatusPengiriman"></div>
          </div>
          <div id="kontenDetailPengiriman" class="text-xs"></div>
        </div>

        <!-- Rubrik / Kriteria Penilaian Table -->
        <div class="mb-3" id="rubrikPenilaianSection">
          <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
            <label class="f-label font-bold text-xs text-bluedark mb-0 block">Penilaian</label>
            <span class="text-[11px] text-blueprim font-medium" id="rubrikInfoBadge">Menggunakan pedoman rubrik standar penilaian</span>
          </div>

          <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl bg-white shadow-2xs">
            <table class="tbl w-full text-xs">
              <thead>
                <tr style="background:#0D47A1 !important; color:#ffffff !important;">
                  <th class="py-2.5 px-3 text-left !text-white text-xs font-bold" style="color:#ffffff !important;">Kriteria Penilaian</th>
                  <th style="width: 10rem;" class="py-2.5 px-3 text-center !text-white text-xs font-bold" style="color:#ffffff !important;">Nilai</th>
                </tr>
              </thead>
              <tbody id="rubrikTableBody">
                <!-- Diisi kriteria dinamis oleh JavaScript -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Alternatif: Input Nilai Langsung (Jika tanpa kriteria rubrik) -->
        <div class="mb-3 hidden" id="directScoreSection">
          <label class="f-label font-bold text-xs text-bluedark">Nilai Tugas Siswa (0 - <span id="labelDirectMaxScore">100</span>) <span class="text-red-500">*</span></label>
          <div class="relative max-w-xs">
            <input type="number" id="inputDirectScore" class="f-input pr-12 text-base font-bold font-mono" min="0" max="100" step="any" placeholder="0">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-bluedark/40 pointer-events-none">Poin</span>
          </div>
          <p class="text-[11px] text-bluedark/55 mt-1 leading-snug">
            💡 Tugas ini tidak menggunakan rubrik. Anda dapat menginput nilai di form ini atau langsung mengetik pada sel tabel siswa di atas.
          </p>
        </div>

        <!-- Hidden input for total score to store -->
        <input type="hidden" name="score" id="inputTotalScore" value="0">

        <div>
          <label class="f-label font-bold text-xs text-bluedark">Catatan / Feedback untuk Siswa (Opsional)</label>
          <input type="text" name="feedback" id="inputFeedback" class="f-input" placeholder="cth. Pengerjaan sangat baik, pertahankan kerapian logika kode.">
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
          <button type="reset" id="btnResetNilaiForm" class="btn btn-outline cursor-pointer">Reset</button>
          <button type="submit" class="btn btn-primary cursor-pointer" id="btnSubmitNilai">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span id="btnSubmitNilaiLabel">Simpan Nilai</span>
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<!-- Modal Detail Bukti Pengiriman Siswa -->
<div id="modalBuktiPengiriman" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden opacity-0 transition-opacity duration-200">
  <div class="bg-white rounded-2xl shadow-xl border border-bluelight max-w-lg w-full overflow-hidden transform transition-transform duration-200 scale-95" id="modalBuktiBox">
    <div class="p-4 bg-gradient-to-r from-blueprim to-blue-800 text-white flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
          <h4 class="font-heading font-bold text-sm text-white" id="modalBuktiTitle">Bukti Pengiriman Tugas</h4>
          <p class="text-[11px] text-blue-100" id="modalBuktiSubtitle">Detail berkas &amp; catatan pengumpulan siswa</p>
        </div>
      </div>
      <button type="button" class="text-white/80 hover:text-white text-xl font-bold p-1 cursor-pointer leading-none" onclick="window.closeModalBukti()">&times;</button>
    </div>

    <div class="p-4 sm:p-5 space-y-3.5 max-h-[70vh] overflow-y-auto db-scroll" id="modalBuktiBody">
      <!-- Konten diisi dinamis oleh JS -->
    </div>

    <div class="p-3.5 bg-slate-50 border-t border-bluelight flex justify-end">
      <button type="button" class="btn btn-outline btn-sm cursor-pointer" onclick="window.closeModalBukti()">Tutup</button>
    </div>
  </div>
</div>

<!-- Data JSON Embed untuk interaksi instan tanpa delay/reload -->
<script>
  window.__RUBRICS_DATA__ = {
    @foreach($rubrics as $rubric)
      {{ $rubric->id }}: {
        id: {{ $rubric->id }},
        name: @json($rubric->name),
        description: @json($rubric->description ?? ''),
        total_points: {{ (float) $rubric->criteria->sum('max_points') }},
        criteria: [
          @foreach($rubric->criteria as $crit)
            {
              id: {{ $crit->id }},
              criterion: @json($crit->criterion),
              description: @json($crit->description ?? ''),
              max_points: {{ (float) $crit->max_points }},
              sort_order: {{ $crit->sort_order }}
            },
          @endforeach
        ]
      },
    @endforeach
  };

  window.__ASSIGNMENTS_NILAI__ = [
    @foreach($assignments as $a)
      {
        id: {{ $a->id }},
        kode: @json($a->schoolClass?->name ?? 'XII RA'),
        jurusan: @json($a->schoolClass?->department?->name ?? 'Rekayasa Perangkat Lunak'),
        siswaCount: {{ $a->gradebooks->first()?->students->count() ?? $a->schoolClass?->enrollments->count() ?? 36 }},
        hari: @json($a->schedules->pluck('day_name')->filter()->unique()->join(' & ') ?: 'Senin & Rabu'),
        semester: @json($a->semester?->name ?? 'Gasal'),
        tahun: @json($a->semester?->academicYear?->name ?? '2026/2027'),
        mapel: @json($a->subject?->name ?? 'Matematika'),
        gradebooks: [
          @forelse($a->gradebooks as $gb)
            {
              id: {{ $gb->id }},
              title: @json($gb->name ?: 'Buku Nilai - ' . ($a->semester?->name ?? 'Gasal') . ' ' . ($a->semester?->academicYear?->name ?? '2026/2027')),
              sub: @json(($a->subject?->name ?? 'Matematika') . ' · Ulangan Harian dan Tugas'),
              buku: @json(($a->semester?->name ?? 'Gasal') . ' ' . ($a->semester?->academicYear?->name ?? '2026/2027')),
              badge: @json($a->subject?->name ?? 'Matematika'),
              columns: [
                @foreach($gb->columns as $col)
                  @php
                    $linkedAssessment = $col->assessments->first();
                    $scoresMap = [];
                    foreach ($col->scores as $sc) {
                        $rubricScoresMap = [];
                        foreach ($sc->rubricScores as $rsc) {
                            $rubricScoresMap[$rsc->rubric_criterion_id] = (float) $rsc->points_awarded;
                        }
                        $scoresMap[$sc->student_id] = [
                            'score' => (float) $sc->final_score,
                            'raw_score' => (float) $sc->raw_score,
                            'feedback' => $sc->feedback ?? '',
                            'rubric_scores' => $rubricScoresMap,
                        ];
                    }

                    $submissionsMap = [];
                    if ($linkedAssessment) {
                        foreach ($linkedAssessment->submissions as $sub) {
                            $mediaFiles = [];
                            foreach ($sub->media as $m) {
                                $mediaFiles[] = [
                                    'id' => $m->id,
                                    'name' => $m->original_name,
                                    'path' => asset('storage/' . $m->path),
                                    'mime' => $m->mime_type,
                                    'size' => $m->size,
                                ];
                            }
                            $submissionsMap[$sub->student_id] = [
                                'id' => $sub->id,
                                'status' => $sub->status?->value ?? 'SUBMITTED',
                                'status_label' => $sub->status?->label() ?? 'Dikumpulkan',
                                'content' => $sub->content ?? '',
                                'submitted_at' => $sub->submitted_at?->format('d M Y, H:i') ?? '',
                                'late_minutes' => (int) $sub->late_minutes,
                                'media' => $mediaFiles,
                            ];
                        }
                    }
                  @endphp
                  {
                    id: {{ $col->id }},
                    name: @json($col->name),
                    code: @json($col->code ?? ''),
                    column_type: @json($col->column_type?->value ?? 'SCORE'),
                    category_name: @json($col->category?->name ?? 'Nilai'),
                    max_score: {{ (float) ($col->max_score ?? 100) }},
                    weight: {{ (float) ($col->weight ?? 1) }},
                    assessment: @if($linkedAssessment)
                      {
                        id: {{ $linkedAssessment->id }},
                        title: @json($linkedAssessment->title),
                        type: @json($linkedAssessment->type?->value ?? 'TASK'),
                        status: @json($linkedAssessment->status?->value ?? 'PUBLISHED'),
                        submission_required: {{ $linkedAssessment->submission_required ? 'true' : 'false' }},
                        rubric_id: {{ $linkedAssessment->rubric_id ?? 'null' }},
                        has_rubric: {{ $linkedAssessment->rubric_id ? 'true' : 'false' }},
                        submissions: @json($submissionsMap)
                      }
                    @else
                      null
                    @endif,
                    scores: @json($scoresMap)
                  },
                @endforeach
              ],
              students: [
                @foreach($gb->students as $gbs)
                  {
                    id: {{ $gbs->student?->id ?? 0 }},
                    name: @json($gbs->student?->full_name ?? 'Siswa')
                  },
                @endforeach
              ]
            },
          @empty
            {
              id: 1,
              title: 'Buku Nilai - Gasal 2026/2027',
              sub: @json(($a->subject?->name ?? 'Matematika') . ' · Ulangan Harian dan Tugas'),
              buku: 'Gasal 2026/2027',
              badge: @json($a->subject?->name ?? 'Matematika'),
              columns: [],
              students: []
            }
          @endforelse
        ]
      },
    @endforeach
  ];

  // Default Mockup Fallback Data matching media_1790821692377.png
  window.__DEFAULT_COLUMNS_NILAI__ = [
    { 
      id: 101, 
      name: 'Ulangan Harian 1', 
      code: 'UH 1', 
      parentCode: '1', 
      column_type: 'SCORE', 
      category_name: 'Ulangan Harian', 
      max_score: 100, 
      assessment: { 
        id: 1, 
        title: 'Persamaan Linear', 
        has_rubric: true, 
        rubric_id: 1, 
        submission_required: false,
        submissions: {} 
      }, 
      scores: {} 
    },
    { 
      id: 102, 
      name: 'Remidi 1', 
      code: 'R1', 
      parentCode: '1', 
      column_type: 'SCORE', 
      category_name: 'Ulangan Harian', 
      max_score: 100, 
      assessment: { 
        id: 2, 
        title: 'Remidi Persamaan Linear', 
        has_rubric: false, 
        rubric_id: null, 
        submission_required: false,
        submissions: {} 
      }, 
      scores: {} 
    },
    { 
      id: 103, 
      name: 'Ulangan Harian 2', 
      code: 'UH 2', 
      parentCode: '2', 
      column_type: 'SCORE', 
      category_name: 'Ulangan Harian', 
      max_score: 100, 
      assessment: { 
        id: 3, 
        title: 'Fungsi Kuadrat', 
        has_rubric: false, 
        rubric_id: null, 
        submission_required: false,
        submissions: {} 
      }, 
      scores: {} 
    },
    { 
      id: 104, 
      name: 'Remidi 2', 
      code: 'R2', 
      parentCode: '2', 
      column_type: 'SCORE', 
      category_name: 'Ulangan Harian', 
      max_score: 100, 
      assessment: null, // Kolom belum ada tugas (cannot be selected)
      scores: {} 
    },
    { 
      id: 105, 
      name: 'Rata-rata Ulangan Harian', 
      code: 'RUH', 
      column_type: 'SUMMARY', 
      category_name: 'Ulangan Harian', 
      max_score: 100, 
      assessment: null, 
      scores: {} 
    },
    { 
      id: 106, 
      name: 'Tugas 1', 
      code: 'T1', 
      column_type: 'SCORE', 
      category_name: 'Tugas', 
      max_score: 100, 
      assessment: { 
        id: 4, 
        title: 'Tugas 1: Persamaan Linear', 
        has_rubric: true, 
        rubric_id: 1, 
        submission_required: true,
        submissions: {
          1: { 
            id: 101, 
            status: 'SUBMITTED', 
            status_label: 'Tepat Waktu', 
            content: 'Berikut adalah berkas pengerjaan tugas persamaan linear dengan langkah penyelesaian lengkap.', 
            submitted_at: '28 Sep 2026, 14:30', 
            late_minutes: 0, 
            media: [{ id: 1, name: 'Tugas_Persamaan_Linear_Ahyar.pdf', path: '#', size: 1024000 }] 
          },
          2: { 
            id: 102, 
            status: 'LATE', 
            status_label: 'Terlambat 1 Hari', 
            content: 'Mohon maaf terlambat mengumpulkan karena kendala teknis jaringan saat pengerjaan.', 
            submitted_at: '29 Sep 2026, 09:15', 
            late_minutes: 1440, 
            media: [{ id: 2, name: 'Latihan_Math_Amalia.pdf', path: '#', size: 845000 }] 
          }
        } 
      }, 
      scores: {} 
    },
    { 
      id: 107, 
      name: 'Tugas 2', 
      code: 'T2', 
      column_type: 'SCORE', 
      category_name: 'Tugas', 
      max_score: 100, 
      assessment: { 
        id: 5, 
        title: 'Tugas 2: Matriks', 
        has_rubric: false, 
        rubric_id: null, 
        submission_required: true,
        submissions: {
          1: { 
            id: 201, 
            status: 'SUBMITTED', 
            status_label: 'Tepat Waktu', 
            content: 'Pengerjaan tugas operasi matriks sudah selesai pak, berikut file lampiran excel.', 
            submitted_at: '30 Sep 2026, 11:00', 
            late_minutes: 0, 
            media: [{ id: 3, name: 'Tugas_Matriks_Ahyar.xlsx', path: '#', size: 512000 }] 
          }
        } 
      }, 
      scores: {} 
    },
    { 
      id: 108, 
      name: 'Tugas 3', 
      code: 'T3', 
      column_type: 'SCORE', 
      category_name: 'Tugas', 
      max_score: 100, 
      assessment: null, // Kolom belum ada tugas (cannot be selected)
      scores: {} 
    },
    { 
      id: 109, 
      name: 'Rata-rata Tugas', 
      code: 'RTG', 
      column_type: 'SUMMARY', 
      category_name: 'Tugas', 
      max_score: 100, 
      assessment: null, 
      scores: {} 
    },
    { 
      id: 110, 
      name: 'Mid Semester', 
      code: 'MID', 
      column_type: 'SCORE', 
      category_name: 'Nilai', 
      max_score: 100, 
      assessment: { 
        id: 6, 
        title: 'Ujian Tengah Semester', 
        has_rubric: false, 
        rubric_id: null, 
        submission_required: false,
        submissions: {} 
      }, 
      scores: {} 
    },
    { 
      id: 111, 
      name: 'Semester', 
      code: 'SEM', 
      column_type: 'SCORE', 
      category_name: 'Nilai', 
      max_score: 100, 
      assessment: null, // Kolom belum ada tugas (cannot be selected)
      scores: {} 
    },
    { 
      id: 112, 
      name: 'Nilai Akhir Siswa', 
      code: 'NSB', 
      column_type: 'SUMMARY', 
      category_name: 'Nilai', 
      max_score: 100, 
      assessment: null, 
      scores: {} 
    }
  ];

  window.__DEFAULT_STUDENTS_NILAI__ = [
    { id: 1, name: 'Ahyar Rosadi' },
    { id: 2, name: 'Amalia Lestari' },
    { id: 3, name: 'Baiq Sepnita' },
    { id: 4, name: 'Dani Ansari' },
    { id: 5, name: 'Eka Hirmayani Agustina' },
    { id: 6, name: 'Ahmad Fajar' },
    { id: 7, name: 'Siti Nurhaliza' },
    { id: 8, name: 'Fajar Nugroho' }
  ];

  // Default Rubric Criteria
  window.__DEFAULT_RUBRIC_CRITERIA__ = [
    { id: 'c1', criterion: 'Dijabarkan Cara Pengerjaannya', description: 'Langkah dan alur penyelesaian sistematis', max_points: 40, default_val: 0 },
    { id: 'c2', criterion: 'Jawaban Benar', description: 'Hasil akhir akurat sesuai kunci jawaban', max_points: 40, default_val: 0 },
    { id: 'c3', criterion: 'Kejujuran', description: 'Integritas pengerjaan mandiri tanpa kecurangan', max_points: 20, default_val: 0 }
  ];
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#nilaiTabs button");
  const panels = document.querySelectorAll('.step-panel[data-panel]');
  let currentClassData = null;
  let currentGradebookData = null;
  let selectedColId = null;
  let selectedStudentId = null;

  function goToStep(step) {
    tabs.forEach(t => t.classList.toggle("active", t.dataset.step === String(step)));
    panels.forEach(p => p.classList.toggle("active", p.dataset.panel === String(step)));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  window.nilaiGoToStep = function(step) {
    if (step === 1) {
      goToStep(1);
    } else if (step === 2 && currentClassData) {
      goToStep(2);
    } else if (step === 3 && currentGradebookData) {
      goToStep(3);
    }
  };

  tabs.forEach(t => {
    t.addEventListener("click", () => {
      if (t.disabled) return;
      goToStep(parseInt(t.dataset.step));
    });
  });

  // Pilih Kelas dari Tab 1 -> Buka Tab 2
  function selectClass(card) {
    const id = parseInt(card.dataset.id);
    const kode = card.dataset.kode;
    const jurusan = card.dataset.jurusan;
    const siswa = card.dataset.siswa;
    const hari = card.dataset.hari;
    const semester = card.dataset.semester;
    const tahun = card.dataset.tahun;
    const mapel = card.dataset.mapel;

    const assignData = window.__ASSIGNMENTS_NILAI__.find(a => a.id === id) || {
      id: id,
      kode: kode,
      jurusan: jurusan,
      siswaCount: siswa,
      hari: hari,
      semester: semester,
      tahun: tahun,
      mapel: mapel,
      gradebooks: [
        {
          id: 1,
          title: 'Buku Nilai - ' + semester + ' ' + tahun,
          sub: mapel + ' · Ulangan Harian dan Tugas',
          buku: semester + ' ' + tahun,
          badge: mapel,
          columns: window.__DEFAULT_COLUMNS_NILAI__,
          students: window.__DEFAULT_STUDENTS_NILAI__
        }
      ]
    };

    currentClassData = assignData;

    document.getElementById("nilaiBreadcrumb2").textContent = kode;
    document.getElementById("nilaiKelasTitle").textContent = "Kelas " + kode;
    document.getElementById("nilaiKelasSub").textContent = jurusan + " · " + siswa + " Siswa";
    document.getElementById("nilaiKelasBadge").textContent = semester + " · " + tahun;

    // Render list buku nilai di Tab 2
    const container = document.getElementById("nilaiBukuNilaiContainer");
    container.innerHTML = "";

    const gradebooks = (assignData.gradebooks && assignData.gradebooks.length > 0) ? assignData.gradebooks : [
      {
        id: 1,
        title: 'Buku Nilai - ' + semester + ' ' + tahun,
        sub: mapel + ' · Ulangan Harian dan Tugas',
        buku: semester + ' ' + tahun,
        badge: mapel,
        columns: window.__DEFAULT_COLUMNS_NILAI__,
        students: window.__DEFAULT_STUDENTS_NILAI__
      }
    ];

    gradebooks.forEach((gb) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "flex items-center justify-between gap-3 rounded-xl border border-bluelight px-4 py-3 hover:border-blueprim transition-colors text-left w-full cursor-pointer bg-white group";
      btn.innerHTML = `
        <div class="flex items-center gap-3 min-w-0">
          <div class="crud-card__icon group-hover:bg-bluelight transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          </div>
          <div class="min-w-0">
            <div class="font-semibold text-sm text-bluedark truncate">${escapeHtml(gb.title)}</div>
            <div class="text-[11px] text-bluedark/50 truncate">${escapeHtml(gb.sub)}</div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="badge badge-blue">${escapeHtml(gb.badge || mapel)}</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft group-hover:text-blueprim transition-colors"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      `;

      btn.addEventListener("click", () => {
        selectGradebook(gb);
      });

      container.appendChild(btn);
    });

    tabs[1].disabled = false;
    goToStep(2);
  }

  // Pilih Buku Nilai dari Tab 2 -> Buka Tab 3
  function selectGradebook(gb, initialColId = null, initialStudentId = null) {
    currentGradebookData = gb;
    const kode = currentClassData ? currentClassData.kode : 'XII RA';
    const mapel = currentClassData ? currentClassData.mapel : 'Matematika';

    document.getElementById("nilaiBreadcrumb3Kelas").textContent = kode;
    document.getElementById("nilaiBreadcrumb3").textContent = gb.buku || gb.title;
    document.getElementById("nilaiKelasName3").textContent = kode;
    document.getElementById("nilaiSub3").textContent = mapel + " - " + (gb.buku || gb.title);

    // Form hidden fields
    document.getElementById("formAssignmentId").value = currentClassData.id;
    document.getElementById("formGradebookId").value = gb.id;

    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : window.__DEFAULT_COLUMNS_NILAI__;
    currentGradebookData.columns = columns;

    const students = (gb.students && gb.students.length > 0) ? gb.students : window.__DEFAULT_STUDENTS_NILAI__;
    currentGradebookData.students = students;

    // Populate #selectKolomPenilaian (disable columns without task)
    const selectKolom = document.getElementById("selectKolomPenilaian");
    selectKolom.innerHTML = "";
    const scoreCols = columns.filter(c => c.column_type !== 'SUMMARY');
    scoreCols.forEach(c => {
      const opt = document.createElement("option");
      opt.value = c.id;
      const hasTask = Boolean(c.assessment);
      if (!hasTask) {
        opt.disabled = true;
        opt.textContent = `${c.name} ${c.code ? '(' + c.code + ')' : ''} [Belum Ada Tugas]`;
      } else {
        const hasRubric = Boolean(c.assessment && c.assessment.rubric_id);
        opt.textContent = `${c.name} ${c.code ? '(' + c.code + ')' : ''} ${hasRubric ? '[Rubrik]' : '[Nilai Langsung]'}`;
      }
      selectKolom.appendChild(opt);
    });

    // Populate #selectSiswaPenilaian
    const selectSiswa = document.getElementById("selectSiswaPenilaian");
    selectSiswa.innerHTML = "";
    students.forEach(st => {
      const opt = document.createElement("option");
      opt.value = st.id;
      opt.textContent = st.name;
      selectSiswa.appendChild(opt);
    });

    // Render Quick Column Selector Pills
    renderQuickColumnSelector(gb);

    // Render Spreadsheet Table
    renderSpreadsheet(gb);

    // Determine default selected student and column (only columns that have an assessment)
    const selectableCols = scoreCols.filter(c => Boolean(c.assessment));
    const defaultColId = (initialColId && selectableCols.some(c => c.id === initialColId)) 
      ? initialColId 
      : (selectableCols.length > 0 ? selectableCols[0].id : (scoreCols.length > 0 ? scoreCols[0].id : columns[0].id));

    const defaultStudentId = (initialStudentId && students.some(s => s.id === initialStudentId))
      ? initialStudentId
      : (students.length > 0 ? students[0].id : 1);

    selectCell(defaultStudentId, defaultColId);

    tabs[2].disabled = false;
    goToStep(3);
  }

  // Render Pill Pilihan Kolom Cepat (kolom tanpa tugas di-disable)
  function renderQuickColumnSelector(gb) {
    const container = document.getElementById("quickColumnSelector");
    if (!container) return;
    container.innerHTML = "";

    const columns = gb.columns || window.__DEFAULT_COLUMNS_NILAI__;
    const scoreCols = columns.filter(c => c.column_type !== 'SUMMARY');

    scoreCols.forEach(col => {
      const btn = document.createElement("button");
      btn.type = "button";
      const hasTask = Boolean(col.assessment);
      const hasRubric = Boolean(col.assessment && col.assessment.rubric_id);
      const isSelected = col.id === selectedColId;

      if (!hasTask) {
        btn.disabled = true;
        btn.className = "quick-col-btn px-2.5 py-1.5 rounded-lg text-xs font-medium border border-dashed border-slate-200 bg-slate-100/70 text-slate-400 cursor-not-allowed flex items-center gap-1.5 shrink-0 opacity-60";
        btn.title = "Belum ada tugas pada kolom ini (tidak dapat dipilih)";
        btn.innerHTML = `
          <span class="w-2 h-2 rounded-full bg-slate-300 inline-block shrink-0"></span>
          <span class="quick-col-name text-slate-500">${escapeHtml(col.code || col.name)}</span>
          <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-500 font-normal">Belum Ada Tugas</span>
        `;
      } else {
        btn.className = `quick-col-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer flex items-center gap-1.5 shrink-0 ${isSelected ? 'active-quick-btn' : 'bg-white text-bluedark/80 border-bluelight hover:border-blueprim hover:bg-blue-50/50'}`;
        btn.dataset.colId = col.id;

        let badgeHtml = '';
        if (hasRubric) {
          badgeHtml = '<span class="quick-rubric-badge text-[9px] px-1.5 py-0.5 rounded font-semibold">Rubrik</span>';
        } else {
          badgeHtml = '<span class="quick-task-badge text-[9px] px-1.5 py-0.5 rounded font-semibold">Nilai Langsung</span>';
        }

        btn.innerHTML = `
          <span class="w-2 h-2 rounded-full ${isSelected ? 'bg-white' : 'bg-blueprim'} inline-block shrink-0"></span>
          <span class="quick-col-name font-medium">${escapeHtml(col.code || col.name)}</span>
          ${badgeHtml}
        `;

        btn.addEventListener("click", () => {
          selectColumn(col.id);
        });
      }

      container.appendChild(btn);
    });
  }

  // Render Spreadsheet Table with Nested Category Headers
  function renderSpreadsheet(gb) {
    const columns = gb.columns || window.__DEFAULT_COLUMNS_NILAI__;
    const students = gb.students || window.__DEFAULT_STUDENTS_NILAI__;
    const thead = document.getElementById("nilaiTableHead");
    const tbody = document.getElementById("nilaiTableBody");

    const hasParentGrouping = columns.some(c => c.parentCode);

    if (hasParentGrouping) {
      // 3-Level Grouping exactly matching media_1790821692377.png
      let row1 = '<tr style="background:#0D47A1 !important; color:#ffffff !important;">';
      row1 += '<th rowspan="3" class="w-10 text-center py-2 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="background:#1565C0 !important; color:#ffffff !important;">No</th>';
      row1 += '<th rowspan="3" class="min-w-[160px] py-2 px-3 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="background:#1565C0 !important; color:#ffffff !important;">Nama Siswa</th>';
      row1 += '<th colspan="5" class="text-center py-1.5 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="background:#1976D2 !important; color:#ffffff !important;">Ulangan Harian</th>';
      row1 += '<th colspan="4" class="text-center py-1.5 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="background:#0288D1 !important; color:#ffffff !important;">Tugas</th>';
      row1 += '<th colspan="2" class="text-center py-1.5 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="background:#0277BD !important; color:#ffffff !important;">Nilai</th>';
      row1 += '<th rowspan="3" class="text-center py-2 px-2.5 text-[11px] font-bold border-b border-blue-800 !text-white" style="background:#0D47A1 !important; color:#ffffff !important;">NSB</th>';
      row1 += '</tr>';

      // Row 2: 1 (colspan 2), 2 (colspan 2), RUH, T1, T2, T3, RTG, MID, SEM
      let row2 = '<tr style="color:#ffffff !important;">';
      row2 += '<th colspan="2" class="text-center py-1 px-1.5 text-[11px] font-bold border-r border-b border-blue-700 !text-white" style="background:#2196F3 !important;">1</th>';
      row2 += '<th colspan="2" class="text-center py-1 px-1.5 text-[11px] font-bold border-r border-b border-blue-700 !text-white" style="background:#2196F3 !important;">2</th>';

      const ruhCol = columns.find(c => c.code === 'RUH') || { id: 105, code: 'RUH', name: 'Rata-rata Ulangan Harian', column_type: 'SUMMARY' };
      row2 += `<th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-700 !text-white" style="background:#0277BD !important;" title="Rata-rata Ulangan Harian">RUH</th>`;

      const t1Col = columns.find(c => c.code === 'T1') || { id: 106, code: 'T1', name: 'Tugas 1' };
      const t2Col = columns.find(c => c.code === 'T2') || { id: 107, code: 'T2', name: 'Tugas 2' };
      const t3Col = columns.find(c => c.code === 'T3') || { id: 108, code: 'T3', name: 'Tugas 3' };
      const rtgCol = columns.find(c => c.code === 'RTG') || { id: 109, code: 'RTG', name: 'Rata-rata Tugas', column_type: 'SUMMARY' };

      [t1Col, t2Col, t3Col].forEach(col => {
        const isSel = col.id === selectedColId;
        const hasTask = Boolean(col.assessment);
        if (!hasTask) {
          row2 += `
            <th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-600 col-disabled-header !text-white/60" 
              style="background:#0288D1 !important;"
              title="Belum ada tugas pada kolom ini (tidak dapat dipilih)">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        } else {
          row2 += `
            <th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-600 transition-all col-select-th cursor-pointer ${isSel ? 'active-col-header' : '!text-white'}" 
              data-col-id="${col.id}" 
              style="${isSel ? '' : 'background:#03A9F4 !important;'}"
              title="Klik untuk memilih kolom ${escapeHtml(col.name)}">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        }
      });
      row2 += `<th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-600 !text-white" style="background:#0277BD !important;" title="Rata-rata Tugas">RTG</th>`;

      const midCol = columns.find(c => c.code === 'MID') || { id: 110, code: 'MID', name: 'Mid Semester' };
      const semCol = columns.find(c => c.code === 'SEM') || { id: 111, code: 'SEM', name: 'Semester' };
      [midCol, semCol].forEach(col => {
        const isSel = col.id === selectedColId;
        const hasTask = Boolean(col.assessment);
        if (!hasTask) {
          row2 += `
            <th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-700 col-disabled-header !text-white/60" 
              style="background:#01579B !important;"
              title="Belum ada tugas pada kolom ini (tidak dapat dipilih)">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        } else {
          row2 += `
            <th rowspan="2" class="text-center py-1.5 px-1.5 text-[11px] font-bold border-r border-b border-blue-700 transition-all col-select-th cursor-pointer ${isSel ? 'active-col-header' : '!text-white'}" 
              data-col-id="${col.id}" 
              style="${isSel ? '' : 'background:#0288D1 !important;'}"
              title="Klik untuk memilih kolom ${escapeHtml(col.name)}">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        }
      });
      row2 += '</tr>';

      // Row 3: Subcolumns under 1 and 2 (UH 1, R1, UH 2, R2)
      const uh1Col = columns.find(c => c.code === 'UH 1') || { id: 101, code: 'UH 1', name: 'Ulangan Harian 1' };
      const r1Col = columns.find(c => c.code === 'R1') || { id: 102, code: 'R1', name: 'Remidi 1' };
      const uh2Col = columns.find(c => c.code === 'UH 2') || { id: 103, code: 'UH 2', name: 'Ulangan Harian 2' };
      const r2Col = columns.find(c => c.code === 'R2') || { id: 104, code: 'R2', name: 'Remidi 2' };

      let row3 = '<tr style="color:#ffffff !important;">';
      [uh1Col, r1Col, uh2Col, r2Col].forEach(col => {
        const isSel = col.id === selectedColId;
        const hasTask = Boolean(col.assessment);
        if (!hasTask) {
          row3 += `
            <th class="text-center py-1 px-1.5 text-[10px] font-bold border-r border-b border-blue-600 col-disabled-header !text-white/60" 
              style="background:#1565C0 !important;"
              title="Belum ada tugas pada kolom ini (tidak dapat dipilih)">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        } else {
          row3 += `
            <th class="text-center py-1 px-1.5 text-[10px] font-bold border-r border-b border-blue-600 transition-all col-select-th cursor-pointer ${isSel ? 'active-col-header' : '!text-white'}" 
              data-col-id="${col.id}" 
              style="${isSel ? '' : 'background:#1E88E5 !important;'}"
              title="Klik untuk memilih kolom ${escapeHtml(col.name)}">
              ${escapeHtml(col.code || col.name)}
            </th>
          `;
        }
      });
      row3 += '</tr>';

      thead.innerHTML = row1 + row2 + row3;
    } else {
      // Group columns by category_name (Standard 2-Level)
      const categoryGroups = [];
      columns.forEach(col => {
        const catName = col.category_name || 'Nilai';
        let group = categoryGroups.find(g => g.name === catName);
        if (!group) {
          group = { name: catName, columns: [] };
          categoryGroups.push(group);
        }
        group.columns.push(col);
      });

      // Row 1: Category Groups
      let row1 = '<tr style="background:#0D47A1 !important; color:#ffffff !important;">';
      row1 += '<th rowspan="2" class="w-10 text-center py-2 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="color:#ffffff !important;">NO</th>';
      row1 += '<th rowspan="2" class="min-w-[170px] py-2 px-3 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="color:#ffffff !important;">NAMA SISWA</th>';

      categoryGroups.forEach(grp => {
        row1 += `<th colspan="${grp.columns.length}" class="text-center py-1.5 px-2 text-[11px] font-bold uppercase tracking-wider border-r border-b border-blue-800 !text-white" style="background:#0D47A1 !important; color:#ffffff !important;">${escapeHtml(grp.name)}</th>`;
      });
      row1 += '</tr>';

      // Row 2: Subheaders for individual columns
      let row2 = '<tr class="bg-blue-50/90 text-bluedark border-b border-bluelight">';
      columns.forEach(col => {
        const isSelected = col.id === selectedColId;
        const isSummary = col.column_type === 'SUMMARY';
        const hasTask = Boolean(col.assessment);

        if (isSummary) {
          row2 += `
            <th class="text-center py-2 px-1.5 text-[11px] font-bold border-r border-bluelight transition-all bg-slate-100/70 text-slate-600" 
              title="Kolom kalkulasi otomatis">
              <div class="leading-tight">
                <span>${escapeHtml(col.code || col.name)}</span>
              </div>
            </th>
          `;
        } else if (!hasTask) {
          row2 += `
            <th class="text-center py-2 px-1.5 text-[11px] font-bold border-r border-bluelight transition-all col-disabled-header bg-slate-100 text-slate-400" 
              title="Belum ada tugas pada kolom ini (tidak dapat dipilih)">
              <div class="leading-tight">
                <span>${escapeHtml(col.code || col.name)}</span>
                <span class="block text-[9px] font-normal text-slate-400">Kosong</span>
              </div>
            </th>
          `;
        } else {
          row2 += `
            <th class="text-center py-2 px-1.5 text-[11px] font-bold border-r border-bluelight transition-all col-select-th cursor-pointer ${isSelected ? 'active-col-header' : 'bg-white hover:bg-blue-50 text-blue-900'}" 
              data-col-id="${col.id}" 
              title="Klik untuk memilih kolom ${escapeHtml(col.name)}">
              <div class="leading-tight">
                <span>${escapeHtml(col.code || col.name)}</span>
                ${col.max_score ? `<span class="block text-[9px] font-normal ${isSelected ? 'text-blue-100' : 'text-blue-600'}">Maks ${col.max_score}</span>` : ''}
              </div>
            </th>
          `;
        }
      });
      row2 += '</tr>';

      thead.innerHTML = row1 + row2;
    }

    // Attach click events on valid column headers (not disabled)
    thead.querySelectorAll(".col-select-th").forEach(th => {
      th.addEventListener("click", () => {
        const colId = parseInt(th.dataset.colId);
        if (colId) selectColumn(colId);
      });
    });

    // Render Body Rows
    tbody.innerHTML = "";
    students.forEach((st, sIdx) => {
      const tr = document.createElement("tr");
      tr.className = `border-b border-bluelight/70 transition-colors student-row ${st.id === selectedStudentId ? 'active-row-student' : 'hover:bg-blue-50/30'}`;
      tr.dataset.studentId = st.id;

      let rowHtml = `
        <td class="text-center py-2 px-2 text-slate-500 font-semibold border-r border-bluelight/60 text-xs">${sIdx + 1}</td>
        <td class="py-2 px-3 font-semibold text-bluedark border-r border-bluelight/60 cursor-pointer hover:text-blueprim select-none student-name-cell" data-student-id="${st.id}">
          ${escapeHtml(st.name)}
        </td>
      `;

      columns.forEach(col => {
        const isSelectedCell = (col.id === selectedColId && st.id === selectedStudentId);
        const isSelectedCol = (col.id === selectedColId);
        const isSummary = col.column_type === 'SUMMARY';
        const hasTask = Boolean(col.assessment);
        const hasRubric = Boolean(col.assessment && col.assessment.rubric_id);

        // Retrieve existing score for this student in this column
        const colScores = col.scores || {};
        const studentScoreData = colScores[st.id] || null;
        let displayScore = '-';
        if (studentScoreData && studentScoreData.score !== undefined && studentScoreData.score !== null) {
          displayScore = studentScoreData.score;
        }

        if (isSummary) {
          rowHtml += `
            <td class="text-center py-2 px-2 border-r border-bluelight/60 bg-slate-50/80 font-mono font-bold text-xs ${col.code === 'NSB' ? 'text-emerald-600' : 'text-blueprim'} summary-cell" 
              data-student-id="${st.id}" data-col-code="${col.code}">
              <span class="summary-val">${displayScore}</span>
            </td>
          `;
        } else if (!hasTask) {
          // Kolom belum ada tugas: Tidak bisa dipilih dan tidak bisa diisi nilai
          rowHtml += `
            <td class="text-center py-2 px-1.5 border-r border-bluelight/60 cell-disabled-task" 
              title="Kolom ${escapeHtml(col.name)} belum memiliki tugas (tidak dapat dinilai)">
              <span class="text-slate-300 font-mono text-xs select-none">-</span>
            </td>
          `;
        } else if (hasRubric) {
          // Tugas pakai rubrik: Nilai TIDAK BISA dimasukkan langsung dari cell itu!
          rowHtml += `
            <td class="text-center py-1 px-1.5 border-r border-bluelight/60 score-cell-interactive ${isSelectedCell ? 'active-score-cell' : ''} ${isSelectedCol && !isSelectedCell ? 'active-col-cell' : ''}" 
              data-student-id="${st.id}" 
              data-col-id="${col.id}"
              data-has-rubric="true"
              data-score="${displayScore !== '-' ? displayScore : ''}"
              title="Tugas menggunakan rubrik. Klik untuk membuka rubrik penilaian ${escapeHtml(st.name)}">
              <div class="inline-flex items-center justify-center gap-1">
                <span class="cell-score-pill ${displayScore === '-' ? 'cell-score-empty' : ''}">
                  ${displayScore}
                </span>
                <span class="badge-rubric-cell" title="Menggunakan Rubrik Penilaian">Rubrik</span>
              </div>
            </td>
          `;
        } else {
          // Tugas TIDAK pakai rubrik: Nilai BISA LANGSUNG dimasukkan dalam cell-cell tugasnya!
          rowHtml += `
            <td class="text-center py-1 px-1 border-r border-bluelight/60 score-cell-interactive ${isSelectedCell ? 'active-score-cell' : ''} ${isSelectedCol && !isSelectedCell ? 'active-col-cell' : ''}" 
              data-student-id="${st.id}" 
              data-col-id="${col.id}"
              data-has-rubric="false"
              data-score="${displayScore !== '-' ? displayScore : ''}">
              <input type="number" 
                class="cell-score-direct-input" 
                value="${displayScore !== '-' ? displayScore : ''}" 
                placeholder="-" 
                min="0" 
                max="${col.max_score || 100}" 
                step="any" 
                data-student-id="${st.id}" 
                data-col-id="${col.id}"
                data-max="${col.max_score || 100}"
                title="Ketik nilai langsung untuk ${escapeHtml(st.name)} (Maks: ${col.max_score || 100})">
            </td>
          `;
        }
      });

      tr.innerHTML = rowHtml;
      tbody.appendChild(tr);
    });

    // Attach click listener to Student Names
    tbody.querySelectorAll(".student-name-cell").forEach(cell => {
      cell.addEventListener("click", () => {
        const stId = parseInt(cell.dataset.studentId);
        if (stId && selectedColId) {
          selectCell(stId, selectedColId);
        }
      });
    });

    // Attach click listener to Rubric Score Cells
    tbody.querySelectorAll(".score-cell-interactive[data-has-rubric='true']").forEach(cell => {
      cell.addEventListener("click", () => {
        const stId = parseInt(cell.dataset.studentId);
        const colId = parseInt(cell.dataset.colId);
        if (stId && colId) {
          selectCell(stId, colId);
        }
      });
    });

    // Attach listeners to Direct Score Cells and Inputs
    tbody.querySelectorAll(".score-cell-interactive[data-has-rubric='false']").forEach(cell => {
      cell.addEventListener("click", (e) => {
        if (e.target.tagName.toLowerCase() === 'input') return;
        const stId = parseInt(cell.dataset.studentId);
        const colId = parseInt(cell.dataset.colId);
        if (stId && colId) {
          selectCell(stId, colId, false);
          const inp = cell.querySelector(".cell-score-direct-input");
          if (inp) inp.focus();
        }
      });
    });

    tbody.querySelectorAll(".cell-score-direct-input").forEach(inp => {
      inp.addEventListener("focus", () => {
        const stId = parseInt(inp.dataset.studentId);
        const colId = parseInt(inp.dataset.colId);
        if (stId && colId) {
          selectCell(stId, colId, false);
        }
      });

      // Cegah scroll mouse mengubah nilai angka secara tidak sengaja
      inp.addEventListener("wheel", (e) => {
        e.preventDefault();
      }, { passive: false });

      inp.addEventListener("input", () => {
        const stId = parseInt(inp.dataset.studentId);
        const colId = parseInt(inp.dataset.colId);
        const maxAllowed = parseFloat(inp.dataset.max) || 100;
        let val = parseFloat(inp.value);
        if (!isNaN(val)) {
          if (val > maxAllowed) {
            val = maxAllowed;
            inp.value = maxAllowed;
          }
          if (val < 0) {
            val = 0;
            inp.value = 0;
          }
        }

        const col = currentGradebookData?.columns?.find(c => c.id === colId);
        if (col) {
          if (!col.scores) col.scores = {};
          if (!col.scores[stId]) col.scores[stId] = {};
          col.scores[stId].score = !isNaN(val) ? val : null;
        }

        if (selectedStudentId === stId && selectedColId === colId) {
          const formDirect = document.getElementById("inputDirectScore");
          const formTotal = document.getElementById("inputTotalScore");
          const bannerBadge = document.getElementById("bannerScoreBadge");
          if (formDirect) formDirect.value = !isNaN(val) ? val : '';
          if (formTotal) formTotal.value = !isNaN(val) ? val : 0;
          if (bannerBadge) bannerBadge.textContent = !isNaN(val) ? `Nilai: ${val}` : 'Nilai: Belum Dinilai';
        }

        recalculateSummaries(currentGradebookData);
      });

      inp.addEventListener("blur", () => {
        const stId = parseInt(inp.dataset.studentId);
        const colId = parseInt(inp.dataset.colId);
        if (inp.value !== '') {
          saveDirectScoreAjax(stId, colId, inp.value, inp);
        }
      });

      inp.addEventListener("keydown", (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          inp.blur();
        }
      });
    });

    // Recalculate summary columns (RUH, RTG, NSB) dynamically
    recalculateSummaries(gb);
  }

  // Auto save direct score via AJAX on cell blur or Enter key
  function saveDirectScoreAjax(studentId, colId, scoreVal, cellInput) {
    if (!currentGradebookData) return;
    const columns = currentGradebookData.columns || [];
    const students = currentGradebookData.students || [];

    const col = columns.find(c => c.id === colId);
    const st = students.find(s => s.id === studentId);
    if (!col || !st || !col.assessment) return;

    const maxAllowed = col.max_score || 100;
    let numericScore = parseFloat(scoreVal);
    if (isNaN(numericScore)) {
      numericScore = 0;
    }
    numericScore = Math.min(maxAllowed, Math.max(0, numericScore));
    numericScore = parseFloat(numericScore.toFixed(2));

    // Update memory
    if (!col.scores) col.scores = {};
    if (!col.scores[studentId]) col.scores[studentId] = {};
    col.scores[studentId].score = numericScore;

    // Recalculate summaries
    recalculateSummaries(currentGradebookData);

    // If currently selected cell, update form inputs as well
    if (selectedStudentId === studentId && selectedColId === colId) {
      const inputDirect = document.getElementById("inputDirectScore");
      const inputTotal = document.getElementById("inputTotalScore");
      const bannerBadge = document.getElementById("bannerScoreBadge");
      if (inputDirect && inputDirect.value != numericScore) inputDirect.value = numericScore;
      if (inputTotal) inputTotal.value = numericScore;
      if (bannerBadge) bannerBadge.textContent = `Nilai: ${numericScore}`;
    }

    // Send AJAX to backend
    const formData = new FormData();
    formData.append("gradebook_column_id", col.id);
    formData.append("student_id", studentId);
    formData.append("score", numericScore);

    fetch("{{ route('teacher.grading.store') }}", {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      },
      body: formData
    })
    .then(res => {
      if (!res.ok) throw new Error("Gagal menyimpan");
      return res.json();
    })
    .then(data => {
      if (cellInput) {
        cellInput.classList.add("border-emerald-500", "bg-emerald-50");
        setTimeout(() => {
          cellInput.classList.remove("border-emerald-500", "bg-emerald-50");
        }, 1200);
      }
      showToast(`Nilai ${st.name.split(' ')[0]} tersimpan (${numericScore})`);
    })
    .catch(err => {
      console.warn("Direct score saved locally:", err);
    });
  }

  // Recalculate RUH, RTG, NSB
  function recalculateSummaries(gb) {
    const columns = gb.columns || [];
    const students = gb.students || [];

    const uhCols = columns.filter(c => c.column_type === 'SCORE' && (c.category_name === 'Ulangan Harian' || (c.code || '').includes('UH')));
    const tgCols = columns.filter(c => c.column_type === 'SCORE' && (c.category_name === 'Tugas' || (c.code || '').startsWith('T')));
    const midCol = columns.find(c => (c.code || '').toUpperCase() === 'MID');
    const semCol = columns.find(c => (c.code || '').toUpperCase() === 'SEM');

    students.forEach(st => {
      // Calculate RUH
      let ruhTotal = 0, ruhCount = 0;
      uhCols.forEach(col => {
        const s = col.scores && col.scores[st.id] ? parseFloat(col.scores[st.id].score) : null;
        if (s !== null && !isNaN(s)) {
          ruhTotal += s;
          ruhCount++;
        }
      });
      const ruhVal = ruhCount > 0 ? (ruhTotal / ruhCount).toFixed(1) : '-';

      // Calculate RTG
      let rtgTotal = 0, rtgCount = 0;
      tgCols.forEach(col => {
        const s = col.scores && col.scores[st.id] ? parseFloat(col.scores[st.id].score) : null;
        if (s !== null && !isNaN(s)) {
          rtgTotal += s;
          rtgCount++;
        }
      });
      const rtgVal = rtgCount > 0 ? (rtgTotal / rtgCount).toFixed(1) : '-';

      // Mid & Sem
      const midVal = (midCol && midCol.scores && midCol.scores[st.id]) ? parseFloat(midCol.scores[st.id].score) : null;
      const semVal = (semCol && semCol.scores && semCol.scores[st.id]) ? parseFloat(semCol.scores[st.id].score) : null;

      // Calculate NSB (Overall Final Score)
      let nsbParts = [];
      if (ruhVal !== '-') nsbParts.push(parseFloat(ruhVal));
      if (rtgVal !== '-') nsbParts.push(parseFloat(rtgVal));
      if (midVal !== null && !isNaN(midVal)) nsbParts.push(midVal);
      if (semVal !== null && !isNaN(semVal)) nsbParts.push(semVal);

      const nsbVal = nsbParts.length > 0 
        ? (nsbParts.reduce((a, b) => a + b, 0) / nsbParts.length).toFixed(1) 
        : '-';

      // Update DOM summary cells for this student
      const tr = document.querySelector(`.student-row[data-student-id="${st.id}"]`);
      if (tr) {
        const ruhCell = tr.querySelector('.summary-cell[data-col-code="RUH"] .summary-val');
        if (ruhCell) ruhCell.textContent = ruhVal;

        const rtgCell = tr.querySelector('.summary-cell[data-col-code="RTG"] .summary-val');
        if (rtgCell) rtgCell.textContent = rtgVal;

        const nsbCell = tr.querySelector('.summary-cell[data-col-code="NSB"] .summary-val');
        if (nsbCell) nsbCell.textContent = nsbVal;
      }
    });
  }

  // Pilih Kolom
  function selectColumn(colId) {
    if (!currentGradebookData) return;
    const col = (currentGradebookData.columns || []).find(c => c.id === colId);
    if (!col || !col.assessment) {
      showToast("Kolom ini belum memiliki tugas dan tidak dapat dinilai.");
      return;
    }
    const studentId = selectedStudentId || (currentGradebookData.students[0]?.id ?? 1);
    selectCell(studentId, colId);
  }

  // Pilih Sel Siswa & Kolom Spesifik
  function selectCell(studentId, colId, focusDirectInput = false) {
    if (!currentGradebookData) return;

    const columns = currentGradebookData.columns || window.__DEFAULT_COLUMNS_NILAI__;
    const students = currentGradebookData.students || window.__DEFAULT_STUDENTS_NILAI__;

    const col = columns.find(c => c.id === colId);
    const st = students.find(s => s.id === studentId);

    if (!col || !st) return;

    // Guard: Kolom yang belum diberikan tugas TIDAK BISA DIPILIH
    if (!col.assessment) {
      showToast("Kolom ini belum memiliki tugas dan tidak dapat dinilai.");
      return;
    }

    selectedStudentId = studentId;
    selectedColId = colId;

    const hasRubric = Boolean(col.assessment && col.assessment.rubric_id);

    // 1. Highlight Quick Column Selector Pill
    document.querySelectorAll("#quickColumnSelector .quick-col-btn").forEach(btn => {
      const isAct = parseInt(btn.dataset.colId) === col.id;
      btn.classList.toggle("active-quick-btn", isAct);
      const dot = btn.querySelector("span:first-child");
      if (dot && !btn.disabled) {
        dot.className = `w-2 h-2 rounded-full ${isAct ? 'bg-white' : 'bg-blueprim'} inline-block shrink-0`;
      }
      if (isAct) {
        btn.classList.remove("bg-white", "text-bluedark/80", "border-bluelight");
      } else if (!btn.disabled) {
        btn.classList.add("bg-white", "text-bluedark/80", "border-bluelight");
      }
    });

    // 2. Highlight Table Column Headers
    document.querySelectorAll("#nilaiTableHead .col-select-th").forEach(th => {
      const isAct = parseInt(th.dataset.colId) === col.id;
      th.classList.toggle("active-col-header", isAct);
      const spanSub = th.querySelector("span:last-child");
      if (isAct) {
        th.classList.remove("bg-white", "text-blue-900");
        if (spanSub) {
          spanSub.classList.remove("text-blue-600");
          spanSub.classList.add("text-blue-100");
        }
      } else {
        th.classList.add("bg-white", "text-blue-900");
        if (spanSub) {
          spanSub.classList.add("text-blue-600");
          spanSub.classList.remove("text-blue-100");
        }
      }
    });

    // 3. Highlight Student Rows and Cell Highlights
    document.querySelectorAll("#nilaiTableBody .student-row").forEach(row => {
      const isActRow = parseInt(row.dataset.studentId) === st.id;
      row.classList.toggle("active-row-student", isActRow);
    });

    document.querySelectorAll("#nilaiTableBody .score-cell-interactive").forEach(cell => {
      const cColId = parseInt(cell.dataset.colId);
      const cStId = parseInt(cell.dataset.studentId);
      const isActCell = (cColId === col.id && cStId === st.id);
      const isColMatch = (cColId === col.id);

      cell.classList.toggle("active-score-cell", isActCell);
      cell.classList.toggle("active-col-cell", isColMatch && !isActCell);
    });

    // 4. Update Form Dropdown Inputs
    const selectKolom = document.getElementById("selectKolomPenilaian");
    if (selectKolom && selectKolom.value != col.id) {
      selectKolom.value = col.id;
    }

    const selectSiswa = document.getElementById("selectSiswaPenilaian");
    if (selectSiswa && selectSiswa.value != st.id) {
      selectSiswa.value = st.id;
    }

    // 5. Update Judul Tugas Input
    const inputJudul = document.getElementById("inputJudulTugas");
    if (inputJudul) {
      if (col.assessment && col.assessment.title) {
        inputJudul.value = col.assessment.title;
      } else {
        inputJudul.value = `${col.name}${col.code ? ' (' + col.code + ')' : ''}`;
      }
    }

    // 6. Get Existing Score Data for this student in this column
    const colScores = col.scores || {};
    const existingScoreData = colScores[st.id] || null;
    const currentScore = existingScoreData ? parseFloat(existingScoreData.score) : null;
    const existingFeedback = existingScoreData ? (existingScoreData.feedback || '') : '';

    // 7. Update Active Selection Banner
    document.getElementById("bannerStudentName").textContent = st.name;
    document.getElementById("bannerColumnName").textContent = `${col.name} (${col.code || '-'})`;
    document.getElementById("bannerScoreBadge").textContent = (currentScore !== null && !isNaN(currentScore)) 
      ? `Nilai: ${currentScore}` 
      : 'Nilai: Belum Dinilai';

    // 8. Update Feedback Input
    const feedbackInput = document.getElementById("inputFeedback");
    if (feedbackInput) {
      feedbackInput.value = existingFeedback;
    }

    // 9. Toggle Rubrik Penilaian Table vs Direct Score Section
    const rubrikSection = document.getElementById("rubrikPenilaianSection");
    const directSection = document.getElementById("directScoreSection");

    if (hasRubric) {
      rubrikSection.classList.remove("hidden");
      directSection.classList.add("hidden");
      renderRubricScoringForm(col, st, existingScoreData);
    } else {
      rubrikSection.classList.add("hidden");
      directSection.classList.remove("hidden");

      const labelMax = document.getElementById("labelDirectMaxScore");
      const inputDirect = document.getElementById("inputDirectScore");
      const inputTotal = document.getElementById("inputTotalScore");

      const maxScoreVal = col.max_score || 100;
      if (labelMax) labelMax.textContent = maxScoreVal;
      if (inputDirect) {
        inputDirect.max = maxScoreVal;
        inputDirect.value = (currentScore !== null && !isNaN(currentScore)) ? currentScore : '';
        if (focusDirectInput) {
          inputDirect.focus();
        }
      }
      if (inputTotal) {
        inputTotal.value = (currentScore !== null && !isNaN(currentScore)) ? currentScore : 0;
      }
    }

    // 10. Periksa Bukti Pengiriman (Jika tugas diperlukan pengiriman bukti)
    renderSubmissionProofSection(col, st);

    // 11. Update Submit Button Label
    const submitBtnLabel = document.getElementById("btnSubmitNilaiLabel");
    if (submitBtnLabel) {
      submitBtnLabel.textContent = `Simpan Nilai ${st.name.split(' ')[0]}`;
    }
  }

  // Render Kriteria Rubrik Form
  function renderRubricScoringForm(col, st, existingScoreData) {
    const tbody = document.getElementById("rubrikTableBody");
    const rubrikBadge = document.getElementById("rubrikInfoBadge");

    // Check if column has custom rubric
    let criteriaList = [];
    let rubricId = col.assessment ? col.assessment.rubric_id : null;

    if (rubricId && window.__RUBRICS_DATA__ && window.__RUBRICS_DATA__[rubricId]) {
      const rub = window.__RUBRICS_DATA__[rubricId];
      criteriaList = rub.criteria || [];
      if (rubrikBadge) {
        rubrikBadge.textContent = `Menggunakan rubrik: ${rub.name} (${rub.total_points} Poin)`;
      }
    } else {
      // Use standard criteria from prototype mockup
      criteriaList = window.__DEFAULT_RUBRIC_CRITERIA__;
      if (rubrikBadge) {
        rubrikBadge.textContent = "Menggunakan pedoman rubrik standar penilaian";
      }
    }

    tbody.innerHTML = "";
    const existingRubricScores = existingScoreData ? (existingScoreData.rubric_scores || {}) : {};
    const existingTotalScore = existingScoreData ? parseFloat(existingScoreData.score) : null;

    criteriaList.forEach((crit) => {
      const tr = document.createElement("tr");
      tr.className = "hover:bg-blue-50/30 transition-colors border-b border-bluelight/60";

      // Calculate initial criterion value: jika belum ada nilai, mulai dari 0 (tidak prefill 85%)
      let currentCritVal = existingRubricScores[crit.id];
      if (currentCritVal === undefined || currentCritVal === null) {
        if (existingTotalScore !== null && !isNaN(existingTotalScore)) {
          const ratio = (crit.max_points || 40) / (col.max_score || 100);
          currentCritVal = Math.round(existingTotalScore * ratio);
        } else {
          currentCritVal = 0;
        }
      }

      tr.innerHTML = `
        <td class="py-2.5 px-3">
          <div class="font-bold text-bluedark text-xs">${escapeHtml(crit.criterion)}</div>
          ${crit.description ? `<div class="text-[11px] text-bluedark/55 mt-0.5 leading-snug">${escapeHtml(crit.description)}</div>` : ''}
        </td>
        <td class="py-2.5 px-3 text-center">
          <div class="flex items-center justify-center gap-1.5">
            <input type="number" 
              name="rubric_scores[${crit.id}]" 
              class="f-input py-1 text-center font-mono font-bold text-xs w-20 rubric-point" 
              value="${currentCritVal}" 
              min="0" 
              max="${crit.max_points || 100}" 
              step="any" 
              data-max="${crit.max_points || 100}">
            <span class="text-[11px] text-bluedark/40 font-medium">/ ${crit.max_points || 100}</span>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    // Total Row
    const totalTr = document.createElement("tr");
    totalTr.style = "background:#0D47A1 !important; color:#ffffff !important;";
    totalTr.innerHTML = `
      <td class="py-2.5 px-3 font-bold text-xs !text-white" style="color:#ffffff !important;">Total</td>
      <td class="py-2.5 px-3 text-center font-bold text-sm !text-white" style="color:#ffffff !important;">
        <span id="labelTotalNilai">0</span>
      </td>
    `;
    tbody.appendChild(totalTr);

    // Attach real-time input listeners to all rubric points
    tbody.querySelectorAll(".rubric-point").forEach(inp => {
      inp.addEventListener("input", calculateRubricTotal);
      inp.addEventListener("wheel", (e) => {
        e.preventDefault();
      }, { passive: false });
    });

    // Initial total calculation
    calculateRubricTotal();
  }

  // Hitung total nilai rubrik live
  function calculateRubricTotal() {
    const inputs = document.querySelectorAll(".rubric-point");
    let total = 0;
    inputs.forEach(inp => {
      total += parseFloat(inp.value) || 0;
    });

    const col = currentGradebookData?.columns?.find(c => c.id === selectedColId);
    const maxAllowed = col ? (col.max_score || 100) : 100;

    total = Math.min(maxAllowed, Math.max(0, total));
    total = parseFloat(total.toFixed(2));

    const labelTotal = document.getElementById("labelTotalNilai");
    const inputTotal = document.getElementById("inputTotalScore");

    if (labelTotal) labelTotal.textContent = total;
    if (inputTotal) inputTotal.value = total;
  }

  // Render Bukti Pengiriman Siswa (Jika tugas memerlukan bukti pengiriman)
  function renderSubmissionProofSection(col, st) {
    const section = document.getElementById("sectionBuktiPengiriman");
    if (!section) return;

    const reqSubmission = Boolean(col.assessment && col.assessment.submission_required);
    if (!reqSubmission) {
      section.classList.add("hidden");
      return;
    }

    section.classList.remove("hidden");
    const submissions = col.assessment.submissions || {};
    const sub = submissions[st.id] || null;

    const dateText = document.getElementById("submissionDateText");
    const badgeStatus = document.getElementById("badgeStatusPengiriman");
    const detailKonten = document.getElementById("kontenDetailPengiriman");

    if (sub) {
      const isLate = sub.late_minutes > 0 || sub.status === 'LATE';
      if (dateText) {
        dateText.textContent = `Dikirim: ${sub.submitted_at || '-'}` + (isLate ? ` · Terlambat ${Math.round(sub.late_minutes / 60) || 1} jam` : ' · Tepat Waktu');
      }
      if (badgeStatus) {
        if (isLate) {
          badgeStatus.innerHTML = `<span class="badge badge-amber text-[10px] font-bold">Terlambat</span>`;
        } else {
          badgeStatus.innerHTML = `<span class="badge badge-emerald text-[10px] font-bold">Tepat Waktu</span>`;
        }
      }

      let mediaListHtml = '';
      if (sub.media && sub.media.length > 0) {
        mediaListHtml = `
          <div class="space-y-1.5 mt-2">
            <span class="text-[10px] font-bold text-bluedark/70 block uppercase tracking-wide">Lampiran Berkas Bukti:</span>
            ${sub.media.map(m => `
              <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-bluelight text-xs gap-2">
                <div class="flex items-center gap-2 truncate min-w-0">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                  <span class="font-medium text-bluedark truncate" title="${escapeHtml(m.name)}">${escapeHtml(m.name)}</span>
                  ${m.size ? `<span class="text-[10px] text-bluedark/40 shrink-0">(${Math.round(m.size / 1024)} KB)</span>` : ''}
                </div>
                <a href="${m.path || '#'}" target="_blank" class="px-2 py-1 bg-blueprim/10 hover:bg-blueprim text-blueprim hover:text-white rounded text-[11px] font-semibold transition-colors shrink-0 flex items-center gap-1">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <span>Buka</span>
                </a>
              </div>
            `).join('')}
          </div>
        `;
      }

      detailKonten.innerHTML = `
        <div class="bg-white p-2.5 rounded-lg border border-bluelight/70 text-bluedark">
          ${sub.content ? `<p class="italic text-[11px] text-bluedark/80 mb-1">"${escapeHtml(sub.content)}"</p>` : '<p class="text-[11px] text-bluedark/40 italic">Tidak ada catatan teks dari siswa.</p>'}
          ${mediaListHtml}
        </div>
        <div class="flex justify-end pt-1">
          <button type="button" class="text-[11px] font-bold text-blueprim hover:text-blue-800 flex items-center gap-1 cursor-pointer" onclick="window.openModalBukti(${st.id}, ${col.id})">
            <span>Periksa Detail Bukti Lengkap</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      `;
    } else {
      if (dateText) {
        dateText.textContent = "Belum Ada Pengiriman";
      }
      if (badgeStatus) {
        badgeStatus.innerHTML = `<span class="badge badge-amber text-[10px] font-semibold">Belum Mengumpulkan</span>`;
      }
      detailKonten.innerHTML = `
        <div class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200 text-[11px] text-amber-800 flex items-center gap-2">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Siswa <strong>${escapeHtml(st.name)}</strong> belum mengirimkan bukti atau berkas untuk tugas ini.</span>
        </div>
      `;
    }
  }

  // Modal Detail Bukti Pengiriman Siswa
  window.openModalBukti = function(studentId, colId) {
    if (!currentGradebookData) return;
    const columns = currentGradebookData.columns || [];
    const students = currentGradebookData.students || [];

    const col = columns.find(c => c.id === colId);
    const st = students.find(s => s.id === studentId);
    if (!col || !st || !col.assessment) return;

    const sub = col.assessment.submissions ? col.assessment.submissions[st.id] : null;

    const modal = document.getElementById("modalBuktiPengiriman");
    const modalBox = document.getElementById("modalBuktiBox");
    const titleEl = document.getElementById("modalBuktiTitle");
    const subTitleEl = document.getElementById("modalBuktiSubtitle");
    const bodyEl = document.getElementById("modalBuktiBody");

    titleEl.textContent = `Bukti: ${col.assessment.title || col.name}`;
    subTitleEl.textContent = `${st.name} · ${currentClassData?.kode || ''}`;

    if (sub) {
      const isLate = sub.late_minutes > 0 || sub.status === 'LATE';
      bodyEl.innerHTML = `
        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-bluelight">
          <div>
            <span class="text-[11px] text-bluedark/60 block">Waktu Pengumpulan:</span>
            <span class="font-bold text-xs text-bluedark">${sub.submitted_at || '-'}</span>
          </div>
          <div>
            ${isLate 
              ? `<span class="badge badge-amber text-xs font-bold">Terlambat (${Math.round(sub.late_minutes / 60) || 1} Jam)</span>` 
              : `<span class="badge badge-emerald text-xs font-bold">Tepat Waktu</span>`
            }
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-bluedark block mb-1">Catatan Pengerjaan dari Siswa:</label>
          <div class="p-3 rounded-xl bg-blue-50/40 border border-bluelight text-xs text-bluedark leading-relaxed">
            ${sub.content ? escapeHtml(sub.content) : '<em class="text-bluedark/50">Tidak ada catatan teks dari siswa.</em>'}
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-bluedark block mb-1.5">Berkas Bukti / Lampiran Tugas:</label>
          ${(sub.media && sub.media.length > 0) ? `
            <div class="space-y-2">
              ${sub.media.map(m => `
                <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-bluelight hover:border-blueprim transition-all">
                  <div class="flex items-center gap-2.5 truncate min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blueprim flex items-center justify-center shrink-0">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <div class="truncate">
                      <div class="font-bold text-xs text-bluedark truncate" title="${escapeHtml(m.name)}">${escapeHtml(m.name)}</div>
                      <div class="text-[10px] text-bluedark/50">${m.size ? Math.round(m.size / 1024) + ' KB' : 'Berkas Tugas'}</div>
                    </div>
                  </div>
                  <a href="${m.path || '#'}" target="_blank" download class="btn btn-primary btn-sm shrink-0 flex items-center gap-1.5">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Unduh / Buka</span>
                  </a>
                </div>
              `).join('')}
            </div>
          ` : `
            <div class="p-3 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-bluedark/50">
              Tidak ada file terlampir.
            </div>
          `}
        </div>
      `;
    } else {
      bodyEl.innerHTML = `
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center text-xs text-amber-800">
          Siswa <strong>${escapeHtml(st.name)}</strong> belum mengumpulkan bukti untuk tugas ini.
        </div>
      `;
    }

    modal.classList.remove("hidden");
    setTimeout(() => {
      modal.classList.remove("opacity-0");
      modalBox.classList.remove("scale-95");
      modalBox.classList.add("scale-100");
    }, 10);
  };

  window.closeModalBukti = function() {
    const modal = document.getElementById("modalBuktiPengiriman");
    const modalBox = document.getElementById("modalBuktiBox");
    if (!modal) return;
    modal.classList.add("opacity-0");
    modalBox.classList.remove("scale-100");
    modalBox.classList.add("scale-95");
    setTimeout(() => {
      modal.classList.add("hidden");
    }, 200);
  };

  // Form Direct Score Input Listener
  const inputDirectScore = document.getElementById("inputDirectScore");
  if (inputDirectScore) {
    inputDirectScore.addEventListener("wheel", (e) => {
      e.preventDefault();
    }, { passive: false });

    inputDirectScore.addEventListener("input", (e) => {
      const val = parseFloat(e.target.value) || 0;
      const inputTotal = document.getElementById("inputTotalScore");
      if (inputTotal) inputTotal.value = val;

      const col = currentGradebookData?.columns?.find(c => c.id === selectedColId);
      if (col && selectedStudentId) {
        if (!col.scores) col.scores = {};
        if (!col.scores[selectedStudentId]) col.scores[selectedStudentId] = {};
        col.scores[selectedStudentId].score = val;

        // Update corresponding input inside spreadsheet
        const cellInput = document.querySelector(`.cell-score-direct-input[data-student-id="${selectedStudentId}"][data-col-id="${selectedColId}"]`);
        if (cellInput && cellInput.value != e.target.value) {
          cellInput.value = e.target.value;
        }

        recalculateSummaries(currentGradebookData);

        const bannerBadge = document.getElementById("bannerScoreBadge");
        if (bannerBadge) bannerBadge.textContent = `Nilai: ${val}`;
      }
    });
  }

  // Global wheel event protection to prevent accidental scroll value increments on focused number inputs
  document.addEventListener("wheel", (e) => {
    if (document.activeElement && (
      document.activeElement.classList.contains("cell-score-direct-input") || 
      document.activeElement.classList.contains("rubric-point") || 
      document.activeElement.id === "inputDirectScore"
    )) {
      e.preventDefault();
    }
  }, { passive: false });

  // Form Dropdown Change Listeners -> Sync with table cells
  const selectKolom = document.getElementById("selectKolomPenilaian");
  if (selectKolom) {
    selectKolom.addEventListener("change", (e) => {
      const colId = parseInt(e.target.value);
      if (colId) {
        selectCell(selectedStudentId || 1, colId);
      }
    });
  }

  const selectSiswa = document.getElementById("selectSiswaPenilaian");
  if (selectSiswa) {
    selectSiswa.addEventListener("change", (e) => {
      const stId = parseInt(e.target.value);
      if (stId) {
        selectCell(stId, selectedColId || 1);
      }
    });
  }

  // Form Reset
  const btnReset = document.getElementById("btnResetNilaiForm");
  if (btnReset) {
    btnReset.addEventListener("click", () => {
      setTimeout(() => {
        if (selectedStudentId && selectedColId) {
          selectCell(selectedStudentId, selectedColId);
        }
      }, 50);
    });
  }

  // Form Submission via AJAX for instant in-page updates
  const formNilai = document.getElementById("formPenilaianSiswa");
  if (formNilai) {
    formNilai.addEventListener("submit", function (e) {
      e.preventDefault();

      const submitBtn = document.getElementById("btnSubmitNilai");
      const originalText = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Menyimpan...
        `;
      }

      const formData = new FormData(formNilai);

      fetch(formNilai.action, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: formData
      })
      .then(res => {
        if (!res.ok) throw new Error("Gagal menyimpan nilai");
        return res.json();
      })
      .then(data => {
        const savedScore = parseFloat(document.getElementById("inputTotalScore").value) || 0;
        const feedbackVal = document.getElementById("inputFeedback").value;

        // Update local memory data
        const col = currentGradebookData?.columns?.find(c => c.id === selectedColId);
        if (col) {
          if (!col.scores) col.scores = {};
          if (!col.scores[selectedStudentId]) col.scores[selectedStudentId] = {};
          col.scores[selectedStudentId].score = savedScore;
          col.scores[selectedStudentId].feedback = feedbackVal;

          // Save rubric items if present
          const rubricInputs = document.querySelectorAll(".rubric-point");
          if (!col.scores[selectedStudentId].rubric_scores) col.scores[selectedStudentId].rubric_scores = {};
          rubricInputs.forEach(inp => {
            const match = inp.name.match(/rubric_scores\[([^\]]+)\]/);
            if (match) {
              col.scores[selectedStudentId].rubric_scores[match[1]] = parseFloat(inp.value) || 0;
            }
          });
        }

        // Update the cell in the spreadsheet table directly!
        const targetCell = document.querySelector(`.score-cell-interactive[data-student-id="${selectedStudentId}"][data-col-id="${selectedColId}"]`);
        if (targetCell) {
          targetCell.dataset.score = savedScore;
          const pill = targetCell.querySelector(".cell-score-pill");
          if (pill) {
            pill.textContent = savedScore;
            pill.classList.remove("cell-score-empty");
          }
          const directInput = targetCell.querySelector(".cell-score-direct-input");
          if (directInput) {
            directInput.value = savedScore;
          }
          // Flash animation
          targetCell.classList.add("bg-emerald-100");
          setTimeout(() => targetCell.classList.remove("bg-emerald-100"), 1200);
        }

        // Recalculate summary columns (RUH, RTG, NSB)
        recalculateSummaries(currentGradebookData);

        // Update Banner badge
        const bannerScoreBadge = document.getElementById("bannerScoreBadge");
        if (bannerScoreBadge) bannerScoreBadge.textContent = `Nilai: ${savedScore}`;

        // Toast feedback
        showToast("Nilai siswa berhasil disimpan ke buku nilai!");
      })
      .catch(err => {
        console.error(err);
        formNilai.submit();
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      });
    });
  }

  function showToast(msg) {
    const existing = document.getElementById("toastNilaiSuccess");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.id = "toastNilaiSuccess";
    toast.className = "fixed bottom-5 right-5 z-50 p-3.5 bg-emerald-600 text-white rounded-xl shadow-lg text-xs font-semibold flex items-center gap-2 transition-all transform translate-y-0";
    toast.innerHTML = `
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span>${escapeHtml(msg)}</span>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.add("opacity-0", "translate-y-2");
      setTimeout(() => toast.remove(), 400);
    }, 3000);
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

  // Attach card click listeners
  document.querySelectorAll("#nilaiKelasGrid .kelas-card").forEach(card => {
    card.addEventListener("click", () => {
      selectClass(card);
    });
  });

  // Tombol Export
  const btnExport = document.getElementById("btnExportNilai");
  if (btnExport) {
    btnExport.addEventListener("click", () => {
      alert("Ekspor nilai kelas " + (currentClassData?.kode || '') + " berhasil disiapkan.");
    });
  }

  // Auto select class/gradebook/column/student from URL parameters or session
  @php
    $selAssignId = session('selected_assignment_id') ?? request('assignment_id');
    $selGradebookId = session('selected_gradebook_id') ?? request('gradebook_id');
    $selColId = session('selected_column_id') ?? request('column_id');
    $selStudentId = session('selected_student_id') ?? request('student_id');
  @endphp

  @if($selAssignId)
    const preselectedCard = document.querySelector(`.kelas-card[data-id="{{ $selAssignId }}"]`);
    if (preselectedCard) {
      selectClass(preselectedCard);

      const reqGbId = {{ $selGradebookId ? (int) $selGradebookId : 'null' }};
      const reqColId = {{ $selColId ? (int) $selColId : 'null' }};
      const reqStudentId = {{ $selStudentId ? (int) $selStudentId : 'null' }};

      let targetGb = null;
      if (reqGbId && currentClassData && currentClassData.gradebooks) {
        targetGb = currentClassData.gradebooks.find(g => g.id === reqGbId);
      }
      if (!targetGb && currentClassData && currentClassData.gradebooks && currentClassData.gradebooks.length > 0) {
        targetGb = currentClassData.gradebooks[0];
      }

      if (targetGb) {
        selectGradebook(targetGb, reqColId, reqStudentId);
      }
    }
  @endif
});
</script>
@endsection
