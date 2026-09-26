<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Guru') — {{ $schoolName ?? config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-body antialiased bg-[#F4F9FD] text-ink min-h-screen flex flex-col">

<div class="flex min-h-screen">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-ink/40 z-40 backdrop-blur-xs hidden transition-opacity lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside id="teacherSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-[#E3F2FD] flex flex-col p-5 -translate-x-full transition-transform duration-250 lg:translate-x-0 lg:static lg:z-auto shrink-0 shadow-sm lg:shadow-none">
        <!-- Brand Header -->
        <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3 px-1 mb-8 group">
            <div class="w-10 h-10 rounded-2xl bg-bluelight/70 p-2 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="leading-tight">
                <div class="font-heading font-bold text-bluedark text-sm">{{ $schoolName ?? config('app.name') }}</div>
                <div class="text-[11px] text-bluedark/60 font-medium">Sekolah Menengah Kejuruan</div>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-1.5 flex-1 overflow-y-auto pr-1">
            <!-- Dashboard -->
            <a href="{{ route('teacher.dashboard') }}" class="db-nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- Tugas & Ulangan -->
            <a href="{{ route('teacher.assessments.index') }}" class="db-nav-item {{ request()->routeIs('teacher.assessments.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                <span>Tugas</span>
            </a>

            <!-- Nilai / Buku Nilai -->
            <a href="{{ route('teacher.gradebooks.index') }}" class="db-nav-item {{ request()->routeIs('teacher.gradebooks.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
                <span>Nilai</span>
            </a>

            <!-- Catatan Nilai -->
            <a href="{{ route('teacher.grade-notes.index') }}" class="db-nav-item {{ request()->routeIs('teacher.grade-notes.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
                <span>Catatan Nilai</span>
            </a>

            <!-- Rubrik Penilaian -->
            <a href="{{ route('teacher.rubrics.index') }}" class="db-nav-item {{ request()->routeIs('teacher.rubrics.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                <span>Rubrik</span>
            </a>

            <!-- Absensi Kelas & Jurnal -->
            <a href="{{ route('teacher.journals.index') }}" class="db-nav-item {{ request()->routeIs('teacher.journals.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                <span>Absensi Kelas</span>
            </a>

            <!-- Profil & Pengaturan -->
            <a href="{{ route('teacher.profile.index') }}" class="db-nav-item {{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
                <span>Profil &amp; Pengaturan</span>
            </a>
        </nav>

        <!-- Sidebar Footer / Academic Year Badge -->
        <div class="pt-4 mt-auto border-t border-[#E3F2FD]">
            <div class="p-3 rounded-2xl bg-[#E3F2FD]/50 text-xs text-bluedark flex items-center justify-between">
                <div>
                    <div class="font-bold text-[11px] uppercase tracking-wider text-blueprim">Tahun Ajaran</div>
                    <div class="font-semibold text-xs mt-0.5">2026/2027 • Gasal</div>
                </div>
                <span class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper (Topbar + Content) -->
    <div class="flex-1 min-w-0 flex flex-col">

        <!-- Topbar -->
        <header class="sticky top-0 z-30 flex items-center gap-3 px-4 md:px-7 h-16 bg-white/95 backdrop-blur-md border-b border-[#E3F2FD]">
            <!-- Mobile Toggle -->
            <button id="sidebarToggle" type="button" class="lg:hidden text-bluedark p-2 -ml-2 rounded-xl hover:bg-bluelight transition-colors">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
            </button>

            <!-- Search Bar -->
            <div class="relative flex-1 max-w-sm hidden sm:block">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input 
                    type="text" 
                    placeholder="Cari tugas, kelas, siswa..." 
                    class="w-full bg-[#E3F2FD]/60 border border-[#E3F2FD] rounded-xl pl-10 pr-3.5 py-2 text-xs md:text-sm text-ink placeholder:text-[#9FB6CE] outline-none focus:border-blueprim focus:bg-white transition-all"
                >
            </div>

            <!-- Right Controls -->
            <div class="ml-auto flex items-center gap-3">
                <!-- Notifications Bell -->
                <button type="button" class="relative w-9 h-9 rounded-xl bg-[#E3F2FD]/70 hover:bg-[#E3F2FD] flex items-center justify-center text-bluedark transition-colors" title="Notifikasi">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="absolute 1 top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 border-2 border-white"></span>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative" id="profileDropdownWrapper">
                    <button 
                        type="button" 
                        id="profileMenuBtn" 
                        class="flex items-center gap-2.5 p-1 rounded-2xl hover:bg-[#E3F2FD]/50 transition-colors cursor-pointer select-none"
                    >
                        <div class="avatar-circle">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left leading-tight pr-1">
                            <div class="text-xs font-bold text-bluedark">{{ auth()->user()->name ?? 'Guru Pengajar' }}</div>
                            <div class="text-[11px] text-bluedark/60">Guru</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Menu Popup -->
                    <div 
                        id="profileMenu" 
                        class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl border border-[#E3F2FD] shadow-xl py-2 z-50 transition-all duration-150 animate-in fade-in"
                    >
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-3">
                            <div class="avatar-circle w-9 h-9 text-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 2)) }}
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-bluedark truncate">{{ auth()->user()->name ?? 'Guru Pengajar' }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email ?? auth()->user()->username }}</p>
                            </div>
                        </div>

                        <a href="{{ route('teacher.profile.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-bluedark hover:bg-bluelight/60 transition-colors">
                            <svg class="w-4 h-4 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Profil &amp; Akun</span>
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>

                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Keluar dari Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 p-4 md:p-7 space-y-6">

            <!-- Toast / Alert Flash Notifications -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 p-1">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm shadow-xs">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Terdapat kesalahan pengisian data:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs text-amber-800">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Dynamic View Content -->
            @yield('content')

        </main>
    </div>

</div>

<!-- Vanilla JS Interactions for Layout -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile Sidebar Drawer Toggle
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('teacherSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (toggleBtn && sidebar && backdrop) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            });

            backdrop.addEventListener('click', function () {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            });
        }

        // Profile Menu Dropdown
        const profileBtn = document.getElementById('profileMenuBtn');
        const profileMenu = document.getElementById('profileMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    profileMenu.classList.add('hidden');
                }
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
