<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran Induk - Semua Kelas &amp; Jurusan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
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
            padding: 16px;
            font-size: 10px;
        }

        .no-print-toolbar {
            max-width: 1200px;
            margin: 0 auto 14px auto;
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
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 24px;
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
            padding-bottom: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        .kop-logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .kop-text h2 {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #1e293b;
        }

        .kop-text h1 {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .kop-text p {
            font-size: 9.5px;
            color: #475569;
        }

        /* Dokumen Title */
        .doc-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-title h3 {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .doc-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 8px;
            padding: 4px 10px;
            background: #f1f5f9;
            border-radius: 6px;
        }

        /* Timetable Grid Master Table */
        table.timetable {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 14px;
        }

        table.timetable th,
        table.timetable td {
            border: 1px solid #94a3b8;
            padding: 4px 4px;
            vertical-align: middle;
            text-align: center;
        }

        table.timetable thead th {
            background-color: #0d47a1;
            color: #ffffff;
            font-weight: 700;
            font-size: 9.5px;
            padding: 6px 3px;
        }

        .day-header-cell {
            background-color: #f1f5f9;
            font-weight: 800;
            color: #0f172a;
            width: 65px;
            text-transform: uppercase;
            font-size: 9.5px;
        }

        .period-header-cell {
            background-color: #f8fafc;
            width: 85px;
            font-size: 8.5px;
            color: #334155;
            font-weight: 600;
        }

        .break-row-cell {
            background-color: #fef3c7 !important;
            color: #78350f !important;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            font-size: 9px;
            padding: 4px;
        }

        .subject-code-cell {
            background-color: #eff6ff;
            color: #1e40af;
            font-weight: 800;
            font-size: 9.5px;
            padding: 4px 2px;
        }

        .room-text {
            display: block;
            font-size: 7.5px;
            font-weight: 600;
            color: #065f46;
            margin-top: 1px;
        }

        .empty-cell {
            color: #cbd5e1;
            font-size: 8.5px;
        }

        /* Legenda Kode Mapel */
        .legend-section {
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .legend-title {
            font-size: 10.5px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        table.legend-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        table.legend-table th,
        table.legend-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 6px;
            text-align: left;
        }

        table.legend-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #334155;
        }

        /* Tanda Tangan */
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 240px;
            text-align: center;
            font-size: 10px;
        }

        .signature-space {
            height: 48px;
        }

        .signature-name {
            font-weight: 800;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-nip {
            font-size: 9px;
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

    <!-- Toolbar Atas (Tidak tercetak) -->
    <div class="no-print-toolbar no-print">
        <div style="display:flex; align-items:center; gap:8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span style="font-weight:700; color:#0f172a;">Jadwal Induk Seluruh Kelas &amp; Jurusan (Kode Mapel)</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" onclick="window.history.back()" class="btn btn-outline">&larr; Kembali</button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Dokumen Kertas -->
    <div class="paper">
        <!-- Kop Surat -->
        <div class="kop-container">
            @php
                $logoUrl = $schoolProfile?->logo_url ?? asset('assets/images/logo/logo.png');
            @endphp
            <img src="{{ $logoUrl }}" alt="Logo Sekolah" class="kop-logo" onerror="this.src='{{ asset('assets/images/logo/logo.png') }}'">
            <div class="kop-text">
                <h2>PEMERINTAH PROVINSI JAWA TENGAH &bull; DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
                <h1>{{ $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR' }}</h1>
                <p>{{ $schoolProfile?->address ?? 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah' }} &bull; Telp: {{ $schoolProfile?->phone ?? '(0271) 494549' }} &bull; Email: {{ $schoolProfile?->email ?? 'info@smkn2karanganyar.sch.id' }}</p>
            </div>
        </div>

        <!-- Judul & Meta Dokumen -->
        <div class="doc-title">
            <h3>JADWAL PELAJARAN INDUK SELURUH KELAS &amp; SEMUA JURUSAN</h3>
        </div>

        <div class="doc-meta">
            <div>
                <span>Cakupan: <strong>Seluruh Rombel ({{ count($classes) }} Kelas Terdaftar)</strong></span>
                <span style="margin: 0 8px;">&bull;</span>
                <span>Mode: <strong>Format Kode Mata Pelajaran</strong></span>
            </div>
            <div>
                <span>Semester: <strong>Ganjil / Genap</strong></span>
                <span style="margin: 0 8px;">&bull;</span>
                <span>Dicetak: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong></span>
            </div>
        </div>

        <!-- Tabel Matriks Jadwal Induk -->
        <table class="timetable">
            <thead>
                <tr>
                    <th style="width: 70px;">Hari</th>
                    <th style="width: 85px;">Jam / Waktu</th>
                    @foreach($classes as $c)
                        <th>
                            <div>{{ $c->name }}</div>
                            <div style="font-size:7.5px; font-weight:normal; opacity:0.85;">{{ $c->department?->code ?? '' }}</div>
                        </th>
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
                            {{-- Baris Istirahat --}}
                            <tr>
                                @if($pIdx === 0)
                                    <td rowspan="{{ $periodsCount }}" class="day-header-cell">
                                        {{ $dayName }}
                                    </td>
                                @endif
                                <td class="break-row-cell" style="font-size:8px;">
                                    ISTIRAHAT
                                </td>
                                <td colspan="{{ count($classes) }}" class="break-row-cell">
                                    ISTIRAHAT &bull; {{ $startTime }} - {{ $endTime }} WIB
                                </td>
                            </tr>
                        @else
                            {{-- Baris Pelajaran Reguler --}}
                            <tr>
                                @if($pIdx === 0)
                                    <td rowspan="{{ $periodsCount }}" class="day-header-cell">
                                        {{ $dayName }}
                                    </td>
                                @endif
                                <td class="period-header-cell">
                                    <strong>Jam {{ $p->period_number }}</strong>
                                    <div>{{ $startTime }}-{{ $endTime }}</div>
                                </td>

                                @foreach($classes as $c)
                                    @php
                                        $cell = $masterGrid[$dayNum][$p->id][$c->id] ?? null;
                                    @endphp

                                    @if($cell)
                                        <td class="subject-code-cell" title="{{ $cell['subject_name'] }} ({{ $cell['teacher_name'] }})">
                                            <span>{{ $cell['subject_code'] }}</span>
                                            @if(!empty($cell['room']))
                                                <span class="room-text">{{ $cell['room'] }}</span>
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

        <!-- Legenda Kode Mata Pelajaran -->
        <div class="legend-section">
            <div class="legend-title">Daftar Kode Mata Pelajaran (Keterangan)</div>
            <table class="legend-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th style="width: 140px;">Program Keahlian</th>
                        <th style="width: 70px;">Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th style="width: 140px;">Program Keahlian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $half = ceil(count($subjectLegends) / 2);
                        $col1 = array_slice($subjectLegends, 0, $half);
                        $col2 = array_slice($subjectLegends, $half);
                    @endphp
                    @for($i = 0; $i < $half; $i++)
                        @php
                            $item1 = $col1[$i] ?? null;
                            $item2 = $col2[$i] ?? null;
                        @endphp
                        <tr>
                            @if($item1)
                                <td style="font-weight:bold; color:#1e40af;">{{ $item1['code'] }}</td>
                                <td>{{ $item1['name'] }}</td>
                                <td style="color:#64748b;">{{ $item1['department'] }}</td>
                            @else
                                <td></td><td></td><td></td>
                            @endif

                            @if($item2)
                                <td style="font-weight:bold; color:#1e40af;">{{ $item2['code'] }}</td>
                                <td>{{ $item2['name'] }}</td>
                                <td style="color:#64748b;">{{ $item2['department'] }}</td>
                            @else
                                <td></td><td></td><td></td>
                            @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan Resmi -->
        <div class="signature-container">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 700;">Kepala Sekolah</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $schoolProfile?->principal_name ?? $schoolProfile?->headmaster_name ?? 'Sukidi, S.Pd., M.Pd.' }}</div>
                <div class="signature-nip">NIP. {{ $schoolProfile?->headmaster_nip ?? '-' }}</div>
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
