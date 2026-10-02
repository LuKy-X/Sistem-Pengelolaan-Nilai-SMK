@extends('layouts.admin')

@section('title', 'Kelola ' . $period->title . ' - PPDB')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-bluedark/50">
                <a href="{{ route('admin.cms.ppdb') }}" class="nav-leave-check hover:text-blueprim flex items-center gap-1 font-medium transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    <span>Daftar Gelombang PPDB</span>
                </a>
                <span>&bull;</span>
                <span class="text-bluedark/80 font-semibold truncate">{{ $period->title }}</span>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">
                Konfigurasi Gelombang PPDB
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('public.ppdb.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Lihat Tampilan Publik</span>
            </a>

            <a href="{{ route('admin.cms.ppdb') }}" class="nav-leave-check btn btn-outline btn-sm flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert Success / Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3 text-sm shadow-xs animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600 shrink-0">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-base leading-none">&times;</button>
        </div>
    @endif

    <!-- Alert Errors -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-xs">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-red-600"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Hero Banner of Selected Wave (Refined High-Contrast Theme) -->
    <div class="panel p-6 sm:p-7 bg-white border border-bluelight rounded-3xl shadow-sm relative overflow-hidden">
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    @if($period->status === 'OPEN')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Sedang Dibuka (Open) &bull; Publik
                        </span>
                    @elseif($period->status === 'CLOSED')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-700 border border-slate-300">
                            Ditutup (Closed)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-300">
                            Draf (Hanya Admin)
                        </span>
                    @endif

                    @if($period->academicYear)
                        <span class="text-xs font-semibold px-3.5 py-1.5 rounded-full bg-bluelight text-bluedark border border-blueprim/25">
                            Tahun Ajaran {{ $period->academicYear->name }}
                        </span>
                    @endif
                </div>

                <h2 class="font-heading font-bold text-xl sm:text-2xl text-bluedark">{{ $period->title }}</h2>

                @if(filled($period->description))
                    <p class="text-bluedark/70 text-xs sm:text-sm max-w-3xl leading-relaxed">{{ $period->description }}</p>
                @endif
            </div>

            <div class="shrink-0 bg-[#F7FBFF] p-4.5 rounded-2xl border border-bluelight text-xs space-y-1.5 sm:text-right shadow-2xs">
                <p class="text-bluedark/50 font-medium">Rentang Waktu Registrasi:</p>
                <p class="font-heading font-bold text-sm text-bluedark flex items-center sm:justify-end gap-1.5">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>{{ \Carbon\Carbon::parse($period->registration_start)->translatedFormat('d F Y') }}</span>
                    <span class="text-bluedark/40">&ndash;</span>
                    <span>{{ \Carbon\Carbon::parse($period->registration_end)->translatedFormat('d F Y') }}</span>
                </p>
                <div class="pt-1 flex items-center sm:justify-end gap-1.5 text-[11px] {{ $period->status !== 'DRAFT' ? 'text-emerald-700 font-semibold' : 'text-amber-700 font-medium' }}">
                    @if($period->status !== 'DRAFT')
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Informasi ini aktif tampil di /ppdb</span>
                    @else
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span>Gelombang masih berupa draf</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="panel p-2 flex items-center gap-2 overflow-x-auto border border-bluelight scrollbar-none" id="ppdbTabsNav">
        <button type="button" onclick="handleTabClick('period')" id="tab-btn-period"
            class="tab-trigger px-4 py-2.5 rounded-xl font-heading font-semibold text-xs transition-all flex items-center gap-2 shrink-0 {{ ($tab ?? 'period') === 'period' ? 'bg-bluedark text-white shadow-xs' : 'text-bluedark/70 hover:bg-bluelight/40' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Informasi Gelombang</span>
        </button>

        <button type="button" onclick="handleTabClick('schedules')" id="tab-btn-schedules"
            class="tab-trigger px-4 py-2.5 rounded-xl font-heading font-semibold text-xs transition-all flex items-center gap-2 shrink-0 {{ ($tab ?? 'period') === 'schedules' ? 'bg-bluedark text-white shadow-xs' : 'text-bluedark/70 hover:bg-bluelight/40' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span>Jadwal Pendaftaran</span>
            <span class="badge-count px-1.5 py-0.5 rounded-md text-[10px] {{ ($tab ?? 'period') === 'schedules' ? 'bg-white/20 text-white' : 'bg-bluelight text-bluedark' }}">{{ $period->scheduleItems->count() }}</span>
        </button>

        <button type="button" onclick="handleTabClick('paths')" id="tab-btn-paths"
            class="tab-trigger px-4 py-2.5 rounded-xl font-heading font-semibold text-xs transition-all flex items-center gap-2 shrink-0 {{ ($tab ?? 'period') === 'paths' ? 'bg-bluedark text-white shadow-xs' : 'text-bluedark/70 hover:bg-bluelight/40' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M13 6h3a2 2 0 0 1 2 2v7"></path><line x1="6" y1="9" x2="6" y2="21"></line></svg>
            <span>Jalur Pendaftaran</span>
            <span class="badge-count px-1.5 py-0.5 rounded-md text-[10px] {{ ($tab ?? 'period') === 'paths' ? 'bg-white/20 text-white' : 'bg-bluelight text-bluedark' }}">{{ $period->paths->count() }}</span>
        </button>

        <button type="button" onclick="handleTabClick('requirements')" id="tab-btn-requirements"
            class="tab-trigger px-4 py-2.5 rounded-xl font-heading font-semibold text-xs transition-all flex items-center gap-2 shrink-0 {{ ($tab ?? 'period') === 'requirements' ? 'bg-bluedark text-white shadow-xs' : 'text-bluedark/70 hover:bg-bluelight/40' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Persyaratan</span>
            <span class="badge-count px-1.5 py-0.5 rounded-md text-[10px] {{ ($tab ?? 'period') === 'requirements' ? 'bg-white/20 text-white' : 'bg-bluelight text-bluedark' }}">{{ $period->requirements->count() }}</span>
        </button>

        <button type="button" onclick="handleTabClick('fees')" id="tab-btn-fees"
            class="tab-trigger px-4 py-2.5 rounded-xl font-heading font-semibold text-xs transition-all flex items-center gap-2 shrink-0 {{ ($tab ?? 'period') === 'fees' ? 'bg-bluedark text-white shadow-xs' : 'text-bluedark/70 hover:bg-bluelight/40' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            <span>Biaya Pendaftaran</span>
            <span class="badge-count px-1.5 py-0.5 rounded-md text-[10px] {{ ($tab ?? 'period') === 'fees' ? 'bg-white/20 text-white' : 'bg-bluelight text-bluedark' }}">{{ $period->feeItems->count() }}</span>
        </button>
    </div>

    <!-- TAB 1: INFORMASI GELOMBANG -->
    <div id="tab-content-period" class="tab-pane {{ ($tab ?? 'period') === 'period' ? '' : 'hidden' }}">
        <div class="panel p-0 overflow-hidden">
            <div class="p-5 border-b border-bluelight bg-white flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-base">Informasi Utama Gelombang</h2>
                    <p class="text-xs text-bluedark/50 mt-0.5">Atur identitas gelombang, tahun ajaran, batas tanggal, dan visibilitas publik</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $period->status === 'OPEN' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                    Status: {{ $period->status }}
                </span>
            </div>

            <form action="{{ route('admin.cms.ppdb.periods.update', $period) }}" method="POST" autocomplete="off" class="track-form-changes p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <select name="academic_year_id" required class="f-select">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ old('academic_year_id', $period->academic_year_id) == $y->id ? 'selected' : '' }}>
                                    {{ $y->name }} {{ $y->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Nama Gelombang <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $period->title) }}" required class="f-input" placeholder="Contoh: PPDB Gelombang 1 - Reguler">
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="f-label">Mulai Pendaftaran <span class="text-red-500">*</span></label>
                        <input type="date" name="registration_start" value="{{ old('registration_start', $period->registration_start?->format('Y-m-d')) }}" required class="f-input">
                    </div>

                    <div>
                        <label class="f-label">Selesai Pendaftaran <span class="text-red-500">*</span></label>
                        <input type="date" name="registration_end" value="{{ old('registration_end', $period->registration_end?->format('Y-m-d')) }}" required class="f-input">
                    </div>

                    <div>
                        <label class="f-label">Status Gelombang <span class="text-red-500">*</span></label>
                        <select name="status" required class="f-select">
                            <option value="OPEN" {{ old('status', $period->status) === 'OPEN' ? 'selected' : '' }}>Dibuka (Open) &mdash; Tampil di Web Publik</option>
                            <option value="DRAFT" {{ old('status', $period->status) === 'DRAFT' ? 'selected' : '' }}>Draf (Draft) &mdash; Hanya di Admin</option>
                            <option value="CLOSED" {{ old('status', $period->status) === 'CLOSED' ? 'selected' : '' }}>Ditutup (Closed)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="f-label">Deskripsi / Pengantar Gelombang (Opsional)</label>
                    <textarea name="description" rows="3" class="f-input" placeholder="Tuliskan keterangan tambahan atau sambutan PPDB untuk gelombang ini...">{{ old('description', $period->description) }}</textarea>
                </div>

                <div class="pt-4 border-t border-bluelight flex items-center justify-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Simpan Informasi Gelombang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: JADWAL PENDAFTARAN (DENGAN DRAG & DROP + SWAP KEATAS/BAWAH + HIGHLIGHT PERUBAHAN) -->
    <div id="tab-content-schedules" class="tab-pane {{ ($tab ?? 'period') === 'schedules' ? '' : 'hidden' }}">
        <div class="panel p-0 overflow-hidden">
            <div class="p-5 border-b border-bluelight bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-base flex items-center gap-2">
                        <span>Jadwal &amp; Alur Tahapan Pendaftaran</span>
                        <span class="badge badge-blue text-[11px]" id="schedule-count-badge">{{ $period->scheduleItems->count() }} Tahap</span>
                    </h2>
                    <p class="text-xs text-bluedark/50 mt-0.5">Tambah jadwal di bawahnya. Anda dapat <strong>menahan klik kiri lalu menggeser baris ke atas/bawah (Drag &amp; Drop)</strong> atau gunakan tombol <strong>Swap</strong>. Baris yang dipindah akan diberi tanda sorotan khusus.</p>
                </div>

                <button type="button" onclick="addScheduleRow()" class="btn btn-primary btn-sm flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Jadwal di Bawah</span>
                </button>
            </div>

            <form action="{{ route('admin.cms.ppdb.periods.schedules.update', $period) }}" method="POST" id="schedulesForm" autocomplete="off" class="track-form-changes p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div id="schedules-container" class="space-y-3.5">
                    @forelse($period->scheduleItems as $index => $item)
                        <div class="schedule-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200" data-index="{{ $index }}" data-initial-index="{{ $index }}" data-initial-id="{{ $item->id }}">
                            <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $item->id }}">

                            <div class="flex flex-col lg:flex-row items-start gap-4">
                                <!-- Reorder Controls: Drag Handle, Step Number & Swap Buttons -->
                                <div class="flex items-center lg:flex-col gap-1.5 shrink-0">
                                    <!-- Drag Handle -->
                                    <div class="drag-handle cursor-grab active:cursor-grabbing p-1.5 rounded-lg border border-bluelight hover:bg-bluelight/70 text-bluedark/40 hover:text-bluedark transition-colors" title="Tahan klik kiri & geser atas/bawah untuk memindahkan urutan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <circle cx="9" cy="6" r="1.5" fill="currentColor"></circle>
                                            <circle cx="15" cy="6" r="1.5" fill="currentColor"></circle>
                                            <circle cx="9" cy="12" r="1.5" fill="currentColor"></circle>
                                            <circle cx="15" cy="12" r="1.5" fill="currentColor"></circle>
                                            <circle cx="9" cy="18" r="1.5" fill="currentColor"></circle>
                                            <circle cx="15" cy="18" r="1.5" fill="currentColor"></circle>
                                        </svg>
                                    </div>

                                    <div class="w-9 h-9 rounded-xl bg-bluedark text-white font-heading font-bold text-xs grid place-items-center step-display shadow-2xs">
                                        {{ $index + 1 }}
                                    </div>

                                    <div class="flex lg:flex-col gap-1">
                                        <button type="button" onclick="swapScheduleUp(this)"
                                            class="btn-swap-up p-1.5 rounded-lg border border-bluelight hover:bg-bluelight text-bluedark/70 hover:text-bluedark disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                            title="Pindahkan Tahap Ini Ke Atas (Swap)">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                        </button>
                                        <button type="button" onclick="swapScheduleDown(this)"
                                            class="btn-swap-down p-1.5 rounded-lg border border-bluelight hover:bg-bluelight text-bluedark/70 hover:text-bluedark disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                            title="Pindahkan Tahap Ini Ke Bawah (Swap)">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Inputs -->
                                <div class="flex-1 w-full space-y-3">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-[11px] font-semibold text-bluedark/60">Pengaturan Jadwal Tahap #<span class="step-label">{{ $index + 1 }}</span></span>
                                        <div class="reorder-badge-placeholder"></div>
                                    </div>

                                    <div class="grid sm:grid-cols-12 gap-3">
                                        <div class="sm:col-span-6">
                                            <label class="f-label text-[11px]">Nama Tahapan Jadwal <span class="text-red-500">*</span></label>
                                            <input type="text" name="schedules[{{ $index }}][title]" value="{{ $item->title }}" required placeholder="Contoh: Pendaftaran Online & Verifikasi Berkas" class="f-input text-xs schedule-title">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="f-label text-[11px]">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" name="schedules[{{ $index }}][start_date]" value="{{ $item->start_date?->format('Y-m-d') }}" required class="f-input text-xs">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="f-label text-[11px]">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" name="schedules[{{ $index }}][end_date]" value="{{ $item->end_date?->format('Y-m-d') }}" required class="f-input text-xs">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="f-label text-[11px]">Deskripsi / Petunjuk Pelaksanaan (Opsional)</label>
                                        <input type="text" name="schedules[{{ $index }}][description]" value="{{ $item->description }}" placeholder="Contoh: Peserta didik mengunggah berkas pada portal sekolah dan datang untuk verifikasi fisik" class="f-input text-xs">
                                    </div>
                                </div>

                                <!-- Delete Action -->
                                <button type="button" onclick="removeScheduleRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end lg:self-center" title="Hapus Tahapan Ini">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div id="schedule-empty-state" class="p-8 text-center border-2 border-dashed border-bluelight rounded-2xl space-y-3">
                            <div class="w-12 h-12 rounded-full bg-bluelight/70 text-blueprim grid place-items-center mx-auto">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
                            </div>
                            <p class="text-xs text-bluedark/60">Belum ada tahapan jadwal pendaftaran untuk gelombang ini.</p>
                            <button type="button" onclick="addScheduleRow()" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah Jadwal Pertama</span>
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Bottom Add Button & Save -->
                <div class="pt-4 border-t border-bluelight flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <button type="button" onclick="addScheduleRow()" class="btn btn-outline btn-sm flex items-center gap-2 self-start">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Baris Jadwal di Bawah</span>
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Simpan Seluruh Jadwal &amp; Urutan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: JALUR PENDAFTARAN (REPEATER TAMBAH DI BAWAH) -->
    <div id="tab-content-paths" class="tab-pane {{ ($tab ?? 'period') === 'paths' ? '' : 'hidden' }}">
        <div class="panel p-0 overflow-hidden">
            <div class="p-5 border-b border-bluelight bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-base flex items-center gap-2">
                        <span>Jalur Seleksi Pendaftaran</span>
                        <span class="badge badge-blue text-[11px]" id="path-count-badge">{{ $period->paths->count() }} Jalur</span>
                    </h2>
                    <p class="text-xs text-bluedark/50 mt-0.5">Kelola jalur penerimaan (cth: Jalur Prestasi, Zonasi, Afirmasi, Reguler). Tambah langsung di bawahnya.</p>
                </div>

                <button type="button" onclick="addPathRow()" class="btn btn-primary btn-sm flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Jalur di Bawah</span>
                </button>
            </div>

            <form action="{{ route('admin.cms.ppdb.periods.paths.update', $period) }}" method="POST" id="pathsForm" autocomplete="off" class="track-form-changes p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div id="paths-container" class="space-y-3.5">
                    @forelse($period->paths as $index => $path)
                        <div class="path-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200" data-index="{{ $index }}">
                            <input type="hidden" name="paths[{{ $index }}][id]" value="{{ $path->id }}">

                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-heading font-bold text-xs grid place-items-center shrink-0 path-number">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 w-full space-y-3">
                                    <div class="grid sm:grid-cols-12 gap-3">
                                        <div class="sm:col-span-6">
                                            <label class="f-label text-[11px]">Nama Jalur Seleksi <span class="text-red-500">*</span></label>
                                            <input type="text" name="paths[{{ $index }}][name]" value="{{ $path->name }}" required placeholder="Contoh: Jalur Prestasi Akademik & Kejuaraan" class="f-input text-xs path-name">
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="f-label text-[11px]">Kuota Siswa (Opsional)</label>
                                            <input type="number" min="0" name="paths[{{ $index }}][quota]" value="{{ $path->quota }}" placeholder="Cth: 50" class="f-input text-xs">
                                        </div>

                                        <div class="sm:col-span-3 flex items-center pt-6">
                                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-bluedark">
                                                <input type="checkbox" name="paths[{{ $index }}][is_active]" value="1" {{ $path->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-blueprim border-bluelight">
                                                <span>Aktifkan Jalur Ini</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="f-label text-[11px]">Keterangan / Kriteria Jalur</label>
                                        <input type="text" name="paths[{{ $index }}][description]" value="{{ $path->description }}" placeholder="Contoh: Diperuntukkan bagi calon peserta didik dengan sertifikat juara tingkat kab/kota" class="f-input text-xs">
                                    </div>
                                </div>

                                <button type="button" onclick="removePathRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Jalur Ini">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div id="path-empty-state" class="p-8 text-center border-2 border-dashed border-bluelight rounded-2xl space-y-3">
                            <div class="w-12 h-12 rounded-full bg-bluelight/70 text-indigo-600 grid place-items-center mx-auto">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><line x1="6" y1="9" x2="6" y2="21"></line></svg>
                            </div>
                            <p class="text-xs text-bluedark/60">Belum ada jalur seleksi untuk gelombang ini.</p>
                            <button type="button" onclick="addPathRow()" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah Jalur Pertama</span>
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 border-t border-bluelight flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <button type="button" onclick="addPathRow()" class="btn btn-outline btn-sm flex items-center gap-2 self-start">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Baris Jalur di Bawah</span>
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Simpan Seluruh Jalur Seleksi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 4: PERSYARATAN (REPEATER TAMBAH DI BAWAH) -->
    <div id="tab-content-requirements" class="tab-pane {{ ($tab ?? 'period') === 'requirements' ? '' : 'hidden' }}">
        <div class="panel p-0 overflow-hidden">
            <div class="p-5 border-b border-bluelight bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-base flex items-center gap-2">
                        <span>Persyaratan Berkas &amp; Dokumen</span>
                        <span class="badge badge-blue text-[11px]" id="requirement-count-badge">{{ $period->requirements->count() }} Syarat</span>
                    </h2>
                    <p class="text-xs text-bluedark/50 mt-0.5">Kelola berkas yang wajib disiapkan oleh calon siswa baru. Anda bisa menambah banyak berkas di bawahnya.</p>
                </div>

                <button type="button" onclick="addRequirementRow()" class="btn btn-primary btn-sm flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Syarat di Bawah</span>
                </button>
            </div>

            <form action="{{ route('admin.cms.ppdb.periods.requirements.update', $period) }}" method="POST" id="requirementsForm" autocomplete="off" class="track-form-changes p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div id="requirements-container" class="space-y-3.5">
                    @forelse($period->requirements as $index => $req)
                        <div class="requirement-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200" data-index="{{ $index }}">
                            <input type="hidden" name="requirements[{{ $index }}][id]" value="{{ $req->id }}">

                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-heading font-bold text-xs grid place-items-center shrink-0 requirement-number">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 w-full space-y-3">
                                    <div>
                                        <label class="f-label text-[11px]">Nama Persyaratan / Berkas <span class="text-red-500">*</span></label>
                                        <input type="text" name="requirements[{{ $index }}][title]" value="{{ $req->title }}" required placeholder="Contoh: Fotokopi Ijazah / Surat Keterangan Lulus SMP" class="f-input text-xs req-title">
                                    </div>

                                    <div>
                                        <label class="f-label text-[11px]">Keterangan Detail (Opsional)</label>
                                        <input type="text" name="requirements[{{ $index }}][description]" value="{{ $req->description }}" placeholder="Contoh: Dilegalisir kepala sekolah sebanyak 2 lembar cap basah" class="f-input text-xs">
                                    </div>
                                </div>

                                <button type="button" onclick="removeRequirementRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Persyaratan Ini">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div id="requirement-empty-state" class="p-8 text-center border-2 border-dashed border-bluelight rounded-2xl space-y-3">
                            <div class="w-12 h-12 rounded-full bg-bluelight/70 text-emerald-600 grid place-items-center mx-auto">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <p class="text-xs text-bluedark/60">Belum ada daftar persyaratan untuk gelombang ini.</p>
                            <button type="button" onclick="addRequirementRow()" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah Syarat Pertama</span>
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 border-t border-bluelight flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <button type="button" onclick="addRequirementRow()" class="btn btn-outline btn-sm flex items-center gap-2 self-start">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Baris Syarat di Bawah</span>
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Simpan Seluruh Persyaratan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 5: BIAYA PENDAFTARAN (REPEATER TAMBAH DI BAWAH) -->
    <div id="tab-content-fees" class="tab-pane {{ ($tab ?? 'period') === 'fees' ? '' : 'hidden' }}">
        <div class="panel p-0 overflow-hidden">
            <div class="p-5 border-b border-bluelight bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-base flex items-center gap-2">
                        <span>Rincian Biaya Pendaftaran</span>
                        <span class="badge badge-blue text-[11px]" id="fee-count-badge">{{ $period->feeItems->count() }} Komponen</span>
                    </h2>
                    <p class="text-xs text-bluedark/50 mt-0.5">Kelola komponen biaya atau centang opsi <strong>Gratis</strong> jika tidak dipungut biaya. Tambah di bawahnya.</p>
                </div>

                <button type="button" onclick="addFeeRow()" class="btn btn-primary btn-sm flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Biaya di Bawah</span>
                </button>
            </div>

            <form action="{{ route('admin.cms.ppdb.periods.fees.update', $period) }}" method="POST" id="feesForm" autocomplete="off" class="track-form-changes p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div id="fees-container" class="space-y-3.5">
                    @forelse($period->feeItems as $index => $fee)
                        <div class="fee-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200" data-index="{{ $index }}">
                            <input type="hidden" name="fees[{{ $index }}][id]" value="{{ $fee->id }}">

                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 font-heading font-bold text-xs grid place-items-center shrink-0 fee-number">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 w-full space-y-3">
                                    <div class="grid sm:grid-cols-12 gap-3">
                                        <div class="sm:col-span-6">
                                            <label class="f-label text-[11px]">Nama Komponen Biaya <span class="text-red-500">*</span></label>
                                            <input type="text" name="fees[{{ $index }}][name]" value="{{ $fee->name }}" required placeholder="Contoh: Biaya Seragam &amp; Atribut" class="f-input text-xs fee-name">
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="f-label text-[11px]">Nominal (Rp)</label>
                                            <input type="number" min="0" step="1000" name="fees[{{ $index }}][amount]" value="{{ (int) $fee->amount }}" placeholder="Cth: 500000" class="f-input text-xs fee-amount" {{ $fee->is_free ? 'disabled' : '' }}>
                                        </div>

                                        <div class="sm:col-span-3 flex items-center pt-6">
                                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-bluedark">
                                                <input type="checkbox" name="fees[{{ $index }}][is_free]" value="1" onchange="toggleFeeFree(this)" {{ $fee->is_free ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 border-bluelight fee-free-checkbox">
                                                <span class="text-emerald-700">Gratis (Bebas Biaya)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="f-label text-[11px]">Keterangan Pembayaran (Opsional)</label>
                                        <input type="text" name="fees[{{ $index }}][description]" value="{{ $fee->description }}" placeholder="Keterangan rincian biaya..." class="f-input text-xs">
                                    </div>
                                </div>

                                <button type="button" onclick="removeFeeRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Biaya Ini">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div id="fee-empty-state" class="p-8 text-center border-2 border-dashed border-bluelight rounded-2xl space-y-3">
                            <div class="w-12 h-12 rounded-full bg-bluelight/70 text-purple-600 grid place-items-center mx-auto">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            </div>
                            <p class="text-xs text-bluedark/60">Belum ada rincian biaya pendaftaran untuk gelombang ini.</p>
                            <button type="button" onclick="addFeeRow()" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah Biaya Pertama</span>
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 border-t border-bluelight flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <button type="button" onclick="addFeeRow()" class="btn btn-outline btn-sm flex items-center gap-2 self-start">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Baris Biaya di Bawah</span>
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Simpan Seluruh Rincian Biaya</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Modal Konfirmasi Meninggalkan Halaman Saat Ada Perubahan Belum Disimpan -->
<div id="unsavedChangesModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150 border border-bluelight space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 grid place-items-center">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>

        <div>
            <h3 class="font-heading font-bold text-lg text-bluedark">Konfirmasi Meninggalkan Halaman</h3>
            <p class="text-xs sm:text-sm text-bluedark/70 mt-1.5 leading-relaxed">
                Terdapat perubahan data yang belum disimpan pada halaman ini. Jika Anda berpindah ke menu atau halaman lain, seluruh perubahan tersebut tidak akan disimpan.
            </p>
            <p class="text-xs sm:text-sm text-bluedark/85 font-semibold mt-2">
                Apakah Anda yakin ingin membatalkan perubahan dan meninggalkan halaman ini?
            </p>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-bluelight">
            <button type="button" onclick="closeUnsavedChangesModal()" class="btn btn-outline btn-sm font-semibold">
                Tetap di Halaman Ini
            </button>
            <button type="button" onclick="confirmLeavePage()" class="btn btn-sm font-semibold bg-red-600 hover:bg-red-700 text-white">
                Ya, Buang Perubahan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    /* =========================================================================
       GLOBAL UNSAVED CHANGES TRACKER & DISCARD PROTECTION
       ========================================================================= */
    let hasUnsavedChanges = false;
    let pendingNavigationAction = null;

    function markUnsavedChanges() {
        hasUnsavedChanges = true;
    }

    function resetUnsavedChanges() {
        hasUnsavedChanges = false;
    }

    // Attach listeners to all inputs and textareas
    document.querySelectorAll('.track-form-changes').forEach(form => {
        form.addEventListener('input', () => markUnsavedChanges());
        form.addEventListener('change', () => markUnsavedChanges());
        form.addEventListener('submit', () => {
            // When submitting a form legitimately, do not block
            resetUnsavedChanges();
        });
    });

    // Native browser beforeunload prompt
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Prevent browser bfcache from retaining dirty state when navigating back
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // Intercept clicks on links that leave the page
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        // Skip anchors or modals
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank') {
            return;
        }

        if (hasUnsavedChanges) {
            e.preventDefault();
            pendingNavigationAction = () => {
                document.querySelectorAll('form').forEach(f => {
                    try { f.reset(); } catch(err) {}
                });
                resetUnsavedChanges();
                window.location.href = href;
            };
            openUnsavedChangesModal();
        }
    });

    function openUnsavedChangesModal() {
        document.getElementById('unsavedChangesModal').classList.remove('hidden');
    }

    function closeUnsavedChangesModal() {
        document.getElementById('unsavedChangesModal').classList.add('hidden');
        pendingNavigationAction = null;
    }

    function confirmLeavePage() {
        // Discard all changes in form elements
        document.querySelectorAll('form').forEach(f => {
            try { f.reset(); } catch(err) {}
        });
        resetUnsavedChanges();
        if (typeof pendingNavigationAction === 'function') {
            const action = pendingNavigationAction;
            pendingNavigationAction = null;
            closeUnsavedChangesModal();
            action();
        } else {
            closeUnsavedChangesModal();
            window.location.reload();
        }
    }

    function handleTabClick(tabName) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || '{{ $tab ?? "period" }}';
        if (tabName === currentTab) return;

        if (hasUnsavedChanges) {
            pendingNavigationAction = () => {
                // Bersihkan dan muat ulang halaman dengan parameter tab tujuan
                window.location.href = window.location.pathname + '?tab=' + tabName;
            };
            openUnsavedChangesModal();
            return;
        }
        switchPpdbTab(tabName);
    }

    // Tab switching logic
    function switchPpdbTab(tabName) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        const activePane = document.getElementById('tab-content-' + tabName);
        if (activePane) activePane.classList.remove('hidden');

        document.querySelectorAll('.tab-trigger').forEach(btn => {
            btn.classList.remove('bg-bluedark', 'text-white', 'shadow-xs');
            btn.classList.add('text-bluedark/70', 'hover:bg-bluelight/40');
            const badge = btn.querySelector('.badge-count');
            if (badge) {
                badge.classList.remove('bg-white/20', 'text-white');
                badge.classList.add('bg-bluelight', 'text-bluedark');
            }
        });

        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.add('bg-bluedark', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('text-bluedark/70', 'hover:bg-bluelight/40');
            const badge = activeBtn.querySelector('.badge-count');
            if (badge) {
                badge.classList.add('bg-white/20', 'text-white');
                badge.classList.remove('bg-bluelight', 'text-bluedark');
            }
        }

        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    /* =========================================================================
       TAB 2: JADWAL PENDAFTARAN (DRAG & DROP, SWAP, HIGHLIGHT PERUBAHAN)
       ========================================================================= */
    function reindexSchedules() {
        const rows = document.querySelectorAll('#schedules-container .schedule-item-row');
        const emptyState = document.getElementById('schedule-empty-state');
        const badge = document.getElementById('schedule-count-badge');
        if (badge) badge.textContent = rows.length + ' Tahap';

        if (rows.length === 0 && emptyState) {
            emptyState.classList.remove('hidden');
        } else if (emptyState) {
            emptyState.classList.add('hidden');
        }

        let anyOrderChanged = false;

        rows.forEach((row, i) => {
            row.setAttribute('data-index', i);
            const stepDisplay = row.querySelector('.step-display');
            if (stepDisplay) stepDisplay.textContent = i + 1;

            const stepLabel = row.querySelector('.step-label');
            if (stepLabel) stepLabel.textContent = i + 1;

            row.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('schedules[')) {
                    input.setAttribute('name', name.replace(/schedules\[\d+\]/, `schedules[${i}]`));
                }
            });

            const btnUp = row.querySelector('.btn-swap-up');
            const btnDown = row.querySelector('.btn-swap-down');
            if (btnUp) btnUp.disabled = (i === 0);
            if (btnDown) btnDown.disabled = (i === rows.length - 1);

            // Periksa apakah posisi baris ini berpindah dari posisi asalnya
            const initialIndexAttr = row.getAttribute('data-initial-index');
            const badgePlaceholder = row.querySelector('.reorder-badge-placeholder');

            if (initialIndexAttr === 'new') {
                anyOrderChanged = true;
                row.classList.add('border-emerald-400', 'bg-emerald-50/30', 'ring-2', 'ring-emerald-300/30');
                row.classList.remove('bg-[#FBFDFF]', 'border-amber-400', 'bg-amber-50/40', 'ring-amber-300/40');
                if (badgePlaceholder) {
                    badgePlaceholder.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Baris Baru Ditambahkan</span>
                        </span>
                    `;
                }
            } else if (initialIndexAttr !== null && initialIndexAttr !== '') {
                const initialIndex = parseInt(initialIndexAttr, 10);
                if (initialIndex !== i) {
                    // URUTAN BERUBAH: Beri highlight amber dan badge
                    anyOrderChanged = true;
                    row.classList.add('border-amber-400', 'bg-amber-50/40', 'ring-2', 'ring-amber-300/40');
                    row.classList.remove('bg-[#FBFDFF]', 'border-emerald-400', 'bg-emerald-50/30', 'ring-emerald-300/30');
                    if (badgePlaceholder) {
                        badgePlaceholder.innerHTML = `
                            <span class="reordered-badge inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 animate-in fade-in duration-150">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                <span>Urutan Diubah</span>
                            </span>
                        `;
                    }
                } else {
                    // KEMBALI KE POSISI SEMULA: HAPUS SEMUA HIGHLIGHT DAN BADGE!
                    row.classList.remove('border-amber-400', 'bg-amber-50/40', 'ring-2', 'ring-amber-300/40', 'border-emerald-400', 'bg-emerald-50/30', 'ring-emerald-300/30');
                    row.classList.add('bg-[#FBFDFF]');
                    if (badgePlaceholder) {
                        badgePlaceholder.innerHTML = '';
                    }
                }
            }
        });

        if (anyOrderChanged) {
            markUnsavedChanges();
        }

        initDragAndDrop();
    }

    function swapScheduleUp(btn) {
        const row = btn.closest('.schedule-item-row');
        const prev = row.previousElementSibling;
        if (prev && prev.classList.contains('schedule-item-row')) {
            row.parentNode.insertBefore(row, prev);
            markUnsavedChanges();
            reindexSchedules();
        }
    }

    function swapScheduleDown(btn) {
        const row = btn.closest('.schedule-item-row');
        const next = row.nextElementSibling;
        if (next && next.classList.contains('schedule-item-row')) {
            row.parentNode.insertBefore(next, row);
            markUnsavedChanges();
            reindexSchedules();
        }
    }

    let draggedItem = null;

    function initDragAndDrop() {
        const container = document.getElementById('schedules-container');
        if (!container) return;

        const rows = container.querySelectorAll('.schedule-item-row');

        rows.forEach(row => {
            const handle = row.querySelector('.drag-handle');
            if (!handle) return;

            // Enable dragging only when handle is clicked/held
            handle.onmousedown = () => {
                row.setAttribute('draggable', 'true');
            };
            handle.onmouseup = () => {
                row.setAttribute('draggable', 'false');
            };

            row.ondragstart = (e) => {
                draggedItem = row;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
                setTimeout(() => {
                    row.classList.add('opacity-40', 'border-blueprim');
                }, 0);
            };

            row.ondragend = () => {
                row.classList.remove('opacity-40', 'border-blueprim');
                row.setAttribute('draggable', 'false');
                draggedItem = null;
                document.querySelectorAll('.schedule-item-row').forEach(r => {
                    r.classList.remove('border-t-4', 'border-b-4', 'border-blueprim');
                });
            };

            row.ondragover = (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                if (!draggedItem || draggedItem === row) return;

                const rect = row.getBoundingClientRect();
                const offset = e.clientY - rect.top;
                if (offset < rect.height / 2) {
                    row.classList.add('border-t-4', 'border-blueprim');
                    row.classList.remove('border-b-4');
                } else {
                    row.classList.add('border-b-4', 'border-blueprim');
                    row.classList.remove('border-t-4');
                }
            };

            row.ondragleave = () => {
                row.classList.remove('border-t-4', 'border-b-4', 'border-blueprim');
            };

            row.ondrop = (e) => {
                e.preventDefault();
                row.classList.remove('border-t-4', 'border-b-4', 'border-blueprim');
                if (!draggedItem || draggedItem === row) return;

                const rect = row.getBoundingClientRect();
                const offset = e.clientY - rect.top;
                if (offset < rect.height / 2) {
                    container.insertBefore(draggedItem, row);
                } else {
                    container.insertBefore(draggedItem, row.nextSibling);
                }

                markUnsavedChanges();
                reindexSchedules();
            };
        });
    }

    function addScheduleRow() {
        const container = document.getElementById('schedules-container');
        const emptyState = document.getElementById('schedule-empty-state');
        if (emptyState) emptyState.classList.add('hidden');

        const rows = container.querySelectorAll('.schedule-item-row');
        const nextIndex = rows.length;
        const nextStep = nextIndex + 1;

        const rowHtml = `
            <div class="schedule-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200 animate-in fade-in duration-200" data-index="${nextIndex}" data-initial-index="new">
                <input type="hidden" name="schedules[${nextIndex}][id]" value="">

                <div class="flex flex-col lg:flex-row items-start gap-4">
                    <div class="flex items-center lg:flex-col gap-1.5 shrink-0">
                        <div class="drag-handle cursor-grab active:cursor-grabbing p-1.5 rounded-lg border border-bluelight hover:bg-bluelight/70 text-bluedark/40 hover:text-bluedark transition-colors" title="Tahan klik kiri & geser atas/bawah untuk memindahkan urutan">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="9" cy="6" r="1.5" fill="currentColor"></circle>
                                <circle cx="15" cy="6" r="1.5" fill="currentColor"></circle>
                                <circle cx="9" cy="12" r="1.5" fill="currentColor"></circle>
                                <circle cx="15" cy="12" r="1.5" fill="currentColor"></circle>
                                <circle cx="9" cy="18" r="1.5" fill="currentColor"></circle>
                                <circle cx="15" cy="18" r="1.5" fill="currentColor"></circle>
                            </svg>
                        </div>

                        <div class="w-9 h-9 rounded-xl bg-bluedark text-white font-heading font-bold text-xs grid place-items-center step-display shadow-2xs">
                            ${nextStep}
                        </div>

                        <div class="flex lg:flex-col gap-1">
                            <button type="button" onclick="swapScheduleUp(this)"
                                class="btn-swap-up p-1.5 rounded-lg border border-bluelight hover:bg-bluelight text-bluedark/70 hover:text-bluedark disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                title="Pindahkan Tahap Ini Ke Atas (Swap)">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </button>
                            <button type="button" onclick="swapScheduleDown(this)"
                                class="btn-swap-down p-1.5 rounded-lg border border-bluelight hover:bg-bluelight text-bluedark/70 hover:text-bluedark disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                title="Pindahkan Tahap Ini Ke Bawah (Swap)">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 w-full space-y-3">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="text-[11px] font-semibold text-bluedark/60">Pengaturan Jadwal Tahap #<span class="step-label">${nextStep}</span></span>
                            <div class="reorder-badge-placeholder"></div>
                        </div>

                        <div class="grid sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-6">
                                <label class="f-label text-[11px]">Nama Tahapan Jadwal <span class="text-red-500">*</span></label>
                                <input type="text" name="schedules[${nextIndex}][title]" required placeholder="Contoh: Tes Kemampuan Akademik & Wawancara" class="f-input text-xs schedule-title">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="f-label text-[11px]">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <input type="date" name="schedules[${nextIndex}][start_date]" required class="f-input text-xs">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="f-label text-[11px]">Tanggal Selesai <span class="text-red-500">*</span></label>
                                <input type="date" name="schedules[${nextIndex}][end_date]" required class="f-input text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="f-label text-[11px]">Deskripsi / Petunjuk Pelaksanaan (Opsional)</label>
                            <input type="text" name="schedules[${nextIndex}][description]" placeholder="Keterangan singkat tahapan..." class="f-input text-xs">
                        </div>
                    </div>

                    <button type="button" onclick="removeScheduleRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end lg:self-center" title="Hapus Tahapan Ini">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        reindexSchedules();
        markUnsavedChanges();

        const newlyAdded = container.lastElementChild;
        if (newlyAdded) {
            const inputTitle = newlyAdded.querySelector('.schedule-title');
            if (inputTitle) inputTitle.focus();
        }
    }

    function removeScheduleRow(btn) {
        const row = btn.closest('.schedule-item-row');
        if (confirm('Hapus tahapan jadwal ini?')) {
            row.remove();
            reindexSchedules();
            markUnsavedChanges();
        }
    }

    /* =========================================================================
       TAB 3: JALUR PENDAFTARAN (REPEATER TAMBAH DI BAWAH)
       ========================================================================= */
    function reindexPaths() {
        const rows = document.querySelectorAll('#paths-container .path-item-row');
        const emptyState = document.getElementById('path-empty-state');
        const badge = document.getElementById('path-count-badge');
        if (badge) badge.textContent = rows.length + ' Jalur';

        if (rows.length === 0 && emptyState) {
            emptyState.classList.remove('hidden');
        } else if (emptyState) {
            emptyState.classList.add('hidden');
        }

        rows.forEach((row, i) => {
            row.setAttribute('data-index', i);
            const num = row.querySelector('.path-number');
            if (num) num.textContent = i + 1;

            row.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('paths[')) {
                    input.setAttribute('name', name.replace(/paths\[\d+\]/, `paths[${i}]`));
                }
            });
        });
    }

    function addPathRow() {
        const container = document.getElementById('paths-container');
        const emptyState = document.getElementById('path-empty-state');
        if (emptyState) emptyState.classList.add('hidden');

        const rows = container.querySelectorAll('.path-item-row');
        const nextIndex = rows.length;
        const nextNum = nextIndex + 1;

        const rowHtml = `
            <div class="path-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200 animate-in fade-in duration-200" data-index="${nextIndex}">
                <input type="hidden" name="paths[${nextIndex}][id]" value="">

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-heading font-bold text-xs grid place-items-center shrink-0 path-number">
                        ${nextNum}
                    </div>

                    <div class="flex-1 w-full space-y-3">
                        <div class="grid sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-6">
                                <label class="f-label text-[11px]">Nama Jalur Seleksi <span class="text-red-500">*</span></label>
                                <input type="text" name="paths[${nextIndex}][name]" required placeholder="Contoh: Jalur Afirmasi / KIP" class="f-input text-xs path-name">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="f-label text-[11px]">Kuota Siswa (Opsional)</label>
                                <input type="number" min="0" name="paths[${nextIndex}][quota]" placeholder="Cth: 30" class="f-input text-xs">
                            </div>

                            <div class="sm:col-span-3 flex items-center pt-6">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-bluedark">
                                    <input type="checkbox" name="paths[${nextIndex}][is_active]" value="1" checked class="w-4 h-4 rounded text-blueprim border-bluelight">
                                    <span>Aktifkan Jalur Ini</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="f-label text-[11px]">Keterangan / Kriteria Jalur</label>
                            <input type="text" name="paths[${nextIndex}][description]" placeholder="Keterangan singkat jalur seleksi..." class="f-input text-xs">
                        </div>
                    </div>

                    <button type="button" onclick="removePathRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Jalur Ini">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        reindexPaths();
        markUnsavedChanges();

        const newlyAdded = container.lastElementChild;
        if (newlyAdded) {
            const inputName = newlyAdded.querySelector('.path-name');
            if (inputName) inputName.focus();
        }
    }

    function removePathRow(btn) {
        const row = btn.closest('.path-item-row');
        if (confirm('Hapus jalur pendaftaran ini?')) {
            row.remove();
            reindexPaths();
            markUnsavedChanges();
        }
    }

    /* =========================================================================
       TAB 4: PERSYARATAN (REPEATER TAMBAH DI BAWAH)
       ========================================================================= */
    function reindexRequirements() {
        const rows = document.querySelectorAll('#requirements-container .requirement-item-row');
        const emptyState = document.getElementById('requirement-empty-state');
        const badge = document.getElementById('requirement-count-badge');
        if (badge) badge.textContent = rows.length + ' Syarat';

        if (rows.length === 0 && emptyState) {
            emptyState.classList.remove('hidden');
        } else if (emptyState) {
            emptyState.classList.add('hidden');
        }

        rows.forEach((row, i) => {
            row.setAttribute('data-index', i);
            const num = row.querySelector('.requirement-number');
            if (num) num.textContent = i + 1;

            row.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('requirements[')) {
                    input.setAttribute('name', name.replace(/requirements\[\d+\]/, `requirements[${i}]`));
                }
            });
        });
    }

    function addRequirementRow() {
        const container = document.getElementById('requirements-container');
        const emptyState = document.getElementById('requirement-empty-state');
        if (emptyState) emptyState.classList.add('hidden');

        const rows = container.querySelectorAll('.requirement-item-row');
        const nextIndex = rows.length;
        const nextNum = nextIndex + 1;

        const rowHtml = `
            <div class="requirement-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200 animate-in fade-in duration-200" data-index="${nextIndex}">
                <input type="hidden" name="requirements[${nextIndex}][id]" value="">

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-heading font-bold text-xs grid place-items-center shrink-0 requirement-number">
                        ${nextNum}
                    </div>

                    <div class="flex-1 w-full space-y-3">
                        <div>
                            <label class="f-label text-[11px]">Nama Persyaratan / Berkas <span class="text-red-500">*</span></label>
                            <input type="text" name="requirements[${nextIndex}][title]" required placeholder="Contoh: Pas Foto Berwarna 3x4 (3 Lembar)" class="f-input text-xs req-title">
                        </div>

                        <div>
                            <label class="f-label text-[11px]">Keterangan Detail (Opsional)</label>
                            <input type="text" name="requirements[${nextIndex}][description]" placeholder="Contoh: Latar belakang warna merah untuk tahun lahir genap" class="f-input text-xs">
                        </div>
                    </div>

                    <button type="button" onclick="removeRequirementRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Persyaratan Ini">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        reindexRequirements();
        markUnsavedChanges();

        const newlyAdded = container.lastElementChild;
        if (newlyAdded) {
            const inputTitle = newlyAdded.querySelector('.req-title');
            if (inputTitle) inputTitle.focus();
        }
    }

    function removeRequirementRow(btn) {
        const row = btn.closest('.requirement-item-row');
        if (confirm('Hapus persyaratan berkas ini?')) {
            row.remove();
            reindexRequirements();
            markUnsavedChanges();
        }
    }

    /* =========================================================================
       TAB 5: BIAYA PENDAFTARAN (REPEATER TAMBAH DI BAWAH)
       ========================================================================= */
    function reindexFees() {
        const rows = document.querySelectorAll('#fees-container .fee-item-row');
        const emptyState = document.getElementById('fee-empty-state');
        const badge = document.getElementById('fee-count-badge');
        if (badge) badge.textContent = rows.length + ' Komponen';

        if (rows.length === 0 && emptyState) {
            emptyState.classList.remove('hidden');
        } else if (emptyState) {
            emptyState.classList.add('hidden');
        }

        rows.forEach((row, i) => {
            row.setAttribute('data-index', i);
            const num = row.querySelector('.fee-number');
            if (num) num.textContent = i + 1;

            row.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.startsWith('fees[')) {
                    input.setAttribute('name', name.replace(/fees\[\d+\]/, `fees[${i}]`));
                }
            });
        });
    }

    function toggleFeeFree(checkbox) {
        const row = checkbox.closest('.fee-item-row');
        const amountInput = row.querySelector('.fee-amount');
        if (amountInput) {
            if (checkbox.checked) {
                amountInput.value = '0';
                amountInput.disabled = true;
            } else {
                amountInput.disabled = false;
                if (amountInput.value === '0') {
                    amountInput.value = '';
                }
            }
        }
        markUnsavedChanges();
    }

    function addFeeRow() {
        const container = document.getElementById('fees-container');
        const emptyState = document.getElementById('fee-empty-state');
        if (emptyState) emptyState.classList.add('hidden');

        const rows = container.querySelectorAll('.fee-item-row');
        const nextIndex = rows.length;
        const nextNum = nextIndex + 1;

        const rowHtml = `
            <div class="fee-item-row p-4 rounded-2xl border border-bluelight bg-[#FBFDFF] hover:border-blueprim/40 transition-all duration-200 animate-in fade-in duration-200" data-index="${nextIndex}">
                <input type="hidden" name="fees[${nextIndex}][id]" value="">

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 font-heading font-bold text-xs grid place-items-center shrink-0 fee-number">
                        ${nextNum}
                    </div>

                    <div class="flex-1 w-full space-y-3">
                        <div class="grid sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-6">
                                <label class="f-label text-[11px]">Nama Komponen Biaya <span class="text-red-500">*</span></label>
                                <input type="text" name="fees[${nextIndex}][name]" required placeholder="Contoh: Biaya Seragam &amp; Atribut" class="f-input text-xs fee-name">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="f-label text-[11px]">Nominal (Rp)</label>
                                <input type="number" min="0" step="1000" name="fees[${nextIndex}][amount]" placeholder="Cth: 500000" class="f-input text-xs fee-amount">
                            </div>

                            <div class="sm:col-span-3 flex items-center pt-6">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-bluedark">
                                    <input type="checkbox" name="fees[${nextIndex}][is_free]" value="1" onchange="toggleFeeFree(this)" class="w-4 h-4 rounded text-emerald-600 border-bluelight fee-free-checkbox">
                                    <span class="text-emerald-700">Gratis (Bebas Biaya)</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="f-label text-[11px]">Keterangan Pembayaran (Opsional)</label>
                            <input type="text" name="fees[${nextIndex}][description]" placeholder="Keterangan rincian biaya..." class="f-input text-xs">
                        </div>
                    </div>

                    <button type="button" onclick="removeFeeRow(this)" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-500 hover:text-red-700 transition-colors shrink-0 self-end sm:self-center" title="Hapus Biaya Ini">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        reindexFees();
        markUnsavedChanges();

        const newlyAdded = container.lastElementChild;
        if (newlyAdded) {
            const inputName = newlyAdded.querySelector('.fee-name');
            if (inputName) inputName.focus();
        }
    }

    function removeFeeRow(btn) {
        const row = btn.closest('.fee-item-row');
        if (confirm('Hapus rincian biaya ini?')) {
            row.remove();
            reindexFees();
            markUnsavedChanges();
        }
    }

    // Initialize initial states on page load
    document.addEventListener('DOMContentLoaded', function() {
        reindexSchedules();
        reindexPaths();
        reindexRequirements();
        reindexFees();

        // Check URL hash if tab not provided in query
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || '{{ $tab ?? "period" }}';
        if (currentTab) {
            switchPpdbTab(currentTab);
        }
    });
</script>
@endpush
@endsection
