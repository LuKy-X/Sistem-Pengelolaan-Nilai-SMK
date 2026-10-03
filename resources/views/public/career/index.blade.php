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

                    @if ($hasCompanies)
                        <div class="mt-8">
                            <h2 class="font-heading font-semibold text-base text-bluedark">Mitra Industri</h2>
                            <div class="mt-4">
                                <x-public.search-form
                                    :action="route('public.career.index')"
                                    id="career-company-search"
                                    label="Cari mitra industri"
                                    search-name="company_q"
                                    :search-value="$companySearch"
                                    placeholder="Cari mitra..."
                                    :hidden-fields="['q' => $search, 'type' => $selectedType]" />
                                <ul id="careerCompanies" class="mt-4 space-y-2">
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
                            @if ($companies->hasPages())
                                <div class="mt-4">
                                    {{ $companies->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Opportunities --}}
                <div class="lg:col-span-2">
                    @php
                        $opportunityTypes = [
                            ['value' => 'JOB', 'label' => 'Lowongan Kerja'],
                            ['value' => 'INTERNSHIP', 'label' => 'Magang / PKL'],
                        ];
                    @endphp
                    <div class="mb-8 flex justify-end">
                        <x-public.search-form
                            :action="route('public.career.index')"
                            id="career-opportunity-search"
                            :search-value="$search"
                            placeholder="Cari lowongan..."
                            filter-name="type"
                            filter-label="Semua jenis"
                            :filter-value="$selectedType"
                            :filters="$opportunityTypes"
                            :hidden-fields="['company_q' => $companySearch]" />
                    </div>

                    <h2 class="font-heading font-semibold text-xl text-bluedark">Lowongan Terbuka</h2>

                    @if ($opportunities->isEmpty())
                        <div class="mt-6 bg-white border border-bluelight rounded-3xl">
                            <x-public.empty-state
                                class="py-10"
                                icon="briefcase"
                                :title="$search !== '' || $selectedType !== '' ? 'Lowongan tidak ditemukan' : 'Belum ada lowongan'"
                                :description="$search !== '' || $selectedType !== '' ? 'Coba ubah kata kunci atau jenis lowongan.' : 'Belum ada lowongan magang atau kerja yang dipublikasikan.'" />
                        </div>
                    @else
                        <div id="careerOpportunities" class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($opportunities as $opportunity)
                                <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-bluelight bg-white shadow-xs transition-shadow hover:shadow-card">
                                    <a href="{{ route('public.career.show', $opportunity) }}"
                                        class="group flex flex-1 flex-col focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blueprim"
                                        aria-label="Lihat detail {{ $opportunity->type->label() }}: {{ $opportunity->title }}">
                                        <div class="aspect-[4/3] overflow-hidden bg-bluelight">
                                            <x-public.media :model="$opportunity" :alt="$opportunity->title" icon="briefcase"
                                                class="h-full w-full transition-transform duration-300 group-hover:scale-[1.03]" />
                                        </div>
                                        <div class="flex flex-1 flex-col p-5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-full bg-bluelight px-2.5 py-1 text-xs font-heading font-semibold text-blueprim">
                                                    {{ $opportunity->type->label() }}
                                                </span>
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-heading font-semibold text-emerald-800">
                                                    Dibuka
                                                </span>
                                            </div>
                                            <h3 class="mt-3 font-heading font-semibold leading-snug text-bluedark group-hover:text-blueprim">
                                                {{ $opportunity->title }}
                                            </h3>
                                            <p class="mt-1.5 text-xs text-bluedark/50">
                                                {{ collect([$opportunity->company?->name, $opportunity->location])->filter()->implode(' · ') }}
                                            </p>
                                            @if (filled($opportunity->description))
                                                <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-bluedark/65">
                                                    {{ $opportunity->description }}
                                                </p>
                                            @endif
                                            <span class="mt-auto inline-flex items-center gap-1.5 pt-5 text-xs font-heading font-semibold text-blueprim">
                                                Lihat detail
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.4" aria-hidden="true">
                                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                                </svg>
                                            </span>
                                        </div>
                                    </a>
                                    @if (filled($opportunity->close_date) || filled($opportunity->application_link))
                                        <div class="flex items-center justify-between gap-3 border-t border-bluelight px-5 py-3">
                                            @if (filled($opportunity->close_date))
                                                <p class="text-[11px] text-bluedark/45">
                                                    Batas pendaftaran
                                                    <span class="mt-0.5 block font-heading font-semibold text-bluedark/70">
                                                        {{ $opportunity->close_date->translatedFormat('d M Y') }}
                                                    </span>
                                                </p>
                                            @else
                                                <span></span>
                                            @endif
                                            @if (filled($opportunity->application_link))
                                                <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-bluedark px-4 py-2 font-heading text-xs font-semibold text-white transition-colors hover:bg-blueprim">
                                                    Lamar
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                        <path d="M7 17 17 7M9 7h8v8" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
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
