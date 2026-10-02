<!DOCTYPE html>
<html lang="id" class="teacher-portal-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') — SMK Negeri 2 Karanganyar</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('assets/js/vendor/chart.umd.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dropdowns.css') }}">
    @stack('styles')
</head>
<body class="font-body antialiased teacher-portal">

<div class="flex min-h-screen">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="db-sidebar-backdrop" id="dbBackdrop"></div>

    <!-- Sidebar Navigation (Sinkron 100% dengan Panel Guru & public/assets) -->
    <aside class="db-sidebar w-48 sm:w-52 lg:w-56 flex-shrink-0 flex flex-col p-2.5 lg:p-3" id="dbSidebar">
        <!-- Brand Header -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-1 mb-4">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-7 h-7 object-contain">
            <div class="leading-tight">
                <div class="font-heading font-bold text-bluedark text-xs">SMK Negeri 2</div>
                <div class="text-[9.5px] text-bluedark/60 font-medium">Karanganyar</div>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-1 flex-1 db-scroll overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="db-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- Akademik Dropdown -->
            @php
                $isAcademicActive = request()->routeIs('admin.academic.*');
            @endphp
            <div class="db-nav-group {{ $isAcademicActive ? 'open' : '' }}" id="navGroupAcademic">
                <button type="button" class="db-nav-item w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                        <span>Akademik</span>
                    </div>
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="db-subnav">
                    <div>
                        <a href="{{ route('admin.academic.years.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.years.*') || request()->routeIs('admin.academic.semesters.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>Tahun &amp; Semester</span>
                        </a>
                        <a href="{{ route('admin.academic.departments.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.departments.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            <span>Jurusan</span>
                        </a>
                        <a href="{{ route('admin.academic.classes.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.classes.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Rombel / Kelas</span>
                        </a>
                        <a href="{{ route('admin.academic.subjects.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.subjects.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>Mata Pelajaran</span>
                        </a>
                        <a href="{{ route('admin.academic.teaching-assignments.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.teaching-assignments.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            <span>Penugasan Guru</span>
                        </a>
                        <a href="{{ route('admin.academic.schedules.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.schedules.*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Jadwal Pelajaran</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Siswa -->
            <a href="{{ route('admin.academic.students.index') }}" class="db-nav-item {{ request()->routeIs('admin.academic.students.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <span>Data Siswa</span>
            </a>

            <!-- Guru & Tenaga Pendidik -->
            <a href="{{ route('admin.users.teachers.index') }}" class="db-nav-item {{ request()->routeIs('admin.users.teachers.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                <span>Data Guru</span>
            </a>

            <!-- Pengguna Sistem -->
            <a href="{{ route('admin.users.index') }}" class="db-nav-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Akun Pengguna</span>
            </a>

            <!-- Absensi Siswa -->
            <a href="{{ route('admin.attendance.index') }}" class="db-nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
                <span>Absensi Siswa</span>
            </a>

            <!-- Monitoring Nilai -->
            <a href="{{ route('admin.grades.index') }}" class="db-nav-item {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                <span>Monitoring Nilai</span>
            </a>

            <!-- Layanan BK & Disiplin -->
            <a href="{{ route('admin.guidance.index') }}" class="db-nav-item {{ request()->routeIs('admin.guidance.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>BK &amp; Disiplin</span>
            </a>

            <!-- CMS & Informasi Dropdown -->
            @php
                $isCmsActive = request()->routeIs('admin.cms.*');
            @endphp
            <div class="db-nav-group {{ $isCmsActive ? 'open' : '' }}" id="navGroupCms">
                <button type="button" class="db-nav-item w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span>Portal &amp; CMS</span>
                    </div>
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="db-subnav">
                    <div>
                        <a href="{{ route('admin.cms.profile') }}" class="db-nav-item {{ request()->routeIs('admin.cms.profile*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span>Profil Sekolah</span>
                        </a>
                        <a href="{{ route('admin.cms.articles') }}" class="db-nav-item {{ request()->routeIs('admin.cms.articles*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            <span>Berita &amp; Artikel</span>
                        </a>
                        <a href="{{ route('admin.cms.ppdb') }}" class="db-nav-item {{ request()->routeIs('admin.cms.ppdb*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>PPDB</span>
                        </a>
                        <a href="{{ route('admin.cms.achievements') }}" class="db-nav-item {{ request()->routeIs('admin.cms.achievements*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                            <span>Prestasi</span>
                        </a>
                        <a href="{{ route('admin.cms.products') }}" class="db-nav-item {{ request()->routeIs('admin.cms.products*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>Produk Siswa</span>
                        </a>
                        <a href="{{ route('admin.cms.career') }}" class="db-nav-item {{ request()->routeIs('admin.cms.career*') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            <span>Karir &amp; BKK</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 min-w-0 flex flex-col">
        <!-- Topbar (100% Selaras dengan Panel Guru & public/assets) -->
        <header class="db-topbar sticky top-0 z-30 flex items-center gap-2 px-3 sm:px-4 md:px-5 h-11 min-h-[44px]">
            <button id="sidebarToggle" class="lg:hidden text-bluedark p-1.5 -ml-1.5" aria-label="Buka Menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
            </button>

            <div class="relative flex-1 max-w-xs hidden sm:block">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-bluesoft" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Search here ..." class="w-full bg-bluelight/60 border border-bluelight rounded-md pl-8 pr-2.5 py-1 text-xs outline-none focus:border-blueprim">
            </div>

            <div class="ml-auto flex items-center gap-2">
                <span class="hidden md:inline text-[11px] font-medium text-bluedark/60" id="todayLabel">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>

                <button class="relative w-7 h-7 rounded-lg bg-bluelight/70 flex items-center justify-center text-bluedark" title="Notifikasi">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-red-500 border border-white"></span>
                </button>

                <!-- Profile Dropdown Component -->
                <div class="relative" id="profileMenuWrap">
                    <button type="button" class="profile-trigger" id="profileTrigger" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</div>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown" role="menu">
                        <div class="profile-dropdown__header">
                            <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</div>
                            <div class="leading-tight">
                                <div class="text-xs font-semibold text-bluedark">{{ auth()->user()->name ?? 'Administrator' }}</div>
                                <div class="text-[10px] text-bluedark/50">Admin</div>
                            </div>
                        </div>

                        <a href="{{ route('admin.cms.profile') }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-bluedark hover:bg-bluelight rounded-lg transition-colors mb-1">
                            <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
                            Profil &amp; Akun
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

        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mx-2.5 sm:mx-4 lg:mx-5 mt-2.5 sm:mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 p-1 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-2.5 sm:mx-4 lg:mx-5 mt-2.5 sm:mt-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between shadow-xs" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1 cursor-pointer">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-2.5 sm:mx-4 lg:mx-5 mt-2.5 sm:mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-xs">
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

        <!-- Main Workspace -->
        <main class="flex-1 p-2.5 sm:p-4 lg:p-5 space-y-3.5 lg:space-y-4">
            @yield('content')
        </main>

        <!-- Footer Sponsor Bar (100% Selaras dengan Panel Guru & JHIC Template) -->
        <x-public.sponsor-bar variant="app" />
    </div>
</div>

<!-- Core Asset Scripts dari public/assets -->
<script src="{{ asset('assets/js/loader.js') }}"></script>
<script src="{{ asset('assets/js/dashboard-ui.js') }}"></script>
<script src="{{ asset('assets/js/admin-dropdowns.js') }}"></script>
<script src="{{ asset('assets/js/check-unique.js') }}"></script>
@stack('scripts')
</body>
</html>
