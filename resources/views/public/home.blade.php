@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', $schoolProfile?->description ?? 'Website resmi ' . $schoolName . ' — jurusan unggulan, karier, PPDB, dan prestasi terbaik.')

@section('content')

{{-- ======================================================
     HERO
     ====================================================== --}}
<section id="home" class="relative overflow-hidden parallax-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-6 md:pt-12">
        <div class="relative rounded-3xl md:rounded-4xl bg-gradient-to-br from-bluelight via-[#EAF4FE] to-bluesoft/70 px-5 sm:px-8 md:px-14 py-10 md:py-20 overflow-hidden">

            {{-- Background blobs --}}
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-bluesoft/40 blur-3xl parallax-layer" data-speed="0.5" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 rounded-full bg-blueprim/10 blur-3xl parallax-layer" data-speed="0.8" aria-hidden="true"></div>

            <div class="relative grid lg:grid-cols-12 gap-10 items-center">

                {{-- Left: headline + CTA --}}
                <div class="lg:col-span-6 reveal">
                    <p class="hero-badge font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-4">
                        {{ $schoolProfile?->npsn ? 'NPSN ' . $schoolProfile->npsn : 'Sekolah Menengah Kejuruan' }}
                    </p>
                    <h1 class="font-heading font-bold text-3xl sm:text-4xl md:text-5xl xl:text-[3.4rem] leading-[1.15] text-bluedark">
                        Belajar Hari Ini,<br>
                        Siap Kerja <span class="text-blueprim">Esok Hari!</span>
                    </h1>
                    <p class="mt-5 text-bluedark/70 text-base md:text-lg max-w-md leading-relaxed">
                        {{ $schoolProfile?->vision
                            ?? $schoolName . ' membekali siswa dengan kurikulum berbasis industri, sertifikasi kompetensi, dan jaringan kerja sama dunia usaha untuk masa depan karier yang nyata.' }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('public.ppdb.index') }}"
                            class="bg-blueprim hover:bg-bluedark transition-colors text-white font-heading font-medium px-6 sm:px-7 py-3 sm:py-3.5 rounded-full shadow-soft">
                            Daftar PPDB Sekarang
                        </a>
                        <a href="#jurusan"
                            class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-white border border-bluesoft/60 hover:bg-bluelight transition-colors">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2.2" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </a>
                        <span class="font-heading text-sm text-bluedark/70">Lihat Jurusan</span>
                    </div>
                </div>

                {{-- Right: hero art + floating chips --}}
                <div class="lg:col-span-6 relative reveal">
                    <div class="relative max-w-md mx-auto">
                        <div class="absolute inset-0 bg-blueprim/10 rounded-full blur-3xl scale-90" aria-hidden="true"></div>
                        <img src="{{ asset('assets/img/hero-jurusan.png') }}"
                            alt="Ilustrasi empat program keahlian {{ $schoolName }}"
                            class="relative w-full h-auto drop-shadow-2xl hero-art-parallax"
                            loading="eager">
                    </div>

                    {{-- Float chip: siswa aktif --}}
                    <div class="hero-float-chip absolute bottom-2 -left-1 sm:-left-2 md:-left-6 bg-white rounded-2xl shadow-card px-3.5 sm:px-4 py-2.5 sm:py-3 flex items-center gap-3 max-w-[13rem] sm:max-w-[15rem]">
                        <div class="flex -space-x-2 shrink-0">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white bg-bluelight grid place-items-center text-xs font-bold text-blueprim">S</span>
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white bg-blueprim/20 grid place-items-center text-xs font-bold text-blueprim">A</span>
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white bg-bluedark/20 grid place-items-center text-xs font-bold text-bluedark">R</span>
                        </div>
                        <div>
                            <p class="font-heading text-xs sm:text-sm font-semibold text-bluedark leading-tight">
                                @if ($statistics->isNotEmpty())
                                    {{ $statistics->first()->value }} Siswa Aktif
                                @else
                                    1.200+ Siswa Aktif
                                @endif
                            </p>
                            <p class="text-[11px] sm:text-xs text-bluedark/60">Belajar tiap hari</p>
                        </div>
                    </div>

                    {{-- Akreditasi badge --}}
                    <div class="absolute top-2 right-0 md:right-4 bg-bluedark text-white rounded-2xl shadow-card px-3.5 sm:px-4 py-2 sm:py-2.5">
                        <p class="font-heading text-[11px] sm:text-xs font-semibold">Akreditasi A</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ======================================================
     RINGKASAN ANGKA (dari CMS: site_statistics, section = HERO)
     ====================================================== --}}
