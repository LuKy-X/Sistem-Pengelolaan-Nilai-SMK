@extends('layouts.teacher')

@section('title', 'Penilaian Siswa — Guru')

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
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penilaian Siswa</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola &amp; Input Nilai Siswa pada Tugas dan Ulangan yang Telah Diatur</p>
  </div>

  <!-- Arrow Tabs: 100% In-Page Navigation (No reload, no loader animation) -->
  <div class="arrow-tabs" id="nilaiTabs">
    <button type="button" class="active" data-step="1"><span class="step-num">1</span>Daftar Kelas</button>
    <button type="button" data-step="2" disabled><span class="step-num">2</span>Buku Nilai</button>
    <button type="button" data-step="3" disabled><span class="step-num">3</span>Penilaian</button>
  </div>

  <!-- ========================================== -->
  <!-- TAB 1: DAFTAR KELAS                        -->
  <!-- ========================================== -->
  <div class="step-panel active" data-panel="1">
    <p class="text-sm text-bluedark/60 mb-4">Pilih kelas untuk melakukan input penilaian siswa</p>

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
  <!-- TAB 3: FORM PENILAIAN                      -->
  <!-- ========================================== -->
  <div class="step-panel" data-panel="3">
    <p class="text-sm text-bluedark/60 mb-4">
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.nilaiGoToStep(1)">Daftar Kelas</button> / 
      <button type="button" class="hover:underline text-blueprim cursor-pointer font-medium" onclick="window.nilaiGoToStep(2)" id="nilaiBreadcrumb3Kelas">XII RA</button> - 
      <span class="font-semibold text-bluedark" id="nilaiBreadcrumb3">Gasal 2026/2027</span>
    </p>

    <!-- Spreadsheet Preview Nilai Siswa -->
    <div class="panel p-5 mb-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">
            Daftar Nilai - Kelas <span id="nilaiKelasName3">XII RA</span>
          </h2>
          <p class="text-xs text-bluedark/50" id="nilaiSub3">Matematika - Gasal 2026/2027</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" id="btnExportNilai">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span>Export</span>
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
          <tbody id="nilaiTableBody">
            <!-- Diisi secara dinamis oleh JavaScript -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form: Penilaian Tugas/Mandiri (Matching nilai.html) -->
    <div class="form-block">
      <div class="form-block__header">
        <h3>Penilaian Tugas/Mandiri</h3>
        <p>Isi form berikut untuk memanajemen nilai tugas atau remidi siswa pada buku nilai</p>
      </div>

      <form action="{{ route('teacher.grading.store') }}" method="POST" class="form-block__body" id="formPenilaianSiswa">
        @csrf

        <div class="mb-4">
          <label class="f-label">Judul Tugas / Evaluasi</label>
          <input type="text" name="task_title" id="inputJudulTugas" class="f-input" placeholder="cth. Tugas Pemrograman Dasar / Persamaan Linear" value="Tugas 1: Persamaan Linear">
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Kolom pada Buku Nilai <span class="text-red-500">*</span></label>
            <select name="gradebook_column_id" class="f-select" id="selectKolomPenilaian" required>
              <!-- Diisi opsi kolom dari gradebook aktif -->
            </select>
          </div>
          <div>
            <label class="f-label">Nama Siswa <span class="text-red-500">*</span></label>
            <select name="student_id" class="f-select" id="selectSiswaPenilaian" required>
              <!-- Diisi daftar siswa dari rombel aktif -->
            </select>
          </div>
        </div>

        <!-- Rubrik / Kriteria Penilaian Table (Matching nilai.html) -->
        <label class="f-label mb-1.5 block">Kriteria &amp; Rincian Penilaian</label>
        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl mb-4 bg-white">
          <table class="tbl w-full">
            <thead>
              <tr>
                <th>Kriteria Penilaian</th>
                <th style="width: 10rem;" class="text-center">Skor / Poin</th>
              </tr>
            </thead>
            <tbody id="rubrikTableBody">
              <tr>
                <td class="font-medium text-bluedark text-xs">Dijabarkan Cara Pengerjaannya</td>
                <td class="text-center"><input type="number" class="f-input py-1 text-center rubric-point" value="40" min="0" max="100"></td>
              </tr>
              <tr>
                <td class="font-medium text-bluedark text-xs">Jawaban Benar &amp; Rapi</td>
                <td class="text-center"><input type="number" class="f-input py-1 text-center rubric-point" value="40" min="0" max="100"></td>
              </tr>
              <tr>
                <td class="font-medium text-bluedark text-xs">Ketepatan Waktu &amp; Kejujuran</td>
                <td class="text-center"><input type="number" class="f-input py-1 text-center rubric-point" value="10" min="0" max="100"></td>
              </tr>
              <tr style="background:#0D47A1;color:#fff;">
                <td class="font-bold text-xs">Total Nilai Akhir</td>
                <td class="text-center font-bold text-sm">
                  <span id="labelTotalNilai">90</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Hidden input for total score to store -->
        <input type="hidden" name="score" id="inputTotalScore" value="90">

        <div class="mb-4">
          <label class="f-label">Catatan / Feedback untuk Siswa (Opsional)</label>
          <input type="text" name="feedback" class="f-input" placeholder="cth. Pengerjaan sangat baik, pertahankan kerapian logika kode.">
        </div>

        <div class="flex justify-end gap-2">
          <button type="reset" class="btn btn-outline cursor-pointer">Reset</button>
          <button type="submit" class="btn btn-primary cursor-pointer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Simpan Nilai</span>
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<!-- Data JSON Embed -->
<script>
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
                { id: 3, name: 'Tugas 1', code: 'T1' }
              ],
              students: []
            }
          @endforelse
        ]
      },
    @endforeach
  ];

  window.__DEFAULT_STUDENTS_NILAI__ = [
    { id: 1, name: 'Ahya Rosadi' },
    { id: 2, name: 'Amalia Lestari' },
    { id: 3, name: 'Baiq Septia' },
    { id: 4, name: 'Dani Ansari' },
    { id: 5, name: 'Eka Himayani Agustina' },
    { id: 6, name: 'Fajar Nugroho' },
    { id: 7, name: 'Gita Rahmawati' },
    { id: 8, name: 'Hendra Setiawan' }
  ];
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll("#nilaiTabs button");
  const panels = document.querySelectorAll('.step-panel[data-panel]');
  let currentClassData = null;
  let currentGradebookData = null;

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

  // Hitung otomatis total rubrik
  function calculateRubricTotal() {
    const inputs = document.querySelectorAll(".rubric-point");
    let total = 0;
    inputs.forEach(inp => {
      total += parseFloat(inp.value) || 0;
    });
    total = Math.min(100, Math.max(0, total));
    document.getElementById("labelTotalNilai").textContent = total;
    document.getElementById("inputTotalScore").value = total;
  }

  document.querySelectorAll(".rubric-point").forEach(inp => {
    inp.addEventListener("input", calculateRubricTotal);
  });

  // Pilih Kelas dari Tab 1
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
          columns: [
            { id: 1, name: 'Ulangan Harian 1', code: 'UH1' },
            { id: 2, name: 'Ulangan Harian 2', code: 'UH2' },
            { id: 3, name: 'Tugas 1', code: 'T1' }
          ],
          students: []
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
        columns: [],
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

  // Pilih Buku Nilai dari Tab 2 -> Buka Tab 3
  function selectGradebook(gb) {
    currentGradebookData = gb;
    const kode = currentClassData ? currentClassData.kode : 'XII RA';
    const mapel = currentClassData ? currentClassData.mapel : 'Matematika';

    document.getElementById("nilaiBreadcrumb3Kelas").textContent = kode;
    document.getElementById("nilaiBreadcrumb3").textContent = gb.buku || gb.title;
    document.getElementById("nilaiKelasName3").textContent = kode;
    document.getElementById("nilaiSub3").textContent = mapel + " - " + (gb.buku || gb.title);

    // Populate kolom select
    const selectKolom = document.getElementById("selectKolomPenilaian");
    selectKolom.innerHTML = "";
    const columns = (gb.columns && gb.columns.length > 0) ? gb.columns : [
      { id: 1, name: 'Ulangan Harian 1', code: 'UH1' },
      { id: 2, name: 'Ulangan Harian 2', code: 'UH2' },
      { id: 3, name: 'Tugas 1', code: 'T1' }
    ];

    columns.forEach(c => {
      const opt = document.createElement("option");
      opt.value = c.id;
      opt.textContent = c.name + (c.code ? ` (${c.code})` : '');
      selectKolom.appendChild(opt);
    });

    // Populate siswa select
    const selectSiswa = document.getElementById("selectSiswaPenilaian");
    selectSiswa.innerHTML = "";
    let students = (gb.students && gb.students.length > 0) ? gb.students : window.__DEFAULT_STUDENTS_NILAI__;

    students.forEach(st => {
      const opt = document.createElement("option");
      opt.value = st.id;
      opt.textContent = st.name;
      selectSiswa.appendChild(opt);
    });

    renderSpreadsheet(students);

    tabs[2].disabled = false;
    goToStep(3);
  }

  function renderSpreadsheet(students) {
    const tbody = document.getElementById("nilaiTableBody");
    tbody.innerHTML = "";

    students.forEach((st, i) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="text-center font-semibold text-slate-500">${i + 1}</td>
        <td class="font-medium text-bluedark">${escapeHtml(st.name)}</td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" value="${80 + (i % 15)}"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center font-mono font-semibold text-blueprim">${80 + (i % 15)}</td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" value="${85 + (i % 10)}"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" placeholder="-"></td>
        <td class="text-center font-mono font-semibold text-blueprim">${85 + (i % 10)}</td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" value="84"></td>
        <td class="text-center"><input type="number" class="f-input py-1 text-center w-14 mx-auto" value="88"></td>
        <td class="text-center font-bold text-emerald-600">85.5</td>
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

  // Attach card listeners
  document.querySelectorAll("#nilaiKelasGrid .kelas-card").forEach(card => {
    card.addEventListener("click", () => {
      selectClass(card);
    });
  });

  const btnExport = document.getElementById("btnExportNilai");
  if (btnExport) {
    btnExport.addEventListener("click", () => {
      alert("Ekspor nilai kelas " + (currentClassData?.kode || '') + " berhasil disiapkan.");
    });
  }
});
</script>
@endsection
