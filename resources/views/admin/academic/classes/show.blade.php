@extends('layouts.admin')

@section('title', 'Detail Kelas — ' . $class->name)

@push('styles')
<style>
*::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
* {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.academic.classes.index') }}" class="text-xs text-blueprim hover:underline">&larr; Kembali ke Daftar Kelas</a>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $class->name }}</h1>
            <p class="text-sm text-bluedark/60">
                Jurusan: <strong class="text-bluedark">{{ $class->department?->name }}</strong> &middot;
                Tingkat: <span class="badge badge-blue">{{ $class->gradeLevel?->name }}</span> &middot;
                Wali Kelas: <strong class="text-bluedark">{{ $class->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.academic.classes.index') }}?promote_id={{ $class->id }}" class="btn btn-outline btn-sm flex items-center gap-1.5 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all shadow-2xs font-semibold" title="Naikkan seluruh siswa rombel ini ke tingkat berikutnya">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="18 15 12 9 6 15"/>
                    <polyline points="18 9 12 3 6 9"/>
                </svg>
                <span>Kenaikan Kelas</span>
            </a>
            <a href="{{ route('admin.academic.schedules.index', ['class_id' => $class->id]) }}" class="btn btn-outline btn-sm">
                Lihat Jadwal Pelajaran
            </a>
            <a href="{{ route('admin.academic.teaching-assignments.index') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Penugasan Guru</span>
            </a>
        </div>
    </div>

    <!-- Grid Siswa & Mata Pelajaran Terdaftar -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Kolom Siswa Terdaftar (2 Kolom) -->
        <div class="panel p-5 lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Siswa Terdaftar ({{ $class->enrollments->count() }})</h2>
                    <p class="text-xs text-bluedark/50">Anggota rombongan belajar resmi pada tahun ajaran aktif</p>
                </div>
                <a href="{{ route('admin.academic.students.index', ['class_id' => $class->id]) }}" class="text-xs text-blueprim hover:underline">
                    Kelola Siswa &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl w-full text-left">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Lengkap</th>
                            <th>L/P</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($class->enrollments as $idx => $enrollment)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="font-mono text-xs">{{ $enrollment->student?->nis }} / {{ $enrollment->student?->nisn }}</td>
                                <td class="font-semibold text-bluedark">
                                    <a href="{{ route('admin.academic.students.show', $enrollment->student_id) }}" class="hover:underline">
                                        {{ $enrollment->student?->full_name }}
                                    </a>
                                </td>
                                <td>{{ $enrollment->student?->gender === 'MALE' ? 'L' : 'P' }}</td>
                                <td>
                                    <span class="badge {{ $enrollment->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                        {{ $enrollment->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-xs text-bluedark/40">Belum ada siswa aktif yang terdaftar di rombel ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Guru Pengajar & Mapel di Kelas Ini (1 Kolom) -->
        <div class="space-y-5">
            <div class="panel p-5">
                <h3 class="font-heading font-semibold text-bluedark text-sm mb-3">Guru &amp; Mata Pelajaran</h3>
                <div class="space-y-3">
                    @forelse($class->teachingAssignments as $ta)
                        <div class="p-3 rounded-xl border border-bluelight bg-bluelight/20 space-y-1">
                            <div class="font-semibold text-xs text-bluedark">{{ $ta->subject?->name }}</div>
                            <div class="text-[11px] text-bluedark/70 flex items-center justify-between">
                                <span>{{ $ta->teacher?->full_name }}</span>
                                <span class="badge badge-blue text-[10px]">{{ $ta->weekly_hours }} Jam</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-bluedark/40 italic py-4 text-center">Belum ada penugasan guru pengajar untuk kelas ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
