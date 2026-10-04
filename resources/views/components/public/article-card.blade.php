@props([
    'article',
])

@php
    $categoryName = $article->category?->name ?? 'Informasi';
    $categorySlug = $article->category?->slug ?? 'informasi';
    $publishedAt = $article->published_at;
@endphp

<article {{ $attributes->merge([
    'class' => 'berita-card link-card relative bg-white rounded-3xl overflow-hidden border border-bluelight shadow-card h-full',
    'data-category' => $categorySlug,
    'data-search' => trim(implode(' ', [$article->title, $article->excerpt, $categoryName])),
]) }}>
    <a href="{{ route('public.articles.show', $article) }}"
        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
        aria-label="Baca artikel: {{ $article->title }}"></a>

    <div class="berita-card__thumb h-44 sm:h-48 shrink-0">
        <x-public.media :model="$article" column="thumbnail" alt="{{ $article->title }}" />
    </div>

    <div class="p-5 flex flex-col flex-1">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start max-w-full truncate">
            {{ $categoryName }}
        </span>

        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug line-clamp-3">{{ $article->title }}</h3>

        <p class="text-xs text-bluedark/50 mt-2">
            {{ $publishedAt?->translatedFormat('d M Y') ?? 'Belum dipublikasikan' }}
        </p>

        @if (filled($article->excerpt))
            <p class="text-sm text-bluedark/60 mt-2.5 leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>
        @endif

        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">
            Baca selengkapnya
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </span>
    </div>
</article>
