@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', $schoolProfile?->description ?? 'Website resmi ' . $schoolName . ' — jurusan unggulan, karier, PPDB, dan prestasi terbaik.')

@section('content')

{{-- ======================================================
     HERO
     ====================================================== --}}
<section id="beranda" class="relative overflow-hidden parallax-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 w-full">
        <div class="relative rounded-3xl md:rounded-4xl bg-gradient-to-br from-bluelight via-[#EAF4FE] to-bluesoft/70 px-5 sm:px-8 md:px-14 py-10 md:py-20 overflow-hidden">

            {{-- Background blobs --}}
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-bluesoft/40 blur-3xl parallax-layer" data-speed="0.5" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 rounded-full bg-blueprim/10 blur-3xl parallax-layer" data-speed="0.8" aria-hidden="true"></div>

            <div class="relative grid lg:grid-cols-12 gap-10 items-center">

                {{-- Left: headline + CTA --}}
                <div class="lg:col-span-6 reveal">
                    <h1 class="font-heading font-bold text-3xl sm:text-4xl md:text-5xl xl:text-[3.4rem] leading-[1.15] text-bluedark">
                        Kenali minat,<br>
                        bangun <span class="text-blueprim">keahlian.</span>
                    </h1>
                    <p class="mt-5 text-bluedark/70 text-base md:text-lg max-w-md leading-relaxed">
                        {{ $schoolProfile?->vision
                            ?? 'Jelajahi program keahlian, karya siswa, dan informasi penerimaan di ' . $schoolName . '.' }}
                    </p>

                    <div class="mt-8 flex flex-nowrap items-center gap-2 sm:gap-4">
                        <a href="{{ route('public.profile') }}"
                            class="inline-flex min-w-0 flex-1 items-center justify-center whitespace-nowrap rounded-full bg-blueprim px-3 py-3 font-heading text-xs font-semibold text-white shadow-soft transition-colors hover:bg-bluedark sm:flex-none sm:px-7 sm:py-3.5 sm:text-sm">
                            Profil Sekolah
                        </a>
                        <a href="#jurusan"
                            class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border border-bluesoft/60 bg-white px-3 py-3 font-heading text-xs font-medium text-bluedark transition-colors hover:bg-bluelight sm:px-4 sm:text-sm">
                            Lihat Jurusan <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>

                {{-- Right: hero art + floating chips --}}
                <div class="lg:col-span-6 relative reveal">
                    <div class="relative max-w-md mx-auto">
                        <div class="absolute inset-0 bg-blueprim/10 rounded-full blur-3xl scale-90" aria-hidden="true"></div>
                        <img src="{{ asset('assets/images/hero/hero-jurusan.png') }}"
                            alt="Ilustrasi empat program keahlian {{ $schoolName }}"
                            class="relative w-full h-auto drop-shadow-2xl hero-art-parallax"
                            loading="eager">
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

{{-- ======================================================
     JURUSAN UNGGULAN
     ====================================================== --}}
<section id="jurusan" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-20 sm:pt-24 md:pt-32 scroll-mt-20">
    <div class="flex items-end justify-between gap-6 reveal">
        <div>
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Program Keahlian</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Jurusan Unggulan</h2>
            <p class="text-bluedark/60 mt-2 max-w-md text-sm sm:text-base">
                {{ $departments->count() }} program keahlian untuk membantu siswa mengembangkan keterampilan sesuai minatnya.
            </p>
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
     LULUSAN TERBAIK (alumni dari backend)
     ====================================================== --}}
