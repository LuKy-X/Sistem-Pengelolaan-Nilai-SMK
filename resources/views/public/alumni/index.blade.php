@extends('layouts.public')

@section('title', 'Alumni')
@section('meta_description', 'Cerita dan prestasi alumni '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Alumni"
        title="Jejak Lulusan"
        description="Kisah alumni yang melanjutkan pendidikan maupun berkarya di dunia kerja."
        :breadcrumb="['Beranda' => route('public.home'), 'Alumni' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @if ($alumni->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="users"
                        title="Belum ada profil alumni"
                        description="Cerita alumni akan tampil di sini setelah admin sekolah melengkapinya." />
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach ($alumni as $alumnus)
                        @php
                            $name = $alumnus->student?->full_name ?? 'Alumni';
                            $initials = collect(preg_split('/\s+/', $name))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_substr($part, 0, 1))
                                ->implode('');
                        @endphp

                        <article class="flex flex-col bg-white rounded-3xl border border-bluelight shadow-card p-6 h-full card-hover">
                            <div class="flex items-center gap-4">
                                <span class="w-14 h-14 rounded-2xl bg-bluelight grid place-items-center font-heading font-bold text-lg text-blueprim shrink-0">
                                    {{ $initials }}
                                </span>
                                <div class="min-w-0">
                                    <h2 class="font-heading font-semibold text-bluedark truncate">{{ $name }}</h2>
                                    <p class="text-xs text-bluedark/50">Lulusan {{ $alumnus->graduation_year }}</p>
                                </div>
                            </div>

                            @if (filled($alumnus->current_occupation))
                                <p class="text-sm text-bluedark/70 mt-4">{{ $alumnus->current_occupation }}</p>
                            @endif

                            @if (filled($alumnus->current_company) || filled($alumnus->city))
                                <p class="text-xs text-bluedark/45 mt-1">
                                    {{ collect([$alumnus->current_company, $alumnus->city])->filter()->implode(' · ') }}
                                </p>
                            @endif

                            @foreach ($alumnus->stories->take(1) as $story)
                                @if (filled($story->quote))
                                    <blockquote class="mt-4 pt-4 border-t border-bluelight text-sm italic text-bluedark/60 leading-relaxed">
                                        &ldquo;{{ $story->quote }}&rdquo;
                                    </blockquote>
                                @endif
                            @endforeach

                            @if (filled($alumnus->social_link))
                                <a href="{{ $alumnus->social_link }}" target="_blank" rel="noopener noreferrer"
                                    class="mt-4 inline-flex items-center gap-1.5 text-xs font-heading font-semibold text-blueprim hover:text-bluedark transition-colors self-start">
                                    Kunjungi profil
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        aria-hidden="true">
                                        <path d="M7 17 17 7M9 7h8v8" />
                                    </svg>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $alumni->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
