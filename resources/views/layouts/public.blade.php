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
    <script src="{{ asset('assets/vendor/gsap/gsap.min.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/gsap/ScrollTrigger.min.js') }}" defer></script>

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

    <main id="konten-utama" @if (request()->routeIs('public.home')) class="public-home" @endif>
        @include('partials.flash')

        @yield('content')
    </main>

    <x-public.footer />

    {{-- AI Chat Widget --}}
    <div class="ai-chat-launcher">
        <button id="aiChatReset" type="button" aria-label="Mulai obrolan baru"
            class="ai-chat-fab ai-chat-fab--reset">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M21 12a9 9 0 11-2.9-6.6"/>
                <path d="M21 3v6h-6"/>
            </svg>
        </button>
        <button id="aiChatFab" type="button" aria-label="Buka chat AI" aria-expanded="false"
            class="ai-chat-fab ai-chat-fab--main">
            <span class="ai-chat-fab__pulse" aria-hidden="true"></span>
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
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </span>
            </button>
    </div>

    <div id="aiChatPanel" class="ai-chat-panel" hidden
        data-opening-url="{{ route('public.chatbot.opening') }}"
        data-reply-url="{{ route('public.chatbot.reply') }}"
        data-csrf="{{ csrf_token() }}">

        <div class="ai-chat-head">
            <div class="ai-chat-avatar" aria-hidden="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <rect x="4" y="7" width="16" height="12" rx="4"/>
                    <path d="M8 7V5a4 4 0 018 0v2"/>
                    <circle cx="9" cy="13" r="1.2" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="13" r="1.2" fill="currentColor" stroke="none"/>
                    <path d="M9 16.5c1 .8 5 .8 6 0"/>
                </svg>
            </div>
            <div class="ai-chat-head__text">
                <p class="ai-chat-head__title">Tanya Chatbot</p>
            </div>
            <div class="ai-chat-head__actions">
                <button id="aiChatResetTop" type="button" aria-label="Mulai obrolan baru" class="ai-chat-icon-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M21 12a9 9 0 11-2.9-6.6"/>
                        <path d="M21 3v6h-6"/>
                    </svg>
                </button>
                <button id="aiChatClose" type="button" aria-label="Tutup chat" class="ai-chat-icon-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="aiChatMessages" class="ai-chat-body" role="log" aria-live="polite" aria-atomic="false"></div>

        <div id="aiChatSuggestions" class="ai-chat-suggestions" hidden></div>

        <form id="aiChatForm" class="ai-chat-composer" autocomplete="off">
            <label for="aiChatInput" class="sr-only">Tulis pertanyaanmu</label>
            <input id="aiChatInput" name="message" type="text" maxlength="500" autocomplete="off"
                placeholder="Tulis pertanyaanmu..."
                class="ai-chat-input">
            <button type="submit" aria-label="Kirim pertanyaan" class="ai-chat-send" data-send-label="Kirim pertanyaan">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 2L11 13"/>
                    <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </button>
        </form>
    </div>

    @stack('scripts')
</body>

</html>
