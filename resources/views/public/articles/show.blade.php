@extends('layouts.public')

@section('title', $article->title)
@section('meta_description', $article->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($article->content), 160))

@section('content')
    <article class="pb-4">
        {{-- Article hero --}}
        <header class="relative overflow-hidden border-b border-bluelight bg-gradient-to-b from-bluelight/70 to-[#F7FBFF]">
            <div class="absolute -top-24 -right-20 w-80 h-80 rounded-full bg-bluesoft/25 blur-3xl"></div>

            <div class="relative max-w-3xl mx-auto px-4 sm:px-6 md:px-8 py-12 sm:py-16 text-center">
                <nav aria-label="Breadcrumb" class="reveal">
                    <ol class="flex flex-wrap items-center justify-center gap-2 text-xs text-bluedark/55 font-heading">
                        <li class="flex items-center gap-2">
                            <a href="{{ route('public.home') }}" class="hover:text-blueprim transition-colors">Beranda</a>
                            <span aria-hidden="true">/</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <a href="{{ route('public.articles.index') }}" class="hover:text-blueprim transition-colors">Berita</a>
                            <span aria-hidden="true">/</span>
                        </li>
                        <li class="text-bluedark/75">{{ $article->category?->name ?? 'Artikel' }}</li>
                    </ol>
                </nav>

                <div class="flex flex-wrap items-center justify-center gap-2 mt-5 reveal">
                    @if ($article->category)
                        <a href="{{ route('public.articles.index', ['kategori' => $article->category->slug]) }}"
                            class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-3 py-1 rounded-full hover:bg-bluesoft transition-colors">
                            {{ $article->category->name }}
                        </a>
                    @endif
                </div>

                <h1 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark mt-5 leading-tight reveal">
                    {{ $article->title }}
                </h1>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 mt-5 text-xs text-bluedark/50 reveal">
                    @if ($article->author)
                        <span class="flex items-center gap-1.5">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            {{ $article->author->name }}
                        </span>
                    @endif

                    @if ($article->published_at)
                        <span class="flex items-center gap-1.5">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="M16 2v4M8 2v4M2 10h20" />
                            </svg>
                            {{ $article->published_at->translatedFormat('d F Y') }}
                        </span>
                    @endif

                    <span class="flex items-center gap-1.5">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        {{ number_format((int) $article->views) }}kali dibaca
                    </span>
                </div>
            </div>
        </header>

        {{-- Article body --}}
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 py-12 sm:py-16">
            @if (filled($article->thumbnail) || $article->media->isNotEmpty())
                <div class="rounded-3xl overflow-hidden border border-bluelight shadow-card mb-8">
                    <x-public.media :model="$article" column="thumbnail" :alt="$article->title" class="w-full aspect-[16/9]" />
                </div>
            @endif

            @if (filled($article->excerpt))
                <p class="text-bluedark/70 text-base sm:text-lg font-medium leading-relaxed border-l-4 border-blueprim pl-4 mb-8">
                    {{ $article->excerpt }}
                </p>
            @endif

            <div class="prose-article">
                {!! nl2br(e($article->content)) !!}
            </div>

            <div class="mt-10 pt-8 border-t border-bluelight flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('public.articles.index') }}"
                    class="inline-flex items-center gap-2 font-heading font-semibold text-sm text-bluedark hover:text-blueprim transition-colors">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                        aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    Kembali ke daftar berita
                </a>
            </div>
        </div>
    </article>

    {{-- Related articles --}}
    @if ($relatedArticles->isNotEmpty())
        <section class="py-14 sm:py-16 bg-white border-y border-bluelight">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-bluedark">Artikel Lainnya</h2>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 mt-8">
                    @foreach ($relatedArticles as $related)
                        <x-public.article-card :article="$related" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
