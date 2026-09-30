@props([
    'variant' => 'card',
])

@php
    /**
     * Sponsor bar — logo JHIC 2026 + pendukung.
     * Varian mengikuti E:\jhic2026: --card (footer publik), --mini (card putih
     * kecil di kolom kiri footer), --app (footer dashboard), --login, dan
     * --siswa.
     *
     * @var string $variant
     */
    $variantClasses = match ($variant) {
        'app'   => 'sponsor-bar--app',
        'login' => 'sponsor-bar--compact sponsor-bar--login',
        'siswa' => 'sponsor-bar--compact sponsor-bar--siswa',
        'mini'  => 'sponsor-bar--mini',
        default => 'sponsor-bar--card',
    };
@endphp

<div class="sponsor-bar {{ $variantClasses }}">
    <div class="sponsor-bar__logos">
        <img src="{{ asset('assets/images/logo/jhic-2026.webp') }}"
            alt="Logo Jagoan Hosting Innovation Competition 2026"
            width="900" height="479" class="sb-jhic" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/jagoan-hosting.webp') }}"
            alt="Logo Jagoan Hosting"
            width="700" height="206" class="sb-jagoan" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/komdigi.webp') }}"
            alt="Logo Komdigi"
            width="500" height="351" class="sb-komdigi" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/garuda-spark.webp') }}"
            alt="Logo Garuda Spark Innovation Hub by Komdigi"
            width="700" height="367" class="sb-garuda" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/ngalup.webp') }}"
            alt="Logo Ngalup.co"
            width="700" height="111" class="sb-ngalup" loading="lazy" decoding="async">
    </div>
</div>
