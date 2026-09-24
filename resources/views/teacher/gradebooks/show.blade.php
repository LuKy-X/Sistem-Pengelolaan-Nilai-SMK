@extends('layouts.teacher')

@section('title', 'Buku Nilai — ' . ($gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas'))

@section('content')
<div class="space-y-6">

    <!-- Stepper Navigation -->
    <div class="flex items-center gap-2 text-xs md:text-sm text-bluedark/60">
        <a href="{{ route('teacher.gradebooks.index') }}" class="font-semibold text-blueprim hover:underline">
            Daftar Kelas
        </a>
        <span>/</span>
        <span class="font-semibold text-bluedark">
            {{ $gradebook->teachingAssignment?->schoolClass?->name }} ({{ $gradebook->teachingAssignment?->subject?->name }})
        </span>
    </div>

    <!-- Main Spreadsheet Panel -->
    <div class="panel p-5">
        
        <!-- Header & Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-[#E3F2FD]">
            <div>
                <h1 class="font-heading text-lg md:text-xl font-bold text-bluedark">
                    Daftar Nilai — Kelas {{ $gradebook->teachingAssignment?->schoolClass?->name }}
                </h1>
                <p class="text-xs text-bluedark/60 mt-0.5">
                    {{ $gradebook->teachingAssignment?->subject?->name }} • {{ $gradebook->teachingAssignment?->semester?->academicYear?->name ?? '2026/2027' }} ({{ $gradebook->teachingAssignment?->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }})
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" onclick="openColumnModal()" class="btn btn-outline btn-sm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>+ Tambah Kolom</span>
                </button>
                <a href="{{ route('teacher.gradebooks.export', $gradebook) }}" class="btn btn-outline btn-sm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Gradebook Scores Form -->
        <form action="{{ route('teacher.gradebooks.scores.store', $gradebook) }}" method="POST" id="gradebookScoresForm">
            @csrf

            <!-- Interactive Spreadsheet Container -->
            <div class="overflow-x-auto rounded-xl border border-[#E3F2FD] shadow-xs max-h-[620px] relative">
                <table class="tbl text-xs">
                    <thead class="sticky top-0 z-20">
                        <tr>
                            <th class="w-12 text-center" rowspan="2">No</th>
                            <th class="min-w-[180px] text-left" rowspan="2">Nama Siswa</th>
                            
                            @if($categories->isNotEmpty())
                                @foreach($categories as $cat)
                                    <th colspan="{{ max(1, $cat->columns->count()) }}" class="text-center bg-[#0D47A1]">
                                        {{ $cat->name }}
                                    </th>
                                @endforeach
                            @else
                                <th colspan="{{ max(1, $columns->count()) }}" class="text-center bg-[#0D47A1]">
                                    Komponen Nilai
                                </th>
                            @endif
                        </tr>
                        <tr>
                            @forelse($columns as $col)
                                <th class="th-sub text-center min-w-[70px] max-w-[90px]" title="{{ $col->name }} (Bobot: {{ $col->weight }}%)">
                                    {{ $col->code }}
                                    @if($col->column_type->value === 'SUMMARY')
                                        <span class="text-[10px] text-cyan-200 block font-normal">(Avg)</span>
                                    @endif
                                </th>
                            @empty
                                <th class="th-sub text-center">Belum ada kolom</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3F2FD]">
                        @forelse($students as $index => $item)
                            <tr class="hover:bg-[#F5FAFF] transition-colors">
                                <td class="text-center font-semibold text-slate-500 bg-slate-50/50">
                                    {{ $index + 1 }}
                                </td>
                                <td class="font-medium text-bluedark whitespace-nowrap px-3">
                                    <div class="font-semibold">{{ $item->student?->full_name ?? 'Siswa' }}</div>
                                    <div class="text-[10px] text-slate-400">NIS: {{ $item->student?->nis ?? '-' }}</div>
                                </td>

                                @foreach($columns as $col)
                                    @php
                                        $val = $scoresMatrix[$item->student_id][$col->id] ?? '';
                                        $isSummary = $col->column_type->value === 'SUMMARY';
                                    @endphp
                                    <td class="p-1 text-center {{ $isSummary ? 'bg-blue-50/60 font-bold text-blueprim' : '' }}">
                                        @if($isSummary)
                                            <span class="inline-block py-1 text-center font-mono">
                                                {{ $val !== '' ? number_format($val, 1) : '-' }}
                                            </span>
                                        @else
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                max="{{ $col->max_score }}"
                                                name="scores[{{ $item->student_id }}][{{ $col->id }}]" 
                                                value="{{ $val !== '' ? $val : '' }}"
                                                placeholder="-"
                                                class="w-full text-center py-1.5 px-1 rounded-lg border border-transparent hover:border-blueprim/40 focus:border-blueprim focus:bg-white bg-transparent font-mono text-xs outline-none transition-colors"
                                            >
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + max(1, $columns->count()) }}" class="py-12 text-center text-xs text-bluedark/50">
                                    Belum ada siswa yang terdaftar pada rombel buku nilai ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- Column Averages Footer -->
                    @if($students->isNotEmpty() && $columns->isNotEmpty())
                        <tfoot class="sticky bottom-0 bg-slate-100/95 backdrop-blur-md z-10 border-t-2 border-[#E3F2FD]">
                            <tr class="font-bold text-bluedark">
                                <td colspan="2" class="px-3 py-2 text-right uppercase text-[11px] tracking-wider">
                                    Rata-rata Kelas:
                                </td>
                                @foreach($columns as $col)
                                    <td class="text-center py-2 px-1 text-xs font-mono text-blueprim">
                                        {{ $columnAverages[$col->id] ?? '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Submit Buttons Bar -->
            @if($students->isNotEmpty() && $columns->isNotEmpty())
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-[#E3F2FD]">
                    <div class="text-xs text-slate-500">
                        * Ubah angka pada kolom input dan klik <strong>Simpan Nilai</strong> untuk memperbarui database.
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="reset" class="btn btn-outline btn-sm">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm shadow-md">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan Nilai</span>
                        </button>
                    </div>
                </div>
            @endif
        </form>

    </div>

</div>

<!-- Modal: Tambah Kolom Nilai Baru -->
<div id="columnModal" class="fixed inset-0 z-50 bg-ink/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
        <div class="form-block__header flex items-center justify-between">
            <div>
                <h3>Tambah Kolom Nilai</h3>
                <p>Tambahkan kolom asesmen dinamis ke dalam buku nilai</p>
            </div>
            <button type="button" onclick="closeColumnModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form action="{{ route('teacher.gradebooks.columns.store', $gradebook) }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="f-label">Nama Kolom <span class="text-red-500">*</span></label>
                <input type="text" name="name" placeholder="cth. Ulangan Harian 3 atau Portofolio 2" required class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Kode Singkat <span class="text-red-500">*</span></label>
                    <input type="text" name="code" placeholder="cth. UH3 / T4" required maxlength="15" class="f-input uppercase">
                </div>
                <div>
                    <label class="f-label">Kategori Komponen</label>
                    <select name="category_id" class="f-select">
                        <option value="">-- Tanpa Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="f-label">Tipe Kolom</label>
                    <select name="column_type" class="f-select">
                        <option value="SCORE">Nilai Input</option>
                        <option value="SUMMARY">Rata-rata</option>
                        <option value="MANUAL">Manual</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Skor Maksimal</label>
                    <input type="number" name="max_score" value="100" min="1" max="100" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Bobot (%)</label>
                    <input type="number" name="weight" value="10" min="0" max="100" required class="f-input">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeColumnModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">+ Simpan Kolom</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openColumnModal() {
        document.getElementById('columnModal').classList.remove('hidden');
    }

    function closeColumnModal() {
        document.getElementById('columnModal').classList.add('hidden');
    }
</script>
@endsection
