@extends('layouts.bk')

@section('title', 'Riwayat Jurnal Kelas — Guru BK')

@section('content')
<div class="space-y-5">

  @include('partials.journals.bk-tabs', ['tabQuery' => ['class_id' => $selectedClass?->id]])

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.journals.attendance') }}" class="font-semibold text-blueprim hover:underline" data-no-transition="true">Lihat Absensi</a>
    /
    <span class="font-semibold text-bluedark">Riwayat Semua Jurnal</span>
  </p>

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Riwayat Jurnal Kelas</h1>
    <p class="text-sm text-bluedark/60 mt-1">
      Seluruh jurnal &amp; absensi kelas yang Anda ampu, lintas mata pelajaran dan pengajar.
      @if($selectedClass)
        Diffilter ke kelas {{ $selectedClass->name }}.
      @endif
    </p>
  </div>

  <div class="grid grid-cols-3 gap-4">
    <div class="kpi-card">
      <div class="kpi-icon bg-amber-50 text-amber-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totals['sakit'] }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Sakit (halaman ini)</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-blue-50 text-blueprim">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totals['izin'] }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Izin (halaman ini)</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-red-50 text-red-600">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totals['alpha'] }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Alpha (halaman ini)</div>
      </div>
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.journals.history') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[220px]">
        <label class="f-label" for="q">Cari Jurnal</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Materi, mata pelajaran, atau nama siswa" class="f-input">
      </div>
      <div class="w-full sm:w-48">
        <label class="f-label" for="class_id">Kelas</label>
        <select id="class_id" name="class_id" class="f-select">
          <option value="">Semua Kelas Binaan</option>
          @foreach($counselorClasses as $schoolClass)
            <option value="{{ $schoolClass->id }}" @selected($selectedClass?->id === $schoolClass->id)>
              {{ $schoolClass->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="w-full sm:w-40">
        <label class="f-label" for="date_from">Dari Tanggal</label>
        <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="f-input">
      </div>
      <div class="w-full sm:w-40">
        <label class="f-label" for="date_to">Sampai Tanggal</label>
        <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="f-input">
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $selectedClass || $dateFrom || $dateTo)
        <a href="{{ route('counselor.journals.history') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Kelas</th>
            <th>Jam ke-</th>
            <th>Mata Pelajaran</th>
            <th>Pengajar</th>
            <th>Materi</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">S</th>
            <th class="text-center">I</th>
            <th class="text-center">A</th>
            <th class="text-right">Rincian</th>
          </tr>
        </thead>
        <tbody>
          @forelse($journals as $journal)
            @php
              $journalDate = $journal->journal_date;
            @endphp
            <tr>
              <td class="text-xs whitespace-nowrap">
                <div class="font-medium text-bluedark">{{ $journalDate?->locale('id')->isoFormat('dddd') }}</div>
                <div class="text-[11px] text-bluedark/45 font-mono">{{ $journalDate?->format('d/m/Y') }}</div>
              </td>
              <td class="text-xs font-semibold text-bluedark">
                {{ $journal->teachingAssignment?->schoolClass?->name ?? '—' }}
              </td>
              <td class="text-xs text-center whitespace-nowrap font-mono">
                {{ $journal->startPeriod?->period_number ?? '—' }} sd {{ $journal->endPeriod?->period_number ?? '—' }}
              </td>
              <td class="text-xs">{{ $journal->teachingAssignment?->subject?->name ?? '—' }}</td>
              <td class="text-xs">
                {{ $journal->creator?->user?->name ?? $journal->creator?->full_name ?? 'Guru' }}
              </td>
              <td class="text-xs max-w-[260px]">
                <span class="block truncate" title="{{ $journal->material }}">{{ $journal->material }}</span>
              </td>
              <td class="text-center">
                <span class="inline-flex items-center justify-center min-w-[26px] h-6 px-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold font-mono text-xs border border-emerald-200/80">
                  {{ $journal->hadir_count }}
                </span>
              </td>
              <td class="text-center text-xs font-mono {{ $journal->sakit_count > 0 ? 'text-amber-700 font-bold' : 'text-bluedark/30' }}">
                {{ $journal->sakit_count }}
              </td>
              <td class="text-center text-xs font-mono {{ $journal->izin_count > 0 ? 'text-blue-700 font-bold' : 'text-bluedark/30' }}">
                {{ $journal->izin_count }}
              </td>
              <td class="text-center text-xs font-mono {{ $journal->alpha_count > 0 ? 'text-red-700 font-bold' : 'text-bluedark/30' }}">
                {{ $journal->alpha_count }}
              </td>
              <td class="text-right">
                <a href="{{ route('counselor.journals.show', $journal) }}" class="btn btn-outline btn-sm" data-no-transition="true">Buka</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center py-8 text-xs text-bluedark/50">
                Belum ada jurnal kelas yang cocok dengan filter.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$journals" />
  </div>

</div>
@endsection
