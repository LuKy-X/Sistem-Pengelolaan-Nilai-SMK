@extends('layouts.guest')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-6 py-16 bg-[#F7FBFF]">
        <div class="w-full max-w-lg text-center">
            <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo {{ $schoolName ?? config('app.name') }}" class="w-14 h-14 mx-auto object-contain">
            <p class="font-heading font-extrabold text-6xl sm:text-7xl text-bluelight mt-6">404</p>
            <h1 class="font-heading font-bold text-2xl sm:text-3xl text-bluedark mt-4">Halaman tidak ditemukan</h1>
            <p class="text-sm text-bluedark/60 mt-3 leading-relaxed">
                Alamat yang Anda tuju tidak tersedia, sudah dipindahkan, atau kontennya belum dipublikasikan.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('public.home') }}"
                    class="inline-flex items-center gap-2 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-semibold text-sm px-7 py-3 rounded-full">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                        aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    Kembali
                </a>
                <a href="{{ route('public.home') }}"
                    class="inline-flex items-center gap-2 bg-white border border-bluesoft/70 text-bluedark font-heading font-semibold text-sm px-7 py-3 rounded-full hover:bg-bluelight transition-colors">
                    Beranda
                </a>
            </div>
        </div>
    </div>
@endsection
