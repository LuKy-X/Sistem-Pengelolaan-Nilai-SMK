@extends('layouts.teacher')

@section('title', 'Manajemen Tugas — Guru')

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

    <!-- Spreadsheet Nilai Siswa -->
    <div class="panel p-5 mb-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">
            Daftar Nilai - Kelas <span id="tugasKelasName3">XII RA</span>
          </h2>
          <p class="text-xs text-bluedark/50" id="tugasSub3">Matematika - Gasal 2026/2027</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" id="btnExportTugas">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export
        </button>
      </div>

      <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
        <table class="tbl">
          <thead>
            <tr>
              <th rowspan="2" class="w-12 text-center">No</th>
              <th rowspan="2" class="min-w-[180px]">Nama Siswa</th>
              <th colspan="3" class="text-center" style="background:#0D47A1;color:#fff;">Ulangan Harian</th>
              <th colspan="4" class="text-center" style="background:#0D47A1;color:#fff;">Tugas</th>
              <th colspan="2" class="text-center" style="background:#0D47A1;color:#fff;">Nilai</th>
              <th rowspan="2" class="text-center" style="background:#0D47A1;color:#fff;">NSB</th>
            </tr>
            <tr>
              <th class="text-center w-16">1 (UH 1)</th>
              <th class="text-center w-16">2 (UH 2)</th>
              <th class="text-center w-16">RUH</th>
              <th class="text-center w-16">T1</th>
              <th class="text-center w-16">T2</th>
              <th class="text-center w-16">T3</th>
              <th class="text-center w-16">RTG</th>
              <th class="text-center w-16">MID</th>
              <th class="text-center w-16">SEM</th>
            </tr>
          </thead>
          <tbody id="tugasNilaiTableBody">
            <!-- Diisi secara dinamis oleh JavaScript -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form: Manajemen Tugas/Mandiri -->
    <div class="form-block">
      <div class="form-block__header">
        <h3>Manajemen Tugas/Mandiri</h3>
        <p>Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai</p>
      </div>

      <form action="{{ route('teacher.assessments.store') }}" method="POST" class="form-block__body" id="formManajemenTugas">
        @csrf
        <input type="hidden" name="teaching_assignment_id" id="formTeachingAssignmentId" value="">

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Tipe</label>
            <select name="type" class="f-select" id="formTipeSelect">
              <option value="TUGAS">Tugas</option>
              <option value="REMEDIAL">Remidi</option>
              <option value="MANDIRI">Mandiri</option>
              <option value="ULANGAN_HARIAN">Ulangan Harian</option>
            </select>
          </div>
          <div>
            <label class="f-label">Kolom pada buku nilai</label>
            <select name="gradebook_column_id" class="f-select" id="formKolomSelect">
              <option value="">Ulangan Harian 1</option>
            </select>
          </div>
        </div>

        <div class="mb-4">
          <label class="f-label">Judul Tugas <span class="text-red-500">*</span></label>
          <input type="text" name="title" class="f-input" placeholder="cth. Tugas Pemrograman Dasar" required>
        </div>

        <div class="mb-4">
          <label class="f-label">Deskripsi Tugas</label>
          <textarea name="description" class="f-textarea" rows="3" placeholder="Tuliskan instruksi atau keterangan tambahan calon tugas"></textarea>
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Batas Pengumpulan</label>
            <input type="date" name="due_at" class="f-input">
          </div>
          <div>
            <label class="f-label">Bobot Maksimum Nilai <span class="text-red-500">*</span></label>
            <input type="number" name="max_score" class="f-input" value="100" min="1" max="100" required>
          </div>
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat</label>
            <div class="grid grid-cols-[1fr_auto_1.4fr] gap-2 items-center">
              <input type="number" name="reduction_value" class="f-input" value="5" min="0">
              <span class="text-bluedark/50 text-sm">/</span>
              <select name="interval" class="f-select">
                <option value="MINGGU">Minggu</option>
                <option value="HARI">Hari</option>
              </select>
            </div>
          </div>
          <div class="field-toggle self-end">
            <span>Gunakan Pengaturan Default</span>
            <label class="toggle-switch">
              <input type="checkbox" name="use_default_policy" value="1" checked>
              <span class="slider"></span>
            </label>
          </div>
        </div>

        <input type="hidden" name="enable_late_policy" value="1">

        <div class="space-y-3 mb-5">
          <div class="field-toggle">
            <div>
              <span>Gunakan Rubrik Penilaian</span>
              <p class="text-[11px] text-bluedark/50 font-normal">Rubrik membantu menstandarisasi cara penilaian tugas/remidi</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="use_rubric" value="1" id="rubricToggle">
              <span class="slider"></span>
            </label>
          </div>

          <div id="rubricSelectWrap" class="hidden pl-2 pt-1">
            <label class="f-label">Pilih Rubrik Penilaian</label>
            <select name="rubric_id" class="f-select">
              <option value="">-- Pilih Rubrik Penilaian --</option>
              @foreach($rubrics as $rubric)
                <option value="{{ $rubric->id }}">{{ $rubric->name }} ({{ $rubric->criteria->count() }} Kriteria)</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button type="reset" class="btn btn-outline cursor-pointer">Reset</button>
          <button type="submit" class="btn btn-primary cursor-pointer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Simpan Tugas</span>
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<!-- JSON Data Embed untuk interaksi instan tanpa request/reload -->
<script>
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
                  { id: {{ $col->id }}, name: @json($col->name), code: @json($col->code) },
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
              columns: [
                { id: 1, name: 'Ulangan Harian 1', code: 'UH1' },
                { id: 2, name: 'Ulangan Harian 2', code: 'UH2' },
                { id: 3, name: 'Tugas 1', code: 'T1' },
                { id: 4, name: 'Tugas 2', code: 'T2' }
              ],
              students: []
            },
            {
              id: 2,
              title: 'Buku Nilai - Genap 2026/2027',
              sub: @json(($a->subject?->name ?? 'Matematika') . ' · Ulangan Harian dan Tugas'),
              buku: 'Genap 2026/2027',
              badge: @json($a->subject?->name ?? 'Matematika'),
              columns: [
                { id: 5, name: 'Ulangan Harian 1', code: 'UH1' },
                { id: 6, name: 'Tugas 1', code: 'T1' }
              ],
              students: []
            }
          @endforelse
        ]
      },
    @endforeach
  ];

  // Default Fallback Siswa matching mockup
  window.__DEFAULT_STUDENTS__ = [
    'Ahya Rosadi',
    'Amalia Lestari',
    'Baiq Septia',
    'Dani Ansari',
    'Eka Himayani Agustina',
    'Fajar Nugroho',
    'Gita Rahmawati',
    'Hendra Setiawan',
    'Indah Permatasari',
    'Joko Susilo'
  ];
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#tugasTabs button");
  const panels = document.querySelectorAll('.step-panel[data-panel]');
  let currentClassData = null;
  let currentGradebookData = null;

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

  // Toggle Rubrik Penilaian
  const rubricToggle = document.getElementById("rubricToggle");
  const rubricWrap = document.getElementById("rubricSelectWrap");
  if (rubricToggle && rubricWrap) {
    rubricToggle.addEventListener("change", (e) => {
      rubricWrap.classList.toggle("hidden", !e.target.checked);
    });
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
          columns: [
            { id: 1, name: 'Ulangan Harian 1', code: 'UH1' },
            { id: 2, name: 'Ulangan Harian 2', code: 'UH2' },
            { id: 3, name: 'Tugas 1', code: 'T1' },
            { id: 4, name: 'Tugas 2', code: 'T2' }
          ],
          students: []
        },
        {
          id: 2,
          title: 'Buku Nilai - Genap ' + tahun,
          sub: mapel + ' · Ulangan Harian dan Tugas',
          buku: 'Genap ' + tahun,
          badge: mapel,
          columns: [
            { id: 5, name: 'Ulangan Harian 1', code: 'UH1' },
            { id: 6, name: 'Tugas 1', code: 'T1' }
          ],
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
        columns: [],
        students: []
      }
    ];

    gradebooks.forEach((gb, idx) => {
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
  function selectGradebook(gb) {
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

    const selectKolom = document.getElementById("formKolomSelect");
    selectKolom.innerHTML = "";

    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : [
      { id: 1, name: 'Ulangan Harian 1', code: 'UH1' },
      { id: 2, name: 'Ulangan Harian 2', code: 'UH2' },
      { id: 3, name: 'Tugas 1', code: 'T1' },
      { id: 4, name: 'Tugas 2', code: 'T2' }
    ];

    columns.forEach(col => {
      const opt = document.createElement("option");
      opt.value = col.id;
      opt.textContent = col.name + (col.code ? ' (' + col.code + ')' : '');
      selectKolom.appendChild(opt);
    });

    // Render Tabel Nilai Siswa
    renderNilaiTable(gb);

    tabs[2].disabled = false;
    goToStep(3);
  }

  function renderNilaiTable(gb) {
    const tbody = document.getElementById("tugasNilaiTableBody");
    tbody.innerHTML = "";

    let students = (gb.students && gb.students.length > 0) ? gb.students.map(s => s.name) : [];
    if (students.length === 0) {
      students = window.__DEFAULT_STUDENTS__;
    }

    students.forEach((name, i) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="text-center font-semibold text-slate-500">${i + 1}</td>
        <td class="font-medium text-bluedark">${escapeHtml(name)}</td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center font-bold text-bluedark">-</td>
      `;
      tbody.appendChild(tr);
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

  // Cek apakah ada assignment_id dari redirect / URL query
  @if(request('assignment_id'))
    const preselectedCard = document.querySelector(`.kelas-card[data-id="{{ request('assignment_id') }}"]`);
    if (preselectedCard) {
      selectClass(preselectedCard);
      // Jika ada gradebook pertama, pilih otomatis untuk lanjut ke tab 3
      if (currentClassData && currentClassData.gradebooks && currentClassData.gradebooks.length > 0) {
        selectGradebook(currentClassData.gradebooks[0]);
      }
    }
  @endif
});
</script>
@endsection
