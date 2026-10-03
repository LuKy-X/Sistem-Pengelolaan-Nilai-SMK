@extends('layouts.admin')

@section('title', 'Rekapitulasi Absensi & Jurnal Kelas')

@push('styles')
<style>
/* =========================================================
   Tabel Jurnal Kelas - Header & Color Styling (Sinkron Guru)
   ========================================================= */
.journal-tbl {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.82rem;
}

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

.journal-tbl thead th.th-navy {
  background-color: #0D47A1 !important;
}
.journal-tbl thead th.th-center {
  text-align: center !important;
}
.journal-tbl thead th.th-left {
  text-align: left !important;
}

.journal-tbl thead th.th-jumlah {
  background-color: #1565C0 !important;
  letter-spacing: 0.07em !important;
  text-align: center !important;
}

.journal-tbl thead th.th-hadir {
  background-color: #0D47A1 !important;
  text-align: center !important;
}

.journal-tbl thead th.th-absensi {
  background-color: #1E88E5 !important;
  text-align: center !important;
}

.journal-tbl thead th.th-sia {
  background-color: #1E88E5 !important;
  text-align: center !important;
  padding: 0.5rem 0.25rem !important;
  font-weight: 800 !important;
}

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

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Rekapitulasi Absensi &amp; Jurnal Kelas</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    Mode Admin (Read-Only)
                </span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">Pantau presensi siswa harian seluruh rombel yang terdata otomatis melalui jurnal mengajar per mapel oleh guru</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Dropdown Ekspor -->
            <div class="relative inline-block text-left" id="exportDropdownWrapper">
                <button type="button" 
                        onclick="toggleExportMenu(event)"
                        class="btn btn-outline btn-sm flex items-center gap-2 bg-white shadow-2xs hover:bg-slate-50 transition-colors">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Ekspor Data</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div id="exportMenu" style="display: none;" class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl border border-slate-200 py-2 z-50">
                    <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Format Excel (.xlsx)
                    </div>
                    <a href="{{ route('admin.attendance.export', array_merge(request()->query(), ['format' => 'excel', 'type' => 'sessions'])) }}" 
                       class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" download>
                        <div class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-xs font-bold">X</div>
                        <div>
                            <div>Rekap Sesi Jurnal (.xlsx)</div>
                            <div class="text-[10px] text-slate-400 font-normal">Data jam, materi &amp; rekap siswa</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.attendance.export', array_merge(request()->query(), ['format' => 'excel', 'type' => 'students'])) }}" 
                       class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" download>
                        <div class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-xs font-bold">X</div>
                        <div>
                            <div>Rekap Presensi Siswa (.xlsx)</div>
                            <div class="text-[10px] text-slate-400 font-normal">Daftar siswa hadir/absen lengkap</div>
                        </div>
                    </a>

                    <div class="my-1 border-t border-slate-100"></div>

                    <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Format CSV (.csv)
                    </div>
                    <a href="{{ route('admin.attendance.export', array_merge(request()->query(), ['format' => 'csv', 'type' => 'sessions'])) }}" 
                       class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        <span>CSV Sesi Jurnal</span>
                    </a>
                    <a href="{{ route('admin.attendance.export', array_merge(request()->query(), ['format' => 'csv', 'type' => 'students'])) }}" 
                       class="flex items-center gap-2 px-3.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        <span>CSV Siswa Lengkap</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Komprehensif -->
    <div class="panel p-5 bg-white border border-bluelight shadow-2xs rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blueprim"></span>
                <h2 class="font-heading font-semibold text-bluedark text-sm md:text-[15px]">Filter Data Presensi &amp; Jurnal</h2>
            </div>

            <!-- Quick Day & Week Navigation -->
            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['date' => $prevDate])) }}" 
                   class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition-colors" title="Hari Sebelumnya">
                    &larr; {{ \Carbon\Carbon::parse($prevDate)->format('d/m') }}
                </a>

                <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['date' => $todayStr])) }}" 
                   class="px-2.5 py-1 rounded-lg {{ $isToday ? 'bg-blueprim text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700' }} border border-slate-200 transition-colors">
                    Hari Ini
                </a>

                <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['date' => $nextDate])) }}" 
                   class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition-colors" title="Hari Berikutnya">
                    {{ \Carbon\Carbon::parse($nextDate)->format('d/m') }} &rarr;
                </a>

                <div class="h-4 w-px bg-slate-200 mx-1"></div>

                <!-- Week indicator -->
                <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-100/70 px-2 py-1 rounded-lg">
                    <span>Minggu:</span>
                    <span class="text-slate-700 font-bold">{{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.attendance.index') }}" id="attendanceFilterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            <input type="hidden" name="layout" value="{{ $layout }}">
            @if($tableTab)
                <input type="hidden" name="table_tab" value="{{ $tableTab }}">
            @endif

            <!-- 1. Tanggal -->
            <div>
                <label class="f-label text-xs">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="f-input text-xs py-2 bg-slate-50/50" onchange="this.form.submit()">
            </div>

            <!-- 2. Kelas / Rombel -->
            <div>
                <label class="f-label text-xs">Kelas / Rombel</label>
                <select name="class_id" class="f-select text-xs py-2" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Jurusan / Departemen -->
            <div>
                <label class="f-label text-xs">Jurusan</label>
                <select name="department_id" class="f-select text-xs py-2" onchange="this.form.submit()">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Guru Pengajar -->
            <div>
                <label class="f-label text-xs">Guru Pengajar</label>
                <select name="teacher_id" class="f-select text-xs py-2" onchange="this.form.submit()">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Mata Pelajaran -->
            <div>
                <label class="f-label text-xs">Mata Pelajaran</label>
                <select name="subject_id" class="f-select text-xs py-2" onchange="this.form.submit()">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ $subjectId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 6. Status Pengisian Jurnal -->
            <div>
                <label class="f-label text-xs">Status Jurnal</label>
                <select name="journal_status" class="f-select text-xs py-2" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="filled" {{ $journalStatus == 'filled' ? 'selected' : '' }}>Sudah Terisi</option>
                    <option value="unfilled" {{ $journalStatus == 'unfilled' ? 'selected' : '' }}>Belum Diisi / Terlewat</option>
                </select>
            </div>

            <div class="col-span-full flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                <div class="text-slate-500 font-medium">
                    Hari: <span class="font-bold text-bluedark">{{ $dayName }}, {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                    @if($selectedClass)
                        &middot; Filter Kelas: <span class="font-bold text-blueprim">{{ $selectedClass->name }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm text-xs px-3.5 py-1.5">
                        Terapkan Filter
                    </button>
                    @if($classId || $departmentId || $teacherId || $subjectId || $journalStatus || $date !== $todayStr)
                        <a href="{{ route('admin.attendance.index', ['layout' => $layout]) }}" class="btn btn-outline btn-sm text-xs px-3 py-1.5 text-slate-500 hover:text-slate-800">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- 5 KPI Ringkasan Statistik -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- 1. Total Sesi Terjadwal -->
        <div class="p-4 rounded-2xl bg-white border border-bluelight shadow-2xs transition-all hover:shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Jadwal Sesi</span>
                <span class="w-7 h-7 rounded-xl bg-blue-50 text-blueprim flex items-center justify-center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-bluedark font-heading leading-tight">{{ $stats['total_schedules'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Sesi Pelajaran Hari Ini</div>
        </div>

        <!-- 2. Jurnal Terisi & Persentase -->
        <div class="p-4 rounded-2xl bg-white border border-emerald-200/80 bg-gradient-to-br from-emerald-50/20 to-white shadow-2xs transition-all hover:shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-800">Jurnal Terisi</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                    {{ $stats['completion_rate'] }}%
                </span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 font-heading leading-tight">
                {{ $stats['filled_journals'] }} <span class="text-xs font-semibold text-slate-400">/ {{ $stats['total_schedules'] }}</span>
            </div>
            <div class="w-full bg-emerald-100 h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $stats['completion_rate']) }}%"></div>
            </div>
        </div>

        <!-- 3. Belum Diisi / Terlewat -->
        <div class="p-4 rounded-2xl bg-white border {{ $stats['unfilled_journals'] > 0 ? 'border-amber-200/90 bg-gradient-to-br from-amber-50/20 to-white' : 'border-bluelight' }} shadow-2xs transition-all hover:shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold {{ $stats['unfilled_journals'] > 0 ? 'text-amber-900' : 'text-slate-500' }}">Belum Diisi Guru</span>
                <span class="w-7 h-7 rounded-xl {{ $stats['unfilled_journals'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold {{ $stats['unfilled_journals'] > 0 ? 'text-amber-700' : 'text-slate-400' }} font-heading leading-tight">
                {{ $stats['unfilled_journals'] }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $isPast ? 'Sesi terlewat belum tercatat' : 'Sesi menunggu pengisian' }}</div>
        </div>

        <!-- 4. Tingkat Kehadiran Siswa -->
        <div class="p-4 rounded-2xl bg-white border border-bluelight shadow-2xs transition-all hover:shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Presensi Siswa</span>
                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blueprim font-bold text-[10px]">
                    {{ $stats['attendance_rate'] }}% Hadir
                </span>
            </div>
            <div class="text-2xl font-extrabold text-blueprim font-heading leading-tight">
                {{ $stats['total_hadir'] }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Total siswa hadir pada sesi terisi</div>
        </div>

        <!-- 5. Rekap Siswa Tidak Hadir (S/I/A) -->
        <div class="p-4 rounded-2xl bg-white border border-bluelight shadow-2xs transition-all hover:shadow-sm col-span-2 sm:col-span-1 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Tidak Hadir (S/I/A)</span>
                <span class="text-xs font-bold text-slate-700 font-mono">{{ $stats['total_absence'] }} Siswa</span>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-mono font-bold" title="Sakit">
                    <span class="text-[10px] text-amber-600 font-bold">S:</span> {{ $stats['total_sakit'] }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 text-xs font-mono font-bold" title="Izin">
                    <span class="text-[10px] text-blue-600 font-bold">I:</span> {{ $stats['total_izin'] }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-xs font-mono font-bold" title="Alpha">
                    <span class="text-[10px] text-rose-600 font-bold">A:</span> {{ $stats['total_alpha'] }}
                </span>
            </div>
            <div class="text-[10px] text-slate-400 mt-2">Akumulasi ketidakhadiran harian</div>
        </div>
    </div>

    <!-- Layout Switcher & Mode Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-white border border-bluelight shadow-2xs">
        <div class="flex items-center gap-2 flex-wrap">
            @if($selectedClass)
                <a href="{{ route('admin.attendance.index', array_merge(request()->except('class_id'), ['layout' => $layout])) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-blueprim hover:underline bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-200">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Kembali ke Semua Kelas</span>
                </a>
                <span class="text-slate-300">/</span>
                <div class="text-xs font-bold text-bluedark">
                    Agenda Kelas: <span class="text-blueprim font-heading text-sm">{{ $selectedClass->name }}</span>
                    <span class="text-slate-400 font-normal">({{ $selectedClass->department?->name ?? 'SMK' }})</span>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mode Tampilan:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span>Semua Kelas ({{ $classesGrid->count() }} Rombel)</span>
                    </span>
                </div>
            @endif
        </div>

        <!-- 2 Layout Switcher Buttons -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl self-start sm:self-auto border border-slate-200/80">
            <!-- Button Tampilan Jadwal -->
            <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['layout' => 'schedule'])) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $layout === 'schedule' ? 'bg-white text-blueprim shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Tampilan Jadwal</span>
            </a>

            <!-- Button Tampilan Tabel -->
            <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['layout' => 'table'])) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $layout === 'table' ? 'bg-white text-blueprim shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                <span>Tampilan Tabel</span>
            </a>
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- A. TAMPILAN JADWAL (SCHEDULE LAYOUT)                                      --}}
    {{-- ========================================================================= --}}
    @if($layout === 'schedule')

        {{-- Sub-case 1: Mode Semua Kelas (Grid Kartu Seluruh Kelas) --}}
        @if(! $selectedClass)
            <div class="panel p-5 md:p-6 bg-white border border-bluelight rounded-2xl shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="font-heading font-semibold text-bluedark text-base md:text-lg">Jurnal &amp; Presensi Seluruh Kelas</h2>
                        <p class="text-xs md:text-sm text-bluedark/50 mt-0.5">
                            Status kelengkapan pengisian jurnal mengajar &amp; presensi per rombel pada {{ $dayName }}, {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @forelse($classesGrid as $item)
                        @php
                            $c = $item['class'];
                            $cardBorderClass = match($item['status_code']) {
                                'overdue' => 'border-rose-300 ring-2 ring-rose-400/20 hover:border-rose-500 bg-gradient-to-br from-rose-50/15 to-white',
                                'partial' => 'border-amber-300 ring-2 ring-amber-400/20 hover:border-amber-500 bg-gradient-to-br from-amber-50/15 to-white',
                                'today_unfilled' => 'border-blue-300 ring-2 ring-blue-400/20 hover:border-blue-500 bg-gradient-to-br from-blue-50/15 to-white',
                                'completed' => 'border-emerald-200 hover:border-emerald-400 bg-gradient-to-br from-emerald-50/10 to-white',
                                default => 'border-bluelight/80 hover:border-blueprim hover:shadow-md bg-white',
                            };
                        @endphp
                        <div class="kelas-card block group p-5 md:p-6 rounded-2xl border {{ $cardBorderClass }} transition-all text-left relative overflow-hidden shadow-2xs hover:shadow-md">
                            @if($item['status_code'] === 'overdue')
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-rose-500 to-amber-500"></div>
                            @elseif($item['status_code'] === 'completed')
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                            @elseif($item['status_code'] === 'partial')
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 to-orange-400"></div>
                            @elseif($item['status_code'] === 'today_unfilled')
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                            @endif

                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors bg-blue-50 text-blueprim mt-0.5">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item['badge_class'] }} shadow-2xs">
                                    @if($item['status_code'] === 'completed')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600"><polyline points="20 6 9 17 4 12"/></svg>
                                    @elseif($item['status_code'] === 'partial')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-amber-600"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    @elseif($item['status_code'] === 'overdue')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-rose-600"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    @elseif($item['status_code'] === 'today_unfilled')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-blue-600"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-500"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    @endif
                                    <span>{{ $item['status_label'] }}</span>
                                </span>
                            </div>

                            <div class="font-heading font-bold text-bluedark text-base md:text-lg mb-1 group-hover:text-blueprim transition-colors">
                                {{ $c->name }}
                            </div>
                            <div class="text-xs text-bluedark/60 mb-3 flex items-center justify-between">
                                <span>{{ $c->department?->name ?? 'Kompetensi Keahlian' }}</span>
                                <span class="font-mono text-[11px] text-slate-500">{{ $item['student_count'] }} Siswa</span>
                            </div>

                            <!-- Wali Kelas & Jam Sesi -->
                            <div class="text-xs text-slate-600 bg-slate-50/80 rounded-xl p-2.5 mb-3 border border-slate-100 flex items-center justify-between">
                                <span class="truncate">
                                    <span class="text-slate-400 font-medium">Wali Kelas:</span> 
                                    <span class="font-semibold text-slate-700">{{ $c->homeroomTeacher?->full_name ?? ($c->homeroomTeacher?->user?->name ?? '-') }}</span>
                                </span>
                                <span class="font-bold text-blueprim shrink-0 ml-1">{{ $item['schedules_count'] }} Sesi Terjadwal</span>
                            </div>

                            <!-- Daftar Sesi & Status Pengisian -->
                            @if(count($item['sched_items']) > 0)
                                <div class="space-y-1.5 mb-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jadwal Mapel Hari Ini:</div>
                                    @foreach($item['sched_items'] as $sItem)
                                        <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg {{ $sItem['is_filled'] ? 'bg-emerald-50/60 text-emerald-950 border border-emerald-200/60' : 'bg-slate-50 text-slate-700 border border-slate-100' }}">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span class="font-mono font-bold text-[11px] px-1.5 py-0.5 rounded {{ $sItem['is_filled'] ? 'bg-emerald-200/70 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                                                    {{ $sItem['period_label'] }}
                                                </span>
                                                <span class="font-medium truncate" title="{{ $sItem['subject'] }} ({{ $sItem['teacher'] }})">
                                                    {{ $sItem['subject'] }} <span class="text-slate-400 text-[11px]">({{ $sItem['teacher'] }})</span>
                                                </span>
                                            </div>
                                            @if($sItem['is_filled'])
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 shrink-0 ml-1">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <span>Diisi</span>
                                                </span>
                                            @else
                                                <span class="text-[11px] font-semibold text-amber-700 shrink-0 ml-1">Belum</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Presensi Summary Badge -->
                            @if($item['filled_count'] > 0)
                                <div class="flex items-center gap-2 pt-2 pb-3 border-t border-slate-100 text-xs">
                                    <span class="text-[11px] text-slate-400">Kehadiran:</span>
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-mono font-bold text-[11px] border border-emerald-200">
                                        H: {{ $item['hadir'] }}
                                    </span>
                                    @if($item['sakit'] > 0)
                                        <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800 font-mono font-bold text-[11px] border border-amber-200">
                                            S: {{ $item['sakit'] }}
                                        </span>
                                    @endif
                                    @if($item['izin'] > 0)
                                        <span class="px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-800 font-mono font-bold text-[11px] border border-blue-200">
                                            I: {{ $item['izin'] }}
                                        </span>
                                    @endif
                                    @if($item['alpha'] > 0)
                                        <span class="px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-800 font-mono font-bold text-[11px] border border-rose-200">
                                            A: {{ $item['alpha'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Card Action Button -->
                            <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['class_id' => $c->id, 'layout' => 'schedule'])) }}" 
                               class="btn btn-outline btn-sm w-full text-xs font-semibold py-2 justify-center group-hover:bg-blueprim group-hover:text-white group-hover:border-blueprim transition-all flex items-center gap-1.5">
                                <span>Buka Jurnal Kelas {{ $c->name }}</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center text-sm text-bluedark/50">
                            Tidak ada data kelas yang sesuai dengan filter yang dipilih.
                        </div>
                    @endforelse
                </div>
            </div>

        {{-- Sub-case 2: Mode Per Kelas (Tabel Agenda Timetable Jam 1..N Sinkron Guru) --}}
        @else
            <div class="panel p-5 md:p-6 bg-white border border-bluelight rounded-2xl shadow-sm">
                <!-- Class Header Banner -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-heading font-bold text-bluedark text-lg md:text-xl">
                                Agenda Jurnal &amp; Presensi: Kelas {{ $selectedClass->name }}
                            </h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $dayName }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $enrolledStudents->count() }} Siswa Terdaftar
                            </span>
                        </div>
                        <p class="text-xs md:text-sm text-bluedark/60 mt-1">
                            {{ $selectedClass->department?->name ?? 'Kompetensi Keahlian' }} &middot; 
                            Wali Kelas: <span class="font-semibold text-slate-700">{{ $selectedClass->homeroomTeacher?->full_name ?? ($selectedClass->homeroomTeacher?->user?->name ?? '-') }}</span> &middot;
                            Tanggal: <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Tampilan Read-Only
                        </span>
                    </div>
                </div>

                <!-- Table Agenda Timetable -->
                <div class="overflow-x-auto db-scroll border border-slate-200 rounded-xl shadow-2xs">
                    <table class="journal-tbl w-full text-left text-xs">
                        <thead>
                            <tr>
                                <th rowspan="3" class="th-navy th-center whitespace-nowrap" style="width: 100px;">Hari / Tanggal</th>
                                <th rowspan="3" class="th-navy th-center whitespace-nowrap" style="width: 85px;">Jam ke-</th>
                                <th rowspan="3" class="th-navy th-left whitespace-nowrap" style="min-width: 140px;">Mata Pelajaran</th>
                                <th rowspan="3" class="th-navy th-left whitespace-nowrap" style="min-width: 150px;">Nama Guru</th>
                                <th rowspan="3" class="th-navy th-left" style="min-width: 190px;">Materi Pokok</th>
                                <th colspan="4" class="th-jumlah">Jumlah Siswa</th>
                                <th rowspan="3" class="th-navy th-left" style="min-width: 200px;">Keterangan / Siswa Absen</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="th-hadir" style="width: 55px;">Hadir</th>
                                <th colspan="3" class="th-absensi">Absensi</th>
                            </tr>
                            <tr>
                                <th class="th-sia" style="width: 36px;">S</th>
                                <th class="th-sia" style="width: 36px;">I</th>
                                <th class="th-sia" style="width: 36px;">A</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @php
                                $coveredPeriodNumbers = [];
                                $dateFormatted = \Carbon\Carbon::parse($date)->format('d/m/Y');
                            @endphp

                            @if($allDayPeriods->isNotEmpty())
                                @foreach($allDayPeriods as $period)
                                    @if($period->is_break)
                                        <!-- Baris Jam Istirahat Melintang Penuh Sinkron Guru -->
                                        <tr class="bg-amber-50/70 border-y-2 border-amber-200/80 hover:bg-amber-50 transition-colors">
                                            <td colspan="10" class="py-2.5 px-4 text-center">
                                                <div class="inline-flex items-center justify-center gap-2 text-amber-950 font-bold text-xs tracking-wide">
                                                    <span class="w-6 h-6 rounded-lg bg-amber-200/80 text-amber-900 flex items-center justify-center shrink-0">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                    </span>
                                                    <span class="uppercase tracking-wider font-extrabold text-amber-900">{{ strtoupper($period->name) }}</span>
                                                    <span class="px-2 py-0.5 rounded-full bg-amber-200/70 text-amber-900 font-mono text-[11px] font-semibold">
                                                        {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                                                    </span>
                                                    <span class="text-[11px] text-amber-800/80 font-normal italic ml-1">&middot; Jam Istirahat Sekolah</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @php
                                            $pNum = $period->period_number;
                                        @endphp

                                        @if(in_array($pNum, $coveredPeriodNumbers))
                                            @continue
                                        @endif

                                        @php
                                            // Check if there is a journal for this period
                                            $j = $classJournals->first(function($item) use ($pNum, $period) {
                                                $startNum = $item->startPeriod?->period_number ?? $item->start_period_id;
                                                return $startNum == $pNum || $item->start_period_id == $period->id;
                                            });

                                            // Check if there is a scheduled assignment for this period
                                            $matchedSched = $classSchedules->first(function($s) use ($pNum, $period) {
                                                $startNum = $s->startPeriod?->period_number;
                                                $endNum = $s->endPeriod?->period_number ?? $startNum;
                                                return ($pNum >= min($startNum, $endNum) && $pNum <= max($startNum, $endNum));
                                            });
                                        @endphp

                                        {{-- 1. Sesi Jurnal yang Sudah Diisi Guru --}}
                                        @if($j)
                                            @php
                                                $startNum = $j->startPeriod?->period_number ?? $pNum;
                                                $endNum = $j->endPeriod?->period_number ?? $startNum;
                                                for ($k = min($startNum, $endNum); $k <= max($startNum, $endNum); $k++) {
                                                    $coveredPeriodNumbers[] = $k;
                                                }

                                                // Attendance details array for modal
                                                $attendanceMap = $j->attendances->keyBy('student_id');
                                                $attStudents = $j->attendances->pluck('student')->filter();
                                                $classStudentList = $enrolledStudents->concat($attStudents)->unique('id')->sortBy('full_name')->values();

                                                $allStudentsForModal = $classStudentList->map(function($st) use ($attendanceMap) {
                                                    $att = $attendanceMap->get($st->id);
                                                    $statusVal = $att ? ($att->status instanceof \App\Enums\AttendanceStatus ? $att->status->value : (string) $att->status) : 'PRESENT';
                                                    return [
                                                        'id' => $st->id,
                                                        'nis' => $st->nis ?? '-',
                                                        'name' => $st->full_name,
                                                        'status' => $statusVal,
                                                        'note' => $att?->note ?? '',
                                                    ];
                                                })->values();

                                                $modalPayload = [
                                                    'class_name' => $selectedClass->name,
                                                    'subject_name' => $j->teachingAssignment?->subject?->name ?? 'Mata Pelajaran',
                                                    'teacher_name' => $j->creator?->user?->name ?? ($j->teachingAssignment?->teacher?->full_name ?? ($j->teachingAssignment?->teacher?->user?->name ?? 'Guru')),
                                                    'date' => \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y'),
                                                    'period_label' => ($startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum} sd {$endNum}").' ('.substr($j->startPeriod?->start_time ?? $period->start_time, 0, 5).' - '.substr($j->endPeriod?->end_time ?? $period->end_time, 0, 5).')',
                                                    'material' => $j->material,
                                                    'notes' => $j->notes ?? '',
                                                    'hadir' => $j->hadir_count,
                                                    'sakit' => $j->sakit_count,
                                                    'izin' => $j->izin_count,
                                                    'alpha' => $j->alpha_count,
                                                    'students' => $allStudentsForModal,
                                                ];

                                                $absentStudents = $j->attendances->filter(fn($a) => $a->status !== \App\Enums\AttendanceStatus::Present);
                                            @endphp
                                            <tr class="hover:bg-blue-50/30 transition-colors">
                                                <td class="px-3 py-3 font-semibold text-xs whitespace-nowrap text-bluedark text-center align-middle">
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
                                                    {{ $j->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                                </td>
                                                <td class="px-3.5 py-3 text-xs text-slate-700 align-middle">
                                                    {{ $j->creator?->user?->name ?? ($j->teachingAssignment?->teacher?->full_name ?? 'Guru') }}
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
                                                    @if($absentStudents->isNotEmpty())
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($absentStudents as $att)
                                                                @php
                                                                    $bCls = match($att->status) {
                                                                        \App\Enums\AttendanceStatus::Sick => ['pill' => 'bg-amber-50 text-amber-900 border-amber-200', 'label' => 'Sakit'],
                                                                        \App\Enums\AttendanceStatus::Permit => ['pill' => 'bg-blue-50 text-blue-900 border-blue-200', 'label' => 'Izin'],
                                                                        \App\Enums\AttendanceStatus::Absent => ['pill' => 'bg-red-50 text-red-900 border-red-200', 'label' => 'Alpha'],
                                                                        default => ['pill' => 'bg-slate-50 text-slate-800 border-slate-200', 'label' => 'Hadir'],
                                                                    };
                                                                @endphp
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $bCls['pill'] }}">
                                                                    <span class="font-bold">{{ $att->student?->full_name ?? 'Siswa' }}</span>
                                                                    <span class="text-[10px] uppercase font-bold opacity-80">({{ $bCls['label'] }})</span>
                                                                    @if($att->note)
                                                                        <span class="text-[10px] opacity-70 italic">- {{ $att->note }}</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <span>Seluruh Siswa Hadir</span>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>

                                        {{-- 2. Sesi Terjadwal Namun Belum Diisi Guru --}}
                                        @elseif($matchedSched)
                                            <tr class="hover:bg-amber-50/30 transition-colors bg-amber-50/10">
                                                <td class="px-3 py-3 font-semibold text-xs whitespace-nowrap text-slate-500 text-center align-middle">
                                                    <div class="font-medium text-slate-600 text-xs capitalize">{{ $dayName }}</div>
                                                    <div class="text-[11px] font-mono text-slate-400 font-normal">{{ $dateFormatted }}</div>
                                                </td>
                                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                                    <span class="inline-flex flex-col items-center">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-amber-50 text-amber-900 border border-amber-200 font-mono font-bold text-xs">
                                                            Jam {{ $pNum }}
                                                        </span>
                                                        <span class="text-[10px] font-mono text-slate-400 mt-0.5">
                                                            {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td class="px-3.5 py-3 text-xs font-semibold text-slate-700 align-middle">
                                                    {{ $matchedSched->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                                </td>
                                                <td class="px-3.5 py-3 text-xs text-slate-700 align-middle">
                                                    {{ $matchedSched->teachingAssignment?->teacher?->full_name ?? ($matchedSched->teachingAssignment?->teacher?->user?->name ?? 'Guru') }}
                                                </td>
                                                <td class="px-3.5 py-3 text-xs text-amber-800/80 italic align-middle">
                                                    Belum ada materi (Jurnal belum diisi oleh guru pengajar)
                                                </td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-3.5 py-3 text-xs text-amber-700 italic align-middle">
                                                    Menunggu pencatatan jurnal &amp; presensi oleh guru
                                                </td>
                                            </tr>

                                        {{-- 3. Jam Pelajaran Tanpa Jadwal --}}
                                        @else
                                            <tr class="hover:bg-slate-50/60 transition-colors bg-white/40">
                                                <td class="px-3 py-3 font-semibold text-xs whitespace-nowrap text-slate-400 text-center align-middle">
                                                    <div class="font-medium text-slate-500 text-xs capitalize">{{ $dayName }}</div>
                                                    <div class="text-[11px] font-mono text-slate-400 font-normal">{{ $dateFormatted }}</div>
                                                </td>
                                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                                    <span class="inline-flex flex-col items-center">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-500 font-mono font-bold text-xs">
                                                            Jam {{ $pNum }}
                                                        </span>
                                                        <span class="text-[10px] font-mono text-slate-400 mt-0.5">
                                                            {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">Tidak ada jadwal</td>
                                                <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">-</td>
                                                <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">-</td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-2 py-3 text-center align-middle"><span class="text-slate-300 font-mono text-xs">-</span></td>
                                                <td class="px-3.5 py-3 text-xs text-slate-400 italic align-middle">-</td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- B. TAMPILAN TABEL (TABLE LAYOUT)                                          --}}
    {{-- ========================================================================= --}}
    @else
        <div class="panel p-5 md:p-6 bg-white border border-bluelight rounded-2xl shadow-sm space-y-5">
            <!-- Table Header Sub-tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['layout' => 'table', 'table_tab' => 'sessions'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $tableTab === 'sessions' ? 'bg-blueprim text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Rekap Sesi Jurnal ({{ $tableSessions->count() }} Sesi)</span>
                    </a>
                    <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['layout' => 'table', 'table_tab' => 'students'])) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $tableTab === 'students' ? 'bg-blueprim text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Catatan Siswa Lengkap ({{ $studentAttendances->total() }} Catatan)</span>
                    </a>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan data tanggal <span class="font-bold text-bluedark">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                </div>
            </div>

            {{-- 1. Tab Sesi Jurnal --}}
            @if($tableTab === 'sessions')
                <div class="overflow-x-auto">
                    <table class="tbl w-full text-left text-xs">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>Jam / Waktu</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengajar</th>
                                <th>Materi Pembelajaran</th>
                                <th class="text-center">Presensi (H | S | I | A)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tableSessions as $idx => $s)
                                <tr>
                                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="font-mono font-bold text-bluedark">{{ $s['period_label'] }}</div>
                                        <div class="text-[11px] font-mono text-slate-400">{{ $s['time_range'] }}</div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-900 border border-blue-200 font-bold text-xs">
                                            {{ $s['class_name'] }}
                                        </span>
                                    </td>
                                    <td class="font-semibold text-bluedark">{{ $s['subject_name'] }}</td>
                                    <td class="text-slate-700">{{ $s['teacher_name'] }}</td>
                                    <td class="max-w-xs text-slate-700">
                                        <div class="line-clamp-2 leading-relaxed">{{ $s['material'] }}</div>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        @if($s['type'] === 'filled')
                                            <div class="inline-flex items-center gap-1 font-mono text-xs">
                                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold" title="Hadir">
                                                    H: {{ $s['hadir_count'] }}
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-bold" title="Sakit">
                                                    S: {{ $s['sakit_count'] }}
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-200 font-bold" title="Izin">
                                                    I: {{ $s['izin_count'] }}
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 font-bold" title="Alpha">
                                                    A: {{ $s['alpha_count'] }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-slate-300 font-mono">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-slate-400">
                                        Belum ada jadwal sesi pembelajaran pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- 2. Tab Catatan Siswa Lengkap (Flat Student Records) --}}
            @else
                <div class="overflow-x-auto">
                    <table class="tbl w-full text-left text-xs">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengajar</th>
                                <th class="w-24 text-center">Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentAttendances as $idx => $att)
                                @php
                                    $statusVal = $att->status instanceof \App\Enums\AttendanceStatus ? $att->status->value : (string) $att->status;
                                    $badgeColor = match($statusVal) {
                                        'PRESENT' => 'badge-green',
                                        'PERMIT' => 'badge-blue',
                                        'SICK' => 'badge-yellow',
                                        'ABSENT' => 'badge-red',
                                        default => 'badge-gray',
                                    };
                                    $label = match($statusVal) {
                                        'PRESENT' => 'Hadir',
                                        'PERMIT' => 'Izin',
                                        'SICK' => 'Sakit',
                                        'ABSENT' => 'Alpha',
                                        default => $statusVal,
                                    };
                                @endphp
                                <tr>
                                    <td class="text-center font-mono">{{ $studentAttendances->firstItem() + $idx }}</td>
                                    <td class="font-mono text-xs">{{ $att->student?->nis ?? '-' }}</td>
                                    <td class="font-semibold text-bluedark">{{ $att->student?->full_name ?? '-' }}</td>
                                    <td>{{ $att->journal?->teachingAssignment?->schoolClass?->name ?? '-' }}</td>
                                    <td>{{ $att->journal?->teachingAssignment?->subject?->name ?? '-' }}</td>
                                    <td>{{ $att->journal?->creator?->user?->name ?? ($att->journal?->teachingAssignment?->teacher?->full_name ?? '-') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeColor }} text-[11px] font-bold">{{ $label }}</span>
                                    </td>
                                    <td class="text-xs text-bluedark/70">{{ $att->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-12 text-slate-400">
                                        Belum ada catatan presensi siswa pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $studentAttendances->links() }}
                </div>
            @endif
        </div>
    @endif

</div>

<!-- Modal Detail Presensi Siswa (Read-Only) -->
<div id="studentAttendanceModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modalClassName" role="dialog" aria-modal="true">
    <!-- Backdrop Overlay (blurs only page content behind modal) -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeStudentAttendanceModal()" aria-hidden="true"></div>

    <!-- Centering & Scroll Container -->
    <div class="fixed inset-0 z-10 overflow-y-auto pointer-events-none">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <!-- Modal Dialog (Elevated on z-20, pointer events enabled, completely crisp and unblurred) -->
            <div class="relative z-20 pointer-events-auto bg-white rounded-2xl text-left overflow-hidden shadow-2xl sm:my-8 sm:max-w-3xl sm:w-full border border-slate-200" onclick="event.stopPropagation()">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-[#0D47A1] to-[#1565C0] px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base md:text-lg" id="modalClassName">
                                Detail Jurnal &amp; Presensi Siswa
                            </h3>
                            <p class="text-xs text-blue-100 mt-0.5" id="modalSubjectAndTeacher">
                                Mata Pelajaran &middot; Guru Pengajar
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeStudentAttendanceModal()" class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors" title="Tutup Modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    <!-- Info Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-slate-400">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>Waktu &amp; Sesi</span>
                            </div>
                            <div class="font-semibold text-slate-800 mt-1" id="modalPeriodLabel">-</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-slate-400">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span>Tanggal</span>
                            </div>
                            <div class="font-semibold text-slate-800 mt-1" id="modalDateLabel">-</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <div class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-emerald-700">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Total Hadir</span>
                            </div>
                            <div class="font-bold text-emerald-800 mt-1 font-mono text-sm" id="modalHadirCount">0 Siswa</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                            <div class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-rose-700">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                <span>Tidak Hadir (S/I/A)</span>
                            </div>
                            <div class="font-bold text-rose-800 mt-1 font-mono text-sm" id="modalAbsenceCount">0 Siswa</div>
                        </div>
                    </div>

                    <!-- Materi Pokok & Catatan Guru -->
                    <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 text-xs">
                        <div class="flex items-center gap-1.5 font-bold text-blueprim mb-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>Materi Pokok Pembelajaran:</span>
                        </div>
                        <div class="text-slate-800 leading-relaxed font-medium pl-5" id="modalMaterial">-</div>

                        <div id="modalNotesWrapper" class="mt-2.5 pt-2.5 border-t border-blue-100/80" style="display: none;">
                            <div class="flex items-center gap-1.5 font-semibold text-slate-500 mb-1">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <span>Catatan Tambahan Guru:</span>
                            </div>
                            <div class="text-slate-700 italic pl-5" id="modalNotes">-</div>
                        </div>
                    </div>

                    <!-- Table Daftar Siswa & Presensi -->
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <h4 class="font-heading font-bold text-bluedark text-xs uppercase tracking-wider flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>Daftar Presensi Siswa Sesi Ini (<span id="modalTotalStudents">0</span> Siswa)</span>
                            </h4>
                            <div class="flex items-center gap-1.5 text-[10px] flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    H: Hadir
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    S: Sakit
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                                    I: Izin
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    A: Alpha
                                </span>
                            </div>
                        </div>

                        <div class="overflow-y-auto max-h-72 border border-slate-200 rounded-xl db-scroll">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200 sticky top-0">
                                    <tr>
                                        <th class="py-2.5 px-3 w-10 text-center font-bold text-slate-600">No</th>
                                        <th class="py-2.5 px-3 w-28 font-bold text-slate-600">NIS</th>
                                        <th class="py-2.5 px-3 font-bold text-slate-600">Nama Siswa</th>
                                        <th class="py-2.5 px-3 w-28 text-center font-bold text-slate-600">Status</th>
                                        <th class="py-2.5 px-3 font-bold text-slate-600">Keterangan / Alasan</th>
                                    </tr>
                                </thead>
                                <tbody id="modalStudentsTableBody" class="divide-y divide-slate-100 bg-white">
                                    <!-- Dynamic rows via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-400 italic flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                        <span>Data tersinkron langsung dari database jurnal pengajar</span>
                    </span>
                    <button type="button" onclick="closeStudentAttendanceModal()" class="btn btn-outline btn-sm text-xs py-1.5 px-4 font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle Dropdown Export
function toggleExportMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('exportDropdownWrapper');
    const menu = document.getElementById('exportMenu');
    if (menu && wrapper && !wrapper.contains(e.target)) {
        menu.style.display = 'none';
    }
});

