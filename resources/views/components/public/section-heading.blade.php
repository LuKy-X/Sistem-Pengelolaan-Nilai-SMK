@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'left',
])

<div class="{{ $align === 'center' ? 'text-center max-w-xl mx-auto' : '' }} reveal">
    @if (filled($eyebrow))
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">
            {{ $eyebrow }}
        </p>
    @endif

    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">{{ $title }}</h2>

    @if (filled($description))
        <p class="text-bluedark/60 mt-3 text-sm sm:text-base {{ $align === 'center' ? 'mx-auto' : 'max-w-md' }}">
            {{ $description }}
        </p>
    @endif
</div>
