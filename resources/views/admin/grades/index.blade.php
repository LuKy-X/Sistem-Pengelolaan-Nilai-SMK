@extends('layouts.admin')

@section('title', 'Monitoring Buku Nilai')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Monitoring Buku Nilai</h1>
            <p class="text-sm text-bluedark/60 mt-1">Pantau perkembangan buku nilai digital, struktur kolom asesmen, dan rekap skor dari seluruh guru</p>
        </div>
    </div>

    <!-- Filter Kelas & Semester -->
    <div class="panel p-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.grades.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <select name="class_id" class="f-select text-xs py-1.5 w-48">
                <option value="">-- Semua Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <select name="semester_id" class="f-select text-xs py-1.5 w-56">
                <option value="">-- Semua Semester --</option>
                @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->academicYear?->name }})</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm text-xs py-1.5">Filter</button>
            @if($classId || $semesterId)
                <a href="{{ route('admin.grades.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
            @endif
        </form>

        <span class="text-xs text-bluedark/50">Total Buku Nilai: <strong>{{ $gradebooks->total() }}</strong></span>
    </div>

    <!-- Table Gradebooks -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Buku Nilai</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th>Semester</th>
                        <th>Kolom Nilai</th>
                        <th>Status</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gradebooks as $idx => $gb)
                        <tr>
                            <td>{{ $gradebooks->firstItem() + $idx }}</td>
                            <td>
                                <span class="font-semibold text-bluedark block">{{ $gb->name }}</span>
                                @if($gb->description)
                                    <span class="text-[11px] text-bluedark/50 block truncate max-w-xs">{{ $gb->description }}</span>
                                @endif
                            </td>
                            <td>{{ $gb->teachingAssignment?->schoolClass?->name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->subject?->name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->teacher?->full_name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->semester?->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-blue text-[11px]">{{ $gb->columns->count() }} Kolom</span>
                            </td>
                            <td>
                                @if($gb->is_active)
                                    <span class="badge badge-green text-[10px]">Aktif</span>
                                @else
                                    <span class="badge badge-gray text-[10px]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.grades.show', $gb) }}" class="btn btn-outline btn-sm text-xs py-1 px-2.5 inline-flex items-center gap-1.5 font-semibold text-bluedark/80 hover:text-blueprim hover:border-blueprim shadow-2xs">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Lihat Nilai</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada buku nilai yang dibuat guru pada kriteria yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $gradebooks->links() }}
        </div>
    </div>

</div>
@endsection
