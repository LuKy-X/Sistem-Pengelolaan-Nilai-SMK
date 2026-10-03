@extends('layouts.public')

@section('title', $department->name)
@section('meta_description', $department->description ?? 'Kompetensi keahlian ' . $department->name . ' di ' . $schoolName . '.')

@section('content')

{{-- Breadcrumb --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-6 md:pt-10 reveal">
    <nav class="flex items-center gap-2 text-xs sm:text-sm text-bluedark/50 font-heading" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}" class="hover:text-blueprim transition-colors">Beranda</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('public.departments.index') }}" class="hover:text-blueprim transition-colors">Jurusan</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        <span class="text-bluedark font-medium">{{ $department->name }}</span>
    </nav>
</section>

{{-- ======================================================
     HERO — teks kiri, gambar kanan (identik template JHIC)
     ====================================================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-4 md:mt-6">
    <div class="relative rounded-3xl md:rounded-4xl bg-bluelight px-5 sm:px-8 md:px-14 py-10 md:py-16 overflow-hidden reveal">
        {{-- Decorative blobs --}}
        <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-bluesoft/40 blur-3xl" aria-hidden="true"></div>

        <div class="relative grid lg:grid-cols-12 gap-8 items-center">

            {{-- Left: text --}}
            <div class="lg:col-span-7">
                <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-3">
                    Program Keahlian &middot; {{ $department->short_name ?: $department->code }}
                </p>
                <h1 class="font-heading font-bold text-3xl sm:text-4xl md:text-5xl leading-[1.15] text-bluedark">
                    {{ $department->name }}
                </h1>
                @if (filled($department->description))
                    <p class="mt-4 text-bluedark/70 text-sm sm:text-base md:text-lg max-w-xl leading-relaxed">
                        {{ Str::limit($department->description, 200) }}
                    </p>
                @endif
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('public.ppdb.index') }}"
                        class="bg-blueprim hover:bg-bluedark transition-colors text-white font-heading font-medium px-6 py-3 rounded-full shadow-soft text-sm">
                        Daftar Jurusan Ini
                    </a>
                    <a href="{{ route('public.departments.index') }}"
                        class="inline-flex items-center gap-2 bg-white border border-bluesoft/60 text-bluedark font-heading font-medium px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
                        Lihat Jurusan Lain
                    </a>
                </div>
            </div>

            {{-- Right: floating art image --}}
            <div class="lg:col-span-5">
                <div class="relative max-w-xs mx-auto">
                    <div class="absolute inset-0 bg-blueprim/10 rounded-full blur-3xl scale-90" aria-hidden="true"></div>
                    <img src="{{ $department->coverImageUrl() }}"
                        alt="Ilustrasi {{ $department->name }}"
                        class="relative w-full h-auto drop-shadow-2xl"
                        width="310" height="270"
                        loading="eager" decoding="async">
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ======================================================
     TENTANG PROGRAM
     ====================================================== --}}
@if (filled($department->description))
    <section class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20 reveal">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Tentang Program</p>
        <h2 class="font-heading font-bold text-2xl sm:text-3xl text-bluedark mb-4">Apa yang Dipelajari di {{ $department->name }}?</h2>
        <p class="text-bluedark/70 leading-relaxed">{{ $department->description }}</p>
    </section>
@endif

