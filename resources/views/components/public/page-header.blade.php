@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<section class="relative overflow-hidden border-b border-bluelight bg-gradient-to-b from-bluelight/70 to-[#F7FBFF]">
    <div class="absolute -top-24 -right-20 w-80 h-80 rounded-full bg-bluesoft/25 blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-12 sm:py-16 md:py-20">
        @if (filled($eyebrow))
            <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold reveal">
                {{ $eyebrow }}
            </p>
        @endif

        <h1 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark mt-2 max-w-3xl reveal">
            {{ $title }}
        </h1>

        @if (filled($description))
            <p class="text-bluedark/60 mt-3 text-sm sm:text-base max-w-2xl leading-relaxed reveal">{{ $description }}</p>
        @endif

        @if (isset($breadcrumb))
            <nav aria-label="Breadcrumb" class="mt-5 reveal">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-bluedark/55 font-heading">
                    @foreach ($breadcrumb as $label => $url)
                        <li class="flex items-center gap-2">
                            @if ($url && ! $loop->last)
                                <a href="{{ $url }}" class="hover:text-blueprim transition-colors">{{ $label }}</a>
                                <span aria-hidden="true">/</span>
                            @else
                                <span class="text-bluedark/75">{{ $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
</section>
