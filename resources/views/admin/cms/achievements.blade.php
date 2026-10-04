@extends('layouts.admin')

@section('title', 'Manajemen Prestasi Sekolah & Siswa')

@push('styles')
<style>
/* ==========================================================================
   ACHIEVEMENTS CMS STYLING & CLEAN AESTHETICS
   ========================================================================== */

/* Sembunyikan scrollbar di seluruh elemen, modal, tabel, dan halaman */
*::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
* {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}
html, body {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}
html::-webkit-scrollbar, body::-webkit-scrollbar {
    display: none !important;
}

/* Dropzone Styling for Drag & Drop Photo Upload */
.ach-dropzone {
    border: 2px dashed #93C5FD;
    background: #F8FAFC;
    border-radius: 1rem;
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    user-select: none;
}
.ach-dropzone:hover,
.ach-dropzone.dragover {
    border-color: #2563EB;
    background: #EFF6FF;
    transform: scale(1.005);
}

/* Neutral Blur Modal Backdrop (No Blue Overlay!) */
.ach-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    overflow-y: auto;
    padding: 1.5rem 1rem;
    display: none;
    justify-content: center;
    align-items: center;
}
.ach-modal-overlay.show {
    display: flex !important;
}
.ach-modal-dialog {
    background: #FFFFFF;
    border-radius: 1.5rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #E2E8F0;
    margin: auto;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: achModalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes achModalFadeIn {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Public Card Replica for Preview */
.ach-preview-card {
    border-radius: 1.25rem;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Etalase Prestasi Sekolah &amp; Siswa</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    CMS Publik
                </span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola data prestasi siswa &amp; sekolah. Semua prestasi ditampilkan di halaman publik (<a href="{{ route('public.achievements.index') }}" target="_blank" class="text-blueprim underline hover:text-blue-700">/prestasi</a>), sedangkan prestasi bertag Unggulan ditampilkan di Beranda utama.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('public.achievements.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2 text-xs font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Lihat di Web</span>
            </a>
            <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Prestasi</span>
            </button>
        </div>
    </div>


    <!-- Quick Stats Cards (Fitur Berguna) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Card 1: Total Prestasi -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-blue-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blueprim flex items-center justify-center shrink-0 border border-blue-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Total Prestasi</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Semua tampil di /prestasi</div>
            </div>
        </div>

        <!-- Card 2: Tag Unggulan (Pinned) -->
        <div class="panel p-4 flex items-center gap-3.5 bg-gradient-to-br from-amber-50/50 to-white border border-amber-200/80 shadow-xs hover:border-amber-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-amber-800/80 uppercase tracking-wider">Tag Unggulan</div>
                <div class="font-heading font-extrabold text-2xl text-amber-900 leading-tight mt-0.5" id="statFeaturedCount">{{ number_format($stats['featured'] ?? 0) }}</div>
                <div class="text-[10px] text-amber-700/70 mt-0.5 truncate">Tampil di Beranda publik</div>
            </div>
        </div>

        <!-- Card 3: Tingkat Nasional & Internasional -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-indigo-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Nasional &amp; Intl</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['national_intl'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Skala kompetisi tinggi</div>
            </div>
        </div>

        <!-- Card 4: Kategori Prestasi -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-teal-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Kategori Tersedia</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['categories'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Klasifikasi lomba</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar (Berguna & Ringan) -->
    <div class="panel p-4 bg-white border border-bluelight/70 shadow-xs">
        <form method="GET" action="{{ route('admin.cms.achievements') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
            
            <!-- Search Keyword -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama lomba, juara, penyelenggara, atau siswa..." class="f-input pl-9 text-xs w-full">
            </div>

            <!-- Filter Kategori -->
            <div class="w-full sm:w-44">
                <select name="category_id" class="f-select text-xs w-full">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tingkat -->
            <div class="w-full sm:w-36">
                <select name="level" class="f-select text-xs w-full">
                    <option value="">Semua Tingkat</option>
                    <option value="KABUPATEN" {{ strtoupper($level ?? '') === 'KABUPATEN' ? 'selected' : '' }}>Kabupaten</option>
                    <option value="PROVINSI" {{ strtoupper($level ?? '') === 'PROVINSI' ? 'selected' : '' }}>Provinsi</option>
                    <option value="NASIONAL" {{ strtoupper($level ?? '') === 'NASIONAL' ? 'selected' : '' }}>Nasional</option>
                    <option value="INTERNASIONAL" {{ strtoupper($level ?? '') === 'INTERNASIONAL' ? 'selected' : '' }}>Internasional</option>
                </select>
            </div>

            <!-- Filter Status Tag Unggulan -->
            <div class="w-full sm:w-44">
                <select name="is_featured" class="f-select text-xs w-full">
                    <option value="">Semua Prestasi</option>
                    <option value="1" {{ (string)$isFeatured === '1' ? 'selected' : '' }}>Tag Unggulan (Beranda)</option>
                    <option value="0" {{ (string)$isFeatured === '0' ? 'selected' : '' }}>Tanpa Tag Unggulan</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="btn btn-primary btn-sm flex items-center justify-center gap-1.5 text-xs font-semibold px-4">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                </button>
                @if($search || $categoryId || $level || $isFeatured !== null && $isFeatured !== '')
                    <a href="{{ route('admin.cms.achievements') }}" class="btn btn-outline btn-sm text-xs font-semibold text-bluedark/70 hover:text-bluedark" title="Reset Filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    </a>
                @endif
            </div>
        </form>

        <!-- Active Filter Badges -->
        @if($search || $categoryId || $level || ($isFeatured !== null && $isFeatured !== ''))
            <div class="flex flex-wrap items-center gap-1.5 mt-3 pt-3 border-t border-bluelight/60 text-xs">
                <span class="text-bluedark/50 text-[11px] font-medium mr-1">Filter Aktif:</span>
                @if($search)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blueprim text-[11px] border border-blue-200">
                        Kata Kunci: "{{ $search }}"
                    </span>
                @endif
                @if($categoryId)
                    @php $catObj = $categories->firstWhere('id', $categoryId); @endphp
                    @if($catObj)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blueprim text-[11px] border border-blue-200">
                            Kategori: {{ $catObj->name }}
                        </span>
                    @endif
                @endif
                @if($level)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blueprim text-[11px] border border-blue-200">
                        Tingkat: {{ ucfirst(strtolower($level)) }}
                    </span>
                @endif
                @if($isFeatured !== null && $isFeatured !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] border border-amber-200">
                        Status: {{ $isFeatured === '1' ? 'Tag Unggulan (Beranda)' : 'Tanpa Tag Unggulan' }}
                    </span>
                @endif
                <a href="{{ route('admin.cms.achievements') }}" class="text-[11px] text-rose-600 hover:underline ml-1 font-medium">Hapus Semua Filter</a>
            </div>
        @endif
    </div>

    <!-- Table Prestasi (Lengkap Semua Data, Ringan 10 per halaman) -->
    <div class="panel p-5 bg-white border border-bluelight/70 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-bluelight/60">
            <div>
                <h2 class="font-heading font-bold text-bluedark text-base">Daftar Rekam Jejak Prestasi</h2>
                <span class="text-xs text-bluedark/50">
                    Menampilkan {{ $achievements->firstItem() ?? 0 }} - {{ $achievements->lastItem() ?? 0 }} dari {{ $achievements->total() }} prestasi (10 data per halaman)
                </span>
            </div>
            <div class="text-xs text-bluedark/60 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>Tag Unggulan: Ditampilkan di Beranda publik (Semua prestasi tetap ada di /prestasi)</span>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto -mx-5 px-5">
            <table class="tbl w-full text-left border-collapse min-w-[960px]">
                <thead>
                    <tr class="border-b border-bluelight text-xs text-bluedark/70">
                        <th class="w-10 text-center py-3">No</th>
                        <th class="w-20 py-3">Foto</th>
                        <th class="min-w-[220px] py-3">Prestasi &amp; Bidang</th>
                        <th class="min-w-[130px] py-3">Kategori &amp; Tingkat</th>
                        <th class="min-w-[120px] py-3">Juara / Peringkat</th>
                        <th class="min-w-[150px] py-3">Siswa Peraih</th>
                        <th class="min-w-[140px] py-3">Penyelenggara &amp; Tanggal</th>
                        <th class="w-32 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bluelight/40 text-xs">
                    @forelse($achievements as $idx => $ach)
                        @php
                            $photoUrl = app(\App\Services\PublicMediaService::class)->forModel($ach);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors group" id="achRow-{{ $ach->id }}">
                            <!-- No -->
                            <td class="text-center font-medium text-bluedark/60 py-3.5">
                                {{ $achievements->firstItem() + $idx }}
                            </td>

                            <!-- Foto Dokumentasi (Clickable to preview) -->
                            <td class="py-3.5">
                                <button type="button" onclick="previewAchievement({{ $ach->id }})" class="relative group/thumb block w-14 h-11 rounded-lg overflow-hidden border border-bluelight/80 bg-slate-100 shadow-2xs focus:outline-hidden hover:ring-2 hover:ring-blueprim transition-all" title="Klik untuk pratinjau prestasi">
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ $ach->title }}" class="w-full h-full object-cover group-hover/thumb:scale-110 transition-transform duration-200">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-blue-50/70 text-blueprim">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/thumb:opacity-100 flex items-center justify-center transition-opacity text-white">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </div>
                                </button>
                            </td>

                            <!-- Prestasi & Bidang -->
                            <td class="py-3.5">
                                <div class="font-heading font-bold text-bluedark text-sm group-hover:text-blueprim transition-colors cursor-pointer" onclick="previewAchievement({{ $ach->id }})">
                                    {{ $ach->title }}
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <!-- Scope Badge -->
                                    @php
                                        $scopeUpper = strtoupper($ach->scope);
                                        $scopeColor = match($scopeUpper) {
                                            'VOKASI' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'AKADEMIK' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $scopeColor }}">
                                        {{ $ach->scope }}
                                    </span>

                                    <!-- Tag Unggulan Badge (HANYA tampil jika is_featured == 1 / true) -->
                                    <span class="ach-featured-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200" 
                                          style="{{ $ach->is_featured ? 'display: inline-flex !important;' : 'display: none !important;' }}" 
                                          title="Tag Unggulan (Tampil di Beranda)">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span>Tag Unggulan</span>
                                    </span>
                                </div>
                                @if(filled($ach->description))
                                    <p class="text-[11px] text-bluedark/60 line-clamp-1 mt-1 font-normal max-w-sm">
                                        {{ $ach->description }}
                                    </p>
                                @endif
                            </td>

                            <!-- Kategori & Tingkat -->
                            <td class="py-3.5">
                                <div class="font-medium text-bluedark">{{ $ach->category?->name ?? 'Umum' }}</div>
                                @php
                                    $lvlUpper = strtoupper($ach->level);
                                    $lvlColor = match($lvlUpper) {
                                        'INTERNASIONAL' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'NASIONAL' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'PROVINSI' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border mt-1 {{ $lvlColor }}">
                                    {{ $ach->level }}
                                </span>
                            </td>

                            <!-- Juara / Peringkat -->
                            <td class="py-3.5">
                                @if(filled($ach->rank))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-heading font-bold text-xs shadow-2xs">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                        <span>{{ $ach->rank }}</span>
                                    </span>
                                @else
                                    <span class="text-bluedark/40 italic">-</span>
                                @endif
                            </td>

                            <!-- Siswa Peraih Prestasi -->
                            <td class="py-3.5">
                                @if($ach->participants->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach($ach->participants->take(2) as $p)
                                            <div class="flex items-center gap-1.5 text-[11px] text-bluedark font-medium">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blueprim flex items-center justify-center text-[9px] font-bold">
                                                    {{ substr($p->student?->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <span class="truncate max-w-[130px]">{{ $p->student?->full_name ?? 'Siswa' }}</span>
                                            </div>
                                        @endforeach
                                        @if($ach->participants->count() > 2)
                                            <span class="text-[10px] text-blueprim font-semibold">
                                                +{{ $ach->participants->count() - 2 }} siswa lainnya
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-bluedark/40 italic">Tim Sekolah / Umum</span>
                                @endif
                            </td>

                            <!-- Penyelenggara & Tanggal -->
                            <td class="py-3.5">
                                <div class="font-medium text-bluedark truncate max-w-[150px]" title="{{ $ach->organizer }}">
                                    {{ $ach->organizer ?: '-' }}
                                </div>
                                <div class="text-[11px] text-bluedark/50 flex items-center gap-1 mt-0.5">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>{{ $ach->achievement_date ? \Carbon\Carbon::parse($ach->achievement_date)->translatedFormat('d M Y') : '-' }}</span>
                                </div>
                            </td>

                            <!-- Kolom Aksi: Pin (di samping Edit), Preview, Edit, Hapus -->
                            <td class="py-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-bluelight/70 shadow-2xs">
                                    
                                    <!-- Tombol Pratinjau (Preview) -->
                                    <button type="button" 
                                            onclick="previewAchievement({{ $ach->id }})" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blueprim hover:bg-white hover:shadow-xs transition-all" 
                                            title="Pratinjau Tampilan Publik">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    <!-- Tombol Pin (Tag Unggulan) - TEPAT DI SAMPING TOMBOL EDIT -->
                                    <button type="button" 
                                            id="achPinBtn-{{ $ach->id }}" 
                                            onclick="togglePinAchievement({{ $ach->id }})" 
                                            data-pinned="{{ $ach->is_featured ? '1' : '0' }}"
                                            class="ach-pin-btn w-7 h-7 rounded-lg flex items-center justify-center transition-all {{ $ach->is_featured ? 'text-amber-700 bg-amber-100 hover:bg-amber-200 border border-amber-300' : 'text-slate-400 hover:text-amber-600 hover:bg-white hover:shadow-xs' }}" 
                                            title="{{ $ach->is_featured ? 'Lepas Tag Unggulan (Saat ini: Bertag Unggulan)' : 'Beri Tag Unggulan (Tampil di Beranda Publik)' }}">
                                        @if($ach->is_featured)
                                            <!-- Solid Pinned Pin -->
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @else
                                            <!-- Outline Pin / Star -->
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @endif
                                    </button>

                                    <!-- Tombol Edit (Buka Modal Edit) -->
                                    <button type="button" 
                                            onclick="openEditModal({{ $ach->id }})" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-white hover:shadow-xs transition-all" 
                                            title="Edit Prestasi">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button type="button" 
                                            onclick="openDeleteModal({{ $ach->id }}, '{{ addslashes($ach->title) }}')" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all" 
                                            title="Hapus Prestasi">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-bluedark/40">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-bluedark/30">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <div class="font-heading font-semibold text-bluedark text-sm">Tidak Ada Data Prestasi</div>
                                    <p class="text-xs text-bluedark/50">
                                        @if($search || $categoryId || $level || ($isFeatured !== null && $isFeatured !== ''))
                                            Tidak ditemukan prestasi yang sesuai dengan kriteria filter saat ini.
                                        @else
                                            Belum ada data prestasi yang dicatat. Klik tombol di atas untuk menambah prestasi baru.
                                        @endif
                                    </p>
                                    @if($search || $categoryId || $level || ($isFeatured !== null && $isFeatured !== ''))
                                        <a href="{{ route('admin.cms.achievements') }}" class="btn btn-outline btn-xs mt-2 inline-flex items-center gap-1">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Laravel Server-side (Strictly 10 per page, ringan) -->
        <div class="mt-5 pt-3 border-t border-bluelight/60">
            {{ $achievements->links() }}
        </div>
    </div>

</div>

<!-- ========================================================================
     MODAL 1: TAMBAH PRESTASI (CREATE)
     ======================================================================== -->
<div id="createAchModal" class="ach-modal-overlay">
    <div class="ach-modal-dialog max-w-2xl max-h-[92vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Tambah Data Prestasi Baru</h3>
                    <p class="text-[11px] text-bluedark/50">Lengkapi formulir untuk mencatat capaian dan kejuaraan siswa</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold">&times;</button>
        </div>

        <!-- Form Tambah -->
        <form method="POST" action="{{ route('admin.cms.achievements.store') }}" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Judul Kejuaraan -->
            <div>
                <label class="f-label text-xs">Nama / Judul Kejuaraan <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Juara 1 LKS Tingkat Nasional Bidang Web Technologies 2026" class="f-input text-xs w-full">
            </div>

            <!-- Kategori & Scope -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label text-xs">Kategori Prestasi <span class="text-rose-500">*</span></label>
                    <select name="achievement_category_id" required class="f-select text-xs w-full">
                        <option value="" disabled selected>Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Bidang / Scope <span class="text-rose-500">*</span></label>
                    <select name="scope" required class="f-select text-xs w-full">
                        <option value="VOKASI" selected>Vokasi / Kejuruan</option>
                        <option value="AKADEMIK">Akademik</option>
                        <option value="NON_AKADEMIK">Non-Akademik / Seni &amp; Olahraga</option>
                    </select>
                </div>
            </div>

            <!-- Tingkat, Peringkat/Juara, Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="f-label text-xs">Tingkat Kompetisi <span class="text-rose-500">*</span></label>
                    <select name="level" required class="f-select text-xs w-full">
                        <option value="KABUPATEN">Kabupaten</option>
                        <option value="PROVINSI">Provinsi</option>
                        <option value="NASIONAL" selected>Nasional</option>
                        <option value="INTERNASIONAL">Internasional</option>
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Juara / Peringkat</label>
                    <input type="text" name="rank" placeholder="Juara 1 Emas / Harapan 1" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                    <input type="date" name="achievement_date" value="{{ date('Y-m-d') }}" required class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Penyelenggara -->
            <div>
                <label class="f-label text-xs">Penyelenggara Kegiatan</label>
                <input type="text" name="organizer" placeholder="Contoh: Balai Pengembangan Talenta Indonesia, Kemendikbudristek" class="f-input text-xs w-full">
            </div>

            <!-- Siswa Peraih Prestasi (Interactive Tagging Selector) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Siswa Peraih Prestasi (Peserta)</label>
                    <span class="text-[10px] text-bluedark/40">Dapat memilih lebih dari 1 siswa</span>
                </div>
                
                <!-- Search & Add Input -->
                <div class="relative">
                    <input type="text" 
                           id="createStudentSearch" 
                           placeholder="Ketik nama atau NIS siswa untuk menambahkan..." 
                           autocomplete="off"
                           class="f-input text-xs w-full pr-8"
                           oninput="filterStudentDropdown('create')">
                    <span class="absolute right-2.5 top-2.5 text-bluedark/40">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>

                    <!-- Dropdown Results -->
                    <div id="createStudentDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-bluelight rounded-xl shadow-lg z-30 max-h-48 overflow-y-auto text-xs divide-y divide-slate-100">
                        @foreach($students as $st)
                            <div class="p-2.5 hover:bg-blue-50 cursor-pointer flex items-center justify-between student-option" 
                                 data-id="{{ $st->id }}" 
                                 data-name="{{ $st->full_name }}" 
                                 data-nis="{{ $st->nis }}"
                                 onclick="addStudentTag('create', {{ $st->id }}, '{{ addslashes($st->full_name) }}', '{{ $st->nis }}')">
                                <span class="font-medium text-bluedark">{{ $st->full_name }}</span>
                                <span class="text-bluedark/40 font-mono text-[10px]">NIS: {{ $st->nis }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Selected Student Tags Container -->
                <div id="createStudentTags" class="flex flex-wrap items-center gap-1.5 mt-2 min-h-[30px] p-2 bg-slate-50/70 border border-bluelight/60 rounded-xl">
                    <span id="createStudentPlaceholder" class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih (tim umum/sekolah).</span>
                </div>
            </div>

            <!-- Upload Foto Dokumentasi (Drag & Drop Zone + File Picker) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Foto Dokumentasi Prestasi</label>
                    <span class="text-[10px] text-bluedark/40">Maks. 4MB (JPG, JPEG, PNG, WEBP)</span>
                </div>

                <div id="createDropzone" class="ach-dropzone" onclick="document.getElementById('createPhotoInput').click()">
                    <input type="file" id="createPhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleFilePicked('create', this.files)">
                    
                    <!-- Prompt -->
                    <div id="createDropzonePrompt" class="space-y-1.5 py-3">
                        <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Tarik &amp; Lepaskan foto ke sini, atau klik untuk memilih</div>
                        <div class="text-[11px] text-bluedark/50">Foto akan tampil di kartu prestasi beranda dan pratinjau</div>
                    </div>

                    <!-- Live Preview -->
                    <div id="createDropzonePreview" style="display: none;" class="space-y-2">
                        <div class="relative w-full aspect-video max-h-48 rounded-lg overflow-hidden border border-bluelight bg-slate-100">
                            <img id="createPreviewImg" src="" alt="Pratinjau" class="w-full h-full object-cover">
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span id="createFileName" class="text-[11px] text-bluedark/70 font-mono truncate max-w-[220px]"></span>
                            <button type="button" onclick="event.stopPropagation(); removeFile('create')" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span>Hapus Foto</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Singkat -->
            <div>
                <label class="f-label text-xs">Deskripsi / Ulasan Kejuaraan</label>
                <textarea name="description" rows="3" placeholder="Tuliskan catatan penting mengenai pencapaian, inovasi proyek lomba, atau medali yang didapatkan..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <!-- Checkbox Tag Unggulan (Featured) -->
            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-2.5">
                <input type="checkbox" name="is_featured" id="createFeatured" value="1" class="mt-0.5 rounded text-blueprim focus:ring-blueprim">
                <label for="createFeatured" class="cursor-pointer">
                    <span class="block text-xs font-bold text-amber-900">Beri Tag Prestasi Unggulan (Tampil di Beranda Publik)</span>
                    <span class="block text-[11px] text-amber-800/80 mt-0.5">
                        Semua prestasi tetap ditampilkan di direktori publik <code>/prestasi</code>. Prestasi dengan tag Unggulan akan ditampilkan di Beranda utama sekolah.
                    </span>
                </label>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeCreateModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Data Prestasi</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================
     MODAL 2: EDIT PRESTASI (UPDATE)
     ======================================================================== -->
<div id="editAchModal" class="ach-modal-overlay">
    <div class="ach-modal-dialog max-w-2xl max-h-[92vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Edit Data Prestasi</h3>
                    <p class="text-[11px] text-bluedark/50">Perbarui data capaian, peserta, foto, dan status tag unggulan</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold">&times;</button>
        </div>

        <!-- Form Edit -->
        <form id="editAchForm" method="POST" action="" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="sync_students" value="1">

            <!-- Judul Kejuaraan -->
            <div>
                <label class="f-label text-xs">Nama / Judul Kejuaraan <span class="text-rose-500">*</span></label>
                <input type="text" id="editTitle" name="title" required class="f-input text-xs w-full">
            </div>

            <!-- Kategori & Scope -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label text-xs">Kategori Prestasi <span class="text-rose-500">*</span></label>
                    <select id="editCategoryId" name="achievement_category_id" required class="f-select text-xs w-full">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Bidang / Scope <span class="text-rose-500">*</span></label>
                    <select id="editScope" name="scope" required class="f-select text-xs w-full">
                        <option value="VOKASI">Vokasi / Kejuruan</option>
                        <option value="AKADEMIK">Akademik</option>
                        <option value="NON_AKADEMIK">Non-Akademik / Seni &amp; Olahraga</option>
                    </select>
                </div>
            </div>

            <!-- Tingkat, Peringkat/Juara, Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="f-label text-xs">Tingkat Kompetisi <span class="text-rose-500">*</span></label>
                    <select id="editLevel" name="level" required class="f-select text-xs w-full">
                        <option value="KABUPATEN">Kabupaten</option>
                        <option value="PROVINSI">Provinsi</option>
                        <option value="NASIONAL">Nasional</option>
                        <option value="INTERNASIONAL">Internasional</option>
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Juara / Peringkat</label>
                    <input type="text" id="editRank" name="rank" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                    <input type="date" id="editDate" name="achievement_date" required class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Penyelenggara -->
            <div>
                <label class="f-label text-xs">Penyelenggara Kegiatan</label>
                <input type="text" id="editOrganizer" name="organizer" class="f-input text-xs w-full">
            </div>

            <!-- Siswa Peraih Prestasi (Interactive Tagging Selector) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Siswa Peraih Prestasi (Peserta)</label>
                    <span class="text-[10px] text-bluedark/40">Dapat memilih lebih dari 1 siswa</span>
                </div>
                
                <!-- Search & Add Input -->
                <div class="relative">
                    <input type="text" 
                           id="editStudentSearch" 
                           placeholder="Ketik nama atau NIS siswa untuk menambahkan..." 
                           autocomplete="off"
                           class="f-input text-xs w-full pr-8"
                           oninput="filterStudentDropdown('edit')">
                    <span class="absolute right-2.5 top-2.5 text-bluedark/40">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>

                    <!-- Dropdown Results -->
                    <div id="editStudentDropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-bluelight rounded-xl shadow-lg z-30 max-h-48 overflow-y-auto text-xs divide-y divide-slate-100">
                        @foreach($students as $st)
                            <div class="p-2.5 hover:bg-blue-50 cursor-pointer flex items-center justify-between student-option" 
                                 data-id="{{ $st->id }}" 
                                 data-name="{{ $st->full_name }}" 
                                 data-nis="{{ $st->nis }}"
                                 onclick="addStudentTag('edit', {{ $st->id }}, '{{ addslashes($st->full_name) }}', '{{ $st->nis }}')">
                                <span class="font-medium text-bluedark">{{ $st->full_name }}</span>
                                <span class="text-bluedark/40 font-mono text-[10px]">NIS: {{ $st->nis }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Selected Student Tags Container -->
                <div id="editStudentTags" class="flex flex-wrap items-center gap-1.5 mt-2 min-h-[30px] p-2 bg-slate-50/70 border border-bluelight/60 rounded-xl">
                    <span id="editStudentPlaceholder" class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih (tim umum/sekolah).</span>
                </div>
            </div>

            <!-- Upload Foto Dokumentasi (Drag & Drop Zone + File Picker) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Foto Dokumentasi Prestasi</label>
                    <span class="text-[10px] text-bluedark/40">Maks. 4MB (JPG, JPEG, PNG, WEBP)</span>
                </div>

                <!-- Existing Photo Banner if available -->
                <div id="editExistingPhotoBox" style="display: none;" class="mb-2 p-3 bg-slate-50 border border-bluelight rounded-xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img id="editExistingImg" src="" alt="Foto Saat Ini" class="w-12 h-10 object-cover rounded-lg border border-bluelight shrink-0">
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-bluedark block">Foto Dokumentasi Saat Ini</span>
                            <span class="text-[10px] text-bluedark/50 block">Dapat diganti dengan menjatuhkan foto baru di bawah</span>
                        </div>
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-rose-600 hover:text-rose-800 shrink-0 font-medium">
                        <input type="checkbox" name="remove_photo" id="editRemovePhoto" value="1" class="rounded text-rose-600" onchange="toggleRemoveExistingPhoto(this.checked)">
                        <span>Hapus Foto</span>
                    </label>
                </div>

                <div id="editDropzone" class="ach-dropzone" onclick="document.getElementById('editPhotoInput').click()">
                    <input type="file" id="editPhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleFilePicked('edit', this.files)">
                    
                    <!-- Prompt -->
                    <div id="editDropzonePrompt" class="space-y-1.5 py-3">
                        <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Tarik &amp; Lepaskan foto baru ke sini, atau klik untuk memilih</div>
                        <div class="text-[11px] text-bluedark/50">Kosongkan jika tidak ingin mengubah foto</div>
                    </div>

                    <!-- Live Preview -->
                    <div id="editDropzonePreview" style="display: none;" class="space-y-2">
                        <div class="relative w-full aspect-video max-h-48 rounded-lg overflow-hidden border border-bluelight bg-slate-100">
                            <img id="editPreviewImg" src="" alt="Pratinjau Foto Baru" class="w-full h-full object-cover">
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span id="editFileName" class="text-[11px] text-bluedark/70 font-mono truncate max-w-[220px]"></span>
                            <button type="button" onclick="event.stopPropagation(); removeFile('edit')" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span>Batal Ganti</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Singkat -->
            <div>
                <label class="f-label text-xs">Deskripsi / Ulasan Kejuaraan</label>
                <textarea id="editDescription" name="description" rows="3" class="f-textarea text-xs w-full"></textarea>
            </div>

            <!-- Checkbox Tag Unggulan (Featured) -->
            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-2.5">
                <input type="checkbox" name="is_featured" id="editFeatured" value="1" class="mt-0.5 rounded text-blueprim focus:ring-blueprim">
                <label for="editFeatured" class="cursor-pointer">
                    <span class="block text-xs font-bold text-amber-900">Beri Tag Prestasi Unggulan (Tampil di Beranda Publik)</span>
                    <span class="block text-[11px] text-amber-800/80 mt-0.5">
                        Semua prestasi tetap ditampilkan di direktori publik <code>/prestasi</code>. Prestasi dengan tag Unggulan akan ditampilkan di Beranda utama sekolah.
                    </span>
                </label>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================
     MODAL 3: PRATINJAU PRESTASI (PREVIEW TAMPILAN PUBLIK)
     ======================================================================== -->
<!-- ========================================================================
     MODAL 3: PRATINJAU PRESTASI (PREVIEW IDENTIK DENGAN TAMPILAN PUBLIK)
     ======================================================================== -->
<div id="previewAchModal" class="ach-modal-overlay">
    <div class="ach-modal-dialog max-w-2xl max-h-[92vh] w-full">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-3.5 border-b border-bluelight bg-slate-50/90 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-sm text-bluedark">Pratinjau Tampilan Prestasi di Web Publik</h3>
                    <p class="text-[11px] text-bluedark/50">Simulasi tampilan kartu prestasi seperti yang dilihat pengunjung website</p>
                </div>
            </div>

            <!-- Tab Switcher (Daftar Prestasi vs Beranda Home) -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-bluelight">
                <button type="button" id="tabBtnAchList" onclick="switchAchievementPreviewTab('list')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all shadow-xs">
                    Kartu Prestasi
                </button>
                <button type="button" id="tabBtnAchHome" onclick="switchAchievementPreviewTab('home')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all">
                    Kartu Beranda (Home)
                </button>
                <button type="button" onclick="closePreviewModal()"
                    class="ml-2 w-7 h-7 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-100 text-lg font-bold leading-none">&times;</button>
            </div>
        </div>

        <!-- Scrollable Modal Body (Sesuai Layout Publik) -->
        <div class="p-6 sm:p-8 overflow-y-auto bg-slate-50/40">

            <!-- ================================================================
                 TAB 1: KARTU PRESTASI PUBLIK (/prestasi)
                 Identik dengan resources/views/public/achievements/index.blade.php
                 ================================================================ -->
            <div id="achListTab" class="max-w-md mx-auto py-2">
                <p class="text-xs text-center text-bluedark/50 mb-4">Simulasi kartu pada halaman publik (<code class="bg-slate-100 px-1 py-0.5 rounded text-blueprim">/prestasi</code>):</p>
                <article class="flex flex-col bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden h-full hover:shadow-lg transition-all duration-300">
                    <div class="h-48 shrink-0 bg-slate-100 relative overflow-hidden flex items-center justify-center">
                        <img id="prevListPhoto" src="" alt="Prestasi" class="w-full h-full object-cover">
                        <div id="prevListPhotoFallback" class="flex flex-col items-center justify-center text-slate-400 p-4 text-center">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="5"/><path d="M8 13l-2 8 6-3 6 3-2-8"/></svg>
                            <span class="text-[11px] mt-1 font-medium">Foto Prestasi</span>
                        </div>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="prevListCategory" class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                Prestasi
                            </span>
                            <span id="prevListFeaturedBadge" class="text-xs font-heading font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full" style="display: none;">
                                Unggulan
                            </span>
                        </div>

                        <h2 id="prevListTitle" class="font-heading font-semibold text-bluedark mt-3 leading-snug text-base">
                            Judul Prestasi
                        </h2>

                        <p id="prevListSubtitle" class="text-xs text-bluedark/50 mt-2">
                            Tingkat · Cakupan · Tanggal
                        </p>

                        <p id="prevListMeta" class="text-xs text-bluedark/45 mt-1">
                            Juara · Penyelenggara
                        </p>

                        <div id="prevListParticipantsWrap" class="mt-2 text-xs text-bluedark/70 bg-slate-50 px-3 py-2 rounded-xl border border-bluelight/70" style="display: none;">
                            <span class="font-semibold text-bluedark">Siswa Peraih:</span>
                            <span id="prevListParticipants">-</span>
                        </div>

                        <p id="prevListDescription" class="text-sm text-bluedark/60 mt-3 leading-relaxed line-clamp-3">
                            Deskripsi ringkas prestasi...
                        </p>
                    </div>
                </article>
            </div>

            <!-- ================================================================
                 TAB 2: KARTU BERANDA PUBLIK (HOME)
                 Identik dengan resources/views/public/home.blade.php lines 574-589
                 ================================================================ -->
            <div id="achHomeTab" class="hidden max-w-sm mx-auto py-4">
                <p class="text-xs text-center text-bluedark/50 mb-4">Simulasi kartu di bagian Prestasi Unggulan <code class="bg-slate-100 px-1 py-0.5 rounded text-blueprim">Beranda (Home)</code>:</p>
                <div class="bg-white rounded-3xl border border-bluelight p-6 shadow-card hover:shadow-lg transition-all duration-300">
                    <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5 text-[#0D47A1]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="8" r="5"/>
                            <path d="M8 13l-2 8 6-3 6 3-2-8"/>
                        </svg>
                    </div>
                    <p id="prevHomeTitle" class="font-heading font-semibold text-bluedark leading-snug text-base">
                        Judul Prestasi
                    </p>
                    <p id="prevHomeSub" class="text-sm text-bluedark/60 mt-2">
                        Nasional, 2026
                    </p>
                    <p id="prevHomeOrganizer" class="text-xs text-bluedark/45 mt-1">
                        Penyelenggara
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between px-6 py-3.5 border-t border-bluelight bg-slate-50/80 shrink-0">
            <button type="button" id="prevPinActionBtn" onclick="" class="btn btn-outline btn-xs flex items-center gap-1.5 font-semibold text-xs">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span id="prevPinActionText">Beri Tag Unggulan</span>
            </button>
            <div class="flex items-center gap-2">
                <button type="button" id="prevEditBtn" onclick="" class="btn btn-primary btn-xs flex items-center gap-1.5 font-bold text-xs">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit Prestasi</span>
                </button>
                <button type="button" onclick="closePreviewModal()" class="btn btn-outline btn-xs text-xs">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================
     MODAL 4: HAPUS PRESTASI (CONFIRM DELETE)
     ======================================================================== -->
<div id="deleteAchModal" class="ach-modal-overlay">
    <div class="ach-modal-dialog max-w-md">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 shadow-xs">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>

            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Data Prestasi?</h3>
                <p class="text-xs text-bluedark/60 mt-1">
                    Prestasi <strong id="deleteAchTitle" class="text-bluedark"></strong> beserta foto dokumentasi dan catatan peraihnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form id="deleteAchForm" method="POST" action="" class="flex items-center justify-center gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm text-xs px-4">Batal</button>
                <button type="submit" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-5 shadow-xs">Hapus Sekarang</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ==========================================================================
   ACHIEVEMENTS CLIENT LOGIC (CRUD, DROPZONE, PIN TOGGLE, PREVIEW)
   ========================================================================== */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// ==========================================
// DRAG & DROP PHOTO UPLOAD COMPONENT
// ==========================================
function initDropzones() {
    ['create', 'edit'].forEach(prefix => {
        const dropzone = document.getElementById(prefix + 'Dropzone');
        if (!dropzone) return;

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                const input = document.getElementById(prefix + 'PhotoInput');
                input.files = files;
                handleFilePicked(prefix, files);
            }
        }, false);
    });
}

function handleFilePicked(prefix, files) {
    if (!files || files.length === 0) return;
    const file = files[0];

    // Validate size (max 4MB)
    if (file.size > 4 * 1024 * 1024) {
        alert('Ukuran file foto melebihi batas maksimal 4MB.');
        removeFile(prefix);
        return;
    }

    // Validate type
    if (!file.type.match('image.*')) {
        alert('Harap pilih file gambar berformat JPG, JPEG, PNG, atau WEBP.');
        removeFile(prefix);
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById(prefix + 'PreviewImg').src = e.target.result;
        document.getElementById(prefix + 'FileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
        document.getElementById(prefix + 'DropzonePrompt').style.display = 'none';
        document.getElementById(prefix + 'DropzonePreview').style.display = 'block';

        if (prefix === 'edit') {
            const removeBox = document.getElementById('editRemovePhoto');
            if (removeBox) removeBox.checked = false;
        }
    };
    reader.readAsDataURL(file);
}

function removeFile(prefix) {
    const input = document.getElementById(prefix + 'PhotoInput');
    if (input) input.value = '';
    document.getElementById(prefix + 'PreviewImg').src = '';
    document.getElementById(prefix + 'FileName').textContent = '';
    document.getElementById(prefix + 'DropzonePrompt').style.display = 'block';
    document.getElementById(prefix + 'DropzonePreview').style.display = 'none';
}

function toggleRemoveExistingPhoto(checked) {
    if (checked) {
        removeFile('edit');
    }
}

// ==========================================
// STUDENT PARTICIPANTS TAGGER
// ==========================================
const selectedStudents = {
    create: new Map(),
    edit: new Map(),
};

function filterStudentDropdown(prefix) {
    const searchInput = document.getElementById(prefix + 'StudentSearch');
    const dropdown = document.getElementById(prefix + 'StudentDropdown');
    const query = searchInput.value.toLowerCase().trim();

    if (!dropdown) return;

    if (query === '') {
        dropdown.classList.add('hidden');
        return;
    }

    const options = dropdown.querySelectorAll('.student-option');
    let hasMatch = false;

    options.forEach(opt => {
        const id = parseInt(opt.getAttribute('data-id'));
        const name = opt.getAttribute('data-name').toLowerCase();
        const nis = opt.getAttribute('data-nis').toLowerCase();

        // Check if already selected
        if (selectedStudents[prefix].has(id)) {
            opt.classList.add('hidden');
            return;
        }

        if (name.includes(query) || nis.includes(query)) {
            opt.classList.remove('hidden');
            hasMatch = true;
        } else {
            opt.classList.add('hidden');
        }
    });

    if (hasMatch) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function addStudentTag(prefix, id, name, nis) {
    selectedStudents[prefix].set(id, { name, nis });

    // Hide dropdown & clear input
    const dropdown = document.getElementById(prefix + 'StudentDropdown');
    const searchInput = document.getElementById(prefix + 'StudentSearch');
    if (dropdown) dropdown.classList.add('hidden');
    if (searchInput) searchInput.value = '';

    renderStudentTags(prefix);
}

function removeStudentTag(prefix, id) {
    selectedStudents[prefix].delete(id);
    renderStudentTags(prefix);
}

function renderStudentTags(prefix) {
    const container = document.getElementById(prefix + 'StudentTags');
    const placeholder = document.getElementById(prefix + 'StudentPlaceholder');
    if (!container) return;

    // Clear existing tags except placeholder
    container.innerHTML = '';

    if (selectedStudents[prefix].size === 0) {
        if (placeholder) {
            container.appendChild(placeholder);
        } else {
            container.innerHTML = '<span class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih (tim umum/sekolah).</span>';
        }
        return;
    }

    selectedStudents[prefix].forEach((student, id) => {
        const tag = document.createElement('div');
        tag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-bluelight text-bluedark text-xs shadow-2xs animate-in fade-in';
        tag.innerHTML = `
            <input type="hidden" name="student_ids[]" value="${id}">
            <div class="w-4 h-4 rounded-full bg-blue-100 text-blueprim flex items-center justify-center text-[9px] font-bold">
                ${student.name.charAt(0)}
            </div>
            <span class="font-medium">${student.name}</span>
            <span class="text-bluedark/40 font-mono text-[10px]">(${student.nis})</span>
            <button type="button" onclick="removeStudentTag('${prefix}', ${id})" class="text-bluedark/40 hover:text-rose-600 text-sm leading-none ml-1 font-bold">&times;</button>
        `;
        container.appendChild(tag);
    });
}

// Close student dropdown when clicking outside
document.addEventListener('click', (e) => {
    ['create', 'edit'].forEach(prefix => {
        const searchInput = document.getElementById(prefix + 'StudentSearch');
        const dropdown = document.getElementById(prefix + 'StudentDropdown');
        if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
            dropdown.classList.add('hidden');
        }
    });
});

// ==========================================
// MODAL CONTROLS: CREATE
// ==========================================
function openCreateModal() {
    removeFile('create');
    selectedStudents.create.clear();
    renderStudentTags('create');
    document.getElementById('createAchModal').classList.add('show');
}
function closeCreateModal() {
    document.getElementById('createAchModal').classList.remove('show');
}

// ==========================================
// MODAL CONTROLS: EDIT
// ==========================================
function openEditModal(achievementId) {
    removeFile('edit');
    selectedStudents.edit.clear();
    renderStudentTags('edit');

    const form = document.getElementById('editAchForm');
    form.action = `/admin/cms/achievements/${achievementId}`;

    // Fetch achievement data from preview endpoint for accurate hydration
    fetch(`/admin/cms/achievements/${achievementId}/preview`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('editTitle').value = data.title || '';
        document.getElementById('editCategoryId').value = data.category_id || '';
        document.getElementById('editScope').value = data.scope || 'VOKASI';
        document.getElementById('editLevel').value = data.level || 'NASIONAL';
        document.getElementById('editRank').value = data.rank !== '-' ? data.rank : '';
        document.getElementById('editDate').value = data.achievement_date || '';
        document.getElementById('editOrganizer').value = data.organizer !== '-' ? data.organizer : '';
        document.getElementById('editDescription').value = data.description || '';
        document.getElementById('editFeatured').checked = Boolean(data.is_featured);

        // Photo
        const existingPhotoBox = document.getElementById('editExistingPhotoBox');
        const existingImg = document.getElementById('editExistingImg');
        const removePhotoBox = document.getElementById('editRemovePhoto');
        if (removePhotoBox) removePhotoBox.checked = false;

        if (data.photo_url) {
            existingImg.src = data.photo_url;
            existingPhotoBox.style.display = 'flex';
        } else {
            existingPhotoBox.style.display = 'none';
        }

        // Hydrate Students
        if (data.participants && data.participants.length > 0) {
            data.participants.forEach(p => {
                selectedStudents.edit.set(p.id, { name: p.name, nis: p.nis });
            });
        }
        renderStudentTags('edit');

        document.getElementById('editAchModal').classList.add('show');
    })
    .catch(err => {
        console.error(err);
    });
}
function closeEditModal() {
    document.getElementById('editAchModal').classList.remove('show');
}

function switchAchievementPreviewTab(tab) {
    const listTab = document.getElementById('achListTab');
    const homeTab = document.getElementById('achHomeTab');
    const btnList = document.getElementById('tabBtnAchList');
    const btnHome = document.getElementById('tabBtnAchHome');

    [listTab, homeTab].forEach(t => t.classList.add('hidden'));
    const inActiveCls = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    const activeCls = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all shadow-xs';

    btnList.className = inActiveCls;
    btnHome.className = inActiveCls;

    if (tab === 'list') {
        listTab.classList.remove('hidden');
        btnList.className = activeCls;
    } else {
        homeTab.classList.remove('hidden');
        btnHome.className = activeCls;
    }
}

function previewAchievement(achievementId) {
    fetch(`/admin/cms/achievements/${achievementId}/preview`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => res.json())
    .then(data => {
        // Reset tab to list view
        switchAchievementPreviewTab('list');

        // Tab 1: Public Card (/prestasi)
        const listImg = document.getElementById('prevListPhoto');
        const listFallback = document.getElementById('prevListPhotoFallback');
        if (data.photo_url) {
            listImg.src = data.photo_url;
            listImg.style.display = 'block';
            listFallback.style.display = 'none';
        } else {
            listImg.src = '';
            listImg.style.display = 'none';
            listFallback.style.display = 'flex';
        }

        document.getElementById('prevListCategory').textContent = data.category_name || 'Prestasi';
        const listFeat = document.getElementById('prevListFeaturedBadge');
        if (listFeat) {
            listFeat.style.display = data.is_featured ? 'inline-flex' : 'none';
        }
        document.getElementById('prevListTitle').textContent = data.title || 'Judul Prestasi';

        const subItems = [data.level, data.scope, data.achievement_date_formatted || data.achievement_date].filter(Boolean);
        document.getElementById('prevListSubtitle').textContent = subItems.length ? subItems.join(' · ') : '-';

        const metaItems = [
            data.rank && data.rank !== '-' ? 'Juara ' + data.rank : null,
            data.organizer && data.organizer !== '-' ? data.organizer : null
        ].filter(Boolean);
        document.getElementById('prevListMeta').textContent = metaItems.length ? metaItems.join(' · ') : '';

        // Participants in Public Card
        const partWrap = document.getElementById('prevListParticipantsWrap');
        const partText = document.getElementById('prevListParticipants');
        if (data.participants && data.participants.length > 0) {
            partText.textContent = data.participants.map(p => p.name).join(', ');
            partWrap.style.display = 'block';
        } else {
            partWrap.style.display = 'none';
        }

        document.getElementById('prevListDescription').textContent = data.description || 'Tidak ada deskripsi rinci.';

        // Tab 2: Home Page Card (Identik public/home.blade.php)
        document.getElementById('prevHomeTitle').textContent = data.title || 'Judul Prestasi';
        const year = data.achievement_date ? data.achievement_date.substring(0, 4) : '';
        const homeSubItems = [data.level, year].filter(Boolean);
        document.getElementById('prevHomeSub').textContent = homeSubItems.join(', ');
        document.getElementById('prevHomeOrganizer').textContent = data.organizer && data.organizer !== '-' ? data.organizer : '';

        // Preview Actions
        const pinActionBtn = document.getElementById('prevPinActionBtn');
        const pinActionText = document.getElementById('prevPinActionText');
        pinActionText.textContent = data.is_featured ? 'Lepas Tag Unggulan' : 'Beri Tag Unggulan';
        pinActionBtn.onclick = () => {
            togglePinAchievement(data.id, () => {
                closePreviewModal();
            });
        };

        const editBtn = document.getElementById('prevEditBtn');
        editBtn.onclick = () => {
            closePreviewModal();
            openEditModal(data.id);
        };

        document.getElementById('previewAchModal').classList.add('show');
    })
    .catch(err => {
        console.error(err);
    });
}
function closePreviewModal() {
    document.getElementById('previewAchModal').classList.remove('show');
}

// ==========================================
// MODAL CONTROLS: DELETE
// ==========================================
function openDeleteModal(id, title) {
    document.getElementById('deleteAchTitle').textContent = `"${title}"`;
    document.getElementById('deleteAchForm').action = `/admin/cms/achievements/${id}`;
    document.getElementById('deleteAchModal').classList.add('show');
}
function closeDeleteModal() {
    document.getElementById('deleteAchModal').classList.remove('show');
}

// ==========================================
// AJAX PIN TOGGLE (TAG UNGGULAN BERANDA)
// ==========================================
function togglePinAchievement(achievementId, callback = null) {
    const pinBtn = document.getElementById('achPinBtn-' + achievementId);
    const row = document.getElementById('achRow-' + achievementId);
    
    // Disable temporarily
    if (pinBtn) pinBtn.disabled = true;

    fetch(`/admin/cms/achievements/${achievementId}/toggle-pin`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const isFeatured = data.is_featured;
            
            // Update table button style and icon
            if (pinBtn) {
                pinBtn.setAttribute('data-pinned', isFeatured ? '1' : '0');
                pinBtn.title = isFeatured ? 'Lepas Tag Unggulan (Saat ini: Bertag Unggulan)' : 'Beri Tag Unggulan (Tampil di Beranda Publik)';
                if (isFeatured) {
                    pinBtn.className = 'ach-pin-btn w-7 h-7 rounded-lg flex items-center justify-center transition-all text-amber-700 bg-amber-100 hover:bg-amber-200 border border-amber-300';
                    pinBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
                } else {
                    pinBtn.className = 'ach-pin-btn w-7 h-7 rounded-lg flex items-center justify-center transition-all text-slate-400 hover:text-amber-600 hover:bg-white hover:shadow-xs';
                    pinBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
                }
            }

            // Update row tag badge (HANYA tampil jika isFeatured == true / 1)
            if (row) {
                const badge = row.querySelector('.ach-featured-badge');
                if (badge) {
                    badge.style.setProperty('display', isFeatured ? 'inline-flex' : 'none', 'important');
                }
            }

            // Update Stats Counter
            const statFeatured = document.getElementById('statFeaturedCount');
            if (statFeatured) {
                let current = parseInt(statFeatured.textContent.replace(/,/g, '')) || 0;
                current = isFeatured ? current + 1 : Math.max(0, current - 1);
                statFeatured.textContent = current.toLocaleString();
            }

            if (typeof callback === 'function') callback();
        }
    })
    .catch(err => {
        console.error(err);
    })
    .finally(() => {
        if (pinBtn) pinBtn.disabled = false;
    });
}

// Close modals when clicking backdrop overlay
document.querySelectorAll('.ach-modal-overlay').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.ach-modal-overlay.show').forEach(m => m.classList.remove('show'));
    }
});

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initDropzones();
});
</script>
@endpush
@endsection
