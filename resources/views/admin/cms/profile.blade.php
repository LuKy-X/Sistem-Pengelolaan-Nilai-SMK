@extends('layouts.admin')

@section('title', 'Konfigurasi Profil Sekolah & Website')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Konfigurasi Website &amp; Profil Sekolah</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola identitas resmi sekolah, kontak, visi-misi, dan informasi publik</p>
        </div>
    </div>

    <!-- Form Profil & Konfigurasi matching template/admin/konfigurasi.html -->
    <div class="panel p-0 overflow-hidden">
        <div class="p-5 border-b border-bluelight bg-white">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Informasi Identitas &amp; Kontak Sekolah</h2>
            <p class="text-xs text-bluedark/50 mt-0.5">Data ini ditampilkan pada header, footer, dan landing page sekolah</p>
        </div>

        <form action="{{ route('admin.cms.profile.update') }}" method="POST" class="p-5 space-y-4">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Nama Sekolah</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $profile->school_name) }}" required class="f-input">
                </div>
                <div>
                    <label class="f-label">NPSN</label>
                    <input type="text" name="npsn" value="{{ old('npsn', $profile->npsn) }}" class="f-input">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Nama Kepala Sekolah</label>
                    <input type="text" name="principal_name" value="{{ old('principal_name', $profile->principal_name) }}" class="f-input">
                </div>
                <div>
                    <label class="f-label">No. Telepon / Fax</label>
                    <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" class="f-input">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Email Resmi Sekolah</label>
                    <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="f-input">
                </div>
                <div>
                    <label class="f-label">Website Resmi</label>
                    <input type="url" name="website" value="{{ old('website', $profile->website) }}" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Alamat Lengkap</label>
                <textarea name="address" rows="2" class="f-textarea">{{ old('address', $profile->address) }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Visi Sekolah</label>
                    <textarea name="vision" rows="3" class="f-textarea">{{ old('vision', $profile->vision) }}</textarea>
                </div>
                <div>
                    <label class="f-label">Misi Sekolah</label>
                    <textarea name="mission" rows="3" class="f-textarea">{{ old('mission', $profile->mission) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-3 border-t border-bluelight">
                <button type="submit" class="btn btn-primary btn-sm">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
