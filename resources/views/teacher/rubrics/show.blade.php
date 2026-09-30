@extends('layouts.teacher')

@section('title', 'Detail Rubrik — ' . $rubric->name)

@section('content')
<div class="space-y-6">

  <!-- Flash Messages -->
  @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
  @endif

  @if(session('error'))
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700 font-bold">&times;</button>
    </div>
  @endif

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Detail Rubrik Nilai</h1>
      <p class="text-sm text-bluedark/60 mt-1">Pedoman Penskoran Asesmen Multi-Kriteria</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('teacher.rubrics.index') }}" class="btn btn-outline btn-sm">
        &larr; Kembali ke Daftar
      </a>
    </div>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.rubrics.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Rubrik Nilai</a> / 
    <span class="font-semibold text-bluedark">{{ $rubric->name }}</span>
  </p>

  <div class="form-block w-full">
    <div class="form-block__header flex items-center justify-between">
      <div>
        <h3 id="rubrikDetailTitle">{{ $rubric->name }}</h3>
        <p>{{ $rubric->description ?? 'Rincian bobot kriteria pada pedoman rubrik penskoran' }}</p>
      </div>
      @if($rubric->assessments->isNotEmpty())
        <span class="badge badge-yellow text-xs font-semibold px-2.5 py-1">
          Digunakan di {{ $rubric->assessments->count() }} Tugas
        </span>
      @endif
    </div>

    <div class="form-block__body">
      <div class="overflow-x-auto db-scroll mb-5 border border-bluelight rounded-xl">
        <table class="tbl w-full">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="w-1/3 min-w-[220px]">Kriteria Penilaian</th>
              <th class="min-w-[280px]">Deskripsi Indikator</th>
              <th style="width:11rem" class="text-center">Bobot Maksimal</th>
            </tr>
          </thead>
          <tbody id="rubrikKriteriaBody">
            @forelse($rubric->criteria as $idx => $crit)
              <tr>
                <td class="text-center font-semibold text-slate-500">{{ $idx + 1 }}</td>
                <td class="font-bold text-bluedark text-sm">{{ $crit->criterion }}</td>
                <td class="text-xs text-slate-600 leading-relaxed">{{ $crit->description ?? '-' }}</td>
                <td class="text-center font-mono font-bold text-sm text-bluedark">{{ $crit->max_points }} Poin</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center py-6 text-xs text-bluedark/50">
                  Belum ada kriteria yang dimasukkan pada rubrik ini.
                </td>
              </tr>
            @endforelse
            @if($rubric->criteria->isNotEmpty())
              <tr style="background:#0D47A1 !important;">
                <td colspan="3" class="font-bold px-4 py-3 text-sm !text-white" style="color:#ffffff !important;">Total Skor Maksimum</td>
                <td class="font-bold font-mono text-center text-sm px-4 py-3 !text-white" style="color:#ffffff !important;">{{ $rubric->criteria->sum('max_points') }} Poin</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
        <span class="text-xs text-bluedark/60">Digunakan pada {{ $rubric->assessments->count() }} penugasan</span>
        <div class="flex items-center gap-2">
          @if($rubric->assessments->isEmpty())
            <a href="{{ route('teacher.rubrics.edit', $rubric) }}" class="btn btn-outline btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              <span>Edit Rubrik Ini</span>
            </a>
          @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold select-none" title="Rubrik ini sudah digunakan pada tugas siswa sehingga tidak dapat diubah">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Terkunci (Sudah Digunakan)</span>
            </span>
          @endif
          <a href="{{ route('teacher.rubrics.index') }}" class="btn btn-outline btn-sm">
            &larr; Kembali ke Daftar Rubrik
          </a>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
