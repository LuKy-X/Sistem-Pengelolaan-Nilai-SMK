@extends('layouts.teacher')

@section('title', 'Absensi Kelas & Jurnal — Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Absensi Kelas</h1>
        <p class="text-sm text-bluedark/60 mt-1">Kelola Absensi Kelas &amp; Jurnal Mengajar</p>
    </div>

    @if(! $selectedAssignment)
        <!-- PANEL 1: Pilih Kelas Grid matching mockup media_1790213026706.html -->
        <div class="panel p-5">
            <div class="mb-4">
                <h2 class="font-heading font-semibold text-bluedark text-[15px]">Absensi Kelas</h2>
                <p class="text-xs text-bluedark/50 mt-0.5">Pilih kelas untuk mengelola journal kelas</p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @forelse($assignments as $assignment)
                    <a href="{{ route('teacher.journals.index', ['assignment_id' => $assignment->id]) }}" class="kelas-card group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                        <div class="font-heading font-bold text-bluedark text-sm">
                            {{ $assignment->schoolClass?->name ?? 'XII RA' }}
                        </div>
                        <div class="text-xs text-bluedark/50">
                            {{ $assignment->subject?->name ?? 'Rekayasa Perangkat Lunak' }}
                        </div>
                        <div class="kelas-card__meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 
                                {{ $assignment->schoolClass?->students_count ?? 36 }} Siswa
                            </span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> 
                                {{ $assignment->weekly_hours }} Jam / Minggu
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-blue">Gasal</span>
                            <span class="badge badge-gray">2026/2027</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-bluedark/50">
                        Belum ada penugasan mengajar yang terdaftar.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- PANEL 2: Detail Journal & Form Absensi matching mockup media_1790213026706.html -->
        <div>
            <p class="text-sm text-bluedark/60 mb-4">
                <a href="{{ route('teacher.journals.index') }}" class="font-semibold text-blueprim hover:underline">Absensi Kelas</a> / 
                <span class="font-semibold text-bluedark">{{ $selectedAssignment->schoolClass?->name }}</span>
            </p>

            <!-- Table Riwayat Journal -->
            <div class="panel p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-heading font-semibold text-bluedark text-[15px]">
                            Journal — Kelas {{ $selectedAssignment->schoolClass?->name }}
                        </h2>
                        <p class="text-xs text-bluedark/50">
                            {{ $selectedAssignment->subject?->name }} - Gasal 2026/2027
                        </p>
                    </div>
                    <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> 
                        Export
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-[#E3F2FD]">
                    <table class="tbl text-xs">
                        <thead>
                            <tr>
                                <th rowspan="2">Hari / Tanggal</th>
                                <th rowspan="2" class="text-center">Jam ke-</th>
                                <th rowspan="2">Mata Pelajaran</th>
                                <th rowspan="2">Nama Guru</th>
                                <th colspan="4" class="text-center">Jumlah Siswa</th>
                                <th rowspan="2">Materi &amp; Keterangan</th>
                            </tr>
                            <tr>
                                <th class="th-sub text-center w-12">Hadir</th>
                                <th class="th-sub text-center w-10">S</th>
                                <th class="th-sub text-center w-10">I</th>
                                <th class="th-sub text-center w-10">A</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3F2FD]">
                            @forelse($journals as $j)
                                <tr class="hover:bg-[#F5FAFF]">
                                    <td class="whitespace-nowrap font-medium text-bluedark">
                                        {{ $j->journal_date->translatedFormat('l, d/m/Y') }}
                                    </td>
                                    <td class="text-center font-mono">
                                        {{ $j->startPeriod?->period_number ?? '1' }} - {{ $j->endPeriod?->period_number ?? '2' }}
                                    </td>
                                    <td>{{ $selectedAssignment->subject?->name }}</td>
                                    <td>{{ $j->creator?->full_name ?? auth()->user()->name }}</td>
                                    <td class="text-center font-mono text-emerald-600 font-bold">
                                        {{ $selectedAssignment->schoolClass?->students_count - $j->attendances->count() }}
                                    </td>
                                    <td class="text-center font-mono text-amber-600">
                                        {{ $j->attendances->where('status.value', 'SICK')->count() }}
                                    </td>
                                    <td class="text-center font-mono text-blue-600">
                                        {{ $j->attendances->where('status.value', 'PERMITTED')->count() }}
                                    </td>
                                    <td class="text-center font-mono text-red-600">
                                        {{ $j->attendances->where('status.value', 'ABSENT')->count() }}
                                    </td>
                                    <td>
                                        <div class="font-semibold text-bluedark">{{ $j->material }}</div>
                                        @if($j->notes)
                                            <div class="text-[11px] text-slate-500 whitespace-pre-line">{{ $j->notes }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-10 text-center text-xs text-bluedark/50">
                                        Belum ada riwayat jurnal kelas untuk rombel ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Manajemen Absensi matching mockup media_1790213026706.html -->
            <div class="form-block">
                <div class="form-block__header">
                    <h3>Manajemen Absensi</h3>
                    <p>Isi form berikut untuk memanajemen kolom absensi pada journal kelas</p>
                </div>

                <form action="{{ route('teacher.journals.store') }}" method="POST" class="form-block__body space-y-4">
                    @csrf
                    <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment->id }}">

                    <!-- Row 1: Jam Pelajaran ke- sd ke- & Tanggal & Mata Pelajaran -->
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="f-label">Jam Pelajaran ke- <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-[1fr_auto_1fr] gap-2 items-center">
                                <select name="start_period_id" class="f-select" required>
                                    @foreach($lessonPeriods as $lp)
                                        <option value="{{ $lp->id }}" {{ $lp->period_number == 1 ? 'selected' : '' }}>
                                            Jam Ke-{{ $lp->period_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-bluedark/50 text-xs font-semibold">sd</span>
                                <select name="end_period_id" class="f-select" required>
                                    @foreach($lessonPeriods as $lp)
                                        <option value="{{ $lp->id }}" {{ $lp->period_number == 2 ? 'selected' : '' }}>
                                            Jam Ke-{{ $lp->period_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="f-label">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                            <input type="date" name="journal_date" value="{{ date('Y-m-d') }}" required class="f-input">
                        </div>

                        <div>
                            <label class="f-label">Mata Pelajaran</label>
                            <input type="text" readonly value="{{ $selectedAssignment->subject?->name }}" class="f-input bg-slate-50 text-slate-600 cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Row 2: Nama Guru & Materi -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Nama Guru</label>
                            <input type="text" readonly value="{{ auth()->user()->teacherProfile?->full_name ?? auth()->user()->name }}" class="f-input bg-slate-50 text-slate-600 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="f-label">Materi Pembelajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="material" placeholder="cth. Pertidaksamaan Linear atau Desain MVC" required class="f-input">
                        </div>
                    </div>

                    <!-- Row 3: Hadir, Sakit, Izin, Alpha -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="f-label">Hadir</label>
                            <input type="number" name="hadir_count" value="{{ $selectedAssignment->schoolClass?->students_count ?? 36 }}" min="0" class="f-input font-mono font-bold text-emerald-600">
                        </div>
                        <div>
                            <label class="f-label">Sakit</label>
                            <input type="number" name="sakit_count" value="0" min="0" class="f-input font-mono font-bold text-amber-600">
                        </div>
                        <div>
                            <label class="f-label">Izin</label>
                            <input type="number" name="izin_count" value="0" min="0" class="f-input font-mono font-bold text-blue-600">
                        </div>
                        <div>
                            <label class="f-label">Alpha</label>
                            <input type="number" name="alpha_count" value="0" min="0" class="f-input font-mono font-bold text-red-600">
                        </div>
                    </div>

                    <!-- Row 4: Catatan Keterangan Tambahan -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="f-label mb-0">Catatan Presensi &amp; Siswa Tidak Hadir</label>
                            <button type="button" onclick="addAbsenceRow()" class="btn btn-outline btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                                + Tambah Siswa
                            </button>
                        </div>

                        <!-- Dynamic Absence Rows -->
                        <div id="absenceContainer" class="space-y-2 mb-3"></div>

                        <textarea name="notes" rows="2" placeholder="Catatan kelas umum, surat dokter / izin, kejadian khusus..." class="f-textarea"></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="reset" class="btn btn-outline btn-sm">Reset</button>
                        <button type="submit" class="btn btn-primary btn-sm shadow-md">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                            Simpan Journal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let absenceIdx = 0;
            const studentsList = [
                @foreach($enrolledStudents as $st)
                    { id: {{ $st->id }}, name: "{{ addslashes($st->full_name) }} ({{ $st->nis }})" },
                @endforeach
            ];

            function addAbsenceRow() {
                const container = document.getElementById('absenceContainer');
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 md:grid-cols-3 gap-2 p-2.5 rounded-xl bg-[#FAFDFF] border border-[#E3F2FD] items-center';

                let studentOptions = '<option value="">-- Pilih Siswa --</option>';
                studentsList.forEach(s => {
                    studentOptions += `<option value="${s.id}">${s.name}</option>`;
                });

                row.innerHTML = `
                    <select name="absences[${absenceIdx}][student_id]" class="f-select text-xs" required>
                        ${studentOptions}
                    </select>
                    <select name="absences[${absenceIdx}][status]" class="f-select text-xs" required>
                        <option value="SAKIT">Sakit (Surat Dokter)</option>
                        <option value="IZIN">Izin (Surat Wali)</option>
                        <option value="ALPHA">Alpha (Tanpa Keterangan)</option>
                    </select>
                    <div class="flex items-center gap-2">
                        <input type="text" name="absences[${absenceIdx}][note]" placeholder="Bukti / Catatan..." class="f-input text-xs flex-1">
                        <button type="button" onclick="this.closest('.grid').remove()" class="text-red-500 hover:text-red-700 text-base font-bold p-1">&times;</button>
                    </div>
                `;
                container.appendChild(row);
                absenceIdx++;
            }
        </script>
    @endif

</div>
@endsection
