<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Kelas - {{ $assignment->schoolClass?->name ?? 'Kelas' }} - {{ $range === 'weekly' ? 'Mingguan' : $selectedDate }}</title>
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
            font-size: 11px;
        }

        .no-print-toolbar {
            max-width: 1050px;
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
            background: #0d47a1;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1565c0;
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
            padding: 22px 26px;
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
            width: 70px;
            height: 70px;
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
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 16px;
            font-size: 10.5px;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .meta-row {
            display: flex;
            align-items: center;
        }

        .meta-label {
            width: 110px;
            font-weight: 700;
            color: #475569;
        }

        .meta-value {
            font-weight: 600;
            color: #0f172a;
        }

        /* Journal Table */
        table.journal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 14px;
        }

        table.journal-table th,
        table.journal-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.journal-table thead th {
            background-color: #0d47a1;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            font-size: 10px;
        }

        .th-sub {
            background-color: #1565c0 !important;
            font-size: 9px;
            font-weight: 600;
        }

        .break-row td {
            background-color: #fef3c7 !important;
            color: #78350f !important;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            font-size: 9.5px;
            padding: 4px;
        }

        .unfilled-row td {
            background-color: #fafafa;
            color: #94a3b8;
        }

        .badge-att {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 9px;
            margin-right: 2px;
        }
        .badge-sakit { background: #fef3c7; color: #92400e; }
        .badge-izin { background: #e0f2fe; color: #0369a1; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }

        /* Signatures Block */
        .signatures-container {
            display: flex;
            justify-content: space-between;
            margin-top: 14px;
            padding: 0 20px;
            font-size: 10.5px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 250px;
            text-align: center;
        }

        .signature-space {
            height: 52px;
        }

        .signature-name {
            font-weight: 800;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-nip {
            font-size: 9.5px;
            color: #64748b;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .paper {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Non-print toolbar -->
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <span style="font-size: 11px; color: #64748b;">
                💡 <em>Pilih "Save as PDF" pada dialog cetak browser untuk menyimpan file.</em>
            </span>
        </div>
        <div>
            <button type="button" onclick="window.close()" class="btn btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                <span>Tutup</span>
            </button>
        </div>
    </div>

    <!-- Printable Paper Sheet -->
    <div class="paper">
        <!-- Kop Surat Resmi -->
        @php
            $logoDiskPath = null;
            if (!empty($schoolProfile?->logo)) {
                if (file_exists(public_path('storage/' . $schoolProfile->logo))) {
                    $logoDiskPath = public_path('storage/' . $schoolProfile->logo);
                } elseif (file_exists(storage_path('app/public/' . $schoolProfile->logo))) {
                    $logoDiskPath = storage_path('app/public/' . $schoolProfile->logo);
                }
            }

            if (!$logoDiskPath && file_exists(public_path('assets/images/logo/logo.png'))) {
                $logoDiskPath = public_path('assets/images/logo/logo.png');
            }

            $logoSrc = asset('assets/images/logo/logo.png');
            if ($logoDiskPath && file_exists($logoDiskPath)) {
                $mime = mime_content_type($logoDiskPath) ?: 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoDiskPath));
            }
        @endphp
        <div class="kop-container">
            <img src="{{ $logoSrc }}" alt="Logo SMK Negeri 2 Karanganyar" class="kop-logo" onerror="this.src='{{ asset('assets/images/logo/logo.png') }}'">
            <div class="kop-text">
                <h2>PEMERINTAH PROVINSI JAWA TENGAH &middot; DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
                <h1>{{ strtoupper($schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR') }}</h1>
                <p>
                    {{ $schoolProfile?->address ?? 'Jl. Laksda Yos Sudarso, Bejen, Kec. Karanganyar, Kab. Karanganyar, Jawa Tengah 57716' }}
                    @if($schoolProfile?->phone) &middot; Telp: {{ $schoolProfile->phone }} @endif
                    @if($schoolProfile?->email) &middot; Email: {{ $schoolProfile->email }} @endif
                    @if($schoolProfile?->website) &middot; Website: {{ $schoolProfile->website }} @endif
                </p>
            </div>
        </div>

        <!-- Dokumen Header & Judul -->
        <div class="doc-title">
            <h3>BUKU AGENDA JURNAL PEMBELAJARAN &amp; PRESENSI KELAS</h3>
        </div>

        <!-- Metadata Info -->
        <div class="doc-meta">
            <div class="meta-row">
                <span class="meta-label">Kelas</span>
                <span class="meta-value">: {{ $assignment->schoolClass?->name ?? '-' }} ({{ $assignment->schoolClass?->department?->name ?? 'SMK' }})</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tahun Ajaran</span>
                <span class="meta-value">: {{ $assignment->semester?->academicYear?->name ?? '2024/2025' }} &middot; {{ $assignment->semester?->name ?? 'Semester Ganjil' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Mata Pelajaran</span>
                <span class="meta-value">: {{ $assignment->subject?->name ?? 'Semua Mapel' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Wali Kelas</span>
                <span class="meta-value">: {{ $assignment->schoolClass?->homeroomTeacher?->full_name ?? ($assignment->schoolClass?->homeroomTeacher?->user?->name ?? '-') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Periode Rekap</span>
                <span class="meta-value">
                    : @if($range === 'weekly')
                        Minggu Ke-{{ $weekStart->isoWeek() }} ({{ $weekStart->format('d/m/Y') }} s.d. {{ $weekEnd->format('d/m/Y') }})
                    @else
                        {{ $referenceDate->isoFormat('dddd, D MMMM Y') }}
                    @endif
                </span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Total Siswa</span>
                <span class="meta-value">: {{ $enrolledStudents->count() }} Siswa Terdaftar</span>
            </div>
        </div>

        <!-- Main Journal Table -->
        <table class="journal-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 85px;">Hari / Tgl</th>
                    <th rowspan="2" style="width: 65px;">Jam Ke-</th>
                    <th rowspan="2" style="width: 85px;">Waktu</th>
                    <th rowspan="2" style="width: 120px;">Mata Pelajaran</th>
                    <th rowspan="2" style="width: 130px;">Guru Pengajar</th>
                    <th rowspan="2" style="min-width: 150px;">Materi Pokok Pembelajaran</th>
                    <th colspan="4" style="width: 130px;">Kehadiran</th>
                    <th rowspan="2" style="min-width: 160px;">Keterangan / Siswa Tidak Hadir</th>
                    <th rowspan="2" style="width: 55px;">Paraf</th>
                </tr>
                <tr>
                    <th class="th-sub" style="width: 32px;">H</th>
                    <th class="th-sub" style="width: 32px;">S</th>
                    <th class="th-sub" style="width: 32px;">I</th>
                    <th class="th-sub" style="width: 32px;">A</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $datesToRender = ($range === 'weekly')
                        ? collect(range(1, 6))->map(fn($d) => $weekStart->copy()->addDays($d - 1))
                        : collect([$referenceDate]);
                @endphp

                @foreach($datesToRender as $dateItem)
                    @php
                        $curDateStr = $dateItem->format('Y-m-d');
                        $dayName = $dateItem->isoFormat('dddd');
                        $dateFormatted = $dateItem->format('d/m/Y');

                        $dayJournals = $journals->filter(function($j) use ($curDateStr) {
                            $jDate = $j->journal_date instanceof \Carbon\Carbon ? $j->journal_date->format('Y-m-d') : substr((string) $j->journal_date, 0, 10);
                            return $jDate === $curDateStr;
                        })->values();

                        $coveredPeriodNumbers = [];
                    @endphp

                    @if($range === 'weekly' && $dayJournals->isEmpty())
                        {{-- Skip days with 0 activities in weekly mode if preferred, or render notice --}}
                        @continue
                    @endif

                    @foreach($allDayPeriods as $period)
                        @if($period->is_break)
                            <tr class="break-row">
                                <td colspan="12">
                                    ☕ {{ strtoupper($period->name) }} ({{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }})
                                    @if(str_contains(strtolower($period->name), '2') || substr($period->start_time, 0, 2) >= '11')
                                        &middot; Waktu Istirahat, Sholat &amp; Makan Siang
                                    @endif
                                </td>
                            </tr>
                        @else
                            @php
                                $pNum = $period->period_number;
                                if (in_array($pNum, $coveredPeriodNumbers)) {
                                    continue;
                                }

                                $j = $dayJournals->first(function($item) use ($pNum, $period) {
                                    $startNum = $item->startPeriod?->period_number ?? $item->start_period_id;
                                    return $startNum == $pNum || $item->start_period_id == $period->id;
                                });
                            @endphp

                            @if($j)
                                @php
                                    $startNum = $j->startPeriod?->period_number ?? $pNum;
                                    $endNum = $j->endPeriod?->period_number ?? $startNum;
                                    for ($k = min($startNum, $endNum); $k <= max($startNum, $endNum); $k++) {
                                        $coveredPeriodNumbers[] = $k;
                                    }
                                @endphp
                                <tr>
                                    <td style="text-align: center; font-weight: 700;">
                                        <div>{{ $dayName }}</div>
                                        <div style="font-size: 9px; color: #64748b; font-weight: normal;">{{ $dateFormatted }}</div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700;">
                                        {{ $startNum == $endNum ? "Jam {$startNum}" : "Jam {$startNum}-{$endNum}" }}
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-size: 9px;">
                                        {{ substr($j->startPeriod?->start_time ?? $period->start_time, 0, 5) }} - {{ substr($j->endPeriod?->end_time ?? $period->end_time, 0, 5) }}
                                    </td>
                                    <td style="font-weight: 700; color: #0d47a1;">
                                        {{ $j->teachingAssignment?->subject?->name ?? $assignment->subject?->name }}
                                    </td>
                                    <td>
                                        {{ $j->creator?->user?->name ?? $j->creator?->full_name ?? 'Guru' }}
                                    </td>
                                    <td style="line-height: 1.35;">
                                        {{ $j->material }}
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: #059669;">{{ $j->hadir_count }}</td>
                                    <td style="text-align: center; font-weight: 700; color: #d97706;">{{ $j->sakit_count }}</td>
                                    <td style="text-align: center; font-weight: 700; color: #2563eb;">{{ $j->izin_count }}</td>
                                    <td style="text-align: center; font-weight: 700; color: #dc2626;">{{ $j->alpha_count }}</td>
                                    <td>
                                        @php
                                            $absents = $j->attendances->filter(fn($a) => $a->status !== \App\Enums\AttendanceStatus::Present);
                                        @endphp
                                        @if($absents->isNotEmpty())
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                @foreach($absents as $att)
                                                    @php
                                                        $badgeCls = match($att->status) {
                                                            \App\Enums\AttendanceStatus::Sick => 'badge-sakit',
                                                            \App\Enums\AttendanceStatus::Permit => 'badge-izin',
                                                            \App\Enums\AttendanceStatus::Absent => 'badge-alpha',
                                                            default => 'badge-hadir',
                                                        };
                                                        $statusLbl = match($att->status) {
                                                            \App\Enums\AttendanceStatus::Sick => 'S',
                                                            \App\Enums\AttendanceStatus::Permit => 'I',
                                                            \App\Enums\AttendanceStatus::Absent => 'A',
                                                            default => 'H',
                                                        };
                                                    @endphp
                                                    <div style="font-size: 9.5px;">
                                                        <span class="badge-att {{ $badgeCls }}">{{ $statusLbl }}</span>
                                                        <strong>{{ $att->student?->full_name ?? 'Siswa' }}</strong>
                                                        @if($att->note)
                                                            <span style="color: #64748b; font-style: italic;">({{ $att->note }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="color: #059669; font-style: italic; font-size: 9px;">Semua Hadir</span>
                                        @endif

                                        @php
                                            $extraNote = trim(preg_replace('/Hadir:\s*\d+\s*\|\s*Sakit:\s*\d+\s*\|\s*Izin:\s*\d+\s*\|\s*Alpha:\s*\d+/i', '', $j->notes ?? ''));
                                        @endphp
                                        @if($extraNote)
                                            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px; font-style: italic;">
                                                Catatan: {{ $extraNote }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: center; color: #94a3b8; font-size: 9px;">
                                        ✓
                                    </td>
                                </tr>
                            @else
                                {{-- Unfilled Slot --}}
                                <tr class="unfilled-row">
                                    <td style="text-align: center;">
                                        <div>{{ $dayName }}</div>
                                        <div style="font-size: 9px;">{{ $dateFormatted }}</div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">Jam {{ $pNum }}</td>
                                    <td style="text-align: center; font-family: monospace; font-size: 9px;">
                                        {{ substr($period->start_time, 0, 5) }} - {{ substr($period->end_time, 0, 5) }}
                                    </td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td style="font-style: italic;">Belum diisi</td>
                                    <td style="text-align: center;">-</td>
                                    <td style="text-align: center;">-</td>
                                    <td style="text-align: center;">-</td>
                                    <td style="text-align: center;">-</td>
                                    <td>-</td>
                                    <td></td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Kolom Tanda Tangan Formal -->
        <div class="signatures-container">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div>Kepala Sekolah / Waka Kurikulum</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ................................................................ )</div>
                <div class="signature-nip">NIP. ....................................................</div>
            </div>

            <div class="signature-box">
                <div>Karanganyar, {{ now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                <div>Guru Mata Pelajaran</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ Auth::user()->name }}</div>
                <div class="signature-nip">NIP/NUPTK: {{ Auth::user()->teacherProfile?->nip ?? '-' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
