@extends('layouts.public')

@section('title', 'BKK & Karier')
@section('meta_description', 'Layanan pendampingan karier dan lowongan magang '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="BKK & Karier"
        title="Pendampingan Karier dan Peluang Kerja"
        description="Layanan pendampingan karier dan lowongan magang dari mitra industri."
        :breadcrumb="['Beranda' => route('public.home'), 'BKK & Karier' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

                {{-- Career services --}}
                <div class="lg:col-span-1">
                    <h2 class="font-heading font-semibold text-xl text-bluedark">Layanan BKK</h2>
                    <p class="text-sm text-bluedark/60 mt-2">Layanan pendampingan yang tersedia untuk siswa.</p>

                    @if ($services->isEmpty())
                        <div class="mt-6 bg-white border border-bluelight rounded-3xl">
                            <x-public.empty-state
                                class="py-10"
                                icon="briefcase"
                                title="Belum ada layanan"
                                description="Data layanan BKK belum diisi." />
                        </div>
                    @else
                        <ul class="mt-6 space-y-3">
                            @foreach ($services as $service)
                                <li class="p-4 bg-white border border-bluelight rounded-2xl">
                                    <div class="flex items-start gap-3">
                                        <span class="w-9 h-9 rounded-xl bg-bluelight text-blueprim grid place-items-center shrink-0">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                            </svg>
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="font-heading font-medium text-sm text-bluedark">{{ $service->title }}</h3>
                                            @if (filled($service->description))
                                                <p class="text-xs text-bluedark/55 mt-1 leading-relaxed">{{ $service->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($companies->isNotEmpty())
                        <div class="mt-8">
                            <h2 class="font-heading font-semibold text-base text-bluedark">Mitra Industri</h2>
                            <ul class="mt-4 space-y-2">
                                @foreach ($companies as $company)
                                    <li class="flex items-center gap-3 p-3 bg-white border border-bluelight rounded-xl">
                                        <div class="w-9 h-9 rounded-lg bg-bluelight/70 grid place-items-center shrink-0 overflow-hidden">
                                            <x-public.media :model="$company" column="logo" :alt="$company->name" icon="briefcase" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-heading font-medium text-bluedark truncate">{{ $company->name }}</p>
                                            @if (filled($company->industry))
                                                <p class="text-xs text-bluedark/50 truncate">{{ $company->industry }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Opportunities --}}
                <div class="lg:col-span-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('public.career.index') }}"
                            @class([
                                'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                                'bg-bluedark text-white border-bluedark' => ! in_array($selectedType, ['JOB', 'INTERNSHIP'], true),
                                'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => in_array($selectedType, ['JOB', 'INTERNSHIP'], true),
                            ])>
                            Semua
                        </a>
                        <a href="{{ route('public.career.index', ['tipe' => 'INTERNSHIP']) }}"
                            @class([
                                'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                                'bg-bluedark text-white border-bluedark' => $selectedType === 'INTERNSHIP',
                                'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedType !== 'INTERNSHIP',
                            ])>
                            Magang
                        </a>
                        <a href="{{ route('public.career.index', ['tipe' => 'JOB']) }}"
                            @class([
                                'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                                'bg-bluedark text-white border-bluedark' => $selectedType === 'JOB',
                                'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedType !== 'JOB',
                            ])>
                            Kerja
                        </a>
                    </div>

                    <h2 class="font-heading font-semibold text-xl text-bluedark mt-8">Lowongan Terbuka</h2>

                    @if ($opportunities->isEmpty())
                        <div class="mt-6 bg-white border border-bluelight rounded-3xl">
                            <x-public.empty-state
                                class="py-10"
                                icon="briefcase"
                                title="Belum ada lowongan"
                                description="Belum ada lowongan magang atau kerja yang dipublikasikan." />
                        </div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($opportunities as $opportunity)
                                <article class="p-5 sm:p-6 bg-white rounded-3xl border border-bluelight shadow-xs card-hover">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex min-w-0 items-start gap-3">
                                            @if ($opportunity->company && filled($opportunity->company->logo))
                                                <x-public.media
                                                    :model="$opportunity->company"
                                                    column="logo"
                                                    :alt="$opportunity->company->name"
                                                    fit="contain"
                                                    class="w-12 h-12 shrink-0 rounded-xl border border-bluelight bg-white" />
                                            @endif

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                                        {{ $opportunity->type->label() }}
                                                    </span>
                                                    @if (filled($opportunity->company?->industry))
                                                        <span class="text-xs text-bluedark/50">{{ $opportunity->company->industry }}</span>
                                                    @endif
                                                </div>

                                                <h3 class="font-heading font-semibold text-bluedark mt-2.5 leading-snug">
                                                    {{ $opportunity->title }}
                                                </h3>

                                                <p class="text-xs text-bluedark/50 mt-1.5">
                                                    {{ collect([$opportunity->company?->name, $opportunity->location])->filter()->implode(' · ') }}
                                                </p>

                                                @if (filled($opportunity->description))
                                                    <p class="text-sm text-bluedark/65 mt-3 leading-relaxed line-clamp-2">
                                                        {{ $opportunity->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            @if (filled($opportunity->close_date))
                                                <p class="text-[11px] text-bluedark/45">
                                                    Ditutup<br>
                                                    <span class="font-heading font-semibold text-bluedark/70">
                                                        {{ $opportunity->close_date->translatedFormat('d M Y') }}
                                                    </span>
                                                </p>
                                            @endif

                                            @if (filled($opportunity->application_link))
                                                <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer"
                                                    class="mt-3 inline-flex items-center gap-1.5 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-semibold text-xs px-4 py-2 rounded-full">
                                                    Lamar
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                                        aria-hidden="true">
                                                        <path d="M7 17 17 7M9 7h8v8" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-10">
                            {{ $opportunities->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @php
        $partnerEmail = filled($schoolProfile?->email) ? $schoolProfile->email : null;
    @endphp

    <x-public.cta-band
        title="Perusahaan ingin bermitra dengan BKK kami?"
        description="BKK kami membuka kanal lowongan magang maupun karier untuk siswa dengan proses yang terstruktur dan aman."
        :action-label="$partnerEmail ? 'Kirim Lowongan' : 'Lihat Kontak Sekolah'"
        :action-url="$partnerEmail
            ? 'mailto:'.$partnerEmail.'?subject=Lowongan%20Kerja%20untuk%20Siswa'
            : route('public.profile')"
        secondary-label="Lihat produk siswa"
        :secondary-url="route('public.products.index')" />
@endsection
