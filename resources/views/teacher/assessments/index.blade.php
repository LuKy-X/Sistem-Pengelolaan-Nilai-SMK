@extends('layouts.teacher')

@section('title', 'Daftar Tugas & Ulangan')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">Manajemen Tugas &amp; Asesmen</h1>
            <p class="text-sm text-bluedark/70 mt-1">Kelola tugas mandiri, ulangan harian, dan evaluasi hasil belajar siswa</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.assessments.create') }}" class="btn btn-primary btn-sm shadow-md">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Buat Tugas Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter by Teaching Assignment / Class -->
    <div class="panel p-4 flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-bluedark">Filter Kelas:</span>
        <a href="{{ route('teacher.assessments.index') }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {{ empty(request('assignment_id')) ? 'bg-bluedark text-white shadow-xs' : 'bg-bluelight/60 text-bluedark hover:bg-bluelight' }}">
            Semua Kelas
        </a>
        @foreach($assignments as $assign)
            <a href="{{ route('teacher.assessments.index', ['assignment_id' => $assign->id]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('assignment_id') == $assign->id ? 'bg-bluedark text-white shadow-xs' : 'bg-bluelight/60 text-bluedark hover:bg-bluelight' }}">
                {{ $assign->schoolClass?->name }} • {{ $assign->subject?->code }}
            </a>
        @endforeach
    </div>

    <!-- Assessments Table Panel -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-[16px]">Daftar Tugas &amp; Evaluasi</h2>
                <p class="text-xs text-bluedark/50">Total {{ $assessments->count() }} asesmen tercatat</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E3F2FD]">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Judul Tugas / Ulangan</th>
                        <th>Kelas &amp; Mapel</th>
                        <th>Kolom Buku Nilai</th>
                        <th>Tenggat Waktu</th>
                        <th class="text-center">Pengumpulan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3F2FD]">
                    @forelse($assessments as $index => $item)
                        @php
                            $submittedCount = $item->submissions->where('status', 'SUBMITTED')->count();
                            $gradedCount = $item->submissions->where('status', 'GRADED')->count();
                            $totalSubmissions = $item->submissions->count();
                        @endphp
                        <tr class="hover:bg-[#F5FAFF] transition-colors">
                            <td class="text-center text-xs font-semibold text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <a href="{{ route('teacher.assessments.show', $item) }}" class="font-bold text-bluedark hover:text-blueprim text-sm block">
                                    {{ $item->title }}
                                </a>
                                <span class="text-[11px] text-slate-500">
                                    Tipe: {{ $item->type->name }}
                                </span>
                            </td>
                            <td>
                                <div class="font-semibold text-xs text-bluedark">
                                    {{ $item->teachingAssignment?->schoolClass?->name }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $item->teachingAssignment?->subject?->name }}
                                </div>
                            </td>
                            <td>
                                @if($item->gradebookColumn)
                                    <span class="badge badge-blue">
                                        {{ $item->gradebookColumn->code }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Mandiri</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $item->due_at ? $item->due_at->translatedFormat('d M Y, H:i') : 'Tidak ditentukan' }}
                            </td>
                            <td class="text-center text-xs">
                                <span class="font-bold text-bluedark">{{ $totalSubmissions }}</span>
                                <span class="text-[11px] text-slate-400 block">
                                    ({{ $gradedCount }} dinilai / {{ $submittedCount }} baru)
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->status->value === 'PUBLISHED')
                                    <span class="badge badge-green">Aktif</span>
                                @elseif($item->status->value === 'CLOSED')
                                    <span class="badge badge-gray">Selesai</span>
                                @else
                                    <span class="badge badge-yellow">Draf</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('teacher.assessments.show', $item) }}" class="btn btn-primary btn-sm py-1 px-3 text-xs shadow-xs">
                                    Periksa &amp; Nilai
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-bluedark/50">
                                Belum ada data tugas untuk kelas yang dipilih. Klik tombol "+ Buat Tugas Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
