@extends('layouts.admin')

@section('title', 'Manajemen Berita & Artikel')

@push('styles')
<style>
/* ==========================================================================
   STYLES FOR CMS ARTICLES INDEX
   ========================================================================== */

/* Toggle Switch On/Off */
.art-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.art-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.art-switch-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #CBD5E1;
    transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 24px;
}
.art-switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.art-switch input:checked + .art-switch-slider {
    background-color: #10B981;
}
.art-switch input:checked + .art-switch-slider:before {
    transform: translateX(20px);
}

/* Modal Backdrop & Dialog */
.art-modal-overlay {
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
.art-modal-overlay.show,
.art-modal-overlay.active {
    display: flex !important;
}
.art-modal-dialog {
    background: #FFFFFF;
    border-radius: 1.5rem;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: 1px solid #E2E8F0;
    margin: auto;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: artModalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes artModalFadeIn {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Public Card Animation Replica */
.berita-card, .link-card {
    transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.25s ease;
    border-radius: 1.25rem;
    background: #FFFFFF;
    border: 1px solid #E3F2FD;
    overflow: hidden;
}
.berita-card:hover, .berita-card:active, .link-card:hover, .link-card:active {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px -8px rgba(13, 71, 161, 0.16);
}
.berita-card .card-thumb-wrap, .link-card .card-thumb-wrap {
    overflow: hidden;
    position: relative;
    aspect-ratio: 16 / 10;
}
.berita-card .card-thumb-wrap img, .link-card .card-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s cubic-bezier(0.2, 0, 0, 1);
}
.berita-card:hover .card-thumb-wrap img,
.berita-card:active .card-thumb-wrap img,
.link-card:hover .card-thumb-wrap img,
.link-card:active .card-thumb-wrap img {
    transform: scale(1.08);
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-bluelight/60">
        <div>
            <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-1">
                <span>CMS Sekolah</span>
                <span>/</span>
                <span class="text-blueprim font-semibold">Berita &amp; Artikel</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold text-bluedark flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blueprim/10 text-blueprim grid place-items-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                        <path d="M18 14h-8"/>
                        <path d="M15 18h-5"/>
                        <path d="M10 6h8v4h-8V6Z"/>
                    </svg>
                </span>
                <span>Manajemen Berita &amp; Artikel</span>
            </h1>
            <p class="text-xs sm:text-sm text-bluedark/70 mt-0.5">
                Kelola konten publikasi, pengumuman, dan artikel prestasi untuk portal informasi publik SMK.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('public.articles.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-1.5 text-xs text-blueprim border-bluelight hover:border-blueprim transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Lihat Web Publik</span>
            </a>
            <a href="{{ route('admin.cms.articles.create') }}" class="btn btn-primary btn-sm flex items-center gap-2 text-xs shadow-xs hover:shadow-md transition-all font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tulis Artikel Baru</span>
            </a>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="panel p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blueprim flex items-center justify-center font-bold text-sm shrink-0 border border-blue-100">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <div>
                <div class="text-[11px] text-bluedark/60 font-medium">Total Artikel</div>
                <div class="text-xl font-heading font-bold text-bluedark">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0 border border-emerald-100">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="text-[11px] text-bluedark/60 font-medium">Terbit (Online)</div>
                <div class="text-xl font-heading font-bold text-emerald-600">{{ $stats['published'] }}</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-200">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div>
                <div class="text-[11px] text-bluedark/60 font-medium">Draf (Offline)</div>
                <div class="text-xl font-heading font-bold text-slate-700">{{ $stats['draft'] }}</div>
            </div>
        </div>

        <div class="panel p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-100">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div>
                <div class="text-[11px] text-bluedark/60 font-medium">Total Pembaca</div>
                <div class="text-xl font-heading font-bold text-amber-600">{{ number_format($stats['views']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Artikel -->
    <div class="panel p-4 sm:p-5 border border-bluelight bg-white shadow-2xs space-y-3.5">
        <div class="flex items-center justify-between gap-3 border-b border-bluelight/70 pb-3 flex-wrap">
            <div class="flex items-center gap-2 text-xs font-semibold text-bluedark">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span>Filter &amp; Pencarian Data Artikel</span>
                @if($search || $categoryId || $status)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blueprim/10 text-blueprim">
                        Filter Aktif
                    </span>
                @endif
            </div>

            <!-- Total count badge -->
            <div class="flex items-center gap-1.5 text-xs text-bluedark/60 bg-[#F7FBFF] px-3 py-1 rounded-xl border border-bluelight/80">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                <span>Menampilkan <strong>{{ $articles->firstItem() ?? 0 }}&ndash;{{ $articles->lastItem() ?? 0 }}</strong> dari <strong>{{ $articles->total() }}</strong> artikel</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.cms.articles') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search input -->
                <div class="lg:col-span-5 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul, slug, kata kunci..." class="f-input pl-9 {{ $search ? 'pr-8' : '' }} text-xs w-full py-2.5">
                    @if($search)
                        <a href="{{ route('admin.cms.articles', array_filter(['category_id' => $categoryId, 'status' => $status])) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-bluedark/40 hover:text-bluedark" title="Hapus teks pencarian">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </a>
                    @endif
                </div>

                <!-- Category dropdown -->
                <div class="lg:col-span-3">
                    <select name="category_id" class="f-select text-xs py-2.5 w-full" onchange="this.form.submit()">
                        <option value="">Semua Kategori ({{ $categories->count() }})</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status dropdown -->
                <div class="lg:col-span-2">
                    <select name="status" class="f-select text-xs py-2.5 w-full" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="PUBLISHED" {{ $status === 'PUBLISHED' ? 'selected' : '' }}>Terbit (Online)</option>
                        <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>Draf (Offline)</option>
                    </select>
                </div>

                <!-- Action buttons -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1 flex items-center justify-center gap-1.5 py-2.5 text-xs font-semibold">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>Filter</span>
                    </button>

                    @if($search || $categoryId || $status)
                        <a href="{{ route('admin.cms.articles') }}" class="btn btn-outline btn-sm py-2.5 px-3 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-200 flex items-center gap-1" title="Reset semua filter">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Filter Chips -->
            @if($search || $categoryId || $status)
                <div class="flex items-center gap-2 pt-2 border-t border-bluelight/60 flex-wrap text-xs">
                    <span class="text-[11px] font-medium text-bluedark/50">Filter aktif:</span>
                    
                    @if($search)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blueprim text-[11px]">
                            <span>Pencarian: "<strong>{{ $search }}</strong>"</span>
                            <a href="{{ route('admin.cms.articles', array_filter(['category_id' => $categoryId, 'status' => $status])) }}" class="text-blueprim/70 hover:text-blueprim font-bold leading-none">&times;</a>
                        </span>
                    @endif

                    @if($categoryId)
                        @php $activeCat = $categories->firstWhere('id', $categoryId); @endphp
                        @if($activeCat)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-[11px]">
                                <span>Kategori: <strong>{{ $activeCat->name }}</strong></span>
                                <a href="{{ route('admin.cms.articles', array_filter(['search' => $search, 'status' => $status])) }}" class="text-indigo-500 hover:text-indigo-800 font-bold leading-none">&times;</a>
                            </span>
                        @endif
                    @endif

                    @if($status)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $status === 'PUBLISHED' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-100 border-slate-300 text-slate-700' }} text-[11px]">
                            <span>Status: <strong>{{ $status === 'PUBLISHED' ? 'Terbit (Online)' : 'Draf (Offline)' }}</strong></span>
                            <a href="{{ route('admin.cms.articles', array_filter(['search' => $search, 'category_id' => $categoryId])) }}" class="hover:text-bluedark font-bold leading-none">&times;</a>
                        </span>
                    @endif

                    <a href="{{ route('admin.cms.articles') }}" class="text-[11px] text-rose-600 hover:underline ml-1">
                        Hapus Semua Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Table Berita & Artikel -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th class="w-20">Foto</th>
                        <th>Judul &amp; Tautan</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Tanggal Terbit</th>
                        <th class="text-center">Views</th>
                        <th class="w-28 text-center">Status</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $idx => $art)
                        @php
                            $mediaUrl = app(\App\Services\PublicMediaService::class)->forModel($art, 'thumbnail');
                            $isPublished = $art->status === \App\Enums\ContentStatus::Published;
                        @endphp
                        <tr id="articleRow{{ $art->id }}" class="hover:bg-blue-50/30 transition-colors">
                            <td class="text-center font-mono text-xs text-bluedark/50">{{ $articles->firstItem() + $idx }}</td>
                            <td>
                                <div class="w-16 h-12 rounded-lg bg-bluelight/60 border border-bluelight overflow-hidden shrink-0 relative group cursor-pointer" onclick="openPreviewModal({{ $art->id }})" title="Klik untuk pratinjau">
                                    @if($mediaUrl)
                                        <img src="{{ $mediaUrl }}" alt="{{ $art->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-blueprim/40 bg-blue-50/50">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-bluedark text-sm line-clamp-2">{{ $art->title }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-bluedark/40 font-mono">/berita/{{ $art->slug }}</span>
                                    @if($isPublished)
                                        <a href="{{ route('public.articles.show', $art) }}" target="_blank" class="text-[10px] text-blueprim hover:underline inline-flex items-center gap-0.5">
                                            <span>Buka</span>
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue text-[11px] px-2.5 py-0.5 rounded-full font-medium">
                                    {{ $art->category?->name ?? 'Informasi' }}
                                </span>
                            </td>
                            <td class="text-xs text-bluedark/70">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-blueprim/10 text-blueprim text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($art->author?->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="truncate max-w-[110px]">{{ $art->author?->name ?? 'Admin Sekolah' }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-bluedark/60 font-mono" id="publishedAtCell{{ $art->id }}">
                                {{ $art->published_at ? $art->published_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="text-center font-mono text-xs font-semibold text-bluedark/70">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                    {{ number_format($art->views) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <label class="art-switch cursor-pointer" title="{{ $isPublished ? 'Klik untuk ubah ke draf' : 'Klik untuk terbitkan' }}">
                                        <input type="checkbox" id="toggleSwitch{{ $art->id }}" {{ $isPublished ? 'checked' : '' }} onchange="toggleArticleStatus({{ $art->id }})">
                                        <span class="art-switch-slider"></span>
                                    </label>
                                    <span class="text-[10px] font-semibold" id="statusBadge{{ $art->id }}">
                                        @if($isPublished)
                                            <span class="text-emerald-600">Terbit</span>
                                        @else
                                            <span class="text-slate-500">Draf</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Tombol Pratinjau Publik -->
                                    <button type="button" onclick="openPreviewModal({{ $art->id }})" class="p-1.5 rounded-lg text-blueprim hover:bg-blue-50 transition-colors" title="Pratinjau Tampilan Publik">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    <!-- Tombol Edit (Halaman Tersendiri) -->
                                    <a href="{{ route('admin.cms.articles.edit', $art) }}" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors inline-flex items-center" title="Edit Artikel">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <button type="button" onclick="openDeleteModal({{ $art->id }}, '{{ addslashes($art->title) }}')" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Artikel">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-xs text-bluedark/40">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                    <div class="font-semibold text-bluedark">Belum ada artikel</div>
                                    <p class="text-[11px] text-bluedark/50">Mulai buat artikel berita dan pengumuman untuk website sekolah.</p>
                                    <a href="{{ route('admin.cms.articles.create') }}" class="btn btn-primary btn-sm text-xs mt-2 inline-flex items-center gap-1.5">
                                        <span>Tulis Artikel Baru</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Langsung dari Laravel (10 Data Per Halaman) -->
        <div class="mt-4 pt-4 border-t border-bluelight/80 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-bluedark/60">
                Menampilkan <strong>{{ $articles->firstItem() ?? 0 }}&ndash;{{ $articles->lastItem() ?? 0 }}</strong> dari total <strong>{{ $articles->total() }}</strong> artikel &bull; Halaman <strong>{{ $articles->currentPage() }}</strong> dari <strong>{{ $articles->lastPage() }}</strong> (10 data per halaman)
            </span>
            <div class="pagination-wrapper text-xs">
                {{ $articles->links() }}
            </div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: PRATINJAU TAMPILAN PUBLIK                                          -->
<!-- ========================================================================= -->
<div id="previewModal" class="hidden art-modal-overlay">
    <div class="art-modal-dialog max-w-4xl w-full">
        <!-- Header Pratinjau -->
        <div class="px-6 py-4 border-b border-bluelight bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark">Pratinjau Tampilan Website Publik</h3>
                    <p class="text-xs text-bluedark/60">Simulasi tampilan artikel seperti yang dilihat pengunjung website</p>
                </div>
            </div>

            <!-- Tab Switcher (Kartu vs Halaman Penuh) -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-bluelight">
                <button type="button" id="tabBtnCard" onclick="switchPreviewTab('card')" class="px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all">
                    Kartu Berita
                </button>
                <button type="button" id="tabBtnFull" onclick="switchPreviewTab('full')" class="px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all">
                    Halaman Penuh
                </button>
                <button type="button" onclick="closePreviewModal()" class="ml-2 text-bluedark/40 hover:text-bluedark text-xl font-bold leading-none p-1">&times;</button>
            </div>
        </div>

        <!-- Body Pratinjau -->
        <div class="p-6 overflow-y-auto max-h-[75vh] bg-slate-100/50">

            <!-- TAB 1: KARTU BERITA PUBLIK -->
            <div id="previewCardTab" class="max-w-md mx-auto py-6">
                <div class="text-xs text-center text-bluedark/50 mb-3">Arahkan kursor / tahan pada kartu di bawah untuk melihat animasi zoom gambar:</div>
                
                <div class="berita-card cursor-pointer">
                    <div class="card-thumb-wrap bg-blue-50">
                        <img id="prevCardImg" src="" alt="Sampul">
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between text-[11px] text-bluedark/60">
                            <span class="badge badge-blue text-[10px] px-2.5 py-0.5 rounded-full font-bold" id="prevCardCategory">
                                Informasi
                            </span>
                            <span id="prevCardDate">-</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-bluedark line-clamp-2 leading-snug" id="prevCardTitle">
                            Judul Artikel
                        </h4>
                        <p class="text-xs text-bluedark/70 line-clamp-3 leading-relaxed" id="prevCardExcerpt">
                            Ringkasan singkat...
                        </p>
                        <div class="pt-2 border-t border-bluelight flex items-center justify-between text-xs text-bluedark/60">
                            <span class="flex items-center gap-1.5 font-medium">
                                <span class="w-5 h-5 rounded-full bg-blueprim text-white text-[10px] font-bold flex items-center justify-center" id="prevCardAuthorInitial">
                                    A
                                </span>
                                <span id="prevCardAuthor">Admin</span>
                            </span>
                            <span class="text-blueprim font-semibold flex items-center gap-1">
                                Baca Selengkapnya &rarr;
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: HALAMAN PENUH -->
            <div id="previewFullTab" class="hidden max-w-2xl mx-auto bg-white p-6 sm:p-10 rounded-2xl border border-bluelight shadow-sm">
                <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-3">
                    <span class="badge badge-blue text-xs px-3 py-1 rounded-full font-bold" id="prevFullCategory">Informasi</span>
                    <span>&bull;</span>
                    <span id="prevFullDate">-</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-heading font-bold text-bluedark leading-tight mb-4" id="prevFullTitle">
                    Judul Artikel Lengkap
                </h1>

                <div class="flex items-center gap-3 pb-6 mb-6 border-b border-bluelight text-xs text-bluedark/70">
                    <div class="w-9 h-9 rounded-full bg-blueprim text-white font-bold flex items-center justify-center shrink-0" id="prevFullAuthorInitial">
                        A
                    </div>
                    <div>
                        <div class="font-bold text-bluedark" id="prevFullAuthor">Admin Sekolah</div>
                        <div class="text-[11px] text-bluedark/50">Penulis Berita &amp; Humas SMKN 2 Karanganyar</div>
                    </div>
                </div>

                <div class="rounded-2xl overflow-hidden border border-bluelight mb-6 bg-slate-100 aspect-video">
                    <img id="prevFullImg" src="" alt="Sampul Artikel" class="w-full h-full object-cover">
                </div>

                <div class="p-4 rounded-xl bg-blue-50/60 border-l-4 border-blueprim text-bluedark/80 text-sm font-medium leading-relaxed mb-6" id="prevFullExcerpt">
                    Ringkasan singkat cuplikan artikel...
                </div>

                <div class="prose-article leading-relaxed text-bluedark/85" id="prevFullContent">
                    Konten artikel lengkap...
                </div>
            </div>

        </div>

        <!-- Footer Pratinjau -->
        <div class="px-6 py-3 border-t border-bluelight bg-white flex items-center justify-between">
            <span class="text-xs text-bluedark/50" id="prevStatusText">Status: -</span>
            <button type="button" onclick="closePreviewModal()" class="btn btn-primary btn-sm text-xs py-1.5 px-4 font-semibold">
                Tutup Pratinjau
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: KONFIRMASI HAPUS ARTIKEL                                           -->
<!-- ========================================================================= -->
<div id="deleteArticleModal" class="hidden art-modal-overlay">
    <div class="art-modal-dialog max-w-md w-full">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            
            <div class="space-y-1.5">
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Artikel Ini?</h3>
                <p class="text-xs text-bluedark/60 leading-relaxed">
                    Artikel "<strong id="deleteArticleTitle" class="text-bluedark"></strong>" beserta file gambar sampulnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form id="deleteArticleForm" method="POST" action="">
                @csrf
                @method('DELETE')
                
                <div class="flex items-center justify-center gap-3 pt-3">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-outline text-xs py-2 px-4 text-bluedark/70 hover:text-bluedark">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger text-xs py-2 px-4 font-semibold shadow-xs">
                        Ya, Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ==========================================================================
   AJAX TOGGLE STATUS (PUBLISHED / DRAFT)
   ========================================================================== */
function toggleArticleStatus(articleId) {
    const checkbox = document.getElementById('toggleSwitch' + articleId);
    const badge = document.getElementById('statusBadge' + articleId);
    const publishedAtCell = document.getElementById('publishedAtCell' + articleId);
    const previousState = !checkbox.checked;

    checkbox.disabled = true;

    fetch('{{ url("admin/cms/articles") }}/' + articleId + '/toggle-status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Network error: ' + res.status);
        return res.json();
    })
    .then(data => {
        checkbox.disabled = false;
        if (data.success) {
            checkbox.checked = data.is_published;
            if (data.is_published) {
                badge.innerHTML = '<span class="text-emerald-600">Terbit</span>';
            } else {
                badge.innerHTML = '<span class="text-slate-500">Draf</span>';
            }
            if (data.published_at && publishedAtCell) {
                publishedAtCell.innerText = data.published_at;
            }
        } else {
            checkbox.checked = previousState;
            alert(data.message || 'Gagal mengubah status artikel.');
        }
    })
    .catch(err => {
        checkbox.disabled = false;
        checkbox.checked = previousState;
        console.error('Error toggling article status:', err);
        alert('Terjadi kesalahan jaringan saat memperbarui status artikel.');
    });
}

/* ==========================================================================
   PUBLIC PREVIEW MODAL
   ========================================================================== */
function openPreviewModal(articleId) {
    const modal = document.getElementById('previewModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');

    fetch('{{ url("admin/cms/articles") }}/' + articleId + '/preview', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const defaultThumb = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='250' viewBox='0 0 400 250'%3E%3Crect width='400' height='250' fill='%23E3F2FD'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%231565C0'%3EBelum Ada Foto Sampul%3C/text%3E%3C/svg%3E";
        const thumb = data.thumbnail_url || defaultThumb;

        // Card view
        document.getElementById('prevCardImg').src = thumb;
        document.getElementById('prevCardTitle').innerText = data.title;
        document.getElementById('prevCardCategory').innerText = data.category_name;
        document.getElementById('prevCardExcerpt').innerText = data.excerpt || (data.content ? data.content.substring(0, 150) + '...' : '-');
        document.getElementById('prevCardDate').innerText = data.published_at || 'Draf';
        document.getElementById('prevCardAuthor').innerText = data.author_name;
        document.getElementById('prevCardAuthorInitial').innerText = (data.author_name || 'A').substring(0, 1).toUpperCase();

        // Full page view
        document.getElementById('prevFullImg').src = thumb;
        document.getElementById('prevFullTitle').innerText = data.title;
        document.getElementById('prevFullCategory').innerText = data.category_name;
        document.getElementById('prevFullDate').innerText = data.published_at || 'Draf';
        document.getElementById('prevFullAuthor').innerText = data.author_name;
        document.getElementById('prevFullAuthorInitial').innerText = (data.author_name || 'A').substring(0, 1).toUpperCase();
        document.getElementById('prevFullExcerpt').innerText = data.excerpt || '';
        document.getElementById('prevFullContent').innerHTML = data.content;

        document.getElementById('prevStatusText').innerText = 'Status: ' + (data.is_published ? 'Terbit (Online)' : 'Draf (Offline)') + ' | ' + data.views + ' pembaca';
    })
    .catch(err => {
        console.error('Error fetching preview data:', err);
    });
}

function closePreviewModal() {
    const modal = document.getElementById('previewModal');
    modal.classList.add('hidden');
    modal.classList.remove('show');
}

function switchPreviewTab(tab) {
    const cardTab = document.getElementById('previewCardTab');
    const fullTab = document.getElementById('previewFullTab');
    const btnCard = document.getElementById('tabBtnCard');
    const btnFull = document.getElementById('tabBtnFull');

    if (tab === 'card') {
        cardTab.classList.remove('hidden');
        fullTab.classList.add('hidden');
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all';
        btnFull.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    } else {
        cardTab.classList.add('hidden');
        fullTab.classList.remove('hidden');
        btnFull.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all';
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    }
}

/* ==========================================================================
   DELETE CONFIRMATION MODAL
   ========================================================================== */
function openDeleteModal(articleId, articleTitle) {
    document.getElementById('deleteArticleTitle').innerText = articleTitle;
    document.getElementById('deleteArticleForm').action = '{{ url("admin/cms/articles") }}/' + articleId;
    const modal = document.getElementById('deleteArticleModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteArticleModal');
    modal.classList.add('hidden');
    modal.classList.remove('show');
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    const prevModal = document.getElementById('previewModal');
    const delModal = document.getElementById('deleteArticleModal');
    if (e.target === prevModal) {
        closePreviewModal();
    }
    if (e.target === delModal) {
        closeDeleteModal();
    }
});
</script>
@endpush
