@props([
    'department',
])

@php
    /**
     * Resolve the floating-art image for this department.
     * Tries short_name, then code (both uppercased) against the art map,
     * falls back to the generic cover / default.
     */
    $artMap  = config('public_site.department_art', []);
    $keyA    = strtoupper((string) $department->short_name);
    $keyB    = strtoupper((string) $department->code);
    $artPath = $artMap[$keyA] ?? $artMap[$keyB] ?? $artMap['default'] ?? 'assets/img/hero-jurusan.png';
    $artUrl  = asset($artPath);
@endphp

{{-- jurusan-item wraps the card + floating art so art overflows above the card --}}
<div {{ $attributes->merge(['class' => 'jurusan-item stagger-item group']) }}>

    <article class="jurusan-card link-card bg-white rounded-3xl border border-bluelight shadow-card">
        {{-- The clickable overlay spans card + floating art (link top is negative to cover the art area) --}}
        <a href="{{ route('public.departments.show', $department) }}"
            class="absolute inset-0 z-10 rounded-3xl jurusan-card__link focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
            aria-label="Selengkapnya tentang {{ $department->name }}"></a>

        <h3 class="font-heading font-semibold text-lg text-bluedark">{{ $department->name }}</h3>

        @if (filled($department->description))
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed line-clamp-3">{{ $department->description }}</p>
        @else
            <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">
                Program keahlian {{ $department->name }} dengan kurikulum berbasis industri dan sertifikasi kompetensi.
            </p>
        @endif

        <span class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-2 group-hover:gap-2.5 transition-all">
            Selengkapnya
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </span>
    </article>

    {{-- Floating art image — positioned above the card via .jurusan-card__art-wrap --}}
    <div class="jurusan-card__art-wrap">
        <img src="{{ $artUrl }}"
            alt="Ilustrasi {{ $department->name }}"
            class="jurusan-card__art"
            loading="lazy">
    </div>
</div>
