@extends('layouts.public')

@section('title', 'Prestasi')
@section('meta_description', 'Rekam jejak prestasi siswa '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Kebanggaan"
        title="Prestasi Siswa"
        description="Capaian siswa di berbagai kompetisi tingkat sekolah, kabupaten, hingga nasional."
        :breadcrumb="['Beranda' => route('public.home'), 'Prestasi' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-8">
                    <a href="{{ route('public.achievements.index') }}"
                        @class([
                            'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                            'bg-bluedark text-white border-bluedark' => $selectedCategory === '',
                            'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedCategory !== '',
                        ])>
                        Semua
                    </a>

                    @foreach ($categories as $category)
                        <a href="{{ route('public.achievements.index', ['kategori' => $category->slug]) }}"
                            @class([
                                'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                                'bg-bluedark text-white border-bluedark' => $selectedCategory === $category->slug,
                                'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedCategory !== $category->slug,
                            ])>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($achievements->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="trophy"
                        title="Belum ada prestasi"
                        :description="$selectedCategory !== ''
                            ? 'Tidak ada prestasi pada kategori ini.'
                            : 'Data prestasi siswa belum dipublikasikan.'" />
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach ($achievements as $achievement)
                        <article class="link-card relative flex flex-col bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden h-full card-hover">
                            <div class="h-40 shrink-0">
                                <x-public.media :model="$achievement" :alt="$achievement->title" icon="award" />
                            </div>

                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                        {{ $achievement->category?->name ?? 'Prestasi' }}
                                    </span>
                                    @if ($achievement->is_featured)
                                        <span class="text-xs font-heading font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                                            Unggulan
                                        </span>
                                    @endif
                                </div>

                                <h2 class="font-heading font-semibold text-bluedark mt-3 leading-snug">{{ $achievement->title }}</h2>

                                <p class="text-xs text-bluedark/50 mt-2">
                                    {{ collect([
                                        $achievement->level,
                                        $achievement->scope,
                                        $achievement->achievement_date?->translatedFormat('d M Y'),
                                    ])->filter()->implode(' · ') }}
                                </p>

                                @if (filled($achievement->rank) || filled($achievement->organizer))
                                    <p class="text-xs text-bluedark/45 mt-1">
                                        {{ collect([$achievement->rank ? 'Juara '.$achievement->rank : null, $achievement->organizer])->filter()->implode(' · ') }}
                                    </p>
                                @endif

                                @if (filled($achievement->description))
                                    <p class="text-sm text-bluedark/60 mt-3 leading-relaxed line-clamp-3">{{ $achievement->description }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $achievements->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
