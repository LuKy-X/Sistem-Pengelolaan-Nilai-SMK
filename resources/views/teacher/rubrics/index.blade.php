@extends('layouts.teacher')

@section('title', 'Rubrik Penilaian — Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">Rubrik Penilaian</h1>
            <p class="text-sm text-bluedark/70 mt-1">Standarisasi rubrik kriteria untuk evaluasi asesmen dan praktikum kejuruan</p>
        </div>
        <div>
            <a href="{{ route('teacher.rubrics.create') }}" class="btn btn-primary btn-sm shadow-md">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Buat Rubrik Baru</span>
            </a>
        </div>
    </div>

    <!-- Rubrics Grid Panel -->
    <div class="panel p-5">
        <div class="mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[16px]">Daftar Rubrik Penilaian</h2>
            <p class="text-xs text-bluedark/50">Total {{ $rubrics->count() }} rubrik tersimpan</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($rubrics as $rubric)
                <div class="panel p-5 border border-[#E3F2FD] hover:border-blueprim hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="badge badge-blue">
                                {{ $rubric->criteria->count() }} Kriteria
                            </span>
                            <span class="text-xs font-mono font-bold text-blueprim">
                                Total: {{ $rubric->criteria->sum('max_points') }} Pts
                            </span>
                        </div>

                        <h3 class="font-heading font-bold text-bluedark text-base mb-1">
                            {{ $rubric->name }}
                        </h3>
                        <p class="text-xs text-bluedark/70 line-clamp-2 leading-relaxed mb-4">
                            {{ $rubric->description ?? 'Tidak ada deskripsi tambahan.' }}
                        </p>

                        <!-- Criteria Chips -->
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            @foreach($rubric->criteria->take(4) as $crit)
                                <span class="text-[11px] px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-slate-600 font-medium">
                                    {{ $crit->criterion }} ({{ $crit->max_points }})
                                </span>
                            @endforeach
                            @if($rubric->criteria->count() > 4)
                                <span class="text-[11px] px-2 py-1 text-slate-400">
                                    +{{ $rubric->criteria->count() - 4 }} lagi
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">
                            Digunakan {{ $rubric->assessments->count() }}x
                        </span>
                        <a href="{{ route('teacher.rubrics.show', $rubric) }}" class="btn btn-outline btn-sm py-1 px-3 text-xs">
                            Detail Kriteria &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-xs text-bluedark/50">
                    Belum ada rubrik penilaian yang dibuat. Klik "+ Buat Rubrik Baru" untuk membuat pedoman penskoran.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
