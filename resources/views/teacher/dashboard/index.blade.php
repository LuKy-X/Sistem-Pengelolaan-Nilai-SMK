@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Greeting & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">
                Selamat Datang, {{ $teacher->full_name }}! 👋
            </h1>
            <p class="text-sm text-bluedark/70 mt-1">
                Pantau perkembangan akademik, jadwal mengajar, dan buku nilai siswa Anda secara terpadu.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('teacher.assessments.create') }}" class="btn btn-primary btn-sm shadow-md">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Buat Tugas</span>
            </a>
            <a href="{{ route('teacher.journals.create') }}" class="btn btn-dark btn-sm shadow-md">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                <span>Isi Jurnal</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Metrics Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Kelas -->
        <div class="panel p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-[#E3F2FD] text-bluedark flex items-center justify-center shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            </div>
            <div>
                <span class="text-xs text-bluedark/60 font-medium block">Rombel Diampu</span>
                <span class="font-heading text-2xl font-bold text-bluedark">{{ $totalClasses }}</span>
                <span class="text-[11px] text-blueprim font-semibold block">Kelas aktif</span>
            </div>
        </div>

        <!-- KPI 2: Siswa -->
        <div class="panel p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <span class="text-xs text-bluedark/60 font-medium block">Total Siswa</span>
                <span class="font-heading text-2xl font-bold text-bluedark">{{ $totalStudents }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold block">Siswa terdaftar</span>
            </div>
        </div>

        <!-- KPI 3: Tugas / Ulangan -->
        <div class="panel p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
            </div>
            <div>
                <span class="text-xs text-bluedark/60 font-medium block">Tugas &amp; UH</span>
                <span class="font-heading text-2xl font-bold text-bluedark">{{ $totalAssessments }}</span>
                <span class="text-[11px] text-amber-600 font-semibold block">Total diterbitkan</span>
            </div>
        </div>

        <!-- KPI 4: Perlu Dinilai -->
        <div class="panel p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
            </div>
            <div>
                <span class="text-xs text-bluedark/60 font-medium block">Perlu Dinilai</span>
                <span class="font-heading text-2xl font-bold text-rose-600">{{ $pendingReviews }}</span>
                <span class="text-[11px] text-rose-500 font-semibold block">Menunggu koreksi</span>
            </div>
        </div>
    </div>

    <!-- Center Section: Chart & Side widgets -->
    <div class="grid lg:grid-cols-3 gap-5">
        
        <!-- Left: Class Averages Bar Chart -->
        <div class="panel p-5 lg:col-span-2 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-[16px]">Rata-rata Nilai per Kelas</h2>
                    <p class="text-xs text-bluedark/50">Capaian kompetensi pada buku nilai aktif</p>
                </div>
                <a href="{{ route('teacher.gradebooks.index') }}" class="text-xs font-semibold text-blueprim hover:underline">
                    Lihat Buku Nilai &rarr;
                </a>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="averageScoresChart"></canvas>
            </div>
        </div>

        <!-- Right Side: Tasks to Review & Today's Schedule -->
        <div class="flex flex-col gap-5">
            <!-- Tugas Perlu Dinilai -->
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-[#E3F2FD]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <h2 class="font-heading font-semibold text-bluedark text-[14px]">Tugas Perlu Dinilai</h2>
                    </div>
                    <a href="{{ route('teacher.assessments.index') }}" class="text-xs font-semibold text-blueprim hover:underline">
                        Semua
                    </a>
                </div>

                @if($recentPendingSubmissions->isEmpty())
                    <div class="py-6 text-center text-xs text-bluedark/50">
                        <svg class="w-8 h-8 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Semua pengumpulan tugas telah diperiksa!
                    </div>
                @else
                    <div class="flex flex-col gap-2.5">
                        @foreach($recentPendingSubmissions as $submission)
                            <div class="p-2.5 rounded-xl border border-[#E3F2FD] bg-[#FAFDFF] hover:bg-white hover:shadow-xs transition-all flex items-center justify-between">
                                <div class="overflow-hidden pr-2">
                                    <h4 class="text-xs font-bold text-bluedark truncate">
                                        {{ $submission->student?->full_name ?? 'Siswa' }}
                                    </h4>
                                    <p class="text-[11px] text-bluedark/60 truncate">
                                        {{ $submission->assessment?->title }} • {{ $submission->assessment?->teachingAssignment?->schoolClass?->name }}
                                    </p>
                                </div>
                                <a href="{{ route('teacher.assessments.show', $submission->assessment_id) }}" class="btn btn-outline btn-sm py-1 px-2.5 text-xs shrink-0">
                                    Nilai
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Jadwal Hari Ini -->
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-[#E3F2FD]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blueprim"></span>
                        <h2 class="font-heading font-semibold text-bluedark text-[14px]">Jadwal Mengajar Hari Ini</h2>
                    </div>
                    <a href="{{ route('teacher.journals.index') }}" class="text-xs font-semibold text-blueprim hover:underline">
                        Jurnal
                    </a>
                </div>

                @if($todaySchedules->isEmpty())
                    <div class="py-6 text-center text-xs text-bluedark/50">
                        <svg class="w-8 h-8 mx-auto text-blueprim/40 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Tidak ada jam mengajar hari ini.
                    </div>
                @else
                    <div class="flex flex-col gap-2.5">
                        @foreach($todaySchedules as $schedule)
                            <div class="p-2.5 rounded-xl border border-[#E3F2FD] bg-[#FAFDFF] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blueprim/10 text-blueprim font-bold text-xs flex items-center justify-center">
                                        {{ $schedule->room ?? 'R' }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-bluedark">
                                            {{ $schedule->teachingAssignment?->schoolClass?->name }} • {{ $schedule->teachingAssignment?->subject?->name }}
                                        </div>
                                        <div class="text-[11px] text-bluedark/60">
                                            Jam Ke-{{ $schedule->start_period_id }} sd {{ $schedule->end_period_id }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('teacher.journals.create') }}?schedule_id={{ $schedule->id }}" class="text-xs font-semibold text-blueprim hover:underline">
                                    Presensi &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Active Teaching Assignment Cards Section -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-[16px]">Daftar Rombel &amp; Mata Pelajaran yang Diampu</h2>
                <p class="text-xs text-bluedark/50">Akses cepat menuju buku nilai dan penugasan kelas</p>
            </div>
            <span class="badge badge-blue">{{ $assignments->count() }} Penugasan</span>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($assignments as $assignment)
                <div class="kelas-card">
                    <div class="flex items-center justify-between mb-2">
                        <div class="crud-card__icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </div>
                        <a href="{{ route('teacher.gradebooks.show', $assignment->gradebooks->first()->id ?? 1) }}" class="text-bluesoft hover:text-blueprim p-1" title="Buka Buku Nilai">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                    
                    <div class="font-heading font-bold text-bluedark text-sm">
                        {{ $assignment->schoolClass?->name ?? 'Kelas' }}
                    </div>
                    <div class="text-xs text-bluedark/70 font-medium">
                        {{ $assignment->subject?->name ?? 'Mata Pelajaran' }}
                    </div>

                    <div class="kelas-card__meta">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ $assignment->schoolClass?->students_count ?? 36 }} Siswa
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $assignment->weekly_hours }} Jam / Minggu
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-100">
                        <span class="badge badge-blue">Gasal</span>
                        <span class="badge badge-gray">2026/2027</span>
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <a href="{{ route('teacher.gradebooks.show', $assignment->gradebooks->first()->id ?? 1) }}" class="btn btn-outline btn-sm flex-1 text-center py-1 text-xs">
                            Buku Nilai
                        </a>
                        <a href="{{ route('teacher.assessments.create') }}?assignment_id={{ $assignment->id }}" class="btn btn-primary btn-sm flex-1 text-center py-1 text-xs">
                            + Tugas
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-bluedark/50">
                    Belum ada penugasan mengajar aktif semester ini.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('averageScoresChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Nilai Rata-rata',
                        data: {!! json_encode($chartAverages) !!},
                        backgroundColor: '#2196F3',
                        hoverBackgroundColor: '#0D47A1',
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 32,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                font: { family: 'Inter', size: 11 },
                                color: '#0D47A1'
                            },
                            grid: {
                                color: '#E3F2FD'
                            }
                        },
                        x: {
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#0D2A4A'
                            },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0D47A1',
                            titleFont: { family: 'Poppins', size: 12 },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
