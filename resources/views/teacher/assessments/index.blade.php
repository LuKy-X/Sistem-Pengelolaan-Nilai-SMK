@extends('layouts.teacher')

@section('title', 'Manajemen Tugas — Guru')

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
  background-color: #EFF6FF !important;
  border-left: 2px solid #2196F3 !important;
  border-right: 2px solid #2196F3 !important;
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
.quick-col-btn.active-quick-btn .quick-new-badge {
  color: #BFDBFE !important;
}
</style>
@endpush

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
  @endif

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Tugas</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Tugas Siswa</p>
  </div>

  <!-- Arrow Tabs: 100% In-Page Navigation (No reload, no loader animation) -->
  <div class="arrow-tabs" id="tugasTabs">
    <button type="button" class="active" data-step="1"><span class="step-num">1</span>Daftar Kelas</button>
    <button type="button" data-step="2" disabled><span class="step-num">2</span>Buku Nilai</button>
    <button type="button" data-step="3" disabled><span class="step-num">3</span>Tugas/Remidi</button>
  </div>

  <!-- ========================================== -->
  <!-- TAB 1: DAFTAR KELAS                        -->
  <!-- ========================================== -->
  <div class="step-panel active" data-panel="1">
    <p class="text-sm text-bluedark/60 mb-4">Pilih kelas untuk mengelola buku nilai dan tugas/remidi</p>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4" id="tugasKelasGrid">
      @forelse($assignments as $assign)
        @php
          $classModel = $assign->schoolClass;
          $className = $classModel?->name ?? 'XII RA';
          $deptName = $classModel?->department?->name ?? 'Rekayasa Perangkat Lunak';
          $studentCount = $assign->gradebooks->first()?->students->count() ?? $classModel?->enrollments->count() ?? 36;
          $days = $assign->schedules->pluck('day_name')->filter()->unique()->join(' & ');
          if (empty($days)) {
              $days = 'Senin & Rabu';
          }
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
        <!-- Fallback Cards matching mockup if no database assignments -->
        <button type="button" class="kelas-card w-full"
          data-id="1" data-kode="XI RA" data-jurusan="Rekayasa Perangkat Lunak" data-siswa="36" data-hari="Senin &amp; Rabu" data-semester="Gasal" data-tahun="2026/2027" data-mapel="Matematika">
          <div class="flex items-center justify-between mb-2">
            <div class="crud-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
          <div class="font-heading font-bold text-bluedark text-sm">XI RA</div>
          <div class="text-xs text-bluedark/50 truncate">Rekayasa Perangkat Lunak</div>
          <div class="kelas-card__meta">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 36 Siswa</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Senin &amp; Rabu</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="badge badge-blue">Gasal</span>
            <span class="badge badge-gray">2026/2027</span>
          </div>
        </button>

        <button type="button" class="kelas-card w-full"
          data-id="2" data-kode="XI TA" data-jurusan="Tekstil" data-siswa="36" data-hari="Senin &amp; Selasa" data-semester="Gasal" data-tahun="2026/2027" data-mapel="Matematika">
          <div class="flex items-center justify-between mb-2">
            <div class="crud-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
          <div class="font-heading font-bold text-bluedark text-sm">XI TA</div>
          <div class="text-xs text-bluedark/50 truncate">Tekstil</div>
          <div class="kelas-card__meta">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 36 Siswa</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Senin &amp; Selasa</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="badge badge-blue">Gasal</span>
            <span class="badge badge-gray">2026/2027</span>
          </div>
        </button>

        <button type="button" class="kelas-card w-full"
          data-id="3" data-kode="XII OA" data-jurusan="Ototronik" data-siswa="35" data-hari="Selasa &amp; Rabu" data-semester="Gasal" data-tahun="2026/2027" data-mapel="Matematika">
          <div class="flex items-center justify-between mb-2">
            <div class="crud-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
          <div class="font-heading font-bold text-bluedark text-sm">XII OA</div>
          <div class="text-xs text-bluedark/50 truncate">Ototronik</div>
          <div class="kelas-card__meta">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 35 Siswa</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Selasa &amp; Rabu</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="badge badge-blue">Gasal</span>
            <span class="badge badge-gray">2026/2027</span>
          </div>
        </button>

        <button type="button" class="kelas-card w-full"
          data-id="4" data-kode="XII MA" data-jurusan="Teknik Mesin" data-siswa="36" data-hari="Rabu &amp; Kamis" data-semester="Gasal" data-tahun="2026/2027" data-mapel="Matematika">
          <div class="flex items-center justify-between mb-2">
            <div class="crud-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
          <div class="font-heading font-bold text-bluedark text-sm">XII MA</div>
          <div class="text-xs text-bluedark/50 truncate">Teknik Mesin</div>
          <div class="kelas-card__meta">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 36 Siswa</span>
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Rabu &amp; Kamis</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="badge badge-blue">Gasal</span>
            <span class="badge badge-gray">2026/2027</span>
          </div>
        </button>
      @endforelse
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB 2: DAFTAR BUKU NILAI                   -->
  <!-- ========================================== -->
  <div class="step-panel" data-panel="2">
    <p class="text-sm text-bluedark/60 mb-4">
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.tugasGoToStep(1)">Daftar Kelas</button> / 
      <span class="font-semibold text-bluedark" id="tugasBreadcrumb2">XII RA</span>
    </p>

    <!-- Banner Biru Kelas Aktif -->
    <div class="panel p-4 flex items-center justify-between gap-3 mb-5" style="background:#2196F3;color:#fff;">
      <div class="flex items-center gap-3 min-w-0">
        <div class="crud-card__icon" style="background:rgba(255,255,255,.2);color:#fff;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div class="min-w-0">
          <div class="font-heading font-bold truncate text-base md:text-lg" id="tugasKelasTitle">Kelas XII RA</div>
          <div class="text-[11px] md:text-xs opacity-85" id="tugasKelasSub">Rekayasa Perangkat Lunak &middot; 36 Siswa</div>
        </div>
      </div>
      <span class="badge" id="tugasKelasBadge" style="background:rgba(255,255,255,.2);color:#fff;">Gasal &middot; 2026/2027</span>
    </div>

    <div class="flex items-center justify-between mb-3">
      <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Buku Nilai</h2>
      <button type="button" class="btn btn-primary btn-sm" id="btnBukuBaru">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Buku Baru</span>
      </button>
    </div>

    <!-- List Buku Nilai untuk kelas terpilih (Dikelola oleh JS) -->
    <div class="flex flex-col gap-3" id="tugasBukuNilaiContainer"></div>
  </div>

  <!-- ========================================== -->
  <!-- TAB 3: TUGAS/REMIDI                        -->
  <!-- ========================================== -->
  <div class="step-panel" data-panel="3">
    <p class="text-sm text-bluedark/60 mb-4">
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.tugasGoToStep(1)">Daftar Kelas</button> / 
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.tugasGoToStep(2)" id="tugasBreadcrumb3Kelas">XII RA</button> - 
      <span class="font-semibold text-bluedark" id="tugasBreadcrumb3">Gasal 2026/2027</span>
    </p>

    <!-- Spreadsheet Nilai Siswa & Pemilihan Kolom -->
    <div class="panel p-4 sm:p-5 mb-5 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-bluelight">
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-sm sm:text-base">
            Daftar Nilai - Kelas <span id="tugasKelasName3">XII RA</span>
          </h2>
          <p class="text-xs text-bluedark/50" id="tugasSub3">Matematika - Gasal 2026/2027</p>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" class="btn btn-outline btn-sm" id="btnExportTugas">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export
          </button>
        </div>
      </div>

      <!-- Panduan & Pilihan Kolom Cepat -->
      <div class="bg-bluelight/40 p-3 rounded-xl border border-bluelight/80">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
          <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-blueprim text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
            <span class="text-xs font-semibold text-bluedark">Pilih Kolom Nilai untuk Membuat / Menyesuaikan Tugas:</span>
          </div>
          <span class="text-[11px] text-bluedark/60 italic">*Klik tombol kolom di bawah atau klik langsung header kolom tabel</span>
        </div>
        <div class="flex flex-wrap items-center gap-1.5" id="quickColumnSelector">
          <!-- Tombol kolom diisi secara dinamis oleh JavaScript -->
        </div>
      </div>

      <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl shadow-2xs">
        <table class="tbl w-full text-left" id="tugasSpreadsheetTable">
          <thead id="tugasNilaiTableHead">
            <!-- Header tabel di-render dinamis oleh JS agar kolom bisa diklik -->
          </thead>
          <tbody id="tugasNilaiTableBody">
            <!-- Diisi secara dinamis oleh JavaScript -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form: Manajemen Tugas/Mandiri -->
    <div class="form-block">
      <div class="form-block__header flex items-center justify-between flex-wrap gap-2">
        <div>
          <h3 class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-blueprim text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
            <span>Manajemen Tugas/Mandiri</span>
          </h3>
          <p>Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai</p>
        </div>
      </div>

      <form action="{{ route('teacher.assessments.store') }}" method="POST" class="form-block__body space-y-4" id="formManajemenTugas">
        @csrf
        <input type="hidden" name="teaching_assignment_id" id="formTeachingAssignmentId" value="">
        <input type="hidden" name="assessment_id" id="formAssessmentId" value="">

        <!-- Banner Kolom Aktif yang Dipilih -->
        <div id="selectedColumnBanner" class="p-3 rounded-xl border border-blue-200 bg-blue-50/80 flex items-center justify-between flex-wrap gap-2 transition-all">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-blueprim text-white flex items-center justify-center shrink-0">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
            </div>
            <div>
              <div class="font-heading font-bold text-xs text-bluedark flex items-center gap-2" id="selectedColumnTitle">
                <span>Kolom Terpilih: Ulangan Harian 1 (UH 1)</span>
              </div>
              <div class="text-[11px] text-bluedark/70 mt-0.5" id="selectedColumnStatus">
                ✨ Belum ada tugas tertaut. Lengkapi form di bawah untuk membuat tugas baru pada kolom ini.
              </div>
            </div>
          </div>
          <span class="badge badge-blue text-[10px]" id="selectedColumnCategoryBadge">Ulangan Harian</span>
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Kolom pada buku nilai <span class="text-blueprim font-bold">*</span></label>
            <select name="gradebook_column_id" class="f-select font-medium" id="formKolomSelect">
              <option value="">-- Pilih Kolom Nilai --</option>
            </select>
          </div>
          <div>
            <label class="f-label">Tipe Tugas / Asesmen <span class="text-blueprim font-bold">*</span></label>
            <select name="type" class="f-select" id="formTipeSelect">
              <option value="TUGAS">Tugas</option>
              <option value="REMEDIAL">Remidi</option>
              <option value="MANDIRI">Mandiri</option>
              <option value="ULANGAN_HARIAN">Ulangan Harian</option>
            </select>
          </div>
        </div>

        <div>
          <label class="f-label">Judul Tugas <span class="text-red-500">*</span></label>
          <input type="text" name="title" id="formTitleInput" class="f-input" placeholder="cth. Tugas Pemrograman Dasar" required>
        </div>

        <div>
          <label class="f-label">Deskripsi Tugas</label>
          <textarea name="description" id="formDescInput" class="f-textarea" rows="2" placeholder="Tuliskan instruksi atau keterangan tambahan calon tugas"></textarea>
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Batas Pengumpulan (Deadline)</label>
            <input type="date" name="due_at" id="formDueInput" class="f-input">
          </div>
          <div>
            <label class="f-label">Bobot Maksimum Nilai <span class="text-red-500">*</span></label>
            <input type="number" name="max_score" id="formMaxScoreInput" class="f-input" value="100" min="1" max="100" required>
          </div>
        </div>

        <!-- Pengaturan Pengurangan Nilai Keterlambatan -->
        <div class="panel p-3.5 rounded-xl border border-bluelight bg-white space-y-2.5">
          <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
              <span class="font-heading font-semibold text-xs text-bluedark block">Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat</span>
              <p class="text-[11px] text-bluedark/60 mt-0.5" id="latePolicyHelpText">
                Aturan default sekolah: Pengurangan batas maksimal nilai sebesar 5 poin per 1 Minggu keterlambatan (batas minimal nilai 50).
              </p>
            </div>
            <div class="field-toggle shrink-0">
              <span class="text-xs font-semibold text-bluedark">Gunakan Pengaturan Default</span>
              <label class="toggle-switch">
                <input type="checkbox" name="use_default_policy" id="useDefaultPolicyToggle" value="1" checked>
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div id="customLatePenaltyWrap" class="grid sm:grid-cols-2 gap-3 pt-2 border-t border-bluelight/60 items-center">
            <div>
              <label class="f-label text-xs">Nilai Pengurangan Poin</label>
              <div class="relative">
                <input type="number" name="reduction_value" id="reductionValueInput" class="f-input" value="5" min="0" max="100">
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-bluedark/50 font-medium pointer-events-none">Poin</span>
              </div>
            </div>
            <div>
              <label class="f-label text-xs">Interval Keterlambatan</label>
              <select name="interval" id="intervalSelect" class="f-select">
                <option value="MINGGU" selected>Per Minggu (7 Hari)</option>
                <option value="HARI">Per Hari (1 Hari)</option>
              </select>
            </div>
          </div>

          <div id="defaultPolicyBadge" class="flex items-center gap-1.5 text-[11px] text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-200">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Pengaturan default aktif: Nilai maksimal tugas otomatis berkurang <strong>5 poin per minggu</strong> terlambat.</span>
          </div>
        </div>

        <input type="hidden" name="enable_late_policy" value="1">

        <!-- Pengaturan Rubrik Penilaian dengan Preview Interaktif -->
        <div class="panel p-3.5 rounded-xl border border-bluelight bg-white space-y-3">
          <div class="field-toggle">
            <div>
              <span class="font-heading font-semibold text-xs text-bluedark block">Gunakan Rubrik Penilaian</span>
              <p class="text-[11px] text-bluedark/50 font-normal">Rubrik membantu menstandarisasi kriteria penilaian tugas siswa</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="use_rubric" value="1" id="rubricToggle">
              <span class="slider"></span>
            </label>
          </div>

          <div id="rubricSelectWrap" class="hidden space-y-2 pt-2 border-t border-bluelight/60">
            <label class="f-label">Pilih Rubrik Penilaian</label>
            <select name="rubric_id" id="rubricSelect" class="f-select">
              <option value="">-- Pilih Rubrik Penilaian --</option>
              @foreach($rubrics as $rubric)
                <option value="{{ $rubric->id }}">{{ $rubric->name }} ({{ $rubric->criteria->count() }} Kriteria · {{ (float) $rubric->criteria->sum('max_points') }} Poin)</option>
              @endforeach
            </select>

            <!-- Container Preview Rubrik -->
            <div id="rubricPreviewContainer"></div>
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <button type="reset" id="btnResetTugasForm" class="btn btn-outline cursor-pointer">Reset Form</button>
          <button type="submit" class="btn btn-primary cursor-pointer" id="btnSubmitTugas">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span id="submitTugasLabel">Simpan Tugas</span>
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<!-- JSON Data Embed untuk interaksi instan tanpa request/reload -->
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

  window.__ASSIGNMENTS_DATA__ = [
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
                        description: @json($linkedAssessment->description ?? ''),
                        due_at: @json($linkedAssessment->due_at?->format('Y-m-d') ?? ''),
                        max_score: {{ (float) $linkedAssessment->max_score }},
                        rubric_id: {{ $linkedAssessment->rubric_id ?? 'null' }},
                        late_policy: @if($linkedAssessment->latePolicy)
                          {
                            enabled: {{ $linkedAssessment->latePolicy->enabled ? 'true' : 'false' }},
                            reduction_value: {{ (float) $linkedAssessment->latePolicy->reduction_value }},
                            interval: {{ $linkedAssessment->latePolicy->interval ?? 7 }},
                            minimum_max_score: {{ (float) $linkedAssessment->latePolicy->minimum_max_score }},
                            is_default: {{ ($linkedAssessment->latePolicy->reduction_value == 5 && $linkedAssessment->latePolicy->interval == 7) ? 'true' : 'false' }}
                          }
                        @else
                          null
                        @endif
                      }
                    @else
                      null
                    @endif
                  },
                @endforeach
              ],
              students: [
                @foreach($gb->students as $gbs)
                  { id: {{ $gbs->student?->id ?? 0 }}, name: @json($gbs->student?->full_name ?? 'Siswa') },
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

  // Default Fallback Columns matching mockup
  window.__DEFAULT_COLUMNS__ = [
    { id: 101, name: 'Ulangan Harian 1', code: '1 (UH 1)', column_type: 'SCORE', category_name: 'Ulangan Harian', max_score: 100, assessment: null },
    { id: 102, name: 'Ulangan Harian 2', code: '2 (UH 2)', column_type: 'SCORE', category_name: 'Ulangan Harian', max_score: 100, assessment: null },
    { id: 103, name: 'Rata-rata Ulangan Harian', code: 'RUH', column_type: 'SUMMARY', category_name: 'Ulangan Harian', max_score: 100, assessment: null },
    { id: 104, name: 'Tugas 1', code: 'T1', column_type: 'SCORE', category_name: 'Tugas', max_score: 100, assessment: null },
    { id: 105, name: 'Tugas 2', code: 'T2', column_type: 'SCORE', category_name: 'Tugas', max_score: 100, assessment: null },
    { id: 106, name: 'Tugas 3', code: 'T3', column_type: 'SCORE', category_name: 'Tugas', max_score: 100, assessment: null },
    { id: 107, name: 'Rata-rata Tugas', code: 'RTG', column_type: 'SUMMARY', category_name: 'Tugas', max_score: 100, assessment: null },
    { id: 108, name: 'Mid Semester', code: 'MID', column_type: 'SCORE', category_name: 'Nilai', max_score: 100, assessment: null },
    { id: 109, name: 'Semester', code: 'SEM', column_type: 'SCORE', category_name: 'Nilai', max_score: 100, assessment: null },
    { id: 110, name: 'Nilai Akhir Siswa', code: 'NSB', column_type: 'SUMMARY', category_name: 'Nilai', max_score: 100, assessment: null }
  ];

  // Default Fallback Siswa matching mockup
  window.__DEFAULT_STUDENTS__ = [
    'Ahmad Fajar',
    'Siti Nurhaliza',
    'Amalia Lestari',
    'Baiq Septia',
    'Dani Ansari',
    'Eka Himayani Agustina',
    'Fajar Nugroho',
    'Gita Rahmawati',
    'Hendra Setiawan',
    'Indah Permatasari'
  ];
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#tugasTabs button");
  const panels = document.querySelectorAll('.step-panel[data-panel]');
  let currentClassData = null;
  let currentGradebookData = null;
  let selectedColId = null;

  function goToStep(step) {
    tabs.forEach(t => t.classList.toggle("active", t.dataset.step === String(step)));
    panels.forEach(p => p.classList.toggle("active", p.dataset.panel === String(step)));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  window.tugasGoToStep = function(step) {
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

  // Toggle & Preview Rubrik Penilaian
  const rubricToggle = document.getElementById("rubricToggle");
  const rubricWrap = document.getElementById("rubricSelectWrap");
  const rubricSelect = document.getElementById("rubricSelect");
  const rubricPreviewContainer = document.getElementById("rubricPreviewContainer");

  if (rubricToggle && rubricWrap) {
    rubricToggle.addEventListener("change", (e) => {
      rubricWrap.classList.toggle("hidden", !e.target.checked);
      if (e.target.checked) {
        renderRubricPreview(rubricSelect.value);
      } else {
        renderRubricPreview(null);
      }
    });
  }

  if (rubricSelect) {
    rubricSelect.addEventListener("change", (e) => {
      const rId = e.target.value;
      renderRubricPreview(rId);
      if (rId && window.__RUBRICS_DATA__ && window.__RUBRICS_DATA__[rId]) {
        const rub = window.__RUBRICS_DATA__[rId];
        if (rub.total_points > 0) {
          const formMaxScoreInput = document.getElementById("formMaxScoreInput");
          if (formMaxScoreInput) {
            formMaxScoreInput.value = rub.total_points;
          }
        }
      }
    });
  }

  function renderRubricPreview(rubricId) {
    if (!rubricPreviewContainer) return;

    if (!rubricId || !window.__RUBRICS_DATA__ || !window.__RUBRICS_DATA__[rubricId]) {
      rubricPreviewContainer.innerHTML = `
        <div class="p-3 rounded-xl border border-dashed border-bluelight text-center text-xs text-bluedark/60 bg-blue-50/30 mt-2">
          <svg class="w-5 h-5 mx-auto mb-1 text-blueprim/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Pilih salah satu rubrik di atas untuk melihat preview kriteria dan pedoman penilaian.
        </div>
      `;
      return;
    }

    const rub = window.__RUBRICS_DATA__[rubricId];
    let criteriaRows = '';
    if (rub.criteria && rub.criteria.length > 0) {
      rub.criteria.forEach((c, idx) => {
        criteriaRows += `
          <tr class="hover:bg-blue-50/40 transition-colors border-b border-bluelight/60">
            <td class="text-center font-semibold text-slate-500 py-2 px-2 text-xs">${idx + 1}</td>
            <td class="font-bold text-bluedark py-2 px-2.5 text-xs">${escapeHtml(c.criterion)}</td>
            <td class="text-slate-600 py-2 px-2.5 leading-relaxed text-[11px]">${escapeHtml(c.description || '-')}</td>
            <td class="text-center font-mono font-bold text-bluedark py-2 px-2 whitespace-nowrap text-xs">${c.max_points} Poin</td>
          </tr>
        `;
      });
    } else {
      criteriaRows = `
        <tr>
          <td colspan="4" class="text-center py-4 text-xs text-bluedark/50">Belum ada kriteria pada rubrik ini.</td>
        </tr>
      `;
    }

    rubricPreviewContainer.innerHTML = `
      <div class="mt-3 p-3.5 rounded-xl border border-bluelight bg-slate-50/70 space-y-2.5">
        <div class="flex items-center justify-between gap-2 flex-wrap pb-2 border-b border-bluelight">
          <div>
            <span class="text-[10px] font-bold text-blueprim uppercase tracking-wider block">Preview Rubrik Penilaian</span>
            <div class="font-heading font-bold text-xs text-bluedark">${escapeHtml(rub.name)}</div>
            ${rub.description ? `<p class="text-[11px] text-bluedark/60 mt-0.5">${escapeHtml(rub.description)}</p>` : ''}
          </div>
          <span class="badge badge-blue text-[11px] font-bold px-2 py-0.5">Total: ${rub.total_points} Poin</span>
        </div>

        <div class="overflow-x-auto db-scroll rounded-lg border border-bluelight/80 bg-white">
          <table class="tbl w-full text-xs">
            <thead>
              <tr style="background:#0D47A1 !important; color:#ffffff !important;">
                <th class="w-10 text-center py-2 !text-white text-[11px]" style="color:#ffffff !important;">No</th>
                <th class="py-2 !text-white text-[11px]" style="color:#ffffff !important;">Kriteria Penilaian</th>
                <th class="py-2 !text-white text-[11px]" style="color:#ffffff !important;">Deskripsi Indikator</th>
                <th class="w-24 text-center py-2 !text-white text-[11px]" style="color:#ffffff !important;">Bobot Maks</th>
              </tr>
            </thead>
            <tbody>
              ${criteriaRows}
              <tr style="background:#0D47A1 !important; color:#ffffff !important;">
                <td colspan="3" class="font-bold px-3 py-2 text-xs !text-white" style="color:#ffffff !important;">Total Skor Maksimum</td>
                <td class="font-bold font-mono text-center text-xs px-2 py-2 !text-white" style="color:#ffffff !important;">${rub.total_points} Poin</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-[10px] text-bluedark/50 italic">*Saat penilaian tugas, guru dapat memberikan skor pada masing-masing kriteria sesuai pedoman di atas.</p>
      </div>
    `;
  }

  // Pengaturan Keterlambatan (Default vs Custom)
  const useDefaultPolicyToggle = document.getElementById("useDefaultPolicyToggle");
  const customLatePenaltyWrap = document.getElementById("customLatePenaltyWrap");
  const defaultPolicyBadge = document.getElementById("defaultPolicyBadge");
  const reductionValueInput = document.getElementById("reductionValueInput");
  const intervalSelect = document.getElementById("intervalSelect");
  const latePolicyHelpText = document.getElementById("latePolicyHelpText");

  function updateLatePolicyUI() {
    if (!useDefaultPolicyToggle) return;
    const isDefault = useDefaultPolicyToggle.checked;

    if (isDefault) {
      if (defaultPolicyBadge) defaultPolicyBadge.classList.remove("hidden");
      if (customLatePenaltyWrap) customLatePenaltyWrap.classList.add("opacity-50");
      if (reductionValueInput) {
        reductionValueInput.value = 5;
        reductionValueInput.readOnly = true;
        reductionValueInput.classList.add("bg-slate-100", "cursor-not-allowed");
      }
      if (intervalSelect) {
        intervalSelect.value = "MINGGU";
        intervalSelect.disabled = true;
        intervalSelect.classList.add("bg-slate-100", "cursor-not-allowed");
      }
      if (latePolicyHelpText) {
        latePolicyHelpText.textContent = "Aturan default sekolah: Pengurangan batas maksimal nilai sebesar 5 poin per 1 Minggu keterlambatan (batas minimal nilai 50).";
      }
    } else {
      if (defaultPolicyBadge) defaultPolicyBadge.classList.add("hidden");
      if (customLatePenaltyWrap) customLatePenaltyWrap.classList.remove("opacity-50");
      if (reductionValueInput) {
        reductionValueInput.readOnly = false;
        reductionValueInput.classList.remove("bg-slate-100", "cursor-not-allowed");
      }
      if (intervalSelect) {
        intervalSelect.disabled = false;
        intervalSelect.classList.remove("bg-slate-100", "cursor-not-allowed");
      }
      if (latePolicyHelpText) {
        latePolicyHelpText.textContent = "Pengaturan kustom: Tentukan nilai pengurangan poin dan interval keterlambatan sendiri untuk tugas ini.";
      }
    }
  }

  if (useDefaultPolicyToggle) {
    useDefaultPolicyToggle.addEventListener("change", updateLatePolicyUI);
  }

  // Pilih Kelas dari Tab 1 -> Render Tab 2
  function selectClass(card) {
    const id = parseInt(card.dataset.id);
    const kode = card.dataset.kode;
    const jurusan = card.dataset.jurusan;
    const siswa = card.dataset.siswa;
    const hari = card.dataset.hari;
    const semester = card.dataset.semester;
    const tahun = card.dataset.tahun;
    const mapel = card.dataset.mapel;

    // Cari data assignment
    const assignData = window.__ASSIGNMENTS_DATA__.find(a => a.id === id) || {
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
          columns: window.__DEFAULT_COLUMNS__,
          students: []
        }
      ]
    };

    currentClassData = assignData;

    // Update Tab 2 Elements
    document.getElementById("tugasBreadcrumb2").textContent = kode;
    document.getElementById("tugasKelasTitle").textContent = "Kelas " + kode;
    document.getElementById("tugasKelasSub").textContent = jurusan + " · " + siswa + " Siswa";
    document.getElementById("tugasKelasBadge").textContent = semester + " · " + tahun;

    // Render Buku Nilai rows di Tab 2
    const container = document.getElementById("tugasBukuNilaiContainer");
    container.innerHTML = "";

    const gradebooks = (assignData.gradebooks && assignData.gradebooks.length > 0) ? assignData.gradebooks : [
      {
        id: 1,
        title: 'Buku Nilai - ' + semester + ' ' + tahun,
        sub: mapel + ' · Ulangan Harian dan Tugas',
        buku: semester + ' ' + tahun,
        badge: mapel,
        columns: window.__DEFAULT_COLUMNS__,
        students: []
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

  // Pilih Buku Nilai dari Tab 2 -> Render Tab 3
  function selectGradebook(gb, initialColId = null) {
    currentGradebookData = gb;
    const kode = currentClassData ? currentClassData.kode : 'XII RA';
    const mapel = currentClassData ? currentClassData.mapel : 'Matematika';

    // Update Tab 3 Elements
    document.getElementById("tugasBreadcrumb3Kelas").textContent = kode;
    document.getElementById("tugasBreadcrumb3").textContent = gb.buku || gb.title;
    document.getElementById("tugasKelasName3").textContent = kode;
    document.getElementById("tugasSub3").textContent = mapel + " - " + (gb.buku || gb.title);

    // Update Form hidden & select inputs
    document.getElementById("formTeachingAssignmentId").value = currentClassData.id;

    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : window.__DEFAULT_COLUMNS__;
    currentGradebookData.columns = columns;

    // Populate #formKolomSelect (Hanya kolom tipe SCORE)
    const selectKolom = document.getElementById("formKolomSelect");
    selectKolom.innerHTML = "";

    const scoreCols = columns.filter(c => c.column_type !== 'SUMMARY');
    scoreCols.forEach(col => {
      const opt = document.createElement("option");
      opt.value = col.id;
      const statusBadge = col.assessment ? ' (Ada Tugas)' : '';
      opt.textContent = `${col.name} ${col.code ? '(' + col.code + ')' : ''}${statusBadge}`;
      selectKolom.appendChild(opt);
    });

    // Render Quick Column Selector & Spreadsheet Table
    renderQuickColumnSelector(gb);
    renderNilaiTable(gb);

    // Otomatis pilih kolom yang ditentukan atau kolom pertama yang bertipe SCORE
    let colToSelect = null;
    if (initialColId && columns.some(c => c.id === initialColId)) {
      colToSelect = initialColId;
    } else {
      colToSelect = scoreCols.length > 0 ? scoreCols[0].id : columns[0].id;
    }
    selectColumn(colToSelect);

    tabs[2].disabled = false;
    goToStep(3);
  }

  // Render Pill Pilihan Kolom Cepat di atas Tabel
  function renderQuickColumnSelector(gb) {
    const container = document.getElementById("quickColumnSelector");
    if (!container) return;
    container.innerHTML = "";

    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : window.__DEFAULT_COLUMNS__;
    const scoreCols = columns.filter(c => c.column_type !== 'SUMMARY');

    scoreCols.forEach(col => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = `quick-col-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer flex items-center gap-1.5 ${col.id === selectedColId ? 'active-quick-btn' : 'bg-white text-bluedark/80 border-bluelight hover:border-blueprim hover:bg-blue-50/50'}`;
      btn.dataset.colId = col.id;

      const hasTask = Boolean(col.assessment);
      btn.innerHTML = `
        <span class="w-2 h-2 rounded-full ${hasTask ? 'bg-emerald-500' : 'bg-slate-300'} inline-block shrink-0"></span>
        <span class="quick-col-name font-medium">${escapeHtml(col.code || col.name)}</span>
        ${hasTask ? '<span class="quick-task-badge text-[9px] px-1.5 py-0.5 rounded">Ada Tugas</span>' : '<span class="quick-new-badge text-[9px] px-1 py-0.5 rounded font-normal">+ Tugas</span>'}
      `;

      btn.addEventListener("click", () => {
        selectColumn(col.id);
      });

      container.appendChild(btn);
    });
  }

  // Render Tabel Spreadsheet Nilai dengan Kolom Interaktif
  function renderNilaiTable(gb) {
    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : window.__DEFAULT_COLUMNS__;
    const thead = document.getElementById("tugasNilaiTableHead");
    const tbody = document.getElementById("tugasNilaiTableBody");

    // Kelompokkan kolom berdasarkan Kategori
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

    // Baris 1 Header: NO, NAMA SISWA, Kategori Colspan
    let row1 = '<tr class="bg-blue-900 text-white">';
    row1 += '<th rowspan="2" class="w-10 text-center py-2 px-2 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="color:#ffffff !important;">NO</th>';
    row1 += '<th rowspan="2" class="min-w-[170px] py-2 px-3 text-[11px] font-bold border-r border-b border-blue-800 !text-white" style="color:#ffffff !important;">NAMA SISWA</th>';

    categoryGroups.forEach(grp => {
      row1 += `<th colspan="${grp.columns.length}" class="text-center py-1.5 px-2 text-[11px] font-bold uppercase tracking-wider border-r border-b border-blue-800 !text-white" style="color:#ffffff !important;">${escapeHtml(grp.name)}</th>`;
    });
    row1 += '</tr>';

    // Baris 2 Header: Nama/Kode Kolom (Kolom SCORE interaktif & clickable)
    let row2 = '<tr class="bg-slate-50">';
    columns.forEach(col => {
      const isSummary = col.column_type === 'SUMMARY';
      const isSelected = selectedColId === col.id;
      if (isSummary) {
        row2 += `
          <th class="text-center py-2 px-2 text-[11px] font-semibold bg-slate-100 text-slate-500 border-r border-b border-bluelight cursor-not-allowed select-none" title="Kolom rata-rata dihitung otomatis">
            <div class="flex flex-col items-center">
              <span>${escapeHtml(col.code || col.name)}</span>
              <span class="text-[9px] text-slate-400 font-normal">Summary</span>
            </div>
          </th>
        `;
      } else {
        const hasTask = Boolean(col.assessment);
        row2 += `
          <th class="text-center py-2 px-2 text-[11px] font-bold border-r border-b border-bluelight cursor-pointer select-none transition-all col-select-th ${isSelected ? 'active-col-header' : 'bg-white hover:bg-blue-50 text-blue-900'}" data-col-id="${col.id}" title="Klik untuk kelola tugas pada kolom ini">
            <div class="flex flex-col items-center gap-0.5">
              <div class="flex items-center gap-1">
                <span>${escapeHtml(col.code || col.name)}</span>
                ${hasTask ? '<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block shadow-xs" title="Tugas aktif"></span>' : ''}
              </div>
              <span class="text-[9px] font-medium ${isSelected ? 'text-blue-100' : 'text-blue-600'}">${hasTask ? 'Tugas Aktif' : '+ Pilih'}</span>
            </div>
          </th>
        `;
      }
    });
    row2 += '</tr>';

    thead.innerHTML = row1 + row2;

    // Render Baris Siswa di Body
    tbody.innerHTML = "";
    let students = (gb.students && gb.students.length > 0) ? gb.students.map(s => s.name) : [];
    if (students.length === 0) {
      students = window.__DEFAULT_STUDENTS__;
    }

    students.forEach((name, i) => {
      const tr = document.createElement("tr");
      let tds = `<td class="text-center font-semibold text-slate-500 py-1.5 px-2 border-r border-bluelight/60 text-xs">${i + 1}</td>`;
      tds += `<td class="font-medium text-bluedark py-1.5 px-3 border-r border-bluelight/60 text-xs truncate max-w-[200px]">${escapeHtml(name)}</td>`;

      columns.forEach(col => {
        const isSummary = col.column_type === 'SUMMARY';
        const isSelected = selectedColId === col.id;
        tds += `
          <td class="text-center py-1.5 px-1.5 border-r border-bluelight/60 ${isSelected ? 'active-col-cell' : ''} ${isSummary ? 'bg-slate-50 font-semibold text-slate-500' : ''}">
            <input type="text" class="f-input py-1 text-center w-12 sm:w-14 mx-auto text-xs ${isSummary ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : ''}" placeholder="-" ${isSummary ? 'readonly' : ''}>
          </td>
        `;
      });
      tr.innerHTML = tds;
      tbody.appendChild(tr);
    });

    // Tambahkan event click ke header kolom SCORE
    thead.querySelectorAll("th.col-select-th").forEach(th => {
      th.addEventListener("click", () => {
        const colId = parseInt(th.dataset.colId);
        selectColumn(colId);
      });
    });
  }

  // Fungsi Inti: Guru Memilih Kolom & Form Menyesuaikan Otomatis
  function selectColumn(colId) {
    if (!currentGradebookData) return;
    selectedColId = colId;

    const columns = (currentGradebookData.columns && currentGradebookData.columns.length > 0) 
      ? currentGradebookData.columns 
      : window.__DEFAULT_COLUMNS__;
    const col = columns.find(c => c.id === colId) || columns[0];
    if (!col) return;

    // 1. Sinkronisasi dropdown #formKolomSelect
    const selectKolom = document.getElementById("formKolomSelect");
    if (selectKolom) {
      selectKolom.value = col.id;
    }

    // 2. Highlight pill pilihan kolom cepat
    document.querySelectorAll("#quickColumnSelector .quick-col-btn").forEach(btn => {
      const isActive = parseInt(btn.dataset.colId) === col.id;
      btn.classList.toggle("active-quick-btn", isActive);
      if (isActive) {
        btn.classList.remove("bg-white", "text-bluedark/80", "border-bluelight");
        btn.classList.add("active-quick-btn");
      } else {
        btn.classList.add("bg-white", "text-bluedark/80", "border-bluelight");
        btn.classList.remove("active-quick-btn");
      }
    });

    // 3. Highlight header kolom pada tabel
    document.querySelectorAll("#tugasNilaiTableHead th.col-select-th").forEach(th => {
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

    // 4. Highlight sel-sel kolom yang dipilih di body tabel
    const colIdx = columns.findIndex(c => c.id === col.id);
    const tbody = document.getElementById("tugasNilaiTableBody");
    if (tbody && colIdx !== -1) {
      const rows = tbody.querySelectorAll("tr");
      rows.forEach(r => {
        const cells = r.querySelectorAll("td");
        cells.forEach((td, idx) => {
          // Index sel = colIdx + 2 (karena 2 kolom pertama adalah NO dan NAMA SISWA)
          if (idx === colIdx + 2) {
            td.classList.add("active-col-cell");
          } else {
            td.classList.remove("active-col-cell");
          }
        });
      });
    }

    // 5. Update Banner Kolom Terpilih
    const titleEl = document.getElementById("selectedColumnTitle");
    const statusEl = document.getElementById("selectedColumnStatus");
    const catBadge = document.getElementById("selectedColumnCategoryBadge");

    if (titleEl) {
      titleEl.innerHTML = `<span>Kolom Terpilih: <strong>${escapeHtml(col.name)}</strong> (${escapeHtml(col.code || '-')})</span>`;
    }
    if (catBadge) {
      catBadge.textContent = col.category_name || 'Nilai';
    }

    const existingAssessment = col.assessment;
    if (statusEl) {
      if (existingAssessment) {
        statusEl.innerHTML = `✨ <strong>Sudah ada tugas tertaut:</strong> "${escapeHtml(existingAssessment.title)}" &middot; Bobot: ${existingAssessment.max_score} Poin. Anda dapat meninjau atau memperbarui tugas di bawah.`;
      } else {
        statusEl.innerHTML = `✨ <strong>Belum ada tugas tertaut.</strong> Lengkapi form di bawah untuk membuat tugas baru pada kolom ini.`;
      }
    }

    // 6. Form menyesuaikan otomatis dari kolom yang dipilih
    const formAssessmentId = document.getElementById("formAssessmentId");
    const formTipeSelect = document.getElementById("formTipeSelect");
    const formTitleInput = document.getElementById("formTitleInput");
    const formDescInput = document.getElementById("formDescInput");
    const formDueInput = document.getElementById("formDueInput");
    const formMaxScoreInput = document.getElementById("formMaxScoreInput");
    const submitLabel = document.getElementById("submitTugasLabel");

    if (existingAssessment) {
      // Isi dari data asesmen yang sudah ada
      formAssessmentId.value = existingAssessment.id;

      const aType = (existingAssessment.type || 'TASK').toUpperCase();
      if (aType.includes('EXAM') || aType.includes('ULANGAN')) {
        formTipeSelect.value = 'ULANGAN_HARIAN';
      } else if (aType.includes('REMEDI')) {
        formTipeSelect.value = 'REMEDIAL';
      } else if (aType.includes('PROJECT') || aType.includes('MANDIRI')) {
        formTipeSelect.value = 'MANDIRI';
      } else {
        formTipeSelect.value = 'TUGAS';
      }

      formTitleInput.value = existingAssessment.title || '';
      formDescInput.value = existingAssessment.description || '';
      formDueInput.value = existingAssessment.due_at || '';
      formMaxScoreInput.value = existingAssessment.max_score || 100;

      // Pengaturan Keterlambatan
      if (existingAssessment.late_policy) {
        useDefaultPolicyToggle.checked = Boolean(existingAssessment.late_policy.is_default);
        reductionValueInput.value = existingAssessment.late_policy.reduction_value || 5;
        intervalSelect.value = (existingAssessment.late_policy.interval == 1) ? 'HARI' : 'MINGGU';
      } else {
        useDefaultPolicyToggle.checked = true;
        reductionValueInput.value = 5;
        intervalSelect.value = 'MINGGU';
      }

      // Pengaturan Rubrik
      if (existingAssessment.rubric_id) {
        rubricToggle.checked = true;
        rubricWrap.classList.remove('hidden');
        rubricSelect.value = existingAssessment.rubric_id;
        renderRubricPreview(existingAssessment.rubric_id);
      } else {
        rubricToggle.checked = false;
        rubricWrap.classList.add('hidden');
        rubricSelect.value = '';
        renderRubricPreview(null);
      }

      if (submitLabel) {
        submitLabel.textContent = `Perbarui Tugas Kolom ${col.code || col.name}`;
      }
    } else {
      // Belum ada asesmen -> Reset form & berikan saran otomatis yang cerdas
      formAssessmentId.value = '';

      const colNameUpper = (col.name + ' ' + (col.code || '')).toUpperCase();
      if (colNameUpper.includes('UH') || colNameUpper.includes('ULANGAN')) {
        formTipeSelect.value = 'ULANGAN_HARIAN';
      } else if (colNameUpper.includes('REMIDI') || colNameUpper.includes('REMEDIAL')) {
        formTipeSelect.value = 'REMEDIAL';
      } else if (colNameUpper.includes('MANDIRI') || colNameUpper.includes('PROJEK') || colNameUpper.includes('PROJECT')) {
        formTipeSelect.value = 'MANDIRI';
      } else {
        formTipeSelect.value = 'TUGAS';
      }

      // Saran judul otomatis
      formTitleInput.value = `${col.name} - ${currentClassData ? currentClassData.mapel : 'Mapel'}`;
      formDescInput.value = '';

      // Default batas pengumpulan 7 hari ke depan
      const nextWeek = new Date();
      nextWeek.setDate(nextWeek.getDate() + 7);
      formDueInput.value = nextWeek.toISOString().split('T')[0];

      formMaxScoreInput.value = col.max_score || 100;

      // Pengaturan keterlambatan default aktif
      useDefaultPolicyToggle.checked = true;
      reductionValueInput.value = 5;
      intervalSelect.value = 'MINGGU';

      // Rubrik tidak aktif secara default
      rubricToggle.checked = false;
      rubricWrap.classList.add('hidden');
      rubricSelect.value = '';
      renderRubricPreview(null);

      if (submitLabel) {
        submitLabel.textContent = `Simpan Tugas Kolom ${col.code || col.name}`;
      }
    }

    updateLatePolicyUI();
  }

  // Sinkronisasi saat dropdown Kolom di form diubah manual
  const formKolomSelect = document.getElementById("formKolomSelect");
  if (formKolomSelect) {
    formKolomSelect.addEventListener("change", (e) => {
      const val = parseInt(e.target.value);
      if (val) {
        selectColumn(val);
      }
    });
  }

  // Reset form button
  const btnReset = document.getElementById("btnResetTugasForm");
  if (btnReset) {
    btnReset.addEventListener("click", () => {
      setTimeout(() => {
        if (selectedColId) {
          selectColumn(selectedColId);
        }
      }, 50);
    });
  }

  // Pastikan field interval aktif saat form di-submit agar nilai terkirim
  const formManajemen = document.getElementById("formManajemenTugas");
  if (formManajemen) {
    formManajemen.addEventListener("submit", () => {
      if (intervalSelect) {
        intervalSelect.disabled = false;
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

  // Attach event click to kelas cards
  const kelasCards = document.querySelectorAll("#tugasKelasGrid .kelas-card");
  kelasCards.forEach(card => {
    card.addEventListener("click", () => {
      selectClass(card);
    });
  });

  // Tombol Export
  const btnExport = document.getElementById("btnExportTugas");
  if (btnExport) {
    btnExport.addEventListener("click", () => {
      alert("Fitur Export Excel/PDF untuk kelas " + (currentClassData?.kode || '') + " siap diunduh.");
    });
  }

  // Tombol + Buku Baru -> Pindah ke halaman form pembuatan buku nilai baru
  const btnBukuBaru = document.getElementById("btnBukuBaru");
  if (btnBukuBaru) {
    btnBukuBaru.addEventListener("click", () => {
      const assignId = currentClassData ? currentClassData.id : '';
      const createUrl = @json(route('teacher.gradebooks.create'));
      window.location.href = createUrl + (assignId ? "?assignment_id=" + assignId : "");
    });
  }

  // Cek apakah ada assignment_id dari redirect session atau URL query
  @php
    $selectedAssignmentId = session('selected_assignment_id') ?? request('assignment_id');
    $selectedGradebookId = session('selected_gradebook_id') ?? request('gradebook_id');
    $selectedColumnId = session('selected_column_id') ?? request('column_id');
  @endphp

  @if($selectedAssignmentId)
    const preselectedCard = document.querySelector(`.kelas-card[data-id="{{ $selectedAssignmentId }}"]`);
    if (preselectedCard) {
      selectClass(preselectedCard);

      const reqGbId = {{ $selectedGradebookId ? (int) $selectedGradebookId : 'null' }};
      const reqColId = {{ $selectedColumnId ? (int) $selectedColumnId : 'null' }};

      let targetGb = null;
      if (reqGbId && currentClassData && currentClassData.gradebooks) {
        targetGb = currentClassData.gradebooks.find(g => g.id === reqGbId);
      }
      if (!targetGb && currentClassData && currentClassData.gradebooks && currentClassData.gradebooks.length > 0) {
        targetGb = currentClassData.gradebooks[0];
      }

      if (targetGb) {
        selectGradebook(targetGb, reqColId);
      }
    }
  @endif
});
</script>
@endsection

