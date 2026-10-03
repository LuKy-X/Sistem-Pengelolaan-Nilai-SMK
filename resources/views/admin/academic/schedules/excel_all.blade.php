<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Jadwal Pelajaran Induk - Semua Kelas &amp; Jurusan</title>
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
            color: #1e3a8a;
        }
        .header-sub {
            font-size: 9.5pt;
            text-align: center;
            color: #475569;
        }
        .meta-table {
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
            width: 140px;
        }
        .timetable {
            border-collapse: collapse;
            width: 100%;
        }
        .timetable th {
            background-color: #1e40af;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #94a3b8;
            padding: 8px 6px;
            font-size: 9.5pt;
        }
        .timetable td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            font-size: 9pt;
            vertical-align: middle;
            text-align: center;
        }
        .day-col {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            color: #0f172a;
        }
        .period-col {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
            color: #334155;
        }
        .break-cell {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            text-align: center;
            border: 1px solid #fcd34d;
        }
        .code-cell {
            background-color: #eff6ff;
            color: #1e40af;
            font-weight: bold;
            text-align: center;
        }
        .empty-cell {
            text-align: center;
            color: #94a3b8;
            background-color: #ffffff;
        }
        .legend-th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 6px;
        }
        .legend-td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8.5pt;
        }
        .sign-table {
            margin-top: 25px;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    <!-- Kop & Judul Dokumen -->
    <table>
        <tr>
            <td colspan="{{ count($classes) + 2 }}" class="header-title">
                {{ strtoupper($schoolProfile?->school_name ?? $schoolName ?? 'SMK NEGERI 2 KARANGANYAR') }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($classes) + 2 }}" class="header-sub">
                {{ $schoolProfile?->address ?? 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah' }} | Telp: {{ $schoolProfile?->phone ?? '(0271) 494549' }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($classes) + 2 }}" style="text-align: center; font-size: 12pt; font-weight: bold; padding: 10px 0; color: #0f172a;">
                JADWAL PELAJARAN INDUK (SEMUA KELAS &amp; SEMUA JURUSAN)
            </td>
        </tr>
    </table>

    <!-- Informasi Meta Dokumen -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Cakupan Kelas:</td>
            <td><strong>Seluruh Rombel ({{ count($classes) }} Kelas Terdaftar)</strong></td>
            <td style="width: 50px;"></td>
            <td class="meta-label">Semester:</td>
            <td><strong>Ganjil / Genap</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Format Tampilan:</td>
            <td><strong>Kode Mata Pelajaran (Subject Code)</strong></td>
            <td></td>
            <td class="meta-label">Tanggal Export:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <br />

    <!-- Tabel Matriks Jadwal Induk -->
    <table class="timetable" border="1">
        <thead>
            <tr>
                <th style="width: 80px;">HARI</th>
                <th style="width: 100px;">JAM / WAKTU</th>
                @foreach($classes as $c)
                    <th style="min-width: 90px;">{{ strtoupper($c->name) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($days as $dayNum => $dayName)
                @php
                    $periodsCount = count($periodsSorted);
                @endphp
                @foreach($periodsSorted as $pIdx => $p)
                    @php
                        $startTime = \Carbon\Carbon::parse($p->start_time)->format('H:i');
                        $endTime = \Carbon\Carbon::parse($p->end_time)->format('H:i');
                    @endphp

                    @if($p->is_break)
                        <tr>
                            @if($pIdx === 0)
                                <td rowspan="{{ $periodsCount }}" class="day-col">
                                    {{ strtoupper($dayName) }}
                                </td>
                            @endif
                            <td class="break-cell">ISTIRAHAT</td>
                            <td colspan="{{ count($classes) }}" class="break-cell">
                                ISTIRAHAT &bull; {{ $startTime }} - {{ $endTime }} WIB
                            </td>
                        </tr>
                    @else
                        <tr>
                            @if($pIdx === 0)
                                <td rowspan="{{ $periodsCount }}" class="day-col">
                                    {{ strtoupper($dayName) }}
                                </td>
                            @endif
                            <td class="period-col">
                                Jam {{ $p->period_number }}<br>
                                <small>{{ $startTime }}-{{ $endTime }}</small>
                            </td>

                            @foreach($classes as $c)
                                @php
                                    $cell = $masterGrid[$dayNum][$p->id][$c->id] ?? null;
                                @endphp

                                @if($cell)
                                    <td class="code-cell">
                                        {{ $cell['subject_code'] }}
                                        @if(!empty($cell['room']))
                                            <br><small style="color: #047857; font-weight: normal;">({{ $cell['room'] }})</small>
                                        @endif
                                    </td>
                                @else
                                    <td class="empty-cell">-</td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>

    <br /><br />

    <!-- Daftar Keterangan Kode Mata Pelajaran -->
    <table border="1" style="border-collapse: collapse; width: 80%;">
        <thead>
            <tr>
                <th colspan="3" style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; text-align: left; padding: 6px;">
                    DAFTAR KODE MATA PELAJARAN (LEGENDA)
                </th>
            </tr>
            <tr>
                <th style="width: 100px; background-color: #f1f5f9; text-align: center;">KODE</th>
                <th style="background-color: #f1f5f9; text-align: left; padding: 6px;">NAMA MATA PELAJARAN</th>
                <th style="width: 200px; background-color: #f1f5f9; text-align: left; padding: 6px;">PROGRAM KEAHLIAN / JURUSAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjectLegends as $leg)
                <tr>
                    <td style="font-weight: bold; color: #1e40af; text-align: center;">{{ $leg['code'] }}</td>
                    <td style="padding: 4px 6px;">{{ $leg['name'] }}</td>
                    <td style="padding: 4px 6px; color: #475569;">{{ $leg['department'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br /><br />

    <!-- Tanda Tangan -->
    <table class="sign-table" style="width: 100%;">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br />
                <strong>Kepala Sekolah</strong><br /><br /><br /><br />
                <strong><u>{{ $schoolProfile?->principal_name ?? $schoolProfile?->headmaster_name ?? 'Sukidi, S.Pd., M.Pd.' }}</u></strong><br />
                NIP. {{ $schoolProfile?->headmaster_nip ?? '-' }}
            </td>
            <td style="width: 50%; text-align: center;">
                Karanganyar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br />
                <strong>Waka Kurikulum</strong><br /><br /><br /><br />
                <strong><u>{{ $schoolProfile?->curriculum_head_name ?? 'Siti Rahmawati, S.Pd., M.Eng.' }}</u></strong><br />
                NIP. {{ $schoolProfile?->curriculum_head_nip ?? '19750914 200212 2 003' }}
            </td>
        </tr>
    </table>
</body>
</html>
