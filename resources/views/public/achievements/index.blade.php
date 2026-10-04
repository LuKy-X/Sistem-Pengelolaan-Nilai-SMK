@extends('layouts.public')

@section('title', 'Prestasi')
@section('meta_description', 'Rekam jejak prestasi siswa '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Kebanggaan"
        title="Prestasi Siswa"
        description="Capaian siswa di berbagai kompetisi tingkat sekolah, kabupaten, hingga nasional."
        :breadcrumb="['Beranda' => route('public.home'), 'Prestasi' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @php
                $categoryFilters = $categories->map(fn ($category) => [
                    'value' => $category->slug,
                    'label' => $category->name,
                ])->all();
            @endphp

            <div class="mb-8 flex justify-end">
                <x-public.search-form
                    :action="route('public.achievements.index')"
                    id="achievement-search"
                    :search-value="$search"
                    placeholder="Cari prestasi..."
                    filter-name="category"
                    filter-label="Semua kategori"
                    :filter-value="$selectedCategory"
                    :filters="$categoryFilters" />
            </div>

            <div id="publicAchievementsResults">
            @if ($achievements->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="trophy"
                        :title="$search !== '' || $selectedCategory !== '' ? 'Prestasi tidak ditemukan' : 'Belum ada prestasi'"
                        :description="$search !== '' || $selectedCategory !== '' ? 'Coba ubah kata kunci atau kategori filter.' : 'Data prestasi siswa belum dipublikasikan.'" />
                </div>
            @else
                <div id="publicAchievements" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 stagger-group">
                    @foreach ($achievements as $achievement)
                        <article
                            class="stagger-item link-card relative flex flex-col bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden h-full card-hover">
                            <a href="{{ route('public.achievements.show', $achievement) }}"
                                class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                                aria-label="Lihat detail prestasi {{ $achievement->title }}"></a>
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
                                <span class="mt-auto pt-4 text-xs font-heading font-medium text-blueprim">
                                    Lihat detail prestasi
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $achievements->links() }}
                </div>
            @endif
            </div>
        </div>
    </section>

    <x-public.cta-band
        title="Ingin ikut berprestasi seperti mereka?"
        description="Program pendampingan tiap bidang membantu siswa menemukan potensi dan prepping kompetisi."
        action-label="Lihat Program Jurusan"
        :action-url="route('public.departments.index')"
        secondary-label="Pelajari soal PPDB"
        :secondary-url="route('public.ppdb.index')" />
@endsection
