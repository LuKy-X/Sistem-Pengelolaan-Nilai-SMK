@extends('layouts.student')

@section('title', 'Detail Tugas')

@section('content')
@php
  $fmt = fn ($value) => $value !== null ? rtrim(rtrim(number_format((float) $value, 2), '0'), '.') : '-';
  $typeLabels = ['TASK' => 'Tugas', 'QUIZ' => 'Kuis', 'PROJECT' => 'Proyek', 'EXAM' => 'Ujian', 'REMEDIAL' => 'Remedial', 'OTHER' => 'Lainnya'];
  $isReviewed = $submission !== null && $submission->status->value === 'REVIEWED';
  $isSubmitted = $submission !== null && in_array($submission->status->value, ['SUBMITTED', 'REVIEWED'], true);
  $isOverdue = $assessment->due_at !== null && $assessment->due_at->isPast();
  $canSubmit = $assessment->submission_required && ! $isReviewed;
  $latePolicy = $assessment->latePolicy;
@endphp
<div class="space-y-4 lg:space-y-5">

  <div>
    <a href="{{ route('student.assignments.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Semua tugas</a>
    <div class="flex flex-wrap items-center gap-2 mt-1">
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $assessment->title }}</h1>
      <span class="badge badge-blue">{{ $typeLabels[$assessment->type->value] ?? $assessment->type->value }}</span>
      @if($isReviewed)
        <span class="badge badge-green">Sudah Dinilai</span>
      @elseif($isSubmitted)
        <span class="badge badge-green">Terkumpul</span>
      @elseif($isOverdue)
        <span class="badge badge-red">Terlewat</span>
      @else
        <span class="badge badge-yellow">Belum Dikumpulkan</span>
      @endif
    </div>
    <p class="text-sm text-bluedark/60 mt-1">
      {{ $assessment->teachingAssignment?->subject?->name ?? 'Mapel' }} &middot; {{ $assessment->teachingAssignment?->schoolClass?->name }} &middot; {{ $assessment->teachingAssignment?->teacher?->full_name ?? '-' }}
    </p>
  </div>

  <div class="grid lg:grid-cols-3 gap-4 lg:gap-5">
    <div class="lg:col-span-2 flex flex-col gap-4 lg:gap-5">

      <div class="panel p-4 lg:p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Instruksi</h2>
        <dl class="info-list mb-4">
          <div class="info-list__row">
            <dt>Tenggat</dt>
            <dd>: {{ $assessment->due_at?->format('d M Y, H:i') ?? 'Tidak ada tenggat' }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Nilai Maksimal</dt>
            <dd>: {{ $fmt($assessment->gradebookColumn?->max_score ?? $assessment->max_score) }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Pengumpulan</dt>
            <dd>: {{ $assessment->submission_required ? 'Wajib (teks / file daring)' : 'Tidak perlu mengumpulkan daring' }}</dd>
          </div>
          @if($latePolicy && $latePolicy->enabled)
            <div class="info-list__row">
              <dt>Kebijakan Telat</dt>
              <dd>: Potongan {{ $fmt($latePolicy->reduction_value) }} poin, nilai akhir minimal {{ $fmt($latePolicy->minimum_max_score) }}</dd>
            </div>
          @endif
        </dl>

        @if($assessment->description)
          <p class="text-xs text-bluedark/80 mb-3">{{ $assessment->description }}</p>
        @endif
        @if($assessment->instructions)
          <div class="p-3 rounded-xl bg-bluelight/40 border border-bluelight text-xs text-bluedark/80 whitespace-pre-line">{{ $assessment->instructions }}</div>
        @endif
      </div>

      @if($assessment->rubric && $assessment->rubric->criteria->isNotEmpty())
        <div class="panel p-4 lg:p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Rubrik Penilaian</h2>
          <table class="w-full text-xs">
            <thead>
              <tr class="text-left text-bluedark/50 border-b border-bluelight">
                <th class="py-1.5 pr-2 font-semibold">Kriteria</th>
                <th class="py-1.5 font-semibold text-right">Poin Maks</th>
              </tr>
            </thead>
            <tbody>
              @foreach($assessment->rubric->criteria as $criterion)
                <tr class="border-b border-bluelight/60">
                  <td class="py-1.5 pr-2">
                    <div class="font-semibold text-bluedark">{{ $criterion->criterion }}</div>
                    @if($criterion->description)<div class="text-[10px] text-bluedark/45">{{ $criterion->description }}</div>@endif
                  </td>
                  <td class="py-1.5 text-right font-semibold text-bluedark">{{ $fmt($criterion->max_points) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      @if($canSubmit)
        <div class="panel p-4 lg:p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">{{ $isSubmitted ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas' }}</h2>
          <p class="text-[11px] text-bluedark/50 mb-4">
            @if($isOverdue)
              Tenggat sudah lewat. Pengumpulan Anda akan tercatat terlambat dan dapat dikenakan potongan sesuai kebijakan guru.
            @else
              Isi jawaban teks, unggah file, atau keduanya.
            @endif
          </p>

          <form method="POST" action="{{ route('student.assignments.submit', $assessment) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
              <label class="f-label" for="content">Jawaban Teks</label>
              <textarea id="content" name="content" rows="5" class="f-textarea" placeholder="Tulis jawaban Anda di sini...">{{ old('content', $submission?->content) }}</textarea>
            </div>
            <div>
              <label class="f-label" for="attachment">File Lampiran (opsional, maks 10 MB)</label>
              <input type="file" id="attachment" name="attachment" class="f-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
              <p class="text-[10px] text-bluedark/45 mt-1">PDF, dokumen Office, gambar, atau ZIP.</p>
            </div>
            <button type="submit" class="btn btn-primary">{{ $isSubmitted ? 'Kirim Ulang' : 'Kirim Tugas' }}</button>
          </form>
        </div>
      @endif

    </div>

    <div class="flex flex-col gap-4 lg:gap-5">

      <div class="panel p-4 lg:p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Status Pengumpulan</h2>
        @if($submission)
          <dl class="info-list">
            <div class="info-list__row">
              <dt>Status</dt>
              <dd>:
                @if($submission->status->value === 'REVIEWED') Sudah dinilai
                @elseif($submission->status->value === 'SUBMITTED') Menunggu penilaian
                @else Draf
                @endif
              </dd>
            </div>
            <div class="info-list__row">
              <dt>Dikumpulkan</dt>
              <dd>: {{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}</dd>
            </div>
            <div class="info-list__row">
              <dt>Keterlambatan</dt>
              <dd>: {{ $submission->late_minutes > 0 ? $submission->late_minutes.' menit' : 'Tepat waktu' }}</dd>
            </div>
          </dl>

          @if($submission->media->isNotEmpty())
            <div class="mt-3">
              <div class="f-label">Lampiran Anda</div>
              <div class="flex flex-col gap-1.5">
                @foreach($submission->media as $media)
                  <a href="{{ asset('storage/'.$media->path) }}" target="_blank" class="flex items-center gap-2 p-2 rounded-lg border border-bluelight hover:bg-bluelight/40 transition-colors text-xs text-bluedark">
                    <svg class="w-3.5 h-3.5 text-blueprim shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    <span class="truncate">{{ $media->original_name }}</span>
                  </a>
                @endforeach
              </div>
            </div>
          @endif
        @else
          <p class="text-xs text-bluedark/50">Anda belum mengumpulkan tugas ini.</p>
        @endif
      </div>

      @if($score || $isReviewed)
        <div class="panel p-4 lg:p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Hasil Penilaian</h2>
          @if($score)
            <div class="flex items-end gap-2 mb-2">
              <div class="font-heading text-3xl font-bold {{ (float) $score->final_score >= 75 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $fmt($score->final_score) }}</div>
              <div class="text-xs text-bluedark/50 pb-1">/ {{ $fmt($score->max_score_snapshot ?? $assessment->gradebookColumn?->max_score) }}</div>
            </div>
            @if((float) $score->late_deduction > 0)
              <p class="text-[11px] text-red-500 mb-2">Termasuk potongan keterlambatan -{{ $fmt($score->late_deduction) }} poin.</p>
            @endif
          @endif

          @if($score && $score->rubricScores->isNotEmpty())
            <div class="text-[11px] font-semibold text-bluedark/60 mb-1.5">Rincian rubrik:</div>
            <div class="flex flex-col gap-1 mb-3">
              @foreach($score->rubricScores as $rubricScore)
                <div class="flex items-center justify-between text-xs">
                  <span class="text-bluedark/70">{{ $rubricScore->criterion?->criterion ?? '-' }}</span>
                  <span class="font-semibold text-bluedark">{{ $fmt($rubricScore->points_awarded) }}</span>
                </div>
              @endforeach
            </div>
          @endif

          @php $teacherFeedback = $score?->feedback ?? $submission?->teacher_feedback; @endphp
          @if($teacherFeedback)
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
              <div class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wide mb-1">Feedback Guru</div>
              <p class="text-xs text-emerald-900">{{ $teacherFeedback }}</p>
            </div>
          @endif
        </div>
      @endif

    </div>
  </div>

</div>
@endsection
