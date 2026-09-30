<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', $schoolName ?? config('app.name'))@hasSection('title') — {{ $schoolName ?? config('app.name') }}@endif</title>

    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @else
        <meta name="description" content="@yield('meta_description', $schoolProfile?->description ?? 'Website resmi sekolah menengah kejuruan.')">
    @endif

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

    {{-- GSAP for scroll animations and parallax (local, from the JHIC template) --}}
    <script src="{{ asset('assets/vendor/gsap/gsap.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/gsap/ScrollTrigger.min.js') }}"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="font-body antialiased bg-[#F7FBFF] text-ink is-landing">
    {{-- Marks JS as available so scroll-reveal only hides content when it can be revealed again. --}}
    <script>document.documentElement.classList.add('js');</script>

    {{-- Page transition overlay (diagonal colour bands) --}}
    <div class="page-transition-overlay" id="pageTransitionOverlay" aria-hidden="true">
        <div class="page-transition-diagonal">
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
            <span class="page-transition-band"></span>
        </div>
    </div>

    <a href="#konten-utama"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-bluedark focus:text-white focus:font-heading focus:text-sm">
        Lewati ke konten utama
    </a>

    <x-public.navbar :nav-sections="$navSections ?? \App\Http\Controllers\Public\HomeController::navSections()" />

    <main id="konten-utama">
        @include('partials.flash')

        @yield('content')
    </main>

    <x-public.footer />

    {{-- AI Chat Widget --}}
    <div class="ai-chat-launcher">
        <button id="aiChatReset" type="button" aria-label="Mulai obrolan baru"
            class="ai-chat-fab ai-chat-fab--reset">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" aria-hidden="true">
                <path d="M21 12a9 9 0 11-2.9-6.6"/>
                <path d="M21 3v6h-6"/>
            </svg>
        </button>
        <button id="aiChatFab" type="button" aria-label="Buka chat AI" aria-expanded="false"
            class="ai-chat-fab ai-chat-fab--main">
            <span class="ai-chat-fab__icon ai-chat-fab__icon--chat">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" aria-hidden="true">
                    <rect x="4" y="7" width="16" height="12" rx="4"/>
                    <path d="M8 7V5a4 4 0 018 0v2"/>
                    <circle cx="9" cy="13" r="1.2" fill="white" stroke="none"/>
                    <circle cx="15" cy="13" r="1.2" fill="white" stroke="none"/>
                    <path d="M9 16.5c1 .8 5 .8 6 0"/>
                    <path d="M2 12h2M20 12h2"/>
                </svg>
            </span>
            <span class="ai-chat-fab__icon ai-chat-fab__icon--close">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </span>
        </button>
    </div>

    <div id="aiChatPanel" class="ai-chat-panel" hidden>
        <div class="bg-gradient-to-r from-blueprim to-bluedark px-4 py-3.5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="7" width="16" height="12" rx="4"/>
                        <path d="M8 7V5a4 4 0 018 0v2"/>
                        <circle cx="9" cy="13" r="1.2" fill="white" stroke="none"/>
                        <circle cx="15" cy="13" r="1.2" fill="white" stroke="none"/>
                    </svg>
                </div>
                <div>
                    <p class="font-heading font-semibold text-white text-sm leading-tight">Tanya AI {{ $schoolName ?? 'SMKN' }}</p>
                    <p class="text-[11px] text-white/70 leading-tight">Siap bantu jawab pertanyaanmu</p>
                </div>
            </div>
            <button id="aiChatClose" type="button" aria-label="Tutup chat"
                class="w-8 h-8 rounded-full hover:bg-white/15 flex items-center justify-center text-white transition-colors shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div id="aiChatMessages"
            class="flex-1 min-h-0 overflow-y-auto px-4 py-4 flex flex-col gap-3 bg-[#F7FBFF]">
            <div class="ai-chat-msg ai-chat-msg--bot">Halo! 👋 Aku asisten virtual {{ $schoolName ?? 'SMK' }}. Ada yang bisa dibantu seputar PPDB, jurusan, PKL, atau produk unggulan sekolah?</div>
        </div>

        <form id="aiChatForm" class="border-t border-bluelight p-3 flex items-center gap-2 shrink-0 bg-white">
            <input id="aiChatInput" type="text" autocomplete="off" placeholder="Tulis pertanyaanmu..."
                class="flex-1 text-sm bg-bluelight/60 rounded-full px-4 py-2.5 outline-none focus:ring-2 focus:ring-blueprim/40 text-bluedark placeholder:text-bluedark/40">
            <button type="submit" aria-label="Kirim pertanyaan"
                class="w-10 h-10 shrink-0 rounded-full bg-blueprim hover:bg-bluedark transition-colors flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" aria-hidden="true">
                    <path d="M22 2L11 13"/>
                    <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </button>
        </form>
    </div>

    @stack('scripts')
</body>

</html>
