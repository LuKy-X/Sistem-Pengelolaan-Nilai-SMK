@extends('layouts.admin')

@section('title', 'Manajemen Semester')

@section('content')
<div class="space-y-5 max-w-full overflow-hidden">

    <!-- Page Header & View Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-1">
                <span>Akademik</span>
                <span>/</span>
                <a href="{{ route('admin.academic.years.index') }}" class="hover:underline text-bluedark/70">Tahun &amp; Semester</a>
                <span>/</span>
                <span class="text-blueprim font-semibold">Tabel Seluruh Semester</span>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Seluruh Semester</h1>
            <p class="text-xs md:text-sm text-bluedark/60 mt-0.5">Daftar rekapitulasi seluruh periode semester akademik, status keaktifan, rentang tanggal, dan pengaturannya.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
            <!-- View Mode Switcher -->
            <div class="inline-flex rounded-xl p-1 bg-bluelight/70 border border-bluelight text-xs font-semibold">
                <a href="{{ route('admin.academic.years.index') }}" class="px-3 py-1.5 rounded-lg text-bluedark/70 hover:text-bluedark hover:bg-white/60 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Per Tahun Ajaran</span>
                </a>
                <span class="px-3 py-1.5 rounded-lg bg-blueprim text-white shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>Tabel Semester</span>
                </span>
            </div>

            @if($academicYears->isNotEmpty())
                <button type="button" onclick="openSemesterModal({{ $academicYears->first()->id }}, '{{ $academicYears->first()->name }}')" class="btn btn-primary btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Semester</span>
                </button>
            @endif
        </div>
    </div>

    @php
        $activeSemester = $semesters->firstWhere('is_active', true);
        $gasalCount = $semesters->where('semester_number', 1)->count();
        $genapCount = $semesters->where('semester_number', 2)->count();
    @endphp

    <!-- KPI Summary Highlight Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Semester -->
        <div class="panel p-3.5 sm:p-4 border border-bluelight shadow-xs flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blueprim flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] sm:text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider truncate">Total Semester</div>
                <div class="font-heading font-extrabold text-lg sm:text-xl text-bluedark leading-tight mt-0.5">{{ $semesters->count() }}</div>
                <div class="text-[10px] text-bluedark/50 truncate">Semua tahun ajaran</div>
            </div>
        </div>

        <!-- 2. Semester Aktif Saat Ini -->
        <div class="panel p-3.5 sm:p-4 border border-bluelight shadow-xs flex items-center gap-3 min-w-0 {{ $activeSemester ? 'bg-gradient-to-br from-emerald-50/50 to-white border-emerald-300' : '' }}">
            <div class="w-10 h-10 rounded-xl {{ $activeSemester ? 'bg-emerald-500 text-white shadow-xs' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] sm:text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider flex items-center gap-1 truncate">
                    <span>Semester Aktif</span>
                    @if($activeSemester)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    @endif
                </div>
                <div class="font-heading font-extrabold text-sm sm:text-base text-bluedark truncate mt-0.5" title="{{ $activeSemester?->name ?? 'Belum ada' }}">
                    {{ $activeSemester ? $activeSemester->name : 'Belum Ada' }}
                </div>
                <div class="text-[10px] text-bluedark/60 truncate">
                    {{ $activeSemester ? 'Tahun ' . ($activeSemester->academicYear?->name ?? '-') : 'Perlu diaktifkan' }}
                </div>
            </div>
        </div>

        <!-- 3. Semester Gasal -->
        <div class="panel p-3.5 sm:p-4 border border-bluelight shadow-xs flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 font-heading font-bold text-sm">
                1
            </div>
            <div class="min-w-0">
                <div class="text-[10px] sm:text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider truncate">Semester Gasal</div>
                <div class="font-heading font-extrabold text-lg sm:text-xl text-bluedark leading-tight mt-0.5">{{ $gasalCount }}</div>
                <div class="text-[10px] text-bluedark/50 truncate">Periode Ganjil</div>
            </div>
        </div>

        <!-- 4. Semester Genap -->
        <div class="panel p-3.5 sm:p-4 border border-bluelight shadow-xs flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center shrink-0 font-heading font-bold text-sm">
                2
            </div>
            <div class="min-w-0">
                <div class="text-[10px] sm:text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider truncate">Semester Genap</div>
                <div class="font-heading font-extrabold text-lg sm:text-xl text-bluedark leading-tight mt-0.5">{{ $genapCount }}</div>
                <div class="text-[10px] text-bluedark/50 truncate">Periode Genap</div>
            </div>
        </div>
    </div>

    <!-- Main Table Panel with Live Search & Filtering -->
    <div class="panel p-4 sm:p-5 border border-bluelight shadow-xs space-y-4 overflow-hidden">
        
        <!-- Filter Bar Toolbar (Redesigned & Elevated) -->
        <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/80 border border-bluelight/80 space-y-3">
            <!-- Row 1: Search Input & Quick Filter Pills -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[240px]">
                    <svg class="w-4 h-4 text-blueprim absolute left-3.5 top-1/2 -translate-y-1/2 shrink-0 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="semesterSearchInput" placeholder="Cari nama semester atau tahun ajaran..." class="f-input pl-10 pr-9 text-xs sm:text-sm py-2 bg-white border-bluelight focus:border-blueprim transition-colors">
                    <button type="button" id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-slate-200 text-bluedark/60 hover:bg-slate-300 flex items-center justify-center text-[10px] font-bold" title="Hapus Pencarian">&times;</button>
                </div>

                <!-- Quick Filter Pills -->
                <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white border border-bluelight/70 text-xs font-semibold overflow-x-auto max-w-full shrink-0 shadow-2xs">
                    <button type="button" onclick="setQuickFilter('all')" id="pill-all" class="quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-semibold bg-blueprim text-white shadow-2xs">
                        Semua
                    </button>
                    <button type="button" onclick="setQuickFilter('gasal')" id="pill-gasal" class="quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-medium text-bluedark/70 hover:text-bluedark hover:bg-slate-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Gasal (1)
                    </button>
                    <button type="button" onclick="setQuickFilter('genap')" id="pill-genap" class="quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-medium text-bluedark/70 hover:text-bluedark hover:bg-slate-100 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Genap (2)
                    </button>
                    <button type="button" onclick="setQuickFilter('aktif')" id="pill-aktif" class="quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-medium text-bluedark/70 hover:text-bluedark hover:bg-slate-100 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Hanya Aktif
                    </button>
                </div>
            </div>

            <!-- Row 2: Detailed Dropdowns with Icon Labels & Reset -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-2.5 border-t border-bluelight/60">
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Dropdown: Tahun Ajaran -->
                    <div class="inline-flex items-center gap-1.5 bg-white border border-bluelight rounded-xl px-2.5 py-1.5 focus-within:border-blueprim transition-all shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="text-[11px] font-bold text-bluedark/60 select-none">Tahun:</span>
                        <select id="yearFilter" class="bg-transparent border-none text-xs font-semibold text-bluedark focus:outline-none cursor-pointer pr-1">
                            <option value="">Semua Tahun</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->name }}">{{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown: Tipe Semester -->
                    <div class="inline-flex items-center gap-1.5 bg-white border border-bluelight rounded-xl px-2.5 py-1.5 focus-within:border-blueprim transition-all shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span class="text-[11px] font-bold text-bluedark/60 select-none">Tipe:</span>
                        <select id="typeFilter" class="bg-transparent border-none text-xs font-semibold text-bluedark focus:outline-none cursor-pointer pr-1">
                            <option value="">Semua Tipe</option>
                            <option value="1">Gasal (1)</option>
                            <option value="2">Genap (2)</option>
                        </select>
                    </div>

                    <!-- Dropdown: Status Keaktifan -->
                    <div class="inline-flex items-center gap-1.5 bg-white border border-bluelight rounded-xl px-2.5 py-1.5 focus-within:border-blueprim transition-all shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="text-[11px] font-bold text-bluedark/60 select-none">Status:</span>
                        <select id="statusFilter" class="bg-transparent border-none text-xs font-semibold text-bluedark focus:outline-none cursor-pointer pr-1">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif Saja</option>
                            <option value="non-aktif">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                    <!-- Active filter badge -->
                    <span id="activeFilterBadge" class="hidden text-[10px] font-semibold text-blueprim bg-blueprim/10 px-2 py-1 rounded-md">
                        Filter Aktif
                    </span>

                    <!-- Reset Button -->
                    <button type="button" id="resetBtn" onclick="resetFilters()" class="btn btn-outline btn-sm text-xs py-1.5 px-2.5 text-bluedark/60 hover:text-rose-600 hover:border-rose-300 transition-colors flex items-center gap-1.5" title="Reset Semua Filter">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Container with Controlled Scroll -->
        <div class="overflow-x-auto rounded-xl border border-bluelight/70 -mx-1 sm:mx-0">
            <table class="tbl w-full text-left min-w-[620px]" id="semestersTable">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="py-2.5 px-3.5 font-heading text-xs font-semibold text-bluedark">Tahun Ajaran</th>
                        <th class="py-2.5 px-3.5 font-heading text-xs font-semibold text-bluedark">Semester &amp; Tipe</th>
                        <th class="py-2.5 px-3.5 font-heading text-xs font-semibold text-bluedark">Rentang Periode</th>
                        <th class="py-2.5 px-3.5 font-heading text-xs font-semibold text-bluedark">Status</th>
                        <th class="py-2.5 px-3.5 font-heading text-xs font-semibold text-bluedark text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bluelight/60 text-xs sm:text-sm">
                    @forelse($semesters as $sem)
                        <tr class="semester-row transition-colors hover:bg-blue-50/30 {{ $sem->is_active ? 'bg-emerald-50/30' : '' }}"
                            data-name="{{ strtolower($sem->name) }}"
                            data-year="{{ $sem->academicYear?->name ?? '' }}"
                            data-type="{{ $sem->semester_number }}"
                            data-status="{{ $sem->is_active ? 'aktif' : 'non-aktif' }}">
                            
                            <!-- Kolom 1: Tahun Ajaran -->
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-7 h-7 rounded-lg {{ $sem->academicYear?->is_active ? 'bg-blueprim text-white' : 'bg-bluelight text-bluedark' }} flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-heading font-bold text-bluedark text-xs sm:text-sm truncate">
                                            Tahun {{ $sem->academicYear?->name ?? '-' }}
                                        </div>
                                        @if($sem->academicYear?->is_active)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">
                                                <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                <span>Aktif</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 2: Nama & Tipe Semester -->
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-md {{ $sem->semester_number == 1 ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }} flex items-center justify-center font-heading font-bold text-xs shrink-0">
                                        {{ $sem->semester_number }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-heading font-bold text-bluedark text-xs sm:text-sm truncate">
                                            {{ $sem->name }}
                                        </div>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold {{ $sem->semester_number == 1 ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $sem->semester_number == 1 ? 'Gasal (1)' : 'Genap (2)' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 3: Rentang Periode Tanggal -->
                            <td class="py-3 px-3.5">
                                <div class="space-y-0.5 min-w-0">
                                    <div class="font-medium text-bluedark flex items-center gap-1 text-xs">
                                        <svg class="w-3 h-3 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>{{ \Carbon\Carbon::parse($sem->start_date)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($sem->end_date)->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 4: Status Keaktifan -->
                            <td class="py-3 px-3.5">
                                @if($sem->is_active)
                                    <span class="badge badge-green text-[11px] font-semibold px-2 py-0.5 flex items-center gap-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="badge badge-gray text-[11px] font-medium px-2 py-0.5 w-max">
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 5: Aksi (Toggle, Edit, Hapus) -->
                            <td class="py-3 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-1 shrink-0">
                                    <!-- Toggle Status Button -->
                                    <form action="{{ route('admin.academic.semesters.toggle-active', $sem) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $sem->is_active ? 'btn-outline border-emerald-400 text-emerald-700 hover:bg-emerald-50' : 'btn-primary' }} text-[11px] py-1 px-2 flex items-center gap-1 font-semibold" title="{{ $sem->is_active ? 'Nonaktifkan Semester' : 'Aktifkan Semester Ini' }}">
                                            @if($sem->is_active)
                                                <svg class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Nonaktif</span>
                                            @else
                                                <svg class="w-2.5 h-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                                                <span>Aktifkan</span>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Edit Semester Button -->
                                    <button type="button" onclick="editSemester({{ json_encode($sem) }}, '{{ $sem->academicYear?->name }}')" class="btn btn-outline btn-sm text-[11px] py-1 px-2 flex items-center gap-1 font-medium hover:border-blueprim hover:text-blueprim" title="Edit Semester">
                                        <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Hapus Semester Button -->
                                    <form action="{{ route('admin.academic.semesters.destroy', $sem) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semester \'{{ $sem->name }}\' (Tahun {{ $sem->academicYear?->name }})? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger text-[11px] py-1 px-2 flex items-center gap-1 font-medium transition-colors" title="Hapus Semester">
                                            <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-xs text-bluedark/40">
                                <span class="font-semibold text-bluedark block">Belum Ada Data Semester</span>
                                <span class="text-bluedark/50">Silakan tambahkan semester baru untuk memulai kalender akademik sekolah.</span>
                            </td>
                        </tr>
                    @endforelse
                    
                    <!-- Row saat pencarian tidak ditemukan -->
                    <tr id="noResultsRow" class="hidden">
                        <td colspan="5" class="text-center py-8 text-xs text-bluedark/60">
                            <span class="font-bold text-bluedark block text-sm">Tidak Ditemukan Hasil Pencarian</span>
                            <span class="text-bluedark/50 block mt-0.5">Tidak ada semester yang cocok dengan kata kunci atau filter yang Anda pilih.</span>
                            <button type="button" onclick="resetFilters()" class="btn btn-outline btn-sm text-xs mt-2.5">Reset Filter</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Info -->
        <div class="flex items-center justify-between text-xs text-bluedark/60 pt-1 flex-wrap gap-2">
            <div>
                Menampilkan <strong id="visibleCount">{{ $semesters->count() }}</strong> dari <strong>{{ $semesters->count() }}</strong> total semester.
            </div>
            <div class="text-[11px] text-bluedark/50">
                Data disinkronkan otomatis dengan kalender akademik.
            </div>
        </div>

    </div>

</div>

<!-- Modal Tambah / Edit Semester -->
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

            <!-- Context Banner with Year Selection -->
            <div>
                <label class="f-label text-xs" for="semester_academic_year_id">
                    Pilih Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <select name="academic_year_id" id="semester_academic_year_id" required class="f-select text-xs font-medium text-bluedark">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}">Tahun Ajaran {{ $y->name }} {{ $y->is_active ? '(Sedang Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Preset Helper (Saat Tambah Baru) -->
            <div id="semesterPresetWrap" class="p-2.5 rounded-xl bg-blue-50/70 border border-bluelight flex items-center justify-between flex-wrap gap-2">
                <span class="text-xs text-bluedark/70 font-medium">Template Cepat:</span>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="applySemesterPreset(1)" class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 hover:bg-blue-200 text-[10px] font-semibold transition-colors">
                        Gasal (Sem 1)
                    </button>
                    <button type="button" onclick="applySemesterPreset(2)" class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200 text-[10px] font-semibold transition-colors">
                        Genap (Sem 2)
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
    function openSemesterModal(yearId, yearName) {
        document.getElementById('semesterModalTitle').innerText = 'Tambah Semester Baru';
        document.getElementById('semesterModalSubtitle').innerText = 'Tahun Ajaran: ' + (yearName || '-');
        document.getElementById('semesterSubmitBtn').innerText = 'Simpan Semester';
        document.getElementById('semesterForm').action = "{{ route('admin.academic.semesters.store') }}";
        document.getElementById('semesterMethodField').innerHTML = '';
        document.getElementById('semesterPresetWrap').classList.remove('hidden');

        if (yearId) {
            document.getElementById('semester_academic_year_id').value = yearId;
        }

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
        document.getElementById('semesterSubmitBtn').innerText = 'Perbarui Semester';
        document.getElementById('semesterForm').action = "/admin/academic/semesters/" + semester.id;
        document.getElementById('semesterMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('semesterPresetWrap').classList.add('hidden');

        document.getElementById('semester_academic_year_id').value = semester.academic_year_id;
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

    // Live search & filter
    const searchInput = document.getElementById('semesterSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const yearFilter = document.getElementById('yearFilter');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('.semester-row');
    const noResultsRow = document.getElementById('noResultsRow');
    const visibleCount = document.getElementById('visibleCount');
    const activeFilterBadge = document.getElementById('activeFilterBadge');

    function clearSearch() {
        if (searchInput) {
            searchInput.value = '';
            filterTable();
            searchInput.focus();
        }
    }

    function setQuickFilter(mode) {
        if (mode === 'all') {
            typeFilter.value = '';
            statusFilter.value = '';
        } else if (mode === 'gasal') {
            typeFilter.value = '1';
        } else if (mode === 'genap') {
            typeFilter.value = '2';
        } else if (mode === 'aktif') {
            statusFilter.value = 'aktif';
        }
        filterTable();
    }

    function updatePillsState() {
        const t = typeFilter.value;
        const s = statusFilter.value;
        const pills = ['all', 'gasal', 'genap', 'aktif'];
        
        pills.forEach(p => {
            const el = document.getElementById('pill-' + p);
            if (!el) return;
            el.className = 'quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-medium text-bluedark/70 hover:text-bluedark hover:bg-slate-100 flex items-center gap-1.5';
        });

        let activePill = null;
        if (!t && !s) {
            activePill = 'all';
        } else if (t === '1' && !s) {
            activePill = 'gasal';
        } else if (t === '2' && !s) {
            activePill = 'genap';
        } else if (!t && s === 'aktif') {
            activePill = 'aktif';
        }

        if (activePill) {
            const activeEl = document.getElementById('pill-' + activePill);
            if (activeEl) {
                activeEl.className = 'quick-pill px-3 py-1.5 rounded-lg transition-all text-xs font-semibold bg-blueprim text-white shadow-2xs flex items-center gap-1.5';
            }
        }

        // Active filter counter
        const activeCount = (searchInput.value.trim() ? 1 : 0) + (yearFilter.value ? 1 : 0) + (typeFilter.value ? 1 : 0) + (statusFilter.value ? 1 : 0);
        if (activeFilterBadge) {
            if (activeCount > 0) {
                activeFilterBadge.innerText = activeCount + ' Filter Aktif';
                activeFilterBadge.classList.remove('hidden');
            } else {
                activeFilterBadge.classList.add('hidden');
            }
        }

        if (clearSearchBtn) {
            if (searchInput.value.trim().length > 0) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }
        }
    }

    function filterTable() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedYear = yearFilter.value.trim();
        const selectedType = typeFilter.value.trim();
        const selectedStatus = statusFilter.value.trim();

        let count = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const year = row.getAttribute('data-year');
            const type = row.getAttribute('data-type');
            const status = row.getAttribute('data-status');

            const matchesQuery = !query || name.includes(query) || year.toLowerCase().includes(query);
            const matchesYear = !selectedYear || year === selectedYear;
            const matchesType = !selectedType || type === selectedType;
            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesQuery && matchesYear && matchesType && matchesStatus) {
                row.classList.remove('hidden');
                count++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (visibleCount) visibleCount.innerText = count;

        if (noResultsRow) {
            if (count === 0 && rows.length > 0) {
                noResultsRow.classList.remove('hidden');
            } else {
                noResultsRow.classList.add('hidden');
            }
        }

        updatePillsState();
    }

    function resetFilters() {
        searchInput.value = '';
        yearFilter.value = '';
        typeFilter.value = '';
        statusFilter.value = '';
        filterTable();
    }

    searchInput.addEventListener('input', filterTable);
    yearFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSemesterModal();
    });

    document.getElementById('semesterModal').addEventListener('click', function(e) {
        if (e.target === this) closeSemesterModal();
    });
</script>
@endpush
@endsection
