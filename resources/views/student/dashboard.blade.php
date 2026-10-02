@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Siswa</h1>
    <p class="text-sm text-bluedark/60 mt-1">
      Selamat datang, <span class="font-semibold text-bluedark">{{ $student->full_name }}</span>!
      @if($student->currentEnrollment?->schoolClass)
        <span class="text-bluedark/50">{{ $student->currentEnrollment->schoolClass->name }} &middot; {{ $student->currentEnrollment->schoolClass->department?->name }}</span>
      @endif
    </p>
  </div>

  @if($activePermit)
    <div class="panel p-4 border-l-4 {{ $activePermit->isOverdue() ? 'border-red-400' : 'border-blueprim' }}">
      <div class="flex flex-wrap items-center gap-3 justify-between">
        <div class="min-w-0">
          <div class="text-[11px] font-semibold uppercase tracking-wide {{ $activePermit->isOverdue() ? 'text-red-600' : 'text-blueprim' }}">Izin keluar sedang aktif</div>
          <div class="font-heading font-semibold text-bluedark text-sm mt-0.5">{{ $activePermit->reason?->name }} &middot; kembali sebelum {{ $activePermit->planned_return_at?->format('H:i') }}</div>
        </div>
        <div class="flex items-center gap-3">
          <span class="countdown-pill ok" data-return-at="{{ $activePermit->planned_return_at?->timestamp }}"><span class="dot"></span>Memuat...</span>
          <a href="{{ route('student.exit-permits.show', $activePermit) }}" class="btn btn-outline btn-sm">Detail</a>
        </div>
      </div>
    </div>
  @elseif($pendingPermit)
    <div class="panel p-4 border-l-4 border-amber-400">
      <div class="flex flex-wrap items-center gap-3 justify-between">
        <div class="min-w-0">
          <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-600">Izin menunggu persetujuan BK</div>
          <div class="font-heading font-semibold text-bluedark text-sm mt-0.5">{{ $pendingPermit->reason?->name }} &middot; diajukan {{ $pendingPermit->requested_at?->diffForHumans() }}</div>
        </div>
        <a href="{{ route('student.exit-permits.show', $pendingPermit) }}" class="btn btn-outline btn-sm">Lihat Status</a>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
    <div class="kpi-card">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $todaySchedules->count() }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Jadwal Hari Ini</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-amber-600 leading-none">{{ $unfinishedCount }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Tugas Belum Selesai</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-blueprim/10 text-blueprim">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold {{ $academicSummary['overall']['average'] !== null ? ($academicSummary['overall']['passing'] ? 'text-emerald-600' : 'text-amber-600') : 'text-bluedark/40' }} leading-none">
          {{ $academicSummary['overall']['average'] !== null ? rtrim(rtrim(number_format($academicSummary['overall']['average'], 2), '0'), '.') : '-' }}
        </div>
        <div class="text-[11px] text-bluedark/55 mt-1">Rata-rata Nilai</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon {{ $standing['tone'] === 'safe' ? 'bg-emerald-100 text-emerald-600' : ($standing['tone'] === 'watch' || $standing['tone'] === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600') }}">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $balance }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Poin Kedisiplinan</div>
      </div>
    </div>
  </div>

  <div class="panel p-4 lg:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <h2 class="font-heading font-semibold text-bluedark text-[15px]">Status Akademis</h2>
      <a href="{{ route('student.grades.recap') }}" class="text-[11px] font-semibold text-blueprim hover:underline">Rekap nilai &rarr;</a>
    </div>

    @if($academicSummary['subjects']->isEmpty())
      <p class="text-xs text-bluedark/55">
        Belum ada nilai untuk ditampilkan. Nilai muncul setelah guru menambahkan Anda ke buku nilai kelas.
      </p>
    @else
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
          <div class="font-heading text-lg font-bold text-bluedark">{{ $academicSummary['overall']['subjectCount'] }}</div>
          <div class="text-[10px] text-bluedark/50">Mata pelajaran</div>
        </div>
        <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
          <div class="font-heading text-lg font-bold text-bluedark">{{ $academicSummary['overall']['predicate'] ?? '-' }}</div>
          <div class="text-[10px] text-bluedark/50">Predikat</div>
        </div>
        <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
          <div class="font-heading text-lg font-bold {{ $academicSummary['tasks']['pending'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $academicSummary['tasks']['pending'] }}</div>
          <div class="text-[10px] text-bluedark/50">Tugas belum dikumpulkan</div>
        </div>
        <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
          <div class="font-heading text-lg font-bold {{ $academicSummary['tasks']['overdue'] > 0 ? 'text-red-500' : 'text-bluedark' }}">{{ $academicSummary['tasks']['overdue'] }}</div>
          <div class="text-[10px] text-bluedark/50">Terlewat tenggat</div>
        </div>
      </div>

      @php
        $weakest = $academicSummary['subjects']
            ->filter(fn ($row) => $row['average'] !== null)
            ->sortBy('average')
            ->first();
      @endphp

      <div class="mt-3 pt-3 border-t border-bluelight/70">
        <div class="text-[10px] font-semibold text-bluedark/50 uppercase tracking-wide mb-1.5">Mata pelajaran terlemah</div>

        @if($weakest === null)
          <p class="text-xs text-bluedark/55">Belum ada nilai yang terisi pada buku nilai manapun.</p>
        @else
          <a href="{{ route('student.grades.show', $weakest['gradebook']) }}" class="flex items-center justify-between gap-2 text-xs text-bluedark/75 hover:text-bluedark">
            <span class="truncate">{{ $weakest['subject']?->name ?? 'Mata Pelajaran' }}</span>
            <span class="shrink-0 font-heading font-bold {{ $weakest['passing'] ? 'text-amber-600' : 'text-red-500' }}">
              {{ rtrim(rtrim(number_format($weakest['average'], 2), '0'), '.') }}
            </span>
          </a>
        @endif
      </div>
    @endif
  </div>

  <div class="grid lg:grid-cols-3 gap-4 lg:gap-5">
    <div class="panel p-4 lg:p-5 lg:col-span-2">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tugas Belum Selesai</h2>
        <a href="{{ route('student.assignments.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Lihat semua</a>
      </div>
      <div class="flex flex-col gap-2">
        @forelse($unfinishedAssignments as $assessment)
          @php $overdue = $assessment->due_at !== null && $assessment->due_at->isPast(); @endphp
          <div class="hl-row {{ $overdue ? 'hl-danger' : '' }}">
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ $assessment->title }}</div>
              <div class="hl-sub truncate">
                {{ $assessment->teachingAssignment?->subject?->name ?? 'Mapel' }}
                @if($assessment->due_at)
                  &middot; tenggat {{ $assessment->due_at->format('d M Y, H:i') }}
                @else
                  &middot; tanpa tenggat
                @endif
              </div>
            </div>
            <a href="{{ route('student.assignments.show', $assessment) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/25 hover:bg-white/40 text-white shrink-0 ml-2 transition-colors">
              Kerjakan
            </a>
          </div>
        @empty
          <p class="text-xs text-bluedark/50 py-3 text-center">Tidak ada tugas yang menunggu. Kerja bagus!</p>
        @endforelse
      </div>

      <div class="flex items-center justify-between mt-5 mb-3">
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tugas Terbaru</h2>
      </div>
      <div class="flex flex-col gap-2">
        @forelse($latestAssignments as $assessment)
          @php $submission = $assessment->submissions->first(); @endphp
          <a href="{{ route('student.assignments.show', $assessment) }}" class="flex items-center gap-3 p-2.5 rounded-xl border border-bluelight/80 hover:border-bluesoft hover:bg-bluelight/30 transition-colors">
            <div class="min-w-0 flex-1">
              <div class="text-xs font-semibold text-bluedark truncate">{{ $assessment->title }}</div>
              <div class="text-[11px] text-bluedark/50 truncate">{{ $assessment->teachingAssignment?->subject?->name ?? 'Mapel' }} &middot; {{ $assessment->published_at?->diffForHumans() }}</div>
            </div>
            @if($submission)
              <span class="badge badge-green">Terkumpul</span>
            @else
              <span class="badge badge-blue">Baru</span>
            @endif
          </a>
        @empty
          <p class="text-xs text-bluedark/50 py-3 text-center">Belum ada tugas yang diterbitkan.</p>
        @endforelse
      </div>
    </div>

    <div class="flex flex-col gap-4 lg:gap-5">
      <div class="panel p-4 lg:p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Jadwal Hari Ini</h2>
          <a href="{{ route('student.schedules.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Lihat semua</a>
        </div>
        <div class="flex flex-col gap-2">
          @forelse($todaySchedules as $schedule)
            <div class="hl-row">
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ $schedule->teachingAssignment?->subject?->name ?? 'Mapel' }}</div>
                <div class="hl-sub truncate">
                  Jam ke-{{ $schedule->startPeriod?->period_number }} sd {{ $schedule->endPeriod?->period_number }}
                  ({{ $schedule->startPeriod?->start_time }} - {{ $schedule->endPeriod?->end_time }})
                  @if($schedule->room) &middot; {{ $schedule->room }} @endif