{{-- ======================================================
     KOMPETENSI (dari backend)
     ====================================================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20">
    <div class="reveal mb-8">
        <h2 class="font-heading font-bold text-2xl sm:text-3xl text-bluedark">Kompetensi Keahlian</h2>
        <p class="text-bluedark/60 mt-2 text-sm sm:text-base max-w-xl">Keterampilan utama yang dibangun sepanjang tiga tahun pembelajaran.</p>
    </div>

    @if ($department->competencies->isNotEmpty())
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
            @foreach ($department->competencies as $competency)
                <div class="card-hover bg-white rounded-3xl border border-bluelight shadow-card p-6 stagger-item">
                    <span class="font-heading text-xs font-bold text-blueprim bg-bluelight w-8 h-8 rounded-lg flex items-center justify-center">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3 class="font-heading font-semibold text-bluedark mt-4">{{ $competency->title }}</h3>
                    @if (filled($competency->description))
                        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">{{ $competency->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty state ketika belum ada data kompetensi --}}
        <div class="bg-white border border-bluelight rounded-3xl p-6 sm:p-8 reveal">
            <x-public.empty-state
                icon="folder"
                title="Kompetensi belum diisi"
                description="Rincian kompetensi keahlian akan ditambahkan oleh admin sekolah." />
        </div>
    @endif
</section>

{{-- ======================================================
     MATA PELAJARAN + FASILITAS (2 kolom, sesuai template)
     ====================================================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20 grid lg:grid-cols-2 gap-6 lg:gap-8">

    {{-- Mata Pelajaran --}}
    <div class="bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
        <h3 class="font-heading font-semibold text-lg text-bluedark mb-5">Mata Pelajaran Produktif</h3>
        @if ($department->subjects->isNotEmpty())
            <ul class="space-y-3.5">
                @foreach ($department->subjects as $subject)
                    <li class="flex gap-3 text-sm text-bluedark/70">
                        <svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="#2196F3" stroke-width="2.4" aria-hidden="true">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        <span>{{ $subject->name }}</span>
                        @if (filled($subject->code))
                            <span class="text-[10px] font-heading font-semibold text-blueprim bg-bluelight px-2 py-0.5 rounded self-center ml-auto shrink-0">{{ $subject->code }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-bluedark/50 italic">Mata pelajaran belum diisi.</p>
        @endif
    </div>

    {{-- Fasilitas + Visi/Misi --}}
    <div class="space-y-6">
        <div class="bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
            <h3 class="font-heading font-semibold text-lg text-bluedark mb-5">Fasilitas &amp; Peralatan Praktik</h3>
            @if ($department->facilities->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($department->facilities as $facility)
                        <span class="text-xs sm:text-sm bg-bluelight/70 text-bluedark/80 px-3 py-1.5 rounded-full font-medium">
                            {{ $facility->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-bluedark/50 italic">Fasilitas belum diisi.</p>
            @endif
        </div>

        @if (filled($department->vision) || filled($department->mission))
            <div class="bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
                @if (filled($department->vision))
                    <h3 class="font-heading font-semibold text-base text-bluedark mb-2">Visi Jurusan</h3>
                    <p class="text-sm text-bluedark/65 leading-relaxed">{{ $department->vision }}</p>
                @endif
                @if (filled($department->mission))
                    <h3 class="font-heading font-semibold text-base text-bluedark mb-2 mt-5">Misi Jurusan</h3>
                    <p class="text-sm text-bluedark/65 leading-relaxed">{{ $department->mission }}</p>
                @endif
            </div>
        @endif
    </div>
</section>

{{-- ======================================================
     PROSPEK KARIER
     `career_prospects` disimpan sebagai teks yang dipisah koma, jadi
     dipecah menjadi chip mengikuti template.
     ====================================================== --}}
@php
    $careerChips = collect(preg_split('/\s*[,;\n]\s*/', (string) $department->career_prospects, -1, PREG_SPLIT_NO_EMPTY))
        ->map(fn (string $career) => trim($career))
        ->filter()
        ->unique()
        ->values();
@endphp

@if ($careerChips->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20">
        <div class="rounded-3xl md:rounded-4xl bg-bluedark px-6 sm:px-10 md:px-14 py-10 md:py-14 reveal">
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-bluesoft font-semibold mb-2">Lulus Mau Kemana?</p>
            <h2 class="font-heading font-bold text-2xl sm:text-3xl text-white mb-2">Prospek Karier Lulusan</h2>
            <p class="text-white/60 text-sm sm:text-base max-w-lg mb-6 leading-relaxed">
                Lulusan dapat langsung bekerja, membuka usaha mandiri, atau melanjutkan kuliah dengan bekal kompetensi yang relevan.
            </p>
            <div class="flex flex-wrap gap-2.5">
                @foreach ($careerChips as $career)
                    <span class="text-xs sm:text-sm bg-white border border-bluelight text-bluedark px-3.5 py-2 rounded-full font-medium shadow-card">
                        {{ $career }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ======================================================
     MITRA INDUSTRI
     ====================================================== --}}
@if ($partners->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20 reveal">
        <h3 class="font-heading font-semibold text-lg sm:text-xl text-bluedark mb-4">Mitra Industri Sekolah</h3>
        <div class="flex flex-wrap gap-2.5">
            @foreach ($partners as $partner)
                <span class="text-xs sm:text-sm bg-bluelight/70 text-bluedark/80 px-3.5 py-1.5 rounded-full font-medium">
                    {{ $partner->name }}
                </span>
            @endforeach
        </div>
    </section>
@endif

{{-- ======================================================
     KARYA SISWA PROGRAM INI
     ====================================================== --}}
@if ($department->studentProducts->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 md:mt-20">
        <div class="reveal mb-8">
            <h2 class="font-heading font-bold text-2xl sm:text-3xl text-bluedark">Karya Siswa {{ $department->short_name ?: $department->code }}</h2>
            <p class="text-bluedark/60 mt-2 text-sm sm:text-base max-w-xl">Produk yang dikembangkan siswa program keahlian ini bersama mentor industri.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 stagger-group">
            @foreach ($department->studentProducts as $product)
                <x-public.product-card :product="$product" :show-department="false" />
            @endforeach
        </div>

        <div class="mt-8 reveal">
            <a href="{{ route('public.products.index') }}"
                class="inline-flex items-center gap-2 bg-white border border-bluesoft/60 text-bluedark font-heading font-medium px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
                Lihat Semua Produk Unggulan
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </section>
@endif

{{-- ======================================================
     CTA DAFTAR
     ====================================================== --}}
<x-public.cta-band
    title="Tertarik Bergabung di {{ $department->name }}?"
    description="Kuota kelas terbatas setiap tahun ajaran — daftar dari sekarang."
    action-label="Daftar PPDB Sekarang"
    :action-url="route('public.ppdb.index')"
    secondary-label="Lihat Jurusan Lain"
    :secondary-url="route('public.departments.index')" />

@endsection
