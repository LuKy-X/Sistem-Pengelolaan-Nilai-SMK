@extends('layouts.admin')

@section('title', 'Daftar Nilai — ' . ($gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas'))

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Navigasi Kembali -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs sm:text-sm text-bluedark/60 flex-wrap">
            <a href="{{ route('admin.grades.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-bluelight text-xs font-semibold text-bluedark/70 hover:text-blueprim hover:border-blueprim transition-all shadow-2xs">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>Kembali ke Monitoring Nilai</span>
            </a>
            <span class="text-bluedark/30">/</span>
            <span class="font-medium text-bluedark/70">{{ $gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas' }}</span>
            <span class="text-bluedark/30">/</span>
            <span class="font-semibold text-bluedark">{{ $gradebook->name }}</span>
        </div>
    </div>

    <!-- Title & Action Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">
                    Daftar Nilai: {{ $gradebook->name }}
                </h1>
                @if($gradebook->is_active)
                    <span class="badge badge-green text-xs font-semibold">Aktif</span>
                @else
                    <span class="badge badge-gray text-xs font-semibold">Nonaktif</span>
                @endif
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blueprim border border-blue-200">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Mode Monitoring (Hanya Lihat)</span>
                </span>
            </div>
            <p class="text-xs text-bluedark/60 mt-1">
                Kelas <strong>{{ $gradebook->teachingAssignment?->schoolClass?->name ?? '-' }}</strong> ({{ $gradebook->teachingAssignment?->schoolClass?->department?->name ?? '-' }}) &middot;
                Mata Pelajaran: <strong>{{ $gradebook->teachingAssignment?->subject?->name ?? '-' }}</strong> &middot;
                Semester {{ $gradebook->teachingAssignment?->semester?->name ?? '-' }} ({{ $gradebook->teachingAssignment?->semester?->academicYear?->name ?? '2026/2027' }}) &middot;
                Guru Pengajar: <strong>{{ $gradebook->teachingAssignment?->teacher?->full_name ?? '-' }}</strong>
            </p>
        </div>

        <!-- Tombol Aksi Admin: Cetak / Navigasi (Hanya Lihat / Read-Only) -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-bluelight text-xs font-semibold text-bluedark/80 hover:text-blueprim hover:bg-bluelight/40 transition-colors shadow-2xs" title="Cetak lembar nilai (Print)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak Rekap</span>
            </button>
            <a href="{{ route('admin.grades.index') }}" class="btn btn-outline btn-sm text-xs font-semibold gap-1.5" title="Kembali ke daftar monitoring buku nilai">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>Daftar Buku Nilai</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Info Cepat (KPI Badges) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-bold text-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <span class="text-[11px] text-bluedark/50 block">Siswa Terdaftar</span>
                <span class="font-heading font-bold text-bluedark text-sm">{{ $students->count() }} Siswa</span>
            </div>
        </div>

        <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-bold text-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <div>
                <span class="text-[11px] text-bluedark/50 block">Struktur Kolom</span>
                <span class="font-heading font-bold text-bluedark text-sm">{{ $columns->count() }} Kolom</span>
            </div>
        </div>

        <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <span class="text-[11px] text-bluedark/50 block">Standar KKM</span>
                <span class="font-heading font-bold text-emerald-600 text-sm">75.00</span>
            </div>
        </div>

        <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <span class="text-[11px] text-bluedark/50 block">Guru Pengajar</span>
                <span class="font-heading font-bold text-indigo-700 text-xs truncate max-w-[130px] block" title="{{ $gradebook->teachingAssignment?->teacher?->full_name }}">
                    {{ $gradebook->teachingAssignment?->teacher?->full_name ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Lembar Spreadsheet Nilai (Read-Only dengan Konsep Kolom) -->
    <div class="panel p-5 bg-white border border-bluelight rounded-2xl shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-bluelight">
            <div>
                <h2 class="font-heading font-bold text-bluedark text-base">
                    Daftar Nilai Siswa
                </h2>
                <p class="text-xs text-bluedark/50 mt-0.5">
                    Tampilan lembar nilai siswa pada kelas {{ $gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas' }} &mdash; Mode Monitoring Admin (Hanya Lihat)
                </p>
            </div>

            <!-- Legend Status Nilai -->
            <div class="flex items-center gap-3 text-xs flex-wrap">
                <span class="flex items-center gap-1.5 text-bluedark/70">
                    <span class="w-2.5 h-2.5 rounded-full bg-blueprim inline-block"></span> Nilai Komponen
                </span>
                <span class="flex items-center gap-1.5 text-bluedark/70">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Kalkulasi / Rata-rata
                </span>
                <span class="flex items-center gap-1.5 text-rose-600 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> &lt; 75 (Di bawah KKM)
                </span>
            </div>
        </div>

        <div class="overflow-x-auto border border-bluelight rounded-xl shadow-2xs">
            <table class="tbl w-full text-left">
                <thead>
                    <tr style="background:#0D47A1;color:#fff;">
                        <th class="text-center w-12 border-r border-blue-900" style="color:#fff;">No</th>
                        <th class="w-28 border-r border-blue-900" style="color:#fff;">NIS</th>
                        <th class="min-w-[190px] border-r border-blue-900" style="color:#fff;">Nama Siswa</th>
                        @forelse($columns as $col)
                            @php
                                $isSummary = $col->column_type->value === 'SUMMARY';
                            @endphp
                            <th class="text-center min-w-[95px] border-r border-blue-900 px-2 py-2.5 {{ $isSummary ? 'bg-blue-900/60' : '' }}" title="{{ $col->name }} (Bobot: {{ $col->weight }}%)" style="color:#fff;">
                                <div class="flex flex-col items-center">
                                    <div class="font-heading font-bold text-xs uppercase">{{ $col->code }}</div>
                                    <div class="text-[10px] font-normal opacity-85 truncate max-w-[90px]">{{ $col->name }}</div>
                                    <span class="text-[9px] px-1 py-0.2 rounded mt-0.5 {{ $isSummary ? 'bg-amber-400 text-slate-900 font-bold' : 'bg-white/20 text-white' }}">
                                        {{ $isSummary ? 'RATA' : ($col->weight . '%') }}
                                    </span>
                                </div>
                            </th>
                        @empty
                            <th class="text-center text-xs py-3 text-white/70 italic" style="color:#fff;">Belum ada kolom nilai</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $item)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="text-center font-semibold text-slate-500 text-xs border-r border-slate-100">{{ $index + 1 }}</td>
                            <td class="font-mono text-xs text-slate-500 border-r border-slate-100">{{ $item->student?->nis ?? '-' }}</td>
                            <td class="font-medium text-bluedark text-xs border-r border-slate-100 py-2.5">
                                <span class="font-semibold block">{{ $item->student?->full_name ?? 'Siswa' }}</span>
                            </td>
                            @foreach($columns as $col)
                                @php
                                    $val = $scoresMatrix[$item->student_id][$col->id] ?? null;
                                    $isSummary = $col->column_type->value === 'SUMMARY';
                                    $hasScore = ($val !== null && $val !== '');
                                @endphp
                                <td class="text-center text-xs border-r border-slate-100 py-2.5 {{ $isSummary ? 'bg-amber-50/50' : '' }}">
                                    @if($hasScore)
                                        <span class="font-mono font-bold {{ $isSummary ? 'text-blueprim text-sm' : ($val < 75 ? 'text-rose-600' : 'text-slate-800') }}">
                                            {{ is_numeric($val) ? (float) $val : $val }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 font-mono text-xs">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + max(1, $columns->count()) }}" class="text-center py-10 text-xs text-bluedark/40 italic">
                                Belum ada siswa yang terdaftar pada buku nilai ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($students->isNotEmpty() && $columns->isNotEmpty())
                    <tfoot>
                        <tr style="background:#F1F7FD;" class="font-bold border-t-2 border-bluelight text-bluedark">
                            <td colspan="3" class="text-right px-4 py-3 text-xs uppercase tracking-wider">
                                Rata-rata Kelas:
                            </td>
                            @foreach($columns as $col)
                                @php
                                    $avg = $columnAverages[$col->id] ?? '-';
                                    $isSummary = $col->column_type->value === 'SUMMARY';
                                @endphp
                                <td class="text-center py-3 font-mono text-xs border-r border-slate-200 {{ $isSummary ? 'text-blueprim font-bold text-sm bg-blue-50/50' : 'text-bluedark' }}">
                                    {{ $avg !== '-' ? (is_numeric($avg) ? number_format((float)$avg, 1) : $avg) : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Footer Information -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 mt-4 border-t border-bluelight/70 text-xs text-bluedark/60">
            <div>
                Menampilkan <strong>{{ $students->count() }} siswa</strong> dan <strong>{{ $columns->count() }} kolom penilaian</strong>.
            </div>
            <div class="flex items-center gap-2 text-bluedark/50 italic text-[11px]">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Mode Monitoring Admin: Lembar nilai tersinkronisasi otomatis dengan buku nilai guru (Read-Only).</span>
            </div>
        </div>
    </div>

</div>
@endsection
