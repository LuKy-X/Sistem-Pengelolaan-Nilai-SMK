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
            <option value="TUGAS">Tugas Mandiri</option>
            <option value="ULANGAN_HARIAN">Ulangan Harian</option>
            <option value="REMEDIAL">Remedial</option>
            <option value="PROJECT">Project / Portofolio</option>
            <option value="PRAKTIK">Uji Praktik Kejuruan</option>
          </select>
        </div>
      </div>

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Kolom pada Buku Nilai</label>
          <select name="gradebook_column_id" class="f-select">
            <option value="">-- Tanpa Tautan Kolom Langsung --</option>
            @foreach($gradebookColumns as $col)
              <option value="{{ $col->id }}">{{ $col->code }} - {{ $col->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="f-label">Rubrik Penilaian (Opsional)</label>
          <select name="rubric_id" class="f-select">
            <option value="">-- Tanpa Rubrik Penilaian --</option>
            @foreach($rubrics as $rubric)
              <option value="{{ $rubric->id }}">{{ $rubric->name }} ({{ $rubric->criteria->count() }} Kriteria)</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="f-label">Judul Tugas <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="cth. Tugas Pemrograman Berorientasi Objek" required class="f-input">
      </div>

      <div class="mb-4">
        <label class="f-label">Deskripsi Tugas &amp; Instruksi Pengerjaan</label>
        <textarea name="description" rows="3" placeholder="Tuliskan instruksi atau petunjuk pengumpulan tugas bagi siswa..." class="f-textarea">{{ old('description') }}</textarea>
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

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat</label>
          <div class="grid grid-cols-[1fr_auto_1.4fr] gap-2 items-center">
            <input type="number" name="reduction_value" value="{{ old('reduction_value', 5) }}" min="0" class="f-input">
            <span class="text-bluedark/50 text-sm">/</span>
            <select name="interval" class="f-select">
              <option value="MINGGU">Minggu</option>
              <option value="HARI">Hari</option>
            </select>
          </div>
        </div>

        <div class="field-toggle self-end">
          <span>Gunakan Kebijakan Keterlambatan</span>
          <label class="toggle-switch">
            <input type="checkbox" name="enable_late_policy" value="1" checked>
            <span class="slider"></span>
          </label>
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
@endsection
