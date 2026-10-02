@extends('layouts.admin')

@section('title', 'Jadwal Mengajar & Jam Pelajaran')

@push('styles')
<style>
/* ==========================================================================
   EXCEL-LIKE SPREADSHEET TIMETABLE STYLING (MATCHING GURU DAFTAR NILAI)
   ========================================================================== */

/* Header Kolom Aktif (Warna Navy Deep #0D47A1) */
.active-col-header {
  background-color: #0D47A1 !important;
  color: #ffffff !important;
  border-color: #0D47A1 !important;
  box-shadow: inset 0 0 0 2px #2196F3, 0 3px 10px rgba(13, 71, 161, 0.25) !important;
}
.active-col-header * {
  color: #ffffff !important;
}

/* Seluruh Kolom yang Sedang Aktif Disorot */
.active-col-cell {
  background-color: #F0F7FF !important;
}

/* Baris Jam Pelajaran yang Sedang Aktif */
.active-row-period {
  background-color: #F8FAFC !important;
}

/* Kotak / Sel Terpilih (Excel Active Cell Effect) */
.active-score-cell {
  background-color: #DBEAFE !important;
  border: 2px solid #2563EB !important;
  box-shadow: inset 0 0 0 1px #2563EB, 0 2px 8px rgba(37, 99, 235, 0.25) !important;
  position: relative;
  z-index: 10;
}

/* Interaktivitas Kotak / Sel Jadwal */
.score-cell-interactive {
  cursor: pointer;
  transition: all 0.15s ease-in-out;
  user-select: none;
  height: 1px; /* Table height stretch rule */
}
.score-cell-interactive:hover {
  background-color: #E0E7FF !important;
  border-color: #93C5FD !important;
}
.score-cell-scheduled {
  background-color: #EFF6FF !important;
  border-left: 3px solid #2563EB !important;
  vertical-align: top;
  height: 100% !important;
}

/* Tombol Filter Kolom Cepat (Hari) */
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

