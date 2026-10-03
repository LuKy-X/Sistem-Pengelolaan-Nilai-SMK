@extends('layouts.admin')

@section('title', 'Bursa Kerja Khusus (BKK) & Mitra Industri')

@push('styles')
<style>
/* ==========================================================================
   CAREER & BKK CMS STYLING
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

/* Dropzone Styling for Logo Upload */
.car-dropzone {
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
.car-dropzone:hover,
.car-dropzone.dragover {
    border-color: #2563EB;
    background: #EFF6FF;
    transform: scale(1.005);
}

/* Neutral Blur Modal Backdrop (No Blue Overlay!) */
.car-modal-overlay {
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
.car-modal-overlay.show {
    display: flex !important;
}
.car-modal-dialog {
    background: #FFFFFF;
    border-radius: 1.5rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #E2E8F0;
    margin: auto;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: carModalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes carModalFadeIn {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Replica Public Card */
.car-preview-card {
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

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Bursa Kerja Khusus (BKK) &amp; PKL</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    Karier &amp; Alumni
                </span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola lowongan magang industri, rekrutmen kerja alumni, mitra perusahaan DUDI, dan layanan pendampingan BKK.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('public.career.index') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2 text-xs font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Lihat di Web</span>
            </a>
            @if($activeTab === 'opportunities')
                <button type="button" onclick="openCreateOppModal()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Buka Lowongan Baru</span>
                </button>
            @elseif($activeTab === 'companies')
                <button type="button" onclick="openCreateCompModal()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Mitra Perusahaan</span>
                </button>
            @else
                <button type="button" onclick="openCreateServModal()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Layanan BKK</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Success -->
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

    <!-- Quick Stats Cards (5 Metric Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Card 1: Total Lowongan -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-blue-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blueprim flex items-center justify-center shrink-0 border border-blue-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Total Lowongan</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['total_opportunities'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">{{ number_format($stats['open_opportunities'] ?? 0) }} Aktif dibuka</div>
            </div>
        </div>

        <!-- Card 2: Lowongan Kerja -->
        <div class="panel p-4 flex items-center gap-3.5 bg-indigo-50/40 border border-indigo-200/80 shadow-xs hover:border-indigo-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 border border-indigo-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-indigo-800/80 uppercase tracking-wider">Pekerjaan</div>
                <div class="font-heading font-extrabold text-2xl text-indigo-900 leading-tight mt-0.5">{{ number_format($stats['job_count'] ?? 0) }}</div>
                <div class="text-[10px] text-indigo-700/70 mt-0.5 truncate">Karir untuk alumni</div>
            </div>
        </div>

        <!-- Card 3: Magang PKL -->
        <div class="panel p-4 flex items-center gap-3.5 bg-amber-50/40 border border-amber-200/80 shadow-xs hover:border-amber-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-amber-800/80 uppercase tracking-wider">Magang PKL</div>
                <div class="font-heading font-extrabold text-2xl text-amber-900 leading-tight mt-0.5">{{ number_format($stats['internship_count'] ?? 0) }}</div>
                <div class="text-[10px] text-amber-700/70 mt-0.5 truncate">Prakerin siswa aktif</div>
            </div>
        </div>

        <!-- Card 4: Mitra DUDI -->
        <div class="panel p-4 flex items-center gap-3.5 bg-emerald-50/40 border border-emerald-200/80 shadow-xs hover:border-emerald-300 transition-colors">
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-emerald-800/80 uppercase tracking-wider">Mitra DUDI</div>
                <div class="font-heading font-extrabold text-2xl text-emerald-900 leading-tight mt-0.5">{{ number_format($stats['total_companies'] ?? 0) }}</div>
                <div class="text-[10px] text-emerald-700/70 mt-0.5 truncate">Perusahaan mitra</div>
            </div>
        </div>

        <!-- Card 5: Layanan BKK -->
        <div class="panel p-4 flex items-center gap-3.5 bg-white border border-bluelight/70 shadow-xs hover:border-teal-300 transition-colors col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider">Layanan BKK</div>
                <div class="font-heading font-extrabold text-2xl text-bluedark leading-tight mt-0.5">{{ number_format($stats['total_services'] ?? 0) }}</div>
                <div class="text-[10px] text-bluedark/40 mt-0.5 truncate">Pendampingan karir</div>
            </div>
        </div>
    </div>

    <!-- Interactive Navigation Tabs -->
    <div class="border-b border-bluelight flex items-center gap-2 overflow-x-auto">
        <!-- Tab 1: Lowongan Karir & Magang -->
        <a href="{{ route('admin.cms.career', ['tab' => 'opportunities']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 font-heading text-xs font-semibold border-b-2 transition-all {{ $activeTab === 'opportunities' ? 'border-blueprim text-blueprim bg-blue-50/50' : 'border-transparent text-bluedark/60 hover:text-bluedark hover:border-slate-300' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <span>Lowongan Karir &amp; Magang</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'opportunities' ? 'bg-blueprim text-white' : 'bg-slate-100 text-bluedark/60' }}">
                {{ $opportunities->total() }}
            </span>
        </a>

        <!-- Tab 2: Mitra Perusahaan / DUDI -->
        <a href="{{ route('admin.cms.career', ['tab' => 'companies']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 font-heading text-xs font-semibold border-b-2 transition-all {{ $activeTab === 'companies' ? 'border-blueprim text-blueprim bg-blue-50/50' : 'border-transparent text-bluedark/60 hover:text-bluedark hover:border-slate-300' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
            <span>Mitra Perusahaan / DUDI</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'companies' ? 'bg-blueprim text-white' : 'bg-slate-100 text-bluedark/60' }}">
                {{ $companies->total() }}
            </span>
        </a>

        <!-- Tab 3: Layanan BKK -->
        <a href="{{ route('admin.cms.career', ['tab' => 'services']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 font-heading text-xs font-semibold border-b-2 transition-all {{ $activeTab === 'services' ? 'border-blueprim text-blueprim bg-blue-50/50' : 'border-transparent text-bluedark/60 hover:text-bluedark hover:border-slate-300' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Layanan Pendampingan BKK</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $activeTab === 'services' ? 'bg-blueprim text-white' : 'bg-slate-100 text-bluedark/60' }}">
                {{ $services->count() }}
            </span>
        </a>
    </div>

    <!-- ====================================================================
         TAB 1 CONTENT: LOWONGAN KARIR & MAGANG
         ==================================================================== -->
    @if($activeTab === 'opportunities')
        <!-- Filter Toolbar -->
        <div class="panel p-4 bg-white border border-bluelight/70 shadow-xs">
            <form method="GET" action="{{ route('admin.cms.career') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <input type="hidden" name="tab" value="opportunities">
                
                <!-- Search Input -->
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" name="search_opp" value="{{ $searchOpp }}" placeholder="Cari posisi lowongan, mitra, deskripsi, lokasi..." class="f-input pl-9 text-xs w-full">
                </div>

                <!-- Tipe Lowongan -->
                <div class="w-full sm:w-40">
                    <select name="type" class="f-select text-xs w-full">
                        <option value="">Semua Tipe</option>
                        <option value="JOB" {{ $oppType === 'JOB' ? 'selected' : '' }}>Pekerjaan (Alumni)</option>
                        <option value="INTERNSHIP" {{ $oppType === 'INTERNSHIP' ? 'selected' : '' }}>Magang PKL</option>
                    </select>
                </div>

                <!-- Status Lowongan -->
                <div class="w-full sm:w-36">
                    <select name="status" class="f-select text-xs w-full">
                        <option value="">Semua Status</option>
                        <option value="OPEN" {{ $oppStatus === 'OPEN' ? 'selected' : '' }}>Dibuka (Open)</option>
                        <option value="CLOSED" {{ $oppStatus === 'CLOSED' ? 'selected' : '' }}>Ditutup</option>
                    </select>
                </div>

                <!-- Perusahaan Mitra -->
                <div class="w-full sm:w-48">
                    <select name="company_id" class="f-select text-xs w-full">
                        <option value="">Semua Mitra Industri</option>
                        @foreach($allCompanies as $comp)
                            <option value="{{ $comp->id }}" {{ (string)$oppCompanyId === (string)$comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="btn btn-primary btn-sm flex items-center justify-center gap-1.5 text-xs font-semibold px-4">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter</span>
                    </button>
                    @if($searchOpp || $oppType || $oppStatus || $oppCompanyId)
                        <a href="{{ route('admin.cms.career', ['tab' => 'opportunities']) }}" class="btn btn-outline btn-sm text-xs font-semibold text-bluedark/70 hover:text-bluedark" title="Reset Filter">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Opportunities -->
        <div class="panel p-5 bg-white border border-bluelight/70 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Lowongan Karir &amp; Magang PKL</h2>
                    <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-bluedark/60 font-semibold font-mono">
                        {{ $opportunities->total() }} Data
                    </span>
                </div>
                <span class="text-xs text-bluedark/50">Halaman {{ $opportunities->currentPage() }} dari {{ $opportunities->lastPage() ?: 1 }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-bluelight bg-slate-50/60">
                            <th class="w-12 py-3 px-3">No</th>
                            <th class="py-3 px-3">Posisi / Judul Lowongan</th>
                            <th class="py-3 px-3">Perusahaan Mitra</th>
                            <th class="py-3 px-3">Tipe</th>
                            <th class="py-3 px-3">Lokasi</th>
                            <th class="py-3 px-3">Batas Pendaftaran</th>
                            <th class="py-3 px-3 text-center">Pelamar</th>
                            <th class="w-28 py-3 px-3 text-center">Status</th>
                            <th class="w-32 py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($opportunities as $idx => $opp)
                            @php
                                $typeVal = $opp->type instanceof \App\Enums\CareerOpportunityType ? $opp->type->value : (string)$opp->type;
                                $statusVal = $opp->status instanceof \App\Enums\CareerOpportunityStatus ? $opp->status->value : (string)$opp->status;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors" id="opp-row-{{ $opp->id }}">
                                <td class="py-3.5 px-3 text-bluedark/60 font-mono">{{ $opportunities->firstItem() + $idx }}</td>
                                
                                <!-- Judul Lowongan -->
                                <td class="py-3.5 px-3">
                                    <div class="font-heading font-semibold text-bluedark text-[13px] leading-snug">
                                        {{ $opp->title }}
                                    </div>
                                    @if(filled($opp->application_link))
                                        <a href="{{ $opp->application_link }}" target="_blank" class="inline-flex items-center gap-1 text-[10.5px] text-blueprim hover:underline mt-0.5">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            <span class="truncate max-w-[180px]">{{ $opp->application_link }}</span>
                                        </a>
                                    @endif
                                </td>

                                <!-- Perusahaan -->
                                <td class="py-3.5 px-3">
                                    <div class="font-medium text-bluedark">{{ $opp->company?->name ?? 'Mitra Sekolah' }}</div>
                                    @if(filled($opp->company?->industry))
                                        <div class="text-[10.5px] text-bluedark/50">{{ $opp->company->industry }}</div>
                                    @endif
                                </td>

                                <!-- Tipe -->
                                <td class="py-3.5 px-3">
                                    @if($typeVal === 'JOB')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blueprim border border-blue-200 font-semibold text-[10px]">
                                            Kerja (Alumni)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-semibold text-[10px]">
                                            Magang PKL
                                        </span>
                                    @endif
                                </td>

                                <!-- Lokasi -->
                                <td class="py-3.5 px-3 text-bluedark/70">
                                    {{ $opp->location ?: '-' }}
                                </td>

                                <!-- Batas Pendaftaran -->
                                <td class="py-3.5 px-3">
                                    @if($opp->close_date)
                                        <div class="text-bluedark font-medium">
                                            {{ \Carbon\Carbon::parse($opp->close_date)->translatedFormat('d M Y') }}
                                        </div>
                                        @if($opp->open_date)
                                            <div class="text-[10px] text-bluedark/40">
                                                Buka: {{ \Carbon\Carbon::parse($opp->open_date)->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-emerald-700 font-medium">Terbuka Selalu</span>
                                    @endif
                                </td>

                                <!-- Pelamar -->
                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-bluedark/70 font-semibold font-mono text-[10.5px]">
                                        {{ $opp->applications->count() }} Pelamar
                                    </span>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-3.5 px-3 text-center">
                                    <button type="button" 
                                            onclick="toggleOppStatus({{ $opp->id }})" 
                                            id="oppStatusBtn-{{ $opp->id }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all {{ $statusVal === 'OPEN' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}"
                                            title="Klik untuk mengubah status buka/tutup">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusVal === 'OPEN' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span id="oppStatusText-{{ $opp->id }}">{{ $statusVal === 'OPEN' ? 'Dibuka' : 'Ditutup' }}</span>
                                    </button>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-bluelight/70 shadow-2xs">
                                        <!-- Pratinjau -->
                                        <button type="button" 
                                                onclick="previewOpp({{ $opp->id }})" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blueprim hover:bg-white hover:shadow-xs transition-all" 
                                                title="Pratinjau Lowongan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>

                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick="openEditOppModal({{ $opp->id }})" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Edit Lowongan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button type="button" 
                                                onclick="openDeleteOppModal({{ $opp->id }}, '{{ addslashes($opp->title) }}')" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Hapus Lowongan">
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
                                        <div class="font-heading font-semibold text-bluedark text-sm">Tidak Ada Lowongan</div>
                                        <p class="text-xs text-bluedark/50">
                                            Belum ada lowongan pekerjaan atau magang PKL yang cocok dengan kriteria filter saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination (10 data per halaman) -->
            <div class="mt-5 pt-3 border-t border-bluelight/60">
                {{ $opportunities->links() }}
            </div>
        </div>

    <!-- ====================================================================
         TAB 2 CONTENT: MITRA PERUSAHAAN / DUDI
         ==================================================================== -->
    @elseif($activeTab === 'companies')
        <!-- Search Toolbar -->
        <div class="panel p-4 bg-white border border-bluelight/70 shadow-xs">
            <form method="GET" action="{{ route('admin.cms.career') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <input type="hidden" name="tab" value="companies">
                
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" name="search_comp" value="{{ $searchComp }}" placeholder="Cari nama perusahaan mitra, bidang industri, alamat, email..." class="f-input pl-9 text-xs w-full">
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="btn btn-primary btn-sm flex items-center justify-center gap-1.5 text-xs font-semibold px-4">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Cari Mitra</span>
                    </button>
                    @if($searchComp)
                        <a href="{{ route('admin.cms.career', ['tab' => 'companies']) }}" class="btn btn-outline btn-sm text-xs font-semibold text-bluedark/70 hover:text-bluedark" title="Reset Pencarian">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Companies -->
        <div class="panel p-5 bg-white border border-bluelight/70 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Mitra Dunia Usaha &amp; Industri (DUDI)</h2>
                    <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-bluedark/60 font-semibold font-mono">
                        {{ $companies->total() }} Perusahaan
                    </span>
                </div>
                <span class="text-xs text-bluedark/50">Halaman {{ $companies->currentPage() }} dari {{ $companies->lastPage() ?: 1 }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-bluelight bg-slate-50/60">
                            <th class="w-12 py-3 px-3">No</th>
                            <th class="w-16 py-3 px-2 text-center">Logo</th>
                            <th class="py-3 px-3">Nama Perusahaan</th>
                            <th class="py-3 px-3">Bidang / Industri</th>
                            <th class="py-3 px-3">Kontak &amp; Email</th>
                            <th class="py-3 px-3">Website</th>
                            <th class="py-3 px-3 text-center">Lowongan</th>
                            <th class="w-24 py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($companies as $idx => $comp)
                            @php
                                $mediaService = app(\App\Services\PublicMediaService::class);
                                $logoUrl = $mediaService->forModel($comp, 'logo');
                                $companyEditData = $comp->toArray();
                                $companyEditData['logo_url'] = $logoUrl;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors" id="comp-row-{{ $comp->id }}">
                                <td class="py-3.5 px-3 text-bluedark/60 font-mono">{{ $companies->firstItem() + $idx }}</td>
                                
                                <!-- Logo -->
                                <td class="py-3.5 px-2 text-center">
                                    @if($logoUrl)
                                        <div class="w-10 h-10 rounded-xl overflow-hidden border border-bluelight mx-auto shadow-2xs bg-white p-1">
                                            <img src="{{ $logoUrl }}" alt="{{ $comp->name }}" class="w-full h-full object-contain">
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 mx-auto flex items-center justify-center text-slate-400">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
                                        </div>
                                    @endif
                                </td>

                                <!-- Nama Perusahaan -->
                                <td class="py-3.5 px-3">
                                    <div class="font-heading font-semibold text-bluedark text-[13px]">
                                        {{ $comp->name }}
                                    </div>
                                    @if(filled($comp->address))
                                        <div class="text-[10.5px] text-bluedark/50 truncate max-w-[200px]" title="{{ $comp->address }}">
                                            {{ $comp->address }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Industri -->
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ $comp->industry ?: 'Umum / Industri' }}
                                    </span>
                                </td>

                                <!-- Kontak -->
                                <td class="py-3.5 px-3">
                                    @if($comp->phone)
                                        <div class="text-[11px] text-bluedark font-medium">{{ $comp->phone }}</div>
                                    @endif
                                    @if($comp->email)
                                        <div class="text-[10.5px] text-bluedark/50 truncate max-w-[160px]">{{ $comp->email }}</div>
                                    @endif
                                    @if(!$comp->phone && !$comp->email)
                                        <span class="text-bluedark/40 italic">-</span>
                                    @endif
                                </td>

                                <!-- Website -->
                                <td class="py-3.5 px-3">
                                    @if($comp->website)
                                        <a href="{{ $comp->website }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blueprim hover:underline">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            <span class="truncate max-w-[130px]">{{ preg_replace('(^https?://)', '', $comp->website) }}</span>
                                        </a>
                                    @else
                                        <span class="text-bluedark/40 italic">-</span>
                                    @endif
                                </td>

                                <!-- Jumlah Lowongan -->
                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blueprim font-bold font-mono text-[10.5px]">
                                        {{ $comp->opportunities_count }} Lowongan
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-bluelight/70 shadow-2xs">
                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick="openEditCompModal(@js($companyEditData))"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Edit Mitra">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button type="button" 
                                                onclick="openDeleteCompModal({{ $comp->id }}, '{{ addslashes($comp->name) }}')" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Hapus Mitra">
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
                                        <div class="font-heading font-semibold text-bluedark text-sm">Tidak Ada Mitra Perusahaan</div>
                                        <p class="text-xs text-bluedark/50">
                                            Belum ada perusahaan mitra industri yang didaftarkan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination (10 data per halaman) -->
            <div class="mt-5 pt-3 border-t border-bluelight/60">
                {{ $companies->links() }}
            </div>
        </div>

    <!-- ====================================================================
         TAB 3 CONTENT: LAYANAN BKK
         ==================================================================== -->
    @else
        <div class="panel p-5 bg-white border border-bluelight/70 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Layanan Pendampingan Karir BKK Siswa &amp; Alumni</h2>
                    <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-bluedark/60 font-semibold font-mono">
                        {{ $services->count() }} Layanan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-bluelight bg-slate-50/60">
                            <th class="w-16 py-3 px-3 text-center">Urutan</th>
                            <th class="py-3 px-3">Judul Layanan &amp; Slug</th>
                            <th class="py-3 px-3">Ikon</th>
                            <th class="py-3 px-3">Deskripsi Singkat</th>
                            <th class="w-28 py-3 px-3 text-center">Status</th>
                            <th class="w-24 py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($services as $idx => $serv)
                            <tr class="hover:bg-slate-50/70 transition-colors" id="serv-row-{{ $serv->id }}">
                                <td class="py-3.5 px-3 text-center font-mono font-bold text-bluedark/70">
                                    {{ $serv->sort_order }}
                                </td>

                                <!-- Judul Layanan -->
                                <td class="py-3.5 px-3">
                                    <div class="font-heading font-semibold text-bluedark text-[13px]">
                                        {{ $serv->title }}
                                    </div>
                                    <div class="font-mono text-[10.5px] text-bluedark/40 mt-0.5">
                                        /{{ $serv->slug }}
                                    </div>
                                </td>

                                <!-- Ikon -->
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $serv->icon ?: 'briefcase' }}
                                    </span>
                                </td>

                                <!-- Deskripsi -->
                                <td class="py-3.5 px-3 text-bluedark/70 leading-relaxed max-w-[280px]">
                                    {{ $serv->description ?: '-' }}
                                </td>

                                <!-- Status Aktif Toggle -->
                                <td class="py-3.5 px-3 text-center">
                                    <button type="button" 
                                            onclick="toggleServStatus({{ $serv->id }})" 
                                            id="servStatusBtn-{{ $serv->id }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all {{ $serv->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}"
                                            title="Klik untuk aktifkan / nonaktifkan">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $serv->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span id="servStatusText-{{ $serv->id }}">{{ $serv->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-bluelight/70 shadow-2xs">
                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick="openEditServModal({{ json_encode($serv) }})" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 hover:text-blue-700 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Edit Layanan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>

                                        <!-- Hapus -->
                                        <button type="button" 
                                                onclick="openDeleteServModal({{ $serv->id }}, '{{ addslashes($serv->title) }}')" 
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all" 
                                                title="Hapus Layanan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-bluedark/40">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="font-heading font-semibold text-bluedark text-sm">Tidak Ada Layanan BKK</div>
                                        <p class="text-xs text-bluedark/50">Belum ada layanan pendampingan BKK yang dicatat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- ========================================================================
     MODALS FOR TAB 1: OPPORTUNITIES
     ======================================================================== -->
<!-- Modal Create Opportunity -->
<div id="createOppModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-2xl max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Buka Lowongan Pekerjaan / Magang Baru</h3>
                    <p class="text-[11px] text-bluedark/50">Publikasikan informasi peluang karir DUDI kepada siswa &amp; alumni</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateOppModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.career.opportunities.store') }}" class="overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Perusahaan Mitra -->
            <div>
                <label class="f-label text-xs">Perusahaan Mitra Industri <span class="text-rose-500">*</span></label>
                <select name="company_id" required class="f-select text-xs w-full">
                    <option value="">-- Pilih Mitra Perusahaan --</option>
                    @foreach($allCompanies as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->industry ?: 'Umum' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Posisi / Judul -->
            <div>
                <label class="f-label text-xs">Posisi / Judul Lowongan <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Junior Web Programmer / Teknisi Perakitan Otomotif" class="f-input text-xs w-full">
            </div>

            <!-- Tipe Lowongan & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Tipe Lowongan <span class="text-rose-500">*</span></label>
                    <select name="type" required class="f-select text-xs w-full">
                        <option value="JOB">Pekerjaan Penuh (Alumni)</option>
                        <option value="INTERNSHIP">Magang PKL / Prakerin</option>
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Status Lowongan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="f-select text-xs w-full">
                        <option value="OPEN" selected>Dibuka (Open)</option>
                        <option value="CLOSED">Ditutup (Closed)</option>
                    </select>
                </div>
            </div>

            <!-- Lokasi & Link Lamaran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Lokasi Penempatan</label>
                    <input type="text" name="location" placeholder="Contoh: Karanganyar / Surakarta / Cikarang" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Link Pendaftaran / Form Online</label>
                    <input type="url" name="application_link" placeholder="https://forms.gle/... atau https://career.mitra.com" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Tanggal Buka & Tanggal Tutup -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Tanggal Dibuka</label>
                    <input type="date" name="open_date" value="{{ date('Y-m-d') }}" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Tanggal Ditutup (Deadline)</label>
                    <input type="date" name="close_date" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Deskripsi Tugas -->
            <div>
                <label class="f-label text-xs">Deskripsi Tugas &amp; Pekerjaan</label>
                <textarea name="description" rows="3" placeholder="Uraikan ringkasan peran, job description, fasilitas, atau benefit yang didapatkan..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <!-- Kualifikasi & Persyaratan -->
            <div>
                <label class="f-label text-xs">Kualifikasi &amp; Persyaratan Pelamar</label>
                <textarea name="requirements" rows="3" placeholder="Contoh: Lulusan SMK Teknik Komputer / Otomotif, menguasai dasar kelistrikan, teliti, siap ditempatkan sistem shift..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeCreateOppModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Publikasikan Lowongan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Opportunity -->
<div id="editOppModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-2xl max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Perbarui Lowongan Karir</h3>
                    <p class="text-[11px] text-bluedark/50">Sesuaikan batas pendaftaran, persyaratan, atau status lowongan</p>
                </div>
            </div>
            <button type="button" onclick="closeEditOppModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form id="editOppForm" method="POST" action="" class="overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')

            <!-- Perusahaan Mitra -->
            <div>
                <label class="f-label text-xs">Perusahaan Mitra Industri <span class="text-rose-500">*</span></label>
                <select id="editOppCompany" name="company_id" required class="f-select text-xs w-full">
                    @foreach($allCompanies as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Posisi / Judul -->
            <div>
                <label class="f-label text-xs">Posisi / Judul Lowongan <span class="text-rose-500">*</span></label>
                <input type="text" id="editOppTitle" name="title" required class="f-input text-xs w-full">
            </div>

            <!-- Tipe Lowongan & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Tipe Lowongan <span class="text-rose-500">*</span></label>
                    <select id="editOppType" name="type" required class="f-select text-xs w-full">
                        <option value="JOB">Pekerjaan Penuh (Alumni)</option>
                        <option value="INTERNSHIP">Magang PKL / Prakerin</option>
                    </select>
                </div>

                <div>
                    <label class="f-label text-xs">Status Lowongan <span class="text-rose-500">*</span></label>
                    <select id="editOppStatus" name="status" required class="f-select text-xs w-full">
                        <option value="OPEN">Dibuka (Open)</option>
                        <option value="CLOSED">Ditutup (Closed)</option>
                    </select>
                </div>
            </div>

            <!-- Lokasi & Link Lamaran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Lokasi Penempatan</label>
                    <input type="text" id="editOppLocation" name="location" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Link Pendaftaran / Form Online</label>
                    <input type="url" id="editOppLink" name="application_link" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Tanggal Buka & Tanggal Tutup -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Tanggal Dibuka</label>
                    <input type="date" id="editOppOpenDate" name="open_date" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Tanggal Ditutup (Deadline)</label>
                    <input type="date" id="editOppCloseDate" name="close_date" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Deskripsi Tugas -->
            <div>
                <label class="f-label text-xs">Deskripsi Tugas &amp; Pekerjaan</label>
                <textarea id="editOppDescription" name="description" rows="3" class="f-textarea text-xs w-full"></textarea>
            </div>

            <!-- Kualifikasi & Persyaratan -->
            <div>
                <label class="f-label text-xs">Kualifikasi &amp; Persyaratan Pelamar</label>
                <textarea id="editOppRequirements" name="requirements" rows="3" class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeEditOppModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================
     MODAL PREVIEW OPPORTUNITY (IDENTIK DENGAN TAMPILAN PUBLIK /karir)
     ======================================================================== -->
<div id="previewOppModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-2xl max-h-[92vh] w-full">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-3.5 border-b border-bluelight bg-slate-50/90 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-sm text-bluedark">Pratinjau Tampilan Lowongan di Web Publik</h3>
                    <p class="text-[11px] text-bluedark/50">Simulasi tampilan kartu lowongan seperti yang dilihat pengunjung website di /karir</p>
                </div>
            </div>

            <button type="button" onclick="closePreviewOppModal()"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-100 text-lg font-bold leading-none">&times;</button>
        </div>

        <!-- Scrollable Modal Body (Sesuai Kartu Publik public/career/index.blade.php) -->
        <div class="p-6 sm:p-8 overflow-y-auto bg-slate-50/40">
            <p class="text-xs text-center text-bluedark/50 mb-4">Simulasi kartu lowongan pada halaman publik (<code class="bg-slate-100 px-1 py-0.5 rounded text-blueprim">/karir</code>):</p>

            <article class="p-5 sm:p-6 bg-white rounded-3xl border border-bluelight shadow-card card-hover transition-all">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <!-- Logo & Badges Row (100% SVG, Zero Emoji) -->
                        <div class="flex items-center gap-2.5 mb-2.5">
                            <div class="w-9 h-9 rounded-xl bg-bluelight/70 border border-bluelight/80 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                <img id="pvOppLogo" src="" alt="Logo" class="w-full h-full object-contain">
                                <span id="pvOppLogoFallback" class="text-blueprim flex items-center justify-center">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="2" y="7" width="20" height="14" rx="2" />
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="pvOppTypeBadge" class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                    Magang
                                </span>
                                <span id="pvOppCompanyIndustry" class="text-xs text-bluedark/50"></span>
                            </div>
                        </div>

                        <!-- Judul Lowongan -->
                        <h3 id="pvOppTitle" class="font-heading font-semibold text-bluedark mt-2 leading-snug text-lg sm:text-xl">
                            Posisi Lowongan
                        </h3>

                        <!-- Perusahaan & Lokasi -->
                        <p id="pvOppCompanyLocation" class="text-xs text-bluedark/50 mt-1.5">
                            PT Perusahaan · Lokasi
                        </p>

                        <!-- Deskripsi Pekerjaan -->
                        <p id="pvOppDescription" class="text-sm text-bluedark/65 mt-3 leading-relaxed whitespace-pre-line"></p>

                        <!-- Kotak Persyaratan & Kualifikasi (Identik Public) -->
                        <div id="pvOppReqBox" class="mt-4 p-4 rounded-2xl bg-bluelight/40 border border-bluelight text-xs text-bluedark/75" style="display: none;">
                            <p class="font-heading font-semibold text-bluedark text-xs mb-1.5 flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-blueprim" aria-hidden="true">
                                    <polyline points="9 11 12 14 22 4" />
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                </svg>
                                <span>Kualifikasi &amp; Persyaratan:</span>
                            </p>
                            <p id="pvOppRequirements" class="whitespace-pre-line leading-relaxed"></p>
                        </div>
                    </div>

                    <!-- Tanggal Ditutup & Tombol Lamar -->
                    <div class="shrink-0 text-right">
                        <div id="pvOppCloseDateWrapper">
                            <p class="text-[11px] text-bluedark/45">
                                Ditutup<br>
                                <span id="pvOppCloseDate" class="font-heading font-semibold text-bluedark/70">
                                    -
                                </span>
                            </p>
                        </div>

                        <a id="pvOppApplyLink" href="#" target="_blank" rel="noopener noreferrer"
                            class="mt-3 inline-flex items-center gap-1.5 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-semibold text-xs px-4 py-2 rounded-full shadow-2xs">
                            <span>Lamar</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                <path d="M7 17 17 7M9 7h8v8" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-bluelight bg-slate-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <span id="pvOppStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">Dibuka</span>
                <span class="inline-flex items-center gap-1 text-xs text-bluedark/60">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span><strong id="pvOppApplicantCount">0</strong> Pelamar terdaftar</span>
                </span>
            </div>
            <button type="button" onclick="closePreviewOppModal()" class="btn btn-primary btn-sm text-xs font-semibold px-4">Tutup Pratinjau</button>
        </div>
    </div>
</div>

<!-- Modal Delete Opportunity -->
<div id="deleteOppModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-md">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Lowongan Karir?</h3>
                <p class="text-xs text-bluedark/60 mt-1.5 leading-relaxed">
                    Anda yakin ingin menghapus lowongan <strong id="deleteOppTitle" class="text-bluedark"></strong>? Data pelamar yang terkait akan ikut terhapus.
                </p>
            </div>
            <form id="deleteOppForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-center gap-2 pt-2">
                    <button type="button" onclick="closeDeleteOppModal()" class="btn btn-outline btn-sm text-xs">Batalkan</button>
                    <button type="submit" class="btn btn-sm text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white px-5">Ya, Hapus Lowongan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================
     MODALS FOR TAB 2: COMPANIES
     ======================================================================== -->
<!-- Modal Create Company -->
<div id="createCompModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-xl max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Tambah Mitra Perusahaan / DUDI</h3>
                    <p class="text-[11px] text-bluedark/50">Daftarkan mitra industri rekanan kerja sama SMK Negeri 2 Karanganyar</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateCompModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.career.companies.store') }}" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf

            <div>
                <label class="f-label text-xs">Nama Perusahaan / Industri <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: PT. Astra Honda Motor / PT. Telkom Indonesia" class="f-input text-xs w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Bidang / Sektor Industri</label>
                    <input type="text" name="industry" placeholder="Contoh: Manufaktur Otomotif / Telekomunikasi" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Telepon Perusahaan</label>
                    <input type="text" name="phone" placeholder="021-xxxxxxxx / 08xxxxxxxx" class="f-input text-xs w-full">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Email Resmi</label>
                    <input type="email" name="email" placeholder="hrd@perusahaan.com" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Website Resmi</label>
                    <input type="url" name="website" placeholder="https://perusahaan.com" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Upload Logo Perusahaan (Dropzone) -->
            <div>
                <label class="f-label text-xs mb-1">Logo Perusahaan (Opsional)</label>
                <div id="createCompDropzone" class="car-dropzone" onclick="document.getElementById('createCompLogoInput').click()">
                    <input type="file" id="createCompLogoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="hidden" onchange="handleCompFilePicked('create', this.files)">
                    
                    <div id="createCompDropzonePrompt" class="space-y-1.5 py-2">
                        <div class="w-8 h-8 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Pilih atau Tarik Logo ke sini</div>
                        <div class="text-[10px] text-bluedark/50">Maks. 4MB (PNG, JPG, SVG, WEBP)</div>
                    </div>

                    <div id="createCompDropzonePreview" style="display: none;" class="space-y-2">
                        <div class="w-20 h-20 mx-auto rounded-xl overflow-hidden border border-bluelight bg-white p-1">
                            <img id="createCompPreviewImg" src="" alt="Pratinjau" class="w-full h-full object-contain">
                        </div>
                        <div class="flex items-center justify-center gap-2 text-xs">
                            <span id="createCompFileName" class="text-[11px] text-bluedark/70 font-mono truncate max-w-[180px]"></span>
                            <button type="button" onclick="event.stopPropagation(); removeCompFile('create')" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label class="f-label text-xs">Alamat Kantor / Pabrik</label>
                <textarea name="address" rows="2" placeholder="Jl. Raya Industri Km. ..., Kawasan Industri ..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeCreateCompModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Mitra Perusahaan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Company -->
<div id="editCompModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-xl max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Perbarui Data Mitra Perusahaan</h3>
                    <p class="text-[11px] text-bluedark/50">Ubah kontak, alamat, logo, atau bidang industri</p>
                </div>
            </div>
            <button type="button" onclick="closeEditCompModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form id="editCompForm" method="POST" action="" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="f-label text-xs">Nama Perusahaan / Industri <span class="text-rose-500">*</span></label>
                <input type="text" id="editCompName" name="name" required class="f-input text-xs w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Bidang / Sektor Industri</label>
                    <input type="text" id="editCompIndustry" name="industry" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Telepon Perusahaan</label>
                    <input type="text" id="editCompPhone" name="phone" class="f-input text-xs w-full">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Email Resmi</label>
                    <input type="email" id="editCompEmail" name="email" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Website Resmi</label>
                    <input type="url" id="editCompWebsite" name="website" class="f-input text-xs w-full">
                </div>
            </div>

            <!-- Logo Saat Ini & Dropzone -->
            <div>
                <label class="f-label text-xs mb-1">Logo Perusahaan</label>
                
                <div id="editCompCurrentLogoWrapper" style="display: none;" class="mb-3 p-3 rounded-xl bg-slate-50 border border-bluelight flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img id="editCompCurrentLogoImg" src="" alt="Logo saat ini" class="w-12 h-12 object-contain rounded-lg border border-bluelight bg-white p-1">
                        <div>
                            <div class="text-xs font-semibold text-bluedark">Logo Perusahaan Saat Ini</div>
                            <div class="text-[10px] text-bluedark/50">Akan diganti jika Anda mengunggah logo baru di bawah</div>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-800 cursor-pointer">
                        <input type="checkbox" name="remove_logo" id="editCompRemoveLogo" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-[11px] font-medium">Hapus Logo</span>
                    </label>
                </div>

                <div id="editCompDropzone" class="car-dropzone" onclick="document.getElementById('editCompLogoInput').click()">
                    <input type="file" id="editCompLogoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="hidden" onchange="handleCompFilePicked('edit', this.files)">
                    
                    <div id="editCompDropzonePrompt" class="space-y-1.5 py-2">
                        <div class="w-8 h-8 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="text-xs font-bold text-bluedark">Pilih atau Tarik Logo Baru</div>
                        <div class="text-[10px] text-bluedark/50">Biarkan kosong jika tidak ingin mengubah logo</div>
                    </div>

                    <div id="editCompDropzonePreview" style="display: none;" class="space-y-2">
                        <div class="w-20 h-20 mx-auto rounded-xl overflow-hidden border border-bluelight bg-white p-1">
                            <img id="editCompPreviewImg" src="" alt="Pratinjau" class="w-full h-full object-contain">
                        </div>
                        <div class="flex items-center justify-center gap-2 text-xs">
                            <span id="editCompFileName" class="text-[11px] text-bluedark/70 font-mono truncate max-w-[180px]"></span>
                            <button type="button" onclick="event.stopPropagation(); removeCompFile('edit')" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold">
                                Batalkan Logo Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label class="f-label text-xs">Alamat Kantor / Pabrik</label>
                <textarea id="editCompAddress" name="address" rows="2" class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeEditCompModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Company -->
<div id="deleteCompModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-md">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Mitra Perusahaan?</h3>
                <p class="text-xs text-bluedark/60 mt-1.5 leading-relaxed">
                    Anda yakin ingin menghapus <strong id="deleteCompName" class="text-bluedark"></strong>? Seluruh lowongan yang terkait perusahaan ini juga akan terhapus.
                </p>
            </div>
            <form id="deleteCompForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-center gap-2 pt-2">
                    <button type="button" onclick="closeDeleteCompModal()" class="btn btn-outline btn-sm text-xs">Batalkan</button>
                    <button type="submit" class="btn btn-sm text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white px-5">Ya, Hapus Mitra</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================
     MODALS FOR TAB 3: SERVICES
     ======================================================================== -->
<!-- Modal Create Service -->
<div id="createServModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-lg max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Tambah Layanan Pendampingan BKK</h3>
                    <p class="text-[11px] text-bluedark/50">Program bimbingan karir, pelatihan, atau penyaluran kerja alumni</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateServModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.career.services.store') }}" class="overflow-y-auto p-6 space-y-4">
            @csrf

            <div>
                <label class="f-label text-xs">Judul Layanan BKK <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Konseling Karir &amp; Pemetaan Bakat Siswa" class="f-input text-xs w-full">
            </div>

            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Ikon Layanan</label>
                    <input type="text" name="icon" placeholder="briefcase / compass / users" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="1" min="0" class="f-input text-xs w-full">
                </div>
            </div>

            <div>
                <label class="f-label text-xs">Deskripsi Singkat</label>
                <textarea name="description" rows="2" placeholder="Uraikan ringkasan layanan yang ditampilkan pada kartu informasi..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <div>
                <label class="f-label text-xs">Konten / Uraian Lengkap (Opsional)</label>
                <textarea name="content" rows="4" placeholder="Detail prosedur layanan, jadwal konsultasi, narahubung BKK..." class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-bluelight flex items-center gap-2">
                <input type="checkbox" name="is_active" id="createServActive" value="1" checked class="rounded text-blueprim focus:ring-blueprim">
                <label for="createServActive" class="text-xs font-semibold text-bluedark cursor-pointer">
                    Aktifkan layanan ini di halaman publik
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeCreateServModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Layanan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Service -->
<div id="editServModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-lg max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Perbarui Layanan BKK</h3>
                    <p class="text-[11px] text-bluedark/50">Ubah judul, urutan, ikon, atau isi deskripsi layanan</p>
                </div>
            </div>
            <button type="button" onclick="closeEditServModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form id="editServForm" method="POST" action="" class="overflow-y-auto p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="f-label text-xs">Judul Layanan BKK <span class="text-rose-500">*</span></label>
                <input type="text" id="editServTitle" name="title" required class="f-input text-xs w-full">
            </div>

            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label text-xs">Ikon Layanan</label>
                    <input type="text" id="editServIcon" name="icon" class="f-input text-xs w-full">
                </div>

                <div>
                    <label class="f-label text-xs">Urutan Tampil</label>
                    <input type="number" id="editServSortOrder" name="sort_order" min="0" class="f-input text-xs w-full">
                </div>
            </div>

            <div>
                <label class="f-label text-xs">Deskripsi Singkat</label>
                <textarea id="editServDescription" name="description" rows="2" class="f-textarea text-xs w-full"></textarea>
            </div>

            <div>
                <label class="f-label text-xs">Konten / Uraian Lengkap (Opsional)</label>
                <textarea id="editServContent" name="content" rows="4" class="f-textarea text-xs w-full"></textarea>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-bluelight flex items-center gap-2">
                <input type="checkbox" name="is_active" id="editServActive" value="1" class="rounded text-blueprim focus:ring-blueprim">
                <label for="editServActive" class="text-xs font-semibold text-bluedark cursor-pointer">
                    Aktifkan layanan ini di halaman publik
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-bluelight">
                <button type="button" onclick="closeEditServModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold px-5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Service -->
<div id="deleteServModal" class="car-modal-overlay">
    <div class="car-modal-dialog max-w-md">
        <div class="p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-bold text-lg text-bluedark">Hapus Layanan BKK?</h3>
                <p class="text-xs text-bluedark/60 mt-1.5 leading-relaxed">
                    Anda yakin ingin menghapus layanan <strong id="deleteServTitle" class="text-bluedark"></strong>?
                </p>
            </div>
            <form id="deleteServForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-center gap-2 pt-2">
                    <button type="button" onclick="closeDeleteServModal()" class="btn btn-outline btn-sm text-xs">Batalkan</button>
                    <button type="submit" class="btn btn-sm text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white px-5">Ya, Hapus Layanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ==========================================================================
   CAREER & BKK JAVASCRIPT CONTROLS
   ========================================================================== */

// --- Opportunities Modals ---
function openCreateOppModal() {
    document.getElementById('createOppModal').classList.add('show');
}
function closeCreateOppModal() {
    document.getElementById('createOppModal').classList.remove('show');
}

function openEditOppModal(id) {
    fetch('{{ url('/admin/cms/career/opportunities') }}/' + id + '/preview', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('editOppForm').action = '{{ url('/admin/cms/career/opportunities') }}/' + data.id;
        document.getElementById('editOppCompany').value = data.company_id;
        document.getElementById('editOppTitle').value = data.title;
        document.getElementById('editOppType').value = data.type;
        document.getElementById('editOppStatus').value = data.status;
        document.getElementById('editOppLocation').value = data.location || '';
        document.getElementById('editOppLink').value = data.application_link || '';
        document.getElementById('editOppOpenDate').value = data.open_date || '';
        document.getElementById('editOppCloseDate').value = data.close_date || '';
        document.getElementById('editOppDescription').value = data.description || '';
        document.getElementById('editOppRequirements').value = data.requirements || '';

        document.getElementById('editOppModal').classList.add('show');
    })
    .catch(err => {
        console.error('Error fetching opportunity:', err);
        alert('Gagal mengambil data lowongan.');
    });
}
function closeEditOppModal() {
    document.getElementById('editOppModal').classList.remove('show');
}

function previewOpp(id) {
    fetch('{{ url('/admin/cms/career/opportunities') }}/' + id + '/preview', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        // Logo & Fallback
        const oppLogo = document.getElementById('pvOppLogo');
        const oppFallback = document.getElementById('pvOppLogoFallback');
        if (data.company_logo) {
            oppLogo.src = data.company_logo;
            oppLogo.style.display = 'block';
            oppFallback.style.display = 'none';
        } else {
            oppLogo.src = '';
            oppLogo.style.display = 'none';
            oppFallback.style.display = 'flex';
        }

        // Badges & Info
        document.getElementById('pvOppTypeBadge').textContent = data.type_label || (data.type === 'INTERNSHIP' ? 'Magang' : 'Pekerjaan');
        document.getElementById('pvOppCompanyIndustry').textContent = data.company_industry || '';
        document.getElementById('pvOppTitle').textContent = data.title;
        document.getElementById('pvOppCompanyLocation').textContent = (data.company_name || 'Mitra Industri') + (data.location ? ' · ' + data.location : '');
        document.getElementById('pvOppDescription').textContent = data.description || 'Tidak ada deskripsi lowongan.';

        // Requirements Box
        const reqBox = document.getElementById('pvOppReqBox');
        if (data.requirements) {
            document.getElementById('pvOppRequirements').textContent = data.requirements;
            reqBox.style.display = 'block';
        } else {
            reqBox.style.display = 'none';
        }

        // Close Date
        const closeWrapper = document.getElementById('pvOppCloseDateWrapper');
        if (data.close_date_formatted) {
            document.getElementById('pvOppCloseDate').textContent = data.close_date_formatted;
            closeWrapper.style.display = 'block';
        } else {
            closeWrapper.style.display = 'none';
        }

        // Apply Link Button
        const applyBtn = document.getElementById('pvOppApplyLink');
        if (data.application_link) {
            applyBtn.href = data.application_link;
            applyBtn.style.display = 'inline-flex';
        } else {
            applyBtn.style.display = 'none';
        }

        // Status Badge & Applicant Count
        document.getElementById('pvOppApplicantCount').textContent = data.applications_count || 0;
        const statusBadge = document.getElementById('pvOppStatusBadge');
        statusBadge.textContent = data.status_label || (data.status === 'OPEN' ? 'Dibuka' : 'Ditutup');
        if (data.status === 'OPEN') {
            statusBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200';
        } else {
            statusBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
        }

        document.getElementById('previewOppModal').classList.add('show');
    })
    .catch(err => {
        console.error('Error fetching preview:', err);
        alert('Gagal memuat pratinjau lowongan.');
    });
}
function closePreviewOppModal() {
    document.getElementById('previewOppModal').classList.remove('show');
}

function openDeleteOppModal(id, title) {
    document.getElementById('deleteOppForm').action = '{{ url('/admin/cms/career/opportunities') }}/' + id;
    document.getElementById('deleteOppTitle').textContent = '"' + title + '"';
    document.getElementById('deleteOppModal').classList.add('show');
}
function closeDeleteOppModal() {
    document.getElementById('deleteOppModal').classList.remove('show');
}

function toggleOppStatus(id) {
    fetch('{{ url('/admin/cms/career/opportunities') }}/' + id + '/toggle-status', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('oppStatusBtn-' + id);
            const text = document.getElementById('oppStatusText-' + id);
            text.textContent = data.status === 'OPEN' ? 'Dibuka' : 'Ditutup';

            if (data.status === 'OPEN') {
                btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100';
                btn.querySelector('span:first-child').className = 'w-1.5 h-1.5 rounded-full bg-emerald-500';
            } else {
                btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200';
                btn.querySelector('span:first-child').className = 'w-1.5 h-1.5 rounded-full bg-slate-400';
            }
        }
    })
    .catch(err => {
        console.error('Error toggling status:', err);
    });
}

// --- Companies Modals ---
function openCreateCompModal() {
    document.getElementById('createCompModal').classList.add('show');
}
function closeCreateCompModal() {
    document.getElementById('createCompModal').classList.remove('show');
}

function openEditCompModal(comp) {
    document.getElementById('editCompForm').action = '{{ url('/admin/cms/career/companies') }}/' + comp.id;
    document.getElementById('editCompName').value = comp.name || '';
    document.getElementById('editCompIndustry').value = comp.industry || '';
    document.getElementById('editCompPhone').value = comp.phone || '';
    document.getElementById('editCompEmail').value = comp.email || '';
    document.getElementById('editCompWebsite').value = comp.website || '';
    document.getElementById('editCompAddress').value = comp.address || '';

    // Reset logo inputs
    removeCompFile('edit');
    const removeLogoCb = document.getElementById('editCompRemoveLogo');
    if (removeLogoCb) removeLogoCb.checked = false;

    // Current logo
    const curWrapper = document.getElementById('editCompCurrentLogoWrapper');
    const curImg = document.getElementById('editCompCurrentLogoImg');
    if (comp.logo_url) {
        curImg.src = comp.logo_url;
        curWrapper.style.display = 'flex';
    } else {
        curWrapper.style.display = 'none';
    }

    document.getElementById('editCompModal').classList.add('show');
}
function closeEditCompModal() {
    document.getElementById('editCompModal').classList.remove('show');
}

function openDeleteCompModal(id, name) {
    document.getElementById('deleteCompForm').action = '{{ url('/admin/cms/career/companies') }}/' + id;
    document.getElementById('deleteCompName').textContent = '"' + name + '"';
    document.getElementById('deleteCompModal').classList.add('show');
}
function closeDeleteCompModal() {
    document.getElementById('deleteCompModal').classList.remove('show');
}

// --- Services Modals ---
function openCreateServModal() {
    document.getElementById('createServModal').classList.add('show');
}
function closeCreateServModal() {
    document.getElementById('createServModal').classList.remove('show');
}

function openEditServModal(serv) {
    document.getElementById('editServForm').action = '{{ url('/admin/cms/career/services') }}/' + serv.id;
    document.getElementById('editServTitle').value = serv.title || '';
    document.getElementById('editServIcon').value = serv.icon || '';
    document.getElementById('editServSortOrder').value = serv.sort_order ?? 0;
    document.getElementById('editServDescription').value = serv.description || '';
    document.getElementById('editServContent').value = serv.content || '';
    document.getElementById('editServActive').checked = !!serv.is_active;

    document.getElementById('editServModal').classList.add('show');
}
function closeEditServModal() {
    document.getElementById('editServModal').classList.remove('show');
}

function openDeleteServModal(id, title) {
    document.getElementById('deleteServForm').action = '{{ url('/admin/cms/career/services') }}/' + id;
    document.getElementById('deleteServTitle').textContent = '"' + title + '"';
    document.getElementById('deleteServModal').classList.add('show');
}
function closeDeleteServModal() {
    document.getElementById('deleteServModal').classList.remove('show');
}

function toggleServStatus(id) {
    fetch('{{ url('/admin/cms/career/services') }}/' + id + '/toggle-status', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('servStatusBtn-' + id);
            const text = document.getElementById('servStatusText-' + id);
            text.textContent = data.is_active ? 'Aktif' : 'Nonaktif';

            if (data.is_active) {
                btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100';
                btn.querySelector('span:first-child').className = 'w-1.5 h-1.5 rounded-full bg-emerald-500';
            } else {
                btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10.5px] transition-all bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200';
                btn.querySelector('span:first-child').className = 'w-1.5 h-1.5 rounded-full bg-slate-400';
            }
        }
    })
    .catch(err => {
        console.error('Error toggling service status:', err);
    });
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const modalIds = [
        'createOppModal', 'editOppModal', 'previewOppModal', 'deleteOppModal',
        'createCompModal', 'editCompModal', 'deleteCompModal',
        'createServModal', 'editServModal', 'deleteServModal'
    ];
    modalIds.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) {
            modal.classList.remove('show');
        }
    });
});

// Company Logo Dropzone Handlers
function handleCompFilePicked(mode, files) {
    if (!files || files.length === 0) return;
    const file = files[0];

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById(mode + 'CompPreviewImg').src = e.target.result;
        document.getElementById(mode + 'CompFileName').textContent = file.name;
        document.getElementById(mode + 'CompDropzonePrompt').style.display = 'none';
        document.getElementById(mode + 'CompDropzonePreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function removeCompFile(mode) {
    const input = document.getElementById(mode + 'CompLogoInput');
    if (input) input.value = '';
    const preview = document.getElementById(mode + 'CompDropzonePreview');
    if (preview) preview.style.display = 'none';
    const prompt = document.getElementById(mode + 'CompDropzonePrompt');
    if (prompt) prompt.style.display = 'block';
}

['create', 'edit'].forEach(mode => {
    const dropzone = document.getElementById(mode + 'CompDropzone');
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
            document.getElementById(mode + 'CompLogoInput').files = files;
            handleCompFilePicked(mode, files);
        }
    }, false);
});
</script>
@endpush
@endsection
