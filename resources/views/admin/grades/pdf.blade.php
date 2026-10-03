<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai Siswa - {{ $gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas' }} - {{ $gradebook->teachingAssignment?->subject?->name ?? 'Mapel' }}</title>
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
            max-width: 1080px;
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
            max-width: 1080px;
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
            flex-shrink: 0;
        }

        .kop-text h2 {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
        }

        .kop-text h1 {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 2px 0;
        }

        .kop-text p {
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
        }

        /* Judul Dokumen & Metadata */
        .doc-title {
            text-align: center;
            margin-bottom: 14px;
        }

        .doc-title h3 {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .doc-meta {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .doc-meta td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .doc-meta .meta-label {
            font-weight: 600;
            color: #475569;
            width: 14%;
        }

        .doc-meta .meta-val {
            font-weight: 700;
            color: #0f172a;
            width: 36%;
        }

        /* Tabel Rekap Nilai */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }

        .grades-table th,
        .grades-table td {
            border: 1px solid #334155;
            padding: 5px 6px;
            text-align: center;
        }

        .grades-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            vertical-align: middle;
        }

        .grades-table td.text-left {
            text-align: left;
        }

        .grades-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .grades-table tfoot td {
            font-weight: 700;
            background-color: #e2e8f0;
        }

        /* Footer & Tanda Tangan */
        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-col {
            width: 250px;
            text-align: center;
            font-size: 11px;
            color: #0f172a;
        }

        .signature-space {
            height: 60px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }

        .signature-nip {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }

        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .paper {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .grades-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grades-table tfoot td {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (Tidak ikut tercetak) -->
    <div class="no-print-toolbar no-print">
        <div style="display:flex; align-items:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span style="font-weight:700; color:#0f172a;">Pratinjau PDF / Cetak Rekap Nilai Siswa</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" onclick="window.close()" class="btn btn-outline">
                Tutup
            </button>
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
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo Sekolah" class="kop-logo" onerror="this.style.display='none'">
            <div class="kop-text">
                <h2>PEMERINTAH PROVINSI JAWA TENGAH &bull; DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
                <h1>{{ $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR' }}</h1>
                <p>
                    {{ $schoolProfile?->address ?? 'Jl. Yos Sudarso, Jayan, Blimbing, Kec. Tasikmadu, Kabupaten Karanganyar, Jawa Tengah 57722' }}<br>
                    Website: {{ $schoolProfile?->website ?? 'smkn2karanganyar.sch.id' }} &bull; Email: {{ $schoolProfile?->email ?? 'info@smkn2karanganyar.sch.id' }} &bull; NPSN: {{ $schoolProfile?->npsn ?? '20312061' }}
                </p>
            </div>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h3>REKAPITULASI BUKU NILAI SISWA</h3>
        </div>

        @php
            $assignment = $gradebook->teachingAssignment;
            $schoolClass = $assignment?->schoolClass;
            $subject = $assignment?->subject;
            $teacher = $assignment?->teacher;
            $semester = $assignment?->semester;
            $academicYear = $semester?->academicYear;
        @endphp

        <!-- Metadata -->
        <table class="doc-meta">
            <tr>
                <td class="meta-label">Rombel / Kelas:</td>
                <td class="meta-val">{{ $schoolClass?->name ?? '-' }} ({{ $schoolClass?->department?->name ?? '-' }})</td>
                <td class="meta-label">Semester / T.A.:</td>
                <td class="meta-val">{{ $semester?->name ?? '-' }} ({{ $academicYear?->name ?? '2026/2027' }})</td>
            </tr>
            <tr>
                <td class="meta-label">Mata Pelajaran:</td>
                <td class="meta-val">{{ $subject?->name ?? '-' }}</td>
                <td class="meta-label">Guru Pengajar:</td>
                <td class="meta-val">{{ $teacher?->full_name ?? '-' }}</td>
            </tr>
        </table>

        <!-- Tabel Rekap Nilai -->
        <table class="grades-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 32px;">No</th>
                    <th rowspan="2" style="width: 90px;">NIS / NISN</th>
                    <th rowspan="2" style="width: 180px; text-align: left; padding-left: 8px;">Nama Peserta Didik</th>
                    @forelse($columns as $col)
                        <th style="min-width: 48px;">{{ $col->category?->name ?? ($col->column_type->value === 'SUMMARY' ? 'Nilai Akhir' : 'Penilaian') }}</th>
                    @empty
                        <th style="min-width: 80px;">Nilai</th>
                    @endforelse
                </tr>
                <tr>
                    @forelse($columns as $col)
                        <th style="font-size: 9px; padding: 3px 4px;" title="{{ $col->name }}">{{ $col->code ?: $col->name }}</th>
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
                        <td class="text-left" style="padding-left: 8px; font-weight: 600;">{{ $st->student?->full_name ?? '-' }}</td>
                        @forelse($columns as $col)
                            @php
                                $val = $scoresMatrix[$st->student_id][$col->id] ?? null;
                            @endphp
                            <td>
                                @if($val !== null && is_numeric($val))
                                    {{ (float) $val }}
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                        @empty
                            <td><span style="color: #94a3b8;">-</span></td>
                        @endforelse
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + max(1, $columns->count()) }}" style="padding: 16px; color: #64748b;">
                            Belum ada data siswa terdaftar pada buku nilai ini.
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

        <!-- Kolom Tanda Tangan -->
        <div class="signature-block">
            <div class="signature-col">
                <p>Mengetahui,</p>
                <p>Kepala SMK Negeri 2 Karanganyar</p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ $schoolProfile?->headmaster_name ?? 'Drs. Sukadi, M.Pd.' }}</p>
                <p class="signature-nip">NIP. {{ $schoolProfile?->headmaster_nip ?? '19680512 199403 1 005' }}</p>
            </div>

            <div class="signature-col">
                <p>Karanganyar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Guru Mata Pelajaran,</p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ $teacher?->full_name ?? 'Guru Pengajar' }}</p>
                <p class="signature-nip">NIP. {{ $teacher?->nip ?? '-' }}</p>
            </div>
        </div>
    </div>

    <script>
        // Dialog print browser otomatis terbuka setelah halaman termuat
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
