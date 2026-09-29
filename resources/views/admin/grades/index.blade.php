@extends('layouts.admin')

@section('title', 'Monitoring Buku Nilai')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Monitoring Buku Nilai</h1>
            <p class="text-sm text-bluedark/60 mt-1">Pantau perkembangan buku nilai, asesmen, dan rekap skor dari seluruh guru</p>
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
                        <th>Judul Buku Nilai</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th>Semester</th>
                        <th>Jumlah Kolom Nilai</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gradebooks as $idx => $gb)
                        <tr>
                            <td>{{ $gradebooks->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">{{ $gb->title }}</td>
                            <td>{{ $gb->teachingAssignment?->schoolClass?->name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->subject?->name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->teacher?->full_name ?? '-' }}</td>
                            <td>{{ $gb->teachingAssignment?->semester?->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-blue text-[11px]">{{ $gb->columns->count() }} Kolom</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.grades.show', $gb) }}" class="btn btn-outline btn-sm text-xs py-1 px-3">
                                    Lihat Nilai
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
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
