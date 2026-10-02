@extends('layouts.student')

@section('title', $dayName.' - '.($schedule->teachingAssignment?->subject?->name ?? 'Jadwal'))

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <a href="{{ route('student.schedules.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Kembali ke jadwal</a>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark mt-1">Detail Jadwal</h1>
  </div>

  <div class="panel p-5 lg:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0">
        <h2 class="font-heading text-lg font-bold text-bluedark">{{ $schedule->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}</h2>
        <p class="text-xs text-bluedark/55 mt-0.5">{{ $schedule->teachingAssignment?->schoolClass?->name ?? '-' }}</p>
      </div>
      <span class="badge badge-blue">{{ $dayName }}</span>
    </div>

    <dl class="info-list mt-4">
      <div class="info-list__row">
        <dt>Jam ke</dt>
        <dd>
          {{ $schedule->startPeriod?->period_number ?? '?' }}&ndash;{{ $schedule->endPeriod?->period_number ?? '?' }}
          <span class="text-bluedark/55">({{ $schedule->startPeriod?->start_time ?? '?' }} &ndash; {{ $schedule->endPeriod?->end_time ?? '?' }})</span>
        </dd>
      </div>
      <div class="info-list__row">
        <dt>Ruang</dt>
        <dd>{{ $schedule->room ?: '-' }}</dd>
      </div>
      <div class="info-list__row">
        <dt>Guru Pengajar</dt>
        <dd>{{ $schedule->teachingAssignment?->teacher?->full_name ?? '-' }}</dd>
      </div>
      <div class="info-list__row">
        <dt>Semester</dt>
        <dd>
          {{ $schedule->teachingAssignment?->semester?->name ?? '-' }}
          <span class="text-bluedark/55">
            {{ $schedule->teachingAssignment?->semester?->academicYear?->name ?? '' }}
          </span>
        </dd>
      </div>
      @if($schedule->teachingAssignment?->weekly_hours)
        <div class="info-list__row">
          <dt>Alokasi Mingguan</dt>
          <dd>{{ $schedule->teachingAssignment->weekly_hours }} jam pelajaran</dd>
        </div>
      @endif
    </dl>
  </div>

  <div class="panel p-4 lg:p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Jurnal Mengajar</h2>

    @if($journal === null)
      <p class="text-xs text-bluedark/55">
        Guru belum mengisi jurnal untuk pertemuan ini. Materi yang diajarkan akan muncul di sini setelah dicatat.
      </p>
    @else
      <div class="rounded-xl bg-bluelight/40 border border-bluelight px-3.5 py-3">
        <div class="flex flex-wrap items-center gap-2 mb-1.5">
          <span class="text-[11px] font-semibold text-bluedark">{{ $journal->journal_date->format('d M Y') }}</span>
          <span class="text-[10px] text-bluedark/45">
            {{ $journal->startPeriod?->period_number ?? '?' }}&ndash;{{ $journal->endPeriod?->period_number ?? '?' }}
          </span>
        </div>
        <p class="text-sm text-bluedark/85">{{ $journal->material }}</p>
        @if($journal->notes)
          <p class="text-[11px] text-bluedark/60 mt-2 pt-2 border-t border-bluelight/70">{{ $journal->notes }}</p>
        @endif
      </div>
    @endif
  </div>

</div>
@endsection