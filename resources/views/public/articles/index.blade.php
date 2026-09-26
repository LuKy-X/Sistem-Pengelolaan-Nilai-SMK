@extends('layouts.public')

@section('title', 'Berita & Artikel')
@section('meta_description', 'Kabar terbaru, kegiatan, dan pengumuman dari '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Informasi"
        title="Berita & Artikel"
        description="Kabar kegiatan sekolah, prestasi siswa, dan pengumuman resmi dari {{ $schoolName }}."
        :breadcrumb="['Beranda' => route('public.home'), 'Berita' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-8">
                    <a href="{{ route('public.articles.index') }}"
                        @class([
                            'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                            'bg-bluedark text-white border-bluedark' => $selectedCategory === '',
                            'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedCategory !== '',
                        ])>
                        Semua
                    </a>

                    @foreach ($categories as $category)
                        <a href="{{ route('public.articles.index', ['kategori' => $category->slug]) }}"
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

            @if ($articles->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="news"
                        title="Belum ada artikel"
                        :description="$selectedCategory !== ''
                            ? 'Tidak ada artikel pada kategori ini.'
                            : 'Belum ada berita yang dipublikasikan. Silakan kembali lagi nanti.'" />
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach ($articles as $article)
                        <x-public.article-card :article="$article" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
