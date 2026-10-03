@extends('layouts.bk')

@section('title', 'Detail Jurnal Kelas — Guru BK')

@section('content')
<div class="space-y-5">

  @include('partials.journals.bk-tabs', ['tabQuery' => ['class_id' => $journal->teachingAssignment?->class_id, 'date' => $journal->journal_date?->format('Y-m-d')]])

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.journals.attendance', ['class_id' => $journal->teachingAssignment?->class_id, 'date' => $journal->journal_date?->format('Y-m-d')]) }}"
       class="font-semibold text-blueprim hover:underline" data-no-transition="true">Lihat Absensi</a>
    /
    <a href="{{ route('counselor.journals.history') }}" class="font-semibold text-blueprim hover:underline">Riwayat Jurnal</a>
    /
    <span class="font-semibold text-bluedark">Detail Jurnal</span>
  </p>

  <div class="panel p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="font-heading text-xl font-bold text-bluedark">
          {{ $journal->teachingAssignment?->schoolClass?->name ?? 'Kelas' }}
          &middot; Jam {{ $journal->startPeriod?->period_number ?? '—' }} sd {{ $journal->endPeriod?->period_number ?? '—' }}
        </h1>
        <p class="text-xs text-bluedark/55 mt-1">
          {{ $journal->journal_date?->locale('id')->isoFormat('dddd, D MMMM Y') }}
          &middot; {{ $journal->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
          &middot; Pengajar: {{ $journal->creator?->user?->name ?? $journal->creator?->full_name ?? 'Guru' }}
        </p>
      </div>
      <a href="{{ route('counselor.journals.attendance', ['class_id' => $journal->teachingAssignment?->class_id, 'date' => $journal->journal_date?->format('Y-m-d')]) }}"
         class="btn btn-outline btn-sm" data-no-transition="true">
        Buka di Lihat Absensi
      </a>
    </div>

    <div class="grid sm:grid-cols-4 gap-3 mt-4">
      <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3 text-center">
        <div class="font-heading text-2xl font-bold text-emerald-700">{{ $journal->hadir_count }}</div>
        <div class="text-[11px] text-emerald-800/70 mt-0.5">Hadir</div>
      </div>
      <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3 text-center">
        <div class="font-heading text-2xl font-bold text-amber-700">{{ $journal->sakit_count }}</div>
        <div class="text-[11px] text-amber-800/70 mt-0.5">Sakit</div>
      </div>
      <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-3 text-center">
        <div class="font-heading text-2xl font-bold text-blue-700">{{ $journal->izin_count }}</div>
        <div class="text-[11px] text-blue-800/70 mt-0.5">Izin</div>
      </div>
      <div class="rounded-xl border border-red-200 bg-red-50/50 p-3 text-center">
        <div class="font-heading text-2xl font-bold text-red-700">{{ $journal->alpha_count }}</div>
        <div class="text-[11px] text-red-800/70 mt-0.5">Alpha</div>
      </div>
    </div>

    <div class="mt-4 space-y-3">
      <div>
        <div class="text-[11px] text-bluedark/50 font-semibold uppercase tracking-wide">Materi / Kegiatan</div>
        <p class="text-sm text-bluedark mt-0.5">{{ $journal->material }}</p>
      </div>
      @php
        $extraNote = trim(preg_replace('/Hadir:\s*\d+\s*\|\s*Sakit:\s*\d+\s*\|\s*Izin:\s*\d+\s*\|\s*Alpha:\s*\d+/i', '', $journal->notes ?? ''));
      @endphp
      @if($extraNote)
        <div>
          <div class="text-[11px] text-bluedark/50 font-semibold uppercase tracking-wide">Catatan Kelas</div>
          <p class="text-xs text-bluedark/70 mt-0.5 whitespace-pre-line">{{ $extraNote }}</p>
        </div>
      @endif
    </div>
  </div>

  <div class="panel p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Rincian Absensi Siswa</h2>
        <p class="text-xs text-bluedark/50">Siswa dengan status izin, sakit, atau alpha pada sesi ini</p>
      </div>
      <span class="badge badge-blue">{{ $journal->attendances->where('status', \App\Enums\AttendanceStatus::Present)->count() }} tercatat hadir</span>
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>Status</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @php
            $rows = $journal->attendances
                ->sortBy(fn ($attendance) => $attendance->student?->full_name ?? '')
                ->values();
          @endphp
          @forelse($rows as $attendance)
            <tr>
              <td class="text-xs text-bluedark/50">{{ $loop->iteration }}</td>
              <td class="text-xs font-medium text-bluedark">
                <a href="{{ route('counselor.students.show', $attendance->student_id) }}" class="hover:text-blueprim" data-no-transition="true">
                  {{ $attendance->student?->full_name ?? 'Siswa #'.$attendance->student_id }}
                </a>
                <div class="text-[11px] text-bluedark/45">
                  {{ $attendance->student?->currentEnrollment?->schoolClass?->name ?? '—' }}
                </div>
              </td>
              <td class="text-xs text-bluedark/60 font-mono">{{ $attendance->student?->nis ?? '—' }}</td>
              <td>
                @php
                  $meta = match ($attendance->status) {
                    \App\Enums\AttendanceStatus::Sick => ['label' => 'Sakit', 'tone' => 'yellow'],
                    \App\Enums\AttendanceStatus::Permit => ['label' => 'Izin', 'tone' => 'blue'],
                    \App\Enums\AttendanceStatus::Absent => ['label' => 'Alpha', 'tone' => 'red'],
                    default => ['label' => 'Hadir', 'tone' => 'green'],
                  };
                @endphp
                <x-bk.status-badge :label="$meta['label']" :tone="$meta['tone']" :dot="true" />
              </td>
              <td class="text-xs text-bluedark/70">{{ $attendance->note ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">
                Belum ada rincian kehadiran per siswa pada jurnal ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
