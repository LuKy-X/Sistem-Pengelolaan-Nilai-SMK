@extends('layouts.teacher')

@section('title', 'Buat Tugas & Asesmen — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Tugas</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Tugas Siswa</p>
  </div>

  <div class="arrow-tabs" id="tugasTabs">
    <a href="{{ route('teacher.gradebooks.index') }}"><span class="step-num">1</span>Daftar Kelas</a>
    <a href="{{ route('teacher.gradebooks.index') }}"><span class="step-num">2</span>Buku Nilai</a>
    <a href="{{ route('teacher.assessments.create') }}" class="active"><span class="step-num">3</span>Tugas/Remidi</a>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.assessments.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Kelas</a> / 
    <span class="font-semibold text-bluedark">{{ $selectedAssignment?->schoolClass?->name ?? 'XII RA' }} - Gasal 2026/2027</span>
  </p>

  <!-- Panel: Preview Daftar Nilai matching tugas.html -->
  <div class="panel p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">
          Daftar Nilai - Kelas <span>{{ $selectedAssignment?->schoolClass?->name ?? 'XII RA' }}</span>
        </h2>
        <p class="text-xs text-bluedark/50">{{ $selectedAssignment?->subject?->name ?? 'Matematika' }} &middot; Ulangan Harian dan Tugas</p>
      </div>
      @if($previewGradebook)
        <a href="{{ route('teacher.gradebooks.export', $previewGradebook) }}" class="btn btn-outline btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export
        </a>
      @endif
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th rowspan="2" class="w-12 text-center">No</th>
            <th rowspan="2">Nama Siswa</th>
            <th colspan="4" class="text-center" style="background:#0D47A1;color:#fff;">Ulangan Harian</th>
            <th colspan="3" class="text-center" style="background:#0D47A1;color:#fff;">Tugas</th>
            <th colspan="4" class="text-center" style="background:#0D47A1;color:#fff;">Nilai Akhir</th>
          </tr>
          <tr>
            <th class="text-center">UH1</th><th class="text-center">R1</th><th class="text-center">UH2</th><th class="text-center">R2</th>
            <th class="text-center">T1</th><th class="text-center">T2</th><th class="text-center">T3</th>
            <th class="text-center">RTO</th><th class="text-center">MID</th><th class="text-center">SEM</th><th class="text-center">NSB</th>
          </tr>
        </thead>
        <tbody>
          @forelse($previewStudents as $idx => $st)
            <tr>
              <td class="text-center font-semibold text-slate-500">{{ $idx + 1 }}</td>
              <td class="font-medium text-bluedark">{{ $st->student?->full_name ?? 'Siswa' }}</td>
              <td class="text-center font-mono text-slate-600">85</td>
              <td class="text-center font-mono text-slate-400">-</td>
              <td class="text-center font-mono text-slate-600">88</td>
              <td class="text-center font-mono text-slate-400">-</td>
              <td class="text-center font-mono text-slate-600">90</td>
              <td class="text-center font-mono text-slate-600">85</td>
              <td class="text-center font-mono text-slate-600">92</td>
              <td class="text-center font-mono font-bold text-blueprim">86.5</td>
              <td class="text-center font-mono text-slate-600">84</td>
              <td class="text-center font-mono text-slate-600">88</td>
              <td class="text-center font-mono font-bold text-emerald-600">87.5</td>
            </tr>
          @empty
            <tr>
              <td colspan="13" class="text-center py-6 text-xs text-bluedark/50">
                Pilih rombel untuk melihat preview daftar nilai siswa.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Form Block: Manajemen Tugas/Mandiri matching tugas.html -->
  <div class="form-block">
    <div class="form-block__header">
      <h3>Manajemen Tugas/Mandiri</h3>
      <p>Isi form berikut untuk memanajemen kolom tugas atau remidi pada buku nilai</p>
    </div>

    <form action="{{ route('teacher.assessments.store') }}" method="POST" class="form-block__body">
      @csrf

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Pilih Rombel / Kelas <span class="text-red-500">*</span></label>
          <select name="teaching_assignment_id" class="f-select" id="assignmentSelect" required>
            @foreach($assignments as $assign)
              <option value="{{ $assign->id }}" {{ ($selectedAssignment?->id == $assign->id) ? 'selected' : '' }}>
                {{ $assign->schoolClass?->name }} — {{ $assign->subject?->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="f-label">Tipe Asesmen <span class="text-red-500">*</span></label>
          <select name="type" class="f-select" required>
            <option value="TASK" {{ old('type') === 'TASK' ? 'selected' : '' }}>Tugas (Task)</option>
            <option value="QUIZ" {{ old('type') === 'QUIZ' ? 'selected' : '' }}>Kuis (Quiz)</option>
            <option value="PROJECT" {{ old('type') === 'PROJECT' ? 'selected' : '' }}>Projek / Praktik (Project)</option>
            <option value="EXAM" {{ old('type') === 'EXAM' ? 'selected' : '' }}>Ulangan / Ujian (Exam)</option>
            <option value="REMEDIAL" {{ old('type') === 'REMEDIAL' ? 'selected' : '' }}>Remidi (Remedial)</option>
            <option value="OTHER" {{ old('type') === 'OTHER' ? 'selected' : '' }}>Lainnya (Other)</option>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="f-label">Kolom pada Buku Nilai</label>
        <select name="gradebook_column_id" class="f-select">
          <option value="">-- Tanpa Tautan Kolom Langsung --</option>
          @foreach($gradebookColumns as $col)
            <option value="{{ $col->id }}" {{ old('gradebook_column_id') == $col->id ? 'selected' : '' }}>{{ $col->code }} - {{ $col->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-4">
        <label class="f-label">Judul Tugas <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="cth. Tugas Pemrograman Berorientasi Objek" required class="f-input">
      </div>

      <div class="mb-4">
        <label class="f-label">Deskripsi Tugas &amp; Instruksi Pengerjaan</label>
        <textarea name="description" rows="3" placeholder="Tuliskan instruksi atau petunjuk umum pengerjaan tugas..." class="f-textarea">{{ old('description') }}</textarea>
      </div>

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Batas Pengumpulan (Deadline)</label>
          <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="f-input">
        </div>
        <div>
          <label class="f-label">Bobot Maksimum Nilai</label>
          <input type="number" name="max_score" value="{{ old('max_score', 100) }}" min="1" max="100" class="f-input">
        </div>
      </div>

      <!-- Pengaturan Rubrik Penilaian (Di bawah Info Tugas) -->
      <div class="panel p-3.5 rounded-xl border border-bluelight bg-white mb-4 space-y-3">
        <div class="field-toggle">
          <div>
            <span class="font-heading font-semibold text-xs text-bluedark block">Gunakan Rubrik Penilaian</span>
            <p class="text-[11px] text-bluedark/50 font-normal">Rubrik membantu menstandarisasi kriteria penilaian tugas siswa</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="use_rubric" value="1" id="createRubricToggle" {{ old('rubric_id') ? 'checked' : '' }}>
            <span class="slider"></span>
          </label>
        </div>

        <div id="createRubricWrap" class="{{ old('rubric_id') ? '' : 'hidden' }} space-y-2 pt-2 border-t border-bluelight/60">
          <label class="f-label">Pilih Rubrik Penilaian</label>
          <select name="rubric_id" id="createRubricSelect" class="f-select">
            <option value="">-- Tanpa Rubrik Penilaian --</option>
            @foreach($rubrics as $rubric)
              <option value="{{ $rubric->id }}" {{ old('rubric_id') == $rubric->id ? 'selected' : '' }}>
                {{ $rubric->name }} ({{ $rubric->criteria->count() }} Kriteria)
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Pengaturan Status Publikasi Tugas (Default DRAFT) -->
      <div class="panel p-3.5 rounded-xl border border-bluelight bg-white mb-4 space-y-2.5">
        <div>
          <span class="font-heading font-semibold text-xs text-bluedark block">Status Publikasi</span>
          <p class="text-[11px] text-bluedark/60 mt-0.5">Tentukan apakah tugas langsung aktif untuk siswa atau disimpan sebagai draf sementara.</p>
        </div>
        <div class="flex items-center gap-4">
          <label class="inline-flex items-center gap-2 text-xs font-semibold cursor-pointer">
            <input type="radio" name="status" value="DRAFT" {{ old('status', 'DRAFT') === 'DRAFT' ? 'checked' : '' }}>
            <span class="text-amber-800">Simpan Sebagai Draf</span>
          </label>
          <label class="inline-flex items-center gap-2 text-xs font-semibold cursor-pointer">
            <input type="radio" name="status" value="PUBLISHED" {{ old('status') === 'PUBLISHED' ? 'checked' : '' }}>
            <span class="text-emerald-700">Publikasikan Sekarang</span>
          </label>
        </div>
      </div>

      <!-- Pengaturan Pengiriman Berkas & Bukti Siswa (Submission - Default Disabled) -->
      <div class="panel p-3.5 rounded-xl border border-bluelight bg-white mb-4 space-y-2.5">
        <div class="flex items-center justify-between">
          <div>
            <span class="font-heading font-semibold text-xs text-bluedark block">Wajibkan Pengiriman / Bukti Tugas Siswa (Online Submission)</span>
            <p class="text-[11px] text-bluedark/60">Aktifkan jika siswa harus mengunggah file bukti pengerjaan melalui sistem.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="submission_required" id="createSubmissionToggle" value="1" {{ old('submission_required') ? 'checked' : '' }}>
            <span class="slider"></span>
          </label>
        </div>
        <div id="createInstructionsWrap" class="{{ old('submission_required') ? '' : 'hidden' }} pt-2 border-t border-bluelight/60">
          <label class="f-label text-xs">Petunjuk Format &amp; Pengiriman Bukti Siswa</label>
          <textarea name="instructions" rows="2" placeholder="cth. Unggah laporan dalam format PDF atau foto dokumentasi..." class="f-textarea">{{ old('instructions') }}</textarea>
        </div>
      </div>

      <!-- Pengaturan Pengurangan Nilai Keterlambatan (Dipindah Paling Bawah - Default Nonaktif) -->
      <div class="panel p-3.5 rounded-xl border border-bluelight bg-white mb-4 space-y-2.5">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div>
            <span class="font-heading font-semibold text-xs text-bluedark block">Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat</span>
            <p class="text-[11px] text-bluedark/60 mt-0.5">
              Aktifkan jika batas maksimal nilai tugas otomatis berkurang ketika siswa terlambat mengumpulkan.
            </p>
          </div>
          <div class="field-toggle shrink-0">
            <span class="text-xs font-semibold text-bluedark" id="createLateToggleLabel">{{ old('enable_late_policy', '0') == '1' ? 'Aktif' : 'Nonaktif' }}</span>
            <label class="toggle-switch">
              <input type="hidden" name="enable_late_policy" value="0">
              <input type="checkbox" name="enable_late_policy" id="createLateToggle" value="1" {{ old('enable_late_policy', '0') == '1' ? 'checked' : '' }}>
              <span class="slider"></span>
            </label>
          </div>
        </div>

        <div id="createLateWrap" class="grid sm:grid-cols-2 gap-3 pt-2 border-t border-bluelight/60 {{ old('enable_late_policy', '0') == '1' ? '' : 'hidden' }}">
          <div>
            <label class="f-label text-xs">Nilai Pengurangan Poin</label>
            <div class="relative">
              <input type="number" name="reduction_value" value="{{ old('reduction_value', 5) }}" min="0" max="100" class="f-input">
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-bluedark/50 font-medium pointer-events-none">Poin</span>
            </div>
          </div>
          <div>
            <label class="f-label text-xs">Interval Keterlambatan</label>
            <select name="interval" class="f-select">
              <option value="MINGGU" {{ old('interval', 'MINGGU') === 'MINGGU' ? 'selected' : '' }}>Per Minggu (7 Hari)</option>
              <option value="HARI" {{ old('interval') === 'HARI' ? 'selected' : '' }}>Per Hari (1 Hari)</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <button type="reset" class="btn btn-outline">Reset</button>
        <button type="submit" class="btn btn-primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Simpan Tugas
        </button>
      </div>
    </form>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const lateToggle = document.getElementById('createLateToggle');
    const lateWrap = document.getElementById('createLateWrap');
    const lateLabel = document.getElementById('createLateToggleLabel');
    if (lateToggle && lateWrap && lateLabel) {
      lateToggle.addEventListener('change', function () {
        if (this.checked) {
          lateWrap.classList.remove('hidden');
          lateLabel.textContent = 'Aktif';
        } else {
          lateWrap.classList.add('hidden');
          lateLabel.textContent = 'Nonaktif';
        }
      });
    }

    const rubricToggle = document.getElementById('createRubricToggle');
    const rubricWrap = document.getElementById('createRubricWrap');
    const rubricSelect = document.getElementById('createRubricSelect');
    if (rubricToggle && rubricWrap && rubricSelect) {
      rubricToggle.addEventListener('change', function () {
        if (this.checked) {
          rubricWrap.classList.remove('hidden');
        } else {
          rubricWrap.classList.add('hidden');
          rubricSelect.value = '';
        }
      });
    }

    const subToggle = document.getElementById('createSubmissionToggle');
    const subWrap = document.getElementById('createInstructionsWrap');
    if (subToggle && subWrap) {
      subToggle.addEventListener('change', function () {
        if (this.checked) {
          subWrap.classList.remove('hidden');
        } else {
          subWrap.classList.add('hidden');
        }
      });
    }
  });
</script>
@endsection
