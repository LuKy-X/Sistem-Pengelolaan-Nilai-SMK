@extends('layouts.teacher')

@section('title', 'Detail Tugas — ' . $assessment->title)

@section('content')
<div class="space-y-6">

    <!-- Stepper Navigation -->
    <div class="flex items-center gap-2 text-xs md:text-sm text-bluedark/60">
        <a href="{{ route('teacher.assessments.index') }}" class="font-semibold text-blueprim hover:underline">
            Daftar Tugas
        </a>
        <span>/</span>
        <span class="font-semibold text-bluedark">
            {{ $assessment->title }}
        </span>
    </div>

    <!-- Assessment Overview Card -->
    <div class="panel p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E3F2FD]">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="badge badge-blue">{{ $assessment->type->name }}</span>
                    @if($assessment->status->value === 'PUBLISHED')
                        <span class="badge badge-green">Aktif</span>
                    @else
                        <span class="badge badge-gray">Selesai</span>
                    @endif
                </div>
                <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">
                    {{ $assessment->title }}
                </h1>
                <p class="text-xs text-bluedark/60 mt-0.5">
                    Kelas: <strong>{{ $assessment->teachingAssignment?->schoolClass?->name }}</strong> • 
                    Mata Pelajaran: <strong>{{ $assessment->teachingAssignment?->subject?->name }}</strong>
                </p>
            </div>
            
            <div class="text-right">
                <span class="text-xs text-slate-500 block">Tenggat Waktu:</span>
                <span class="font-bold text-sm text-bluedark">
                    {{ $assessment->due_at ? $assessment->due_at->translatedFormat('l, d F Y - H:i') : 'Tidak ditentukan' }}
                </span>
            </div>
        </div>

        @if($assessment->description)
            <div class="mt-4 p-4 rounded-2xl bg-[#FAFDFF] border border-[#E3F2FD] text-xs text-ink/80 leading-relaxed">
                <h4 class="font-bold text-bluedark mb-1">Petunjuk Tugas:</h4>
                {!! nl2br(e($assessment->description)) !!}
            </div>
        @endif

        <!-- Quick Info Badges -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 text-xs">
            <div class="p-3 rounded-xl bg-[#E3F2FD]/50">
                <span class="text-slate-500 block">Kolom Buku Nilai</span>
                <span class="font-bold text-bluedark">{{ $assessment->gradebookColumn?->name ?? 'Tugas Mandiri' }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#E3F2FD]/50">
                <span class="text-slate-500 block">Skor Maksimal</span>
                <span class="font-bold text-bluedark">100 Poin</span>
            </div>
            <div class="p-3 rounded-xl bg-[#E3F2FD]/50">
                <span class="text-slate-500 block">Aturan Terlambat</span>
                <span class="font-bold text-bluedark">
                    {{ $assessment->latePolicy && $assessment->latePolicy->enabled ? 'Potongan ' . $assessment->latePolicy->reduction_value . ' Poin' : 'Tanpa Potongan' }}
                </span>
            </div>
            <div class="p-3 rounded-xl bg-[#E3F2FD]/50">
                <span class="text-slate-500 block">Rubrik Terlampir</span>
                <span class="font-bold text-bluedark">
                    {{ $assessment->rubric ? $assessment->rubric->title : 'Tidak Menggunakan Rubrik' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Student Submissions Table Card -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-[16px]">Pengumpulan &amp; Penilaian Siswa</h2>
                <p class="text-xs text-bluedark/50">Daftar siswa dan status hasil pengerjaan tugas</p>
            </div>
            <span class="text-xs font-semibold text-blueprim">
                {{ $assessment->submissions->where('status', 'GRADED')->count() }} dari {{ $assessment->submissions->count() }} dinilai
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E3F2FD]">
            <table class="tbl text-xs">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Waktu Pengumpulan</th>
                        <th class="text-center">Keterlambatan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3F2FD]">
                    @forelse($assessment->submissions as $index => $sub)
                        <tr class="hover:bg-[#F5FAFF]">
                            <td class="text-center text-xs font-semibold text-slate-500">{{ $index + 1 }}</td>
                            <td class="font-semibold text-bluedark">{{ $sub->student?->full_name ?? 'Siswa' }}</td>
                            <td class="text-slate-500">{{ $sub->student?->nis ?? '-' }}</td>
                            <td class="text-slate-600">
                                {{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($sub->is_late)
                                    <span class="badge badge-red">Terlambat</span>
                                @else
                                    <span class="badge badge-green">Tepat Waktu</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($sub->status === 'GRADED')
                                    <span class="badge badge-green">Dinilai</span>
                                @elseif($sub->status === 'SUBMITTED')
                                    <span class="badge badge-blue">Terkirim</span>
                                @else
                                    <span class="badge badge-gray">Belum Kumpul</span>
                                @endif
                            </td>
                            <td class="text-center font-mono font-bold text-bluedark">
                                {{ $sub->score !== null ? number_format($sub->score, 1) : '-' }}
                            </td>
                            <td class="text-center">
                                <button 
                                    type="button" 
                                    onclick="openGradingModal('{{ $sub->id }}', '{{ addslashes($sub->student?->full_name ?? '') }}', '{{ $sub->score ?? '' }}', '{{ addslashes($sub->feedback ?? '') }}')"
                                    class="btn btn-primary btn-sm py-1 px-3 text-xs"
                                >
                                    {{ $sub->score !== null ? 'Koreksi' : 'Beri Nilai' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-xs text-bluedark/50">
                                Belum ada data pengumpulan tugas dari siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Grading Modal -->
<div id="gradingModal" class="fixed inset-0 z-50 bg-ink/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
        <div class="form-block__header flex items-center justify-between">
            <div>
                <h3>Penilaian Pengumpulan Tugas</h3>
                <p id="modalStudentName">Nama Siswa</p>
            </div>
            <button type="button" onclick="closeGradingModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form id="gradingForm" method="POST" action="" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="f-label">Nilai / Skor (Maks. 100) <span class="text-red-500">*</span></label>
                <input type="number" id="gradeScoreInput" name="score" step="0.1" min="0" max="100" required class="f-input text-lg font-bold font-mono">
            </div>

            <div>
                <label class="f-label">Catatan / Feedback Guru</label>
                <textarea id="gradeFeedbackInput" name="feedback" rows="3" placeholder="Tuliskan catatan perbaikan atau apresiasi untuk siswa..." class="f-textarea"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeGradingModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">Simpan Nilai</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openGradingModal(submissionId, studentName, score, feedback) {
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('gradeScoreInput').value = score;
        document.getElementById('gradeFeedbackInput').value = feedback;
        
        const form = document.getElementById('gradingForm');
        form.action = "{{ url('guru/assessments') }}/{{ $assessment->id }}/submissions/" + submissionId + "/grade";

        document.getElementById('gradingModal').classList.remove('hidden');
    }

    function closeGradingModal() {
        document.getElementById('gradingModal').classList.add('hidden');
    }
</script>
@endsection
