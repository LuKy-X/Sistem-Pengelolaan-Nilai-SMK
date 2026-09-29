@props([
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'secondaryLabel' => null,
    'secondaryUrl' => null,
])

{{-- This band always carries its own top margin, so the gap above it is the
     same on every page no matter which section (or conditional section) happens
     to render before it. Sections that sit directly above a CTA therefore have
     no bottom padding of their own. --}}
@if (filled($title))
    <section {{ $attributes->merge(['class' => 'max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-16 sm:mt-20 md:mt-24 mb-4']) }}>
        <div class="rounded-3xl bg-bluedark p-8 sm:p-10 text-center relative overflow-hidden reveal">
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>

            <p class="font-heading font-bold text-xl sm:text-2xl text-white relative">
                {{ $title }}
            </p>

            @if (filled($description))
                <p class="text-white/70 text-sm mt-1.5 relative max-w-xl mx-auto">
                    {{ $description }}
                </p>
            @endif

            @if (filled($actionLabel) && filled($actionUrl))
                <div class="mt-5 flex flex-col sm:flex-row items-center justify-center gap-3 relative">
                    <a href="{{ $actionUrl }}"
                        class="inline-flex items-center gap-2 bg-white text-bluedark font-heading font-semibold px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
                        {{ $actionLabel }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>

                    @if (filled($secondaryLabel) && filled($secondaryUrl))
                        <a href="{{ $secondaryUrl }}"
                            class="inline-flex items-center gap-2 border border-white/30 text-white font-heading font-medium px-6 py-3 rounded-full hover:bg-white/10 transition-colors text-sm">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endif
