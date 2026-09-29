@extends('layouts.teacher')

@section('title', 'Manajemen Rubrik Nilai — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Rubrik Nilai</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Rubrik Penilaian Multi-Kriteria</p>
  </div>

  <div class="step-panel active" data-panel="list">
    <div class="panel p-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Rubrik Nilai</h2>
          <p class="text-xs text-bluedark/50">{{ $rubrics->count() }} rubrik tersimpan pada akun Anda</p>
        </div>
        <a href="{{ route('teacher.rubrics.create') }}" class="btn btn-primary btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
          Rubrik Baru
        </a>
      </div>

      <div class="flex flex-col gap-3" id="rubrikList">
        @forelse($rubrics as $rubric)
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-bluelight p-4 hover:border-blueprim transition-colors">
            <div class="flex items-center gap-3 min-w-0">
              <div class="crud-card__icon shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
              </div>
              <div class="min-w-0">
                <div class="font-semibold text-sm text-bluedark truncate">{{ $rubric->name }}</div>
                <div class="text-[11px] text-bluedark/50">
                  {{ $rubric->criteria->count() }} Kriteria &middot; {{ Str::limit($rubric->description ?? 'Rubrik penilaian', 70) }}
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
              <span class="badge badge-blue font-mono font-bold">
                Total: {{ $rubric->criteria->sum('max_points') }} Pts
              </span>
              <a href="{{ route('teacher.rubrics.show', $rubric) }}" class="btn btn-outline btn-sm py-1 px-3 text-xs">
                Detail Kriteria &rarr;
              </a>
            </div>
          </div>
        @empty
          <div class="py-12 text-center text-xs text-bluedark/50">
            Belum ada rubrik penilaian yang dibuat. Klik "+ Rubrik Baru" untuk membuat rubrik.
          </div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
