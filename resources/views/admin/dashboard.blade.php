@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Admin</h1>
            <p class="text-sm text-bluedark/60 mt-1">
                Selamat datang, <span class="font-semibold text-bluedark">{{ auth()->user()->name }}</span>! Pantau dan kelola seluruh aktivitas sekolah di sini.
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

    <!-- KPI Metric Cards Row (Sinkron dengan Style Guru) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="kpiRow">
        <div class="kpi-card">
            <div class="kpi-icon bg-bluelight text-bluedark">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalStudents }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Siswa Terdaftar</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-blueprim/10 text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalClasses }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Rombongan Belajar</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-amber-100 text-amber-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-amber-600 leading-none">{{ $totalTeachers }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Guru &amp; Pendidik</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18 8.4 3.9a1 1 0 0 1 0 1.83l-8.4 3.9a2 2 0 0 1-1.66 0L2.77 7.91a1 1 0 0 1 0-1.83l8.4-3.9a2 2 0 0 1 1.66 0Z"/><path d="m22 12.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 12.5"/><path d="m22 17.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 17.5"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-emerald-600 leading-none">{{ $totalDepartments }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Jurusan Keahlian</div>
            </div>
        </div>
    </div>

    <!-- Main Section: 2 Kolom Konten + 1 Kolom Samping -->
    <div class="grid lg:grid-cols-3 gap-5">

        <!-- Kolom Kiri: Chart & Presensi (2 Kolom) -->
        <div class="space-y-5 lg:col-span-2">

            <!-- Panel Grafik Distribusi Siswa per Jurusan -->
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Distribusi Siswa per Jurusan</h2>
                        <p class="text-xs text-bluedark/50">Komparasi jumlah siswa aktif berdasarkan kompetensi keahlian</p>
                    </div>
                    <a href="{{ route('admin.academic.departments.index') }}" class="text-xs font-semibold text-blueprim hover:underline">
                        Lihat Jurusan &rarr;
                    </a>
                </div>
                <div class="chart-box">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            <!-- Panel Presensi & Rombel Aktif -->
            <div class="panel p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Presensi Hari Ini</h2>
                        <p class="text-xs text-bluedark/50">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <a href="{{ route('admin.attendance.index') }}" class="text-xs font-semibold text-blueprim hover:underline">
                        Lihat Rekap Presensi &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
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

                <div class="pt-2">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-heading font-semibold text-bluedark text-xs uppercase tracking-wider">Rombel Aktif Terbaru</h3>
                        <a href="{{ route('admin.academic.classes.index') }}" class="text-xs text-blueprim hover:underline">Semua Kelas &rarr;</a>
                    </div>
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
                                        <td class="font-semibold text-bluedark">
                                            <a href="{{ route('admin.academic.classes.show', $c) }}" class="hover:underline hover:text-blueprim">
                                                {{ $c->name }}
                                            </a>
                                        </td>
                                        <td>{{ $c->department?->name ?? '-' }}</td>
                                        <td><span class="badge badge-blue text-[10px]">{{ $c->gradeLevel?->name ?? 'Tingkat' }}</span></td>
                                        <td>{{ $c->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-xs text-bluedark/40">Belum ada rombel terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Kolom Kanan: Aksi Cepat & Berita Sekolah (1 Kolom) -->
        <div class="flex flex-col gap-5">

            <!-- Panel Aksi Cepat -->
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Aksi Cepat</h2>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.academic.classes.index') }}" class="hl-row hover:opacity-95 transition-opacity">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">Kelola Rombel / Kelas</div>
                            <div class="hl-sub truncate">Daftar kelas, wali kelas &amp; siswa</div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-white/20 text-white shrink-0 ml-2">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.academic.students.index') }}" class="hl-row hl-amber hover:opacity-95 transition-opacity">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">Data Induk Siswa</div>
                            <div class="hl-sub truncate">Pendaftaran NISN &amp; penempatan</div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-white/20 text-white shrink-0 ml-2">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.users.teachers.index') }}" class="hl-row hover:opacity-95 transition-opacity">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">Data Guru &amp; Pengajar</div>
                            <div class="hl-sub truncate">Akun pendidik &amp; penugasan</div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-white/20 text-white shrink-0 ml-2">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.guidance.index') }}" class="hl-row hl-danger hover:opacity-95 transition-opacity">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">Layanan BK &amp; Kedisiplinan</div>
                            <div class="hl-sub truncate">Surat peringatan, izin &amp; poin</div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-white/20 text-white shrink-0 ml-2">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Panel Berita & Informasi Sekolah -->
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Informasi Sekolah</h2>
                    <a href="{{ route('admin.cms.articles') }}" class="text-xs font-semibold text-blueprim hover:underline">Kelola &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentArticles as $article)
                        <div class="p-3 rounded-xl border border-bluelight hover:bg-bluelight/20 transition-colors">
                            <div class="font-semibold text-xs text-bluedark line-clamp-1">{{ $article->title }}</div>
                            <div class="text-[11px] text-bluedark/50 mt-1.5 flex items-center justify-between">
                                <span>{{ $article->created_at?->diffForHumans() }}</span>
                                <span class="badge badge-blue text-[10px]">{{ $article->category?->name ?? 'Berita' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-bluedark/40 py-4 text-center">Belum ada artikel atau pengumuman.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/vendor/chart.umd.min.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const ctx = document.getElementById("mainChart");
    if (ctx && window.Chart) {
        const labels = @json($chartLabels);
        const data = @json($chartData);

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels.length ? labels : ["PPLG", "TJKT", "DKV", "TBSM"],
                datasets: [{
                    label: "Jumlah Siswa",
                    data: data.length ? data : [0, 0, 0, 0],
                    backgroundColor: "#2196F3",
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { family: 'Inter' },
                            color: '#5C7C9E',
                            precision: 0
                        },
                        grid: { color: '#E3F2FD' }
                    },
                    x: {
                        ticks: {
                            font: { family: 'Inter' },
                            color: '#5C7C9E'
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush
