{{--
    Workspace "Absensi Kelas" yang dipakai bersama oleh Guru pengajar dan Guru BK.

    Variabel yang diharapkan:
      $assignments, $selectedAssignment, $selectedDate, $journals, $enrolledStudents,
      $allDayPeriods, $lessonPeriods, $occupiedPeriods, $previousJournalAttendances,
      $previousJournalInfo, $defaultStartPeriodId, $defaultEndPeriodId, $weekStart,
      $weekEnd, $isCurrentWeek, $prevWeekDate, $nextWeekDate, $matchedSchedule,
      $attendanceGate, $lateNotice, $journalIndexRoute, $journalStoreRoute,
      $journalUpdateRoute, $journalDestroyRoute, $journalShowExportMenu,
      $journalHeading, $journalSubheading, $journalEmptyMessage
--}}

@push('styles')
<style>
/* =========================================================
   Tabel Jurnal Kelas — Header & Color Styling
   ========================================================= */
.journal-tbl {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.82rem;
}

/* Force crystal-clear pure white text on all header cells with crisp borders */
.journal-tbl thead th {
  color: #FFFFFF !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 0.72rem !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
  vertical-align: middle !important;
  border: 1px solid rgba(255, 255, 255, 0.28) !important;
  padding: 0.75rem 0.85rem !important;
  line-height: 1.35 !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
}

/* Base columns (Deep Navy Blue - Material Blue 900) */
.journal-tbl thead th.th-navy {
  background-color: #0D47A1 !important;
}
.journal-tbl thead th.th-center {
  text-align: center !important;
}
.journal-tbl thead th.th-left {
  text-align: left !important;
}

/* Super header: Jumlah Siswa (Royal Navy Blue - Material Blue 800) */
.journal-tbl thead th.th-jumlah {
  background-color: #1565C0 !important;
  letter-spacing: 0.07em !important;
  text-align: center !important;
}

/* Hadir Sub-header (Deep Navy Blue) */
.journal-tbl thead th.th-hadir {
  background-color: #0D47A1 !important;
  text-align: center !important;
}

/* Absensi Sub-header (Harmonious Bright Cobalt Blue - Material Blue 600) */
.journal-tbl thead th.th-absensi {
  background-color: #1E88E5 !important;
  text-align: center !important;
}

/* S, I, A Sub-headers (Unified with Absensi!) */
.journal-tbl thead th.th-sia {
  background-color: #1E88E5 !important;
  text-align: center !important;
  padding: 0.5rem 0.25rem !important;
  font-weight: 800 !important;
}

/* Table Body Styling */
.journal-tbl tbody td {
  padding: 0.85rem 0.95rem;
  border-bottom: 1px solid #E2E8F0;
  vertical-align: middle;
  font-size: 0.82rem;
  color: #0F172A;
}

.journal-tbl tbody tr:hover {
  background-color: #F8FAFC;
}

.journal-tbl tbody tr:last-child td {
  border-bottom: none;
}
</style>
@endpush

@php
  $isAttendanceBlocked = $attendanceGate['blocked'];
@endphp

<div class="space-y-6">

  @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-medium">{{ session('success') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
  @endif

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $journalHeading }}</h1>
    <p class="text-sm text-bluedark/60 mt-1">{{ $journalSubheading }}</p>
  </div>

  @if(! $selectedAssignment)
    <!-- Step 1: Panel Pilih Kelas (Spacious 3-Column Grid) -->
    <div class="step-panel active" data-panel="1">
      <div class="panel p-5 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-base md:text-lg">Daftar Kelas</h2>
            <p class="text-xs md:text-sm text-bluedark/50 mt-0.5">Pilih kelas untuk mengelola journal kelas sesi minggu ini</p>
          </div>

          <!-- Week Navigation Selector -->
          <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-50 border border-slate-200/90 rounded-2xl px-3 py-1.5 shadow-2xs self-start sm:self-auto">
            <a href="{{ route($journalIndexRoute, ['date' => $prevWeekDate]) }}" 
               class="p-1 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition-colors" 
               title="Minggu Sebelumnya">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </a>

            <div class="text-center px-2">
              <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                {{ $isCurrentWeek ? 'Minggu Berjalan' : 'Riwayat Minggu' }}
              </div>
              <div class="text-xs font-bold text-slate-700 whitespace-nowrap">
                {{ $weekStart->translatedFormat('d M') }} — {{ $weekEnd->translatedFormat('d M Y') }}
              </div>
            </div>

            <a href="{{ route($journalIndexRoute, ['date' => $nextWeekDate]) }}" 
               class="p-1 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition-colors" 
               title="Minggu Berikutnya">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            @if(!$isCurrentWeek)
              <a href="{{ route($journalIndexRoute) }}" 
                 class="ml-1 text-[11px] font-semibold text-blueprim hover:underline px-2 py-0.5 rounded-lg bg-blue-50 border border-blue-200 whitespace-nowrap">
                Minggu Ini
              </a>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="absensiKelasGrid">
          @forelse($assignments as $assignment)
            @php
              $classModel = $assignment->schoolClass;
              $className = $classModel?->name ?? 'Kelas';
              $deptName = $classModel?->department?->name ?? $assignment->subject?->name ?? 'Rekayasa Perangkat Lunak';
              $studentCount = $classModel?->activeStudentCount() ?? 0;
              $semesterName = $assignment->semester?->semester_number == 1 ? 'Gasal' : 'Genap';
              $academicYear = $assignment->semester?->academicYear?->name ?? '2025/2026';

              $summary = $assignment->schedule_summary ?? [
                'primary_date' => $selectedDate ?? now()->format('Y-m-d'),
                'status_code' => 'no_schedule',
                'status_label' => 'Belum Ada Jadwal',
                'badge_class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'is_today' => false,
                'is_overdue' => false,
                'all_schedules' => [],
              ];
              $targetDate = $summary['primary_date'];

              // Border accents depending on status
              $cardBorderClass = match($summary['status_code']) {
                'overdue' => 'border-amber-300 ring-2 ring-amber-400/20 hover:border-amber-500 bg-gradient-to-br from-amber-50/20 to-white shadow-xs',
                'today_unfilled' => 'border-emerald-300 ring-2 ring-emerald-400/25 hover:border-emerald-500 bg-gradient-to-br from-emerald-50/20 to-white shadow-xs',
                'today_filled' => 'border-emerald-200 hover:border-emerald-400',
                default => 'border-bluelight/70 hover:border-blueprim hover:shadow-md',
              };
            @endphp
            <a href="{{ route($journalIndexRoute, ['assignment_id' => $assignment->id, 'date' => $targetDate]) }}" 
               class="kelas-card block group p-5 md:p-6 rounded-2xl bg-white border {{ $cardBorderClass }} transition-all text-left relative overflow-hidden" 
               data-group="absensi" data-kode="{{ $className }}">
              
              @if($summary['is_overdue'])
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-rose-400"></div>
              @elseif($summary['status_code'] === 'today_unfilled')
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400"></div>
              @endif

              <div class="flex items-start justify-between gap-2 mb-3">
                <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors bg-blue-50 text-blueprim mt-0.5">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>

                <!-- Schedule / Missed / Filled Badge -->
                <div class="flex flex-col items-end">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $summary['badge_class'] }} shadow-2xs">
                    @if($summary['status_code'] === 'today_unfilled')
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    @elseif($summary['status_code'] === 'overdue')
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                    {{ $summary['status_label'] }}
                  </span>
                </div>
              </div>

              <div class="font-heading font-bold text-bluedark text-base md:text-lg mb-1 group-hover:text-blueprim transition-colors">
                {{ $className }}
              </div>
              <div class="text-xs md:text-sm text-bluedark/60 mb-3">
                {{ $deptName }}
              </div>

              <div class="kelas-card__meta mb-3">
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 
                  {{ $studentCount }} Siswa
                </span>
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> 
                  {{ $assignment->weekly_hours ?? 4 }} Jam / Mgg
                </span>
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="badge badge-blue text-[11px]">{{ $semesterName }}</span>
                  <span class="badge badge-gray text-[11px]">{{ $academicYear }}</span>
                </div>
                <span class="font-medium text-blueprim group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-0.5 text-xs">
                  Buka Jurnal
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
              </div>
            </a>
          @empty
            <div class="col-span-full py-12 text-center text-sm text-bluedark/50">
              {{ $journalEmptyMessage }}
            </div>
          @endforelse
        </div>
      </div>
    </div>
  @else
    <!-- Step 2: Detail Journal & Form Absensi -->
    <div class="step-panel active" data-panel="2">
      <p class="text-sm text-bluedark/60 mb-4 flex items-center gap-1.5">
        <a href="{{ route($journalIndexRoute, ['date' => $selectedDate]) }}" class="font-semibold text-blueprim hover:underline inline-flex items-center gap-1">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Daftar Kelas
        </a> 
        <span class="text-slate-400">/</span> 
        <span class="font-semibold text-bluedark" id="absensiBreadcrumb">{{ $selectedAssignment->schoolClass?->name }}</span>
      </p>

      @if($isAttendanceBlocked)
        <div class="mb-5 p-4 rounded-2xl border text-sm flex items-start gap-3 shadow-2xs
                    {{ $attendanceGate['tone'] === 'rose'
                        ? 'bg-rose-50 border-rose-200 text-rose-900'
                        : 'bg-amber-50 border-amber-200 text-amber-900' }}">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $attendanceGate['tone'] === 'rose' ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <div>
            <span class="font-bold block">{{ $attendanceGate['title'] }}</span>
            <span class="text-xs opacity-80">{{ $attendanceGate['detail'] }}</span>
          </div>
        </div>
      @elseif($lateNotice)
        <div class="mb-5 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-sm flex items-start gap-3 shadow-2xs">
          <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <span class="font-bold block">{{ $lateNotice['title'] }}</span>
            <span class="text-xs text-indigo-800/80">{{ $lateNotice['detail'] }}</span>
          </div>
        </div>
      @endif


      <!-- Panel Journal Table -->
      <div class="panel p-5 md:p-6 mb-6 rounded-2xl bg-white border border-bluelight shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="font-heading font-bold text-bluedark text-base md:text-lg">
                Journal - Kelas {{ $selectedAssignment->schoolClass?->name }}
              </h2>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                1 Hari ({{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd') }})
              </span>
              @if($matchedSchedule)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  Jadwal: Jam {{ $matchedSchedule->startPeriod?->period_number }} sd {{ $matchedSchedule->endPeriod?->period_number }}
                </span>
              @endif
            </div>
            <p class="text-xs md:text-sm text-bluedark/60 mt-0.5">
              {{ $selectedAssignment->schoolClass?->department?->name ?? 'Rekayasa Perangkat Lunak' }} - {{ $selectedAssignment->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }} {{ $selectedAssignment->semester?->academicYear?->name ?? '2026/2027' }} &middot; 
              <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            </p>
          </div>
          <div class="flex items-center gap-2.5 flex-wrap">
            <form method="GET" action="{{ route($journalIndexRoute) }}" class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs">
              <input type="hidden" name="assignment_id" value="{{ $selectedAssignment->id }}">
              <label for="filterDate" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Tanggal:</label>
              <input type="date" id="filterDate" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" 
                     class="text-xs font-semibold text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
            </form>

            @if($journalShowExportMenu)

            <!-- Dropdown Export Jurnal (PDF & Excel) -->
            <div class="relative inline-block text-left" id="exportJournalDropdownWrapper">
              <button type="button" 
                      id="btnExportJournalDropdown"
                      onclick="toggleExportJournalDropdown(event)"
                      class="btn btn-sm px-3.5 py-2 rounded-xl bg-bluedark hover:bg-blueprim text-white font-medium text-xs shadow-sm inline-flex items-center gap-1.5 transition-all">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> 
                <span>Export Jurnal</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              
              <div id="exportJournalDropdownMenu" 
                   style="display: none;" 
                   class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl border border-slate-200 py-2 z-50">
                <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                  Format PDF (Cetak / Buku Agenda)
                </div>
                <a href="{{ route('teacher.journals.export.pdf', ['assignment_id' => $selectedAssignment->id, 'date' => $selectedDate, 'range' => 'daily']) }}" 
                   target="_blank" 
                   class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                  <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                  <div>
                    <div class="font-bold">PDF Jurnal Hari Ini</div>
                    <div class="text-[11px] font-normal text-slate-400">{{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                  </div>
                </a>
                <a href="{{ route('teacher.journals.export.pdf', ['assignment_id' => $selectedAssignment->id, 'date' => $selectedDate, 'range' => 'weekly']) }}" 
                   target="_blank" 
                   class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                  <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
                  </div>
                  <div>
                    <div class="font-bold">PDF Rekap Mingguan</div>
                    <div class="text-[11px] font-normal text-slate-400">Minggu Ke-{{ $weekStart->isoWeek() }} ({{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m/Y') }})</div>
                  </div>
                </a>

                <div class="my-1 border-t border-slate-100"></div>

                <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                  Format Spreadsheet (Excel .xlsx)
                </div>
                <a href="{{ route('teacher.journals.export.excel', ['assignment_id' => $selectedAssignment->id, 'date' => $selectedDate, 'range' => 'daily']) }}" 
                   class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                   data-no-transition="true" download>
                  <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                  </div>
                  <div>
                    <div class="font-bold">Excel Jurnal Hari Ini (.xlsx)</div>
                    <div class="text-[11px] font-normal text-slate-400">Data sesi &amp; presensi harian</div>
                  </div>
                </a>
                <a href="{{ route('teacher.journals.export.excel', ['assignment_id' => $selectedAssignment->id, 'date' => $selectedDate, 'range' => 'weekly']) }}" 
                   class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                   data-no-transition="true" download>
                  <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                  </div>
                  <div>
                    <div class="font-bold">Excel Rekap Mingguan (.xlsx)</div>
                    <div class="text-[11px] font-normal text-slate-400">Seluruh sesi minggu ini</div>
                  </div>
                </a>
              </div>
            </div>
            @endif
          </div>
        </div>

        <div class="overflow-x-auto db-scroll border border-slate-200 rounded-xl shadow-2xs">
          <table class="journal-tbl w-full text-left text-xs">
            <thead>
              <tr>
                <th rowspan="3" class="th-navy th-center whitespace-nowrap" style="width: 110px;">Hari / Tanggal</th>
                <th rowspan="3" class="th-navy th-center whitespace-nowrap" style="width: 85px;">Jam ke-</th>
                <th rowspan="3" class="th-navy th-left whitespace-nowrap" style="min-width: 140px;">Mata Pelajaran</th>
                <th rowspan="3" class="th-navy th-left whitespace-nowrap" style="min-width: 150px;">Nama Guru</th>
                <th rowspan="3" class="th-navy th-left" style="min-width: 190px;">Materi</th>
                <th colspan="4" class="th-jumlah">Jumlah Siswa</th>
                <th rowspan="3" class="th-navy th-left" style="min-width: 220px;">Keterangan</th>
                <th rowspan="3" class="th-navy th-center whitespace-nowrap" style="width: 95px;">Aksi</th>
              </tr>
              <tr>
                <th rowspan="2" class="th-hadir" style="width: 60px;">Hadir</th>
                <th colspan="3" class="th-absensi">Absensi</th>
              </tr>
              <tr>
                <th class="th-sia" style="width: 38px;">S</th>
                <th class="th-sia" style="width: 38px;">I</th>
                <th class="th-sia" style="width: 38px;">A</th>
              </tr>
            </thead>
            <tbody id="journalTableBody" class="divide-y divide-slate-100 bg-white">
              @php
                $cDate = \Carbon\Carbon::parse($selectedDate)->locale('id');
                $dayName = $cDate->isoFormat('dddd');
                $dateFormatted = $cDate->format('d/m/Y');
                $coveredPeriodNumbers = [];
                $renderedJournalIds = [];
              @endphp

              @if($allDayPeriods->isNotEmpty())
                @foreach($allDayPeriods as $period)
                  @if($period->is_break)
                    <!-- Baris Jam Istirahat Melintang Penuh -->
                    <tr class="bg-amber-50/70 border-y-2 border-amber-200/80 hover:bg-amber-50 transition-colors">
                      <td colspan="11" class="py-2.5 px-4 text-center">
                        <div class="inline-flex items-center justify-center gap-2 text-amber-950 font-bold text-xs tracking-wide">
                          @if(str_contains(strtolower($period->name), '2') || str_contains(strtolower($period->name), 'ii') || substr($period->start_time, 0, 2) >= '11')
                            <span class="text-base leading-none">🕌</span>
                          @else
                            <span class="text-base leading-none">☕</span>
                          @endif
                          <span class="uppercase tracking-wider font-extrabold text-amber-900">{{ strtoupper($period->name) }}</span>
                          <span class="px-2 py-0.5 rounded-full bg-amber-200/70 text-amber-900 font-mono text-[11px] font-semibold">
                            {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                          </span>
                          @if(str_contains(strtolower($period->name), '2') || str_contains(strtolower($period->name), 'ii') || substr($period->start_time, 0, 2) >= '11')
                            <span class="text-[11px] text-amber-800/80 font-normal italic ml-1">&middot; Waktu Istirahat, Sholat &amp; Makan Siang</span>
                          @else
                            <span class="text-[11px] text-amber-800/80 font-normal italic ml-1">&middot; Waktu Istirahat &amp; Rehat Siswa</span>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @else
                    @php
                      $pNum = $period->period_number;
                    @endphp

                    @if(in_array($pNum, $coveredPeriodNumbers))
                      {{-- Jam ini telah terangkum dalam rentang jurnal multi-jam sebelumnya --}}
                      @continue
                    @endif

                    @php
                      // Cari apakah ada jurnal yang dimulai pada jam pelajaran ini
                      $j = $journals->first(function($item) use ($pNum, $period) {
                        $startNum = $item->startPeriod?->period_number ?? $item->start_period_id;
                        return $startNum == $pNum || $item->start_period_id == $period->id;
                      });
                    @endphp

                    @if($j)
                      @php
                        $renderedJournalIds[] = $j->id;
                        $startNum = $j->startPeriod?->period_number ?? $pNum;
                        $endNum = $j->endPeriod?->period_number ?? $startNum;
                        for ($k = min($startNum, $endNum); $k <= max($startNum, $endNum); $k++) {
                          $coveredPeriodNumbers[] = $k;
                        }

                        $currentTeacherId = auth()->user()->teacherProfile?->id;
                        $isOwner = $currentTeacherId && $j->created_by === $currentTeacherId;
                        $editPayload = [
                          'id' => $j->id,
                          'start_period_id' => $j->start_period_id,
                          'end_period_id' => $j->end_period_id,
                          'start_period_number' => $startNum,
                          'end_period_number' => $endNum,
                          'material' => $j->material,
                          'notes' => trim(preg_replace('/Hadir:\s*\d+\s*\|\s*Sakit:\s*\d+\s*\|\s*Izin:\s*\d+\s*\|\s*Alpha:\s*\d+/i', '', $j->notes ?? '')),
                          'hadir_count' => $j->hadir_count,
                          'sakit_count' => $j->sakit_count,
                          'izin_count' => $j->izin_count,
                          'alpha_count' => $j->alpha_count,
                          'update_url' => route($journalUpdateRoute, $j->id),
                          'attendances' => $j->attendances
                            ->filter(fn($att) => $att->status !== \App\Enums\AttendanceStatus::Present)
                            ->map(fn($att) => [
                              'student_id' => $att->student_id,
                              'status' => match($att->status) {
                                \App\Enums\AttendanceStatus::Sick => 'SAKIT',
                                \App\Enums\AttendanceStatus::Permit => 'IZIN',
                                \App\Enums\AttendanceStatus::Absent => 'ALPHA',
                                default => 'HADIR',
                              },
                              'note' => $att->note ?? '',
                            ])->values(),
                        ];
                      @endphp
                      <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-3.5 py-3 font-semibold text-xs whitespace-nowrap text-bluedark text-center align-middle">
                          <div class="font-bold text-bluedark text-xs capitalize">{{ $dayName }}</div>
                          <div class="text-[11px] font-mono text-slate-500 font-normal">{{ $dateFormatted }}</div>
                        </td>
                        <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                          <span class="inline-flex flex-col items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-900 border border-blue-200/80 font-mono font-bold text-xs">
                              @if($startNum == $endNum)
                                Jam {{ $startNum }}
                              @else
                                Jam {{ $startNum }} sd {{ $endNum }}
                              @endif
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 mt-0.5">
                              {{ substr($j->startPeriod?->start_time ?? $period->start_time, 0, 5) }} - {{ substr($j->endPeriod?->end_time ?? $period->end_time, 0, 5) }}
                            </span>
                          </span>
                        </td>
                        <td class="px-3.5 py-3 text-xs font-semibold text-bluedark align-middle">
                          {{ $j->teachingAssignment?->subject?->name ?? $selectedAssignment->subject?->name }}
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-700 align-middle">
                          {{ $j->creator?->user?->name ?? $j->creator?->full_name ?? auth()->user()->name }}
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-800 align-middle font-medium leading-relaxed">
                          {{ $j->material }}
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-emerald-50 text-emerald-700 font-bold font-mono text-xs border border-emerald-200/80">
                            {{ $j->hadir_count }}
                          </span>
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          @if($j->sakit_count > 0)
                            <span class="inline-flex items-center justify-center min-w-[26px] h-7 px-1.5 rounded-lg bg-amber-50 text-amber-700 font-bold font-mono text-xs border border-amber-200/80">
                              {{ $j->sakit_count }}
                            </span>
                          @else
                            <span class="text-slate-300 font-mono text-xs font-semibold">0</span>
                          @endif
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          @if($j->izin_count > 0)
                            <span class="inline-flex items-center justify-center min-w-[26px] h-7 px-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold font-mono text-xs border border-blue-200/80">
                              {{ $j->izin_count }}
                            </span>
                          @else
                            <span class="text-slate-300 font-mono text-xs font-semibold">0</span>
                          @endif
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          @if($j->alpha_count > 0)
                            <span class="inline-flex items-center justify-center min-w-[26px] h-7 px-1.5 rounded-lg bg-red-50 text-red-700 font-bold font-mono text-xs border border-red-200/80">
                              {{ $j->alpha_count }}
                            </span>
                          @else
                            <span class="text-slate-300 font-mono text-xs font-semibold">0</span>
                          @endif
                        </td>
                        <td class="px-3.5 py-3 text-xs align-middle">
                          @php
                            $absentStudents = $j->attendances->filter(fn($a) => $a->status !== \App\Enums\AttendanceStatus::Present);
                          @endphp
                          @if($absentStudents->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                              @foreach($absentStudents as $att)
                                @php
                                  $bCls = match($att->status) {
                                    \App\Enums\AttendanceStatus::Sick => [
                                      'pill' => 'bg-amber-50 text-amber-900 border-amber-200',
                                      'name' => 'text-amber-800',
                                      'tag'  => 'bg-amber-200/80 text-amber-900',
                                      'label' => 'Sakit'
                                    ],
                                    \App\Enums\AttendanceStatus::Permit => [
                                      'pill' => 'bg-blue-50 text-blue-900 border-blue-200',
                                      'name' => 'text-blue-800',
                                      'tag'  => 'bg-blue-200/80 text-blue-900',
                                      'label' => 'Izin'
                                    ],
                                    \App\Enums\AttendanceStatus::Absent => [
                                      'pill' => 'bg-red-50 text-red-900 border-red-200',
                                      'name' => 'text-red-800',
                                      'tag'  => 'bg-red-200/80 text-red-900',
                                      'label' => 'Alpha'
                                    ],
                                    default => [
                                      'pill' => 'bg-slate-50 text-slate-800 border-slate-200',
                                      'name' => 'text-slate-800',
                                      'tag'  => 'bg-slate-200 text-slate-700',
                                      'label' => 'Hadir'
                                    ],
                                  };
                                @endphp
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs {{ $bCls['pill'] }} border shadow-2xs">
                                  <span class="font-bold {{ $bCls['name'] }}">{{ $att->student?->full_name ?? 'Siswa' }}</span>
                                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $bCls['tag'] }} uppercase tracking-wider">{{ $bCls['label'] }}</span>
                                  @if($att->note)
                                    <span class="text-[11px] opacity-75">- {{ $att->note }}</span>
                                  @endif
                                </div>
                              @endforeach
                            </div>
                          @else
                            <span class="text-xs text-slate-400 italic">Semua Hadir</span>
                          @endif

                          @php
                            $extraNote = trim(preg_replace('/Hadir:\s*\d+\s*\|\s*Sakit:\s*\d+\s*\|\s*Izin:\s*\d+\s*\|\s*Alpha:\s*\d+/i', '', $j->notes ?? ''));
                          @endphp
                          @if($extraNote)
                            <div class="text-[11px] text-slate-500 mt-1 italic flex items-center gap-1">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                              <span>{{ $extraNote }}</span>
                            </div>
                          @endif
                        </td>
                        <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                          @if($isOwner)
                            <div class="inline-flex items-center gap-1.5">
                              <button type="button" 
                                      class="btn-edit-journal p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                      title="Edit Jurnal"
                                      data-journal='@json($editPayload)'>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                              </button>
                              <form action="{{ route($journalDestroyRoute, $j->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jurnal jam pelajaran ini?')" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 transition-colors shadow-2xs"
                                        title="Hapus Jurnal">
                                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                              </form>
                            </div>
                          @else
                            <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium" title="Hanya guru pembuat yang dapat mengedit/menghapus">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                              Terkunci
                            </span>
                          @endif
                        </td>
                      </tr>
                    @else
                      {{-- Jam belum diisi: Baris Placeholder Agenda Buku Jurnal Fisik --}}
                      <tr class="hover:bg-slate-50/60 transition-colors bg-white/40">
                        <td class="px-3.5 py-3 font-semibold text-xs whitespace-nowrap text-slate-500 text-center align-middle">
                          <div class="font-medium text-slate-600 text-xs capitalize">{{ $dayName }}</div>
                          <div class="text-[11px] font-mono text-slate-400 font-normal">{{ $dateFormatted }}</div>
                        </td>
                        <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                          <span class="inline-flex flex-col items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-600 font-mono font-bold text-xs">
                              Jam {{ $pNum }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 mt-0.5">
                              {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                            </span>
                          </span>
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">
                          Belum diisi
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">
                          -
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">
                          Belum ada materi pembelajaran
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          <span class="text-slate-300 font-mono text-xs font-semibold">-</span>
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          <span class="text-slate-300 font-mono text-xs font-semibold">-</span>
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          <span class="text-slate-300 font-mono text-xs font-semibold">-</span>
                        </td>
                        <td class="px-2 py-3 text-center align-middle">
                          <span class="text-slate-300 font-mono text-xs font-semibold">-</span>
                        </td>
                        <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">
                          -
                        </td>
                        <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                          @if($isAttendanceBlocked)
                            <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium" title="Absensi tidak dapat diisi pada tanggal ini">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                              Terkunci
                            </span>
                          @else
                            <button type="button" 
                                    onclick="selectPeriodRange({{ $period->id }}, {{ $period->id }})"
                                    class="btn-fill-period inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200/80 hover:bg-blue-100 hover:text-blue-800 transition-colors shadow-2xs"
                                    title="Isi jurnal untuk Jam ke-{{ $pNum }}">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                              <span>Isi Jam Ini</span>
                            </button>
                          @endif
                        </td>
                      </tr>
                    @endif
                  @endif
                @endforeach

                {{-- Render residual journals outside regular periods if any --}}
                @foreach($journals->whereNotIn('id', $renderedJournalIds) as $j)
                  <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-3.5 py-3 font-semibold text-xs whitespace-nowrap text-bluedark text-center align-middle">
                      <div class="font-bold text-bluedark text-xs capitalize">{{ $dayName }}</div>
                      <div class="text-[11px] font-mono text-slate-500 font-normal">{{ $dateFormatted }}</div>
                    </td>
                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                      <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-900 border border-blue-200/80 font-mono font-bold text-xs">
                        Jam {{ $j->startPeriod?->period_number ?? '?' }} sd {{ $j->endPeriod?->period_number ?? '?' }}
                      </span>
                    </td>
                    <td class="px-3.5 py-3 text-xs font-semibold text-bluedark align-middle">
                      {{ $j->teachingAssignment?->subject?->name ?? $selectedAssignment->subject?->name }}
                    </td>
                    <td class="px-3.5 py-3 text-xs text-slate-700 align-middle">
                      {{ $j->creator?->user?->name ?? 'Guru' }}
                    </td>
                    <td class="px-3.5 py-3 text-xs text-slate-800 align-middle font-medium leading-relaxed">
                      {{ $j->material }}
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-emerald-50 text-emerald-700 font-bold font-mono text-xs border border-emerald-200/80">
                        {{ $j->hadir_count }}
                      </span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->sakit_count }}</span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->izin_count }}</span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->alpha_count }}</span>
                    </td>
                    <td class="px-3.5 py-3 text-xs align-middle">
                      {{ $j->notes ?: '-' }}
                    </td>
                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                      -
                    </td>
                  </tr>
                @endforeach
              @else
                @forelse($journals as $j)
                  <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-3.5 py-3 font-semibold text-xs whitespace-nowrap text-bluedark text-center align-middle">
                      <div class="font-bold text-bluedark text-xs capitalize">{{ $dayName }}</div>
                      <div class="text-[11px] font-mono text-slate-500 font-normal">{{ $dateFormatted }}</div>
                    </td>
                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                      <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-bluedark font-mono font-bold text-xs">
                        {{ $j->startPeriod?->period_number ?? '1' }} sd {{ $j->endPeriod?->period_number ?? '2' }}
                      </span>
                    </td>
                    <td class="px-3.5 py-3 text-xs font-semibold text-bluedark align-middle">
                      {{ $j->teachingAssignment?->subject?->name ?? $selectedAssignment->subject?->name }}
                    </td>
                    <td class="px-3.5 py-3 text-xs text-slate-700 align-middle">
                      {{ $j->creator?->user?->name ?? 'Guru' }}
                    </td>
                    <td class="px-3.5 py-3 text-xs text-slate-800 align-middle font-medium leading-relaxed">
                      {{ $j->material }}
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-emerald-50 text-emerald-700 font-bold font-mono text-xs border border-emerald-200/80">
                        {{ $j->hadir_count }}
                      </span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->sakit_count }}</span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->izin_count }}</span>
                    </td>
                    <td class="px-2 py-3 text-center align-middle">
                      <span class="font-mono text-xs font-semibold">{{ $j->alpha_count }}</span>
                    </td>
                    <td class="px-3.5 py-3 text-xs align-middle">
                      {{ $j->notes ?: '-' }}
                    </td>
                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                      -
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="11" class="text-center py-10 text-xs text-slate-400">
                      Belum ada data jadwal jam pelajaran. Silakan hubungi kurikulum/administrator.
                    </td>
                  </tr>
                @endforelse
              @endif
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form Block: Manajemen Absensi (Matching Image 2) -->
      <div class="rounded-2xl overflow-hidden shadow-sm border border-slate-200" id="cardManajemenAbsensi">
        <!-- Deep Blue Header Bar -->
        <div class="bg-bluedark text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h3 class="font-heading font-bold text-lg text-white" id="formCardTitle">Manajemen Absensi</h3>
              <span id="formEditBadge" style="display: none;" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-amber-950 uppercase tracking-wide">
                Mode Edit
              </span>
            </div>
            <p class="text-xs text-blue-100 mt-0.5" id="formCardDesc">Isi form berikut untuk memanajemen kolom absensi pada journal kelas</p>
          </div>
          <button type="button" id="btnCancelEdit" style="display: none;" class="btn btn-sm px-3.5 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-semibold items-center gap-1.5 transition-colors self-start sm:self-auto">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Batal Edit
          </button>
        </div>

        <form action="{{ route($journalStoreRoute) }}" method="POST" id="formManajemenAbsensi" class="bg-white p-5 md:p-6 space-y-5">
          @csrf
          <input type="hidden" name="_method" value="POST" id="formMethodInput">
          <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment->id }}">
          <input type="hidden" name="journal_date" id="journalDateInput" value="{{ $selectedDate }}">

          <!-- Row 1: Jam Pelajaran & Mata Pelajaran -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="f-label font-semibold text-slate-700">Jam Pelajaran ke- <span class="text-red-500">*</span></label>
              <div class="flex items-center gap-2">
                <select name="start_period_id" id="startPeriodSelect" class="f-select flex-1" required>
                  @foreach($lessonPeriods as $period)
                    @php
                      $isOccupied = isset($occupiedPeriods[$period->period_number]);
                    @endphp
                    <option value="{{ $period->id }}" 
                            data-period="{{ $period->period_number }}"
                            {{ $isOccupied ? 'disabled' : '' }}
                            {{ ($defaultStartPeriodId ? $period->id == $defaultStartPeriodId : (!$isOccupied && $loop->iteration == 1)) ? 'selected' : '' }}
                            class="{{ $isOccupied ? 'text-slate-400 bg-slate-50 italic' : '' }}">
                      {{ $period->period_number }} ({{ substr($period->start_time, 0, 5) }}) {{ $isOccupied ? '(Terisi)' : '' }}
                    </option>
                  @endforeach
                </select>
                <span class="text-slate-500 font-semibold px-1 text-sm">sd</span>
                <select name="end_period_id" id="endPeriodSelect" class="f-select flex-1" required>
                  @foreach($lessonPeriods as $period)
                    @php
                      $isOccupied = isset($occupiedPeriods[$period->period_number]);
                    @endphp
                    <option value="{{ $period->id }}" 
                            data-period="{{ $period->period_number }}"
                            {{ $isOccupied ? 'disabled' : '' }}
                            {{ ($defaultEndPeriodId ? $period->id == $defaultEndPeriodId : (!$isOccupied && $loop->iteration == min(2, $lessonPeriods->count()))) ? 'selected' : '' }}
                            class="{{ $isOccupied ? 'text-slate-400 bg-slate-50 italic' : '' }}">
                      {{ $period->period_number }} ({{ substr($period->end_time, 0, 5) }}) {{ $isOccupied ? '(Terisi)' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div>
              <label class="f-label font-semibold text-slate-700">Mata Pelajaran</label>
              <select class="f-select w-full bg-slate-50" disabled>
                <option selected>{{ $selectedAssignment->subject?->name ?? 'Matematika' }}</option>
              </select>
            </div>
          </div>

          <!-- Row 2: Nama Guru -->
          <div>
            <label class="f-label font-semibold text-slate-700">Nama Guru</label>
            <input type="text" value="{{ auth()->user()->name }}" readonly class="f-input bg-slate-50 text-slate-700 cursor-not-allowed">
          </div>

          <!-- Row 3: Materi -->
          <div>
            <label class="f-label font-semibold text-slate-700">Materi <span class="text-red-500">*</span></label>
            <input type="text" name="material" placeholder="cth. Persamaan Linear" required class="f-input">
          </div>

          <!-- Bagian Kehadiran & Rekap Absensi Siswa -->
          <div class="p-4 md:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-4">
            <!-- Header Kehadiran Siswa dengan Tombol "Samakan dengan Jam Sebelumnya" -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
              <div>
                <label class="font-heading font-bold text-slate-800 text-sm md:text-base block">Rekapitulasi Kehadiran Siswa</label>
                <span class="text-xs text-slate-500">Jumlah siswa hadir &amp; rincian siswa tidak hadir pada jam ini</span>
              </div>

              <!-- Tombol Samakan dengan Jam Sebelumnya (Diposisikan di atas Hadir, Sakit, Izin, Alpha) -->
              <button type="button" id="btnSyncPrevious" 
                      class="btn btn-sm inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 hover:border-indigo-300 transition-all shadow-2xs"
                      title="Salin rekap kehadiran (Hadir, Sakit, Izin, Alpha) dan rincian siswa dari jam pelajaran sebelumnya">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                <span>Samakan dengan Jam Sebelumnya</span>
              </button>
            </div>

            <!-- Toast / Feedback alert when syncing -->
            <div id="syncFeedbackAlert" class="hidden text-xs px-3.5 py-2.5 rounded-xl border flex items-center justify-between">
              <span id="syncFeedbackText"></span>
              <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold ml-2">&times;</button>
            </div>

            <!-- 4 Counter Fields: Hadir, Sakit, Izin, Alpha -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <label class="f-label font-semibold text-slate-700 flex items-center justify-between">
                  <span>Hadir</span>
                  <span class="text-[10px] text-emerald-600 font-medium">Otomatis</span>
                </label>
                <input type="number" name="hadir_count" id="inputHadir" value="{{ $enrolledStudents->count() }}" min="0" class="f-input font-bold text-emerald-700 bg-white">
              </div>
              <div>
                <label class="f-label font-semibold text-slate-700">Sakit</label>
                <input type="number" name="sakit_count" id="inputSakit" value="0" min="0" class="f-input font-bold text-amber-700 bg-white">
              </div>
              <div>
                <label class="f-label font-semibold text-slate-700">Izin</label>
                <input type="number" name="izin_count" id="inputIzin" value="0" min="0" class="f-input font-bold text-blue-700 bg-white">
              </div>
              <div>
                <label class="f-label font-semibold text-slate-700">Alpha</label>
                <input type="number" name="alpha_count" id="inputAlpha" value="0" min="0" class="f-input font-bold text-red-700 bg-white">
              </div>
            </div>

            <!-- Keterangan (Siswa Tidak Hadir) -->
            <div class="pt-2 border-t border-slate-200/70">
              <div class="flex items-center justify-between mb-2.5">
                <div>
                  <label class="f-label font-semibold text-slate-800 text-sm mb-0">Keterangan Siswa Tidak Hadir</label>
                  <p class="text-[11px] text-slate-500">Pilih siswa yang Sakit, Izin, atau Alpha beserta keterangannya (opsional)</p>
                </div>
                <!-- Tombol Tambah Siswa (+ TIDAK DOUBLE) -->
                <button type="button" id="btnAddStudentRow" 
                        class="btn btn-sm inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-blueprim hover:bg-bluedark text-white transition-all shadow-2xs">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  <span>Tambah Siswa</span>
                </button>
              </div>

              <!-- Header label columns for desktop -->
              <div id="keteranganHeaderCols" class="hidden md:grid md:grid-cols-[1.5fr_1fr_1.8fr_auto] gap-2.5 px-3 py-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-100/70 rounded-xl mb-2">
                <div>Siswa</div>
                <div>Status</div>
                <div>Keterangan / Alasan (Opsional)</div>
                <div class="w-8 text-center">Hapus</div>
              </div>

              <!-- Container baris siswa tidak hadir -->
              <div id="keteranganRowsContainer" class="space-y-2.5"></div>
              <div id="keteranganEmptyNotice" class="text-xs text-slate-400 py-3 text-center bg-white rounded-xl border border-dashed border-slate-200">
                Belum ada siswa yang ditambahkan ke kolom keterangan (Seluruh siswa dihitung Hadir).
              </div>
            </div>
          </div>

          <!-- Row 6: Catatan Tambahan -->
          <div>
            <label class="f-label font-semibold text-slate-700">Catatan Tambahan / Kegiatan Kelas</label>
            <textarea name="notes" rows="2" placeholder="Catatan kelas, kendala, atau ketuntasan materi..." class="f-textarea"></textarea>
          </div>

          <!-- Bottom Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="reset" id="btnResetForm" class="btn btn-outline px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 font-medium text-sm">
              Reset
            </button>
            <button type="submit" id="btnSubmitForm" 
                    @if($isAttendanceBlocked) disabled @endif
                    class="btn btn-primary px-6 py-2.5 rounded-xl font-semibold text-sm shadow-sm inline-flex items-center gap-2 {{ $isAttendanceBlocked ? 'bg-slate-300 text-slate-500 cursor-not-allowed opacity-75' : 'bg-blueprim hover:bg-bluedark text-white' }}">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              <span id="btnSubmitText">{{ $isAttendanceBlocked ? 'Belum Dapat Diisi' : 'Simpan Journal' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const enrolledStudents = @json($enrolledStudents ?? []);
  const previousAttendances = @json($previousJournalAttendances ?? []);
  const previousInfo = @json($previousJournalInfo ?? null);
  const totalStudents = enrolledStudents.length || 36;

  const container = document.getElementById('keteranganRowsContainer');
  const emptyNotice = document.getElementById('keteranganEmptyNotice');
  const btnAdd = document.getElementById('btnAddStudentRow');
  const btnSync = document.getElementById('btnSyncPrevious');
  const btnReset = document.getElementById('btnResetForm');

  const inputHadir = document.getElementById('inputHadir');
  const inputSakit = document.getElementById('inputSakit');
  const inputIzin = document.getElementById('inputIzin');
  const inputAlpha = document.getElementById('inputAlpha');

  const feedbackAlert = document.getElementById('syncFeedbackAlert');
  const feedbackText = document.getElementById('syncFeedbackText');

  let rowIndex = 0;

  const headerCols = document.getElementById('keteranganHeaderCols');
  const formCardTitle = document.getElementById('formCardTitle');
  const formCardDesc = document.getElementById('formCardDesc');
  const formEditBadge = document.getElementById('formEditBadge');
  const btnCancelEdit = document.getElementById('btnCancelEdit');
  const formMethodInput = document.getElementById('formMethodInput');
  const formManajemenAbsensi = document.getElementById('formManajemenAbsensi');
  const btnSubmitText = document.getElementById('btnSubmitText');
  const startPeriodSelect = document.getElementById('startPeriodSelect');
  const endPeriodSelect = document.getElementById('endPeriodSelect');
  const defaultAction = "{{ route($journalStoreRoute) }}";
  const isAttendanceBlocked = @json($isAttendanceBlocked);

  if (isAttendanceBlocked) {
    if (btnAdd) {
      btnAdd.disabled = true;
      btnAdd.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (btnSync) {
      btnSync.disabled = true;
      btnSync.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (btnReset) {
      btnReset.disabled = true;
      btnReset.classList.add('opacity-50', 'cursor-not-allowed');
    }
    if (formManajemenAbsensi) {
      formManajemenAbsensi.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        el.disabled = true;
      });
      formManajemenAbsensi.addEventListener('submit', function(e) {
        e.preventDefault();
        alert(@json('Pengisian absensi tidak dapat dilakukan pada tanggal ini.'));
      });
    }
  }

  function renderRow(selectedStudentId = '', selectedStatus = 'IZIN', studentNote = '') {
    if (emptyNotice) {
      emptyNotice.classList.add('hidden');
    }
    if (headerCols) {
      headerCols.classList.remove('hidden');
    }

    const rowId = `keterangan-row-${rowIndex++}`;
    const rowDiv = document.createElement('div');
    rowDiv.id = rowId;
    rowDiv.className = 'keterangan-row grid grid-cols-1 md:grid-cols-[1.5fr_1fr_1.8fr_auto] gap-2.5 items-center p-2.5 rounded-xl bg-white border border-slate-200 shadow-2xs transition-all';

    // Student Select Options
    let studentOptions = '<option value="" disabled ' + (!selectedStudentId ? 'selected' : '') + '>-- Pilih Siswa --</option>';
    enrolledStudents.forEach(st => {
      const isSelected = String(st.id) === String(selectedStudentId) ? 'selected' : '';
      const nisnText = st.nisn || st.nis ? ` (${st.nisn || st.nis})` : '';
      studentOptions += `<option value="${st.id}" ${isSelected}>${st.full_name}${nisnText}</option>`;
    });

    const isIzin = selectedStatus.toUpperCase() === 'IZIN' ? 'selected' : '';
    const isSakit = selectedStatus.toUpperCase() === 'SAKIT' ? 'selected' : '';
    const isAlpha = selectedStatus.toUpperCase() === 'ALPHA' ? 'selected' : '';
    const safeNote = studentNote ? String(studentNote).replace(/"/g, '&quot;') : '';

    rowDiv.innerHTML = `
      <div>
        <label class="md:hidden text-[11px] font-semibold text-slate-500 mb-1 block">Siswa</label>
        <select name="absences[${rowIndex}][student_id]" class="f-select student-select w-full bg-slate-50 text-xs" required>
          ${studentOptions}
        </select>
      </div>
      <div>
        <label class="md:hidden text-[11px] font-semibold text-slate-500 mb-1 block">Status</label>
        <select name="absences[${rowIndex}][status]" class="f-select status-select w-full bg-slate-50 text-xs font-medium" required>
          <option value="IZIN" ${isIzin}>Izin</option>
          <option value="SAKIT" ${isSakit}>Sakit</option>
          <option value="ALPHA" ${isAlpha}>Alpha</option>
        </select>
      </div>
      <div>
        <label class="md:hidden text-[11px] font-semibold text-slate-500 mb-1 block">Keterangan / Alasan (Opsional)</label>
        <input type="text" name="absences[${rowIndex}][note]" value="${safeNote}" placeholder="Keterangan (opsional, cth: Sakit demam, urusan keluarga)" class="f-input note-input w-full bg-slate-50 text-xs">
      </div>
      <div class="flex justify-end md:justify-center">
        <button type="button" class="btn-remove-row p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Siswa">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            <line x1="10" y1="11" x2="10" y2="17"/>
            <line x1="14" y1="11" x2="14" y2="17"/>
          </svg>
        </button>
      </div>
    `;

    // Listeners for changes in this row
    const statusSelect = rowDiv.querySelector('.status-select');
    statusSelect.addEventListener('change', recalculateCounts);

    const btnRemove = rowDiv.querySelector('.btn-remove-row');
    btnRemove.addEventListener('click', function() {
      rowDiv.remove();
      if (container.querySelectorAll('.keterangan-row').length === 0) {
        if (emptyNotice) emptyNotice.classList.remove('hidden');
        if (headerCols) headerCols.classList.add('hidden');
      }
      recalculateCounts();
    });

    container.appendChild(rowDiv);
  }

  function recalculateCounts() {
    const rows = container.querySelectorAll('.keterangan-row');
    let sakit = 0;
    let izin = 0;
    let alpha = 0;

    rows.forEach(r => {
      const status = r.querySelector('.status-select')?.value?.toUpperCase();
      if (status === 'SAKIT') sakit++;
      else if (status === 'IZIN') izin++;
      else if (status === 'ALPHA') alpha++;
    });

    if (inputSakit) inputSakit.value = sakit;
    if (inputIzin) inputIzin.value = izin;
    if (inputAlpha) inputAlpha.value = alpha;
    if (inputHadir) inputHadir.value = Math.max(0, totalStudents - (sakit + izin + alpha));
  }

  // Tambah Siswa button
  if (btnAdd) {
    btnAdd.addEventListener('click', function() {
      renderRow();
      recalculateCounts();
    });
  }

  // Samakan dengan Jam Sebelumnya button
  if (btnSync) {
    btnSync.addEventListener('click', function() {
      if (!previousInfo) {
        if (feedbackAlert && feedbackText) {
          feedbackAlert.className = 'text-xs px-3.5 py-2.5 rounded-xl border mb-3 flex items-center justify-between bg-amber-50 border-amber-200 text-amber-800';
          feedbackText.textContent = 'Belum ada data jurnal pada jam sebelumnya untuk disalin.';
          feedbackAlert.classList.remove('hidden');
        }
        return;
      }

      if (!previousAttendances || previousAttendances.length === 0) {
        // Entire class was Present or no specific students recorded
        container.innerHTML = '';
        if (emptyNotice) emptyNotice.classList.remove('hidden');
        if (headerCols) headerCols.classList.add('hidden');

        if (inputHadir) inputHadir.value = previousInfo.hadir_count !== undefined ? previousInfo.hadir_count : totalStudents;
        if (inputSakit) inputSakit.value = previousInfo.sakit_count !== undefined ? previousInfo.sakit_count : 0;
        if (inputIzin) inputIzin.value = previousInfo.izin_count !== undefined ? previousInfo.izin_count : 0;
        if (inputAlpha) inputAlpha.value = previousInfo.alpha_count !== undefined ? previousInfo.alpha_count : 0;

        if (feedbackAlert && feedbackText) {
          feedbackAlert.className = 'text-xs px-3.5 py-2.5 rounded-xl border mb-3 flex items-center justify-between bg-emerald-50 border-emerald-200 text-emerald-800';
          const infoText = ` (Jam ${previousInfo.start_period} sd ${previousInfo.end_period} - ${previousInfo.subject} oleh ${previousInfo.teacher})`;
          feedbackText.textContent = `Berhasil menyamakan data kehadiran dengan jam sebelumnya${infoText}. Seluruh siswa tercatat Hadir (${inputHadir?.value ?? totalStudents} siswa).`;
          feedbackAlert.classList.remove('hidden');
        }
        return;
      }

      // Clear existing rows
      container.innerHTML = '';

      // Populate with previous attendances
      previousAttendances.forEach(att => {
        renderRow(att.student_id, att.status, att.note);
      });

      recalculateCounts();

      // Ensure explicit counts match previous session
      if (inputHadir && previousInfo.hadir_count !== undefined) inputHadir.value = previousInfo.hadir_count;
      if (inputSakit && previousInfo.sakit_count !== undefined) inputSakit.value = previousInfo.sakit_count;
      if (inputIzin && previousInfo.izin_count !== undefined) inputIzin.value = previousInfo.izin_count;
      if (inputAlpha && previousInfo.alpha_count !== undefined) inputAlpha.value = previousInfo.alpha_count;

      if (feedbackAlert && feedbackText) {
        feedbackAlert.className = 'text-xs px-3.5 py-2.5 rounded-xl border mb-3 flex items-center justify-between bg-emerald-50 border-emerald-200 text-emerald-800';
        const infoText = ` (Jam ${previousInfo.start_period} sd ${previousInfo.end_period} - ${previousInfo.subject} oleh ${previousInfo.teacher})`;
        const countDetails = `Hadir: ${inputHadir?.value ?? 0}, Sakit: ${inputSakit?.value ?? 0}, Izin: ${inputIzin?.value ?? 0}, Alpha: ${inputAlpha?.value ?? 0}`;
        feedbackText.textContent = `Berhasil menyamakan kehadiran dengan jam sebelumnya${infoText}. Rekap: ${countDetails}. Sebanyak ${previousAttendances.length} siswa disalin ke keterangan.`;
        feedbackAlert.classList.remove('hidden');
      }
    });
  }

  const occupiedPeriods = @json($occupiedPeriods ?? []);
  const defaultStartPeriodId = @json($defaultStartPeriodId ?? null);
  const defaultEndPeriodId = @json($defaultEndPeriodId ?? null);

  window.selectPeriodRange = function(startId, endId) {
    if (startPeriodSelect && startId) startPeriodSelect.value = startId;
    if (endPeriodSelect && endId) endPeriodSelect.value = endId;
    const card = document.getElementById('cardManajemenAbsensi');
    if (card) {
      card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    const matInput = formManajemenAbsensi ? formManajemenAbsensi.querySelector('input[name="material"]') : null;
    if (matInput) {
      setTimeout(() => matInput.focus(), 350);
    }
  };

  // Edit Jurnal Mode Handlers
  function switchToEdit(journalData) {
    if (!journalData) return;

    if (formMethodInput) formMethodInput.value = 'PUT';
    if (formManajemenAbsensi) formManajemenAbsensi.action = journalData.update_url;
    if (formCardTitle) formCardTitle.textContent = `Edit Jurnal Kelas (Jam ${journalData.start_period_number} sd ${journalData.end_period_number})`;
    if (formCardDesc) formCardDesc.textContent = 'Perbarui data materi, catatan tambahan, dan absensi untuk jam pelajaran ini';
    if (formEditBadge) formEditBadge.style.display = 'inline-block';
    if (btnCancelEdit) btnCancelEdit.style.display = 'inline-flex';
    if (btnSubmitText) btnSubmitText.textContent = 'Simpan Perubahan';

    // Re-enable options for the current journal being edited
    const editStartNum = parseInt(journalData.start_period_number);
    const editEndNum = parseInt(journalData.end_period_number || editStartNum);
    [startPeriodSelect, endPeriodSelect].forEach(select => {
      if (!select) return;
      Array.from(select.options).forEach(opt => {
        const pNum = parseInt(opt.dataset.period);
        if (pNum >= Math.min(editStartNum, editEndNum) && pNum <= Math.max(editStartNum, editEndNum)) {
          opt.disabled = false;
          opt.classList.remove('text-slate-400', 'bg-slate-50', 'italic');
          opt.textContent = opt.textContent.replace(' (Terisi)', '');
        }
      });
    });

    if (startPeriodSelect) startPeriodSelect.value = journalData.start_period_id;
    if (endPeriodSelect) endPeriodSelect.value = journalData.end_period_id;

    const materialInput = formManajemenAbsensi.querySelector('input[name="material"]');
    if (materialInput) materialInput.value = journalData.material || '';

    const notesInput = formManajemenAbsensi.querySelector('textarea[name="notes"]');
    if (notesInput) notesInput.value = journalData.notes || '';

    if (inputHadir) inputHadir.value = journalData.hadir_count;
    if (inputSakit) inputSakit.value = journalData.sakit_count;
    if (inputIzin) inputIzin.value = journalData.izin_count;
    if (inputAlpha) inputAlpha.value = journalData.alpha_count;

    container.innerHTML = '';
    if (journalData.attendances && journalData.attendances.length > 0) {
      journalData.attendances.forEach(att => {
        renderRow(att.student_id, att.status, att.note);
      });
    } else {
      if (emptyNotice) emptyNotice.classList.remove('hidden');
      if (headerCols) headerCols.classList.add('hidden');
    }

    const card = document.getElementById('cardManajemenAbsensi');
    if (card) {
      card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function cancelEdit() {
    if (formMethodInput) formMethodInput.value = 'POST';
    if (formManajemenAbsensi) {
      formManajemenAbsensi.action = defaultAction;
      formManajemenAbsensi.reset();
    }
    if (formCardTitle) formCardTitle.textContent = 'Manajemen Absensi';
    if (formCardDesc) formCardDesc.textContent = 'Isi form berikut untuk memanajemen kolom absensi pada journal kelas';
    if (formEditBadge) formEditBadge.style.display = 'none';
    if (btnCancelEdit) btnCancelEdit.style.display = 'none';
    if (btnSubmitText) btnSubmitText.textContent = 'Simpan Journal';

    // Restore occupied periods disabled state
    [startPeriodSelect, endPeriodSelect].forEach(select => {
      if (!select) return;
      Array.from(select.options).forEach(opt => {
        const pNum = parseInt(opt.dataset.period);
        if (occupiedPeriods[pNum]) {
          opt.disabled = true;
          opt.classList.add('text-slate-400', 'bg-slate-50', 'italic');
          if (!opt.textContent.includes('(Terisi)')) {
            opt.textContent = opt.textContent.trim() + ' (Terisi)';
          }
        }
      });
    });

    if (startPeriodSelect && defaultStartPeriodId) startPeriodSelect.value = defaultStartPeriodId;
    if (endPeriodSelect && defaultEndPeriodId) endPeriodSelect.value = defaultEndPeriodId;

    container.innerHTML = '';
    if (emptyNotice) emptyNotice.classList.remove('hidden');
    if (headerCols) headerCols.classList.add('hidden');
    if (inputHadir) inputHadir.value = totalStudents;
    if (inputSakit) inputSakit.value = 0;
    if (inputIzin) inputIzin.value = 0;
    if (inputAlpha) inputAlpha.value = 0;
  }

  if (btnCancelEdit) {
    btnCancelEdit.addEventListener('click', cancelEdit);
  }

  document.querySelectorAll('.btn-edit-journal').forEach(btn => {
    btn.addEventListener('click', function() {
      const payload = JSON.parse(this.dataset.journal || '{}');
      switchToEdit(payload);
    });
  });

  // Reset Form
  if (btnReset) {
    btnReset.addEventListener('click', function() {
      setTimeout(() => {
        if (formEditBadge) formEditBadge.style.display = 'none';
        if (btnCancelEdit) btnCancelEdit.style.display = 'none';
        if (startPeriodSelect && defaultStartPeriodId) startPeriodSelect.value = defaultStartPeriodId;
        if (endPeriodSelect && defaultEndPeriodId) endPeriodSelect.value = defaultEndPeriodId;
        container.innerHTML = '';
        if (emptyNotice) emptyNotice.classList.remove('hidden');
        if (headerCols) headerCols.classList.add('hidden');
        if (feedbackAlert) feedbackAlert.classList.add('hidden');
        if (inputHadir) inputHadir.value = totalStudents;
        if (inputSakit) inputSakit.value = 0;
        if (inputIzin) inputIzin.value = 0;
        if (inputAlpha) inputAlpha.value = 0;
      }, 50);
    });
  }

  // Initial check: if form is opened and there are old input values (e.g. after validation error)
  @if(old('absences'))
    const oldAbsences = @json(old('absences'));
    Object.values(oldAbsences).forEach(abs => {
      renderRow(abs.student_id, abs.status, abs.note);
    });
    recalculateCounts();
  @endif
});

// Dropdown Export Jurnal Toggle
function toggleExportJournalDropdown(e) {
  e.stopPropagation();
  const menu = document.getElementById('exportJournalDropdownMenu');
  if (menu) {
    const isHidden = menu.style.display === 'none' || menu.classList.contains('hidden');
    menu.style.display = isHidden ? 'block' : 'none';
  }
}

document.addEventListener('click', function(e) {
  const wrapper = document.getElementById('exportJournalDropdownWrapper');
  const menu = document.getElementById('exportJournalDropdownMenu');
  if (wrapper && menu && !wrapper.contains(e.target)) {
    menu.style.display = 'none';
  }
});
</script>
@endpush
