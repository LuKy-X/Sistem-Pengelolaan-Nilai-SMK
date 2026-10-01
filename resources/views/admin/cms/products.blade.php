@extends('layouts.admin')

@section('title', 'Katalog & Pengelolaan Produk Siswa')

@push('styles')
<style>
/* ==========================================================================
   PRODUCTS CMS STYLING & CLEAN AESTHETICS
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
.prd-dropzone {
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
.prd-dropzone:hover,
.prd-dropzone.dragover {
    border-color: #2563EB;
    background: #EFF6FF;
    transform: scale(1.005);
}

/* Neutral Blur Modal Backdrop (No Blue Overlay!) */
.prd-modal-overlay {
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
.prd-modal-overlay.show {
    display: flex !important;
}
.prd-modal-dialog {
    background: #FFFFFF;
    border-radius: 1.5rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #E2E8F0;
    margin: auto;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: prdModalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes prdModalFadeIn {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Public Card Replica for Preview */
.prd-preview-card {
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
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Katalog Produk &amp; Jasa Kreatif Siswa</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    TEFA &amp; Vokasi
                </span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola karya inovasi, produk Teaching Factory, status ketersediaan, estimasi harga, dan tim siswa pembuat.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('public.products.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2 text-xs font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Lihat di Web</span>
            </a>
            <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Status -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3 text-sm shadow-xs animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600 shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-lg leading-none font-bold">&times;</button>
        </div>
    @endif

    <!-- Alert Validation Errors -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600 shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-5 space-y-0.5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Quick Stats Cards (5 Card Metrics) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Card 1: Total Produk -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-blue-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blueprim flex items-center justify-center shrink-0 border border-blue-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Total Produk</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Karya TEFA siswa</div>
            </div>
        </div>

        <!-- Card 2: Tersedia (Ready) -->
        <div class="panel p-4 flex items-center gap-3.5 bg-emerald-50/40 border border-emerald-200/80 shadow-xs hover:border-emerald-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-emerald-800/80 uppercase tracking-wider">Tersedia</div>
                <div class="font-heading font-extrabold text-2xl text-emerald-900 leading-tight mt-0.5">{{ number_format($stats['available'] ?? 0) }}</div>
                <div class="text-[10px] text-emerald-700/70 mt-0.5 truncate">Siap dipesan publik</div>
            </div>
        </div>

        <!-- Card 3: Pre-Order -->
        <div class="panel p-4 flex items-center gap-3.5 bg-amber-50/40 border border-amber-200/80 shadow-xs hover:border-amber-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-amber-800/80 uppercase tracking-wider">Pre-Order</div>
                <div class="font-heading font-extrabold text-2xl text-amber-900 leading-tight mt-0.5">{{ number_format($stats['pre_order'] ?? 0) }}</div>
                <div class="text-[10px] text-amber-700/70 mt-0.5 truncate">Sistem pesan dahulu</div>
            </div>
        </div>

        <!-- Card 4: Stok Habis -->
        <div class="panel p-4 flex items-center gap-3.5 bg-slate-50/50 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-slate-700/80 uppercase tracking-wider">Stok Habis</div>
                <div class="font-heading font-extrabold text-2xl text-slate-800 leading-tight mt-0.5">{{ number_format($stats['out_of_stock'] ?? 0) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5 truncate">Perlu restock</div>
            </div>
        </div>

        <!-- Card 5: Kategori -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-indigo-300 transition-colors col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Kategori</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['categories'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Grup produk TEFA</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="panel p-4 bg-white border border-bluelight/70 shadow-xs">
        <form method="GET" action="{{ route('admin.cms.products') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
            
            <!-- Search Keyword -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk, kontak, deskripsi, atau siswa pembuat..." class="f-input pl-9 text-xs w-full">
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

            <!-- Filter Jurusan -->
            <div class="w-full sm:w-48">
                <select name="department_id" class="f-select text-xs w-full">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string)$departmentId === (string)$dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Ketersediaan -->
            <div class="w-full sm:w-40">
                <select name="status" class="f-select text-xs w-full">
                    <option value="">Semua Status</option>
                    <option value="AVAILABLE" {{ $status === 'AVAILABLE' ? 'selected' : '' }}>Tersedia (Ready)</option>
                    <option value="PRE_ORDER" {{ $status === 'PRE_ORDER' ? 'selected' : '' }}>Pre-Order</option>
                    <option value="OUT_OF_STOCK" {{ $status === 'OUT_OF_STOCK' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="btn btn-primary btn-sm flex items-center justify-center gap-1.5 text-xs font-semibold px-4">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                </button>
                @if($search || $categoryId || $departmentId || $status)
                    <a href="{{ route('admin.cms.products') }}" class="btn btn-outline btn-sm text-xs font-semibold text-bluedark/70 hover:text-bluedark" title="Reset Filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    </a>
                @endif
            </div>
        </form>

        <!-- Active Filter Badges -->
        @if($search || $categoryId || $departmentId || $status)
            <div class="flex flex-wrap items-center gap-1.5 mt-3 pt-3 border-t border-bluelight/60 text-xs">
                <span class="text-bluedark/50 text-[11px] font-medium mr-1">Filter Aktif:</span>
                @if($search)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px]">
                        Kata Kunci: "{{ $search }}"
                    </span>
                @endif
                @if($categoryId)
                    @php $activeCat = $categories->firstWhere('id', $categoryId); @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                        Kategori: {{ $activeCat?->name ?? $categoryId }}
                    </span>
                @endif
                @if($departmentId)
                    @php $activeDept = $departments->firstWhere('id', $departmentId); @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200 text-[11px]">
                        Jurusan: {{ $activeDept?->name ?? $departmentId }}
                    </span>
                @endif
                @if($status)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-[11px]">
                        Status: {{ $status === 'AVAILABLE' ? 'Tersedia' : ($status === 'PRE_ORDER' ? 'Pre-Order' : 'Stok Habis') }}
                    </span>
                @endif
                <a href="{{ route('admin.cms.products') }}" class="text-[11px] text-rose-600 hover:underline font-semibold ml-2">Hapus Semua Filter</a>
            </div>
        @endif
    </div>

    <!-- Data Table Panel -->
    <div class="panel p-5 bg-white border border-bluelight/70 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Katalog Produk</h2>
                <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-bluedark/60 font-semibold font-mono">
                    {{ $products->total() }} Data
                </span>
            </div>
            <span class="text-xs text-bluedark/50">Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() ?: 1 }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-bluelight bg-slate-50/60">
                        <th class="w-12 py-3 px-3">No</th>
                        <th class="w-16 py-3 px-2 text-center">Foto</th>
                        <th class="py-3 px-3">Nama Produk &amp; Slug</th>
                        <th class="py-3 px-3">Kategori &amp; Jurusan</th>
                        <th class="py-3 px-3">Estimasi Harga</th>
                        <th class="py-3 px-3">Siswa Pembuat / Tim</th>
                        <th class="py-3 px-3">Kontak Pemesanan</th>
                        <th class="w-28 py-3 px-3 text-center">Status</th>
                        <th class="w-32 py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $idx => $p)
                        @php
                            $mediaService = app(\App\Services\PublicMediaService::class);
                            $photoUrl = $mediaService->forModel($p);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors" id="product-row-{{ $p->id }}">
                            <!-- No -->
                            <td class="py-3.5 px-3 text-bluedark/60 font-mono">{{ $products->firstItem() + $idx }}</td>

                            <!-- Foto Thumbnail -->
                            <td class="py-3.5 px-2 text-center">
                                @if($photoUrl)
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-bluelight mx-auto cursor-pointer hover:opacity-90 shadow-2xs" 
                                         onclick="previewProduct({{ $p->id }})" title="Klik untuk pratinjau">
                                        <img src="{{ $photoUrl }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 mx-auto flex items-center justify-center text-slate-400">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                @endif
                            </td>

                            <!-- Nama Produk & Slug -->
                            <td class="py-3.5 px-3">
                                <div class="font-heading font-semibold text-bluedark text-[13px] leading-snug">
                                    {{ $p->name }}
                                </div>
                                <div class="font-mono text-[10.5px] text-bluedark/40 mt-0.5 truncate max-w-[200px]" title="{{ $p->slug }}">
                                    /{{ $p->slug }}
                                </div>
                            </td>

                            <!-- Kategori & Jurusan -->
                            <td class="py-3.5 px-3">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-blue-50 text-blueprim border border-blue-100">
                                        {{ $p->category?->name ?? 'Kategori Umum' }}
                                    </span>
                                    <span class="text-[11px] text-bluedark/60 font-medium">
                                        {{ $p->department?->name ?? 'Lintas Jurusan' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Estimasi Harga -->
                            <td class="py-3.5 px-3">
                                @if($p->price !== null)
                                    <div class="font-heading font-bold text-emerald-700 text-xs">
                                        Rp {{ number_format((float) $p->price, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="text-bluedark/40 italic">Hubungi Penjual</span>
                                @endif
                            </td>

                            <!-- Siswa Pembuat / Tim -->
                            <td class="py-3.5 px-3">
                                @if($p->students->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach($p->students->take(2) as $st)
                                            <div class="flex items-center gap-1.5 text-[11px] text-bluedark font-medium">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blueprim flex items-center justify-center text-[9px] font-bold">
                                                    {{ substr($st->full_name, 0, 1) }}
                                                </div>
                                                <span class="truncate max-w-[140px]">{{ $st->full_name }}</span>
                                            </div>
                                        @endforeach
                                        @if($p->students->count() > 2)
                                            <span class="text-[10px] text-blueprim font-semibold">
                                                +{{ $p->students->count() - 2 }} siswa lainnya
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-bluedark/40 italic">Tim Vokasi / Umum</span>
                                @endif
                            </td>

                            <!-- Kontak Pemesanan -->
                            <td class="py-3.5 px-3">
                                @if(filled($p->contact))
                                    <div class="flex items-center gap-1.5 text-bluedark/80 text-[11px]">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600 shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <span class="truncate max-w-[130px]" title="{{ $p->contact }}">{{ $p->contact }}</span>
                                    </div>
                                @else
                                    <span class="text-bluedark/40 italic">-</span>
                                @endif
                            </td>

                            <!-- Status Ketersediaan -->
                            <td class="py-3.5 px-3 text-center">
                                @if($p->status === 'AVAILABLE')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold text-[10.5px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @elseif($p->status === 'PRE_ORDER')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-semibold text-[10.5px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pre-Order
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-[10.5px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Stok Habis
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3.5 px-3 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-bluelight/70 shadow-2xs">
                                    <!-- Pratinjau -->
                                    <button type="button" 
                                            onclick="previewProduct({{ $p->id }})" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blueprim hover:bg-white hover:shadow-xs transition-all" 
                                            title="Pratinjau Produk">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    <!-- Edit -->
                                    <button type="button" 
                                            onclick="openEditModal({{ $p->id }})" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-white hover:shadow-xs transition-all" 
                                            title="Edit Produk">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button" 
                                            onclick="openDeleteModal({{ $p->id }}, '{{ addslashes($p->name) }}')" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all" 
                                            title="Hapus Produk">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-bluedark/40">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-bluedark/30">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <div class="font-heading font-semibold text-bluedark text-sm">Tidak Ada Katalog Produk</div>
                                    <p class="text-xs text-bluedark/50">
                                        @if($search || $categoryId || $departmentId || $status)
                                            Tidak ditemukan produk siswa yang cocok dengan filter.
                                        @else
                                            Belum ada data produk siswa yang didaftarkan. Klik tombol di atas untuk mendaftarkan karya baru.
                                        @endif
                                    </p>
                                    @if($search || $categoryId || $departmentId || $status)
                                        <a href="{{ route('admin.cms.products') }}" class="btn btn-outline btn-xs mt-2 inline-flex items-center gap-1">
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

        <!-- Pagination Laravel Server-side (10 data per halaman) -->
        <div class="mt-5 pt-3 border-t border-bluelight/60">
            {{ $products->links() }}
        </div>
    </div>

</div>

<!-- ========================================================================
     MODAL 1: TAMBAH PRODUK BARU (CREATE)
     ======================================================================== -->
<div id="createProductModal" class="prd-modal-overlay">
    <div class="prd-modal-dialog max-w-2xl max-h-[92vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Daftarkan Produk Siswa Baru</h3>
                    <p class="text-[11px] text-bluedark/50">Lengkapi informasi produk Teaching Factory / karya vokasi kejuruan</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold">&times;</button>
        </div>

        <!-- Form Tambah -->
        <form method="POST" action="{{ route('admin.cms.products.store') }}" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Nama Produk -->
            <div>
                <label class="f-label text-xs">Nama Produk / Jasa Vokasi <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Aplikasi POS Kasir Pintar / Kain Batik Ecoprint" class="f-input text-xs w-full">
            </div>

            <!-- Kategori & Jurusan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Kategori Produk <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="f-select text-xs w-full">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Jurusan / Kompetensi Keahlian</label>
                    <select name="department_id" class="f-select text-xs w-full">
                        <option value="">-- Lintas Jurusan --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Estimasi Harga & Status Ketersediaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Estimasi Harga (Rp)</label>
                    <input type="number" name="price" min="0" placeholder="Contoh: 150000 (kosongkan jika negosiasi)" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Status Ketersediaan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="f-select text-xs w-full">
                        <option value="AVAILABLE" selected>Tersedia (Ready)</option>
                        <option value="PRE_ORDER">Pre-Order</option>
                        <option value="OUT_OF_STOCK">Stok Habis</option>
                    </select>
                </div>
            </div>

            <!-- Kontak Pemesanan -->
            <div>
                <label class="f-label text-xs">Kontak Pemesanan (WhatsApp / Telepon / Email)</label>
                <input type="text" name="contact" placeholder="Contoh: 081234567890 (Humas TEFA) atau tefa@smkn2-kra.sch.id" class="f-input text-xs w-full">
            </div>

            <!-- Siswa Pembuat / Tim Kreator (Interactive Tagging Selector) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Siswa Pembuat / Tim Kreator</label>
                    <span class="text-[10px] text-bluedark/40">Dapat memilih lebih dari 1 siswa</span>
                </div>
                
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
                    <span id="createStudentPlaceholder" class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih (tim umum vokasi).</span>
                </div>
            </div>

            <!-- Upload Foto Produk (Drag & Drop Zone + File Picker) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Foto Produk / Media</label>
                    <span class="text-[10px] text-bluedark/40">Maks. 4MB (JPG, JPEG, PNG, WEBP)</span>
                </div>

                <div id="createDropzone" class="prd-dropzone" onclick="document.getElementById('createPhotoInput').click()">
                    <input type="file" id="createPhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleFilePicked('create', this.files)">
                    
                    <!-- Prompt -->
                    <div id="createDropzonePrompt" class="space-y-1.5 py-3">
                        <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Tarik &amp; Lepaskan foto ke sini, atau klik untuk memilih</div>
                        <div class="text-[11px] text-bluedark/50">Foto akan tampil di katalog publik dan pratinjau</div>
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

            <!-- Deskripsi Produk -->
            <div>
                <label class="f-label text-xs">Deskripsi Lengkap Produk</label>
                <textarea name="description" rows="3" placeholder="Jelaskan spesifikasi, keunggulan, bahan/fitur teknologi produk, cara pemesanan, dll..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeCreateModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================
     MODAL 2: EDIT PRODUK (UPDATE)
     ======================================================================== -->
<div id="editProductModal" class="prd-modal-overlay">
    <div class="prd-modal-dialog max-w-2xl max-h-[92vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Perbarui Informasi Produk</h3>
                    <p class="text-[11px] text-bluedark/50">Sesuaikan harga, stok, deskripsi, foto, atau siswa pembuat</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold">&times;</button>
        </div>

        <!-- Form Edit -->
        <form id="editProductForm" method="POST" action="" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="sync_students" value="1">

            <!-- Nama Produk -->
            <div>
                <label class="f-label text-xs">Nama Produk / Jasa Vokasi <span class="text-rose-500">*</span></label>
                <input type="text" id="editName" name="name" required class="f-input text-xs w-full">
            </div>

            <!-- Kategori & Jurusan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Kategori Produk <span class="text-rose-500">*</span></label>
                    <select id="editCategory" name="category_id" required class="f-select text-xs w-full">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Jurusan / Kompetensi Keahlian</label>
                    <select id="editDepartment" name="department_id" class="f-select text-xs w-full">
                        <option value="">-- Lintas Jurusan --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Estimasi Harga & Status Ketersediaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Estimasi Harga (Rp)</label>
                    <input type="number" id="editPrice" name="price" min="0" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Status Ketersediaan <span class="text-rose-500">*</span></label>
                    <select id="editStatus" name="status" required class="f-select text-xs w-full">
                        <option value="AVAILABLE">Tersedia (Ready)</option>
                        <option value="PRE_ORDER">Pre-Order</option>
                        <option value="OUT_OF_STOCK">Stok Habis</option>
                    </select>
                </div>
            </div>

            <!-- Kontak Pemesanan -->
            <div>
                <label class="f-label text-xs">Kontak Pemesanan (WhatsApp / Telepon / Email)</label>
                <input type="text" id="editContact" name="contact" class="f-input text-xs w-full">
            </div>

            <!-- Siswa Pembuat / Tim Kreator -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Siswa Pembuat / Tim Kreator</label>
                    <span class="text-[10px] text-bluedark/40">Dapat memilih lebih dari 1 siswa</span>
                </div>
                
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

                <div id="editStudentTags" class="flex flex-wrap items-center gap-1.5 mt-2 min-h-[30px] p-2 bg-slate-50/70 border border-bluelight/60 rounded-xl">
                    <span id="editStudentPlaceholder" class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih.</span>
                </div>
            </div>

            <!-- Upload / Ganti Foto Produk -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="f-label text-xs mb-0">Foto Produk</label>
                    <span class="text-[10px] text-bluedark/40">Maks. 4MB</span>
                </div>

                <!-- Foto Saat Ini (Jika Ada) -->
                <div id="editCurrentPhotoWrapper" style="display: none;" class="mb-3 p-3 rounded-xl bg-slate-50 border border-bluelight flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img id="editCurrentPhotoImg" src="" alt="Foto saat ini" class="w-14 h-14 object-cover rounded-lg border border-bluelight">
                        <div>
                            <div class="text-xs font-semibold text-bluedark">Foto Produk Saat Ini</div>
                            <div class="text-[10px] text-bluedark/50">Akan diganti jika Anda mengunggah foto baru di bawah</div>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-800 cursor-pointer">
                        <input type="checkbox" name="remove_photo" id="editRemovePhoto" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-[11px] font-medium">Hapus Foto</span>
                    </label>
                </div>

                <!-- Dropzone Baru -->
                <div id="editDropzone" class="prd-dropzone" onclick="document.getElementById('editPhotoInput').click()">
                    <input type="file" id="editPhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleFilePicked('edit', this.files)">
                    
                    <div id="editDropzonePrompt" class="space-y-1.5 py-2.5">
                        <div class="w-8 h-8 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Pilih atau Tarik Foto Baru</div>
                        <div class="text-[10px] text-bluedark/50">Biarkan kosong jika tidak ingin mengubah foto saat ini</div>
                    </div>

                    <div id="editDropzonePreview" style="display: none;" class="space-y-2">
                        <div class="relative w-full aspect-video max-h-48 rounded-lg overflow-hidden border border-bluelight bg-slate-100">
                            <img id="editPreviewImg" src="" alt="Pratinjau" class="w-full h-full object-cover">
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span id="editFileName" class="text-[11px] text-bluedark/70 font-mono truncate max-w-[220px]"></span>
                            <button type="button" onclick="event.stopPropagation(); removeFile('edit')" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span>Batalkan Foto Baru</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Produk -->
            <div>
                <label class="f-label text-xs">Deskripsi Lengkap Produk</label>
                <textarea id="editDescription" name="description" rows="3" class="f-textarea text-xs w-full"></textarea>
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
     MODAL 3: PRATINJAU TAMPILAN PUBLIK (PREVIEW IDENTIK DENGAN WEB PUBLIK)
     ======================================================================== -->
<div id="previewProductModal" class="prd-modal-overlay">
    <div class="prd-modal-dialog max-w-4xl max-h-[92vh] w-full">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-3.5 border-b border-bluelight bg-slate-50/90 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-sm text-bluedark">Pratinjau Tampilan Produk di Web Publik</h3>
                    <p class="text-[11px] text-bluedark/50">Simulasi tampilan produk seperti yang dilihat pengunjung website sekolah</p>
                </div>
            </div>

            <!-- Tab Switcher (Detail Halaman vs Kartu Katalog) -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-bluelight">
                <button type="button" id="tabBtnProdDetail" onclick="switchProductPreviewTab('detail')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all shadow-xs">
                    Halaman Detail
                </button>
                <button type="button" id="tabBtnProdCard" onclick="switchProductPreviewTab('card')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all">
                    Kartu Katalog
                </button>
                <button type="button" onclick="closePreviewModal()"
                    class="ml-2 w-7 h-7 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-100 text-lg font-bold leading-none">&times;</button>
            </div>
        </div>

        <!-- Scrollable Modal Body (Sesuai Layout Publik) -->
        <div class="p-6 sm:p-8 overflow-y-auto bg-slate-50/40">

            <!-- ================================================================
                 TAB 1: TAMPILAN HALAMAN DETAIL PRODUK (/produk/{slug})
                 ================================================================ -->
            <div id="prodDetailTab" class="bg-white rounded-3xl border border-bluelight shadow-xs p-6 sm:p-8">
                <!-- Page Header Simulation (Breadcrumbs & Eyebrow) -->
                <div class="mb-6 pb-4 border-b border-bluelight/70">
                    <nav class="flex items-center gap-1.5 text-xs text-bluedark/50 mb-2">
                        <span>Beranda</span>
                        <span>/</span>
                        <span>Produk Siswa</span>
                        <span>/</span>
                        <span id="pvBreadcrumbTitle" class="text-bluedark font-medium truncate">...</span>
                    </nav>
                    <p id="pvEyebrow" class="font-heading text-xs tracking-[0.2em] uppercase text-blueprim font-semibold">Kategori</p>
                    <h2 id="pvHeaderTitle" class="font-heading font-bold text-xl sm:text-2xl text-bluedark mt-1 leading-snug">Nama Produk</h2>
                </div>

                <!-- 2-Column Product Detail Layout (Identik dengan public/products/show.blade.php) -->
                <div class="grid md:grid-cols-2 gap-8 items-start">
                    <!-- Kolom Kiri: Galeri Foto & Siswa Pembuat -->
                    <div>
                        <div class="rounded-3xl overflow-hidden border border-bluelight h-64 sm:h-72 md:h-80 bg-slate-100 flex items-center justify-center relative">
                            <img id="pvDetailImg" src="" alt="Foto Produk" class="w-full h-full object-cover">
                            <div id="pvDetailImgFallback" class="flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                <span class="text-xs mt-2 font-medium">Foto Produk Belum Diunggah</span>
                            </div>
                        </div>

                        <p id="pvDetailStudentsText" class="text-xs text-bluedark/50 mt-3">
                            Dikerjakan oleh <span id="pvDetailStudents" class="font-medium text-bluedark">Siswa SMKN 2 Karanganyar</span>
                        </p>
                    </div>

                    <!-- Kolom Kanan: Informasi, Harga, Spesifikasi & Aksi WhatsApp -->
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span id="pvDetailCategoryBadge"
                                class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">
                                Produk
                            </span>
                            <span id="pvDetailStatusBadge"
                                class="text-xs font-heading font-semibold px-2.5 py-1 rounded-full">
                                Tersedia
                            </span>
                        </div>

                        <h1 id="pvDetailTitle" class="font-heading font-bold text-2xl sm:text-3xl text-bluedark mt-3 leading-snug">
                            Nama Produk
                        </h1>

                        <div class="flex items-end gap-2 mt-4">
                            <p id="pvDetailPrice" class="font-heading font-bold text-2xl sm:text-3xl text-bluedark">
                                Rp 0
                            </p>
                            <p class="text-xs sm:text-sm text-bluedark/50 mb-1">harga</p>
                        </div>

                        <p id="pvDetailDescription" class="text-sm text-bluedark/60 mt-4 leading-relaxed whitespace-pre-line"></p>

                        <!-- Box Spesifikasi (Identik Public) -->
                        <div class="mt-6 bg-bluelight/50 rounded-2xl p-5 border border-bluelight/60">
                            <p class="font-heading font-semibold text-bluedark text-sm mb-3">Detail &amp; Spesifikasi</p>
                            <ul class="space-y-2 text-sm text-bluedark/70">
                                <li class="flex items-start gap-2">
                                    <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                    <span>Program keahlian: <strong id="pvDetailDepartment" class="font-medium text-bluedark">Umum</strong></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                    <span>Kategori: <strong id="pvDetailCategory" class="font-medium text-bluedark">Produk</strong></span>
                                </li>
                                <li id="pvDetailContactLi" class="flex items-start gap-2">
                                    <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                    <span>Kontak penjual: <strong id="pvDetailContact" class="font-medium text-bluedark">-</strong></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                    <span>Status: <strong id="pvDetailStatusText" class="font-medium text-bluedark">Tersedia</strong></span>
                                </li>
                            </ul>
                        </div>

                        <!-- Tombol Aksi WhatsApp (Identik Public) -->
                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <a id="pvDetailWaBtn" href="#" target="_blank" rel="noopener"
                                class="inline-flex items-center justify-center gap-2 font-heading font-medium text-white bg-[#25D366] hover:bg-[#1DA851] transition-colors px-6 py-3.5 rounded-full text-sm">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span>Pesan via WhatsApp</span>
                            </a>
                            <button type="button" onclick="closePreviewModal()"
                                class="inline-flex items-center justify-center gap-2 font-heading font-medium text-bluedark border border-bluelight hover:bg-bluelight transition-colors px-6 py-3.5 rounded-full text-sm">
                                Tutup Pratinjau
                            </button>
                        </div>
                        <p class="text-xs text-bluedark/40 mt-3">
                            Harga dapat berubah sesuai spesifikasi &amp; kesepakatan. Konfirmasi akhir dilakukan lewat chat WhatsApp.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ================================================================
                 TAB 2: TAMPILAN KARTU KATALOG PUBLIK (/produk)
                 ================================================================ -->
            <div id="prodCardTab" class="hidden max-w-sm mx-auto py-4">
                <p class="text-xs text-center text-bluedark/50 mb-4">Simulasi kartu di halaman katalog publik (<code class="bg-slate-100 px-1 py-0.5 rounded text-blueprim">/produk</code>):</p>
                <article class="flex flex-col bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden hover:shadow-lg transition-all duration-300">
                    <div class="block h-48 shrink-0 overflow-hidden bg-slate-100 relative">
                        <img id="pvCardImg" src="" alt="Foto Produk" class="w-full h-full object-cover">
                        <div id="pvCardImgFallback" class="w-full h-full flex items-center justify-center text-slate-400">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        </div>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="pvCardCategory" class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                Kategori
                            </span>
                            <span id="pvCardDepartment" class="text-xs text-bluedark/50">Jurusan</span>
                        </div>

                        <h3 id="pvCardTitle" class="font-heading font-semibold text-bluedark mt-3 leading-snug">
                            Nama Produk
                        </h3>

                        <p id="pvCardDescription" class="text-sm text-bluedark/60 mt-2.5 leading-relaxed line-clamp-3">
                            Deskripsi produk...
                        </p>

                        <div class="mt-auto pt-4 border-t border-bluelight/40">
                            <p id="pvCardPrice" class="font-heading font-bold text-blueprim text-base">
                                Rp 0
                            </p>
                            <p id="pvCardContact" class="text-xs text-bluedark/50 mt-1 line-clamp-2">
                                Kontak...
                            </p>
                        </div>
                    </div>
                </article>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-bluelight bg-slate-50 flex items-center justify-between shrink-0">
            <span class="text-xs text-bluedark/50">Halaman publik: <span class="font-mono text-blueprim">/produk/{slug}</span></span>
            <button type="button" onclick="closePreviewModal()" class="btn btn-primary btn-sm text-xs font-semibold px-4">Tutup Pratinjau</button>
        </div>
    </div>
</div>

<!-- ========================================================================
     MODAL 4: KONFIRMASI HAPUS (DELETE)
     ======================================================================== -->
<div id="deleteProductModal" class="prd-modal-overlay">
    <div class="prd-modal-dialog max-w-md">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Katalog Produk?</h3>
                <p class="text-xs text-bluedark/60 mt-1.5 leading-relaxed">
                    Anda yakin ingin menghapus produk <strong id="deleteProductName" class="text-bluedark"></strong>? File foto dan relasi siswa pembuat akan dihapus. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form id="deleteProductForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-center gap-2 pt-2">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm text-xs">Batalkan</button>
                    <button type="submit" class="btn btn-sm text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white px-5">Ya, Hapus Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ==========================================================================
   PRODUCTS JAVASCRIPT CONTROLS
   ========================================================================== */

let selectedStudents = {
    create: [],
    edit: []
};

// --- Modal Show / Hide ---
function openCreateModal() {
    document.getElementById('createProductModal').classList.add('show');
}
function closeCreateModal() {
    document.getElementById('createProductModal').classList.remove('show');
}

function openEditModal(productId) {
    // Fetch product details via AJAX
    fetch('{{ url('/admin/cms/products') }}/' + productId + '/preview', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const form = document.getElementById('editProductForm');
        form.action = '{{ url('/admin/cms/products') }}/' + data.id;

        document.getElementById('editName').value = data.name;
        document.getElementById('editCategory').value = data.category_id;
        document.getElementById('editDepartment').value = data.department_id || '';
        document.getElementById('editPrice').value = data.price !== null ? data.price : '';
        document.getElementById('editStatus').value = data.status;
        document.getElementById('editContact').value = data.contact || '';
        document.getElementById('editDescription').value = data.description || '';

        // Reset file upload
        removeFile('edit');
        const removePhotoCheckbox = document.getElementById('editRemovePhoto');
        if (removePhotoCheckbox) removePhotoCheckbox.checked = false;

        // Current Photo
        const curWrapper = document.getElementById('editCurrentPhotoWrapper');
        const curImg = document.getElementById('editCurrentPhotoImg');
        if (data.photo_url) {
            curImg.src = data.photo_url;
            curWrapper.style.display = 'flex';
        } else {
            curWrapper.style.display = 'none';
        }

        // Students
        selectedStudents.edit = [];
        if (data.students && data.students.length > 0) {
            data.students.forEach(st => {
                selectedStudents.edit.push({
                    id: st.id,
                    name: st.name,
                    nis: st.nis
                });
            });
        }
        renderStudentTags('edit');

        document.getElementById('editProductModal').classList.add('show');
    })
    .catch(err => {
        console.error('Error fetching product data:', err);
        alert('Gagal mengambil data produk untuk diedit.');
    });
}
function closeEditModal() {
    document.getElementById('editProductModal').classList.remove('show');
}

function switchProductPreviewTab(tab) {
    const detailTab = document.getElementById('prodDetailTab');
    const cardTab = document.getElementById('prodCardTab');
    const btnDetail = document.getElementById('tabBtnProdDetail');
    const btnCard = document.getElementById('tabBtnProdCard');

    if (tab === 'detail') {
        detailTab.classList.remove('hidden');
        cardTab.classList.add('hidden');
        btnDetail.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all shadow-xs';
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    } else {
        detailTab.classList.add('hidden');
        cardTab.classList.remove('hidden');
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all shadow-xs';
        btnDetail.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    }
}

function previewProduct(productId) {
    fetch('{{ url('/admin/cms/products') }}/' + productId + '/preview', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        // Reset view tab to detail
        switchProductPreviewTab('detail');

        // Breadcrumb & Eyebrow
        document.getElementById('pvBreadcrumbTitle').textContent = data.name;
        document.getElementById('pvEyebrow').textContent = data.category_name;
        document.getElementById('pvHeaderTitle').textContent = data.name;

        // Detail Tab Hydration
        document.getElementById('pvDetailTitle').textContent = data.name;
        document.getElementById('pvDetailCategoryBadge').textContent = data.category_name;
        document.getElementById('pvDetailCategory').textContent = data.category_name;
        document.getElementById('pvDetailDepartment').textContent = data.department_name;
        document.getElementById('pvDetailPrice').textContent = data.formatted_price;
        document.getElementById('pvDetailDescription').textContent = data.description || 'Tidak ada deskripsi rinci untuk produk ini.';
        document.getElementById('pvDetailStatusText').textContent = data.status_label;

        // Status badge
        const pvStatus = document.getElementById('pvDetailStatusBadge');
        pvStatus.textContent = data.status_label;
        if (data.status === 'AVAILABLE') {
            pvStatus.className = 'text-xs font-heading font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200';
        } else if (data.status === 'PRE_ORDER') {
            pvStatus.className = 'text-xs font-heading font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200';
        } else {
            pvStatus.className = 'text-xs font-heading font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200';
        }

        // Image in Detail & Card
        const detailImg = document.getElementById('pvDetailImg');
        const detailFallback = document.getElementById('pvDetailImgFallback');
        const cardImg = document.getElementById('pvCardImg');
        const cardFallback = document.getElementById('pvCardImgFallback');

        if (data.photo_url) {
            detailImg.src = data.photo_url;
            detailImg.style.display = 'block';
            detailFallback.style.display = 'none';

            cardImg.src = data.photo_url;
            cardImg.style.display = 'block';
            cardFallback.style.display = 'none';
        } else {
            detailImg.style.display = 'none';
            detailFallback.style.display = 'flex';

            cardImg.style.display = 'none';
            cardFallback.style.display = 'flex';
        }

        // Students credit (Identik public: "Dikerjakan oleh Nama Siswa 1, Nama Siswa 2")
        const studentsWrap = document.getElementById('pvDetailStudentsText');
        const studentsSpan = document.getElementById('pvDetailStudents');
        if (data.students && data.students.length > 0) {
            studentsSpan.textContent = data.students.map(s => s.name).join(', ');
            studentsWrap.style.display = 'block';
        } else {
            studentsSpan.textContent = 'Siswa SMKN 2 Karanganyar';
            studentsWrap.style.display = 'block';
        }

        // Contact & WA Button (Identik public)
        const contactLi = document.getElementById('pvDetailContactLi');
        const contactText = document.getElementById('pvDetailContact');
        const waBtn = document.getElementById('pvDetailWaBtn');

        if (data.contact) {
            contactText.textContent = data.contact;
            contactLi.style.display = 'flex';

            const cleanNumber = data.contact.replace(/\D/g, '');
            let waNumber = cleanNumber;
            if (waNumber.startsWith('0')) waNumber = '62' + waNumber.substring(1);
            waBtn.href = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent('Halo, saya tertarik dengan produk ' + data.name + ' dari SMK Negeri 2 Karanganyar');
            waBtn.style.display = 'inline-flex';
        } else {
            contactText.textContent = '-';
            contactLi.style.display = 'none';
            waBtn.style.display = 'none';
        }

        // Card Tab Hydration (Identik components/public/product-card.blade.php)
        document.getElementById('pvCardTitle').textContent = data.name;
        document.getElementById('pvCardCategory').textContent = data.category_name;
        document.getElementById('pvCardDepartment').textContent = data.department_code || data.department_name;
        document.getElementById('pvCardDescription').textContent = data.description || '';
        document.getElementById('pvCardPrice').textContent = data.formatted_price;
        document.getElementById('pvCardContact').textContent = data.contact ? 'Kontak: ' + data.contact : '';

        document.getElementById('previewProductModal').classList.add('show');
    })
    .catch(err => {
        console.error('Error fetching preview:', err);
        alert('Gagal memuat pratinjau produk.');
    });
}
function closePreviewModal() {
    document.getElementById('previewProductModal').classList.remove('show');
}

function openDeleteModal(productId, productName) {
    document.getElementById('deleteProductForm').action = '{{ url('/admin/cms/products') }}/' + productId;
    document.getElementById('deleteProductName').textContent = '"' + productName + '"';
    document.getElementById('deleteProductModal').classList.add('show');
}
function closeDeleteModal() {
    document.getElementById('deleteProductModal').classList.remove('show');
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    ['createProductModal', 'editProductModal', 'previewProductModal', 'deleteProductModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
});

// --- Student Tagger Component ---
function filterStudentDropdown(mode) {
    const input = document.getElementById(mode + 'StudentSearch');
    const filter = input.value.toLowerCase().trim();
    const dropdown = document.getElementById(mode + 'StudentDropdown');
    const options = dropdown.querySelectorAll('.student-option');

    if (!filter) {
        dropdown.classList.add('hidden');
        return;
    }

    let matchCount = 0;
    options.forEach(opt => {
        const name = opt.getAttribute('data-name').toLowerCase();
        const nis = opt.getAttribute('data-nis').toLowerCase();
        const id = parseInt(opt.getAttribute('data-id'), 10);

        // Check if already selected
        const isSelected = selectedStudents[mode].some(s => s.id === id);

        if ((name.includes(filter) || nis.includes(filter)) && !isSelected) {
            opt.style.display = 'flex';
            matchCount++;
        } else {
            opt.style.display = 'none';
        }
    });

    if (matchCount > 0) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function addStudentTag(mode, id, name, nis) {
    if (!selectedStudents[mode].some(s => s.id === id)) {
        selectedStudents[mode].push({ id: id, name: name, nis: nis });
        renderStudentTags(mode);
    }
    const input = document.getElementById(mode + 'StudentSearch');
    input.value = '';
    document.getElementById(mode + 'StudentDropdown').classList.add('hidden');
    input.focus();
}

function removeStudentTag(mode, id) {
    selectedStudents[mode] = selectedStudents[mode].filter(s => s.id !== id);
    renderStudentTags(mode);
}

function renderStudentTags(mode) {
    const container = document.getElementById(mode + 'StudentTags');
    container.innerHTML = '';

    if (selectedStudents[mode].length === 0) {
        container.innerHTML = `<span id="${mode}StudentPlaceholder" class="text-[11px] text-bluedark/40 italic">Belum ada siswa yang dipilih (tim umum/sekolah).</span>`;
        return;
    }

    selectedStudents[mode].forEach(st => {
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 text-xs font-semibold animate-in fade-in duration-100';
        tag.innerHTML = `
            <span>${st.name}</span>
            <span class="text-[10px] text-blue-700 font-mono">(${st.nis})</span>
            <button type="button" onclick="removeStudentTag('${mode}', ${st.id})" class="text-blue-700 hover:text-rose-600 font-bold ml-0.5">&times;</button>
            <input type="hidden" name="student_ids[]" value="${st.id}">
        `;
        container.appendChild(tag);
    });
}

// --- File Dropzone & Picker ---
function handleFilePicked(mode, files) {
    if (!files || files.length === 0) return;
    const file = files[0];

    // Validate type
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('Format file tidak didukung. Harap pilih gambar JPG, PNG, atau WEBP.');
        return;
    }

    // Validate size (max 4MB)
    if (file.size > 4 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimum ukuran adalah 4MB.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById(mode + 'PreviewImg').src = e.target.result;
        document.getElementById(mode + 'FileName').textContent = file.name;
        document.getElementById(mode + 'DropzonePrompt').style.display = 'none';
        document.getElementById(mode + 'DropzonePreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function removeFile(mode) {
    const input = document.getElementById(mode + 'PhotoInput');
    if (input) input.value = '';
    const preview = document.getElementById(mode + 'DropzonePreview');
    if (preview) preview.style.display = 'none';
    const prompt = document.getElementById(mode + 'DropzonePrompt');
    if (prompt) prompt.style.display = 'block';
}

// Setup Drag & Drop Listeners
['create', 'edit'].forEach(mode => {
    const dropzone = document.getElementById(mode + 'Dropzone');
    if (!dropzone) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            document.getElementById(mode + 'PhotoInput').files = files;
            handleFilePicked(mode, files);
        }
    }, false);
});
</script>
@endpush
@endsection