</div>

              </div>
            </div>
          @empty
            <p class="text-xs text-bluedark/50 py-3 text-center">Tidak ada jadwal pelajaran hari ini.</p>
          @endforelse
        </div>
      </div>

      <div class="panel p-4 lg:p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Nilai Terbaru</h2>
          <a href="{{ route('student.grades.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Rekap nilai</a>
        </div>
        <div class="flex flex-col gap-2">
          @forelse($recentGrades as $grade)
            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-bluelight/80">
              <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold text-bluedark truncate">{{ $grade->column?->name ?? 'Nilai' }}</div>
                <div class="text-[11px] text-bluedark/50 truncate">{{ $grade->column?->gradebook?->teachingAssignment?->subject?->name ?? 'Mapel' }}</div>
              </div>
              <div class="font-heading font-bold text-blueprim text-sm shrink-0">{{ $grade->final_score !== null ? rtrim(rtrim(number_format((float) $grade->final_score, 2), '0'), '.') : '-' }}</div>
            </div>
          @empty
            <p class="text-xs text-bluedark/50 py-3 text-center">Belum ada nilai yang masuk.</p>
          @endforelse
        </div>
      </div>

      <div class="panel p-4 lg:p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Kedisiplinan</h2>
          <a href="{{ route('student.discipline.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Buku Saku</a>
        </div>
        <div class="flex items-center gap-3">
          <div class="font-heading text-3xl font-bold {{ $standing['tone'] === 'safe' ? 'text-emerald-600' : ($standing['tone'] === 'watch' || $standing['tone'] === 'warning' ? 'text-amber-600' : 'text-red-600') }}">{{ $balance }}</div>
          <div>
            <x-bk.point-badge :balance="$balance" :standing="$standing" :show-balance="false" />
            <div class="text-[11px] text-bluedark/50 mt-1">Saldo poin tahun ajaran berjalan</div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
