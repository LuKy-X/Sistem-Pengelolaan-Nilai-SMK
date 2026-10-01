@extends('layouts.public')

@section('title', 'Kompetensi Keahlian')
@section('meta_description', 'Daftar kompetensi keahlian yang tersedia di '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Kompetensi Keahlian"
        title="Jurusan yang Siap Membentuk Kompetensi Industri"
        description="Setiap bidang keahlian disusun bersama industri dengan pembelajaran berbasis proyek, praktikum, dan sertifikasi kompetensi."
        :breadcrumb="['Beranda' => route('public.home'), 'Kompetensi Keahlian' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @if ($departments->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="folder"
                        title="Belum ada kompetensi keahlian"
                        description="Data jurusan belum diisi oleh admin sekolah. Silakan kembali lagi nanti." />
                </div>
            @else
                {{-- `stagger-group` is required: the department card is a `.stagger-item`
                     and stays hidden unless GSAP is handed an element to animate. --}}
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 stagger-group">
                    @foreach ($departments as $department)
                        <x-public.department-card :department="$department" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-public.cta-band
        title="Belum yakin memilih jurusan?"
        description="Tim BKK kami siap membahas kecocokan minat dan bakat kamu dengan program keahlian yang tersedia."
        action-label="Lihat Alur Pendaftaran"
        :action-url="route('public.ppdb.index')"
        secondary-label="Tanya BKK"
        :secondary-url="route('public.career.index')" />
@endsection