/* Card Jadwal di dalam Kotak Sel: Full Height & Filled Visual */
.schedule-card-inner {
  border-radius: 0.625rem;
  transition: all 0.15s ease;
  border: 1px solid #93C5FD;
  border-left: 3px solid #2563EB;
  background: linear-gradient(180deg, #F0F7FF 0%, #E4EFFC 100%);
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
}
.schedule-card-inner:hover {
  border-color: #2563EB;
  background: linear-gradient(180deg, #E0EFFF 0%, #D2E4FA 100%);
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.18);
}

/* Badge Jam / Ruangan */
.badge-period-span {
  font-size: 9px;
  font-weight: 700;
  color: #1E40AF;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 4px;
  padding: 1px 5px;
}
.badge-room-pill {
  font-size: 9px;
  font-weight: 600;
  color: #065F46;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  border-radius: 4px;
  padding: 1px 5px;
}

/* Border Tabel Grid Halus ala Excel */
.spreadsheet-grid {
  border-collapse: separate;
  border-spacing: 0;
}
.spreadsheet-grid th,
.spreadsheet-grid td {
  border-right: 1px solid #E2E8F0;
  border-bottom: 1px solid #E2E8F0;
}
.spreadsheet-grid tr:first-child th {
  border-top: 1px solid #E2E8F0;
}
.spreadsheet-grid th:first-child,
.spreadsheet-grid td:first-child {
  border-left: 1px solid #E2E8F0;
}

/* Sticky First Column for Period Numbers */
.sticky-col-period {
  position: sticky;
  left: 0;
  z-index: 20;
  background-color: #FFFFFF;
}
thead .sticky-col-period {
  z-index: 30;
  background-color: #0D47A1 !important;
}

/* Animasi Pulse Hint untuk Sel Kosong saat Hover */
.cell-empty-hint {
  opacity: 0;
  transition: opacity 0.15s ease;
}
.score-cell-interactive:hover .cell-empty-hint {
  opacity: 1;
}

@media print {
  aside, .db-sidebar, header, .no-print {
    display: none !important;
  }
  body, .teacher-portal {
    background: #fff !important;
  }
  .panel {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
  }
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Flash Message Notification -->
    @if(session('success'))
      <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
      </div>
    @endif

    @if(session('error'))
      <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          <span class="font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
      </div>
    @endif

    <!-- Header Section (Mirip Desain Guru Daftar Nilai) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 no-print relative z-30">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Jadwal Pelajaran &amp; Jam Belajar</h1>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola jadwal mingguan tiap rombel dan jam pelajaran sekolah dengan konsep Spreadsheet Grid (Excel)
            </p>
        </div>

        <!-- Tombol Aksi Atas (1 Baris Sejajar Rapi, Floating Dropdown z-99999 Bebas Scroll & Bebas Tertimpa) -->
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap shrink-0 relative">
            <!-- Tombol Tambah Baris Jam Pelajaran (Kolom ke Bawah) -->
            <button type="button" onclick="openAddPeriodModal()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0 text-blueprim" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Jam</span>
            </button>

            <!-- Tombol Kelola Kolom Hari (5 atau 6 Hari) -->
            <button type="button" onclick="openColumnManagerModal()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0 text-bluedark/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                <span>Kelola Hari</span>
            </button>

            <!-- Tombol Tambah Kolom Cepat (Sabtu) jika masih 5 hari, atau sebaliknya -->
            @if($daysCount < 6)
                <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 6]) }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Sabtu</span>
                </a>
            @else
                <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 5]) }}" class="btn btn-outline btn-sm flex items-center gap-1.5 text-slate-600 shadow-2xs whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Sembunyikan Sabtu</span>
                </a>
            @endif

            <!-- Dropdown Export Jadwal (PDF / Excel) — Floating Fixed Menu Bebas Clipping/Scroll -->
            <div class="relative inline-block text-left shrink-0" id="exportScheduleDropdownWrapper">
                <button type="button" id="exportScheduleBtn" onclick="toggleExportDropdown(event)" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs hover:bg-slate-50 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0 text-bluedark/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Export Jadwal</span>
                    <svg class="w-3 h-3 text-bluedark/50 transition-transform duration-200" id="exportScheduleChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="exportScheduleDropdownMenu" class="hidden fixed z-[99999] w-48 rounded-2xl bg-white shadow-2xl border border-bluelight py-1.5">
                    <a href="{{ route('admin.academic.schedules.export.pdf', ['class_id' => $selectedClass?->id, 'days_count' => $daysCount]) }}" target="_blank" class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs font-semibold text-bluedark hover:bg-rose-50 hover:text-rose-600 transition-colors">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Export ke PDF</span>
                    </a>
                    <a href="{{ route('admin.academic.schedules.export.excel', ['class_id' => $selectedClass?->id, 'days_count' => $daysCount]) }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs font-semibold text-bluedark hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="17"/><line x1="8" y1="17" x2="16" y2="13"/></svg>
                        <span>Export ke Excel</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Biru Kelas Aktif & Filter Rombel (Matching Panel Guru) -->
    <div class="panel p-4 flex items-center justify-between gap-4 flex-wrap" style="background:#2196F3; color:#fff;">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs" style="background:rgba(255,255,255,.2); color:#fff;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="font-heading font-bold text-base md:text-lg text-white truncate">
                    Jadwal Mingguan: {{ $selectedClass?->name ?? 'Belum ada kelas' }}
                </div>
                <div class="text-xs text-blue-100 flex items-center gap-2 flex-wrap">
                    <span>Jurusan: <strong>{{ $selectedClass?->department?->name ?? 'Semua' }}</strong></span>
                    <span>&bull;</span>
                    <span>Mode Kolom: <strong>{{ $daysCount }} Hari ({{ implode(' - ', array_values($days)) }})</strong></span>
                    <span>&bull;</span>
                    <span>Total Jadwal: <strong>{{ $schedules->count() }} Mapel</strong></span>
                </div>
            </div>
        </div>

        <!-- Filter Kelas Dropdown -->
        <form method="GET" action="{{ route('admin.academic.schedules.index') }}" class="flex items-center gap-2 bg-white/10 p-1.5 rounded-xl border border-white/20 no-print">
            <input type="hidden" name="days_count" value="{{ $daysCount }}">
            <label class="text-xs font-semibold text-white px-2">Pilih Rombel:</label>
            <select name="class_id" onchange="this.form.submit()" class="f-select text-xs py-1.5 px-3 bg-white text-bluedark font-semibold w-56 rounded-lg border-0 shadow-xs">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->department?->code ?? $c->department?->name }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Section Spreadsheet Timetable -->
    <div class="panel p-4 sm:p-5 space-y-4">

        <!-- Panduan & Pilihan Kolom Cepat (Hari) -->
        <div class="bg-bluelight/40 p-3 rounded-xl border border-bluelight/80 no-print">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-bluedark">Pilih Cepat Kolom Hari:</span>
                    <span class="text-[11px] text-bluedark/60 hidden sm:inline">(Klik tombol hari untuk sorot kolom atau <strong>klik 2 kali</strong> pada kotak jadwal di tabel untuk mengedit ala Excel)</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-bluedark/70 font-medium">
                    <span class="inline-block w-2.5 h-2.5 rounded bg-blue-100 border border-blue-500"></span>
                    <span>Default 5 Hari (Senin - Jumat) &bull; Maks 6 Hari</span>
                </div>
            </div>
            
            <div class="flex items-center gap-2 overflow-x-auto pb-1 db-scroll" id="quickDaySelector">
                <button type="button" onclick="highlightDayColumn(null)" class="quick-col-btn active-quick-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-bluelight flex items-center gap-1.5 shrink-0 bg-white">
                    <span class="quick-col-name">Semua Hari</span>
                    <span class="quick-task-badge px-1.5 py-0.2 rounded-full text-[10px]">{{ $schedules->count() }}</span>
                </button>

                @foreach($days as $dayNum => $dayName)
                    @php
                        $dayMapelCount = $schedules->where('day_of_week', $dayNum)->count();
                    @endphp
                    <button type="button" onclick="highlightDayColumn({{ $dayNum }})" id="quickBtnDay{{ $dayNum }}" class="quick-col-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-bluelight flex items-center gap-1.5 shrink-0 bg-white text-bluedark hover:border-blueprim">
                        <span class="quick-col-name">{{ $dayName }}</span>
                        <span class="quick-task-badge px-1.5 py-0.2 rounded-full text-[10px]">{{ $dayMapelCount }}</span>
                    </button>
                @endforeach

                @if($daysCount < 6)
                    <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 6]) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-dashed border-blue-400 text-blue-600 hover:bg-blue-50 flex items-center gap-1 shrink-0" title="Tambah Kolom Hari Sabtu">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Kolom Sabtu</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Banner Sel Terpilih Interaktif (Exact Style Guru Daftar Nilai) -->
        <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-200 flex items-center justify-between flex-wrap gap-3" id="selectedCellBanner">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blueprim text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-bluedark" id="bannerSelectedTitle">
                        Sel Terpilih: <span id="bannerCellInfo">Belum ada kotak yang dipilih</span>
                    </div>
                    <div class="text-[11px] text-bluedark/60" id="bannerSelectedSubtitle">
                        Klik pada kotak tabel untuk memilih, atau <strong>klik 2 kali (Double Click)</strong> untuk menambah/mengedit jadwal pelajaran langsung mirip Excel.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2" id="bannerActionButtons">
                <span class="badge badge-gray text-xs font-bold px-2.5 py-1" id="bannerStatusBadge">Status: Belum Dipilih</span>
                <button type="button" id="btnEditSelectedCell" onclick="openSelectedCellModal()" class="btn btn-primary btn-sm text-xs py-1 px-3 hidden">
                    <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit / Isi Jadwal (Klik 2x)
                </button>
                <button type="button" id="btnDeleteSelectedCell" onclick="deleteSelectedSchedule()" class="btn btn-outline btn-sm text-xs py-1 px-3 text-rose-600 border-rose-200 hover:bg-rose-50 hidden">
                    <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Hapus
                </button>
            </div>
        </div>

        @php
            // Hitung Matrix Penjadwalan dengan Dukungan Rentang Jam (Rowspan)
            $matrix = [];
            $periodsSorted = $periods->sortBy('sort_order')->values();
            $periodIndexMap = [];
            foreach ($periodsSorted as $idx => $p) {
                $periodIndexMap[$p->id] = $idx;
            }

            foreach ($schedules as $sch) {
                $day = $sch->day_of_week;
                $startId = $sch->start_period_id;
                $endId = $sch->end_period_id;

                $startIdx = $periodIndexMap[$startId] ?? null;
                $endIdx = $periodIndexMap[$endId] ?? null;

                if ($startIdx !== null && $endIdx !== null && $endIdx >= $startIdx) {
                    $span = $endIdx - $startIdx + 1;
                    $matrix[$startId][$day] = [
                        'type' => 'start',
                        'schedule' => $sch,
                        'span' => $span,
                    ];

                    for ($i = $startIdx + 1; $i <= $endIdx; $i++) {
                        $pId = $periodsSorted[$i]->id;
                        $matrix[$pId][$day] = [
                            'type' => 'covered',
                            'schedule' => $sch,
                        ];
                    }
                } elseif ($startIdx !== null) {
                    $matrix[$startId][$day] = [
                        'type' => 'start',
                        'schedule' => $sch,
                        'span' => 1,
                    ];
                }
            }
        @endphp

        <!-- Spreadsheet Table Container -->
        <div class="overflow-x-auto db-scroll border border-bluelight rounded-2xl shadow-2xs max-h-[620px] bg-white">
            <table class="spreadsheet-grid w-full text-xs" id="scheduleSpreadsheetTable">
                
                <!-- Table Header: Deep Blue (#0D47A1) Sticky Header -->
                <thead class="sticky top-0 z-30 shadow-xs">
                    <tr style="background:#0D47A1 !important; color:#ffffff !important;">
                        <!-- Kolom Jam Pelajaran / Waktu (Sticky Left) -->
                        <th class="sticky-col-period py-3 px-3.5 text-left font-bold text-xs uppercase tracking-wider w-36 sm:w-44 border-r border-blue-900 shadow-sm" style="background:#0D47A1 !important; color:#ffffff !important;">
                            <div class="flex items-center justify-between">
                                <span class="font-heading">Jam / Waktu</span>
                                <span class="text-[10px] text-blue-200 font-normal">Pukul WIB</span>
                            </div>
                        </th>

                        <!-- Kolom Hari (Senin s/d Jumat/Sabtu) -->
                        @foreach($days as $dayNum => $dayName)
                            @php
                                $dayCount = $schedules->where('day_of_week', $dayNum)->count();
                            @endphp
                            <th class="day-col-header py-3 px-3 text-center font-bold text-xs cursor-pointer select-none transition-colors border-r border-blue-900/60"
                                data-day="{{ $dayNum }}"
                                onclick="highlightDayColumn({{ $dayNum }})"
                                title="Klik untuk menyorot kolom {{ $dayName }}"
                                style="background:#0D47A1; color:#ffffff;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="font-heading text-sm">{{ $dayName }}</span>
                                    <span class="badge text-[10px] font-bold px-1.5 py-0.2 rounded-full" style="background:rgba(255,255,255,0.22); color:#fff;">
                                        {{ $dayCount }}
                                    </span>
                                </div>
                            </th>
                        @endforeach

                        @if($daysCount < 6)
                            <th class="py-3 px-3 text-center font-medium text-xs border-r border-blue-900/60" style="background:#0A387E; color:#93C5FD;">
                                <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 6]) }}" class="inline-flex items-center gap-1 text-[11px] hover:text-white transition-colors" title="Tambah Kolom Hari Sabtu">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Hari Ke-6 (Sabtu)</span>
                                </a>
                            </th>
                        @endif
                    </tr>
                </thead>

                <!-- Table Body: Baris Jam Pelajaran x Kolom Hari -->
                <tbody id="scheduleTableBody">
                    @forelse($periodsSorted as $p)
                        @php
                            $startTime = \Carbon\Carbon::parse($p->start_time)->format('H:i');
                            $endTime = \Carbon\Carbon::parse($p->end_time)->format('H:i');
                        @endphp

                        @if($p->is_break)
                            {{-- Baris Jam Istirahat (Kuning, Unclickable, Tanpa Nomor Jam Pelajaran) --}}
                            <tr class="period-row period-row-break bg-amber-50/70 border-b border-amber-200/90" data-period-id="{{ $p->id }}" data-is-break="true">
                                
                                <!-- Kolom Label Jam (Sticky Left) Kuning -->
                                <td class="sticky-col-period py-2.5 px-3 border-r border-amber-200 bg-amber-100/90 text-amber-950 shadow-2xs">
                                    <div class="flex items-center justify-between group">
                                        <div>
                                            <div class="font-heading font-bold text-xs text-amber-950 flex items-center gap-1.5">
                                                <span class="badge text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-amber-200 text-amber-950 border border-amber-300">Istirahat</span>
                                            </div>
                                            <div class="text-[10px] font-mono text-amber-800 font-semibold mt-0.5">
                                                {{ $startTime }} - {{ $endTime }}
                                            </div>
                                        </div>

                                        <!-- Quick Action Hapus Jam Istirahat -->
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 no-print">
                                            <form action="{{ route('admin.academic.schedules.periods.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus jam istirahat ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded hover:bg-rose-100 text-rose-500" title="Hapus Jam">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom Hari: Banner Kuning Jam Istirahat Penuh (Tidak Bisa Diklik) -->
                                <td colspan="{{ count($days) + ($daysCount < 6 ? 1 : 0) }}" class="py-2.5 px-4 bg-amber-50/80 border-r border-b border-amber-200 select-none cursor-not-allowed">
                                    <div class="w-full py-2 px-4 rounded-xl bg-amber-100/90 border border-amber-300 text-amber-950 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs">
                                        <svg class="w-4 h-4 text-amber-800 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                                        <span class="tracking-wider uppercase font-heading text-xs">ISTIRAHAT &bull; {{ $startTime }} - {{ $endTime }} WIB</span>
                                    </div>
                                </td>
                            </tr>
                        @else
                            {{-- Baris Jam Pelajaran Reguler --}}
                            <tr class="period-row transition-colors hover:bg-slate-50/70" data-period-id="{{ $p->id }}" data-period-number="{{ $p->period_number }}">
                                
                                <!-- Kolom Label Jam (Sticky Left) -->
                                <td class="sticky-col-period py-2.5 px-3 border-r border-bluelight/80 bg-slate-50/90 text-bluedark">
                                    <div class="flex items-center justify-between group">
                                        <div>
                                            <div class="font-heading font-bold text-xs text-bluedark flex items-center gap-1.5">
                                                <span>Jam ke-{{ $p->period_number }}</span>
                                            </div>
                                            <div class="text-[10.5px] font-mono text-bluedark/60 font-medium">
                                                {{ $startTime }} - {{ $endTime }}
                                            </div>
                                        </div>

                                        <!-- Quick Action Hapus Period Row on Hover -->
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 no-print">
                                            <form action="{{ route('admin.academic.schedules.periods.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus jam pelajaran ini? (Hanya bisa jika belum terpakai)');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded hover:bg-rose-100 text-rose-500" title="Hapus Jam">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                <!-- Sel Jadwal Tiap Hari (Senin - Jumat / Sabtu) -->
                                @foreach($days as $dayNum => $dayName)
                                    @php
                                        $cell = $matrix[$p->id][$dayNum] ?? ['type' => 'empty'];
                                    @endphp

                                    @if($cell['type'] === 'covered')
                                        {{-- Dilewati karena merupakan bagian dari multi-period jadwal di atasnya (Rowspan) --}}
                                        @continue
                                    @endif

                                    @if($cell['type'] === 'start')
                                        @php
                                            $sch = $cell['schedule'];
                                            $span = $cell['span'];
                                            $subjectName = $sch->teachingAssignment?->subject?->name ?? 'Mata Pelajaran';
                                            $teacherName = $sch->teachingAssignment?->teacher?->full_name ?? 'Guru Pengampu';
                                            $roomName = $sch->room ?: 'R. Kelas';
                                            $startPeriodNum = $sch->startPeriod?->period_number ?? $p->period_number;
                                            $endPeriodNum = $sch->endPeriod?->period_number ?? $p->period_number;
                                            $durationText = $startPeriodNum === $endPeriodNum 
                                                ? "Jam {$startPeriodNum}" 
                                                : "Jam {$startPeriodNum} - {$endPeriodNum} ({$span} Jam / " . ($span * 45) . "m)";
                                        @endphp

                                        <td rowspan="{{ $span }}"
                                            class="score-cell-interactive score-cell-scheduled p-1.5 align-top relative border-r border-b border-bluelight transition-all"
                                            style="height: {{ $span * 72 }}px;"
                                            data-day="{{ $dayNum }}"
                                            data-day-name="{{ $dayName }}"
                                            data-period-id="{{ $p->id }}"
                                            data-period-num="{{ $p->period_number }}"
                                            data-period-time="{{ $startTime }} - {{ $endTime }}"
                                            data-has-schedule="true"
                                            data-schedule-id="{{ $sch->id }}"
                                            data-subject-name="{{ $subjectName }}"
                                            data-teacher-name="{{ $teacherName }}"
                                            data-room="{{ $roomName }}"
                                            data-start-period-id="{{ $sch->start_period_id }}"
                                            data-end-period-id="{{ $sch->end_period_id }}"
                                            data-assignment-id="{{ $sch->teaching_assignment_id }}"
                                            onclick="handleCellClick(this)"
                                            ondblclick="handleCellDblClick(this)"
                                            title="Klik untuk memilih &bull; Klik 2x untuk mengedit jadwal">
                                            
                                            <div class="schedule-card-inner p-2.5 flex flex-col justify-between shadow-2xs group"
                                                 style="height: calc({{ $span }} * 72px - 14px); min-height: calc({{ $span }} * 72px - 14px);">
                                                <div class="space-y-1">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-heading font-bold text-xs text-bluedark leading-tight line-clamp-2">
                                                             {{ $subjectName }}
                                                        </span>
                                                        <!-- Tombol Hapus Cepat (Hover) -->
                                                        <form action="{{ route('admin.academic.schedules.destroy', $sch) }}" method="POST" onsubmit="return confirm('Hapus jadwal mata pelajaran ini?');" class="opacity-0 group-hover:opacity-100 transition-opacity no-print">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-rose-400 hover:text-rose-600 font-bold text-xs leading-none p-0.5" title="Hapus jadwal">&times;</button>
                                                        </form>
                                                    </div>

                                                    <div class="text-[11px] text-bluedark/70 truncate flex items-center gap-1 font-medium">
                                                        <svg class="w-3 h-3 shrink-0 text-bluedark/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                        <span class="truncate">{{ $teacherName }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-between gap-1 pt-2 mt-2 border-t border-bluelight/60 flex-wrap">
                                                    <span class="badge-period-span">{{ $durationText }}</span>
                                                    <span class="badge-room-pill truncate max-w-[80px]">{{ $roomName }}</span>
                                                </div>
                                            </div>
                                        </td>

                                    @else
                                        {{-- Sel Kosong: Siap Diisi dengan Klik 2x --}}
                                        <td class="score-cell-interactive p-2 align-middle text-center relative bg-white border-r border-b border-bluelight text-slate-300 transition-all hover:bg-blue-50/50"
                                            style="height: 72px;"
                                            data-day="{{ $dayNum }}"
                                            data-day-name="{{ $dayName }}"
                                            data-period-id="{{ $p->id }}"
                                            data-period-num="{{ $p->period_number }}"
                                            data-period-time="{{ $startTime }} - {{ $endTime }}"
                                            data-has-schedule="false"
                                            data-schedule-id=""
                                            data-subject-name=""
                                            data-teacher-name=""
                                            data-room=""
                                            data-start-period-id="{{ $p->id }}"
                                            data-end-period-id="{{ $p->id }}"
                                            data-assignment-id=""
                                            onclick="handleCellClick(this)"
                                            ondblclick="handleCellDblClick(this)"
                                            title="Klik untuk memilih &bull; Klik 2x untuk menambah jadwal baru">
                                            
                                            <div class="py-3 flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-400 cell-empty-hint">
                                                <div class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-200 text-blueprim flex items-center justify-center shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                </div>
                                                <span class="text-[10px] text-blueprim font-semibold">Klik 2x</span>
                                            </div>
                                            <span class="text-slate-300 text-xs block group-hover:hidden select-none">-</span>
                                        </td>
                                    @endif
                                @endforeach

                                @if($daysCount < 6)
                                    <td class="p-2 bg-slate-50/30 text-center text-slate-300 text-xs border-r border-bluelight">
                                        <span class="text-[10px] text-slate-400 italic">Off</span>
                                    </td>
                                @endif
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 2 }}" class="py-12 text-center text-xs text-bluedark/40 italic">
                                Belum ada master jam pelajaran. Klik tombol <strong>Jam Pelajaran Baru</strong> untuk menambahkan baris jam pertama.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Baris Tambah Jam Pelajaran di Bagian Paling Bawah (Konsep Kolom Bawah / Tambah Jam) -->
                    <tr class="bg-blue-50/40 border-t-2 border-dashed border-blue-200 no-print">
                        <td class="sticky-col-period py-3 px-3.5 bg-blue-50/80 border-r border-blue-200">
                            <button type="button" onclick="openAddPeriodModal()" class="w-full text-left font-bold text-xs text-blueprim hover:text-bluedark flex items-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tambah Baris Jam</span>
                            </button>
                        </td>
                        <td colspan="{{ count($days) + ($daysCount < 6 ? 1 : 0) }}" class="py-3 px-4 text-xs text-bluedark/60 font-medium">
                            <div class="flex items-center gap-1.5 cursor-pointer hover:text-blueprim transition-colors" onclick="openAddPeriodModal()">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                <span>Klik di sini untuk menambah jam pelajaran atau jam istirahat baru ke baris bawah tabel.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Keterangan & Footer Navigasi Spreadsheet -->
        <div class="flex items-center justify-between text-[11px] text-bluedark/60 pt-1 flex-wrap gap-3">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-100 border-2 border-blue-500 inline-block"></span> Sel Terpilih</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-50 border border-blue-300 inline-block"></span> Sel Terisi Jadwal</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-amber-100 border border-amber-300 inline-block"></span> Jam Istirahat</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-white border border-slate-200 inline-block"></span> Sel Kosong (Bebas)</span>
            </div>
            <div class="italic text-bluedark/50">
                *Tip: Gunakan klik ganda (Double Click) pada kotak manapun untuk mengubah atau menambah jadwal secara instan.
            </div>
        </div>

    </div>

    <!-- Panel Informasi Master Jam Pelajaran Terdaftar -->
    <div class="panel p-5 no-print">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div>
                <h3 class="font-heading font-semibold text-bluedark text-sm">Master Jam Pelajaran &amp; Istirahat Sekolah</h3>
                <p class="text-xs text-bluedark/50">Urutan jam pelajaran dan jam istirahat yang berlaku untuk seluruh rombel</p>
            </div>
            <button type="button" onclick="openAddPeriodModal()" class="btn btn-outline btn-sm text-xs">
                + Tambah Jam / Istirahat
            </button>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto pb-2 db-scroll">
            @forelse($periodsSorted as $p)
                <div class="px-3.5 py-2.5 rounded-xl border {{ $p->is_break ? 'border-amber-300 bg-amber-50/70' : 'border-bluelight bg-white' }} text-center shrink-0 shadow-2xs hover:border-blueprim transition-colors group relative cursor-pointer" onclick="{{ $p->is_break ? 'openEditPeriodModal('.$p->id.', null, \''.addslashes($p->label).'\', \''.\Carbon\Carbon::parse($p->start_time)->format('H:i').'\', \''.\Carbon\Carbon::parse($p->end_time)->format('H:i').'\', true)' : 'openEditPeriodModal('.$p->id.', '.$p->period_number.', \''.addslashes($p->label).'\', \''.\Carbon\Carbon::parse($p->start_time)->format('H:i').'\', \''.\Carbon\Carbon::parse($p->end_time)->format('H:i').'\', false)' }}">
                    <div class="flex items-center justify-center gap-1 text-xs font-bold {{ $p->is_break ? 'text-amber-900' : 'text-bluedark' }}">
                        @if($p->is_break)
                            <span class="badge text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-amber-200 text-amber-950 border border-amber-300">Istirahat</span>
                        @else
                            <span>Jam ke-{{ $p->period_number }}</span>
                        @endif
                    </div>
                    <div class="text-[10px] font-mono {{ $p->is_break ? 'text-amber-800' : 'text-bluedark/60' }} font-medium">
                        {{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($p->end_time)->format('H:i') }}
                    </div>
                    <div class="pt-1 text-[9px] {{ $p->is_break ? 'text-amber-800 font-bold' : 'text-blueprim font-semibold' }} truncate max-w-[110px]">
                        {{ $p->label }}
                    </div>
                </div>
            @empty
                <div class="text-xs text-bluedark/40 italic py-4">Belum ada jam pelajaran terdaftar.</div>
            @endforelse
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: EXCEL CELL EDITOR (KLIK 2 KALI / DOUBLE CLICK UNTUK EDIT JADWAL)  -->
<!-- ========================================================================= -->
<div id="cellEditorModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in duration-150">
        
        <!-- Header Modal Bertema Deep Navy #0D47A1 -->
        <div class="p-5 flex items-center justify-between text-white" style="background:#0D47A1 !important;">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-500/30 flex items-center justify-center text-white shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-white" id="modalEditorTitle">Edit Jadwal Pelajaran</h3>
                    <p class="text-xs text-blue-200 mt-0.5" id="modalEditorSubtitle">Atur mata pelajaran pada sel yang dipilih</p>
                </div>
            </div>
            <button type="button" onclick="closeCellEditorModal()" class="text-white/60 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>

        <form id="cellScheduleForm" method="POST" action="{{ route('admin.academic.schedules.store') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formHttpMethod" value="POST">
            <input type="hidden" name="class_id" value="{{ $selectedClass?->id }}">
            <input type="hidden" name="days_count" value="{{ $daysCount }}">

            <!-- Baris Info Sel (Hari & Jam Terpilih) -->
            <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-bluedark/60 block">Posisi Sel:</span>
                    <span class="text-xs font-bold text-bluedark" id="modalCellPositionBadge">Senin &bull; Jam ke-1</span>
                </div>
                <span class="badge badge-blue text-[10px]" id="modalClassBadge">{{ $selectedClass?->name }}</span>
            </div>

            <div id="noPeriodAvailableWarning" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Semua jam pelajaran pada hari ini sudah terisi jadwal atau merupakan jam istirahat.</span>
            </div>

            <!-- Hari (Kolom) -->
            <div>
                <label class="f-label font-bold text-xs text-bluedark">Hari (Kolom)</label>
                <select name="day_of_week" id="inputDayOfWeek" required class="f-select font-medium text-xs" onchange="updatePeriodDropdownAvailability()">
                    @foreach($allDays as $dNum => $dName)
                        <option value="{{ $dNum }}">{{ $dName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mata Pelajaran & Guru Pengampu -->
            <div>
                <label class="f-label font-bold text-xs text-bluedark">Mata Pelajaran &amp; Guru Pengampu <span class="text-rose-500">*</span></label>
                <select name="teaching_assignment_id" id="inputAssignmentId" required class="f-select font-medium text-xs" onchange="updateAssignmentQuotaIndicator()">
                    <option value="">-- Pilih Mata Pelajaran &amp; Guru --</option>
                    @foreach($assignments as $a)
                        <option value="{{ $a->id }}"
                            data-weekly-hours="{{ $a->weekly_hours }}"
                            data-scheduled-hours="{{ $a->scheduled_hours }}"
                            data-remaining-hours="{{ $a->remaining_hours }}">
                            {{ $a->subject?->name }} — {{ $a->teacher?->full_name }} ({{ $a->scheduled_hours }}/{{ $a->weekly_hours }} Jam Digunakan)
                        </option>
                    @endforeach
                </select>
                <div id="assignmentQuotaFeedback" class="mt-1.5 text-[11px] transition-all"></div>
                @if($assignments->isEmpty())
                    <div class="flex items-start gap-1.5 text-[11px] text-amber-600 mt-1.5 leading-snug">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>Belum ada penugasan guru untuk kelas ini. Tambahkan di menu <strong>Penugasan Guru</strong> terlebih dahulu.</span>
                    </div>
                @endif
            </div>

            <!-- Rentang Jam Pelajaran (Dropdown hanya menampilkan jam yang kosong) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Mulai Jam Ke-</label>
                    <select name="start_period_id" id="inputStartPeriodId" required class="f-select font-medium text-xs" onchange="updateEndPeriodDropdownAvailability()">
                        <option value="">-- Memuat Jam --</option>
                    </select>
                </div>
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Selesai Jam Ke-</label>
                    <select name="end_period_id" id="inputEndPeriodId" required class="f-select font-medium text-xs" onchange="updateAssignmentQuotaIndicator()">
                        <option value="">-- Memuat Jam --</option>
                    </select>
                </div>
            </div>

            <div id="noPeriodAvailableWarning" class="hidden p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Seluruh jam pelajaran pada hari ini sudah terisi jadwal atau tidak tersedia.</span>
            </div>

            <!-- Ruangan / Lokasi Belajar -->
            <div>
                <label class="f-label font-bold text-xs text-bluedark">Ruangan (Opsional)</label>
                <input type="text" name="room" id="inputRoom" placeholder="Lab Komputer 1 / R. Teori 3" class="f-input text-xs font-medium">
            </div>

            <!-- Footer Tombol Aksi Modal -->
            <div class="flex items-center justify-between pt-4 border-t border-bluelight gap-2">
                <button type="button" id="btnModalDeleteSchedule" onclick="deleteCurrentModalSchedule()" class="btn btn-outline btn-sm text-xs text-rose-600 border-rose-200 hover:bg-rose-50 hidden">
                    Hapus Jadwal
                </button>
                <div class="flex items-center justify-end gap-2 ml-auto">
                    <button type="button" onclick="closeCellEditorModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm text-xs" id="btnSubmitCellSchedule">Simpan Jadwal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: KELOLA KOLOM HARI (DEFAULT 5 HARI, BISA NIKMATI 6 HARI)           -->
<!-- ========================================================================= -->
<div id="columnManagerModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <div>
                <h3 class="font-heading font-bold text-base text-bluedark">Kelola Kolom Hari</h3>
                <p class="text-xs text-bluedark/60">Atur jumlah hari aktif mingguan</p>
            </div>
            <button type="button" onclick="closeColumnManagerModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="GET" action="{{ route('admin.academic.schedules.index') }}" class="space-y-4">
            <input type="hidden" name="class_id" value="{{ $selectedClass?->id }}">

            <div class="space-y-2.5">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight cursor-pointer hover:bg-slate-50 transition-colors {{ $daysCount == 5 ? 'bg-blue-50/60 border-blue-300' : '' }}">
                    <input type="radio" name="days_count" value="5" {{ $daysCount == 5 ? 'checked' : '' }} class="mt-1">
                    <div>
                        <div class="font-bold text-xs text-bluedark">5 Hari Kerja (Default)</div>
                        <div class="text-[11px] text-bluedark/60">Senin, Selasa, Rabu, Kamis, Jumat</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight cursor-pointer hover:bg-slate-50 transition-colors {{ $daysCount == 6 ? 'bg-blue-50/60 border-blue-300' : '' }}">
                    <input type="radio" name="days_count" value="6" {{ $daysCount == 6 ? 'checked' : '' }} class="mt-1">
                    <div>
                        <div class="font-bold text-xs text-bluedark">6 Hari Kerja</div>
                        <div class="text-[11px] text-bluedark/60">Senin, Selasa, Rabu, Kamis, Jumat, Sabtu</div>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeColumnManagerModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs">Terapkan Kolom</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: TAMBAH / SISIPKAN JAM PELAJARAN / ISTIRAHAT (BISA BERGESER)     -->
<!-- ========================================================================= -->
@php
    $lastPeriod = $periodsSorted->last();
    $nextStartTime = $lastPeriod ? \Carbon\Carbon::parse($lastPeriod->end_time)->format('H:i') : '07:00';
    $nextEndTime = $lastPeriod ? \Carbon\Carbon::parse($lastPeriod->end_time)->addMinutes(45)->format('H:i') : '07:45';
@endphp

<div id="periodModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blueprim flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark" id="periodModalTitle">Tambah Jam Pelajaran / Istirahat</h3>
                    <p class="text-xs text-bluedark/60" id="periodModalSubtitle">Atur posisi dan rentang waktu jam</p>
                </div>
            </div>
            <button type="button" onclick="closePeriodModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="periodForm" method="POST" action="{{ route('admin.academic.schedules.periods.store') }}" class="space-y-4" onsubmit="return checkPeriodFormSubmit(event)">
            @csrf
            <input type="hidden" name="_method" id="periodHttpMethod" value="POST">

            <!-- Checkbox Jam Istirahat (Kuning, Unclickable) -->
            <div class="p-3 rounded-xl bg-amber-50/90 border border-amber-200">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_break" id="inputPeriodIsBreak" value="1" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4 mt-0.5" onchange="toggleBreakMode(this.checked)">
                    <div>
                        <span class="text-xs font-bold text-amber-900 block">Tandai sebagai Jam Istirahat</span>
                        <span class="text-[11px] text-amber-700 block">Jam istirahat tidak dihitung sebagai jam pelajaran reguler dan berlatar kuning</span>
                    </div>
                </label>
            </div>

            <input type="hidden" name="insert_position" id="inputPeriodInsertPosition" value="end">

            <div id="periodLabelContainer">
                <label class="f-label font-bold text-xs text-bluedark">Label Jam (Opsional)</label>
                <input type="text" name="label" id="inputPeriodLabel" class="f-input text-xs" placeholder="Biarkan kosong untuk otomatis">
            </div>

            <!-- Format Memilih Jam dengan Input Time (Validasi Bentrok / Overlap Realtime) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Mulai (WIB) <span class="text-rose-500">*</span></label>
                    <input type="time" name="start_time" id="inputPeriodStartTime" required class="f-input text-xs font-semibold cursor-pointer" value="{{ $nextStartTime }}" oninput="validatePeriodTimeNoOverlap()" onchange="validatePeriodTimeNoOverlap()">
                </div>
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Selesai (WIB) <span class="text-rose-500">*</span></label>
                    <input type="time" name="end_time" id="inputPeriodEndTime" required class="f-input text-xs font-semibold cursor-pointer" value="{{ $nextEndTime }}" oninput="validatePeriodTimeNoOverlap()" onchange="validatePeriodTimeNoOverlap()">
                </div>
            </div>

            <!-- Notice Jam Bentrok / Overlap Realtime -->
            <div id="periodTimeCollisionAlert" class="hidden p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div id="periodTimeCollisionMessage" class="leading-relaxed"></div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePeriodModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs" id="btnSubmitPeriod">Simpan Jam</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // State Tracker Interaktif ala Guru Daftar Nilai / Excel
    let activeCell = null;
    let currentSelectedScheduleId = null;
    let currentCellEditingData = null;
    let currentEditingPeriodId = null;

    const occupiedByDay = @json($occupiedByDay);
    const periodsSortedArray = @json($periodsSorted->values());
    const periodsData = @json($periodsSorted->keyBy('id'));

    function formatTimeHM(timeStr) {
        if (!timeStr) return '';
        return timeStr.substring(0, 5);
    }

    /**
     * Pilih / Klik Sel pada Spreadsheet (Excel Active Cell Effect)
     */
    function handleCellClick(cell) {
        // Hapus penanda sel aktif sebelumnya
        document.querySelectorAll('#scheduleSpreadsheetTable .active-score-cell').forEach(c => {
            c.classList.remove('active-score-cell');
        });
        document.querySelectorAll('#scheduleSpreadsheetTable .active-col-cell').forEach(c => {
            c.classList.remove('active-col-cell');
        });
        document.querySelectorAll('#scheduleSpreadsheetTable .active-row-period').forEach(r => {
            r.classList.remove('active-row-period');
        });

        // Set sel aktif sekarang
        activeCell = cell;
        cell.classList.add('active-score-cell');

        // Sorot baris dan kolom yang bersesuaian
        const day = cell.getAttribute('data-day');
        const periodId = cell.getAttribute('data-period-id');
        const periodNum = cell.getAttribute('data-period-num');
        const periodTime = cell.getAttribute('data-period-time');
        const dayName = cell.getAttribute('data-day-name');
        const hasSchedule = cell.getAttribute('data-has-schedule') === 'true';
        const scheduleId = cell.getAttribute('data-schedule-id');
        const subjectName = cell.getAttribute('data-subject-name');
        const teacherName = cell.getAttribute('data-teacher-name');
        const roomName = cell.getAttribute('data-room');

        currentSelectedScheduleId = scheduleId;

        // Sorot kolom hari aktif di header
        highlightDayColumn(day, false);

        // Sorot baris jam
        const row = cell.closest('tr');
        if (row) {
            row.classList.add('active-row-period');
        }

        // Perbarui Banner Sel Terpilih (Matching Banner Guru Daftar Nilai)
        const bannerCellInfo = document.getElementById('bannerCellInfo');
        const bannerSelectedSubtitle = document.getElementById('bannerSelectedSubtitle');
        const bannerStatusBadge = document.getElementById('bannerStatusBadge');
        const btnEdit = document.getElementById('btnEditSelectedCell');
        const btnDelete = document.getElementById('btnDeleteSelectedCell');

        bannerCellInfo.innerHTML = `Hari <strong>${dayName}</strong> &bull; Jam ke-<strong>${periodNum}</strong> (${periodTime})`;

        if (hasSchedule) {
            bannerSelectedSubtitle.innerHTML = `Mata Pelajaran: <strong>${subjectName}</strong> &bull; Guru: <strong>${teacherName}</strong> &bull; Ruang: <strong>${roomName || 'R. Kelas'}</strong>`;
            bannerStatusBadge.className = 'badge badge-blue text-xs font-bold px-2.5 py-1';
            bannerStatusBadge.textContent = 'Status: Terjadwal';
            btnEdit.classList.remove('hidden');
            btnDelete.classList.remove('hidden');
        } else {
            bannerSelectedSubtitle.innerHTML = `Kotak ini masih kosong. <strong>Klik 2 kali (Double Click)</strong> pada kotak ini untuk menambah jadwal baru.`;
            bannerStatusBadge.className = 'badge badge-gray text-xs font-bold px-2.5 py-1';
            bannerStatusBadge.textContent = 'Status: Kosong';
            btnEdit.classList.remove('hidden');
            btnDelete.classList.add('hidden');
        }
    }

    /**
     * Klik 2 Kali (Double Click) pada Kotak Sel ala Excel
     */
    function handleCellDblClick(cell) {
        handleCellClick(cell);
        openCellEditor(cell);
    }

    /**
     * Sembunyikan Jam yang Sudah Terisi / Istirahat di Dropdown agar Tidak Bisa Ditimpa
     * Dropdown hanya menampilkan jam kosong dan tidak menampilkan jam istirahat.
     */
    function updatePeriodDropdownAvailability() {
        const day = document.getElementById('inputDayOfWeek').value;
        const startSelect = document.getElementById('inputStartPeriodId');
        const warningBox = document.getElementById('noPeriodAvailableWarning');
        const submitBtn = document.getElementById('btnSubmitCellSchedule');
        const occupiedForDay = (occupiedByDay && occupiedByDay[day]) ? occupiedByDay[day] : {};

        const isEditing = currentCellEditingData && currentCellEditingData.hasSchedule;
        const currentScheduleId = isEditing ? String(currentCellEditingData.scheduleId) : null;

        // Filter jam yang bukan istirahat dan tidak diisi jadwal lain
        const availablePeriods = periodsSortedArray.filter(p => {
            if (p.is_break) return false;
            const occ = occupiedForDay[p.id];
            if (occ && String(occ.schedule_id) !== currentScheduleId) {
                return false;
            }
            return true;
        });

        startSelect.innerHTML = '';

        if (availablePeriods.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- Tidak ada jam kosong --';
            startSelect.appendChild(opt);
            if (warningBox) warningBox.classList.remove('hidden');
            submitBtn.disabled = true;
            updateEndPeriodDropdownAvailability();
            return;
        }

        if (warningBox) warningBox.classList.add('hidden');
        submitBtn.disabled = false;

        let targetStartId = currentCellEditingData ? String(currentCellEditingData.startPeriodId) : null;
        let selectedId = null;

        availablePeriods.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.setAttribute('data-num', p.period_number);
            opt.setAttribute('data-order', p.sort_order);
            opt.textContent = `Jam Ke-${p.period_number} (${formatTimeHM(p.start_time)} - ${formatTimeHM(p.end_time)})`;
            startSelect.appendChild(opt);

            if (targetStartId && String(p.id) === targetStartId) {
                selectedId = p.id;
            }
        });

        if (selectedId) {
            startSelect.value = selectedId;
        } else {
            startSelect.value = availablePeriods[0].id;
        }

        updateEndPeriodDropdownAvailability();
    }

    /**
     * Dropdown Jam Selesai: Hanya menampilkan jam lanjutan yang tersambung (kontigu)
     * tanpa melompati jam istirahat atau jadwal pelajaran lain.
     */
    function updateEndPeriodDropdownAvailability() {
        const day = document.getElementById('inputDayOfWeek').value;
        const startSelect = document.getElementById('inputStartPeriodId');
        const endSelect = document.getElementById('inputEndPeriodId');
        const occupiedForDay = (occupiedByDay && occupiedByDay[day]) ? occupiedByDay[day] : {};
        const isEditing = currentCellEditingData && currentCellEditingData.hasSchedule;
        const currentScheduleId = isEditing ? String(currentCellEditingData.scheduleId) : null;

        endSelect.innerHTML = '';

        const selectedStartId = parseInt(startSelect.value, 10);
        const startIndex = periodsSortedArray.findIndex(p => p.id === selectedStartId);

        if (startIndex === -1) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- Memuat Jam --';
            endSelect.appendChild(opt);
            updateAssignmentQuotaIndicator();
            return;
        }

        let countHours = 0;
        let targetEndId = currentCellEditingData ? String(currentCellEditingData.endPeriodId) : null;
        let selectedEndId = null;

        for (let i = startIndex; i < periodsSortedArray.length; i++) {
            const p = periodsSortedArray[i];

            // Jam istirahat membatasi rentang jam pelajaran bersambung
            if (p.is_break) {
                break;
            }

            // Jam yang terisi jadwal lain membatasi rentang
            const occ = occupiedForDay[p.id];
            if (occ && String(occ.schedule_id) !== currentScheduleId) {
                break;
            }

            countHours++;
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.setAttribute('data-num', p.period_number);
            opt.setAttribute('data-order', p.sort_order);
            opt.setAttribute('data-hours', countHours);

            const durationInfo = countHours === 1 
                ? '1 Jam Pelajaran' 
                : `${countHours} Jam Pelajaran (${countHours * 45} Menit)`;

            opt.textContent = `Jam Ke-${p.period_number} (${formatTimeHM(p.start_time)} - ${formatTimeHM(p.end_time)}) — ${durationInfo}`;
            endSelect.appendChild(opt);

            if (targetEndId && String(p.id) === targetEndId) {
                selectedEndId = p.id;
            }
        }

        if (selectedEndId) {
            endSelect.value = selectedEndId;
        } else {
            endSelect.value = selectedStartId;
        }

        updateAssignmentQuotaIndicator();
    }

    function toggleBreakMode(isBreak) {
        const labelInput = document.getElementById('inputPeriodLabel');
        if (isBreak) {
            labelInput.value = 'Istirahat';
        } else {
            if (labelInput.value === 'Istirahat') {
                labelInput.value = '';
            }
        }
    }


    function updateAssignmentQuotaIndicator() {
        const select = document.getElementById('inputAssignmentId');
        const feedback = document.getElementById('assignmentQuotaFeedback');
        const submitBtn = document.getElementById('btnSubmitCellSchedule');
        const endSelect = document.getElementById('inputEndPeriodId');

        if (!select || !feedback || !submitBtn) return;

        const selectedOpt = select.options[select.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            feedback.innerHTML = '';
            submitBtn.disabled = false;
            return;
        }

        const weeklyHours = parseInt(selectedOpt.getAttribute('data-weekly-hours') || '0', 10);
        const remainingHours = parseInt(selectedOpt.getAttribute('data-remaining-hours') || '0', 10);

        const endOpt = endSelect.options[endSelect.selectedIndex];
        const slotHours = parseInt(endOpt?.getAttribute('data-hours') || '1', 10);

        // Jika sedang edit jadwal yang sudah ada dan mata pelajaran yang dipilih sama dengan aslinya:
        let originalSlotHours = 0;
        if (currentCellEditingData && currentCellEditingData.hasSchedule && currentCellEditingData.assignmentId == selectedOpt.value) {
            const origStartIndex = periodsSortedArray.findIndex(p => String(p.id) === String(currentCellEditingData.startPeriodId));
            const origEndIndex = periodsSortedArray.findIndex(p => String(p.id) === String(currentCellEditingData.endPeriodId));
            if (origStartIndex !== -1 && origEndIndex !== -1) {
                originalSlotHours = (origEndIndex - origStartIndex) + 1;
            }
        }

        const effectiveAvailable = remainingHours + originalSlotHours;

        if (slotHours > effectiveAvailable) {
            feedback.innerHTML = `
                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 font-medium flex items-start gap-2 text-xs leading-snug">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="font-bold block text-rose-900">Batas Kuota Mengajar Terlampaui!</strong>
                        <span>Slot ini (${slotHours} jam) melebihi alokasi penugasan guru di kelas ini (${weeklyHours} jam/minggu). Sisa kuota tersedia hanya ${effectiveAvailable} jam.</span>
                    </div>
                </div>
            `;
            submitBtn.disabled = true;
        } else {
            const sisaAkhir = effectiveAvailable - slotHours;
            feedback.innerHTML = `
                <div class="p-2 rounded-xl bg-blue-50 border border-blue-200 text-bluedark flex items-center justify-between gap-2 text-xs">
                    <span class="text-blueprim font-bold">Penugasan: ${weeklyHours} Jam/Minggu</span>
                    <span>Slot Ini: <strong>${slotHours} Jam</strong></span>
                    <span class="text-emerald-700 font-semibold">Sisa Kuota: <strong>${sisaAkhir} Jam</strong></span>
                </div>
            `;
            submitBtn.disabled = false;
        }
    }

    /**
     * Buka Modal Edit / Tambah Jadwal untuk Sel Tertentu
     */
    function openCellEditor(cell) {
        const form = document.getElementById('cellScheduleForm');
        const methodInput = document.getElementById('formHttpMethod');
        const titleEl = document.getElementById('modalEditorTitle');
        const subEl = document.getElementById('modalEditorSubtitle');
        const posEl = document.getElementById('modalCellPositionBadge');
        const btnDelete = document.getElementById('btnModalDeleteSchedule');

        const day = cell.getAttribute('data-day');
        const dayName = cell.getAttribute('data-day-name');
        const periodId = cell.getAttribute('data-period-id');
        const periodNum = cell.getAttribute('data-period-num');
        const periodTime = cell.getAttribute('data-period-time');
        const hasSchedule = cell.getAttribute('data-has-schedule') === 'true';
        const scheduleId = cell.getAttribute('data-schedule-id');
        const assignmentId = cell.getAttribute('data-assignment-id');
        const startPeriodId = cell.getAttribute('data-start-period-id') || periodId;
        const endPeriodId = cell.getAttribute('data-end-period-id') || periodId;
        const roomName = cell.getAttribute('data-room') || '';

        currentCellEditingData = {
            hasSchedule: hasSchedule,
            scheduleId: scheduleId,
            assignmentId: assignmentId,
            startPeriodId: startPeriodId,
            endPeriodId: endPeriodId,
        };

        posEl.innerHTML = `Hari ${dayName} &bull; Jam ke-${periodNum} (${periodTime})`;

        // Isi form controls
        document.getElementById('inputDayOfWeek').value = day;
        document.getElementById('inputAssignmentId').value = assignmentId || '';
        document.getElementById('inputStartPeriodId').value = startPeriodId;
        document.getElementById('inputEndPeriodId').value = endPeriodId;
        document.getElementById('inputRoom').value = roomName;

        updatePeriodDropdownAvailability();

        if (hasSchedule && scheduleId) {
            titleEl.textContent = 'Edit Jadwal Pelajaran';
            subEl.textContent = 'Perbarui data mata pelajaran atau ruangan pada jadwal ini';
            form.action = `{{ url('/admin/academic/schedules') }}/${scheduleId}`;
            methodInput.value = 'PUT';
            btnDelete.classList.remove('hidden');
        } else {
            titleEl.textContent = 'Tambah Jadwal Pelajaran';
            subEl.textContent = 'Isi form berikut untuk menempatkan jadwal pada sel ini';
            form.action = `{{ route('admin.academic.schedules.store') }}`;
            methodInput.value = 'POST';
            btnDelete.classList.add('hidden');
        }

        document.getElementById('cellEditorModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('inputAssignmentId').focus();
        }, 100);
    }

    function openSelectedCellModal() {
        if (activeCell) {
            openCellEditor(activeCell);
        }
    }

    function closeCellEditorModal() {
        document.getElementById('cellEditorModal').classList.add('hidden');
    }

    /**
     * Hapus jadwal yang sedang dibuka di modal
     */
    function deleteCurrentModalSchedule() {
        if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;
        const form = document.getElementById('cellScheduleForm');
        document.getElementById('formHttpMethod').value = 'DELETE';
        form.submit();
    }

    /**
     * Hapus jadwal dari tombol aksi banner
     */
    function deleteSelectedSchedule() {
        if (!currentSelectedScheduleId) return;
        if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;

        const dummyForm = document.createElement('form');
        dummyForm.method = 'POST';
        dummyForm.action = `{{ url('/admin/academic/schedules') }}/${currentSelectedScheduleId}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        dummyForm.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        dummyForm.appendChild(methodInput);

        document.body.appendChild(dummyForm);
        dummyForm.submit();
    }

    /**
     * Sorot Kolom Hari Tertentu (Header & Sel Kolom)
     */
    function highlightDayColumn(dayNum, scroll = true) {
        // Reset header
        document.querySelectorAll('.day-col-header').forEach(th => {
            th.classList.remove('active-col-header');
        });
        document.querySelectorAll('.quick-col-btn').forEach(btn => {
            btn.classList.remove('active-quick-btn');
        });
        document.querySelectorAll('.active-col-cell').forEach(td => {
            td.classList.remove('active-col-cell');
        });

        if (dayNum === null) {
            // Tampilkan semua hari
            const allBtn = document.querySelector('#quickDaySelector button');
            if (allBtn) allBtn.classList.add('active-quick-btn');
            return;
        }

        // Aktifkan header hari
        const targetHeader = document.querySelector(`.day-col-header[data-day="${dayNum}"]`);
        if (targetHeader) {
            targetHeader.classList.add('active-col-header');
            if (scroll) {
                targetHeader.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        }

        // Aktifkan quick pill button
        const quickBtn = document.getElementById(`quickBtnDay${dayNum}`);
        if (quickBtn) {
            quickBtn.classList.add('active-quick-btn');
        }

        // Sorot seluruh sel pada kolom hari tersebut
        document.querySelectorAll(`#scheduleSpreadsheetTable td[data-day="${dayNum}"]`).forEach(td => {
            if (!td.classList.contains('active-score-cell')) {
                td.classList.add('active-col-cell');
            }
        });
    }

    /**
     * Modal Jam Pelajaran Baru (Tambah Baris ke Paling Bawah)
     */
    /**
     * Validasi bentrok / overlap jam pelajaran secara realtime.
     * Jika jam mulai & selesai bersinggungan / mengambil jam yang sudah dipakai oleh baris jam lain,
     * tombol simpan otomatis di-disable dan ditampilkan peringatan nama jam yang bentrok.
     */
    function validatePeriodTimeNoOverlap() {
        const startInput = document.getElementById('inputPeriodStartTime');
        const endInput = document.getElementById('inputPeriodEndTime');
        const alertBox = document.getElementById('periodTimeCollisionAlert');
        const messageEl = document.getElementById('periodTimeCollisionMessage');
        const submitBtn = document.getElementById('btnSubmitPeriod');

        if (!startInput || !endInput || !submitBtn) return;

        const startVal = startInput.value;
        const endVal = endInput.value;

        // Reset visual input error
        startInput.classList.remove('border-rose-500', 'bg-rose-50/50');
        endInput.classList.remove('border-rose-500', 'bg-rose-50/50');

        if (!startVal || !endVal) {
            if (alertBox) alertBox.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }

        // 1. Validasi jam mulai < jam selesai
        if (startVal >= endVal) {
            if (alertBox) alertBox.classList.remove('hidden');
            if (messageEl) {
                messageEl.innerHTML = '<strong class="font-bold block text-rose-900">Format Waktu Tidak Valid</strong><span>Jam selesai harus lebih besar daripada jam mulai.</span>';
            }
            startInput.classList.add('border-rose-500', 'bg-rose-50/50');
            endInput.classList.add('border-rose-500', 'bg-rose-50/50');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        // 2. Deteksi overlap dengan seluruh jam yang sudah ada (kecuali jam yang sedang diedit)
        let collidingPeriods = [];

        periodsSortedArray.forEach(p => {
            if (currentEditingPeriodId && String(p.id) === String(currentEditingPeriodId)) {
                return;
            }

            const pStart = p.start_time.substring(0, 5);
            const pEnd = p.end_time.substring(0, 5);

            // Kondisi overlap interval: startVal < pEnd && endVal > pStart
            if (startVal < pEnd && endVal > pStart) {
                const pLabel = p.is_break ? 'Istirahat' : (p.label || `Jam Ke-${p.period_number}`);
                collidingPeriods.push(`${pLabel} (${pStart} - ${pEnd})`);
            }
        });

        if (collidingPeriods.length > 0) {
            if (alertBox) alertBox.classList.remove('hidden');
            if (messageEl) {
                messageEl.innerHTML = `<strong class="font-bold block text-rose-900">Jam Bentrok / Sudah Digunakan!</strong><span>Waktu <strong>${startVal} - ${endVal}</strong> bertabrakan dengan <strong>${collidingPeriods.join(', ')}</strong>. Rentang jam yang sudah digunakan tidak dapat dipakai kembali.</span>`;
            }
            startInput.classList.add('border-rose-500', 'bg-rose-50/50');
            endInput.classList.add('border-rose-500', 'bg-rose-50/50');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            if (alertBox) alertBox.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function checkPeriodFormSubmit(e) {
        validatePeriodTimeNoOverlap();
        const submitBtn = document.getElementById('btnSubmitPeriod');
        if (submitBtn && submitBtn.disabled) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            return false;
        }
        return true;
    }

    /**
     * Modal Jam Pelajaran Baru (Tambah Baris ke Paling Bawah)
     */
    function openAddPeriodModal() {
        currentEditingPeriodId = null;
        document.getElementById('periodModalTitle').textContent = 'Tambah Jam Pelajaran / Istirahat';
        document.getElementById('periodModalSubtitle').textContent = 'Tambahkan baris jam baru di bagian paling bawah';
        document.getElementById('periodForm').action = `{{ route('admin.academic.schedules.periods.store') }}`;
        document.getElementById('periodHttpMethod').value = 'POST';
        document.getElementById('inputPeriodInsertPosition').value = 'end';
        document.getElementById('inputPeriodLabel').value = '';
        document.getElementById('inputPeriodStartTime').value = '{{ $nextStartTime }}';
        document.getElementById('inputPeriodEndTime').value = '{{ $nextEndTime }}';
        document.getElementById('inputPeriodIsBreak').checked = false;
        document.getElementById('periodModal').classList.remove('hidden');
        validatePeriodTimeNoOverlap();
    }

    function closePeriodModal() {
        document.getElementById('periodModal').classList.add('hidden');
    }

    /**
     * Dropdown Export Jadwal (PDF / Excel) — Floating Fixed Popover
     */
    function toggleExportDropdown(e) {
        if (e) e.stopPropagation();
        const btn = document.getElementById('exportScheduleBtn');
        const menu = document.getElementById('exportScheduleDropdownMenu');
        const chevron = document.getElementById('exportScheduleChevron');
        if (!menu || !btn) return;

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            const rect = btn.getBoundingClientRect();
            const menuWidth = 192; // 12rem = 192px
            let left = rect.right - menuWidth;
            if (left < 10) left = 10;
            if (left + menuWidth > window.innerWidth - 10) {
                left = window.innerWidth - menuWidth - 10;
            }
            menu.style.top = `${rect.bottom + 6}px`;
            menu.style.left = `${left}px`;
            menu.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    function closeExportDropdown() {
        const menu = document.getElementById('exportScheduleDropdownMenu');
        const chevron = document.getElementById('exportScheduleChevron');
        if (menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    document.addEventListener('click', function(e) {
        const btn = document.getElementById('exportScheduleBtn');
        const menu = document.getElementById('exportScheduleDropdownMenu');
        if (menu && !menu.classList.contains('hidden')) {
            if (btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                closeExportDropdown();
            }
        }
    });

    window.addEventListener('scroll', function() {
        closeExportDropdown();
    }, true);

    window.addEventListener('resize', function() {
        closeExportDropdown();
    });

    /**
     * Modal Kelola Kolom Hari (5 atau 6 Hari)
     */
    function openColumnManagerModal() {
        document.getElementById('columnManagerModal').classList.remove('hidden');
    }
    function closeColumnManagerModal() {
        document.getElementById('columnManagerModal').classList.add('hidden');
    }

    // Keyboard Shortcuts ala Excel
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCellEditorModal();
            closePeriodModal();
            closeColumnManagerModal();
            closeExportDropdown();
        }

        // Tekan Enter pada sel terpilih untuk langsung edit
        if (e.key === 'Enter' && activeCell && document.getElementById('cellEditorModal').classList.contains('hidden')) {
            e.preventDefault();
            openCellEditor(activeCell);
        }
    });
</script>
@endpush
@endsection
