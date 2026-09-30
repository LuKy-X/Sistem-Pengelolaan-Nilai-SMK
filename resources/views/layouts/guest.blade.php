<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Masuk') — {{ $schoolName ?? config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="font-body antialiased bg-[#F4F9FD] text-ink">
    <a href="#konten-utama"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-bluedark focus:text-white focus:font-heading focus:text-sm">
        Lewati ke konten utama
    </a>

    <main id="konten-utama">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
