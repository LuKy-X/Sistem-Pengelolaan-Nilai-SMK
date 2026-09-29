@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">

    <!-- Header Greeting -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Admin</h1>
            <p class="text-sm text-bluedark/60 mt-1">
                Selamat datang, <span class="font-semibold text-blueprim">{{ auth()->user()->name }}</span>. Pantau dan kelola seluruh ekosistem sekolah di sini.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge badge-blue">
                TA: {{ $activeYear?->name ?? 'Belum Aktif' }}
            </span>
            <span class="badge badge-green">
                {{ $activeSemester?->name ?? 'Semester Aktif' }}
            </span>
        </div>
    </div>

    <!-- KPI Metric Cards matching template -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight flex items-center gap-3.5 shadow-xs">
            <div class="kpi-icon w-12 h-12 rounded-xl bg-bluelight text-bluedark flex items-center justify-center shrink-0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalClasses }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Kelas</div>
            </div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight flex items-center gap-3.5 shadow-xs">
            <div class="kpi-icon w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalStudents }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Siswa</div>
            </div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight flex items-center gap-3.5 shadow-xs">
            <div class="kpi-icon w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18 8.4 3.9a1 1 0 0 1 0 1.83l-8.4 3.9a2 2 0 0 1-1.66 0L2.77 7.91a1 1 0 0 1 0-1.83l8.4-3.9a2 2 0 0 1 1.66 0Z"/><path d="m22 12.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 12.5"/><path d="m22 17.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 17.5"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalDepartments }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Jurusan</div>
            </div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight flex items-center gap-3.5 shadow-xs">
            <div class="kpi-icon w-12 h-12 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center shrink-0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalTeachers }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Guru &amp; Pendidik</div>
            </div>
        </div>
    </div>

    <!-- Middle Section: Presensi Hari Ini & Rombel Terbaru -->
    <div class="grid lg:grid-cols-3 gap-5">
        <!-- Panel Presensi Hari Ini -->
        <div class="panel p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Rekap Presensi Siswa Hari Ini</h2>
                    <p class="text-xs text-bluedark/50">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline btn-sm text-xs">
                    Lihat Selengkapnya
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                    <div class="text-2xl font-bold text-emerald-700 font-heading">{{ $attendanceStats['hadir'] }}</div>
                    <div class="text-xs text-emerald-600 font-medium mt-0.5">Hadir</div>
                </div>
                <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-100 text-center">
                    <div class="text-2xl font-bold text-blue-700 font-heading">{{ $attendanceStats['izin'] }}</div>
                    <div class="text-xs text-blue-600 font-medium mt-0.5">Izin</div>
                </div>
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-100 text-center">
                    <div class="text-2xl font-bold text-amber-700 font-heading">{{ $attendanceStats['sakit'] }}</div>
                    <div class="text-xs text-amber-600 font-medium mt-0.5">Sakit</div>
                </div>
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-100 text-center">
                    <div class="text-2xl font-bold text-rose-700 font-heading">{{ $attendanceStats['alpha'] }}</div>
                    <div class="text-xs text-rose-600 font-medium mt-0.5">Alpha</div>
                </div>
            </div>

            <!-- Rombel Terbaru -->
            <h3 class="font-heading font-semibold text-bluedark text-xs uppercase tracking-wider mb-2">Rombel Aktif</h3>
            <div class="overflow-x-auto">
                <table class="tbl w-full text-left">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Tingkat</th>
                            <th>Wali Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentClasses as $c)
                            <tr>
                                <td class="font-semibold">{{ $c->name }}</td>
                                <td>{{ $c->department?->name ?? '-' }}</td>
                                <td><span class="badge badge-gray">{{ $c->gradeLevel?->name ?? 'X' }}</span></td>
                                <td>{{ $c->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-xs text-bluedark/40">Belum ada kelas terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel Berita / Informasi Terkini -->
        <div class="panel p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Informasi Sekolah</h2>
                    <a href="{{ route('admin.cms.articles') }}" class="text-xs text-blueprim hover:underline">Kelola</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentArticles as $article)
                        <div class="p-3 rounded-xl border border-bluelight hover:bg-bluelight/20 transition-colors">
                            <div class="font-semibold text-xs text-bluedark line-clamp-1">{{ $article->title }}</div>
                            <div class="text-[11px] text-bluedark/50 mt-1 flex items-center justify-between">
                                <span>{{ $article->created_at?->diffForHumans() }}</span>
                                <span class="badge badge-blue text-[10px]">{{ $article->category?->name ?? 'Berita' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-bluedark/40">
                            Belum ada artikel atau berita sekolah.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Shortcut Cepat Admin -->
            <div class="mt-6 pt-4 border-t border-bluelight space-y-2">
                <div class="text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider">Aksi Cepat</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.academic.students.index') }}" class="btn btn-outline btn-sm text-xs justify-start w-full">
                        + Siswa Baru
                    </a>
                    <a href="{{ route('admin.users.teachers.index') }}" class="btn btn-outline btn-sm text-xs justify-start w-full">
                        + Guru Baru
                    </a>
                    <a href="{{ route('admin.academic.classes.index') }}" class="btn btn-outline btn-sm text-xs justify-start w-full">
                        + Rombel Baru
                    </a>
                    <a href="{{ route('admin.academic.years.index') }}" class="btn btn-outline btn-sm text-xs justify-start w-full">
                        Atur Semester
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
