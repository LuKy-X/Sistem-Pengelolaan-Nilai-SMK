@extends('layouts.public')

@section('title', 'Profil Sekolah')
@section('meta_description', 'Profil, visi, misi, dan sejarah '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Tentang Kami"
        title="Profil Sekolah"
        :description="$schoolProfile?->description"
        :breadcrumb="['Beranda' => route('public.home'), 'Profil Sekolah' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            @if ($schoolProfile === null)
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="inbox"
                        title="Profil sekolah belum tersedia"
                        description="Data profil belum diisi oleh admin sekolah. Silakan kembali lagi nanti." />
                </div>
            @else
                <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

                    {{-- Main column --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-3xl overflow-hidden border border-bluelight shadow-card">
                            <x-public.media
                                :src="app(\App\Services\PublicMediaService::class)->url($schoolProfile->hero_image, 'public') ?? asset(config('public_site.hero_fallback'))"
                                alt="{{ $schoolName }}"
                                class="w-full aspect-[16/9] bg-bluelight/50" />
                        </div>

                        @if (filled($schoolProfile->history))
                            <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6 sm:p-8">
                                <h2 class="font-heading font-semibold text-xl text-bluedark">Sejarah Sekolah</h2>
                                <div class="prose-article mt-3 text-bluedark/70 text-sm sm:text-base leading-relaxed">
                                    {!! nl2br(e($schoolProfile->history)) !!}
                                </div>
                            </div>
                        @endif

                        <div class="grid sm:grid-cols-2 gap-5">
                            @if (filled($schoolProfile->vision))
                                <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6">
                                    <h2 class="font-heading font-semibold text-base text-bluedark">Visi</h2>
                                    <p class="text-sm text-bluedark/65 mt-2.5 leading-relaxed">{{ $schoolProfile->vision }}</p>
                                </div>
                            @endif

                            @if (filled($schoolProfile->mission))
                                <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6">
                                    <h2 class="font-heading font-semibold text-base text-bluedark">Misi</h2>
                                    <div class="text-sm text-bluedark/65 mt-2.5 leading-relaxed">
                                        {!! nl2br(e($schoolProfile->mission)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($departments->isNotEmpty())
                            <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6 sm:p-8">
                                <h2 class="font-heading font-semibold text-xl text-bluedark">Kompetensi Keahlian</h2>
                                <ul class="mt-4 divide-y divide-bluelight">
                                    @foreach ($departments as $department)
                                        <li>
                                            <a href="{{ route('public.departments.show', $department) }}"
                                                class="flex items-center justify-between gap-4 py-3.5 group">
                                                <div class="min-w-0">
                                                    <p class="font-heading font-medium text-sm text-bluedark group-hover:text-blueprim transition-colors">
                                                        {{ $department->name }}
                                                    </p>
                                                    @if (filled($department->description))
                                                        <p class="text-xs text-bluedark/55 mt-0.5 line-clamp-1">{{ $department->description }}</p>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded shrink-0">
                                                    {{ $department->short_name ?: $department->code }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Sidebar --}}
                    <aside class="space-y-6">
                        <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6 text-center">
                            <span class="inline-grid place-items-center w-24 h-24 mx-auto rounded-3xl bg-bluelight/70 p-2">
                                <x-public.media
                                    :src="app(\App\Services\PublicMediaService::class)->url($schoolProfile->logo, 'public') ?? asset(config('public_site.logo_fallback'))"
                                    alt="Logo {{ $schoolName }}" fit="contain" />
                            </span>

                            <h2 class="font-heading font-semibold text-lg text-bluedark mt-4">{{ $schoolName }}</h2>

                            @if (filled($schoolProfile->principal_name))
                                <p class="text-sm text-bluedark/60 mt-2">{{ $schoolProfile->principal_name }}</p>
                                <p class="text-xs text-bluedark/45">Kepala Sekolah</p>
                            @endif
                        </div>

                        <div class="bg-white border border-bluelight rounded-3xl shadow-xs p-6">
                            <h2 class="font-heading font-semibold text-base text-bluedark">Informasi Sekolah</h2>

                            <dl class="mt-4 space-y-4 text-sm">
                                @if (filled($schoolProfile->npsn))
                                    <div>
                                        <dt class="text-xs text-bluedark/45">NPSN</dt>
                                        <dd class="font-heading font-medium text-bluedark mt-0.5">{{ $schoolProfile->npsn }}</dd>
                                    </div>
                                @endif

                                @if (filled($schoolProfile->address))
                                    <div>
                                        <dt class="text-xs text-bluedark/45">Alamat</dt>
                                        <dd class="text-bluedark/70 mt-0.5 leading-relaxed">{{ $schoolProfile->address }}</dd>
                                    </div>
                                @endif

                                @if (filled($schoolProfile->phone))
                                    <div>
                                        <dt class="text-xs text-bluedark/45">Telepon</dt>
                                        <dd class="mt-0.5">
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $schoolProfile->phone) }}"
                                                class="font-heading font-medium text-bluedark hover:text-blueprim transition-colors">
                                                {{ $schoolProfile->phone }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                @if (filled($schoolProfile->email))
                                    <div>
                                        <dt class="text-xs text-bluedark/45">Email</dt>
                                        <dd class="mt-0.5">
                                            <a href="mailto:{{ $schoolProfile->email }}"
                                                class="font-heading font-medium text-bluedark hover:text-blueprim transition-colors break-all">
                                                {{ $schoolProfile->email }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                @if (filled($schoolProfile->website))
                                    <div>
                                        <dt class="text-xs text-bluedark/45">Website</dt>
                                        <dd class="mt-0.5">
                                            <a href="{{ $schoolProfile->website }}" target="_blank" rel="noopener noreferrer"
                                                class="font-heading font-medium text-bluedark hover:text-blueprim transition-colors break-all">
                                                {{ $schoolProfile->website }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            @if (blank($schoolProfile->npsn)
                                && blank($schoolProfile->address)
                                && blank($schoolProfile->phone)
                                && blank($schoolProfile->email)
                                && blank($schoolProfile->website))
                                <p class="text-sm text-bluedark/50 mt-4">
                                    Informasi kontak belum diisi oleh admin sekolah.
                                </p>
                            @endif
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
