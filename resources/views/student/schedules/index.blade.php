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
    {{-- Ringkasan ketujuh hari. Jumlah pelajaran ditulis eksplisit supaya
         siswa bisa melihat hari mana yang terpadat tanpa membuka tiap hari,
         sesuatu yang dulu harus disimpulkan dari tinggi card. --}}
    <div class="grid grid-cols-7 gap-1 sm:gap-1.5" role="navigation" aria-label="Ringkasan jadwal per hari">
      @foreach(\App\Http\Controllers\Student\ScheduleController::DAY_NAMES as $dayNumber => $dayName)
        @php $count = $dayCounts[$dayNumber]; $isSelected = $dayNumber === $selectedDay; @endphp
        <a href="{{ route('student.schedules.index', ['day' => $dayNumber]) }}"
           @if($isSelected) aria-current="page" @endif
           @class([
             'flex flex-col items-center justify-center gap-0.5 rounded-xl border px-1 py-2 text-center transition-colors min-w-0',
             'border-blueprim bg-blueprim text-white shadow-sm' => $isSelected,
             'border-bluelight bg-white hover:bg-bluelight/50' => ! $isSelected,
           ])>
          <span @class([
            'text-[10px] sm:text-[11px] font-semibold truncate w-full',
            'text-white' => $isSelected,
            'text-bluedark' => ! $isSelected,
          ])>{{ \App\Http\Controllers\Student\ScheduleController::DAY_SHORT_NAMES[$dayNumber] }}</span>
          <span @class([
            'text-[11px] sm:text-xs font-bold leading-none',
            'text-white/90' => $isSelected,
            $count === 0 ? 'text-bluedark/35' : 'text-blueprim',
          ])>{{ $count }}</span>
          <span class="sr-only">{{ $dayName }}: {{ $count }} pelajaran</span>
        </a>
      @endforeach
    </div>

    {{-- Pemilih hari. Tombol panah berupa link biasa agar tetap jalan tanpa
         JavaScript, bisa di-back, dan bisa diklik kanan untuk menyalin URL. --}}
    <div class="panel p-3 sm:p-4">
      <div class="flex items-center gap-2 sm:gap-3">
        <a href="{{ route('student.schedules.index', ['day' => $previousDay]) }}"
           class="btn btn-outline px-2.5 sm:px-3 shrink-0"
           aria-label="Hari sebelumnya ({{ \App\Http\Controllers\Student\ScheduleController::DAY_NAMES[$previousDay] }})"
           rel="prev">&lsaquo;</a>

        <div class="flex-1 min-w-0">
          <label for="day-picker" class="sr-only">Pilih hari</label>
          <select id="day-picker"
                  class="f-select w-full"
                  data-day-picker
                  @if($selectedDay === $today) aria-describedby="day-picker-hint" @endif>
            @foreach(\App\Http\Controllers\Student\ScheduleController::DAY_NAMES as $dayNumber => $dayName)
              @php $count = $dayCounts[$dayNumber]; @endphp
              <option value="{{ $dayNumber }}" @selected($dayNumber === $selectedDay)>
                {{ $dayName }}@if($count > 0) &middot; {{ $count }} pelajaran @else &middot; tidak ada @endif
              </option>
            @endforeach
          </select>
        </div>

        <a href="{{ route('student.schedules.index', ['day' => $nextDay]) }}"
           class="btn btn-outline px-2.5 sm:px-3 shrink-0"
           aria-label="Hari berikutnya ({{ \App\Http\Controllers\Student\ScheduleController::DAY_NAMES[$nextDay] }})"
           rel="next">&rsaquo;</a>
      </div>

      <div class="flex items-center gap-2 mt-2.5 min-h-[1.25rem]">
        <h2 class="font-heading font-semibold text-bluedark text-sm truncate min-w-0">
          {{ \App\Http\Controllers\Student\ScheduleController::DAY_NAMES[$selectedDay] }}
        </h2>
        @if($selectedDay === $today)
          <span class="badge badge-blue shrink-0">Hari ini</span>
        @endif
        <span id="day-picker-hint" class="text-[11px] text-bluedark/50 ml-auto shrink-0">
          {{ $daySchedules->count() }} pelajaran
        </span>
      </div>
    </div>

    {{-- Satu-satunya card jadwal: hanya hari yang dipilih. --}}
    <div class="panel p-3 sm:p-4">
      <div class="flex flex-col gap-2">
        @forelse($daySchedules as $schedule)
          <a href="{{ route('student.schedules.show', $schedule) }}" class="block p-2.5 rounded-xl bg-bluelight/40 border border-bluelight hover:bg-bluelight/70 transition-colors">
            <div class="flex items-center justify-between gap-2">
              <div class="text-xs font-semibold text-bluedark truncate min-w-0">{{ $schedule->teachingAssignment?->subject?->name ?? 'Mapel' }}</div>
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
          <p class="text-[11px] text-bluedark/40 text-center py-6">
            Tidak ada pelajaran pada hari {{ \App\Http\Controllers\Student\ScheduleController::DAY_SHORT_NAMES[$selectedDay] }}.
          </p>
        @endforelse
      </div>
    </div>
  @endif

</div>

@push('scripts')
<script>
  // Dropdown hari memakai navigasi biasa, bukan AJAX: halaman cukup ringan dan
  // server sudah merender ulang daftar hari yang dipilih.
  document.querySelectorAll('[data-day-picker]').forEach(function (picker) {
    picker.addEventListener('change', function () {
      var url = new URL(window.location.href);
      url.searchParams.set('day', picker.value);
      window.location.assign(url.toString());
    });
  });
</script>
@endpush
@endsection
