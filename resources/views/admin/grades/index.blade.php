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

    <!-- KPI Metric Cards Row (Sesuai Style Dashboard Admin) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-bluelight text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Buku Nilai</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['active'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Buku Nilai Aktif</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-amber-100 text-amber-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['columns'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Kolom Asesmen</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-blueprim/10 text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['teachers'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Guru Pengampu Aktif</div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Toolbar -->
    <div class="panel p-4 space-y-3">
        <form method="GET" action="{{ route('admin.grades.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-bluedark/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari buku nilai, mapel, guru, kelas..." class="f-input text-xs py-2 pl-9 pr-3 w-full">
            </div>

            <div class="w-full sm:w-auto min-w-[140px]">
                <select name="class_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[170px]">
                <select name="semester_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Semua Semester --</option>
                    @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->academicYear?->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[150px]">
                <select name="subject_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Mata Pelajaran --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ ($subjectId ?? '') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[150px]">
                <select name="teacher_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Guru Pengampu --</option>
                    @foreach($teachers as $tch)
                        <option value="{{ $tch->id }}" {{ ($teacherId ?? '') == $tch->id ? 'selected' : '' }}>{{ $tch->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[120px]">
                <select name="status" class="f-select text-xs py-2 w-full">
                    <option value="">-- Status --</option>
                    <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-2 px-4 flex items-center gap-1.5 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Terapkan</span>
                </button>

                @if($search || $classId || $semesterId || ($subjectId ?? '') || ($teacherId ?? '') || ($status !== null && $status !== ''))
                    <a href="{{ route('admin.grades.index') }}" class="btn btn-outline btn-sm text-xs py-2 px-3 text-rose-600 border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between text-xs text-bluedark/50 pt-2 border-t border-slate-100">
            <div>
                Menampilkan <strong>{{ $gradebooks->count() }}</strong> dari <strong>{{ $gradebooks->total() }}</strong> buku nilai
            </div>
            @if($search || $classId || $semesterId || ($subjectId ?? '') || ($teacherId ?? '') || ($status !== null && $status !== ''))
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blueprim">
                    <span class="w-2 h-2 rounded-full bg-blueprim animate-pulse"></span>
                    Filter aktif
                </span>
            @endif
        </div>
    </div>

    <!-- Table Gradebooks -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Buku Nilai</th>
                        <th>Kelas &amp; Jurusan</th>
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
                        @php
                            $assignment = $gb->teachingAssignment;
                            $schoolClass = $assignment?->schoolClass;
                            $department = $schoolClass?->department;
                            $subject = $assignment?->subject;
                            $teacher = $assignment?->teacher;
                            $semester = $assignment?->semester;
                        @endphp
                        <tr>
                            <td>{{ $gradebooks->firstItem() + $idx }}</td>
                            <td>
                                <span class="font-semibold text-bluedark block">{{ $gb->name }}</span>
                                @if($gb->description)
                                    <span class="text-[11px] text-bluedark/50 block truncate max-w-xs">{{ $gb->description }}</span>
                                @endif
                            </td>
                            <td>
                                @if($schoolClass)
                                    <div>
                                        <span class="font-semibold text-bluedark block text-xs">{{ $schoolClass->name }}</span>
                                        @if($department)
                                            <span class="badge badge-blue text-[9px] py-0 px-1.5 mt-0.5">{{ $department->short_name ?: $department->code }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-bluedark/40">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-semibold text-bluedark block text-xs">{{ $subject?->name ?? '-' }}</span>
                                @if($subject?->code)
                                    <span class="font-mono text-[10px] text-bluedark/50">{{ $subject->code }}</span>
                                @endif
                            </td>
                            <td>
                                @if($teacher)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blueprim/10 text-blueprim font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-medium text-bluedark">{{ $teacher->full_name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-bluedark/40">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs font-medium text-bluedark block">{{ $semester?->name ?? '-' }}</span>
                                @if($semester?->academicYear)
                                    <span class="text-[10px] text-bluedark/50 block">{{ $semester->academicYear->name }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-blue text-[11px] font-semibold">{{ $gb->columns->count() }} Kolom</span>
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
                            <td colspan="9" class="text-center py-12">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-bluedark/40 flex items-center justify-center">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-bluedark">Buku Nilai Tidak Ditemukan</p>
                                    <p class="text-xs text-bluedark/50">Belum ada buku nilai yang cocok dengan kriteria pencarian atau filter yang dipilih.</p>
                                    @if($search || $classId || $semesterId || ($subjectId ?? '') || ($teacherId ?? '') || ($status !== null && $status !== ''))
                                        <div class="pt-2">
                                            <a href="{{ route('admin.grades.index') }}" class="btn btn-outline btn-sm text-xs py-1 px-3">
                                                Reset Filter
                                            </a>
                                        </div>
                                    @endif
                                </div>
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
