@props([
    'model' => null,
    'column' => null,
    'collection' => null,
    'src' => null,
    'disk' => null,
    'alt' => '',
    'class' => '',
    'ratio' => null,
    'fit' => 'cover',
    'icon' => 'image',
    'loading' => 'lazy',
])

@php
    $resolved = $src ?? (isset($model)
        ? app(\App\Services\PublicMediaService::class)->forModel($model, $column, $collection)
        : null);

    $wrapperClass = trim('relative overflow-hidden h-full w-full bg-bluelight/60 '.$class);
    $imageFitClass = $fit === 'contain' ? 'max-h-full max-w-full object-contain' : 'h-full w-full object-cover';
@endphp

@if (filled($resolved))
    <div class="{{ $wrapperClass }}" @if ($ratio) style="aspect-ratio: {{ $ratio }};" @endif>
        <img src="{{ $resolved }}" alt="{{ $alt }}" loading="{{ $loading }}" class="{{ $imageFitClass }} mx-auto">
    </div>
@else
    <div class="{{ $wrapperClass }} grid place-items-center text-blueprim/50" @if ($ratio) style="aspect-ratio: {{ $ratio }};" @endif
        role="img" aria-label="{{ $alt !== '' ? $alt : 'Gambar tidak tersedia' }}">
        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            @if ($icon === 'user')
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            @elseif ($icon === 'award')
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                <path d="M4 22h16" />
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
            @elseif ($icon === 'box')
                <path d="M21 8v11a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 19V8a2 2 0 0 1 1-1.73l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8Z" />
                <path d="m3.3 7 8.7 5 8.7-5" />
                <path d="M12 22V12" />
            @elseif ($icon === 'briefcase')
                <rect x="2" y="7" width="20" height="14" rx="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
            @else
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 6-10 7L2 6" />
            @endif
        </svg>
    </div>
@endif
