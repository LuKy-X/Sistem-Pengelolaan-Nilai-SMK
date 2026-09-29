@extends('layouts.teacher')

@section('title', 'Catatan Nilai — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Catatan Nilai</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Catatan Nilai &amp; Bimbingan Evaluasi Siswa</p>
  </div>

  @if(! $selectedAssignment)
    <!-- Step 1: Panel Pilih Kelas matching catatan-nilai.html -->
    <div class="step-panel active" data-panel="1">
      <div class="panel p-5">
        <div class="mb-4">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Catatan Nilai</h2>
          <p class="text-xs text-bluedark/50 mt-0.5">Pilih kelas untuk mengelola catatan nilai siswa</p>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4" id="catatanKelasGrid">
          @forelse($assignments as $assignment)
            <a href="{{ route('teacher.grade-notes.index', ['assignment_id' => $assignment->id]) }}" 
               class="kelas-card block group" data-group="catatan" data-kode="{{ $assignment->schoolClass?->name }}">
              <div class="flex items-center justify-between mb-2">
                <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft group-hover:text-blueprim transition-colors"><polyline points="9 18 15 12 9 6"/></svg>
              </div>
              <div class="font-heading font-bold text-bluedark text-sm">
                {{ $assignment->schoolClass?->name ?? 'Kelas' }}
              </div>
              <div class="text-xs text-bluedark/50">
                {{ $assignment->schoolClass?->department?->name ?? $assignment->subject?->name }}
              </div>
              <div class="kelas-card__meta">
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 
                  {{ $assignment->schoolClass?->students_count ?? 36 }} Siswa
                </span>
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> 
                  {{ $assignment->weekly_hours }} Jam / Mgg
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="badge badge-blue">{{ $assignment->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }}</span>
                <span class="badge badge-gray">{{ $assignment->semester?->academicYear?->name ?? '2026/2027' }}</span>
              </div>
            </a>
          @empty
            <div class="col-span-full py-10 text-center text-xs text-bluedark/50">
              Belum ada kelas yang terdaftar pada penugasan mengajar Anda.
            </div>
          @endforelse
        </div>
      </div>
    </div>
  @else
    <!-- Step 2: Detail Catatan Nilai matching catatan-nilai.html -->
    <div class="step-panel active" data-panel="2">
      <p class="text-sm text-bluedark/60 mb-4">
        <a href="{{ route('teacher.grade-notes.index') }}" class="font-semibold text-blueprim hover:underline" id="catatanBack">Catatan Nilai</a> / 
        <span class="font-semibold text-bluedark" id="catatanBreadcrumb">{{ $selectedAssignment->schoolClass?->name }}</span>
      </p>

      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">
              Catatan Nilai - Kelas <span id="catatanKelasName">{{ $selectedAssignment->schoolClass?->name }}</span>
            </h2>
            <p class="text-xs text-bluedark/50">{{ $selectedAssignment->subject?->name }} &middot; Gasal 2026/2027</p>
          </div>
          <button type="button" onclick="openModal('addNoteModal')" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
            Catatan Baru
          </button>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th style="width:3.5rem" class="text-center">No</th>
                <th class="min-w-[180px]">Nama Siswa</th>
                <th class="w-28 text-center">Kategori</th>
                <th>Catatan Nilai / Evaluasi</th>
                <th class="w-32 text-center">Tanggal</th>
                <th style="width:6rem" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="catatanTableBody">
              @forelse($notes as $index => $item)
                <tr>
                  <td class="text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                  <td class="font-medium text-bluedark">{{ $item->student?->full_name ?? 'Siswa' }}</td>
                  <td class="text-center">
                    <span class="badge {{ $item->category === 'REMEDIAL' ? 'badge-yellow' : ($item->category === 'PRESTASI' ? 'badge-green' : 'badge-blue') }}">
                      {{ $item->category }}
                    </span>
                  </td>
                  <td class="text-xs text-slate-700 leading-relaxed">{{ $item->note }}</td>
                  <td class="text-center text-xs text-slate-500 whitespace-nowrap">
                    {{ $item->created_at ? $item->created_at->translatedFormat('d M Y') : '-' }}
                  </td>
                  <td class="text-center">
                    <form action="{{ route('teacher.grade-notes.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="icon-btn icon-btn--delete cursor-pointer" title="Hapus Catatan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-8 text-xs text-bluedark/50">
                    Belum ada catatan nilai atau bimbingan remedial untuk kelas ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Catatan Baru -->
    <div class="modal-overlay" id="addNoteModal">
      <div class="modal-box">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
          <h3 class="font-heading font-bold text-bluedark text-base">Tambah Catatan Nilai Siswa</h3>
          <button type="button" onclick="closeModal('addNoteModal')" class="text-slate-400 hover:text-bluedark text-lg cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('teacher.grade-notes.store') }}" method="POST" class="space-y-4">
          @csrf
          <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment->id }}">

          <div>
            <label class="f-label">Nama Siswa <span class="text-red-500">*</span></label>
            <select name="student_id" class="f-select" required>
              <option value="">-- Pilih Siswa --</option>
              @foreach($students as $st)
                <option value="{{ $st->id }}">{{ $st->full_name }} (NIS: {{ $st->nis ?? '-' }})</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="f-label">Kategori Catatan <span class="text-red-500">*</span></label>
            <select name="category" class="f-select" required>
              <option value="REMEDIAL">Remedial Pembelajaran</option>
              <option value="PENGAYAAN">Pengayaan / Penguatan Materi</option>
              <option value="EVALUASI">Evaluasi Sikap &amp; Kerapian</option>
              <option value="PRESTASI">Pencapaian Prestasi Istimewa</option>
            </select>
          </div>

          <div>
            <label class="f-label">Uraian Catatan Evaluasi <span class="text-red-500">*</span></label>
            <textarea name="note" rows="3" placeholder="Tuliskan catatan khusus atau rencana tindak lanjut bimbingan..." required class="f-textarea"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('addNoteModal')" class="btn btn-outline">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Catatan</button>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>
@endsection
