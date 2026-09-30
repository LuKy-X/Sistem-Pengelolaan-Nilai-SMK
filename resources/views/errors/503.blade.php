@extends('layouts.guest')

@section('title', 'Sistem Sedang Maintenance')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-6 py-16 bg-[#F7FBFF]">
        <div class="w-full max-w-lg text-center">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo {{ $schoolName ?? config('app.name') }}" class="w-14 h-14 mx-auto object-contain">
            <p class="font-heading font-extrabold text-6xl sm:text-7xl text-bluelight mt-6">503</p>
            <h1 class="font-heading font-bold text-2xl sm:text-3xl text-bluedark mt-4">Sistem sedang dalam pemeliharaan</h1>
            <p class="text-sm text-bluedark/60 mt-3 leading-relaxed">
                Layanan sedang diperbarui agar dapat melayani Anda dengan lebih baik. Silakan kembali beberapa saat lagi.
            </p>

            <a href="{{ route('public.home') }}"
                class="mt-8 inline-flex items-center gap-2 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-semibold text-sm px-7 py-3 rounded-full">
                Kembali ke beranda
            </a>
        </div>
    </div>
@endsection
