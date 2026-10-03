@extends('layouts.public')

@section('title', $opportunity->title)
@section('meta_description', str($opportunity->description ?? '')->limit(155)->value() ?: $opportunity->type->label().' di '.$schoolName)

@section('content')
    <x-public.page-header
        :eyebrow="$opportunity->type->label()"
        :title="$opportunity->title"
        :description="$opportunity->company?->name"
        :breadcrumb="[
            'Beranda' => route('public.home'),
            'PKL & Karier' => route('public.career.index'),
            $opportunity->title => null,
        ]" />

    <section class="pt-8 pb-14 sm:pt-10 sm:pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <article class="lg:col-span-2 rounded-3xl border border-bluelight bg-white p-6 shadow-xs sm:p-8">
                    <div class="-mx-6 -mt-6 mb-6 h-56 overflow-hidden bg-bluelight sm:-mx-8 sm:-mt-8 sm:h-72">
                        <x-public.media :model="$opportunity" :alt="$opportunity->title" icon="briefcase" />
                    </div>

                    <div class="flex flex-wrap items-start gap-4">
                        @if ($opportunity->company && filled($opportunity->company->logo))
                            <x-public.media
                                :model="$opportunity->company"
                                column="logo"
                                :alt="$opportunity->company->name"
                                fit="contain"
                                class="h-14 w-14 shrink-0 rounded-xl border border-bluelight bg-white p-2" />
                        @endif

                        <div>
                            <span class="inline-flex rounded-full bg-bluelight px-3 py-1 text-xs font-heading font-semibold text-blueprim">
                                {{ $opportunity->type->label() }}
                            </span>
                            <h2 class="mt-3 font-heading text-xl font-semibold leading-snug text-bluedark sm:text-2xl">
                                {{ $opportunity->title }}
                            </h2>
                            <p class="mt-1 text-sm text-bluedark/55">
                                {{ collect([$opportunity->company?->name, $opportunity->company?->industry, $opportunity->location])->filter()->implode(' · ') }}
                            </p>
                        </div>
                    </div>

                    @if (filled($opportunity->description))
                        <section class="mt-8">
                            <h3 class="font-heading text-base font-semibold text-bluedark">Deskripsi</h3>
                            <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-bluedark/70">
                                {{ $opportunity->description }}
                            </div>
                        </section>
                    @endif

                    @if (filled($opportunity->requirements))
                        <section class="mt-8 border-t border-bluelight pt-6">
                            <h3 class="font-heading text-base font-semibold text-bluedark">Kualifikasi &amp; Persyaratan</h3>
                            <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-bluedark/70">
                                {{ $opportunity->requirements }}
                            </div>
                        </section>
                    @endif
                </article>

                <aside class="h-fit rounded-3xl border border-bluelight bg-white p-6 shadow-xs">
                    <h2 class="font-heading text-lg font-semibold text-bluedark">Informasi lowongan</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        @if (filled($opportunity->location))
                            <div>
                                <dt class="text-xs text-bluedark/45">Lokasi</dt>
                                <dd class="mt-1 font-medium text-bluedark">{{ $opportunity->location }}</dd>
                            </div>
                        @endif

                        @if ($opportunity->open_date)
                            <div>
                                <dt class="text-xs text-bluedark/45">Dibuka</dt>
                                <dd class="mt-1 font-medium text-bluedark">{{ $opportunity->open_date->translatedFormat('d F Y') }}</dd>
                            </div>
                        @endif

                        @if ($opportunity->close_date)
                            <div>
                                <dt class="text-xs text-bluedark/45">Batas pendaftaran</dt>
                                <dd class="mt-1 font-medium text-bluedark">{{ $opportunity->close_date->translatedFormat('d F Y') }}</dd>
                            </div>
                        @endif

                        @if (filled($opportunity->company?->address))
                            <div>
                                <dt class="text-xs text-bluedark/45">Alamat perusahaan</dt>
                                <dd class="mt-1 leading-relaxed text-bluedark/70">{{ $opportunity->company->address }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if (filled($opportunity->application_link))
                        <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer"
                            class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-bluedark px-5 py-3 font-heading text-sm font-semibold text-white transition-colors hover:bg-blueprim">
                            Daftar / Lamar
                        </a>
                    @else
                        <p class="mt-6 rounded-xl bg-bluelight/50 p-4 text-xs leading-relaxed text-bluedark/65">
                            Informasi pendaftaran belum tersedia. Hubungi sekolah untuk mendapatkan petunjuk lebih lanjut.
                        </p>
                        <a href="{{ route('public.profile') }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-bluelight px-5 py-3 font-heading text-sm font-semibold text-bluedark transition-colors hover:bg-bluelight">
                            Kontak sekolah
                        </a>
                    @endif

                    @if (filled($opportunity->company?->website))
                        <a href="{{ $opportunity->company->website }}" target="_blank" rel="noopener noreferrer"
                            class="mt-3 block text-center text-xs font-medium text-blueprim hover:text-bluedark">
                            Kunjungi situs perusahaan
                        </a>
                    @endif
                </aside>
            </div>

            <a href="{{ route('public.career.index') }}"
                class="mt-8 inline-flex items-center gap-2 font-heading text-sm font-medium text-bluedark transition-colors hover:text-blueprim">
                <span aria-hidden="true">←</span> Kembali ke daftar lowongan
            </a>
        </div>
    </section>
@endsection
