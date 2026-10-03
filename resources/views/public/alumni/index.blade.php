@extends('layouts.public')

@section('title', 'Alumni')
@section('meta_description', 'Cerita dan prestasi alumni '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Alumni"
        title="Jejak Lulusan"
        description="Kisah alumni yang melanjutkan pendidikan maupun berkarya di dunia kerja."
        :breadcrumb="['Beranda' => route('public.home'), 'Alumni' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="mb-8 flex justify-end">
                <x-public.search-form
                    :action="route('public.alumni.index')"
                    id="alumni-search"
                    :search-value="$search"
                    placeholder="Cari alumni..." />
            </div>

            @if ($alumni->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="users"
                        :title="$search !== '' ? 'Alumni tidak ditemukan' : 'Belum ada profil alumni'"
                        :description="$search !== '' ? 'Coba kata kunci lain.' : 'Cerita alumni akan tampil di sini setelah admin sekolah melengkapinya.'" />
                </div>
            @else
                <div id="publicAlumni" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 stagger-group">
                    @foreach ($alumni as $alumnus)
                        @php
                            $name = $alumnus->student?->full_name ?? 'Alumni';
                            $story = $alumnus->stories->first();
                            $initials = collect(preg_split('/\s+/', $name))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_substr($part, 0, 1))
                                ->implode('');
                        @endphp

                        <article class="stagger-item relative flex flex-col bg-white rounded-3xl border border-bluelight shadow-card p-6 h-full card-hover">
                            <a href="{{ route('public.alumni.show', $alumnus) }}"
                                class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                                aria-label="Lihat kisah alumni {{ $name }}"></a>
                            <div class="mb-5 aspect-[4/3] overflow-hidden rounded-2xl bg-bluelight">
                                <x-public.media :model="$story" :alt="$name" icon="user" class="h-full w-full" />
                            </div>

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

                            @if ($story)
                                <div class="mt-4 border-t border-bluelight pt-4">
                                    <h3 class="font-heading text-sm font-semibold text-bluedark">{{ $story->title }}</h3>
                                    @if (filled($story->career_story))
                                        <p class="mt-2 text-sm leading-relaxed text-bluedark/60">{{ \Illuminate\Support\Str::limit($story->career_story, 220) }}</p>
                                    @elseif (filled($story->story))
                                        <p class="mt-2 text-sm leading-relaxed text-bluedark/60">{{ \Illuminate\Support\Str::limit($story->story, 220) }}</p>
                                    @endif
                                    @if (filled($story->quote))
                                        <blockquote class="mt-3 text-sm italic leading-relaxed text-bluedark/60">
                                            &ldquo;{{ $story->quote }}&rdquo;
                                        </blockquote>
                                    @endif
                                </div>
                            @endif

                            @if (filled($alumnus->social_link))
                                <a href="{{ $alumnus->social_link }}" target="_blank" rel="noopener noreferrer"
                                    class="relative z-20 mt-4 inline-flex items-center gap-1.5 text-xs font-heading font-semibold text-blueprim hover:text-bluedark transition-colors self-start">
                                    Kunjungi profil
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        aria-hidden="true">
                                        <path d="M7 17 17 7M9 7h8v8" />
                                    </svg>
                                </a>
                            @endif
                            <span class="relative z-0 mt-auto inline-flex items-center gap-1.5 pt-5 text-xs font-heading font-semibold text-blueprim">
                                Baca kisah lengkap
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </span>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $alumni->links() }}
                </div>
            @endif
        </div>
    </section>

    <x-public.cta-band
        title="Jadilah bagian dari alumni berikutnya"
        description="Pendaftaran masih dibuka. Kenali program keahlian yang paling sesuai dengan minat dan bakatmu."
        action-label="Daftar PPDB"
        :action-url="route('public.ppdb.index')"
        secondary-label="Tanyakan ke BKK"
        :secondary-url="route('public.career.index')" />
@endsection
