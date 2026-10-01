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
}
.score-cell-interactive:hover {
  background-color: #E0E7FF !important;
  border-color: #93C5FD !important;
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

/* Card Jadwal di dalam Kotak Sel */
.schedule-card-inner {
  border-radius: 0.625rem;
  transition: all 0.15s ease;
  border: 1px solid rgba(37, 99, 235, 0.2);
  background: #ffffff;
}
.schedule-card-inner:hover {
  border-color: #2563EB;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Jadwal Pelajaran &amp; Jam Belajar</h1>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola jadwal mingguan tiap rombel dan jam pelajaran sekolah dengan konsep Spreadsheet Grid (Excel)
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Tombol Tambah Baris Jam Pelajaran (Kolom ke Bawah) -->
            <button type="button" onclick="openAddPeriodModal()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 shrink-0 text-blueprim" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Jam Pelajaran Baru (Baris)</span>
            </button>

            <!-- Tombol Kelola Kolom Hari (5 atau 6 Hari) -->
            <button type="button" onclick="openColumnManagerModal()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 shrink-0 text-bluedark/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                <span>Edit Kolom Hari</span>
            </button>

            <!-- Tombol Tambah Kolom Cepat (Sabtu) jika masih 5 hari, atau sebaliknya -->
            @if($daysCount < 6)
                <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 6]) }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>+ Tambah Kolom (Sabtu)</span>
                </a>
            @else
                <a href="{{ route('admin.academic.schedules.index', ['class_id' => $selectedClass?->id, 'days_count' => 5]) }}" class="btn btn-outline btn-sm flex items-center gap-1.5 text-slate-600 shadow-2xs">
                    <svg class="w-3.5 h-3.5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>- Sembunyikan Sabtu (5 Hari)</span>
                </a>
            @endif

            <button type="button" onclick="window.print()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 shrink-0 text-bluedark/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak</span>
            </button>
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
                        <span>+ Kolom Sabtu</span>
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
            $periodsSorted = $periods->sortBy('period_number')->values();
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
                                    <span>+ Hari Ke-6 (Sabtu)</span>
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

                                    <!-- Quick Actions Edit / Hapus Period Row on Hover -->
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 no-print">
                                        <button type="button" onclick="openEditPeriodModal({{ $p->id }}, {{ $p->period_number }}, '{{ addslashes($p->label) }}', '{{ $startTime }}', '{{ $endTime }}')" class="p-1 rounded hover:bg-blue-100 text-blueprim" title="Ubah Jam Pelajaran">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
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
                                            : "Jam {$startPeriodNum} - {$endPeriodNum} (" . ($span * 45) . "m)";
                                    @endphp

                                    <td rowspan="{{ $span }}"
                                        class="score-cell-interactive p-1.5 align-top relative bg-white border-r border-b border-bluelight transition-all"
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
                                        
                                        <div class="schedule-card-inner p-2.5 h-full flex flex-col justify-between shadow-2xs group">
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
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 2 }}" class="py-12 text-center text-xs text-bluedark/40 italic">
                                Belum ada master jam pelajaran. Klik tombol <strong>+ Jam Pelajaran Baru</strong> untuk menambahkan baris jam pertama.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Baris Tambah Jam Pelajaran di Bagian Paling Bawah (Konsep Kolom Bawah / Tambah Jam) -->
                    <tr class="bg-blue-50/40 border-t-2 border-dashed border-blue-200 no-print">
                        <td class="sticky-col-period py-3 px-3.5 bg-blue-50/80 border-r border-blue-200">
                            <button type="button" onclick="openAddPeriodModal()" class="w-full text-left font-bold text-xs text-blueprim hover:text-bluedark flex items-center gap-1.5 transition-colors">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>+ Tambah Baris Jam</span>
                            </button>
                        </td>
                        <td colspan="{{ count($days) + ($daysCount < 6 ? 1 : 0) }}" class="py-3 px-4 text-xs text-bluedark/60 font-medium">
                            <span class="cursor-pointer hover:text-blueprim hover:underline" onclick="openAddPeriodModal()">
                                💡 Klik di sini untuk menambah jam pelajaran berikutnya (contoh: Jam ke-{{ ($periodsSorted->max('period_number') ?? 0) + 1 }}) ke arah bawah tabel.
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Keterangan & Footer Navigasi Spreadsheet -->
        <div class="flex items-center justify-between text-[11px] text-bluedark/60 pt-1 flex-wrap gap-3">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-100 border-2 border-blue-500 inline-block"></span> Sel Terpilih</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-white border border-slate-300 inline-block"></span> Sel Terisi Jadwal</span>
                <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-slate-50 border border-slate-200 inline-block"></span> Sel Kosong (Bebas)</span>
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
                <h3 class="font-heading font-semibold text-bluedark text-sm">Master Jam Pelajaran Sekolah</h3>
                <p class="text-xs text-bluedark/50">Urutan jam pelajaran yang berlaku untuk seluruh rombel</p>
            </div>
            <button type="button" onclick="openAddPeriodModal()" class="btn btn-outline btn-sm text-xs">
                + Tambah Jam Pelajaran
            </button>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto pb-2 db-scroll">
            @forelse($periodsSorted as $p)
                <div class="px-3.5 py-2.5 rounded-xl border border-bluelight bg-white text-center shrink-0 shadow-2xs hover:border-blueprim transition-colors group relative">
                    <div class="text-xs font-bold text-bluedark">Jam ke-{{ $p->period_number }}</div>
                    <div class="text-[10px] font-mono text-bluedark/60 font-medium">
                        {{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($p->end_time)->format('H:i') }}
                    </div>
                    <div class="pt-1 text-[9px] text-blueprim font-semibold truncate max-w-[110px]">
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

            <!-- Hari (Kolom) -->
            <div>
                <label class="f-label font-bold text-xs text-bluedark">Hari (Kolom)</label>
                <select name="day_of_week" id="inputDayOfWeek" required class="f-select font-medium text-xs">
                    @foreach($allDays as $dNum => $dName)
                        <option value="{{ $dNum }}">{{ $dName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mata Pelajaran & Guru Pengampu -->
            <div>
                <label class="f-label font-bold text-xs text-bluedark">Mata Pelajaran &amp; Guru Pengampu <span class="text-rose-500">*</span></label>
                <select name="teaching_assignment_id" id="inputAssignmentId" required class="f-select font-medium text-xs">
                    <option value="">-- Pilih Mata Pelajaran &amp; Guru --</option>
                    @foreach($assignments as $a)
                        <option value="{{ $a->id }}">
                            {{ $a->subject?->name }} — {{ $a->teacher?->full_name }}
                        </option>
                    @endforeach
                </select>
                @if($assignments->isEmpty())
                    <p class="text-[11px] text-amber-600 mt-1 leading-snug">
                        ⚠️ Belum ada penugasan guru untuk kelas ini. Tambahkan di menu <strong>Penugasan Guru</strong> terlebih dahulu.
                    </p>
                @endif
            </div>

            <!-- Rentang Jam Pelajaran (Bisa 1 jam atau multi jam sekaligus) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Mulai Jam Ke-</label>
                    <select name="start_period_id" id="inputStartPeriodId" required class="f-select font-medium text-xs">
                        @foreach($periodsSorted as $p)
                            <option value="{{ $p->id }}" data-num="{{ $p->period_number }}">
                                Jam {{ $p->period_number }} ({{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Selesai Jam Ke-</label>
                    <select name="end_period_id" id="inputEndPeriodId" required class="f-select font-medium text-xs">
                        @foreach($periodsSorted as $p)
                            <option value="{{ $p->id }}" data-num="{{ $p->period_number }}">
                                Jam {{ $p->period_number }} ({{ \Carbon\Carbon::parse($p->end_time)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
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
<!-- MODAL 3: TAMBAH BARIS JAM PELAJARAN (KOLOM BAWAH / ROW BARU)               -->
<!-- ========================================================================= -->
@php
    $nextPeriodNum = ($periodsSorted->max('period_number') ?? 0) + 1;
    $lastPeriod = $periodsSorted->last();
    $nextStartTime = $lastPeriod ? \Carbon\Carbon::parse($lastPeriod->end_time)->format('H:i') : '07:00';
    $nextEndTime = $lastPeriod ? \Carbon\Carbon::parse($lastPeriod->end_time)->addMinutes(45)->format('H:i') : '07:45';
@endphp

<div id="periodModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <div>
                <h3 class="font-heading font-bold text-base text-bluedark" id="periodModalTitle">Tambah Jam Pelajaran Baru</h3>
                <p class="text-xs text-bluedark/60">Menambah baris jam ke bawah pada tabel jadwal</p>
            </div>
            <button type="button" onclick="closePeriodModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="periodForm" method="POST" action="{{ route('admin.academic.schedules.periods.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="periodHttpMethod" value="POST">

            <div>
                <label class="f-label font-bold text-xs text-bluedark">Jam Ke- (Nomor Urut)</label>
                <input type="number" name="period_number" id="inputPeriodNumber" min="1" max="20" required class="f-input text-xs font-semibold" value="{{ $nextPeriodNum }}">
            </div>

            <div>
                <label class="f-label font-bold text-xs text-bluedark">Label Jam Pelajaran</label>
                <input type="text" name="label" id="inputPeriodLabel" required class="f-input text-xs" value="Jam Ke-{{ $nextPeriodNum }}">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Mulai (WIB)</label>
                    <input type="text" name="start_time" id="inputPeriodStartTime" required class="f-input text-xs font-mono" value="{{ $nextStartTime }}" placeholder="07:00">
                </div>
                <div>
                    <label class="f-label font-bold text-xs text-bluedark">Selesai (WIB)</label>
                    <input type="text" name="end_time" id="inputPeriodEndTime" required class="f-input text-xs font-mono" value="{{ $nextEndTime }}" placeholder="07:45">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePeriodModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs">Simpan Jam Pelajaran</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // State Tracker Interaktif ala Guru Daftar Nilai / Excel
    let activeCell = null;
    let currentSelectedScheduleId = null;

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

        posEl.innerHTML = `Hari ${dayName} &bull; Jam ke-${periodNum} (${periodTime})`;

        // Isi form controls
        document.getElementById('inputDayOfWeek').value = day;
        document.getElementById('inputAssignmentId').value = assignmentId || '';
        document.getElementById('inputStartPeriodId').value = startPeriodId;
        document.getElementById('inputEndPeriodId').value = endPeriodId;
        document.getElementById('inputRoom').value = roomName;

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
     * Modal Jam Pelajaran Baru (Tambah Baris ke Bawah)
     */
    function openAddPeriodModal() {
        document.getElementById('periodModalTitle').textContent = 'Tambah Jam Pelajaran Baru';
        document.getElementById('periodForm').action = `{{ route('admin.academic.schedules.periods.store') }}`;
        document.getElementById('periodHttpMethod').value = 'POST';
        document.getElementById('inputPeriodNumber').value = '{{ $nextPeriodNum }}';
        document.getElementById('inputPeriodLabel').value = 'Jam Ke-{{ $nextPeriodNum }}';
        document.getElementById('inputPeriodStartTime').value = '{{ $nextStartTime }}';
        document.getElementById('inputPeriodEndTime').value = '{{ $nextEndTime }}';
        document.getElementById('periodModal').classList.remove('hidden');
    }

    function openEditPeriodModal(id, number, label, startTime, endTime) {
        document.getElementById('periodModalTitle').textContent = `Ubah Jam Pelajaran ke-${number}`;
        document.getElementById('periodForm').action = `{{ url('/admin/academic/schedules/periods') }}/${id}`;
        document.getElementById('periodHttpMethod').value = 'PUT';
        document.getElementById('inputPeriodNumber').value = number;
        document.getElementById('inputPeriodLabel').value = label;
        document.getElementById('inputPeriodStartTime').value = startTime;
        document.getElementById('inputPeriodEndTime').value = endTime;
        document.getElementById('periodModal').classList.remove('hidden');
    }

    function closePeriodModal() {
        document.getElementById('periodModal').classList.add('hidden');
    }

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
