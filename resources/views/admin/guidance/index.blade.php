@extends('layouts.admin')

@section('title', 'Monitoring Layanan BK & Kedisiplinan')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Layanan BK &amp; Kedisiplinan Siswa</h1>
            <p class="text-sm text-bluedark/60 mt-1">Pantau perizinan keluar sekolah, rekam pelanggaran/penghargaan, dan surat peringatan</p>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight shadow-xs">
            <div class="text-2xl font-bold text-blueprim font-heading">{{ $permitStats['total'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Total Izin Keluar</div>
            <div class="text-[10px] text-amber-600 mt-0.5">{{ $permitStats['pending'] }} Menunggu Approval</div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight shadow-xs">
            <div class="text-2xl font-bold text-rose-600 font-heading">{{ $permitStats['late'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Izin Terlambat Kembali</div>
            <div class="text-[10px] text-bluedark/40 mt-0.5">Melebihi batas toleransi</div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight shadow-xs">
            <div class="text-2xl font-bold text-rose-700 font-heading">{{ $disciplineStats['violations'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Kasus Pelanggaran</div>
            <div class="text-[10px] text-rose-600 mt-0.5">Mutasi poin negatif</div>
        </div>

        <div class="kpi-card bg-white p-4 rounded-2xl border border-bluelight shadow-xs">
            <div class="text-2xl font-bold text-emerald-600 font-heading">{{ $disciplineStats['rewards'] }}</div>
            <div class="text-xs text-bluedark/60 font-medium mt-1">Penghargaan / Prestasi</div>
            <div class="text-[10px] text-emerald-600 mt-0.5">Mutasi poin positif</div>
        </div>
    </div>

    <!-- Dua Kolom: Surat Peringatan & Izin Keluar Terkini -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Panel Surat Peringatan (SP) -->
        <div class="panel p-5 space-y-3">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Surat Peringatan Diterbitkan</h2>
            <div class="overflow-x-auto">
                <table class="tbl w-full text-left">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Tingkat SP</th>
                            <th>No. Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disciplinaryLetters as $sp)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sp->issued_at)->translatedFormat('d M Y') }}</td>
                                <td class="font-semibold text-bluedark">{{ $sp->student?->full_name }}</td>
                                <td>
                                    @php
                                        $typeVal = $sp->type instanceof \BackedEnum ? $sp->type->value : (string) $sp->type;
                                        $spColor = match($typeVal) {
                                            'SP3' => 'badge-red',
                                            'SP2' => 'badge-yellow',
                                            default => 'badge-blue',
                                        };
                                    @endphp
                                    <span class="badge {{ $spColor }}">{{ $typeVal }}</span>
                                </td>
                                <td class="font-mono text-xs">SP-{{ str_pad($sp->id, 4, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-xs text-bluedark/40">Belum ada surat peringatan yang diterbitkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel Izin Keluar Terbaru -->
        <div class="panel p-5 space-y-3">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Aktivitas Izin Keluar Terbaru</h2>
            <div class="overflow-x-auto">
                <table class="tbl w-full text-left">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Alasan</th>
                            <th>Batas Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPermits as $permit)
                            <tr>
                                <td class="font-semibold text-bluedark">{{ $permit->student?->full_name }}</td>
                                <td class="text-xs">{{ $permit->reason?->name ?? 'Keperluan Pribadi' }}</td>
                                <td class="text-xs font-mono">
                                    {{ \Carbon\Carbon::parse($permit->planned_return_at)->format('H:i') }} WIB
                                </td>
                                <td>
                                    <span class="badge {{ $permit->status === 'APPROVED' ? 'badge-green' : ($permit->status === 'LATE' ? 'badge-red' : 'badge-yellow') }} text-[10px]">
                                        {{ $permit->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-xs text-bluedark/40">Belum ada riwayat izin keluar siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Panel Histori Mutasi Poin Kedisiplinan -->
    <div class="panel p-5 space-y-3">
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Catatan Pelanggaran &amp; Penghargaan Disiplin Terkini</h2>
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="w-24 text-center">Poin Delta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDisciplineRecords as $idx => $rec)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($rec->occurred_at)->translatedFormat('d M Y') }}</td>
                            <td class="font-semibold text-bluedark">{{ $rec->student?->full_name }}</td>
                            <td>{{ $rec->category?->name ?? 'Umum' }}</td>
                            <td class="text-xs text-bluedark/70">{{ $rec->description }}</td>
                            <td class="text-center font-bold font-mono">
                                @if($rec->points_delta > 0)
                                    <span class="text-emerald-600">+{{ $rec->points_delta }}</span>
                                @else
                                    <span class="text-rose-600">{{ $rec->points_delta }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-xs text-bluedark/40">Belum ada catatan mutasi poin disiplin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
