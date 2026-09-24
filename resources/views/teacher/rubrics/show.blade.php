@extends('layouts.teacher')

@section('title', 'Detail Rubrik — ' . $rubric->name)

@section('content')
<div class="space-y-6">

    <!-- Stepper Navigation -->
    <div class="flex items-center gap-2 text-xs md:text-sm text-bluedark/60">
        <a href="{{ route('teacher.rubrics.index') }}" class="font-semibold text-blueprim hover:underline">
            Rubrik Penilaian
        </a>
        <span>/</span>
        <span class="font-semibold text-bluedark">
            {{ $rubric->name }}
        </span>
    </div>

    <!-- Header Panel -->
    <div class="panel p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E3F2FD]">
            <div>
                <span class="badge badge-blue mb-1">
                    {{ $rubric->criteria->count() }} Kriteria • Total {{ $rubric->criteria->sum('max_points') }} Poin
                </span>
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">
                    {{ $rubric->name }}
                </h1>
                <p class="text-xs text-bluedark/60 mt-0.5">
                    {{ $rubric->description ?? 'Pedoman penskoran terstandarisasi untuk asesmen.' }}
                </p>
            </div>
            <div>
                <span class="text-xs text-slate-500">Status:</span>
                <span class="badge badge-green ml-1">Tersedia untuk Asesmen</span>
            </div>
        </div>

        <!-- Criteria Table -->
        <div class="mt-5 overflow-x-auto rounded-xl border border-[#E3F2FD]">
            <table class="tbl text-xs">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th class="w-1/3">Kriteria Penilaian</th>
                        <th>Deskriptor / Standar Capaian</th>
                        <th class="w-28 text-center">Poin Maks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3F2FD]">
                    @foreach($rubric->criteria as $index => $c)
                        <tr class="hover:bg-[#F5FAFF]">
                            <td class="text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                            <td class="font-bold text-bluedark">{{ $c->criterion }}</td>
                            <td class="text-ink/80 leading-relaxed">{{ $c->description ?? '-' }}</td>
                            <td class="text-center font-mono font-bold text-blueprim text-sm">
                                {{ number_format($c->max_points, 0) }} Pts
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-slate-50 text-bluedark border-t-2 border-[#E3F2FD]">
                        <td colspan="3" class="text-right px-4 py-2 uppercase text-[11px] tracking-wider">
                            Total Skor Maksimum:
                        </td>
                        <td class="text-center font-mono text-sm text-blueprim py-2">
                            {{ number_format($rubric->criteria->sum('max_points'), 0) }} Pts
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
