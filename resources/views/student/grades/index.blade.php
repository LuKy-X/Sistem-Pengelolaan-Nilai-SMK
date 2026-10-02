@extends('layouts.student')

@section('title', 'Nilai Saya')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Nilai Saya</h1>
      <p class="text-sm text-bluedark/60 mt-1">Rekap nilai dari buku nilai digital yang dikelola guru Anda.</p>
    </div>
    @if($memberships->isNotEmpty())
      <a href="{{ route('student.grades.recap') }}" class="btn btn-outline btn-sm shrink-0">Rekap Nilai</a>
    @endif
  </div>

  @if($memberships->isEmpty())
    <div class="panel p-6 text-center">
      <p class="text-sm text-bluedark/60">Anda belum terdaftar pada buku nilai manapun. Nilai akan muncul setelah guru menambahkan Anda ke buku nilai kelas.</p>
    </div>
  @else
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($memberships as $gradebook)
        @php
          $summary = $summaries[$gradebook->id] ?? ['average' => null, 'graded' => 0, 'total' => 0];
          $assignment = $gradebook->teachingAssignment;
        @endphp
        <a href="{{ route('student.grades.show', $gradebook) }}" class="crud-card card-hover block">
          <div class="crud-card__head">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="crud-card__icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
              </div>
              <div class="min-w-0">
                <div class="crud-card__title truncate">{{ $assignment?->subject?->name ?? 'Mata Pelajaran' }}</div>
                <div class="crud-card__sub truncate">{{ $gradebook->name }} &middot; {{ $assignment?->schoolClass?->name }}</div>
              </div>
            </div>
          </div>

          <div class="flex items-end justify-between mt-3">
            <div>
              <div class="font-heading text-2xl font-bold {{ $summary['average'] !== null ? ($summary['average'] >= 75 ? 'text-emerald-600' : 'text-amber-600') : 'text-bluedark/30' }}">
                {{ $summary['average'] !== null ? rtrim(rtrim(number_format($summary['average'], 2), '0'), '.') : '-' }}
              </div>
              <div class="text-[10px] text-bluedark/50">Rata-rata nilai</div>
            </div>
            <div class="text-right">
              <div class="text-xs font-semibold text-bluedark">{{ $summary['graded'] }}/{{ $summary['total'] }}</div>
              <div class="text-[10px] text-bluedark/50">Kolom terisi</div>
            </div>
          </div>

          <div class="crud-card__foot mt-3">
            <span class="text-[11px] text-bluedark/50 truncate">{{ $assignment?->teacher?->full_name ?? '-' }}</span>
            <span class="ml-auto text-[11px] font-semibold text-blueprim">Detail &rarr;</span>
          </div>
        </a>
      @endforeach
    </div>
  @endif

</div>
@endsection