@if ($alumni->isNotEmpty())
<section id="lulusan-terbaik" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36 scroll-mt-20">
    @php
        $firstAlumni = $alumni->first();
        $featuredStory = $firstAlumni->stories->first();
        $alumniImage = $featuredStory
            ? app(\App\Services\PublicMediaService::class)->forModel($featuredStory)
            : null;
    @endphp
    <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
        <figure class="relative mx-auto w-full max-w-md reveal lg:col-span-5 lg:mx-0">
            <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-bluelight shadow-soft">
                <img src="{{ $alumniImage ?? asset(config('public_site.alumni_fallback')) }}"
                    alt="{{ $firstAlumni->student?->full_name ?? $schoolName }}"
                    class="lulusan-photo-parallax h-full w-full object-cover"
                    loading="lazy"
                    decoding="async">
            </div>
            @if (filled($featuredStory?->quote))
                <figcaption class="absolute -bottom-5 right-4 max-w-[85%] rounded-2xl bg-white px-5 py-4 text-sm italic leading-relaxed text-bluedark/70 shadow-card sm:right-6">
                    &ldquo;{{ $featuredStory->quote }}&rdquo;
                </figcaption>
            @endif
        </figure>

        <div class="reveal lg:col-span-7">
            <p class="mb-3 font-heading text-xs font-semibold uppercase tracking-[0.2em] text-blueprim">Kisah alumni</p>
            <h2 class="font-heading text-2xl font-bold leading-tight text-bluedark sm:text-3xl md:text-[2.6rem]">
                {{ $featuredStory->title }}
            </h2>
            @if (filled($featuredStory?->career_story))
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-bluedark/60 sm:text-base">
                    {{ $featuredStory->career_story }}
                </p>
            @elseif (filled($featuredStory?->story))
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-bluedark/60 sm:text-base">
                    {{ \Illuminate\Support\Str::limit($featuredStory->story, 360) }}
                </p>
            @endif

            <div class="mt-8 max-w-lg rounded-3xl bg-white p-5 shadow-card sm:p-6">
                <p class="font-heading font-semibold text-bluedark">{{ $firstAlumni->student->full_name }}</p>
                <p class="mt-1 text-xs text-bluedark/50">
                    @if ($firstAlumni->graduation_year)
                        Lulusan {{ $firstAlumni->graduation_year }}
                    @endif
                    @if (filled($firstAlumni->current_occupation))
                        {{ $firstAlumni->graduation_year ? ' · ' : '' }}{{ $firstAlumni->current_occupation }}
                    @endif
                </p>
                @if (filled($firstAlumni->current_company))
                    <p class="mt-1 text-sm text-bluedark/70">{{ $firstAlumni->current_company }}</p>
                @endif
            </div>

            <a href="{{ route('public.alumni.index') }}"
                class="mt-8 inline-flex items-center gap-2 font-heading font-medium text-bluedark transition-colors hover:text-blueprim">
                Lihat semua kisah alumni
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif


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
        <a href="{{ route('public.career.index') }}"
            class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Lowongan Kerja</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi rekrutmen terbaru dari mitra industri, khusus untuk siswa dan alumni.</p>
            @if ($jobOpportunities->isNotEmpty())
                <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
                    <li class="rounded-lg bg-bluelight/60 px-3 py-2">
                        <span class="block">Lowongan terbaru</span>
                        <span class="mt-2 flex items-center gap-3">
                            @if ($jobOpportunities->first()->media->isNotEmpty())
                                <span class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-white">
                                    <x-public.media :model="$jobOpportunities->first()" :alt="$jobOpportunities->first()->title" class="h-full w-full" />
                                </span>
                            @endif
                            <span class="font-heading font-semibold text-blueprim">{{ $jobOpportunities->first()->title }}</span>
                        </span>
                    </li>
                </ul>
            @endif
            <span
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-5">
                Lihat lowongan
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>

        {{-- Mitra Industri --}}
        <a href="#kerja-sama-industri"
            class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Mitra Industri</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Daftar perusahaan mitra tempat siswa melaksanakan PKL dan penyaluran kerja.</p>
            <span
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-5">
                Lihat mitra industri
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>

        {{-- Alumni --}}
        <a href="{{ route('public.alumni.index') }}"
            class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">Alumni</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Kisah dan perjalanan lulusan setelah menempuh pendidikan di sekolah.</p>
            <span
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-3">
                Lihat kisah alumni
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>

        {{-- PKL --}}
        <a href="{{ route('public.career.index') }}"
            class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col stagger-item">
            <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true"><path d="M12 2l9 4.9V17L12 22l-9-5.1V6.9L12 2z"/><path d="M12 12l9-5M12 12v10M12 12L3 7"/></svg>
            </div>
            <h3 class="font-heading font-semibold text-bluedark">PKL</h3>
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi jadwal, pembekalan, dan penempatan Praktik Kerja Lapangan siswa.</p>
            @if ($internshipOpportunities->isNotEmpty())
                <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
                    <li class="rounded-lg bg-bluelight/60 px-3 py-2">
                        <span class="block">Info PKL terbaru</span>
                        <span class="mt-2 flex items-center gap-3">
                            @if ($internshipOpportunities->first()->media->isNotEmpty())
                                <span class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-white">
                                    <x-public.media :model="$internshipOpportunities->first()" :alt="$internshipOpportunities->first()->title" class="h-full w-full" />
                                </span>
                            @endif
                            <span class="font-heading font-semibold text-blueprim">{{ $internshipOpportunities->first()->title }}</span>
                        </span>
                    </li>
                </ul>
            @endif
            <span
                class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-5">
                Info PKL
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>

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
        <p class="text-bluedark/60 mt-3 max-w-lg mx-auto text-sm sm:text-base">Perusahaan yang tercatat sebagai mitra industri sekolah.</p>
    </div>

    @if ($companies->isNotEmpty())
        <div class="relative mt-10 sm:mt-12 w-screen left-1/2 -translate-x-1/2 industri-marquee-wrap">
            <div class="reveal">
                <div class="industri-marquee-track">
                    @foreach ([false, true] as $isDuplicate)
                        <div class="industri-marquee-set" @if ($isDuplicate) aria-hidden="true" @endif>
                            @foreach ($companies as $company)
                                @php
                                    $logoUrl = app(\App\Services\PublicMediaService::class)->url($company->logo, 'public');
                                @endphp
                                <div class="industri-logo-card">
                                    @if (filled($company->website))
                                        <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer"
                                            aria-label="Kunjungi situs {{ $company->name }}"
                                            @if ($isDuplicate) tabindex="-1" @endif>
                                            <img src="{{ $logoUrl }}" alt="{{ $isDuplicate ? '' : $company->name }}"
                                                width="180" height="64" loading="lazy" decoding="async">
                                        </a>
                                    @else
                                        <span>
                                            <img src="{{ $logoUrl }}" alt="{{ $isDuplicate ? '' : $company->name }}"
                                                width="180" height="64" loading="lazy" decoding="async">
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <p class="mt-8 text-center text-sm text-bluedark/50">Informasi mitra industri akan ditampilkan di sini.</p>
    @endif

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
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Karya dan capaian yang ditandai unggulan oleh sekolah.</p>
    </div>

    @if ($achievements->isEmpty())
        <div class="mt-10 bg-white border border-bluelight rounded-3xl">
            <x-public.empty-state
                icon="trophy"
                title="Belum ada prestasi unggulan"
                description="Prestasi pilihan sekolah akan ditampilkan di sini." />
        </div>
    @else
        <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
            @foreach ($achievements as $achievement)
                <article data-category="{{ $achievement->category?->slug ?? 'lainnya' }}"
                    class="relative link-card stagger-item flex h-full flex-col overflow-hidden rounded-3xl border border-bluelight bg-white shadow-card">
                    <a href="{{ route('public.achievements.show', $achievement) }}"
                        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                        aria-label="Lihat detail prestasi {{ $achievement->title }}"></a>
                    <div class="h-40 shrink-0">
                        <x-public.media :model="$achievement" :alt="$achievement->title" icon="award" />
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-bluelight px-2.5 py-1 text-xs font-heading font-semibold text-blueprim">
                                {{ $achievement->category?->name ?? 'Prestasi' }}
                            </span>
                            @if ($achievement->is_featured)
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-heading font-semibold text-amber-700">
                                    Unggulan
                                </span>
                            @endif
                        </div>
                        <h3 class="mt-3 font-heading font-semibold leading-snug text-bluedark">{{ $achievement->title }}</h3>
                        <p class="mt-2 text-xs text-bluedark/50">
                            {{ collect([$achievement->level, $achievement->scope, $achievement->achievement_date?->translatedFormat('d M Y')])->filter()->implode(' · ') }}
                        </p>
                        @if (filled($achievement->rank) || filled($achievement->organizer))
                            <p class="mt-1 text-xs text-bluedark/45">
                                {{ collect([$achievement->rank ? 'Juara '.$achievement->rank : null, $achievement->organizer])->filter()->implode(' · ') }}
                            </p>
                        @endif
                        @if (filled($achievement->description))
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-bluedark/60">{{ $achievement->description }}</p>
                        @endif
                        <span class="mt-auto pt-4 text-xs font-heading font-medium text-blueprim">Lihat detail prestasi</span>
                    </div>
                </article>
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
            $allCategories = $articles->pluck('category')->filter()->unique('id');
        @endphp
        <div class="mt-6 flex flex-wrap gap-2 reveal" id="beritaFilter"
            data-public-filter data-filter-target="beritaGrid" data-filter-empty="beritaEmpty">
            <button type="button" data-filter="all" aria-pressed="true"
                class="berita-filter-btn is-active font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">
                Semua
            </button>
            @foreach ($allCategories as $category)
                <button type="button" data-filter="{{ $category->slug }}" aria-pressed="false"
                    class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">
                    {{ $category->name }}
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
        <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group" id="beritaGrid">
            @foreach ($articles as $article)
                <article
                    data-category="{{ $article->category?->slug ?? 'informasi' }}"
                    class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight stagger-item">
                    <a href="{{ route('public.articles.show', $article) }}"
                        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                        aria-label="Baca artikel: {{ $article->title }}"></a>
                    <div class="berita-card__thumb h-44 sm:h-48">
                        <x-public.media :model="$article" column="thumbnail" alt="{{ $article->title }}"
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
    @if ($admissionPeriod)
        <div class="text-center max-w-xl mx-auto reveal">
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Penerimaan Peserta Didik Baru</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Pendaftaran sedang dibuka</h2>
            <p class="text-bluedark/60 mt-3 text-sm sm:text-base">
                {{ $admissionPeriod->description ?: 'Informasi jadwal dan persyaratan pendaftaran.' }}
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
                <p class="text-sm leading-relaxed text-bluedark/60">
                    Jadwal pendaftaran belum diumumkan. Silakan lihat halaman PPDB atau hubungi sekolah untuk informasi terbaru.
                </p>
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
                    <p class="text-sm leading-relaxed text-bluedark/60">
                        Persyaratan pendaftaran belum dicantumkan. Silakan hubungi sekolah untuk konfirmasi.
                    </p>
                @endif
            </div>

            {{-- PPDB CTA gradient card --}}
            <div class="rounded-3xl bg-gradient-to-br from-blueprim to-bluedark p-6 sm:p-8 text-center reveal flex-1 flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

                <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-400/20 px-3.5 py-1.5 font-heading text-xs font-semibold text-emerald-200">
                    Pendaftaran dibuka
                </span>
                <p class="font-heading text-lg font-semibold leading-snug text-white">{{ $admissionPeriod->title }}</p>
                @if ($admissionPeriod->registration_start && $admissionPeriod->registration_end)
                    <p class="mt-2 text-xs text-white/70">
                        {{ $admissionPeriod->registration_start->translatedFormat('d M') }} —
                        {{ $admissionPeriod->registration_end->translatedFormat('d M Y') }}
                    </p>
                @endif

                <a href="{{ route('public.ppdb.index') }}"
                    class="inline-flex items-center gap-2 mt-5 bg-white text-bluedark font-heading font-semibold px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
                    Lihat jadwal &amp; persyaratan
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-2xl rounded-3xl border border-bluelight bg-white px-6 py-10 text-center shadow-xs sm:px-10">
            <p class="font-heading text-xs font-semibold uppercase tracking-[0.16em] text-blueprim">Penerimaan Peserta Didik Baru</p>
            <h2 class="mt-3 font-heading text-2xl font-bold text-bluedark sm:text-3xl">PPDB sedang ditutup</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-bluedark/60">
                Periode pendaftaran belum dibuka. Informasi jadwal dan persyaratan akan tersedia di halaman PPDB saat pendaftaran dimulai.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('public.ppdb.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-bluelight px-5 py-3 font-heading text-sm font-semibold text-bluedark transition-colors hover:bg-bluelight">
                    Informasi PPDB
                </a>
                <a href="{{ route('public.profile') }}"
                    class="inline-flex items-center justify-center rounded-full bg-bluedark px-5 py-3 font-heading text-sm font-semibold text-white transition-colors hover:bg-blueprim">
                    Kontak sekolah
                </a>
            </div>
    </div>
    @endif
</section>

@endsection
