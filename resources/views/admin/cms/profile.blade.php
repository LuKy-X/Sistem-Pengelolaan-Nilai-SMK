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
            <button type="button" onclick="document.getElementById('profileForm').submit()" class="btn btn-primary btn-sm flex items-center gap-2 text-xs font-bold shadow-md hover:shadow-lg">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- Form Utama -->
    <form id="profileForm" action="{{ route('admin.cms.profile.update') }}" method="POST" class="max-w-4xl mx-auto space-y-5">
        @csrf

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
                            <input type="text" name="principal_name" id="inpPrincipal" value="{{ old('principal_name', $profile->principal_name) }}" placeholder="Sukidi, S.Pd., M.Pd." class="f-input text-xs w-full" oninput="syncLivePreview()">
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

        <!-- Bottom Action Bar -->
        <div class="panel p-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50 border border-bluelight rounded-2xl">
            <div class="text-xs text-bluedark/60 text-center sm:text-left">
                Pastikan data identitas sekolah sudah valid sebelum disimpan.
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button" onclick="location.reload()" class="btn btn-outline btn-sm text-xs">
                    Reset
                </button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-bold flex items-center gap-1.5 shadow-xs">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
