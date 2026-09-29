@props([
    'navSections' => [],
])

@php
    $currentRoute = request()->route()?->getName();
    $isHomePage   = $currentRoute === 'public.home';
@endphp

<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-bluelight">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 h-16 md:h-20 flex items-center justify-between gap-4">

        {{-- Brand --}}
        <a href="{{ route('public.home') }}" class="flex items-center gap-2.5 md:gap-3 min-w-0">
            <img src="{{ asset('assets/img/logo.png') }}"
                alt="Logo {{ $schoolName }}"
                class="w-9 h-9 md:w-11 md:h-11 object-contain shrink-0">
            <span class="font-heading leading-tight truncate">
                <span class="block text-[13px] md:text-[15px] font-semibold text-bluedark truncate">{{ $schoolName }}</span>
                <span class="block text-[10px] md:text-xs font-medium text-blueprim tracking-wide">Sekolah Menengah Kejuruan</span>
            </span>
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden lg:flex items-center gap-4 xl:gap-6 font-heading text-sm font-medium text-bluedark/80">
            @php
                $templateNavItems = [
                    ['label' => 'Home',             'href' => $isHomePage ? '#beranda'          : route('public.home'),              'route' => 'public.home'],
                    ['label' => 'PPDB',             'href' => $isHomePage ? '#ppdb'             : route('public.ppdb.index'),        'route' => 'public.ppdb.index'],
                    ['label' => 'Jurusan',          'href' => $isHomePage ? '#jurusan'          : route('public.departments.index'), 'route' => 'public.departments.index'],
                    ['label' => 'PKL & Karier',     'href' => $isHomePage ? '#karier'           : route('public.career.index'),      'route' => 'public.career.index'],
                    ['label' => 'Produk Unggulan',  'href' => $isHomePage ? '#produk-unggulan'  : route('public.products.index'),    'route' => 'public.products.index'],
                    ['label' => 'Berita',           'href' => $isHomePage ? '#berita'           : route('public.articles.index'),    'route' => 'public.articles.index'],
                ];
            @endphp

            @foreach ($templateNavItems as $item)
                @php
                    $isActive = $currentRoute === $item['route'];
                @endphp
                <a href="{{ $item['href'] }}"
                    @if ($isActive) aria-current="page" @endif
                    class="hover:text-blueprim transition-colors {{ $isActive ? 'text-blueprim font-semibold' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- CTA: Daftar Sekarang (guest) / Dashboard (auth) --}}
        <div class="flex items-center gap-2 shrink-0">
            @auth
                <a href="{{ route(auth()->user()->dashboardRouteName()) }}"
                    class="hidden lg:inline-flex items-center gap-2 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-medium text-sm px-5 py-2.5 rounded-full">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="hidden lg:inline-flex items-center gap-2 border border-bluedark/25 hover:border-bluedark hover:bg-bluelight transition-colors text-bluedark font-heading font-medium text-sm px-5 py-2.5 rounded-full">
                    Masuk
                </a>
                <a href="{{ route('public.ppdb.index') }}"
                    class="hidden lg:inline-flex items-center gap-2 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-medium text-sm px-5 py-2.5 rounded-full">
                    Daftar Sekarang
                </a>
            @endauth

            {{-- Mobile menu toggle --}}
            <button type="button" data-nav-toggle aria-label="Buka menu" aria-expanded="false"
                aria-controls="publicMobileMenu"
                class="lg:hidden w-9 h-9 md:w-10 md:h-10 grid place-items-center rounded-lg border border-bluesoft/60 text-bluedark shrink-0 hover:bg-bluelight transition-colors">
                <svg data-nav-icon="open" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
                <svg data-nav-icon="close" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" class="hidden" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile navigation --}}
    <div id="publicMobileMenu" class="hidden lg:hidden border-t border-bluelight bg-white">
        <nav class="flex flex-col px-4 sm:px-6 py-4 gap-1 font-heading text-bluedark">
            <a href="{{ $isHomePage ? '#beranda' : route('public.home') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">Home</a>
            <a href="{{ $isHomePage ? '#ppdb' : route('public.ppdb.index') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">PPDB</a>
            <a href="{{ $isHomePage ? '#jurusan' : route('public.departments.index') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">Jurusan</a>
            <a href="{{ $isHomePage ? '#karier' : route('public.career.index') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">PKL &amp; Karier</a>
            <a href="{{ $isHomePage ? '#produk-unggulan' : route('public.products.index') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">Produk Unggulan</a>
            <a href="{{ $isHomePage ? '#berita' : route('public.articles.index') }}"
                class="py-2.5 px-2 rounded-lg hover:bg-bluelight transition-colors">Berita</a>

            @auth
                <a href="{{ route(auth()->user()->dashboardRouteName()) }}"
                    class="mt-2 text-center bg-bluedark text-white py-2.5 px-2 rounded-full font-medium">
                    Dashboard
                </a>
            @else
                <a href="{{ route('public.ppdb.index') }}"
                    class="mt-2 text-center bg-blueprim text-white py-2.5 px-2 rounded-full font-medium">
                    Daftar Sekarang
                </a>
                <a href="{{ route('login') }}"
                    class="mt-1 text-center border border-bluesoft text-bluedark py-2.5 px-2 rounded-full font-medium hover:bg-bluelight transition-colors">
                    Masuk ke Portal
                </a>
            @endauth
        </nav>
    </div>
</header>
