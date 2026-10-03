<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Nilai Siswa - {{ $gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #0f172a;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }
        .header-sub {
            font-size: 10pt;
            text-align: center;
            color: #334155;
        }
        .meta-table {
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
            width: 130px;
        }
        .grades-table {
            border-collapse: collapse;
            width: 100%;
        }
        .grades-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
            border: 1px solid #334155;
            padding: 6px 4px;
            font-size: 10pt;
        }
        .grades-table td {
            border: 1px solid #334155;
            padding: 5px;
            font-size: 9.5pt;
            vertical-align: middle;
            text-align: center;
        }
        .grades-table td.text-left {
            text-align: left;
        }
        .grades-table tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $assignment = $gradebook->teachingAssignment;
        $schoolClass = $assignment?->schoolClass;
        $subject = $assignment?->subject;
        $teacher = $assignment?->teacher;
        $semester = $assignment?->semester;
        $academicYear = $semester?->academicYear;
        $schoolName = $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR';
        $totalCols = 3 + max(1, $columns->count());
    @endphp

    <table style="width: 100%; margin-bottom: 16px;">
        <tr>
            <td colspan="{{ $totalCols }}" class="header-title">{{ strtoupper($schoolName) }}</td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" class="header-title">REKAPITULASI BUKU NILAI PESERTA DIDIK</td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" class="header-sub">Tahun Pelajaran {{ $academicYear?->name ?? '2026/2027' }} &bull; Semester {{ $semester?->name ?? 'Gasal' }}</td>
        </tr>
    </table>

    <table class="meta-table" style="width: 100%; margin-bottom: 14px;">
        <tr>
            <td class="meta-label">Rombel / Kelas</td>
            <td>: {{ $schoolClass?->name ?? '-' }} ({{ $schoolClass?->department?->name ?? '-' }})</td>
            <td class="meta-label">Mata Pelajaran</td>
            <td>: {{ $subject?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Guru Pengajar</td>
            <td>: {{ $teacher?->full_name ?? '-' }}</td>
            <td class="meta-label">Tanggal Export</td>
            <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 35px;">No</th>
                <th rowspan="2" style="width: 100px;">NIS / NISN</th>
                <th rowspan="2" style="width: 220px; text-align: left; padding-left: 8px;">Nama Peserta Didik</th>
                @forelse($columns as $col)
                    <th>{{ $col->category?->name ?? ($col->column_type->value === 'SUMMARY' ? 'Nilai Akhir' : 'Penilaian') }}</th>
                @empty
                    <th>Nilai</th>
                @endforelse
            </tr>
            <tr>
                @forelse($columns as $col)
                    <th style="font-size: 9pt;">{{ $col->code ?: $col->name }}</th>
                @empty
                    <th>-</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $st)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $st->student?->nisn ?? $st->student?->nis ?? '-' }}</td>
                    <td class="text-left" style="padding-left: 8px; font-weight: bold;">{{ $st->student?->full_name ?? '-' }}</td>
                    @forelse($columns as $col)
                        @php
                            $val = $scoresMatrix[$st->student_id][$col->id] ?? null;
                        @endphp
                        <td>{{ ($val !== null && is_numeric($val)) ? (float) $val : '-' }}</td>
                    @empty
                        <td>-</td>
                    @endforelse
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalCols }}" style="padding: 14px; color: #64748b;">
                        Belum ada data siswa terdaftar.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($students->isNotEmpty() && $columns->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: left; padding-left: 8px;">RATA-RATA KELAS</td>
                    @foreach($columns as $col)
                        <td>{{ $columnAverages[$col->id] ?? '-' }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
