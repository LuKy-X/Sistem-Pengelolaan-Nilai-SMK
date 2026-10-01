@extends('layouts.admin')

@section('title', 'Manajemen Informasi PPDB')

@push('styles')
<style>
/* PPDB Status Toggle Switch */
.ppdb-toggle-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
}
.ppdb-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.ppdb-toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #CBD5E1;
    transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 9999px;
}
.ppdb-toggle-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: #FFFFFF;
    transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
}
.ppdb-toggle-switch input:checked + .ppdb-toggle-slider {
    background-color: #10B981;
}
.ppdb-toggle-switch input:checked + .ppdb-toggle-slider:before {
    transform: translateX(18px);
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penerimaan Peserta Didik Baru (PPDB)</h1>
                <span class="badge badge-blue text-xs font-semibold">{{ $periods->count() }} Gelombang</span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">Pilih salah satu gelombang di bawah ini untuk mengatur jadwal, jalur pendaftaran, persyaratan, dan rincian biaya.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('public.ppdb.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Lihat Web PPDB Publik</span>
            </a>

            <button type="button" onclick="openPpdbModal()" class="btn btn-primary btn-sm flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Buka Gelombang Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Success -->
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

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="panel p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-bluelight/70 text-blueprim grid place-items-center shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs text-bluedark/50 font-medium">Total Gelombang</p>
                <p class="font-heading font-bold text-lg text-bluedark">{{ $stats['total_periods'] ?? $periods->count() }}</p>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 grid place-items-center shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <p class="text-xs text-bluedark/50 font-medium">Gelombang Dibuka (Open)</p>
                <p class="font-heading font-bold text-lg text-emerald-600">{{ $stats['open_periods'] ?? $periods->where('status', 'OPEN')->count() }}</p>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 grid place-items-center shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <p class="text-xs text-bluedark/50 font-medium">Total Tahapan Jadwal</p>
                <p class="font-heading font-bold text-lg text-amber-600">{{ $stats['total_schedules'] ?? 0 }}</p>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 grid place-items-center shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="18" r="3"></circle>
                    <circle cx="6" cy="6" r="3"></circle>
                    <path d="M13 6h3a2 2 0 0 1 2 2v7"></path>
                    <line x1="6" y1="9" x2="6" y2="21"></line>
                </svg>
            </div>
            <div>
                <p class="text-xs text-bluedark/50 font-medium">Total Jalur Seleksi</p>
                <p class="font-heading font-bold text-lg text-indigo-600">{{ $stats['total_paths'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Notice info box -->
    <div class="p-4 rounded-2xl bg-blueprim/10 border border-blueprim/20 flex items-start gap-3.5 text-xs text-bluedark/80">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <div class="space-y-1">
            <p class="font-semibold text-bluedark text-sm">Petunjuk Pengelolaan PPDB</p>
            <p class="leading-relaxed">Setiap gelombang PPDB memiliki konfigurasi tersendiri untuk <strong>Jadwal Pelaksanaan</strong>, <strong>Jalur Pendaftaran</strong>, <strong>Persyaratan Berkas</strong>, dan <strong>Rincian Biaya</strong>. Silakan klik tombol <strong>"Kelola Gelombang"</strong> pada salah satu gelombang di bawah ini untuk mulai mengisi atau mengedit datanya.</p>
        </div>
    </div>

    <!-- Daftar Gelombang PPDB -->
    <div class="space-y-4">
        @forelse($periods as $period)
            <div class="panel p-5 space-y-4 hover:border-blueprim/40 transition-all duration-200">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="space-y-1.5 max-w-2xl">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="font-heading font-bold text-bluedark text-lg hover:text-blueprim transition-colors">
                                <a href="{{ route('admin.cms.ppdb.periods.manage', $period) }}">
                                    {{ $period->title }}
                                </a>
                            </h2>

                            @if($period->status === 'OPEN')
                                <span class="period-badge-{{ $period->id }} inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Dibuka (Open)
                                </span>
                            @elseif($period->status === 'CLOSED')
                                <span class="period-badge-{{ $period->id }} inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-300">
                                    Ditutup (Closed)
                                </span>
                            @else
                                <span class="period-badge-{{ $period->id }} inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-300">
                                    Draf (Draft)
                                </span>
                            @endif

                            @if($period->academicYear)
                                <span class="text-xs px-2.5 py-1 rounded-full bg-bluelight text-bluedark font-medium">
                                    TA: {{ $period->academicYear->name }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 text-xs text-bluedark/60 flex-wrap">
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blueprim"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Masa Pendaftaran: <strong>{{ \Carbon\Carbon::parse($period->registration_start)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($period->registration_end)->translatedFormat('d M Y') }}</strong>
                            </span>

                            @if($period->status !== 'DRAFT')
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Tayang di Web Publik
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 font-medium">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    Hanya di Admin (Draf)
                                </span>
                            @endif
                        </div>

                        @if(filled($period->description))
                            <p class="text-xs text-bluedark/70 mt-1 line-clamp-2 leading-relaxed">
                                {{ $period->description }}
                            </p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 shrink-0 flex-wrap">
                        <!-- Toggle Status Dibuka / Ditutup -->
                        <div class="flex items-center gap-2.5 bg-slate-50 border border-bluelight px-3 py-1.5 rounded-xl shadow-2xs hover:bg-slate-100/70 transition-colors" title="Beralih status Dibuka (Open) atau Ditutup (Closed)">
                            <span class="text-xs font-semibold {{ $period->status === 'OPEN' ? 'text-emerald-700' : 'text-slate-600' }} status-toggle-label-{{ $period->id }}">
                                {{ $period->status === 'OPEN' ? 'Dibuka' : 'Ditutup' }}
                            </span>
                            <label class="ppdb-toggle-switch">
                                <input type="checkbox"
                                    class="period-status-toggle"
                                    data-period-id="{{ $period->id }}"
                                    data-url="{{ route('admin.cms.ppdb.periods.toggle-status', $period) }}"
                                    {{ $period->status === 'OPEN' ? 'checked' : '' }}>
                                <span class="ppdb-toggle-slider"></span>
                            </label>
                        </div>

                        <a href="{{ route('admin.cms.ppdb.periods.manage', $period) }}" class="btn btn-primary btn-sm flex items-center gap-2">
                            <span>Kelola Gelombang</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>

                        <button type="button"
                            onclick='openEditPeriodModal(@json($period))'
                            class="p-2 rounded-xl border border-bluelight hover:bg-bluelight/50 text-bluedark/70 hover:text-bluedark transition-colors"
                            title="Edit Data Gelombang">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>

                        <form method="POST" action="{{ route('admin.cms.ppdb.periods.destroy', $period) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gelombang {{ $period->title }} ini? Seluruh data jadwal, jalur, persyaratan, dan biaya pada gelombang ini akan terhapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-600 transition-colors" title="Hapus Gelombang">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 4 Sub-tables status pill summary -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3 border-t border-bluelight/80">
                    <a href="{{ route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'schedules']) }}"
                        class="p-2.5 rounded-xl border border-bluelight/80 bg-bluelight/10 hover:bg-bluelight/30 hover:border-blueprim/30 transition-all flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 grid place-items-center text-xs font-bold shrink-0">
                            {{ $period->schedule_items_count ?? $period->scheduleItems->count() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-bluedark/50">Jadwal Seleksi</p>
                            <p class="font-heading font-semibold text-xs text-bluedark truncate">Tahapan Pelaksanaan</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'paths']) }}"
                        class="p-2.5 rounded-xl border border-bluelight/80 bg-bluelight/10 hover:bg-bluelight/30 hover:border-blueprim/30 transition-all flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 grid place-items-center text-xs font-bold shrink-0">
                            {{ $period->paths_count ?? $period->paths->count() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-bluedark/50">Jalur Pendaftaran</p>
                            <p class="font-heading font-semibold text-xs text-bluedark truncate">Pilihan Jalur Masuk</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'requirements']) }}"
                        class="p-2.5 rounded-xl border border-bluelight/80 bg-bluelight/10 hover:bg-bluelight/30 hover:border-blueprim/30 transition-all flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 grid place-items-center text-xs font-bold shrink-0">
                            {{ $period->requirements_count ?? $period->requirements->count() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-bluedark/50">Persyaratan</p>
                            <p class="font-heading font-semibold text-xs text-bluedark truncate">Dokumen & Berkas</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'fees']) }}"
                        class="p-2.5 rounded-xl border border-bluelight/80 bg-bluelight/10 hover:bg-bluelight/30 hover:border-blueprim/30 transition-all flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 grid place-items-center text-xs font-bold shrink-0">
                            {{ $period->fee_items_count ?? $period->feeItems->count() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-bluedark/50">Rincian Biaya</p>
                            <p class="font-heading font-semibold text-xs text-bluedark truncate">Komponen Biaya</p>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="panel p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-bluelight text-blueprim grid place-items-center mx-auto">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark">Belum Ada Gelombang PPDB</h3>
                    <p class="text-xs text-bluedark/60 max-w-md mx-auto mt-1">Buat gelombang pendaftaran baru untuk mulai mengelola jadwal tahapan, jalur seleksi, syarat, dan rincian biaya.</p>
                </div>
                <button type="button" onclick="openPpdbModal()" class="btn btn-primary btn-sm inline-flex items-center gap-2">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Buka Gelombang Pertama</span>
                </button>
            </div>
        @endforelse
    </div>

    @if($periods->hasPages())
        <div class="mt-4 pt-3 border-t border-bluelight">
            {{ $periods->links() }}
        </div>
    @endif

</div>

<!-- Modal Tambah Gelombang Baru -->
<div id="ppdbModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-bluelight">
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Buka Gelombang PPDB Baru</h3>
                <p class="text-xs text-bluedark/50 mt-0.5">Tentukan periode dan tahun ajaran gelombang baru</p>
            </div>
            <button type="button" onclick="closePpdbModal()" class="w-8 h-8 rounded-full hover:bg-bluelight/50 grid place-items-center text-bluedark/50 hover:text-bluedark text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.ppdb.periods.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select name="academic_year_id" required class="f-select">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->name }} {{ $y->is_active ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Nama Gelombang <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: PPDB Gelombang 1 - Reguler & Prestasi" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Mulai Pendaftaran <span class="text-red-500">*</span></label>
                    <input type="date" name="registration_start" required value="{{ date('Y-m-d') }}" class="f-input">
                </div>
                <div>
                    <label class="f-label">Selesai Pendaftaran <span class="text-red-500">*</span></label>
                    <input type="date" name="registration_end" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Status Gelombang <span class="text-red-500">*</span></label>
                <select name="status" required class="f-select">
                    <option value="OPEN" selected>Dibuka (Open) &mdash; Tampil di Web Publik</option>
                    <option value="DRAFT">Draf (Draft) &mdash; Hanya tersimpan di Admin</option>
                    <option value="CLOSED">Ditutup (Closed) &mdash; Pendaftaran selesai</option>
                </select>
            </div>

            <div>
                <label class="f-label">Deskripsi Ringkas (Opsional)</label>
                <textarea name="description" rows="3" placeholder="Informasi singkat tentang gelombang ini..." class="f-input"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePpdbModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan &amp; Lanjut Atur Gelombang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Gelombang -->
<div id="editPeriodModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-bluelight">
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Edit Informasi Gelombang</h3>
                <p class="text-xs text-bluedark/50 mt-0.5">Ubah nama, tanggal, status, atau tahun ajaran</p>
            </div>
            <button type="button" onclick="closeEditPeriodModal()" class="w-8 h-8 rounded-full hover:bg-bluelight/50 grid place-items-center text-bluedark/50 hover:text-bluedark text-xl leading-none">&times;</button>
        </div>

        <form id="editPeriodForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="f-label">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select id="edit_academic_year_id" name="academic_year_id" required class="f-select">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }} {{ $y->is_active ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Nama Gelombang <span class="text-red-500">*</span></label>
                <input type="text" id="edit_title" name="title" required class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Mulai Pendaftaran <span class="text-red-500">*</span></label>
                    <input type="date" id="edit_registration_start" name="registration_start" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Selesai Pendaftaran <span class="text-red-500">*</span></label>
                    <input type="date" id="edit_registration_end" name="registration_end" required class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Status Gelombang <span class="text-red-500">*</span></label>
                <select id="edit_status" name="status" required class="f-select">
                    <option value="OPEN">Dibuka (Open) &mdash; Tampil di Web Publik</option>
                    <option value="DRAFT">Draf (Draft) &mdash; Hanya tersimpan di Admin</option>
                    <option value="CLOSED">Ditutup (Closed) &mdash; Pendaftaran selesai</option>
                </select>
            </div>

            <div>
                <label class="f-label">Deskripsi Ringkas</label>
                <textarea id="edit_description" name="description" rows="3" class="f-input"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeEditPeriodModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPpdbModal() {
        document.getElementById('ppdbModal').classList.remove('hidden');
    }
    function closePpdbModal() {
        document.getElementById('ppdbModal').classList.add('hidden');
    }

    function openEditPeriodModal(period) {
        const form = document.getElementById('editPeriodForm');
        form.action = `/admin/cms/ppdb/periods/${period.id}`;

        document.getElementById('edit_academic_year_id').value = period.academic_year_id;
        document.getElementById('edit_title').value = period.title;
        document.getElementById('edit_registration_start').value = (period.registration_start || '').split('T')[0];
        document.getElementById('edit_registration_end').value = (period.registration_end || '').split('T')[0];
        document.getElementById('edit_status').value = period.status;
        document.getElementById('edit_description').value = period.description || '';

        document.getElementById('editPeriodModal').classList.remove('hidden');
    }
    function closeEditPeriodModal() {
        document.getElementById('editPeriodModal').classList.add('hidden');
    }

    // Close on outside click
    window.addEventListener('click', function(e) {
        const addModal = document.getElementById('ppdbModal');
        const editModal = document.getElementById('editPeriodModal');
        if (e.target === addModal) closePpdbModal();
        if (e.target === editModal) closeEditPeriodModal();
    });

    // Toggle status Dibuka / Ditutup real-time
    document.querySelectorAll('.period-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const periodId = this.dataset.periodId;
            const url = this.dataset.url;
            const isChecked = this.checked;
            const label = document.querySelector(`.status-toggle-label-${periodId}`);
            const badge = document.querySelector(`.period-badge-${periodId}`);

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ is_open: isChecked })
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response not ok');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (label) {
                        label.textContent = data.is_open ? 'Dibuka' : 'Ditutup';
                        label.className = `text-xs font-semibold ${data.is_open ? 'text-emerald-700' : 'text-slate-600'} status-toggle-label-${periodId}`;
                    }
                    if (badge) {
                        if (data.is_open) {
                            badge.className = `period-badge-${periodId} inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300`;
                            badge.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Dibuka (Open)`;
                        } else {
                            badge.className = `period-badge-${periodId} inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-300`;
                            badge.innerHTML = `Ditutup (Closed)`;
                        }
                    }
                } else {
                    this.checked = !isChecked;
                    alert('Gagal mengubah status gelombang: ' + (data.message || 'Error'));
                }
            })
            .catch(() => {
                this.checked = !isChecked;
                alert('Terjadi kesalahan jaringan saat memperbarui status.');
            });
        });
    });
</script>
@endpush
@endsection
