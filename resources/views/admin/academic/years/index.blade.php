@extends('layouts.admin')

@section('title', 'Tahun Ajaran & Semester')

@section('content')
<div class="space-y-5 max-w-full overflow-hidden">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-1">
                <span>Akademik</span>
                <span>/</span>
                <span class="text-blueprim font-medium">Tahun &amp; Semester</span>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Tahun Ajaran &amp; Semester</h1>
            <p class="text-xs md:text-sm text-bluedark/60 mt-0.5">Kelola kalender akademik SMK, tentukan periode semester aktif, serta atur tahun ajaran &amp; semester.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-xl p-1 bg-bluelight/70 border border-bluelight text-xs font-semibold">
                <span class="px-3 py-1.5 rounded-lg bg-blueprim text-white shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Per Tahun Ajaran</span>
                </span>
                <a href="{{ route('admin.academic.semesters.index') }}" class="px-3 py-1.5 rounded-lg text-bluedark/70 hover:text-bluedark hover:bg-white/60 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>Tabel Semester</span>
                </a>
            </div>

            <button type="button" onclick="openYearModal()" class="btn btn-primary btn-sm flex items-center gap-1.5 text-xs shadow-xs">
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tahun Ajaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Active Academic Year & Semester Card (Light Card matching Teacher Portal Style) -->
    <div class="panel p-5 bg-white border border-bluelight/80 rounded-2xl shadow-2xs overflow-hidden">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-bluelight/70">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blueprim/10 text-blueprim flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-sm">Status Kalender Akademik Aktif</h2>
                    <p class="text-[11px] text-bluedark/50">Periode tahun ajaran dan semester yang sedang berjalan untuk kegiatan belajar mengajar</p>
                </div>
            </div>
            @if($activeYear)
                <span class="badge badge-green text-xs font-semibold px-2.5 py-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span>Sistem Aktif</span>
                </span>
            @endif
        </div>

        @if($activeYear)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Box 1: Tahun Ajaran Aktif -->
                <div class="p-4 rounded-xl border border-bluelight bg-slate-50/70 space-y-2.5 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-bluedark/60 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Tahun Ajaran Aktif</span>
                        </span>
                        <span class="badge badge-blue text-[10px] font-semibold">Aktif</span>
                    </div>
                    <div>
                        <div class="font-heading font-bold text-lg sm:text-xl text-bluedark truncate">
                            Tahun Ajaran {{ $activeYear->name }}
                        </div>
                        <div class="text-xs text-bluedark/60 mt-0.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>{{ \Carbon\Carbon::parse($activeYear->start_date)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($activeYear->end_date)->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-bluelight/70 flex-wrap">
                        <span class="badge badge-gray text-[11px]">
                            {{ $activeYear->semesters->count() }} Semester Terdaftar
                        </span>
                        <button type="button" onclick="editYear({{ json_encode($activeYear) }})" class="btn btn-outline btn-sm text-[11px] py-1 px-2.5 flex items-center gap-1 text-bluedark hover:text-blueprim">
                            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Tahun</span>
                        </button>
                    </div>
                </div>

                <!-- Box 2: Semester Berjalan Aktif -->
                <div class="p-4 rounded-xl border border-bluelight bg-slate-50/70 space-y-2.5 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-bluedark/60 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>Semester Berjalan</span>
                        </span>
                        @if($activeSemester)
                            <span class="badge badge-green text-[10px] font-semibold">Aktif</span>
                        @endif
                    </div>

                    @if($activeSemester)
                        <div>
                            <div class="font-heading font-bold text-lg sm:text-xl text-bluedark truncate flex items-center gap-2">
                                <span class="truncate">{{ $activeSemester->name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold shrink-0 {{ $activeSemester->semester_number == 1 ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    Semester {{ $activeSemester->semester_number }}
                                </span>
                            </div>
                            <div class="text-xs text-bluedark/60 mt-0.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span>{{ \Carbon\Carbon::parse($activeSemester->start_date)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($activeSemester->end_date)->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-bluelight/70 flex-wrap">
                            <button type="button" onclick="editSemester({{ json_encode($activeSemester) }}, '{{ $activeSemester->academicYear?->name ?? $activeYear->name }}')" class="btn btn-outline btn-sm text-[11px] py-1 px-2.5 flex items-center gap-1 text-bluedark hover:text-blueprim">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span>Edit Semester</span>
                            </button>
                            <button type="button" onclick="openSemesterModal({{ $activeYear->id }}, '{{ $activeYear->name }}')" class="btn btn-primary btn-sm text-[11px] py-1 px-2.5 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tambah Semester</span>
                            </button>
                        </div>
                    @else
                        <div class="text-xs text-amber-800 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                            Belum ada semester yang aktif untuk tahun ajaran ini.
                        </div>
                        <div class="pt-1">
                            <button type="button" onclick="openSemesterModal({{ $activeYear->id }}, '{{ $activeYear->name }}')" class="btn btn-primary btn-sm text-[11px] py-1 px-2.5 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tambah Semester Sekarang</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-xl p-4 bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <strong class="font-bold text-sm block">Belum Ada Tahun Ajaran Aktif</strong>
                        <span>Silakan buat tahun ajaran baru atau aktifkan salah satu tahun ajaran di bawah ini.</span>
                    </div>
                </div>
                <button type="button" onclick="openYearModal()" class="btn btn-primary btn-sm shrink-0 text-xs">
                    Buat Tahun Ajaran
                </button>
            </div>
        @endif
    </div>

    <!-- Section: Seluruh Tahun Ajaran (Container matching Teacher Portal Panels) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="font-heading font-bold text-bluedark text-base md:text-lg">Daftar Seluruh Tahun Ajaran</h2>
                <span class="badge badge-gray text-xs font-semibold">
                    {{ $academicYears->total() }} Tahun
                </span>
            </div>
            <button type="button" onclick="openYearModal()" class="btn btn-outline btn-sm text-xs flex items-center gap-1.5">
                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Tahun</span>
            </button>
        </div>

        <div class="space-y-4">
            @forelse($academicYears as $year)
                <div class="panel p-5 bg-white border border-bluelight/80 rounded-2xl shadow-2xs space-y-4 overflow-hidden {{ $year->is_active ? 'ring-2 ring-blueprim/20' : '' }}">
                    
                    <!-- Year Header Area -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-bluelight/70">
                        <!-- Left: Year Identity -->
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $year->is_active ? 'bg-blueprim text-white shadow-xs' : 'bg-bluelight text-bluedark' }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-heading font-bold text-bluedark text-base sm:text-lg">
                                        Tahun Ajaran {{ $year->name }}
                                    </h3>
                                    @if($year->is_active)
                                        <span class="badge badge-green text-xs font-semibold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                            <span>Tahun Ajaran Aktif</span>
                                        </span>
                                    @else
                                        <span class="badge badge-gray text-xs font-medium">Non-Aktif</span>
                                    @endif
                                    <span class="badge badge-blue text-[11px] font-semibold">
                                        {{ $year->semesters->count() }} Semester
                                    </span>
                                    <span class="badge badge-gray text-[11px]">
                                        {{ $year->classes_count ?? $year->classes->count() }} Rombel Kelas
                                    </span>
                                </div>
                                <div class="text-xs text-bluedark/60 mt-0.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>Periode: <strong>{{ \Carbon\Carbon::parse($year->start_date)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($year->end_date)->translatedFormat('d M Y') }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Year Level Actions -->
                        <div class="flex items-center gap-1.5 shrink-0 flex-wrap self-start sm:self-center">
                            <form action="{{ route('admin.academic.years.toggle-active', $year) }}" method="POST" class="inline">
                                @csrf
                                @if($year->is_active)
                                    <button type="submit" class="btn btn-sm btn-outline border-emerald-500 text-emerald-700 hover:bg-emerald-50 text-xs font-semibold py-1 px-2.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span>Sedang Aktif</span>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-primary text-xs font-semibold py-1 px-2.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                                        <span>Jadikan Aktif</span>
                                    </button>
                                @endif
                            </form>

                            <button type="button" onclick="editYear({{ json_encode($year) }})" class="btn btn-outline btn-sm text-xs font-medium py-1 px-2.5 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span>Edit</span>
                            </button>

                            <form action="{{ route('admin.academic.years.destroy', $year) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Tahun Ajaran {{ $year->name }}? Penghapusan akan gagal jika terdapat data kelas atau semester yang terhubung.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger text-xs font-medium py-1 px-2.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sub-section: Semesters List Inside This Academic Year -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-bluedark flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    <span>Semester dalam Tahun Ajaran Ini</span>
                                </span>
                                <span class="badge badge-gray text-[10px]">
                                    {{ $year->semesters->count() }} Semester
                                </span>
                            </div>

                            <button type="button" onclick="openSemesterModal({{ $year->id }}, '{{ $year->name }}')" class="btn btn-sm bg-bluelight text-blueprim hover:bg-bluesoft/30 text-xs font-semibold py-1 px-2.5 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Tambah Semester</span>
                            </button>
                        </div>

                        @if($year->semesters->isNotEmpty())
                            <!-- Full-Width Responsive Semester Rows -->
                            <div class="space-y-2">
                                @foreach($year->semesters as $sem)
                                    <div class="p-3 sm:p-3.5 rounded-xl border transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $sem->is_active ? 'bg-emerald-50/40 border-emerald-300 ring-1 ring-emerald-300/40' : 'bg-slate-50/60 border-bluelight hover:bg-white' }}">
                                        
                                        <!-- Left: Chip, Nama, Periode, Status -->
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-7 h-7 rounded-lg {{ $sem->semester_number == 1 ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }} flex items-center justify-center font-heading font-bold text-xs shrink-0">
                                                {{ $sem->semester_number }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-heading font-bold text-bluedark text-xs sm:text-sm">
                                                        {{ $sem->name }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sem->semester_number == 1 ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                        Semester {{ $sem->semester_number }} ({{ $sem->semester_number == 1 ? 'Gasal' : 'Genap' }})
                                                    </span>
                                                    @if($sem->is_active)
                                                        <span class="badge badge-green text-[10px] font-semibold px-2 py-0.2 flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="badge badge-gray text-[10px] font-medium px-2 py-0.2">
                                                            Non-Aktif
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-bluedark/60 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                    <svg class="w-3 h-3 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                    <span>{{ \Carbon\Carbon::parse($sem->start_date)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($sem->end_date)->translatedFormat('d M Y') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Action Buttons (Aktifkan, Edit, Hapus) -->
                                        <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                            <!-- Toggle Aktif -->
                                            <form action="{{ route('admin.academic.semesters.toggle-active', $sem) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $sem->is_active ? 'btn-outline border-emerald-400 text-emerald-700 hover:bg-emerald-50' : 'btn-primary' }} text-[11px] py-1 px-2.5 flex items-center gap-1 font-semibold" title="{{ $sem->is_active ? 'Nonaktifkan Semester' : 'Aktifkan Semester Ini' }}">
                                                    @if($sem->is_active)
                                                        <svg class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                        <span>Nonaktifkan</span>
                                                    @else
                                                        <svg class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                                                        <span>Aktifkan</span>
                                                    @endif
                                                </button>
                                            </form>

                                            <!-- Edit Semester -->
                                            <button type="button" onclick="editSemester({{ json_encode($sem) }}, '{{ $year->name }}')" class="btn btn-outline btn-sm text-[11px] py-1 px-2.5 flex items-center gap-1 font-medium hover:border-blueprim hover:text-blueprim" title="Edit Semester">
                                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Hapus Semester -->
                                            <form action="{{ route('admin.academic.semesters.destroy', $sem) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semester \'{{ $sem->name }}\' (Tahun {{ $year->name }})? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger text-[11px] py-1 px-2.5 flex items-center gap-1 font-medium transition-colors" title="Hapus Semester">
                                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-bluelight p-4 text-center bg-slate-50/40">
                                <p class="text-xs text-bluedark/70 font-semibold">Belum ada data semester untuk Tahun Ajaran {{ $year->name }}.</p>
                                <p class="text-[11px] text-bluedark/50 mt-0.5">Tambahkan Semester 1 (Gasal) atau Semester 2 (Genap) agar dapat mengatur jadwal dan buku nilai.</p>
                                <button type="button" onclick="openSemesterModal({{ $year->id }}, '{{ $year->name }}')" class="btn btn-primary btn-sm text-xs mt-2.5 inline-flex items-center gap-1.5 py-1 px-3">
                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Tambah Semester Sekarang</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="panel p-8 text-center border border-dashed border-bluelight">
                    <h3 class="font-heading font-bold text-base text-bluedark">Belum Ada Tahun Ajaran Terdaftar</h3>
                    <p class="text-xs text-bluedark/60 max-w-sm mx-auto mt-1">Mulai dengan membuat tahun ajaran pertama Anda (misal: 2026/2027) untuk mengatur kalender akademik sekolah.</p>
                    <button type="button" onclick="openYearModal()" class="btn btn-primary btn-sm mt-3 inline-flex items-center gap-1.5 text-xs">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Tambah Tahun Ajaran Baru</span>
                    </button>
                </div>
            @endforelse
        </div>

        @if($academicYears->hasPages())
            <div class="mt-4 pt-3 border-t border-bluelight">
                {{ $academicYears->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal 1: Tambah / Edit Tahun Ajaran -->
<div id="yearModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl animate-in fade-in duration-150 border border-bluelight">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-bluelight">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-bluelight text-blueprim flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h3 id="yearModalTitle" class="font-heading font-bold text-base text-bluedark">Tambah Tahun Ajaran Baru</h3>
                    <p class="text-[11px] text-bluedark/50">Tentukan nama periode dan rentang tanggal</p>
                </div>
            </div>
            <button type="button" onclick="closeYearModal()" class="w-7 h-7 rounded-lg hover:bg-bluelight text-bluedark/50 hover:text-bluedark flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form id="yearForm" method="POST" action="{{ route('admin.academic.years.store') }}" class="space-y-3.5">
            @csrf
            <div id="yearMethodField"></div>

            <div>
                <label class="f-label" for="year_name">
                    Nama Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="year_name" required placeholder="Contoh: 2026/2027" class="f-input text-xs sm:text-sm">
                <span class="text-[10px] text-bluedark/50 mt-1 block">Format standar: TAHUN_AWAL/TAHUN_AKHIR (contoh: 2026/2027)</span>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="f-label" for="year_start_date">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="year_start_date" required class="f-input text-xs">
                </div>
                <div>
                    <label class="f-label" for="year_end_date">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="year_end_date" required class="f-input text-xs">
                </div>
            </div>

            <div class="p-2.5 rounded-xl bg-blue-50/60 border border-bluelight">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="year_is_active" value="1" class="rounded text-blueprim focus:ring-blueprim w-4 h-4">
                    <span class="text-xs font-semibold text-bluedark">Jadikan tahun ajaran ini aktif</span>
                </label>
                <p class="text-[10px] text-bluedark/60 ml-6 mt-0.5">Tahun ajaran aktif saat ini akan dinonaktifkan otomatis.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeYearModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" id="yearSubmitBtn" class="btn btn-primary btn-sm text-xs">Simpan Tahun Ajaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah / Edit Semester -->
<div id="semesterModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl animate-in fade-in duration-150 border border-bluelight max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-bluelight">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-bluelight text-blueprim flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <h3 id="semesterModalTitle" class="font-heading font-bold text-base text-bluedark">Tambah Semester Baru</h3>
                    <p id="semesterModalSubtitle" class="text-[11px] text-bluedark/50">Tahun Ajaran: -</p>
                </div>
            </div>
            <button type="button" onclick="closeSemesterModal()" class="w-7 h-7 rounded-lg hover:bg-bluelight text-bluedark/50 hover:text-bluedark flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form id="semesterForm" method="POST" action="{{ route('admin.academic.semesters.store') }}" class="space-y-3.5">
            @csrf
            <div id="semesterMethodField"></div>
            <input type="hidden" name="academic_year_id" id="semester_academic_year_id">

            <!-- Context Banner with Presets -->
            <div class="p-2.5 rounded-xl bg-blue-50/70 border border-bluelight flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-bluedark/60">Tahun:</span>
                    <strong id="semesterYearDisplay" class="text-blueprim font-bold font-heading">-</strong>
                </div>
                <div id="semesterPresetWrap" class="flex items-center gap-1.5">
                    <span class="text-[10px] text-bluedark/50 uppercase font-bold tracking-wider">Preset:</span>
                    <button type="button" onclick="applySemesterPreset(1)" class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 hover:bg-blue-200 text-[10px] font-semibold transition-colors">
                        Gasal (1)
                    </button>
                    <button type="button" onclick="applySemesterPreset(2)" class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200 text-[10px] font-semibold transition-colors">
                        Genap (2)
                    </button>
                </div>
            </div>

            <div>
                <label class="f-label text-xs" for="semester_name">
                    Nama Semester <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="semester_name" required placeholder="Contoh: Semester Gasal" class="f-input text-xs sm:text-sm">
            </div>

            <div>
                <label class="f-label text-xs" for="semester_number">
                    Nomor / Tipe Semester <span class="text-rose-500">*</span>
                </label>
                <select name="semester_number" id="semester_number" required class="f-select text-xs">
                    <option value="1">Semester 1 &mdash; Gasal / Ganjil</option>
                    <option value="2">Semester 2 &mdash; Genap</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="f-label text-xs" for="semester_start_date">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="semester_start_date" required class="f-input text-xs">
                </div>
                <div>
                    <label class="f-label text-xs" for="semester_end_date">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="semester_end_date" required class="f-input text-xs">
                </div>
            </div>

            <div class="p-2.5 rounded-xl bg-blue-50/60 border border-bluelight">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="semester_is_active" value="1" class="rounded text-blueprim focus:ring-blueprim w-4 h-4">
                    <span class="text-xs font-semibold text-bluedark">Jadikan semester aktif saat ini</span>
                </label>
                <p class="text-[10px] text-bluedark/60 ml-6 mt-0.5">
                    Otomatis mengaktifkan tahun ajaran induknya dan menonaktifkan semester lain.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeSemesterModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" id="semesterSubmitBtn" class="btn btn-primary btn-sm text-xs">Simpan Semester</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openYearModal() {
        document.getElementById('yearModalTitle').innerText = 'Tambah Tahun Ajaran Baru';
        document.getElementById('yearSubmitBtn').innerText = 'Simpan Tahun Ajaran';
        document.getElementById('yearForm').action = "{{ route('admin.academic.years.store') }}";
        document.getElementById('yearMethodField').innerHTML = '';
        document.getElementById('year_name').value = '';
        document.getElementById('year_start_date').value = '';
        document.getElementById('year_end_date').value = '';
        document.getElementById('year_is_active').checked = false;
        document.getElementById('yearModal').classList.remove('hidden');
        document.getElementById('year_name').focus();
    }

    function editYear(year) {
        document.getElementById('yearModalTitle').innerText = 'Edit Tahun Ajaran: ' + year.name;
        document.getElementById('yearSubmitBtn').innerText = 'Perbarui Tahun Ajaran';
        document.getElementById('yearForm').action = "/admin/academic/years/" + year.id;
        document.getElementById('yearMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('year_name').value = year.name;
        document.getElementById('year_start_date').value = year.start_date ? year.start_date.substring(0, 10) : '';
        document.getElementById('year_end_date').value = year.end_date ? year.end_date.substring(0, 10) : '';
        document.getElementById('year_is_active').checked = !!year.is_active;
        document.getElementById('yearModal').classList.remove('hidden');
        document.getElementById('year_name').focus();
    }

    function closeYearModal() {
        document.getElementById('yearModal').classList.add('hidden');
    }

    function openSemesterModal(yearId, yearName) {
        document.getElementById('semesterModalTitle').innerText = 'Tambah Semester Baru';
        document.getElementById('semesterModalSubtitle').innerText = 'Tahun Ajaran: ' + yearName;
        document.getElementById('semesterYearDisplay').innerText = yearName;
        document.getElementById('semesterSubmitBtn').innerText = 'Simpan Semester';
        document.getElementById('semesterForm').action = "{{ route('admin.academic.semesters.store') }}";
        document.getElementById('semesterMethodField').innerHTML = '';
        document.getElementById('semester_academic_year_id').value = yearId;
        document.getElementById('semesterPresetWrap').classList.remove('hidden');

        document.getElementById('semester_name').value = 'Semester Gasal';
        document.getElementById('semester_number').value = '1';
        document.getElementById('semester_start_date').value = '';
        document.getElementById('semester_end_date').value = '';
        document.getElementById('semester_is_active').checked = false;

        document.getElementById('semesterModal').classList.remove('hidden');
        document.getElementById('semester_name').focus();
    }

    function editSemester(semester, yearName) {
        document.getElementById('semesterModalTitle').innerText = 'Edit Semester: ' + semester.name;
        document.getElementById('semesterModalSubtitle').innerText = 'Tahun Ajaran: ' + (yearName || '-');
        document.getElementById('semesterYearDisplay').innerText = yearName || '-';
        document.getElementById('semesterSubmitBtn').innerText = 'Perbarui Semester';
        document.getElementById('semesterForm').action = "/admin/academic/semesters/" + semester.id;
        document.getElementById('semesterMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('semester_academic_year_id').value = semester.academic_year_id;
        document.getElementById('semesterPresetWrap').classList.add('hidden');

        document.getElementById('semester_name').value = semester.name;
        document.getElementById('semester_number').value = semester.semester_number;
        document.getElementById('semester_start_date').value = semester.start_date ? semester.start_date.substring(0, 10) : '';
        document.getElementById('semester_end_date').value = semester.end_date ? semester.end_date.substring(0, 10) : '';
        document.getElementById('semester_is_active').checked = !!semester.is_active;

        document.getElementById('semesterModal').classList.remove('hidden');
        document.getElementById('semester_name').focus();
    }

    function closeSemesterModal() {
        document.getElementById('semesterModal').classList.add('hidden');
    }

    function applySemesterPreset(type) {
        if (type === 1) {
            document.getElementById('semester_name').value = 'Semester Gasal';
            document.getElementById('semester_number').value = '1';
        } else if (type === 2) {
            document.getElementById('semester_name').value = 'Semester Genap';
            document.getElementById('semester_number').value = '2';
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeYearModal();
            closeSemesterModal();
        }
    });

    document.getElementById('yearModal').addEventListener('click', function(e) {
        if (e.target === this) closeYearModal();
    });
    document.getElementById('semesterModal').addEventListener('click', function(e) {
        if (e.target === this) closeSemesterModal();
    });
</script>
@endpush
@endsection
