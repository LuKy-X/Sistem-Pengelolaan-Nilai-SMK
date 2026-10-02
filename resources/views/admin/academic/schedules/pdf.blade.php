<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - {{ $selectedClass?->name ?? 'Semua Kelas' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #0f172a;
            padding: 20px;
            font-size: 11px;
        }

        .no-print-toolbar {
            max-width: 1050px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-outline:hover {
            background: #f1f5f9;
        }

        .paper {
            max-width: 1050px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 30px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        /* Kop Surat Resmi */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
            text-align: center;
        }

        .kop-logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .kop-text h2 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #1e293b;
        }

        .kop-text h1 {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .kop-text p {
            font-size: 10px;
            color: #475569;
        }

        /* Dokumen Title */
        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title h3 {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .doc-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
            padding: 4px 8px;
            background: #f1f5f9;
            border-radius: 6px;
        }

        /* Timetable Table */
        table.timetable {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 16px;
        }

        table.timetable th,
        table.timetable td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.timetable thead th {
            background-color: #0d47a1;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            font-size: 11px;
            height: 32px;
        }

        .period-header-col {
            width: 110px;
            text-align: center;
            background-color: #f8fafc;
            font-weight: 700;
        }

        .break-cell {
            background-color: #fef3c7 !important;
            color: #78350f !important;
            font-weight: 800;
            text-align: center;
            letter-spacing: 1px;
            font-size: 10.5px;
            padding: 6px;
        }

        .schedule-cell {
            background: #eff6ff;
            border-left: 3px solid #2563eb !important;
            padding: 6px;
            height: 100%;
        }

        .subject-name {
            font-weight: 800;
            font-size: 10.5px;
            color: #0f172a;
            display: block;
            margin-bottom: 2px;
        }

        .teacher-name {
            font-size: 9.5px;
            color: #334155;
            display: block;
        }

        .room-badge {
            display: inline-block;
            margin-top: 3px;
            font-size: 8.5px;
            font-weight: 700;
            color: #1e40af;
            background: #dbeafe;
            padding: 1px 4px;
            border-radius: 4px;
        }

        .empty-cell {
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }

        /* Tanda Tangan */
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 240px;
            text-align: center;
            font-size: 11px;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            font-weight: 800;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-nip {
            font-size: 10px;
            color: #475569;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .paper {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (Tidak ikut tercetak) -->
    <div class="no-print-toolbar no-print">
        <div style="display:flex; align-items:center; gap:8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span style="font-weight:700; color:#0f172a;">Pratinjau PDF / Cetak Jadwal Pelajaran</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" onclick="window.history.back()" class="btn btn-outline">&larr; Kembali</button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Halaman Dokumen Cetak / PDF -->
    <div class="paper">
        <!-- Kop Surat -->
        <div class="kop-container">
            @if(!empty($schoolProfile?->logo))
                <img src="{{ asset('storage/'.$schoolProfile->logo) }}" alt="Logo" class="kop-logo">
            @else
                <div class="kop-logo" style="display:flex; align-items:center; justify-content:center; background:#0d47a1; color:#fff; font-weight:800; border-radius:10px; font-size:16px;">
                    SMK
                </div>
            @endif
            <div class="kop-text">
                <h2>PEMERINTAH PROVINSI JAWA TENGAH &bull; DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
                <h1>{{ $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR' }}</h1>
                <p>{{ $schoolProfile?->address ?? 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah' }} &bull; Telp: {{ $schoolProfile?->phone ?? '(0271) 494549' }} &bull; Email: {{ $schoolProfile?->email ?? 'info@smkn2karanganyar.sch.id' }}</p>
            </div>
        </div>

        <!-- Judul & Meta Informasi -->
        <div class="doc-title">
            <h3>JADWAL PELAJARAN MINGGUAN KELAS {{ strtoupper($selectedClass?->name ?? 'SEMUA KELAS') }}</h3>
        </div>

        <div class="doc-meta">
            <div>
                <span>Kelas / Rombel: <strong>{{ $selectedClass?->name ?? '-' }}</strong></span>
                <span style="margin: 0 8px;">&bull;</span>
                <span>Jurusan: <strong>{{ $selectedClass?->department?->name ?? '-' }}</strong></span>
            </div>
            <div>
                <span>Semester: <strong>Ganjil / Genap</strong></span>
                <span style="margin: 0 8px;">&bull;</span>
                <span>Dicetak: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong></span>
            </div>
        </div>

        <!-- Tabel Jadwal -->
        <table class="timetable">
            <thead>
                <tr>
                    <th style="width: 120px;">Jam / Waktu</th>
                    @foreach($days as $dayNum => $dayName)
                        <th>{{ strtoupper($dayName) }}</th>
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
                        {{-- Baris Istirahat --}}
                        <tr>
                            <td class="period-header-col break-cell" style="border-right: none;">
                                ISTIRAHAT
                            </td>
                            <td colspan="{{ count($days) }}" class="break-cell" style="border-left: none;">
                                ISTIRAHAT &bull; {{ $startTime }} - {{ $endTime }} WIB
                            </td>
                        </tr>
                    @else
                        {{-- Baris Jam Pelajaran Reguler --}}
                        <tr>
                            <td class="period-header-col">
                                <div style="font-weight: 800; font-size: 11px;">Jam ke-{{ $p->period_number }}</div>
                                <div style="font-size: 9px; color:#64748b;">{{ $startTime }} - {{ $endTime }}</div>
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
                                        $subjectName = $sch->teachingAssignment?->subject?->name ?? 'Mata Pelajaran';
                                        $teacherName = $sch->teachingAssignment?->teacher?->full_name ?? 'Guru Pengampu';
                                        $roomName = $sch->room ?: 'R. Kelas';
                                    @endphp
                                    <td rowspan="{{ $span }}" class="schedule-cell">
                                        <span class="subject-name">{{ $subjectName }}</span>
                                        <span class="teacher-name">{{ $teacherName }}</span>
                                        <span class="room-badge">{{ $roomName }}</span>
                                    </td>
                                @else
                                    <td class="empty-cell">-</td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ count($days) + 1 }}" style="text-align: center; padding: 20px; color:#94a3b8;">
                            Belum ada jadwal pelajaran untuk kelas ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Resmi -->
        <div class="signature-container">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 700;">Kepala Sekolah</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $schoolProfile?->headmaster_name ?? 'Drs. H. Sukardi, M.Pd.' }}</div>
                <div class="signature-nip">NIP. {{ $schoolProfile?->headmaster_nip ?? '19680512 199403 1 008' }}</div>
            </div>

            <div class="signature-box">
                <div>Karanganyar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight: 700;">Wakil Kepala Sekolah Bid. Kurikulum</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $schoolProfile?->curriculum_head_name ?? 'Siti Rahmawati, S.Pd., M.Eng.' }}</div>
                <div class="signature-nip">NIP. {{ $schoolProfile?->curriculum_head_nip ?? '19750914 200212 2 003' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
