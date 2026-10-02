@extends('layouts.admin')

@section('title', 'Rekapitulasi Absensi Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Rekapitulasi Absensi Siswa</h1>
            <p class="text-sm text-bluedark/60 mt-1">Pantau presensi harian seluruh rombel yang dicatat melalui jurnal guru</p>
        </div>

        <a href="{{ route('admin.attendance.export', request()->query()) }}" class="btn btn-outline btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Ekspor CSV</span>
        </a>
    </div>

    <!-- Filter Rekap Absensi matching template/admin/absensi-siswa.html -->
    <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">Filter Rekap Absensi</h2>

        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="f-label">Tanggal Presensi</label>
                <input type="date" name="date" value="{{ $date }}" class="f-input text-xs py-2">
            </div>

            <div>
                <label class="f-label">Kelas / Rombel</label>
                <select name="class_id" class="f-select text-xs py-2">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Status Kehadiran</label>
                <select name="status" class="f-select text-xs py-2">
                    <option value="">Semua Status</option>
                    <option value="PRESENT" {{ $status == 'PRESENT' ? 'selected' : '' }}>Hadir</option>
                    <option value="PERMIT" {{ $status == 'PERMIT' ? 'selected' : '' }}>Izin</option>
                    <option value="SICK" {{ $status == 'SICK' ? 'selected' : '' }}>Sakit</option>
                    <option value="ABSENT" {{ $status == 'ABSENT' ? 'selected' : '' }}>Alpha</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-2.5 w-full">
                    Terapkan Filter
                </button>
                @if($classId || $status || $date !== today()->toDateString())
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline btn-sm text-xs py-2.5">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- KPI Ringkasan Hari Ini -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-bluelight text-center shadow-xs">
            <div class="text-2xl font-bold text-emerald-600 font-heading">{{ $stats['hadir'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Total Hadir</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-bluelight text-center shadow-xs">
            <div class="text-2xl font-bold text-blue-600 font-heading">{{ $stats['izin'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Total Izin</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-bluelight text-center shadow-xs">
            <div class="text-2xl font-bold text-amber-600 font-heading">{{ $stats['sakit'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Total Sakit</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-bluelight text-center shadow-xs">
            <div class="text-2xl font-bold text-rose-600 font-heading">{{ $stats['alpha'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Total Alpha</div>
        </div>
    </div>

    <!-- Table Rekap Absensi -->
    <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">
            Catatan Presensi: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </h2>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th class="w-24 text-center">Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $idx => $att)
                        @php
                            $statusVal = $att->status instanceof \App\Enums\AttendanceStatus ? $att->status->value : (string) $att->status;
                            $badgeColor = match($statusVal) {
                                'PRESENT' => 'badge-green',
                                'PERMIT' => 'badge-blue',
                                'SICK' => 'badge-yellow',
                                'ABSENT' => 'badge-red',
                                default => 'badge-gray',
                            };
                            $label = match($statusVal) {
                                'PRESENT' => 'Hadir',
                                'PERMIT' => 'Izin',
                                'SICK' => 'Sakit',
                                'ABSENT' => 'Alpha',
                                default => $statusVal,
                            };
                        @endphp
                        <tr>
                            <td>{{ $attendances->firstItem() + $idx }}</td>
                            <td class="font-mono text-xs">{{ $att->student?->nis ?? '-' }}</td>
                            <td class="font-semibold text-bluedark">{{ $att->student?->full_name ?? '-' }}</td>
                            <td>{{ $att->journal?->teachingAssignment?->schoolClass?->name ?? '-' }}</td>
                            <td>{{ $att->journal?->teachingAssignment?->subject?->name ?? '-' }}</td>
                            <td>{{ $att->journal?->teachingAssignment?->teacher?->full_name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badgeColor }} text-[11px]">{{ $label }}</span>
                            </td>
                            <td class="text-xs text-bluedark/60">{{ $att->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada catatan absensi siswa pada tanggal dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attendances->links() }}
        </div>
    </div>

</div>
@endsection
