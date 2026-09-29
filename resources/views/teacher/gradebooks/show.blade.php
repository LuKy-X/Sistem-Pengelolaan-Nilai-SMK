@extends('layouts.teacher')

@section('title', 'Daftar Nilai — ' . ($gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas'))

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Nilai</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Nilai Siswa pada Buku Nilai Digital</p>
  </div>

  <div class="arrow-tabs" id="nilaiTabs">
    <a href="{{ route('teacher.gradebooks.index') }}"><span class="step-num">1</span>Daftar Kelas</a>
    <a href="{{ route('teacher.gradebooks.show', $gradebook) }}" class="active"><span class="step-num">2</span>Buku Nilai</a>
    <a href="{{ route('teacher.assessments.index', ['assignment_id' => $gradebook->teaching_assignment_id]) }}"><span class="step-num">3</span>Tugas/Remidi</a>
  </div>

  <p class="text-sm text-bluedark/60 mb-2">
    <a href="{{ route('teacher.gradebooks.index') }}" class="font-semibold text-blueprim hover:underline">Daftar Kelas</a> / 
    <span class="font-semibold text-bluedark">{{ $gradebook->teachingAssignment?->schoolClass?->name }} - {{ $gradebook->name }}</span>
  </p>

  <div class="panel p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">
          Daftar Nilai - Kelas <span>{{ $gradebook->teachingAssignment?->schoolClass?->name }}</span>
        </h2>
        <p class="text-xs text-bluedark/50 mt-0.5">
          {{ $gradebook->teachingAssignment?->subject?->name }} &middot; {{ $gradebook->teachingAssignment?->semester?->academicYear?->name ?? '2026/2027' }} ({{ $gradebook->teachingAssignment?->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }})
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('teacher.gradebooks.edit', $gradebook) }}" class="btn btn-outline btn-sm" title="Edit identitas dan kelola kolom">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Atur Kolom
        </a>
        <button type="button" onclick="openModal('addColumnModal')" class="btn btn-primary btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Kolom
        </button>
        <a href="{{ route('teacher.gradebooks.export', $gradebook) }}" class="btn btn-outline btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> 
          Export
        </a>
      </div>
    </div>

    <form action="{{ route('teacher.gradebooks.scores.store', $gradebook) }}" method="POST" id="gradebookForm">
      @csrf

      <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
        <table class="tbl">
          <thead>
            <tr>
              <th rowspan="2" class="text-center w-12">No</th>
              <th rowspan="2" class="min-w-[180px]">Nama Siswa</th>
              @if($columns->isNotEmpty())
                <th colspan="{{ $columns->count() }}" class="text-center" style="background:#0D47A1;color:#fff;">
                  Komponen Penilaian &amp; Ulangan
                </th>
              @else
                <th class="text-center" style="background:#0D47A1;color:#fff;">Kolom Nilai</th>
              @endif
            </tr>
            <tr>
              @forelse($columns as $col)
                <th class="text-center min-w-[75px]" title="{{ $col->name }} (Bobot: {{ $col->weight }}%)">
                  {{ $col->code }}
                  @if($col->column_type->value === 'SUMMARY')
                    <span class="text-[10px] text-blueprim block font-normal">(Avg)</span>
                  @endif
                </th>
              @empty
                <th class="text-center text-xs py-2 text-slate-400">Belum ada kolom nilai</th>
              @endforelse
            </tr>
          </thead>
          <tbody id="nilaiTableBody">
            @forelse($students as $index => $item)
              <tr>
                <td class="text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                <td class="font-medium text-bluedark">
                  <div class="font-semibold">{{ $item->student?->full_name ?? 'Siswa' }}</div>
                  <div class="text-[10px] text-slate-400">NIS: {{ $item->student?->nis ?? '-' }}</div>
                </td>
                @foreach($columns as $col)
                  @php
                    $val = $scoresMatrix[$item->student_id][$col->id] ?? '';
                    $isSummary = $col->column_type->value === 'SUMMARY';
                  @endphp
                  <td class="p-1.5 text-center {{ $isSummary ? 'bg-bluelight/40 font-bold text-blueprim' : '' }}">
                    @if($isSummary)
                      <span class="font-mono text-sm">{{ $val !== '' ? $val : '-' }}</span>
                    @else
                      <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="{{ $col->max_score }}" 
                        name="scores[{{ $item->student_id }}][{{ $col->id }}]" 
                        value="{{ $val }}" 
                        class="f-input py-1 text-center font-mono text-xs w-16"
                        placeholder="0"
                      >
                    @endif
                  </td>
                @endforeach
              </tr>
            @empty
              <tr>
                <td colspan="{{ 2 + max(1, $columns->count()) }}" class="text-center py-8 text-xs text-bluedark/50">
                  Belum ada siswa terdaftar pada buku nilai ini.
                </td>
              </tr>
            @endforelse
          </tbody>
          @if($students->isNotEmpty() && $columns->isNotEmpty())
            <tfoot>
              <tr style="background:#F7FBFF;" class="font-bold border-t-2 border-bluelight">
                <td colspan="2" class="text-right px-4 py-2.5 text-xs text-bluedark uppercase tracking-wider">
                  Rata-rata Kelas:
                </td>
                @foreach($columns as $col)
                  <td class="text-center py-2.5 font-mono text-xs text-blueprim">
                    {{ $columnAverages[$col->id] ?? '-' }}
                  </td>
                @endforeach
              </tr>
            </tfoot>
          @endif
        </table>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
          Simpan Nilai
        </button>
      </div>
    </form>
  </div>

  <!-- Form Block: Bantuan Manajemen Rubrik & Nilai Cepat matching example nilai.html -->
  <div class="form-block">
    <div class="form-block__header">
      <h3>Manajemen Tugas/Mandiri</h3>
      <p>Isi form berikut untuk memanajemen nilai tugas atau remidi siswa pada buku nilai</p>
    </div>
    <div class="form-block__body">
      <div class="mb-4">
        <label class="f-label">Judul Tugas / Evaluasi</label>
        <input type="text" class="f-input" value="{{ $columns->first()?->name ?? 'Evaluasi Pembelajaran' }}" readonly>
      </div>
      <div class="form-row cols-2">
        <div>
          <label class="f-label">Kolom pada Buku Nilai</label>
          <select class="f-select" id="quickColSelect">
            @foreach($columns as $col)
              <option value="{{ $col->id }}">{{ $col->code }} - {{ $col->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Nama Siswa</label>
          <select class="f-select" id="quickStudentSelect">
            @foreach($students as $st)
              <option value="{{ $st->student_id }}">{{ $st->student?->full_name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-xs text-bluedark/60">Gunakan spreadsheet di atas untuk pengisian nilai massal yang efisien.</span>
        <button type="button" onclick="document.getElementById('gradebookForm').scrollIntoView({behavior: 'smooth'})" class="btn btn-outline btn-sm">
          Ke Spreadsheet Nilai &uarr;
        </button>
      </div>
    </div>
  </div>

</div>

<!-- Modal Tambah Kolom Nilai -->
<div class="modal-overlay" id="addColumnModal">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
      <h3 class="font-heading font-bold text-bluedark text-base">Tambah Kolom Nilai</h3>
      <button type="button" onclick="closeModal('addColumnModal')" class="text-slate-400 hover:text-bluedark text-lg cursor-pointer">&times;</button>
    </div>

    <form action="{{ route('teacher.gradebooks.columns.store', $gradebook) }}" method="POST" class="space-y-4">
      @csrf

      <div>
        <label class="f-label">Nama Kolom <span class="text-red-500">*</span></label>
        <input type="text" name="name" placeholder="cth. Ulangan Harian 3" required class="f-input">
      </div>

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Kode Singkat <span class="text-red-500">*</span></label>
          <input type="text" name="code" placeholder="cth. UH3" required class="f-input uppercase font-mono">
        </div>
        <div>
          <label class="f-label">Tipe Kolom <span class="text-red-500">*</span></label>
          <select name="column_type" class="f-select">
            <option value="SCORE">Nilai Riil (Score)</option>
            <option value="SUMMARY">Rata-rata (Summary)</option>
          </select>
        </div>
      </div>

      <div class="form-row cols-2">
        <div>
          <label class="f-label">Nilai Maksimum</label>
          <input type="number" name="max_score" value="100" min="1" class="f-input">
        </div>
        <div>
          <label class="f-label">Bobot Penilaian (%)</label>
          <input type="number" name="weight" value="10" min="0" max="100" class="f-input">
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeModal('addColumnModal')" class="btn btn-outline">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Kolom</button>
      </div>
    </form>
  </div>
</div>
@endsection
