@extends('layouts.bk')

@section('title', 'Dashboard BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Bimbingan Konseling</h1>
      <p class="text-sm text-bluedark/60 mt-1">
        Pemantauan kedisiplinan, izin keluar, dan rekam jejak konseling siswa
      </p>
    </div>
    <div class="text-right">
      <div class="badge badge-blue">{{ $academicYear?->name ?? 'Tahun ajaran belum diatur' }}</div>
      @if($setting)
        <p class="text-[11px] text-bluedark/50 mt-1">
          Poin awal {{ $setting->initial_points }} &middot; SP1 ≤ {{ $setting->sp1_threshold }} &middot; SP2 ≤ {{ $setting->sp2_threshold }} &middot; SP3 ≤ {{ $setting->sp3_threshold }}
        </p>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="{{ route('counselor.exit-permits.index') }}" class="kpi-card">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $studentsOutCount }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Siswa Sedang Izin Keluar</div>
      </div>
    </a>

    <a href="{{ route('counselor.exit-permits.index', ['status' => 'PENDING']) }}" class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $pendingPermitCount }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Pengajuan Izin Menunggu</div>
      </div>
    </a>

    <a href="{{ route('counselor.appeals.index', ['decision' => 'PENDING']) }}" class="kpi-card">
      <div class="kpi-icon bg-blueprim/10 text-blueprim">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M5 7h5l-2.5 6.5A3 3 0 0 0 10 15a3 3 0 0 0 4.9 0 3 3 0 0 0 2.5-1.5L14.9 7H19"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $pendingAppealCount }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Banding Menunggu Validasi</div>
      </div>
    </a>

    <a href="{{ route('counselor.discipline.index', ['type' => 'VIOLATION']) }}" class="kpi-card">
      <div class="kpi-icon bg-red-100 text-red-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $violationThisMonth }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Pelanggaran Bulan Ini</div>
      </div>
    </a>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="panel p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tren Monitoring</h2>
            <p class="text-xs text-bluedark/50">Data {{ $academicYear?->name ?? 'tahun ajaran berjalan' }}</p>
          </div>
          <select id="chartSelect" class="f-select w-auto text-xs py-1.5">
            <option value="izin">Pengajuan Izin (7 hari)</option>
            <option value="pelanggaran">Pelanggaran per Kategori</option>
          </select>
        </div>
        <div class="chart-box h-64">
          <canvas id="mainChart"></canvas>
        </div>
      </div>

      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Pengajuan Izin Menunggu Persetujuan</h2>
            <p class="text-xs text-bluedark/50">Diurutkan dari pengajuan terlama</p>
          </div>
          <a href="{{ route('counselor.exit-permits.index', ['status' => 'PENDING']) }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>

        <div class="space-y-2.5">
          @forelse($pendingPermits as $permit)
            <div class="hl-row hl-amber">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ $permit->student?->full_name }}</div>
                <div class="hl-sub truncate">
                  {{ $permit->student?->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
                  &middot; {{ $permit->reason?->name }}
                </div>
              </div>
              <a href="{{ route('counselor.exit-permits.show', $permit) }}" class="text-xs font-semibold flex-shrink-0 ml-2 underline">Proses</a>
            </div>
          @empty
            <p class="text-sm text-bluedark/50">Tidak ada pengajuan izin yang menunggu persetujuan.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="space-y-5">
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Poin Terendah</h2>
            <p class="text-xs text-bluedark/50">Siswa yang paling perlu dibina</p>
          </div>
          <a href="{{ route('counselor.discipline.index') }}" class="btn btn-outline btn-sm">Poin</a>
        </div>

        <div class="space-y-2.5">
          @forelse($topViolators as $row)
            @php
                $variant = match ($row['standing']['tone']) {
                    'critical' => 'hl-danger',
                    'high', 'warning' => 'hl-amber',
                    default => '',
                };
            @endphp
            <a href="{{ route('counselor.students.show', $row['student']) }}"
               class="hl-row {{ $variant }} block hover:opacity-90 transition-opacity">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ $row['student']->full_name }}</div>
                <div class="hl-sub truncate">{{ $row['student']->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}</div>
              </div>
              <span class="text-xs font-bold flex-shrink-0 ml-2">{{ $row['balance'] }} poin</span>
            </a>
          @empty
            <p class="text-sm text-bluedark/50">Belum ada catatan kedisiplinan siswa.</p>
          @endforelse
        </div>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Surat Peringatan Aktif</h2>
        <p class="text-xs text-bluedark/50 mb-4">Total {{ $activeLetterCount }} SP aktif tahun ajaran ini</p>

        <div class="grid grid-cols-3 gap-2.5 text-center">
          <div class="rounded-xl border border-bluelight py-3">
            <div class="font-heading text-lg font-bold text-amber-600 leading-none">{{ $sp1Count }}</div>
            <div class="text-[11px] text-bluedark/50 mt-1">SP1</div>
          </div>
          <div class="rounded-xl border border-bluelight py-3">
            <div class="font-heading text-lg font-bold text-red-500 leading-none">{{ $sp2Count }}</div>
            <div class="text-[11px] text-bluedark/50 mt-1">SP2</div>
          </div>
          <div class="rounded-xl border border-bluelight py-3">
            <div class="font-heading text-lg font-bold text-red-700 leading-none">{{ $sp3Count }}</div>
            <div class="text-[11px] text-bluedark/50 mt-1">SP3</div>
          </div>
        </div>

        <a href="{{ route('counselor.disciplinary-letters.index') }}" class="btn btn-outline btn-sm w-full mt-4">Kelola Surat Peringatan</a>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Ringkasan Aktivitas</h2>
        <p class="text-xs text-bluedark/50 mb-4">Siswa dengan catatan aktif tahun ajaran berjalan</p>
        <div class="space-y-2 text-xs text-bluedark/70">
          <div class="flex items-center justify-between">
            <span>Siswa dengan catatan disiplin / konseling</span>
            <span class="font-semibold text-bluedark">{{ $trackedStudentCount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Banding menunggu validasi</span>
            <span class="font-semibold text-bluedark">{{ $pendingAppealCount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Siswa sedang di luar sekolah</span>
            <span class="font-semibold text-bluedark">{{ $studentsOutCount }}</span>
          </div>
        </div>
        <a href="{{ route('counselor.counseling.index') }}" class="btn btn-primary btn-sm w-full mt-4">Catat Konseling Siswa</a>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('mainChart');

    if (! canvas) return;

    var datasets = {
      izin: {
        labels: @json($permitChart['labels']),
        label: 'Pengajuan Izin',
        data: @json($permitChart['totals']),
        color: '#90CAF9'
      },
      pelanggaran: {
        labels: @json($violationChart['labels']),
        label: 'Jumlah Kasus',
        data: @json($violationChart['totals']),
        color: '#EF4444'
      }
    };

    var active = datasets.izin;

    var chart = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: active.labels,
        datasets: [{
          label: active.label,
          data: active.data,
          backgroundColor: active.color,
          borderRadius: 8,
          maxBarThickness: 42
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F0F7FF' } },
          x: { grid: { display: false } }
        }
      }
    });

    document.getElementById('chartSelect').addEventListener('change', function (event) {
      var next = datasets[event.target.value] || datasets.izin;

      chart.data.labels = next.labels;
      chart.data.datasets[0].label = next.label;
      chart.data.datasets[0].data = next.data;
      chart.data.datasets[0].backgroundColor = next.color;
      chart.update();
    });

    requestAnimationFrame(function () { chart.resize(); });
  });
</script>
@endpush
