@extends('layouts.public')

@section('title', 'PPDB')
@section('meta_description', 'Informasi penerimaan peserta didik baru '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Penerimaan Peserta Didik Baru"
        title="PPDB"
        description="Informasi jadwal, jalur pendaftaran, persyaratan, dan biaya pendaftaran peserta didik baru."
        :breadcrumb="['Beranda' => route('public.home'), 'PPDB' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @if ($periods->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="news"
                        title="PPDB sedang ditutup"
                        description="Belum ada periode pendaftaran yang dibuka. Jadwal dan persyaratan akan ditampilkan saat pendaftaran dimulai." />
                    <div class="pb-8 text-center">
                        <a href="{{ route('public.profile') }}"
                            class="inline-flex items-center justify-center rounded-full bg-bluedark px-5 py-3 font-heading text-sm font-semibold text-white transition-colors hover:bg-blueprim">
                            Kontak sekolah
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-8">
                    @foreach ($periods as $period)
                        <div class="bg-white border border-bluelight rounded-3xl shadow-card overflow-hidden">
                            {{-- Period header --}}
                            <div class="bg-bluedark text-white p-6 sm:p-8 relative overflow-hidden">
                                <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-blueprim/20 blur-3xl"></div>

                                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div>
                                        <span @class([
                                            'inline-flex items-center gap-2 font-heading font-semibold text-xs px-3.5 py-1.5 rounded-full',
                                            'bg-emerald-400/20 text-emerald-200 border border-emerald-400/40' => $period->status === 'OPEN',
                                            'bg-white/10 text-white/70 border border-white/20' => $period->status !== 'OPEN',
                                        ])>
                                            {{ $period->status === 'OPEN' ? 'Sedang dibuka' : 'Ditutup' }}
                                        </span>

                                        <h2 class="font-heading font-bold text-xl sm:text-2xl mt-3">{{ $period->title }}</h2>

                                        @if (filled($period->academicYear?->name))
                                            <p class="text-white/60 text-sm mt-1">Tahun pelajaran {{ $period->academicYear->name }}</p>
                                        @endif
                                    </div>

                                    <div class="shrink-0 text-sm">
                                        <p class="text-white/60 text-xs">Periode pendaftaran</p>
                                        <p class="font-heading font-semibold mt-1">
                                            {{ $period->registration_start->translatedFormat('d M Y') }}
                                            &ndash;
                                            {{ $period->registration_end->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </div>

                                @if (filled($period->description))
                                    <p class="relative text-white/70 text-sm mt-5 leading-relaxed max-w-3xl">{{ $period->description }}</p>
                                @endif
                            </div>

                            <div class="p-6 sm:p-8 space-y-8">
                                @if ($period->scheduleItems->isNotEmpty())
                                    <div>
                                        <h3 class="font-heading font-semibold text-base text-bluedark">Jadwal Pendaftaran</h3>
                                        <ol class="mt-4 space-y-3">
                                            @foreach ($period->scheduleItems as $item)
                                                <li class="flex items-start gap-4 p-4 rounded-2xl bg-[#F7FBFF] border border-bluelight">
                                                    <span class="w-8 h-8 rounded-lg bg-bluedark text-white font-heading font-semibold text-xs grid place-items-center shrink-0">
                                                        {{ $item->step_number }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="font-heading font-medium text-sm text-bluedark">{{ $item->title }}</p>
                                                        @if (filled($item->description))
                                                            <p class="text-xs text-bluedark/55 mt-1 leading-relaxed">{{ $item->description }}</p>
                                                        @endif
                                                        <p class="text-xs text-blueprim font-heading font-medium mt-1.5">
                                                            {{ $item->start_date->translatedFormat('d M Y') }}
                                                            &ndash;
                                                            {{ $item->end_date->translatedFormat('d M Y') }}
                                                        </p>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endif

                                @if ($period->paths->isNotEmpty())
                                    <div>
                                        <h3 class="font-heading font-semibold text-base text-bluedark">Jalur Pendaftaran</h3>
                                        <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                            @foreach ($period->paths as $path)
                                                <div class="p-4 rounded-2xl border border-bluelight">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <p class="font-heading font-medium text-sm text-bluedark">{{ $path->name }}</p>
                                                        @if ($path->quota !== null)
                                                            <span class="text-[10px] font-heading font-semibold text-blueprim bg-bluelight px-2 py-0.5 rounded shrink-0">
                                                                Kuota {{ $path->quota }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if (filled($path->description))
                                                        <p class="text-xs text-bluedark/55 mt-2 leading-relaxed">{{ $path->description }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="grid lg:grid-cols-2 gap-8">
                                    @if ($period->requirements->isNotEmpty())
                                        <div id="syarat">
                                            <h3 class="font-heading font-semibold text-base text-bluedark">Persyaratan</h3>
                                            <ul class="mt-4 space-y-2.5">
                                                @foreach ($period->requirements as $requirement)
                                                    <li class="flex items-start gap-2.5 text-sm text-bluedark/70">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
                                                            class="text-blueprim shrink-0 mt-0.5" aria-hidden="true">
                                                            <path d="M20 6 9 17l-5-5" />
                                                        </svg>
                                                        <span>
                                                            <span class="font-heading font-medium text-bluedark">{{ $requirement->title }}</span>
                                                            @if (filled($requirement->description))
                                                                <span class="block text-xs text-bluedark/55 mt-0.5">{{ $requirement->description }}</span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if ($period->feeItems->isNotEmpty())
                                        <div>
                                            <h3 class="font-heading font-semibold text-base text-bluedark">Biaya Pendaftaran</h3>
                                            <ul class="mt-4 divide-y divide-bluelight">
                                                @foreach ($period->feeItems as $fee)
                                                    <li class="flex items-center justify-between gap-4 py-3">
                                                        <div class="min-w-0">
                                                            <p class="text-sm text-bluedark/75">{{ $fee->name }}</p>
                                                            @if (filled($fee->description))
                                                                <p class="text-xs text-bluedark/50 mt-0.5">{{ $fee->description }}</p>
                                                            @endif
                                                        </div>
                                                        <span @class([
                                                            'font-heading font-semibold text-sm shrink-0',
                                                            'text-emerald-600' => $fee->is_free,
                                                            'text-bluedark' => ! $fee->is_free,
                                                        ])>
                                                            {{ $fee->is_free
                                                                ? 'Gratis'
                                                                : 'Rp '.number_format((float) $fee->amount, 0, ',', '.') }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-6 border-t border-bluelight flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <p class="text-sm text-bluedark/60">
                                        Pendaftaran dilakukan langsung di sekolah. Silakan hubungi sekolah untuk informasi lebih lanjut.
                                    </p>

                                    <a href="{{ $schoolProfile?->phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $schoolProfile->phone) : '#' }}"
                                        @class([
                                            'inline-flex items-center gap-2 font-heading font-semibold text-sm px-6 py-3 rounded-full transition-colors',
                                            'bg-bluedark hover:bg-blueprim text-white' => filled($schoolProfile?->phone),
                                            'bg-bluelight text-bluedark/40 pointer-events-none' => blank($schoolProfile?->phone),
                                        ])>
                                        Hubungi Sekolah
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($periods->isNotEmpty())
        <x-public.cta-band
            title="Ada pertanyaan tentang pendaftaran?"
            description="Hubungi sekolah jika kamu memerlukan bantuan terkait jadwal, persyaratan, atau jalur PPDB."
            action-label="Hubungi Sekolah"
            :action-url="route('public.profile')"
            secondary-label="Lihat semua jurusan"
            :secondary-url="route('public.departments.index')" />
    @endif
@endsection
