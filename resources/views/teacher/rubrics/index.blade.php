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
          <span>Rubrik Baru</span>
        </a>
      </div>

      <div class="flex flex-col gap-3" id="rubrikList">
        @forelse($rubrics as $rubric)
          @php
            $isUsed = $rubric->assessments->isNotEmpty();
          @endphp
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-bluelight p-4 hover:border-blueprim transition-colors">
            <div class="flex items-center gap-3 min-w-0">
              <div class="crud-card__icon shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-sm text-bluedark truncate">{{ $rubric->name }}</span>
                  @if($isUsed)
                    <span class="badge badge-yellow text-[10px] py-0 px-2 font-medium" title="Rubrik ini sudah digunakan pada tugas siswa sehingga terkunci dari perubahan">
                      Terkunci
                    </span>
                  @endif
                </div>
                <div class="text-[11px] text-bluedark/50 mt-0.5">
                  {{ $rubric->criteria->count() }} Kriteria &middot; {{ Str::limit($rubric->description ?? 'Rubrik penilaian', 70) }}
                  @if($isUsed)
                    &middot; <strong class="text-blueprim">{{ $rubric->assessments->count() }} Tugas Menggunakan</strong>
                  @endif
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
              <span class="badge badge-blue font-mono font-bold">
                Total: {{ $rubric->criteria->sum('max_points') }} Pts
              </span>
              @if(!$isUsed)
                <a href="{{ route('teacher.rubrics.edit', $rubric) }}" class="btn btn-outline btn-sm py-1 px-2.5 text-xs text-slate-700 hover:text-blueprim hover:border-blueprim" title="Edit Rubrik">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Edit</span>
                </a>
              @endif
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