@if ($statistics->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-10 sm:pt-12">
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-6 reveal">
            @foreach ($statistics->take(4) as $stat)
                <div class="border-l-2 border-bluelight pl-4">
                    <dt class="font-heading font-bold text-xl sm:text-2xl text-bluedark leading-none counter"
                        data-target="{{ preg_replace('/[^0-9.]/', '', $stat->value) }}"
                        data-suffix="{{ preg_replace('/[0-9.]/', '', $stat->value) }}">
                        {{ $stat->value }}
                    </dt>
                    <dd class="text-[11px] sm:text-xs text-bluedark/50 mt-1.5">{{ $stat->label }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif


{{-- ======================================================
     JURUSAN UNGGULAN
     ====================================================== --}}
<section id="jurusan" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-20 sm:pt-24 md:pt-32 scroll-mt-20">
    <div class="flex items-end justify-between gap-6 reveal">
        <div>
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Program Keahlian</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Jurusan Unggulan</h2>
            <p class="text-bluedark/60 mt-2 max-w-md text-sm sm:text-base">Empat program keahlian dengan kurikulum yang disusun bersama mitra industri.</p>
        </div>
        <a href="{{ route('public.departments.index') }}"
            class="hidden md:inline-flex items-center gap-2 font-heading font-medium text-bluedark hover:text-blueprim transition-colors shrink-0 text-sm">
            Lihat semua jurusan
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
        </a>
    </div>

    @if ($departments->isEmpty())
        <div class="mt-10 bg-white border border-bluelight rounded-3xl">
            <x-public.empty-state
                icon="folder"
                title="Belum ada kompetensi keahlian"
                description="Data jurusan belum diisi oleh admin sekolah. Silakan kembali lagi nanti." />
        </div>
    @else
        <div class="mt-10 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 stagger-group">
            @foreach ($departments as $department)
                <x-public.department-card :department="$department" />
            @endforeach
        </div>
    @endif
</section>


{{-- ======================================================
     ALUR PERJALANAN
     ====================================================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
    <div class="text-center max-w-xl mx-auto reveal">
        <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Jalan Menuju Karier, Dibuat Sederhana</h2>
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Dari pendaftaran hingga penyaluran kerja, setiap tahap dirancang agar siswa siap terjun ke dunia industri.</p>
    </div>

    <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch stagger-group">
        <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between stagger-item">
            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2" aria-hidden="true"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-semibold text-bluedark text-lg">Daftar &amp; Seleksi</h3>
                <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Isi formulir PPDB online, lengkapi berkas, dan ikuti proses seleksi sesuai jurusan pilihan.</p>
            </div>
        </div>

        <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between stagger-item">
            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-semibold text-bluedark text-lg">Belajar &amp; Sertifikasi</h3>
                <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Kurikulum berbasis industri, praktik langsung, dan sertifikasi kompetensi yang diakui dunia kerja.</p>
            </div>
        </div>

        <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between stagger-item">
            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div>
                <h3 class="font-heading font-semibold text-bluedark text-lg">Kerja &amp; Karier</h3>
                <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Praktik kerja lapangan di mitra industri, rekrutmen langsung, hingga bekal melanjutkan kuliah.</p>
            </div>
        </div>
    </div>
</section>


{{-- ======================================================
     LULUSAN TERBAIK (alumni dari backend)
     ====================================================== --}}
<section id="lulusan-terbaik" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="grid lg:grid-cols-12 gap-10 items-center">

        <div class="lg:col-span-5 relative reveal">
            <div class="rounded-3xl md:rounded-4xl overflow-hidden shadow-soft aspect-[4/5] max-w-sm mx-auto lg:mx-0 bg-bluelight">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80"
                    alt="Lulusan terbaik {{ $schoolName }}"
                    class="w-full h-full object-cover lulusan-photo-parallax"
                    loading="lazy">
            </div>
            <div class="absolute -bottom-6 right-4 sm:right-2 md:right-8 bg-white rounded-2xl shadow-card px-4 sm:px-5 py-3 sm:py-4 max-w-[11rem] sm:max-w-[13rem]">
                <p class="font-heading text-xl sm:text-2xl font-bold text-blueprim counter" data-target="96" data-suffix="%">0%</p>
                <p class="text-xs text-bluedark/60 mt-1">Lulusan terserap kerja atau kuliah dalam 6 bulan</p>
            </div>
        </div>

        <div class="lg:col-span-7 reveal mt-8 lg:mt-0">
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-3">Kisah Sukses</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-[2.6rem] leading-tight text-bluedark">
                Lulusan Terbaik Kami<br class="hidden sm:block">
                Berkarier di Perusahaan Ternama
            </h2>
            <p class="text-bluedark/60 mt-4 max-w-lg leading-relaxed text-sm sm:text-base">
                Setiap tahun, alumni {{ $schoolName }} diterima bekerja di perusahaan nasional maupun melanjutkan ke perguruan tinggi favorit berkat bekal kompetensi dan sertifikasi yang mereka bawa.
            </p>

            @if ($alumni->isNotEmpty())
                @php $firstAlumni = $alumni->first(); @endphp
                <div class="mt-8 bg-white rounded-3xl shadow-card p-5 sm:p-6 flex items-center gap-4 max-w-lg">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-bluelight grid place-items-center shrink-0">
                        <span class="font-heading font-bold text-lg text-blueprim">
                            {{ mb_substr($firstAlumni->student?->full_name ?? 'A', 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <p class="font-heading font-semibold text-bluedark">{{ $firstAlumni->student?->full_name ?? 'Alumni' }}</p>
                        <p class="text-xs text-bluedark/50">
                            Lulusan {{ $firstAlumni->graduation_year }}
                            @if (filled($firstAlumni->current_occupation))
                                — {{ $firstAlumni->current_occupation }}
                            @endif
                        </p>
                        @if (filled($firstAlumni->current_company))
                            <p class="text-sm text-bluedark/70 mt-1">{{ $firstAlumni->current_company }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('public.alumni.index') }}"
                class="inline-flex items-center gap-2 mt-8 text-bluedark font-heading font-medium hover:text-blueprim transition-colors">
                Lihat semua kisah alumni
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
        </div>
    </div>
</section>


{{-- ======================================================
     PKL & CAREER CENTER
     ====================================================== --}}
<section id="karier" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="text-center max-w-xl mx-auto reveal">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Bursa Kerja Khusus</p>
        <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">PKL &amp; Career Center</h2>
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base">BKK {{ $schoolName }} menjembatani siswa dan alumni dengan dunia kerja — mulai dari praktik kerja lapangan, informasi lowongan, hingga penelusuran alumni.</p>
    </div>

    <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">

        {{-- Lowongan Kerja --}}
        <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Lowongan Kerja</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi rekrutmen terbaru dari mitra industri, khusus untuk siswa dan alumni.</p>
            @if ($careerOpportunities->isNotEmpty())
                <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
                    <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
                        <span>Lowongan Terbaru</span>
                        <span class="font-heading font-semibold text-blueprim">{{ $careerOpportunities->count() }} baru</span>
                    </li>
                    <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
                        <span>Jadwal Rekrutmen</span>
                        <span class="font-heading font-semibold text-blueprim">{{ now()->translatedFormat('M Y') }}</span>
                    </li>
                </ul>
            @endif
            <a href="{{ route('public.career.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-5">
                Lihat lowongan
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        {{-- Mitra Industri --}}
        <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Mitra Industri</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Daftar perusahaan mitra tempat siswa melaksanakan PKL dan penyaluran kerja.</p>
            <div class="mt-4 flex-1 flex items-end">
                <p class="font-heading text-2xl font-bold text-bluedark counter" data-target="{{ $companies->count() ?: 60 }}" data-suffix="+">0</p>
            </div>
            <a href="#kerja-sama-industri"
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-3">
                Lihat mitra
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        {{-- Alumni --}}
        <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Alumni</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Jejaring alumni dan hasil tracer study penyerapan lulusan tiap tahunnya.</p>
            <div class="mt-4 flex-1 flex items-end">
                <p class="font-heading text-2xl font-bold text-bluedark counter" data-target="96" data-suffix="%">0%</p>
            </div>
            <a href="{{ route('public.alumni.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-3">
                Tracer Study
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        {{-- PKL --}}
        <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M12 2l9 4.9V17L12 22l-9-5.1V6.9L12 2z"/><path d="M12 12l9-5M12 12v10M12 12L3 7"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">PKL</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi jadwal, pembekalan, dan penempatan Praktik Kerja Lapangan siswa.</p>
            @if ($careerServices->isNotEmpty())
                <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
                    <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
                        <span>Layanan BKK</span>
                        <span class="font-heading font-semibold text-blueprim">{{ $careerServices->count() }} layanan</span>
                    </li>
                </ul>
            @endif
            <a href="{{ route('public.career.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-5">
                Info PKL
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

    </div>

    <div class="mt-6 sm:mt-8 flex justify-center reveal">
        <a href="{{ route('public.career.index') }}"
            class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm sm:text-base">
            Tentang BKK Selengkapnya
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>


{{-- ======================================================
     KERJA SAMA INDUSTRI (marquee logo)
     ====================================================== --}}
<section id="kerja-sama-industri" class="mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 text-center reveal">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Jaringan Dunia Usaha &amp; Industri</p>
        <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Kerja Sama Industri</h2>
        <p class="text-bluedark/60 mt-3 max-w-lg mx-auto text-sm sm:text-base">Dipercaya bermitra dengan perusahaan-perusahaan terkemuka untuk praktik kerja lapangan, rekrutmen, hingga penyusunan kurikulum.</p>
    </div>

    @if ($companies->isNotEmpty())
        <div class="relative mt-10 sm:mt-12 w-screen left-1/2 -translate-x-1/2 industri-marquee-wrap">
            <div class="reveal">
                <div class="industri-marquee-track">
                    <div class="industri-marquee-set">
                        @foreach ($companies as $company)
                            <div class="industri-logo-card">
                                @if (filled($company->logo))
                                    <img src="{{ asset('storage/' . $company->logo) }}"
                                        alt="{{ $company->name }}"
                                        width="80" height="40"
                                        class="h-9 md:h-10 w-auto object-contain">
                                @else
                                    <span class="font-heading font-semibold text-xs text-bluedark/60 whitespace-nowrap px-2">{{ $company->name }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    {{-- Duplicate set for seamless loop (filled by JS) --}}
                    <div class="industri-marquee-set" aria-hidden="true">
                        @foreach ($companies as $company)
                            <div class="industri-logo-card">
                                @if (filled($company->logo))
                                    <img src="{{ asset('storage/' . $company->logo) }}"
                                        alt=""
                                        width="80" height="40"
                                        class="h-9 md:h-10 w-auto object-contain">
                                @else
                                    <span class="font-heading font-semibold text-xs text-bluedark/60 whitespace-nowrap px-2">{{ $company->name }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- placeholder when no companies yet --}}
        <div class="relative mt-10 sm:mt-12 w-screen left-1/2 -translate-x-1/2 industri-marquee-wrap">
            <div class="industri-marquee-track">
                <div class="industri-marquee-set">
                    @foreach (range(1, 6) as $i)
                        <div class="industri-logo-card">
                            <span class="font-heading font-semibold text-xs text-bluedark/40 whitespace-nowrap px-2">Mitra Industri {{ $i }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="industri-marquee-set" aria-hidden="true">
                    @foreach (range(1, 6) as $i)
                        <div class="industri-logo-card">
                            <span class="font-heading font-semibold text-xs text-bluedark/40 whitespace-nowrap px-2">Mitra Industri {{ $i }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 reveal">
        <div class="relative mt-10 sm:mt-12 flex justify-center gap-10 sm:gap-14 border-t border-bluelight pt-7">
            <div class="text-center">
                <p class="font-heading text-xl sm:text-2xl font-bold text-bluedark counter"
                    data-target="{{ $companies->count() ?: 60 }}" data-suffix="+">0</p>
                <p class="text-xs text-bluedark/50 mt-1">Perusahaan mitra</p>
            </div>
            <div class="text-center">
                <p class="font-heading text-xl sm:text-2xl font-bold text-bluedark counter" data-target="15">0</p>
                <p class="text-xs text-bluedark/50 mt-1">Kelas industri</p>
            </div>
        </div>
    </div>
</section>


{{-- ======================================================
     PRODUK UNGGULAN
     ====================================================== --}}
<section id="produk-unggulan" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="flex items-end justify-between gap-6 reveal">
        <div>
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Karya Siswa &amp; Sekolah</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Produk Unggulan Sekolah</h2>
            <p class="text-bluedark/60 mt-2 max-w-md text-sm sm:text-base">Katalog produk dan jasa hasil karya siswa dari setiap program keahlian.</p>
        </div>
        <a href="{{ route('public.products.index') }}"
            class="hidden md:inline-flex items-center gap-2 font-heading font-medium text-bluedark hover:text-blueprim transition-colors shrink-0 text-sm">
            Katalog Produk &amp; Jasa
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    @if ($products->isEmpty())
        <div class="mt-10 bg-white border border-bluelight rounded-3xl">
            <x-public.empty-state
                icon="box"
                title="Belum ada produk"
                description="Produk siswa belum dipublikasikan." />
        </div>
    @else
        <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
            @foreach ($products as $product)
                <article class="link-card group relative bg-white rounded-3xl overflow-hidden border border-bluelight stagger-item">
                    <a href="{{ route('public.products.show', $product) }}"
                        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                        aria-label="Detail Produk {{ $product->name }}"></a>
                    <div class="h-40 overflow-hidden">
                        <x-public.media :model="$product" alt="{{ $product->name }}" icon="box"
                            class="w-full h-full object-cover" />
                    </div>
                    <div class="p-5">
                        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                            {{ $product->category?->name ?? $product->department?->short_name ?? 'Produk' }}
                        </span>
                        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">{{ $product->name }}</h3>
                        @if (filled($product->description))
                            <p class="text-xs text-bluedark/50 mt-2 leading-relaxed line-clamp-2">{{ $product->description }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-4">
                            <p class="font-heading font-bold text-bluedark">
                                {{ $product->price !== null
                                    ? 'Rp ' . number_format((float) $product->price, 0, ',', '.')
                                    : 'Tanyakan harga' }}
                            </p>
                            <span class="text-xs font-heading font-medium text-blueprim">Detail →</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="mt-8 flex md:hidden justify-center reveal">
        <a href="{{ route('public.products.index') }}"
            class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm">
            Katalog Produk &amp; Jasa
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>


{{-- ======================================================
     PRESTASI
     ====================================================== --}}
<section id="prestasi" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="text-center max-w-xl mx-auto reveal">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Pencapaian</p>
        <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Prestasi Siswa &amp; Sekolah</h2>
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Beberapa capaian terbaru dari siswa dan sekolah di tingkat regional hingga nasional.</p>
    </div>

    @if ($achievements->isEmpty())
        <div class="mt-10 bg-white border border-bluelight rounded-3xl">
            <x-public.empty-state
                icon="trophy"
                title="Belum ada prestasi"
                description="Data prestasi siswa belum dipublikasikan." />
        </div>
    @else
        <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
            @foreach ($achievements as $achievement)
                <div class="card-hover bg-white rounded-3xl border border-bluelight p-6 stagger-item">
                    <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="8" r="5"/>
                            <path d="M8 13l-2 8 6-3 6 3-2-8"/>
                        </svg>
                    </div>
                    <p class="font-heading font-semibold text-bluedark leading-snug">{{ $achievement->title }}</p>
                    <p class="text-sm text-bluedark/60 mt-2">
                        {{ collect([$achievement->level, $achievement->achievement_date?->translatedFormat('Y')])->filter()->implode(', ') }}
                    </p>
                    @if (filled($achievement->organizer))
                        <p class="text-xs text-bluedark/45 mt-1">{{ $achievement->organizer }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-8 sm:mt-10 flex justify-center reveal">
        <a href="{{ route('public.achievements.index') }}"
            class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm sm:text-base">
            Lihat Semua Prestasi
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>


{{-- ======================================================
     BERITA & ARTIKEL TERBARU
     ====================================================== --}}
<section id="berita" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    <div class="flex items-end justify-between gap-6 reveal">
        <div>
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Info Sekolah</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Berita &amp; Artikel Terbaru</h2>
        </div>
        <a href="{{ route('public.articles.index') }}"
            class="hidden md:inline-flex items-center gap-2 font-heading font-medium text-bluedark hover:text-blueprim transition-colors shrink-0 text-sm">
            Lihat semua artikel
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    {{-- Category filter pills (tags from articles) --}}
    @if ($articles->isNotEmpty())
        @php
            $allCategories = $articles->pluck('category.name')->filter()->unique()->values();
        @endphp
        <div class="mt-6 flex flex-wrap gap-2 reveal" id="beritaFilter">
            <button type="button" data-filter="all"
                class="berita-filter-btn is-active font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">
                Semua
            </button>
            @foreach ($allCategories as $cat)
                <button type="button" data-filter="{{ Str::slug($cat) }}"
                    class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">
                    {{ $cat }}
                </button>
            @endforeach
        </div>
    @endif

    @if ($articles->isEmpty())
        <div class="mt-10 bg-white border border-bluelight rounded-3xl">
            <x-public.empty-state
                icon="news"
                title="Belum ada artikel"
                description="Belum ada berita yang dipublikasikan. Silakan kembali lagi nanti." />
        </div>
    @else
        <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 md:grid-cols-3 gap-6 stagger-group" id="beritaGrid">
            @foreach ($articles as $article)
                <article
                    data-category="{{ Str::slug($article->category?->name ?? 'umum') }}"
                    class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight stagger-item">
                    <a href="{{ route('public.articles.show', $article) }}"
                        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                        aria-label="Baca artikel: {{ $article->title }}"></a>
                    <div class="berita-card__thumb h-44 sm:h-48">
                        <x-public.media :model="$article" alt="{{ $article->title }}"
                            class="w-full h-full object-cover" icon="news" />
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        @if ($article->category)
                            <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">
                                {{ $article->category->name }}
                            </span>
                        @endif
                        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">{{ $article->title }}</h3>
                        <p class="text-xs text-bluedark/50 mt-2">
                            {{ $article->published_at?->translatedFormat('d M Y') }}
                            @if (filled($article->read_time))
                                · {{ $article->read_time }} menit baca
                            @endif
                        </p>
                        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">
                            Baca selengkapnya
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <p id="beritaEmpty" class="hidden text-center text-sm text-bluedark/50 mt-10">Belum ada artikel untuk kategori ini.</p>

    <div class="mt-8 flex md:hidden justify-center reveal">
        <a href="{{ route('public.articles.index') }}"
            class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm">
            Lihat Semua Artikel
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>


{{-- ======================================================
     PPDB CTA
     ====================================================== --}}
<section id="ppdb" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-32 scroll-mt-20">
    <div class="text-center max-w-xl mx-auto reveal">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Penerimaan Peserta Didik Baru</p>
        <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Informasi Alur &amp; Syarat Pendaftaran</h2>
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base">
            {{ $admissionPeriod?->description ?? 'Simak alur pendaftaran dan siapkan berkas persyaratan berikut ini.' }}
        </p>
    </div>

    <div class="mt-10 sm:mt-12 grid lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">

        {{-- Alur pendaftaran --}}
        <div class="lg:col-span-7 bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
            <h3 class="font-heading font-semibold text-lg text-bluedark mb-6">Alur Pendaftaran</h3>

            @if ($admissionPeriod && $admissionPeriod->scheduleItems->isNotEmpty())
                <ol class="space-y-6">
                    @foreach ($admissionPeriod->scheduleItems->take(4) as $index => $item)
                        <li class="flex gap-4">
                            <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-heading font-semibold text-bluedark">{{ $item->title }}</p>
                                @if (filled($item->description))
                                    <p class="text-sm text-bluedark/60 mt-1">{{ $item->description }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <ol class="space-y-6">
                    @foreach ([
                        ['Registrasi Online', 'Isi formulir pendaftaran melalui link pendaftaran online resmi sekolah.'],
                        ['Unggah Berkas', 'Unggah dokumen persyaratan sesuai jadwal dan pilih jurusan yang diminati.'],
                        ['Verifikasi & Tes Seleksi', 'Panitia memverifikasi berkas, dilanjutkan tes/wawancara sesuai jurusan.'],
                        ['Pengumuman & Daftar Ulang', 'Hasil seleksi diumumkan secara online, dilanjutkan daftar ulang peserta didik baru.'],
                    ] as $i => [$title, $desc])
                        <li class="flex gap-4">
                            <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">{{ $i + 1 }}</span>
                            <div>
                                <p class="font-heading font-semibold text-bluedark">{{ $title }}</p>
                                <p class="text-sm text-bluedark/60 mt-1">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <div class="lg:col-span-5 flex flex-col gap-6 h-full">

            {{-- Syarat pendaftaran --}}
            <div class="bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
                <h3 class="font-heading font-semibold text-lg text-bluedark mb-5">Syarat Pendaftaran</h3>

                @php
                    $requirements = $admissionPeriod?->requirements ?? collect();
                @endphp

                @if ($requirements->isNotEmpty())
                    <ul class="space-y-3.5 text-sm text-bluedark/70">
                        @foreach ($requirements as $requirement)
                            <li class="flex gap-3">
                                <svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                <span>
                                    {{ $requirement->title }}
                                    @if (filled($requirement->description))
                                        <span class="block text-xs text-bluedark/50 mt-0.5">{{ $requirement->description }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('public.ppdb.index') }}#syarat"
                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim hover:text-bluedark transition-colors">
                        Lihat detail pendaftaran
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                @else
                    <ul class="space-y-3.5 text-sm text-bluedark/70">
                        @foreach ([
                            'Fotokopi ijazah/SKL SMP atau sederajat',
                            'Fotokopi Kartu Keluarga & akta kelahiran',
                            'Pas foto berwarna terbaru 3×4',
                            'Surat keterangan sehat dari puskesmas/dokter',
                        ] as $req)
                            <li class="flex gap-3">
                                <svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                {{ $req }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- PPDB CTA gradient card --}}
            <div class="rounded-3xl bg-gradient-to-br from-blueprim to-bluedark p-6 sm:p-8 text-center reveal flex-1 flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

                @if ($admissionPeriod)
                    <span @class([
                        'inline-flex items-center gap-2 font-heading font-semibold text-xs px-3.5 py-1.5 rounded-full mb-4',
                        'bg-emerald-400/20 text-emerald-200 border border-emerald-400/40' => $admissionPeriod->status === 'OPEN',
                        'bg-white/10 text-white/70 border border-white/20' => $admissionPeriod->status !== 'OPEN',
                    ])>
                        {{ $admissionPeriod->status === 'OPEN' ? '● Pendaftaran dibuka' : 'Pendaftaran ditutup' }}
                    </span>
                    <p class="font-heading font-semibold text-white text-lg leading-snug">{{ $admissionPeriod->title }}</p>
                    @if ($admissionPeriod->registration_start && $admissionPeriod->registration_end)
                        <p class="text-white/70 text-xs mt-2">
                            {{ $admissionPeriod->registration_start->translatedFormat('d M') }} —
                            {{ $admissionPeriod->registration_end->translatedFormat('d M Y') }}
                        </p>
                    @endif
                @else
                    <p class="font-heading font-semibold text-white">Link Pendaftaran Online</p>
                    <p class="text-white/70 text-sm mt-1.5">Daftar langsung melalui portal PPDB resmi sekolah.</p>
                @endif

                <a href="{{ route('public.ppdb.index') }}"
                    class="inline-flex items-center gap-2 mt-5 bg-white text-bluedark font-heading font-semibold px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
                    Daftar PPDB Sekarang
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
