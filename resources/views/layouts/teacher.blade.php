<!DOCTYPE html>
<html lang="id" class="teacher-portal-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard Guru') — SMK Negeri 2 Karanganyar</title>
<link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

{{-- Single stylesheet: Tailwind v4 build + the JHIC template custom layer,
     including the local Poppins/Inter @font-face. No Google Fonts, no CDN. --}}
@vite(['resources/css/app.css'])
<script src="{{ asset('assets/js/vendor/chart.umd.min.js') }}"></script>
@stack('styles')
</head>
<body class="font-body antialiased teacher-portal">
<div class="flex min-h-screen">

  <div class="db-sidebar-backdrop" id="dbBackdrop"></div>

  <aside class="db-sidebar w-56 lg:w-60 flex-shrink-0 flex flex-col p-3.5 lg:p-4" id="dbSidebar">
    <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-2.5 px-1 mb-5">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-8 h-8 object-contain">
      <div class="leading-tight">
        <div class="font-heading font-bold text-bluedark text-sm">SMK Negeri 2</div>
        <div class="text-[10.5px] text-bluedark/60 font-medium">Karanganyar</div>
      </div>
    </a>

    <nav class="flex flex-col gap-1.5 flex-1 db-scroll overflow-y-auto">
      <a href="{{ route('teacher.dashboard') }}" class="db-nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
        <span>Dashboard</span>
      </a>
      <a href="{{ route('teacher.assessments.index') }}" class="db-nav-item {{ request()->routeIs('teacher.assessments.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
        <span>Tugas</span>
      </a>
      <a href="{{ route('teacher.grading.index') }}" class="db-nav-item {{ request()->routeIs('teacher.grading.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
        <span>Penilaian</span>
      </a>
      <a href="{{ route('teacher.gradebooks.index') }}" class="db-nav-item {{ request()->routeIs('teacher.gradebooks.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
        <span>Buku Nilai</span>
      </a>
      <a href="{{ route('teacher.rubrics.index') }}" class="db-nav-item {{ request()->routeIs('teacher.rubrics.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
        <span>Rubrik</span>
      </a>
      <a href="{{ route('teacher.journals.index') }}" class="db-nav-item {{ request()->routeIs('teacher.journals.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Absensi Kelas</span>
      </a>
      <a href="{{ route('teacher.profile.index') }}" class="db-nav-item {{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
        <span>Profil &amp; Pengaturan</span>
      </a>
    </nav>
  </aside>

  <div class="flex-1 min-w-0 flex flex-col">

    <header class="db-topbar sticky top-0 z-30 flex items-center gap-3 px-4 md:px-6 h-13 min-h-[50px]">
      <button id="sidebarToggle" class="lg:hidden text-bluedark p-1.5 -ml-1.5" aria-label="Buka Menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
      </button>
      <div class="relative flex-1 max-w-xs hidden sm:block">
        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-bluesoft" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search here ..." class="w-full bg-bluelight/60 border border-bluelight rounded-md pl-8 pr-2.5 py-1 text-xs outline-none focus:border-blueprim">
      </div>
      <div class="ml-auto flex items-center gap-2.5">
        <button class="relative w-8 h-8 rounded-lg bg-bluelight/70 flex items-center justify-center text-bluedark" title="Notifikasi">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-red-500 border border-white"></span>
        </button>
        <div class="relative" id="profileMenuWrap">
          <button type="button" class="profile-trigger" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'GR', 0, 2)) }}</div>
          </button>
          <div class="profile-dropdown" id="profileDropdown" role="menu">
            <div class="profile-dropdown__header">
              <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr(auth()->user()->name ?? 'GR', 0, 2)) }}</div>
              <div class="leading-tight">
                <div class="text-xs font-semibold text-bluedark">{{ auth()->user()->name ?? 'Guru Pengajar' }}</div>
                <div class="text-[10px] text-bluedark/50">Guru</div>
              </div>
            </div>
            <a href="{{ route('teacher.profile.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-bluedark hover:bg-bluelight rounded-lg transition-colors mb-1">
              <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
              Profil &amp; Akun
            </a>
            <a href="{{ route('public.home') }}" target="_blank" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-bluedark hover:bg-bluelight rounded-lg transition-colors mb-1">
              <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              Web Publik
            </a>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="profile-dropdown__logout w-full text-left cursor-pointer" role="menuitem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Keluar
              </button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 p-3.5 sm:p-5 lg:p-6 space-y-4 lg:space-y-5">

      @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
          </div>
          <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 p-1 cursor-pointer">&times;</button>
        </div>
      @endif

      @if(session('error'))
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between shadow-xs">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
          </div>
          <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1 cursor-pointer">&times;</button>
        </div>
      @endif

      @if($errors->any())
        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-xs">
          <div class="flex items-center gap-2 font-semibold mb-1">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Terdapat beberapa kesalahan:</span>
          </div>
          <ul class="list-disc list-inside space-y-0.5 text-amber-800">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')

    </main>

    <x-public.sponsor-bar variant="app" />
  </div>
</div>

<script src="{{ asset('assets/js/dashboard-ui.js') }}"></script>

@stack('modals')
@stack('scripts')
</body>
</html>
