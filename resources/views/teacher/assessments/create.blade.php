@extends('layouts.teacher')

@section('title', 'Manajemen Tugas — Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">Manajemen Tugas</h1>
        <p class="text-sm text-bluedark/60 mt-0.5">Kelola Tugas Siswa</p>
    </div>

    <!-- Stepper Navigation matching user screenshot -->
    <div class="flex items-center overflow-hidden rounded-xl border border-[#90CAF9] bg-[#E3F2FD] text-xs font-semibold shadow-xs max-w-2xl">
        <a href="{{ route('teacher.gradebooks.index') }}" class="flex-1 py-2.5 px-4 text-center text-bluedark hover:bg-white/40 transition-colors">
            Daftar Kelas
        </a>
        <div class="w-4 h-full border-r border-[#90CAF9] transform skew-x-[-20deg]"></div>
        <a href="{{ route('teacher.gradebooks.show', $previewGradebook->id ?? 1) }}" class="flex-1 py-2.5 px-4 text-center text-bluedark hover:bg-white/40 transition-colors">
            Buku Nilai
        </a>
        <div class="w-4 h-full border-r border-[#90CAF9] transform skew-x-[-20deg]"></div>
        <div class="flex-1 py-2.5 px-4 text-center bg-blueprim text-white font-bold shadow-inner">
            Tugas/Remidi
        </div>
    </div>

    <!-- Class Subtitle Breadcrumb -->
    <div class="text-xs md:text-sm text-bluedark/70">
        <a href="{{ route('teacher.gradebooks.index') }}" class="text-blueprim font-semibold hover:underline">
            Daftar Kelas
        </a>
        <span>/</span>
        <span class="font-semibold text-bluedark">
            {{ $selectedAssignment?->schoolClass?->name ?? 'XII RA' }} - {{ $selectedAssignment?->semester?->academicYear?->name ?? '2026/2027' }} ({{ $selectedAssignment?->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }})
        </span>
    </div>

    <!-- Top Card: Daftar Nilai Preview Spreadsheet -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-base">
                    Daftar Nilai — Kelas {{ $selectedAssignment?->schoolClass?->name ?? 'XII RA' }}
                </h2>
                <p class="text-xs text-bluedark/50">
                    {{ $selectedAssignment?->subject?->name ?? 'Matematika' }} - Gasal 2026/2027
                </p>
            </div>
            @if($previewGradebook)
                <a href="{{ route('teacher.gradebooks.export', $previewGradebook) }}" class="btn btn-outline btn-sm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Export</span>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E3F2FD]">
            <table class="tbl text-xs">
                <thead>
                    <tr>
                        <th class="w-12 text-center" rowspan="2">No</th>
                        <th class="min-w-[170px]" rowspan="2">Nama Siswa</th>
                        <th colspan="4" class="text-center">Ulangan Harian</th>
                        <th class="text-center" rowspan="2">RUH</th>
                        <th colspan="3" class="text-center">Tugas</th>
                        <th class="text-center" rowspan="2">RTG</th>
                        <th colspan="2" class="text-center">Nilai</th>
                        <th class="text-center" rowspan="2">NSB</th>
                    </tr>
                    <tr>
                        <th class="th-sub text-center w-12">UH 1</th>
                        <th class="th-sub text-center w-12">R1</th>
                        <th class="th-sub text-center w-12">UH 2</th>
                        <th class="th-sub text-center w-12">R2</th>
                        <th class="th-sub text-center w-12">T1</th>
                        <th class="th-sub text-center w-12">T2</th>
                        <th class="th-sub text-center w-12">T3</th>
                        <th class="th-sub text-center w-14">MID</th>
                        <th class="th-sub text-center w-14">SEM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3F2FD]">
                    @forelse($previewStudents as $idx => $st)
                        <tr class="hover:bg-[#F5FAFF]">
                            <td class="text-center text-xs font-semibold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="font-medium text-bluedark px-3">{{ $st->student?->full_name ?? 'Siswa' }}</td>
                            <td class="text-center font-mono text-slate-600">85</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono text-slate-600">88</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono font-bold text-blueprim">86.5</td>
                            <td class="text-center font-mono text-slate-600">90</td>
                            <td class="text-center font-mono text-slate-600">85</td>
                            <td class="text-center font-mono text-slate-600">92</td>
                            <td class="text-center font-mono font-bold text-blueprim">89.0</td>
                            <td class="text-center font-mono text-slate-600">84</td>
                            <td class="text-center font-mono text-slate-600">88</td>
                            <td class="text-center font-mono font-bold text-emerald-600">87.5</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-xs font-semibold text-slate-500">1</td>
                            <td class="font-medium text-bluedark px-3">Ahyar Rosadi</td>
                            <td class="text-center font-mono text-slate-600">85</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono text-slate-600">88</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono font-bold text-blueprim">86.5</td>
                            <td class="text-center font-mono text-slate-600">90</td>
                            <td class="text-center font-mono text-slate-600">85</td>
                            <td class="text-center font-mono text-slate-600">92</td>
                            <td class="text-center font-mono font-bold text-blueprim">89.0</td>
                            <td class="text-center font-mono text-slate-600">84</td>
                            <td class="text-center font-mono text-slate-600">88</td>
                            <td class="text-center font-mono font-bold text-emerald-600">87.5</td>
                        </tr>
                        <tr>
                            <td class="text-center text-xs font-semibold text-slate-500">2</td>
                            <td class="font-medium text-bluedark px-3">Amalia Lestari</td>
                            <td class="text-center font-mono text-slate-600">90</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono text-slate-600">92</td>
                            <td class="text-center font-mono text-slate-400">-</td>
                            <td class="text-center font-mono font-bold text-blueprim">91.0</td>
                            <td class="text-center font-mono text-slate-600">95</td>
                            <td class="text-center font-mono text-slate-600">90</td>
                            <td class="text-center font-mono text-slate-600">94</td>
                            <td class="text-center font-mono font-bold text-blueprim">93.0</td>
                            <td class="text-center font-mono text-slate-600">90</td>
                            <td class="text-center font-mono text-slate-600">92</td>
                            <td class="text-center font-mono font-bold text-emerald-600">92.0</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lower Card: Form Manajemen Tugas/Mandiri matching Mockup -->
    <div class="form-block">
        <div class="form-block__header">
            <h3>Manajemen Tugas/Mandiri</h3>
            <p>Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai</p>
        </div>

        <form action="{{ route('teacher.assessments.store') }}" method="POST" class="form-block__body space-y-4">
            @csrf

            <!-- Hidden / Target Teaching Assignment -->
            <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment?->id }}">

            <!-- Row 1: Tipe & Kolom pada Buku Nilai -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" required class="f-select">
                        <option value="TUGAS">Tugas Mandiri</option>
                        <option value="UH">Ulangan Harian</option>
                        <option value="REMIDI">Remidi</option>
                        <option value="PRAKTIK">Praktik Kejuruan</option>
                        <option value="PROJEK">Projek Siswa</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Kolom pada Buku Nilai</label>
                    <select name="gradebook_column_id" class="f-select">
                        <option value="">-- Pilih Kolom Nilai (Opsional) --</option>
                        @if($previewGradebook)
                            @foreach($previewColumns as $col)
                                <option value="{{ $col->id }}">{{ $col->name }} ({{ $col->code }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Row 2: Judul Tugas -->
            <div>
                <label class="f-label">Judul Tugas <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    placeholder="cth. Tugas Persamaan Linear" 
                    required 
                    class="f-input"
                >
            </div>

            <!-- Row 3: Deskripsi Tugas -->
            <div>
                <label class="f-label">Deskripsi Tugas</label>
                <textarea 
                    name="description" 
                    rows="3" 
                    placeholder="Tuliskan instruksi atau keterangan tambahan" 
                    class="f-textarea"
                ></textarea>
            </div>

            <!-- Row 4: Batas Pengumpulan & Bobot Maksimum Nilai -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="f-label">Batas Pengumpulan (Deadline)</label>
                    <input 
                        type="datetime-local" 
                        name="due_at" 
                        class="f-input"
                    >
                </div>
                <div>
                    <label class="f-label">Bobot Maksimum Nilai <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="max_score" 
                        value="100" 
                        min="1" 
                        max="100" 
                        required 
                        class="f-input"
                    >
                </div>
            </div>

            <!-- Row 5: Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat + Toggle Pengaturan Default -->
            <div class="grid md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="f-label">Pengurangan Batas Maksimal Nilai Karena Terlambat</label>
                    <div class="grid grid-cols-[1fr_auto_1fr] gap-2 items-center">
                        <input 
                            type="number" 
                            name="reduction_value" 
                            value="5" 
                            min="0" 
                            max="100" 
                            class="f-input"
                        >
                        <span class="text-bluedark/50 text-sm font-semibold">/</span>
                        <select name="interval" class="f-select">
                            <option value="MINGGU">Minggu</option>
                            <option value="HARI">Hari</option>
                        </select>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#E3F2FD]/40 border border-[#E3F2FD] flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-bluedark">Gunakan Pengaturan Default</div>
                        <div class="text-[11px] text-bluedark/60">Terapkan aturan keterlambatan otomatis</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enable_late_policy" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blueprim"></div>
                    </label>
                </div>
            </div>

            <!-- Row 6: Gunakan Rubrik Penilaian Toggle & Selector -->
            <div class="p-4 rounded-2xl bg-[#FAFDFF] border border-[#E3F2FD] space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-bluedark">Gunakan Rubrik Penilaian</div>
                        <div class="text-[11px] text-bluedark/60">Rubrik membantu menstandarisasi cara penilaian tugas/remidi secara obyektif</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="useRubricToggle" name="use_rubric" value="1" onchange="toggleRubricDropdown(this)" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blueprim"></div>
                    </label>
                </div>

                <div id="rubricSelectWrap" class="hidden pt-2 border-t border-[#E3F2FD]">
                    <label class="f-label">Pilih Rubrik Penilaian:</label>
                    <select name="rubric_id" class="f-select">
                        <option value="">-- Pilih Rubrik --</option>
                        @foreach($rubrics as $rubric)
                            <option value="{{ $rubric->id }}">{{ $rubric->title }} ({{ $rubric->criteria->count() }} Kriteria)</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-right">
                        <a href="{{ route('teacher.rubrics.create') }}" target="_blank" class="text-[11px] text-blueprim font-semibold hover:underline">
                            + Buat Rubrik Baru di Tab Baru
                        </a>
                    </div>
                </div>
            </div>

            <!-- Action Buttons: Reset & Simpan Tugas -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="reset" class="btn btn-outline btn-sm">
                    Reset
                </button>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>+ Simpan Tugas</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function toggleRubricDropdown(checkbox) {
        const wrap = document.getElementById('rubricSelectWrap');
        if (checkbox.checked) {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }
</script>
@endsection
