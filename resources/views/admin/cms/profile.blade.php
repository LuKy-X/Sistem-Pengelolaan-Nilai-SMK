@extends('layouts.admin')

@section('title', 'Konfigurasi Profil Sekolah & Website')

@push('styles')
<style>
    /* Hilangkan scrollbar di seluruh panel halaman profil */
    .prof-wrap, .prof-wrap * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .prof-wrap *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>
@endpush

@section('content')
<div class="prof-wrap space-y-5">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Identitas &amp; Profil Sekolah</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    CMS Publik
                </span>
            </div>
            <p class="text-sm text-bluedark/60 mt-1">
                Kelola data identitas resmi SMK Negeri 2 Karanganyar, kontak lembaga, deskripsi naratif, serta visi, misi, dan kilas balik sejarah pendirian sekolah.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('public.profile') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm flex items-center gap-2 text-xs font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Lihat di Web</span>
            </a>
            <button type="button" onclick="document.getElementById('profileForm').submit()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- Form Utama -->
    <form id="profileForm" action="{{ route('admin.cms.profile.update') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- LEFT 2 COLUMNS: Form Fields -->
            <div class="lg:col-span-2 space-y-5">

                <!-- CARD 1: Identitas Pokok & Kepala Sekolah -->
                <div class="panel p-0 overflow-hidden border border-bluelight shadow-xs">
                    <div class="px-5 py-4 border-b border-bluelight bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blueprim flex items-center justify-center font-bold">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <div>
                                <h2 class="font-heading font-bold text-sm text-bluedark">Identitas Pokok &amp; Pimpinan Sekolah</h2>
                                <p class="text-[11px] text-bluedark/50">Nama resmi sekolah, NPSN, dan nama kepala sekolah aktif</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4 bg-white">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="f-label text-xs">Nama Resmi Sekolah <span class="text-rose-500">*</span></label>
                                <input type="text" name="school_name" id="inpSchoolName" value="{{ old('school_name', $profile->school_name) }}" required placeholder="SMK Negeri 2 Karanganyar" class="f-input text-xs w-full" oninput="syncLivePreview()">
                            </div>
                            <div>
                                <label class="f-label text-xs">Nomor Pokok Sekolah Nasional (NPSN)</label>
                                <input type="text" name="npsn" id="inpNpsn" value="{{ old('npsn', $profile->npsn) }}" placeholder="Contoh: 20312071" class="f-input text-xs w-full" oninput="syncLivePreview()">
                            </div>
                        </div>

                        <div>
                            <label class="f-label text-xs">Nama Lengkap Kepala Sekolah (Beserta Gelar)</label>
                            <input type="text" name="principal_name" id="inpPrincipal" value="{{ old('principal_name', $profile->principal_name) }}" placeholder="Drs. H. Sukardi, M.Pd." class="f-input text-xs w-full" oninput="syncLivePreview()">
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Kontak Resmi & Domisili Lembaga -->
                <div class="panel p-0 overflow-hidden border border-bluelight shadow-xs">
                    <div class="px-5 py-4 border-b border-bluelight bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blueprim flex items-center justify-center font-bold">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <h2 class="font-heading font-bold text-sm text-bluedark">Kontak Resmi &amp; Alamat Domisili</h2>
                                <p class="text-[11px] text-bluedark/50">Informasi saluran komunikasi dan lokasi resmi yang ditampilkan pada portal publik</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4 bg-white">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="f-label text-xs">No. Telepon / Fax</label>
                                <input type="text" name="phone" id="inpPhone" value="{{ old('phone', $profile->phone) }}" placeholder="0271-494549" class="f-input text-xs w-full" oninput="syncLivePreview()">
                            </div>
                            <div>
                                <label class="f-label text-xs">Alamat Email Resmi</label>
                                <input type="email" name="email" id="inpEmail" value="{{ old('email', $profile->email) }}" placeholder="info@smkn2-kra.sch.id" class="f-input text-xs w-full" oninput="syncLivePreview()">
                            </div>
                            <div>
                                <label class="f-label text-xs">Website Resmi</label>
                                <input type="text" name="website" id="inpWebsite" value="{{ old('website', $profile->website) }}" placeholder="https://smkn2kra.sch.id" class="f-input text-xs w-full" oninput="syncLivePreview()">
                            </div>
                        </div>

                        <div>
                            <label class="f-label text-xs">Alamat Lengkap Sekolah</label>
                            <textarea name="address" id="inpAddress" rows="2" placeholder="Jl. Yos Sudarso, Karanganyar, Jawa Tengah" class="f-textarea text-xs w-full" oninput="syncLivePreview()">{{ old('address', $profile->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Narasi Profil, Visi, Misi & Sejarah Sekolah -->
                <div class="panel p-0 overflow-hidden border border-bluelight shadow-xs">
                    <div class="px-5 py-4 border-b border-bluelight bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blueprim flex items-center justify-center font-bold">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                            <div>
                                <h2 class="font-heading font-bold text-sm text-bluedark">Profil Naratif, Visi, Misi &amp; Sejarah</h2>
                                <p class="text-[11px] text-bluedark/50">Deskripsi selayang pandang, visi, misi, dan kilas balik sejarah pendirian</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4 bg-white">
                        <!-- Deskripsi / Selayang Pandang -->
                        <div>
                            <label class="f-label text-xs flex items-center justify-between">
                                <span>Deskripsi / Selayang Pandang Profil Sekolah</span>
                                <span class="text-[10px] text-bluedark/50 font-normal">Tampil di hero landing page &amp; ringkasan profil</span>
                            </label>
                            <textarea name="description" id="inpDescription" rows="3" placeholder="Tuliskan gambaran umum keunggulan dan profil SMK Negeri 2 Karanganyar..." class="f-textarea text-xs w-full" oninput="syncLivePreview()">{{ old('description', $profile->description) }}</textarea>
                        </div>

                        <!-- Visi & Misi -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="f-label text-xs">Visi Sekolah</label>
                                <textarea name="vision" rows="4" placeholder="Menjadi Sekolah Menengah Kejuruan Unggul, Berkarakter..." class="f-textarea text-xs w-full">{{ old('vision', $profile->vision) }}</textarea>
                            </div>
                            <div>
                                <label class="f-label text-xs">Misi Sekolah</label>
                                <textarea name="mission" rows="4" placeholder="1. Menyelenggarakan pendidikan vokasi berkualitas...&#10;2. Mengembangkan kerja sama erat dengan industri..." class="f-textarea text-xs w-full">{{ old('mission', $profile->mission) }}</textarea>
                            </div>
                        </div>

                        <!-- Sejarah Sekolah -->
                        <div>
                            <label class="f-label text-xs flex items-center justify-between">
                                <span>Sejarah Singkat &amp; Kilas Balik Pendirian</span>
                                <span class="text-[10px] text-bluedark/50 font-normal">Tampil lengkap di tab Profil Publik</span>
                            </label>
                            <textarea name="history" rows="5" placeholder="Tuliskan kronologi pendirian sekolah, tonggak prestasi, serta transformasi perkembangannya..." class="f-textarea text-xs w-full">{{ old('history', $profile->history) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT 1 COLUMN: Live Preview Card & Sticky Actions -->
            <div class="space-y-5">

                <!-- LIVE PREVIEW CARD -->
                <div class="panel p-0 overflow-hidden border border-bluelight shadow-xs sticky top-4">
                    <div class="px-4 py-3 bg-slate-50 border-b border-bluelight flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-heading font-bold text-xs text-bluedark">Pratinjau Kartu Publik</span>
                        </div>
                        <span class="text-[10px] text-bluedark/50">Update Real-time</span>
                    </div>

                    <!-- Miniatur Kartu -->
                    <div class="p-4 bg-white space-y-4">
                        <!-- Mini Hero Header -->
                        <div class="w-full h-24 rounded-xl overflow-hidden relative border border-slate-200 bg-bluedark p-3 text-white flex flex-col justify-end">
                            <div class="leading-tight drop-shadow-xs">
                                <div id="liveCardName" class="font-heading font-bold text-xs line-clamp-1">{{ $profile->school_name ?? 'SMK Negeri 2 Karanganyar' }}</div>
                                <div class="text-[10px] text-blue-200 font-medium">NPSN: <span id="liveCardNpsn">{{ $profile->npsn ?? '20312071' }}</span></div>
                            </div>
                        </div>

                        <!-- Data Singkat Lembaga -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-start gap-2 text-bluedark/80">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-bluedark/50">Kepala Sekolah</div>
                                    <div id="liveCardPrincipal" class="font-semibold text-bluedark">{{ $profile->principal_name ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-bluedark/80">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-bluedark/50">Kontak Telepon</div>
                                    <div id="liveCardPhone" class="font-medium text-bluedark">{{ $profile->phone ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-bluedark/80">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-bluedark/50">Email Lembaga</div>
                                    <div id="liveCardEmail" class="font-medium text-bluedark break-all">{{ $profile->email ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-bluedark/80">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-bluedark/50">Website Resmi</div>
                                    <div id="liveCardWebsite" class="font-medium text-bluedark truncate">{{ $profile->website ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-bluedark/80">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blueprim shrink-0 mt-0.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-bluedark/50">Alamat</div>
                                    <div id="liveCardAddress" class="text-[11px] text-bluedark/70 leading-snug line-clamp-2">{{ $profile->address ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi Simpan -->
                        <div class="pt-3 border-t border-bluelight space-y-2">
                            <button type="submit" class="btn btn-primary w-full py-2.5 text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-all">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Konfigurasi Profil</span>
                            </button>
                            <button type="button" onclick="location.reload()" class="btn btn-outline w-full py-2 text-xs font-medium text-bluedark/60 hover:text-bluedark">
                                Reset Perubahan
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    /**
     * Sinkronisasi Real-time Nilai Input ke Kartu Pratinjau
     */
    function syncLivePreview() {
        const nameVal = document.getElementById('inpSchoolName')?.value?.trim();
        const npsnVal = document.getElementById('inpNpsn')?.value?.trim();
        const princVal = document.getElementById('inpPrincipal')?.value?.trim();
        const phoneVal = document.getElementById('inpPhone')?.value?.trim();
        const emailVal = document.getElementById('inpEmail')?.value?.trim();
        const webVal = document.getElementById('inpWebsite')?.value?.trim();
        const addrVal = document.getElementById('inpAddress')?.value?.trim();

        if (document.getElementById('liveCardName')) {
            document.getElementById('liveCardName').textContent = nameVal || 'SMK Negeri 2 Karanganyar';
        }
        if (document.getElementById('liveCardNpsn')) {
            document.getElementById('liveCardNpsn').textContent = npsnVal || '-';
        }
        if (document.getElementById('liveCardPrincipal')) {
            document.getElementById('liveCardPrincipal').textContent = princVal || '-';
        }
        if (document.getElementById('liveCardPhone')) {
            document.getElementById('liveCardPhone').textContent = phoneVal || '-';
        }
        if (document.getElementById('liveCardEmail')) {
            document.getElementById('liveCardEmail').textContent = emailVal || '-';
        }
        if (document.getElementById('liveCardWebsite')) {
            document.getElementById('liveCardWebsite').textContent = webVal || '-';
        }
        if (document.getElementById('liveCardAddress')) {
            document.getElementById('liveCardAddress').textContent = addrVal || '-';
        }
    }
</script>
@endpush
