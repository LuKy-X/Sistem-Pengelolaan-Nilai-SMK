@extends('layouts.teacher')

@section('title', 'Detail Rubrik — ' . $rubric->name)

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Detail Rubrik Nilai</h1>
    <p class="text-sm text-bluedark/60 mt-1">Pedoman Penskoran Asesmen Multi-Kriteria</p>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.rubrics.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Rubrik Nilai</a> / 
    <span class="font-semibold text-bluedark">{{ $rubric->name }}</span>
  </p>

  <div class="form-block">
    <div class="form-block__header">
      <h3 id="rubrikDetailTitle">{{ $rubric->name }}</h3>
      <p>{{ $rubric->description ?? 'Rincian bobot kriteria pada pedoman rubrik penskoran' }}</p>
    </div>

    <div class="form-block__body">
      <div class="overflow-x-auto db-scroll mb-5 border border-bluelight rounded-xl">
        <table class="tbl">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th>Kriteria Penilaian</th>
              <th>Deskripsi Indikator</th>
              <th style="width:10rem" class="text-center">Bobot Maksimal</th>
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
              <tr style="background:#0D47A1;color:#fff;">
                <td colspan="3" class="font-semibold px-4 py-3 text-sm">Total Skor Maksimum</td>
                <td class="font-semibold font-mono text-center text-sm px-4 py-3">{{ $rubric->criteria->sum('max_points') }} Poin</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center pt-2">
        <span class="text-xs text-bluedark/60">Digunakan pada {{ $rubric->assessments->count() }} penugasan</span>
        <a href="{{ route('teacher.rubrics.index') }}" class="btn btn-outline">
          &larr; Kembali ke Daftar Rubrik
        </a>
      </div>
    </div>
  </div>

</div>
@endsection
