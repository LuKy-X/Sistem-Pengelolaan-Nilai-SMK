@extends('layouts.admin')

@section('title', 'Detail Nilai — ' . $gradebook->title)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.grades.index') }}" class="text-xs text-blueprim hover:underline">&larr; Kembali ke Monitoring Nilai</a>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $gradebook->title }}</h1>
            <p class="text-sm text-bluedark/60">
                Kelas: <strong class="text-bluedark">{{ $gradebook->teachingAssignment?->schoolClass?->name }}</strong> &middot;
                Mapel: <strong class="text-bluedark">{{ $gradebook->teachingAssignment?->subject?->name }}</strong> &middot;
                Guru: <strong class="text-bluedark">{{ $gradebook->teachingAssignment?->teacher?->full_name }}</strong>
            </p>
        </div>
    </div>

    <!-- Spreadsheet Nilai Table (Read-Only) -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Rekap Lembar Nilai Siswa</h2>
            <span class="text-xs text-bluedark/50">{{ $students->count() }} Siswa Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-24">NIS</th>
                        <th>Nama Siswa</th>
                        @foreach($columns as $col)
                            <th class="text-center font-heading text-xs">
                                <div>{{ $col->name }}</div>
                                <span class="badge {{ $col->type === 'SUMMARY' ? 'badge-yellow' : 'badge-blue' }} text-[9px] mt-0.5">
                                    {{ $col->type }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $st)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="font-mono text-xs">{{ $st->student?->nis }}</td>
                            <td class="font-semibold text-bluedark">{{ $st->student?->full_name }}</td>
                            @foreach($columns as $col)
                                @php
                                    $scoreKey = $st->student_id . '_' . $col->id;
                                    $scoreObj = $scores->get($scoreKey)?->first();
                                @endphp
                                <td class="text-center font-mono font-medium {{ $col->type === 'SUMMARY' ? 'bg-amber-50/40 text-amber-900 font-bold' : '' }}">
                                    {{ $scoreObj?->score !== null ? number_format($scoreObj->score, 1) : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $columns->count() }}" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada siswa yang dimasukkan ke buku nilai ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
