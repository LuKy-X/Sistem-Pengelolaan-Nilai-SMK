@extends('layouts.teacher')

@section('title', 'Detail Tugas — ' . $assessment->title)

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Detail Tugas &amp; Evaluasi</h1>
    <p class="text-sm text-bluedark/60 mt-1">Pantau dan Koreksi Pengumpulan Jawaban Siswa</p>
  </div>

  <div class="arrow-tabs" id="tugasTabs">
    <a href="{{ route('teacher.gradebooks.index') }}"><span class="step-num">1</span>Daftar Kelas</a>
    <a href="{{ route('teacher.gradebooks.index') }}"><span class="step-num">2</span>Buku Nilai</a>
    <a href="{{ route('teacher.assessments.index') }}" class="active"><span class="step-num">3</span>Tugas/Remidi</a>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.assessments.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Tugas</a> / 
    <span class="font-semibold text-bluedark">{{ $assessment->title }}</span>
  </p>

  <!-- Assessment Overview Card -->
  <div class="panel p-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-bluelight">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <span class="badge badge-blue">{{ $assessment->type->name }}</span>
          @if($assessment->status->value === 'PUBLISHED')
            <span class="badge badge-green">Aktif</span>
          @else
            <span class="badge badge-gray">Selesai</span>
          @endif
        </div>
        <h2 class="font-heading text-lg md:text-xl font-bold text-bluedark">
          {{ $assessment->title }}
        </h2>
        <p class="text-xs text-bluedark/60 mt-1">
          Kelas: <strong>{{ $assessment->teachingAssignment?->schoolClass?->name }}</strong> &middot; 
          Mata Pelajaran: <strong>{{ $assessment->teachingAssignment?->subject?->name }}</strong>
        </p>
      </div>

      <div class="text-right">
        <span class="text-xs text-slate-500 block">Tenggat Waktu:</span>
        <span class="font-bold text-sm text-bluedark">
          {{ $assessment->due_at ? $assessment->due_at->translatedFormat('l, d F Y - H:i') : 'Tidak dibatasi' }}
        </span>
      </div>
    </div>

    @if($assessment->description)
      <div class="mt-4 p-4 rounded-xl bg-bluelight/30 border border-bluelight text-xs text-ink/80 leading-relaxed">
        <h4 class="font-bold text-bluedark mb-1">Petunjuk Tugas:</h4>
        {!! nl2br(e($assessment->description)) !!}
      </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 text-xs">
      <div class="p-3 rounded-xl bg-bluelight/50">
        <span class="text-slate-500 block">Kolom Buku Nilai</span>
        <span class="font-bold text-bluedark">{{ $assessment->gradebookColumn?->name ?? 'Tugas Mandiri' }}</span>
      </div>
      <div class="p-3 rounded-xl bg-bluelight/50">
        <span class="text-slate-500 block">Skor Maksimal</span>
        <span class="font-bold text-bluedark">{{ $assessment->max_score }} Poin</span>
      </div>
      <div class="p-3 rounded-xl bg-bluelight/50">
        <span class="text-slate-500 block">Aturan Terlambat</span>
        <span class="font-bold text-bluedark">
          {{ $assessment->latePolicy && $assessment->latePolicy->enabled ? 'Potongan ' . $assessment->latePolicy->reduction_value . ' Poin/' . $assessment->latePolicy->interval : 'Tanpa Potongan' }}
        </span>
      </div>
      <div class="p-3 rounded-xl bg-bluelight/50">
        <span class="text-slate-500 block">Rubrik Terlampir</span>
        <span class="font-bold text-bluedark">
          {{ $assessment->rubric ? $assessment->rubric->name : 'Tanpa Rubrik' }}
        </span>
      </div>
    </div>
  </div>

  <!-- Student Submissions Table Card -->
  <div class="panel p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Pengumpulan &amp; Penilaian Siswa</h2>
        <p class="text-xs text-bluedark/50">Daftar siswa dan status hasil pengumpulan tugas</p>
      </div>
      <span class="text-xs font-semibold text-blueprim">
        {{ $assessment->submissions->where('status', 'GRADED')->count() }} dari {{ $assessment->submissions->count() }} dinilai
      </span>
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th class="w-12 text-center">No</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>Waktu Pengumpulan</th>
            <th class="text-center">Keterlambatan</th>
            <th class="text-center">Nilai Akhir</th>
            <th class="text-center">Status</th>
            <th class="text-center w-28">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($assessment->submissions as $idx => $sub)
            <tr>
              <td class="text-center font-semibold text-slate-500">{{ $idx + 1 }}</td>
              <td class="font-medium text-bluedark">{{ $sub->student?->full_name }}</td>
              <td class="text-slate-500 font-mono text-xs">{{ $sub->student?->nis ?? '-' }}</td>
              <td class="text-xs text-slate-600 whitespace-nowrap">
                {{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d M Y, H:i') : '-' }}
              </td>
              <td class="text-center text-xs">
                @if($sub->late_duration_minutes > 0)
                  <span class="badge badge-red">Telat {{ $sub->late_duration_minutes }} menit</span>
                @else
                  <span class="badge badge-green">Tepat Waktu</span>
                @endif
              </td>
              <td class="text-center font-mono font-bold text-sm">
                @if($sub->status->value === 'GRADED')
                  <span class="text-emerald-600">{{ $sub->score }}</span>
                @else
                  <span class="text-slate-400">-</span>
                @endif
              </td>
              <td class="text-center">
                @if($sub->status->value === 'GRADED')
                  <span class="badge badge-green">Dinilai</span>
                @elseif($sub->status->value === 'SUBMITTED')
                  <span class="badge badge-yellow">Menunggu Koreksi</span>
                @else
                  <span class="badge badge-gray">Draft</span>
                @endif
              </td>
              <td class="text-center">
                <button type="button" 
                  onclick="openGradeModal({{ $sub->id }}, '{{ addslashes($sub->student?->full_name) }}', {{ $sub->score ?? 'null' }}, '{{ addslashes($sub->feedback ?? '') }}', '{{ addslashes($sub->content ?? '') }}')" 
                  class="btn btn-outline btn-sm py-1 px-3 text-xs">
                  {{ $sub->status->value === 'GRADED' ? 'Koreksi Ulang' : 'Beri Nilai' }}
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-8 text-xs text-bluedark/50">
                Belum ada pengumpulan jawaban dari siswa.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal Beri Nilai Siswa -->
<div class="modal-overlay" id="gradeSubmissionModal">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
      <div>
        <h3 class="font-heading font-bold text-bluedark text-base">Koreksi Jawaban Siswa</h3>
        <p class="text-xs text-bluedark/60" id="gradeStudentName">-</p>
      </div>
      <button type="button" onclick="closeModal('gradeSubmissionModal')" class="text-slate-400 hover:text-bluedark text-lg cursor-pointer">&times;</button>
    </div>

    <form id="gradeSubmissionForm" method="POST" class="space-y-4">
      @csrf

      <div>
        <label class="f-label">Jawaban Teks Siswa:</label>
        <div id="studentAnswerContent" class="p-3 rounded-xl bg-bluelight/30 border border-bluelight text-xs text-slate-700 min-h-[60px] max-h-40 overflow-y-auto whitespace-pre-wrap">
          -
        </div>
      </div>

      <div>
        <label class="f-label">Nilai Akhir (Skala 0 - {{ $assessment->max_score }}) <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0" max="{{ $assessment->max_score }}" name="score" id="inputScore" required class="f-input">
      </div>

      <div>
        <label class="f-label">Catatan / Feedback Guru</label>
        <textarea name="feedback" id="inputFeedback" rows="3" placeholder="Berikan komentar perbaikan atau apresiasi terhadap hasil kerja siswa..." class="f-textarea"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeModal('gradeSubmissionModal')" class="btn btn-outline">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openGradeModal(submissionId, studentName, currentScore, currentFeedback, studentContent) {
  document.getElementById('gradeStudentName').textContent = 'Siswa: ' + studentName;
  document.getElementById('studentAnswerContent').textContent = studentContent || 'Tidak ada teks jawaban (lampiran file).';
  document.getElementById('inputScore').value = currentScore !== null ? currentScore : '';
  document.getElementById('inputFeedback').value = currentFeedback || '';
  
  const form = document.getElementById('gradeSubmissionForm');
  form.action = "{{ url('/guru/assessments/' . $assessment->id . '/submissions') }}/" + submissionId + "/grade";
  
  openModal('gradeSubmissionModal');
}
</script>
@endpush
