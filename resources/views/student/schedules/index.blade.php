@extends('layouts.student')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Jadwal Pelajaran</h1>
    <p class="text-sm text-bluedark/60 mt-1">
      @if($enrollment?->schoolClass)
        Jadwal mingguan kelas <span class="font-semibold text-bluedark">{{ $enrollment->schoolClass->name }}</span>
        @if($enrollment->schoolClass->department) &middot; {{ $enrollment->schoolClass->department->name }} @endif
      @else
        Anda belum terdaftar pada kelas aktif manapun.
      @endif
    </p>
  </div>

  @if($enrollment === null)
    <div class="panel p-6 text-center">
      <p class="text-sm text-bluedark/60">Hubungi admin sekolah untuk penetapan rombongan belajar Anda.</p>
    </div>
  @else
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($dayNames as $dayNumber => $dayName)
        @php $daySchedules = $schedules->get($dayNumber, collect()); @endphp
        <div class="panel p-4 {{ $dayNumber === now()->dayOfWeekIso ? 'ring-2 ring-blueprim/40' : '' }}">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-heading font-semibold text-bluedark text-sm">{{ $dayName }}</h2>
            @if($dayNumber === now()->dayOfWeekIso)
              <span class="badge badge-blue">Hari ini</span>
            @endif
          </div>

          <div class="flex flex-col gap-2">
            @forelse($daySchedules as $schedule)
              <a href="{{ route('student.schedules.show', $schedule) }}" class="block p-2.5 rounded-xl bg-bluelight/40 border border-bluelight hover:bg-bluelight/70 transition-colors">
                <div class="flex items-center justify-between gap-2">
                  <div class="text-xs font-semibold text-bluedark truncate">{{ $schedule->teachingAssignment?->subject?->name ?? 'Mapel' }}</div>
                  <div class="text-[10px] font-semibold text-blueprim shrink-0">
                    Jam {{ $schedule->startPeriod?->period_number }}@if($schedule->end_period_id !== $schedule->start_period_id) &ndash; {{ $schedule->endPeriod?->period_number }}@endif
                  </div>
                </div>
                <div class="text-[11px] text-bluedark/55 mt-0.5">
                  {{ $schedule->startPeriod?->start_time }} &ndash; {{ $schedule->endPeriod?->end_time }}
                  @if($schedule->room) &middot; {{ $schedule->room }} @endif
                </div>
                <div class="text-[11px] text-bluedark/45 mt-0.5 truncate">{{ $schedule->teachingAssignment?->teacher?->full_name ?? '-' }}</div>
              </a>
            @empty
              <p class="text-[11px] text-bluedark/40 text-center py-2">Tidak ada pelajaran.</p>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
