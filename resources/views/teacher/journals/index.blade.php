@extends('layouts.teacher')

@section('title', 'Absensi Kelas & Jurnal — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Absensi Kelas</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Absensi Kelas &amp; Jurnal Harian Mengajar</p>
  </div>

  @if(! $selectedAssignment)
    <!-- Step 1: Panel Pilih Kelas matching absensi-kelas.html -->
    <div class="step-panel active" data-panel="1">
      <div class="panel p-5">
        <div class="mb-4">
          <h2 class="font-heading font-semibold text-bluedark text-[15px]">Absensi Kelas</h2>
          <p class="text-xs text-bluedark/50 mt-0.5">Pilih kelas untuk mengelola journal kelas</p>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4" id="absensiKelasGrid">
          @forelse($assignments as $assignment)
            <a href="{{ route('teacher.journals.index', ['assignment_id' => $assignment->id]) }}" 
               class="kelas-card block group" data-group="absensi" data-kode="{{ $assignment->schoolClass?->name }}">
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
    <!-- Step 2: Detail Journal & Form Absensi matching absensi-kelas.html -->
    <div class="step-panel active" data-panel="2">
      <p class="text-sm text-bluedark/60 mb-4">
        <a href="{{ route('teacher.journals.index') }}" class="font-semibold text-blueprim hover:underline" id="absensiBack">Absensi Kelas</a> / 
        <span class="font-semibold text-bluedark" id="absensiBreadcrumb">{{ $selectedAssignment->schoolClass?->name }}</span>
      </p>

      <!-- Panel Journal Table -->
      <div class="panel p-5 mb-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">
              Journal - Kelas <span id="absensiKelasName">{{ $selectedAssignment->schoolClass?->name }}</span>
            </h2>
            <p class="text-xs text-bluedark/50">
              {{ $selectedAssignment->subject?->name }} - {{ $selectedAssignment->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }} {{ $selectedAssignment->semester?->academicYear?->name ?? '2026/2027' }}
            </p>
          </div>
          <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> 
            Export
          </button>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th rowspan="2">Hari/Tanggal</th>
                <th rowspan="2" class="text-center">Jam ke-</th>
                <th rowspan="2">Mata Pelajaran</th>
                <th rowspan="2">Nama Guru</th>
                <th colspan="4" class="text-center" style="background:#0D47A1;color:#fff;">Jumlah Siswa</th>
                <th rowspan="2">Materi &amp; Keterangan</th>
              </tr>
              <tr>
                <th class="text-center w-12 font-bold">Hadir</th>
                <th class="text-center w-10 font-bold">S</th>
                <th class="text-center w-10 font-bold">I</th>
                <th class="text-center w-10 font-bold">A</th>
              </tr>
            </thead>
            <tbody id="journalTableBody">
              @forelse($journals as $j)
                <tr>
                  <td class="font-medium text-xs whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($j->journal_date)->translatedFormat('l, d M Y') }}
                  </td>
                  <td class="text-center font-mono text-xs font-semibold text-bluedark">
                    {{ $j->startPeriod?->period_number }} sd {{ $j->endPeriod?->period_number }}
                  </td>
                  <td class="text-xs font-medium">{{ $selectedAssignment->subject?->name }}</td>
                  <td class="text-xs">{{ auth()->user()->name }}</td>
                  <td class="text-center font-bold text-emerald-600 font-mono">{{ $j->hadir_count }}</td>
                  <td class="text-center font-bold text-amber-600 font-mono">{{ $j->sakit_count }}</td>
                  <td class="text-center font-bold text-blue-600 font-mono">{{ $j->izin_count }}</td>
                  <td class="text-center font-bold text-red-600 font-mono">{{ $j->alpha_count }}</td>
                  <td class="text-xs">
                    <span class="font-semibold text-bluedark block">{{ $j->material }}</span>
                    @if($j->notes)
                      <span class="text-[11px] text-slate-500 block">{{ $j->notes }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-8 text-xs text-bluedark/50">
                    Belum ada riwayat jurnal kelas untuk rombel ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form Block: Manajemen Absensi matching absensi-kelas.html -->
      <div class="form-block">
        <div class="form-block__header">
          <h3>Manajemen Absensi</h3>
          <p>Isi form berikut untuk memanajemen kolom absensi pada journal kelas</p>
        </div>

        <form action="{{ route('teacher.journals.store') }}" method="POST" class="form-block__body">
          @csrf
          <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment->id }}">

          <div class="form-row cols-2">
            <div>
              <label class="f-label">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
              <input type="date" name="journal_date" value="{{ date('Y-m-d') }}" required class="f-input">
            </div>

            <div>
              <label class="f-label">Jam Pelajaran ke- <span class="text-red-500">*</span></label>
              <div class="grid grid-cols-[1fr_auto_1fr] gap-2 items-center">
                <select name="start_period_id" class="f-select" required>
                  @foreach($lessonPeriods as $period)
                    <option value="{{ $period->id }}">Jam ke-{{ $period->period_number }} ({{ $period->start_time }})</option>
                  @endforeach
                </select>
                <span class="text-bluedark/50 text-sm font-semibold">sd</span>
                <select name="end_period_id" class="f-select" required>
                  @foreach($lessonPeriods as $period)
                    <option value="{{ $period->id }}" {{ $loop->iteration == min(2, $lessonPeriods->count()) ? 'selected' : '' }}>
                      Jam ke-{{ $period->period_number }} ({{ $period->end_time }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="form-row cols-2">
            <div>
              <label class="f-label">Mata Pelajaran</label>
              <input type="text" value="{{ $selectedAssignment->subject?->name }}" readonly class="f-input bg-bluelight/20">
            </div>
            <div>
              <label class="f-label">Nama Guru Pengajar</label>
              <input type="text" value="{{ auth()->user()->name }}" readonly class="f-input bg-bluelight/20">
            </div>
          </div>

          <div class="mb-4">
            <label class="f-label">Materi Pembelajaran yang Disampaikan <span class="text-red-500">*</span></label>
            <input type="text" name="material" placeholder="cth. Pertidaksamaan Linear dan Pembuktian Rumus" required class="f-input">
          </div>

          <div class="form-row cols-4">
            <div>
              <label class="f-label">Hadir</label>
              <input type="number" name="hadir_count" id="hadirCount" value="{{ $enrolledStudents->count() }}" min="0" class="f-input">
            </div>
            <div>
              <label class="f-label">Sakit (S)</label>
              <input type="number" name="sakit_count" id="sakitCount" value="0" min="0" class="f-input">
            </div>
            <div>
              <label class="f-label">Izin (I)</label>
              <input type="number" name="izin_count" id="izinCount" value="0" min="0" class="f-input">
            </div>
            <div>
              <label class="f-label">Alpha (A)</label>
              <input type="number" name="alpha_count" id="alphaCount" value="0" min="0" class="f-input">
            </div>
          </div>

          <div class="mb-4">
            <label class="f-label">Catatan Tambahan / Kegiatan Kelas</label>
            <textarea name="notes" rows="2" placeholder="Catatan kelas, kendala, atau ketuntasan materi..." class="f-textarea"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="reset" class="btn btn-outline">Reset</button>
            <button type="submit" class="btn btn-primary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
              Simpan Journal
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>
@endsection
