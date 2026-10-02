<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Jadwal Pelajaran - {{ $selectedClass?->name ?? $className ?? 'Semua Kelas' }}</title>
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
            font-size: 10pt;
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
            width: 120px;
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
            font-size: 10pt;
        }
        .timetable td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        .period-col {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
            width: 110px;
            border: 1px solid #cbd5e1;
        }
        .break-cell {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            text-align: center;
            border: 1px solid #fcd34d;
        }
        .schedule-cell {
            background-color: #eff6ff;
            border-left: 3px solid #2563eb;
            vertical-align: top;
        }
        .subject-name {
            font-weight: bold;
            color: #1e3a8a;
            font-size: 10pt;
            display: block;
        }
        .teacher-name {
            color: #334155;
            font-size: 8.5pt;
            display: block;
        }
        .room-pill {
            color: #047857;
            font-size: 8pt;
            font-weight: bold;
            display: block;
            margin-top: 2px;
        }
        .empty-cell {
            text-align: center;
            color: #94a3b8;
            background-color: #ffffff;
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
            <td colspan="{{ count($days) + 1 }}" class="header-title">
                {{ strtoupper($schoolProfile?->school_name ?? $schoolName ?? 'SMK NEGERI 2 KARANGANYAR') }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($days) + 1 }}" class="header-sub">
                {{ $schoolProfile?->address ?? 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah' }} | Telp: {{ $schoolProfile?->phone ?? '(0271) 494549' }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($days) + 1 }}" style="text-align: center; font-size: 12pt; font-weight: bold; padding: 10px 0; color: #0f172a;">
                JADWAL PELAJARAN MINGGUAN KELAS {{ strtoupper($selectedClass?->name ?? $className ?? 'SEMUA KELAS') }}
            </td>
        </tr>
    </table>

    <!-- Informasi Kelas & Waktu -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Kelas / Rombel:</td>
            <td><strong>{{ $selectedClass?->name ?? $className ?? '-' }}</strong></td>
            <td style="width: 50px;"></td>
            <td class="meta-label">Semester:</td>
            <td><strong>Ganjil / Genap</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Program Keahlian:</td>
            <td><strong>{{ $selectedClass?->department?->name ?? '-' }}</strong></td>
            <td></td>
            <td class="meta-label">Tanggal Export:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <br />

    <!-- Tabel Matriks Jadwal -->
    <table class="timetable" border="1">
        <thead>
            <tr>
                <th style="width: 120px;">Jam / Waktu</th>
                @foreach($days as $dayNum => $dayName)
                    <th style="width: 160px;">{{ strtoupper($dayName) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($periodsSorted as $p)
                @php
                    $startTime = \Carbon\Carbon::parse($p->start_time)->format('H:i');
                    $endTime = \Carbon\Carbon::parse($p->end_time)->format('H:i');
                @endphp

                @if($p->is_break)
                    <tr>
                        <td class="period-col break-cell">ISTIRAHAT</td>
                        <td colspan="{{ count($days) }}" class="break-cell">
                            ISTIRAHAT &bull; {{ $startTime }} - {{ $endTime }} WIB
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="period-col">
                            Jam ke-{{ $p->period_number }}<br />
                            <small style="color: #64748b; font-weight: normal;">{{ $startTime }} - {{ $endTime }}</small>
                        </td>

                        @foreach($days as $dayNum => $dayName)
                            @php
                                $cell = $matrix[$p->id][$dayNum] ?? ['type' => 'empty'];
                            @endphp

                            @if($cell['type'] === 'covered')
                                @continue
                            @endif

                            @if($cell['type'] === 'start')
                                @php
                                    $sch = $cell['schedule'];
                                    $span = $cell['span'];
                                    $subName = $sch->teachingAssignment?->subject?->name ?? 'Mata Pelajaran';
                                    $teaName = $sch->teachingAssignment?->teacher?->full_name ?? 'Guru Pengampu';
                                    $room = $sch->room ?: 'R. Kelas';
                                @endphp
                                <td rowspan="{{ $span }}" class="schedule-cell">
                                    <span class="subject-name">{{ $subName }}</span>
                                    <span class="teacher-name">{{ $teaName }}</span>
                                    <span class="room-pill">{{ $room }}</span>
                                </td>
                            @else
                                <td class="empty-cell">-</td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ count($days) + 1 }}" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Belum ada jam pelajaran yang ditambahkan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br /><br />

    <!-- Tanda Tangan -->
    <table class="sign-table">
        <tr>
            <td colspan="2" style="text-align: center; width: 50%;">
                Mengetahui,<br />
                Kepala Sekolah
            </td>
            @if(count($days) > 3)
                <td colspan="{{ count($days) - 3 }}"></td>
            @endif
            <td colspan="2" style="text-align: center; width: 50%;">
                Karanganyar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br />
                Waka Kurikulum
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($days) + 1 }}" style="height: 55px;"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">
                {{ $schoolProfile?->headmaster_name ?? 'Drs. H. Sukardi, M.Pd.' }}
            </td>
            @if(count($days) > 3)
                <td colspan="{{ count($days) - 3 }}"></td>
            @endif
            <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">
                Waka Kurikulum, M.Pd.
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; font-size: 8.5pt; color: #475569;">
                NIP. {{ $schoolProfile?->headmaster_nip ?? '19700101 199503 1 002' }}
            </td>
            @if(count($days) > 3)
                <td colspan="{{ count($days) - 3 }}"></td>
            @endif
            <td colspan="2" style="text-align: center; font-size: 8.5pt; color: #475569;">
                NIP. 19780512 200501 1 008
            </td>
        </tr>
    </table>
</body>
</html>
