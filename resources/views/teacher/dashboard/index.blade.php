@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Guru</h1>
    <p class="text-sm text-bluedark/60 mt-1">Selamat datang, <span class="font-semibold text-bluedark">{{ $teacher->full_name }}</span>! Pantau kegiatan mengajar Anda di sini.</p>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="kpiRow">
    <div class="kpi-card">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalStudents }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Total Siswa Diampu</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-blueprim/10 text-blueprim">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalClasses }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Kelas Diampu</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-amber-600 leading-none">{{ $pendingReviews }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Tugas Belum Dinilai</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-red-100 text-red-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalAssessments }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Total Tugas &amp; UH</div>
      </div>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="panel p-5 lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Rata-rata Nilai per Kelas</h2>
        <a href="{{ route('teacher.gradebooks.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Lihat Buku Nilai &rarr;</a>
      </div>
      <div class="chart-box">
        <canvas id="mainChart"></canvas>
      </div>
    </div>

    <div class="flex flex-col gap-5">
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tugas Perlu Dinilai</h2>
          <a href="{{ route('teacher.assessments.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Lihat semua</a>
        </div>
        <div class="flex flex-col gap-2" id="tugasBelumDinilaiList">
          @forelse($recentPendingSubmissions as $sub)
            <div class="hl-row hl-amber">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ $sub->assessment?->title }}</div>
                <div class="hl-sub truncate">{{ $sub->assessment?->teachingAssignment?->schoolClass?->name }} &middot; {{ $sub->student?->full_name }}</div>
              </div>
              <a href="{{ route('teacher.assessments.show', $sub->assessment_id) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/25 hover:bg-white/40 text-white shrink-0 ml-2 transition-colors">
                Nilai
              </a>
            </div>
          @empty
            <p class="text-xs text-bluedark/50 py-3 text-center">Semua tugas siswa telah dinilai.</p>
          @endforelse
        </div>
      </div>

      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Jadwal Mengajar Hari Ini</h2>
          <a href="{{ route('teacher.journals.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Lihat semua</a>
        </div>
        <div class="flex flex-col gap-2" id="jadwalHariIniList">
          @php
            $schedulesToShow = $todaySchedules->isNotEmpty() ? $todaySchedules : $allSchedules->take(3);
          @endphp
          @forelse($schedulesToShow as $schedule)
            <div class="hl-row">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ $schedule->teachingAssignment?->schoolClass?->name }} &middot; {{ $schedule->teachingAssignment?->subject?->name }}</div>
                <div class="hl-sub truncate">
                  @if($todaySchedules->isEmpty())
                    Hari {{ ['1'=>'Senin','2'=>'Selasa','3'=>'Rabu','4'=>'Kamis','5'=>'Jumat','6'=>'Sabtu','7'=>'Minggu'][$schedule->day_of_week] ?? '' }} &middot;
                  @endif
                  Jam ke-{{ $schedule->startPeriod?->period_number }} sd {{ $schedule->endPeriod?->period_number }} ({{ $schedule->startPeriod?->start_time }} - {{ $schedule->endPeriod?->end_time }})
                </div>
              </div>
            </div>
          @empty
            <p class="text-xs text-bluedark/50 py-3 text-center">Tidak ada jadwal mengajar.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById("mainChart");
  if (ctx && window.Chart) {
    const labels = @json($chartLabels);
    const data = @json($chartAverages);

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels.length ? labels : ["XII RA", "XII TA", "XII OA", "XII MA"],
        datasets: [{
          label: "Rata-rata Nilai",
          data: data.length ? data : [82, 79, 85, 77],
          backgroundColor: "#90CAF9",
          borderRadius: 8,
          maxBarThickness: 42
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { font: { family: 'Inter' }, color: '#5C7C9E' },
            grid: { color: '#E3F2FD' }
          },
          x: {
            ticks: { font: { family: 'Inter' }, color: '#5C7C9E' },
            grid: { display: false }
          }
        }
      }
    });
  }
});
</script>
@endpush
