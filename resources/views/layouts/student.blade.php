<!DOCTYPE html>
<html lang="id" class="teacher-portal-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Portal Siswa') — SMK Negeri 2 Karanganyar</title>
<link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

@vite(['resources/css/app.css'])
<script src="{{ asset('assets/js/vendor/chart.umd.min.js') }}"></script>
<style>
  /* Seluruh perbaikan tampilan portal siswa ditulis di sini, bukan di app.css,
     supaya dashboard Guru dan Guru BK tidak ikut berubah.

     1. Indikator fokus keyboard.
     2. Perbaikan overflow: app.css memberi body { overflow-x: hidden } sehingga
        konten yang terlalu lebar terpotong dan tidak bisa digeser. Di portal
        siswa aturan itu dilonggarkan menjadi clip pada elemen html supaya
        halaman tetap bisa digeser horizontal bila memang ada konten melebar.
     3. Perbaikan .info-list yang memakai kolom 9rem 1fr. Karena 1fr berarti
        minmax(auto, 1fr), nilai yang panjang tanpa spasi seperti alamat email
        memaksa kolom melebar keluar kartu. minmax(0, 1fr) membuatnyashrink. */
  .teacher-portal :is(a, button, input, select, textarea, [tabindex]):focus-visible {
    outline: 2px solid #2196F3;
    outline-offset: 2px;
    border-radius: 6px;
  }

  .teacher-portal .f-input:focus-visible,
  .teacher-portal .f-select:focus-visible,
  .teacher-portal .f-textarea:focus-visible {
    outline: none;
  }

  .teacher-portal .db-nav-item:focus-visible {
    outline-offset: -2px;
  }

  .teacher-portal .badge:focus-visible {
    outline: 2px solid #0D47A1;
  }

  html {
    overflow-x: clip;
  }

  body.teacher-portal {
    overflow-x: clip;
  }

  .teacher-portal .info-list__row {
    grid-template-columns: minmax(0, 9rem) minmax(0, 1fr);
  }

  .teacher-portal .info-list__row dd {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .teacher-portal .info-list__row dt {
    overflow-wrap: break-word;
  }

  .teacher-portal :is(.panel, .crud-card, .kpi-card) :is(p, dd, dt, li, td, h1, h2, h3, span, div) {
    overflow-wrap: break-word;
  }

  @media (max-width: 639px) {
    .teacher-portal .info-list__row {
      grid-template-columns: minmax(0, 1fr);
      gap: 0.1rem;
    }

    .teacher-portal .info-list__row dt {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      opacity: 0.75;
    }
  }

  /* Animasi denyut pada lencana keterlambatan. Tanpa ini, animasi berjalan
     terus-menerus bagi pengguna yang meminta gerak dikurangi. */
  @media (prefers-reduced-motion: reduce) {
    .teacher-portal .countdown-pill.over {
      animation: none !important;
    }

    .teacher-portal .db-sidebar {
      transition: none !important;
    }

    .teacher-portal .profile-dropdown {
      transition: none !important;
    }
  }
</style>
@stack('styles')
</head>
<body class="font-body antialiased teacher-portal">
<a href="#konten-utama" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-2 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-bluedark focus:shadow-lg">Lewati ke konten utama</a>
<div class="flex min-h-screen">

  <div class="db-sidebar-backdrop" id="dbBackdrop"></div>

  <aside class="db-sidebar db-scroll w-48 sm:w-52 lg:w-56 flex-shrink-0 flex flex-col p-2.5 lg:p-3" id="dbSidebar" aria-label="Menu navigasi siswa">
    <div class="flex items-center gap-2 px-1 mb-4">
      <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 min-w-0 flex-1">
        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0">
        <span class="leading-tight min-w-0">
          <span class="font-heading font-bold text-bluedark text-xs block truncate">SMK Negeri 2</span>
          <span class="text-[9.5px] text-bluedark/60 font-medium block truncate">Portal Siswa</span>
        </span>
      </a>
      <button type="button" id="sidebarClose" class="lg:hidden text-bluedark/60 hover:text-bluedark p-1 -mr-1" aria-label="Tutup Menu">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <nav class="flex flex-col gap-1 flex-1 min-h-0">
      <a href="{{ route('student.dashboard') }}" class="db-nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
        <span>Dashboard</span>
      </a>
      <a href="{{ route('student.assignments.index') }}" class="db-nav-item {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
        <span>Tugas</span>
      </a>
      <a href="{{ route('student.grades.index') }}" class="db-nav-item {{ request()->routeIs('student.grades.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
        <span>Nilai</span>
      </a>
      <a href="{{ route('student.schedules.index') }}" class="db-nav-item {{ request()->routeIs('student.schedules.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Jadwal</span>
      </a>
      <a href="{{ route('student.exit-permits.index') }}" class="db-nav-item {{ request()->routeIs('student.exit-permits.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        <span>Izin Keluar</span>
      </a>
      <a href="{{ route('student.appeals.index') }}" class="db-nav-item {{ request()->routeIs('student.appeals.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M5 7h5l-2.5 6.5A3 3 0 0 0 10 15a3 3 0 0 0 4.9 0 3 3 0 0 0 2.5-1.5L14.9 7H19"/></svg>
        <span>Banding</span>
      </a>
      <a href="{{ route('student.discipline.index') }}" class="db-nav-item {{ request()->routeIs('student.discipline.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span>Buku Saku</span>
      </a>
      <a href="{{ route('student.profile.index') }}" class="db-nav-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
        <span>Profil</span>
      </a>
      <a href="{{ route('student.notifications.index') }}" class="db-nav-item {{ request()->routeIs('student.notifications.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span>Notifikasi</span>
        @if(($unreadNotificationCount ?? 0) > 0)
          <span class="ml-auto badge badge-red !px-1.5 !py-0.5 text-[10px] leading-none">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
        @endif
      </a>
    </nav>
  </aside>

  <div class="flex-1 min-w-0 flex flex-col">

    <header class="db-topbar sticky top-0 z-30 flex items-center gap-2 px-3 sm:px-4 md:px-5 h-11 min-h-[44px]">
      <button id="sidebarToggle" class="lg:hidden text-bluedark p-1.5 -ml-1.5" aria-label="Buka Menu" aria-expanded="false" aria-controls="dbSidebar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
      </button>
      <div class="hidden sm:block">
        <div class="text-[10px] text-bluedark/50 leading-none" id="todayLabel">&nbsp;</div>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <div class="relative" id="profileMenuWrap">
          <button type="button" class="profile-trigger" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'SW', 0, 2)) }}</div>
          </button>
          <div class="profile-dropdown" id="profileDropdown" role="menu">
            <div class="profile-dropdown__header">
              <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr(auth()->user()->name ?? 'SW', 0, 2)) }}</div>
              <div class="leading-tight">
                <div class="text-xs font-semibold text-bluedark">{{ auth()->user()->name ?? 'Siswa' }}</div>
                <div class="text-[10px] text-bluedark/50">Siswa</div>
              </div>
            </div>
            <a href="{{ route('student.profile.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-bluedark hover:bg-bluelight rounded-lg transition-colors mb-1">
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

    <main id="konten-utama" class="flex-1 p-2.5 sm:p-4 lg:p-5 space-y-3.5 lg:space-y-4">

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
<script>
  // Perbaikan drawer navigasi untuk layar kecil.
  //
  // dashboard-ui.js sudah membuka dan menutup sidebar, tetapi tidak mengunci
  // scroll halaman di belakangnya, tidak menyediakan tombol tutup, dan tidak
  // menutup drawer dengan tombol Escape. Ketiganya dilengkapi di sini agar
  // dashboard Guru dan Guru BK tidak ikut berubah.
  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('dbSidebar');
    var backdrop = document.getElementById('dbBackdrop');

    if (!toggle || !sidebar || !backdrop) return;

    var isOpen = function () {
      return sidebar.classList.contains('open');
    };

    var close = function () {
      sidebar.classList.remove('open');
      backdrop.classList.remove('show');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Buka Menu');
      document.body.style.overflow = '';
    };

    // Tombol tutup di dalam drawer, untuk layar sentuh.
    var closeButton = document.getElementById('sidebarClose');

    if (closeButton) {
      closeButton.addEventListener('click', close);
    }

    // Mengunci scroll halaman supaya konten tidak bisa meluncur di belakang
    // drawer yang sedang terbuka.
    var observe = function () {
      if (isOpen()) {
        document.body.style.overflow = 'hidden';
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Tutup Menu');
      } else {
        document.body.style.overflow = '';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka Menu');
      }
    };

    new MutationObserver(observe).observe(sidebar, {
      attributes: true,
      attributeFilter: ['class']
    });

    // Escape menutup drawer.
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen()) {
        close();
      }
    });
  });

  // Delegasi global untuk buka/tutup modal via data-modal-open / data-modal-close.
  // Pola yang sama dengan dashboard Guru BK (layouts/bk.blade.php) supaya portal siswa
  // punya perilaku modal yang konsisten tanpa mengubah script bersama dashboard-ui.js.
  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-modal-open]');

    if (opener) {
      var before = document.querySelector('.modal-overlay.show');

      if (before && before.id !== opener.getAttribute('data-modal-open')) {
        closeModal(before.id);
      }

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

  // Countdown kepulangan izin: deterministik dari planned_return_at vs waktu riil,
  // pola yang sama dengan dashboard Guru BK.
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
        var hours = Math.floor(absolute / 3600);
        var text = (hours > 0 ? pad(hours) + ':' : '') + pad(Math.floor((absolute % 3600) / 60)) + ':' + pad(absolute % 60);
        var state = remaining < 0 ? 'over' : (remaining <= 300 ? 'soon' : 'ok');

        pill.className = 'countdown-pill ' + state;
        pill.innerHTML = '<span class="dot"></span>' + (remaining < 0 ? 'Telat ' + text : 'Sisa ' + text);
      });
    };

    tick();
    window.setInterval(tick, 1000);
  });
</script>
@stack('scripts')
</body>
</html>
