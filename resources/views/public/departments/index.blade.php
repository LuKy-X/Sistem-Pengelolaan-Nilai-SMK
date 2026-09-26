@extends('layouts.public')

@section('title', 'Kompetensi Keahlian')
@section('meta_description', 'Daftar kompetensi keahlian yang tersedia di '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Kompetensi Keahlian"
        title="Jurusan yang Siap Membentuk Kompetensi Industri"
        description="Setiap bidang keahlian disusun bersama industri dengan pembelajaran berbasis proyek, praktikum, dan sertifikasi kompetensi."
        :breadcrumb="['Beranda' => route('public.home'), 'Kompetensi Keahlian' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @if ($departments->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="folder"
                        title="Belum ada kompetensi keahlian"
                        description="Data jurusan belum diisi oleh admin sekolah. Silakan kembali lagi nanti." />
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach ($departments as $department)
                        <x-public.department-card :department="$department" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
