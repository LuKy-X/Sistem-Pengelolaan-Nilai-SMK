@extends('layouts.bk')

@section('title', 'Lihat Absensi — Guru BK')

@section('content')
<div class="space-y-5">

  @include('partials.journals.bk-tabs', ['tabQuery' => ['class_id' => $selectedClass?->id, 'date' => $selectedDate]])

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Lihat Absensi Kelas</h1>
    <p class="text-sm text-bluedark/60 mt-1">
      Read-only: Anda dapat melihat absensi kelas yang Anda bina maupun yang Anda ajar,
      termasuk pada hari ketika tidak ada jadwal mata pelajaran Anda.
    </p>
  </div>

  @if($visibleClasses->isEmpty())
    <div class="panel p-6 text-center">
      <p class="text-sm text-bluedark/60">Belum ada kelas binaaan maupun penugasan mengajar yang tercatat untuk Anda.</p>
    </div>
  @else
    <div class="panel p-5">
      <form method="GET" action="{{ route('counselor.journals.attendance') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div class="flex-1 min-w-[200px]">
          <label class="f-label" for="class_id">Kelas</label>
          <select id="class_id" name="class_id" onchange="this.form.submit()" class="f-select">
            <option value="">Semua Kelas ({{ $visibleClasses->count() }})</option>
            @foreach($visibleClasses as $schoolClass)
              <option value="{{ $schoolClass->id }}" @selected($selectedClass?->id === $schoolClass->id)>
                {{ $schoolClass->name }} &mdash; {{ $schoolClass->department?->name ?? 'Jurusan' }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="w-full sm:w-44">
          <label class="f-label" for="date">Tanggal</label>
          <input type="date" id="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="f-input">
        </div>
        <button type="submit" class="btn btn-primary">Terapkan</button>
      </form>

      <div class="flex flex-wrap items-center gap-1.5 mt-3 pt-3 border-t border-slate-100 text-[11px] text-bluedark/60">
        <span class="font-semibold">Navigasi cepat:</span>
        <a href="{{ route('counselor.journals.attendance', ['class_id' => $selectedClass?->id, 'date' => $weekStartDate]) }}"
           class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 font-semibold">
          Awal Minggu
        </a>
        <a href="{{ route('counselor.journals.attendance', ['class_id' => $selectedClass?->id, 'date' => $yesterday]) }}"
           class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 font-semibold">
          Kemarin
        </a>
        <a href="{{ route('counselor.journals.attendance', ['class_id' => $selectedClass?->id, 'date' => $today]) }}"
           class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 font-semibold">
          Hari Ini
        </a>
        <a href="{{ route('counselor.journals.attendance', ['class_id' => $selectedClass?->id, 'date' => $prevDate]) }}"
           class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 font-semibold">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          {{ \Carbon\Carbon::parse($prevDate)->locale('id')->isoFormat('dddd, D MMM') }}
        </a>
        <span class="px-2 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 font-bold">
          {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y') }}
        </span>
        <a href="{{ route('counselor.journals.attendance', ['class_id' => $selectedClass?->id, 'date' => $nextDate]) }}"
           class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 font-semibold">
          {{ \Carbon\Carbon::parse($nextDate)->locale('id')->isoFormat('dddd, D MMM') }}
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="panel p-4 text-center">
          <div class="font-heading text-2xl font-bold text-emerald-700">{{ $totals['hadir'] }}</div>
          <div class="text-[11px] text-bluedark/55 mt-0.5">Total Hadir</div>
        </div>
        <div class="panel p-4 text-center">
          <div class="font-heading text-2xl font-bold text-amber-700">{{ $totals['sakit'] }}</div>
          <div class="text-[11px] text-bluedark/55 mt-0.5">Total Sakit</div>
        </div>
        <div class="panel p-4 text-center">
          <div class="font-heading text-2xl font-bold text-blue-700">{{ $totals['izin'] }}</div>
          <div class="text-[11px] text-bluedark/55 mt-0.5">Total Izin</div>
        </div>
        <div class="panel p-4 text-center">
          <div class="font-heading text-2xl font-bold text-red-700">{{ $totals['alpha'] }}</div>
          <div class="text-[11px] text-bluedark/55 mt-0.5">Total Alpha</div>
        </div>
      </div>

      <div class="panel p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">
              {{ $selectedClass?->name ?? 'Semua Kelas' }} &middot; {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </h2>
            <p class="text-xs text-bluedark/50">
              {{ $journals->count() }} sesi terisi pada tanggal ini, lintas mata pelajaran dan pengajar
              @unless($selectedClass)
                di {{ $visibleClasses->count() }} kelasZu amphibODS dan yang Anda ajar
              @endunless
              .
            </p>
          </div>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border border-slate-200 bg-slate-50 text-slate-600">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Mode lihat saja
          </span>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th>Kelas</th>
                <th>Jam ke-</th>
                <th>Mata Pelajaran</th>
                <th>Pengajar</th>
                <th>Materi</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">S</th>
                <th class="text-center">I</th>
                <th class="text-center">A</th>
                <th>Siswa Tidak Hadir</th>
                <th class="text-right">Rincian</th>
              </tr>
            </thead>
            <tbody>
              @forelse($journals as $journal)
                <tr>
                  <td class="text-xs font-semibold text-bluedark">
                    {{ $journal->teachingAssignment?->schoolClass?->name ?? '—' }}
                  </td>
                  <td class="text-xs text-center whitespace-nowrap font-mono">
                    {{ $journal->startPeriod?->period_number ?? '—' }} sd {{ $journal->endPeriod?->period_number ?? '—' }}
                  </td>
                  <td class="text-xs font-semibold text-bluedark">{{ $journal->teachingAssignment?->subject?->name ?? '—' }}</td>
                  <td class="text-xs">{{ $journal->creator?->user?->name ?? $journal->creator?->full_name ?? 'Guru' }}</td>
                  <td class="text-xs max-w-[220px]">
                    <span class="block truncate" title="{{ $journal->material }}">{{ $journal->material }}</span>
                  </td>
                  <td class="text-center">
                    <span class="inline-flex items-center justify-center min-w-[26px] h-6 px-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold font-mono text-xs border border-emerald-200/80">
                      {{ $journal->hadir_count }}
                    </span>
                  </td>
                  <td class="text-center text-xs font-mono {{ $journal->sakit_count > 0 ? 'text-amber-700 font-bold' : 'text-bluedark/30' }}">{{ $journal->sakit_count }}</td>
                  <td class="text-center text-xs font-mono {{ $journal->izin_count > 0 ? 'text-blue-700 font-bold' : 'text-bluedark/30' }}">{{ $journal->izin_count }}</td>
                  <td class="text-center text-xs font-mono {{ $journal->alpha_count > 0 ? 'text-red-700 font-bold' : 'text-bluedark/30' }}">{{ $journal->alpha_count }}</td>
                  <td class="text-xs">
                    @php
                      $absentStudents = $journal->attendances->filter(fn ($attendance) => $attendance->status !== \App\Enums\AttendanceStatus::Present);
                    @endphp
                    @if($absentStudents->isEmpty())
                      <span class="text-bluedark/40 italic">Semua hadir</span>
                    @else
                      <div class="flex flex-wrap gap-1">
                        @foreach($absentStudents as $attendance)
                          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border border-slate-200 bg-slate-50 text-[11px]">
                            <span class="font-semibold text-bluedark">{{ $attendance->student?->full_name ?? 'Siswa' }}</span>
                            <x-bk.status-badge
                              :label="match ($attendance->status) {
                                \App\Enums\AttendanceStatus::Sick => 'Sakit',
                                \App\Enums\AttendanceStatus::Permit => 'Izin',
                                \App\Enums\AttendanceStatus::Absent => 'Alpha',
                                default => 'Hadir',
                              }"
                              :tone="match ($attendance->status) {
                                \App\Enums\AttendanceStatus::Sick => 'yellow',
                                \App\Enums\AttendanceStatus::Permit => 'blue',
                                \App\Enums\AttendanceStatus::Absent => 'red',
                                default => 'green',
                              }" />
                          </span>
                        @endforeach
                      </div>
                    @endif
                  </td>
                  <td class="text-right">
                    <a href="{{ route('counselor.journals.show', $journal) }}" class="btn btn-outline btn-sm" data-no-transition="true">Buka</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center py-10 text-xs text-bluedark/50">
                    Belum ada absensi yang terisi pada tanggal ini
                    @if($selectedClass)
                      untuk kelas {{ $selectedClass->name }}
                    @else
                      pada kelas yang Anda bina maupun yang Anda ajar
                    @endif
                    .
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
  @endif

</div>
@endsection