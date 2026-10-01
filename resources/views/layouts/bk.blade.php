<!DOCTYPE html>
<html lang="id" class="teacher-portal-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Bimbingan Konseling') — SMK Negeri 2 Karanganyar</title>
<link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

{{-- Single stylesheet: Tailwind v4 build + the JHIC template custom layer,
     including the local Poppins/Inter @font-face. No Google Fonts, no CDN. --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="{{ asset('assets/js/vendor/chart.umd.min.js') }}"></script>
@stack('styles')
</head>
{{-- "teacher-portal" is the shared compact dashboard theme scope used by the
     admin/guru dashboard; BK reuses it so both areas look identical. --}}
<body class="font-body antialiased teacher-portal">

<div class="page-transition-overlay is-hidden" id="pageTransitionOverlay" aria-hidden="true">
  <div class="page-transition-diagonal">
    <span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span>
  </div>
</div>
<script>
  try {
    if (sessionStorage.getItem('playPageTransition') === '1') {
      document.getElementById('pageTransitionOverlay')?.classList.remove('is-hidden');
    }
  } catch (e) {}
</script>

<div class="flex min-h-screen">

  <div class="db-sidebar-backdrop" id="dbBackdrop"></div>

  <aside class="db-sidebar w-48 sm:w-52 lg:w-56 flex-shrink-0 flex flex-col p-2.5 lg:p-3" id="dbSidebar">
    <a href="{{ route('counselor.dashboard') }}" class="flex items-center gap-2 px-1 mb-4">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo SMK Negeri 2 Karanganyar" class="w-7 h-7 object-contain">
      <div class="leading-tight">
        <div class="font-heading font-bold text-bluedark text-xs">SMK Negeri 2</div>
        <div class="text-[9.5px] text-bluedark/60 font-medium">Bimbingan Konseling</div>
      </div>
    </a>

    <nav class="flex flex-col gap-1 flex-1 db-scroll overflow-y-auto">
      <a href="{{ route('counselor.dashboard') }}" class="db-nav-item {{ request()->routeIs('counselor.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
        <span>Dashboard</span>
      </a>
      <a href="{{ route('counselor.exit-permits.index') }}" class="db-nav-item {{ request()->routeIs('counselor.exit-permits.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        <span>Izin Keluar</span>
      </a>
      <a href="{{ route('counselor.appeals.index') }}" class="db-nav-item {{ request()->routeIs('counselor.appeals.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M5 7h5l-2.5 6.5A3 3 0 0 0 10 15a3 3 0 0 0 4.9 0 3 3 0 0 0 2.5-1.5L14.9 7H19"/></svg>
        <span>Banding</span>
      </a>
      <a href="{{ route('counselor.discipline.index') }}" class="db-nav-item {{ request()->routeIs('counselor.discipline.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        <span>Poin Disiplin</span>
      </a>
      <a href="{{ route('counselor.disciplinary-letters.index') }}" class="db-nav-item {{ request()->routeIs('counselor.disciplinary-letters.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="14" y2="17"/></svg>
        <span>Surat Peringatan</span>
      </a>
      <a href="{{ route('counselor.counseling.index') }}" class="db-nav-item {{ request()->routeIs('counselor.counseling.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
        <span>Rekam Konseling</span>
      </a>
      <a href="{{ route('counselor.students.index') }}" class="db-nav-item {{ request()->routeIs('counselor.students.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Data Siswa</span>
      </a>
    </nav>
  </aside>

  <div class="flex-1 min-w-0 flex flex-col">

    <header class="db-topbar sticky top-0 z-30 flex items-center gap-2 px-3 sm:px-4 md:px-5 h-11 min-h-[44px]">
      <button id="sidebarToggle" class="lg:hidden text-bluedark p-1.5 -ml-1.5" aria-label="Buka Menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
      </button>
      <div class="hidden sm:block text-[11px] text-bluedark/50 leading-none" id="todayLabel">&nbsp;</div>
      <div class="ml-auto flex items-center gap-2">
        @isset($pendingCount)
          <a href="{{ route('counselor.exit-permits.index', ['status' => 'PENDING']) }}"
             class="hidden md:inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 transition-colors">
            <span class="dot bg-amber-500"></span>
            {{ $pendingCount }} pengajuan menunggu
          </a>
        @endisset
        <button class="relative w-7 h-7 rounded-lg bg-bluelight/70 flex items-center justify-center text-bluedark" title="Notifikasi">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </button>
        <div class="relative" id="profileMenuWrap">
          <button type="button" class="profile-trigger" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'BK', 0, 2)) }}</div>
          </button>
          <div class="profile-dropdown" id="profileDropdown" role="menu">
            <div class="profile-dropdown__header">
              <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr(auth()->user()->name ?? 'BK', 0, 2)) }}</div>
              <div class="leading-tight">
                <div class="text-xs font-semibold text-bluedark">{{ auth()->user()->name ?? 'Guru BK' }}</div>
                <div class="text-[10px] text-bluedark/50">Guru Bimbingan Konseling</div>
              </div>
            </div>
            <a href="{{ route('counselor.students.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-bluedark hover:bg-bluelight rounded-lg transition-colors mb-1">
              <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              Data Siswa Binaan
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

    <main class="flex-1 p-2.5 sm:p-4 lg:p-5 space-y-3.5 lg:space-y-4">

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

<script src="{{ asset('assets/js/loader.js') }}"></script>
<script src="{{ asset('assets/js/dashboard-ui.js') }}"></script>
<script>
  // Delegasi global untuk buka/tutup modal via data-modal-open / data-modal-close.
  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-modal-open]');

    if (opener) {
      var before = document.querySelector('.modal-overlay.show');

      if (before && before.id !== opener.getAttribute('data-modal-open')) {
        closeModal(before.id);
      }

      opener.dispatchEvent(new CustomEvent('bk:before-open', { bubbles: true }));
      openModal(opener.getAttribute('data-modal-open'));
      return;
    }

    if (event.target.closest('[data-modal-close]')) {
      var overlay = event.target.closest('.modal-overlay');

      if (overlay) {
        closeModal(overlay.id);
      }
    }
  });

  // Countdown kepulangan izin: deterministik dari planned_return_at vs waktu riil.
  document.addEventListener('DOMContentLoaded', function () {
    var pills = Array.prototype.slice.call(document.querySelectorAll('[data-return-at]'));

    if (! pills.length) return;

    var pad = function (value) { return String(value).padStart(2, '0'); };

    var tick = function () {
      var nowSeconds = Math.floor(Date.now() / 1000);

      pills.forEach(function (pill) {
        var target = parseInt(pill.getAttribute('data-return-at'), 10);

        if (! target) return;

        var remaining = target - nowSeconds;
        var absolute = Math.abs(remaining);
        var text = pad(Math.floor(absolute / 60)) + ':' + pad(absolute % 60);
        var state = remaining < 0 ? 'over' : (remaining <= 300 ? 'soon' : 'ok');

        pill.className = 'countdown-pill ' + state;
        pill.innerHTML = '<span class="dot"></span>' + (remaining < 0 ? 'Telat ' + text : 'Sisa ' + text);
      });
    };

    tick();
    window.setInterval(tick, 1000);
  });
</script>
@stack('modals')
@stack('scripts')
</body>
</html>