// Helper XSS prevention
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Modal Detail Presensi Siswa
function openStudentAttendanceModal(data) {
    if (!data) return;

    document.getElementById('modalClassName').textContent = 'Detail Jurnal: Kelas ' + (data.class_name || '');
    document.getElementById('modalSubjectAndTeacher').textContent = (data.subject_name || '') + ' \u2022 Guru: ' + (data.teacher_name || '-');
    document.getElementById('modalPeriodLabel').textContent = data.period_label || '-';
    document.getElementById('modalDateLabel').textContent = data.date || '-';
    document.getElementById('modalMaterial').textContent = data.material || 'Belum ada catatan materi pembelajaran.';
    document.getElementById('modalHadirCount').textContent = (data.hadir || 0) + ' Siswa';

    const absenceTotal = (parseInt(data.sakit || 0) + parseInt(data.izin || 0) + parseInt(data.alpha || 0));
    document.getElementById('modalAbsenceCount').textContent = absenceTotal + ' Siswa (S:' + (data.sakit||0) + ' I:' + (data.izin||0) + ' A:' + (data.alpha||0) + ')';

    const notesWrapper = document.getElementById('modalNotesWrapper');
    if (data.notes && data.notes.trim() !== '') {
        notesWrapper.style.display = 'block';
        document.getElementById('modalNotes').textContent = data.notes;
    } else {
        notesWrapper.style.display = 'none';
    }

    const students = data.students || [];
    document.getElementById('modalTotalStudents').textContent = students.length;

    const tbody = document.getElementById('modalStudentsTableBody');
    tbody.innerHTML = '';

    if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada daftar siswa yang tercatat pada database untuk kelas dan sesi ini.</td></tr>';
    } else {
        students.forEach((st, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';

            let badgeHtml = '';
            const statusUpper = (st.status || 'PRESENT').toUpperCase();
            if (statusUpper === 'PRESENT') {
                badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>Hadir</span>';
            } else if (statusUpper === 'SICK') {
                badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Sakit</span>';
            } else if (statusUpper === 'PERMIT') {
                badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>Izin</span>';
            } else if (statusUpper === 'ABSENT') {
                badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Alpha</span>';
            } else {
                badgeHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">' + escapeHtml(statusUpper) + '</span>';
            }

            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center font-mono text-slate-400">${idx + 1}</td>
                <td class="py-2.5 px-3 font-mono text-slate-600">${escapeHtml(st.nis || '-')}</td>
                <td class="py-2.5 px-3 font-semibold text-slate-800">${escapeHtml(st.name || '-')}</td>
                <td class="py-2.5 px-3 text-center">${badgeHtml}</td>
                <td class="py-2.5 px-3 text-slate-500 italic">${escapeHtml(st.note || '-')}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    const modal = document.getElementById('studentAttendanceModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.classList.add('overflow-hidden');
    }
}

function closeStudentAttendanceModal() {
    const modal = document.getElementById('studentAttendanceModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }
}

// Close on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStudentAttendanceModal();
    }
});
</script>
@endpush
@endsection
