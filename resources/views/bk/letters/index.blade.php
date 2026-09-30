@extends('layouts.bk')

@section('title', 'Surat Peringatan — BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Surat Peringatan</h1>
      <p class="text-sm text-bluedark/60 mt-1">Terbitkan SP1&ndash;SP3, unduh berkas, dan pantau statusaktifnya</p>
    </div>
    <button type="button" class="btn btn-primary" data-modal-open="letterModal">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Terbitkan SP
    </button>
  </div>

  <div class="grid grid-cols-3 gap-4">
    @foreach(\App\Enums\DisciplinaryLetterType::cases() as $case)
      @php
          $color = match ($case->value) { 'SP1' => ['bg-amber-100', 'text-amber-700'], 'SP2' => ['bg-red-100', 'text-red-600'], default => ['bg-red-200', 'text-red-800'] };
      @endphp
      <div class="kpi-card">
        <div class="kpi-icon {{ $color[0] }} {{ $color[1] }}">
          <span class="font-heading font-bold text-sm">{{ $case->value }}</span>
        </div>
        <div>
          <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $typeCounts[$case->value] ?? 0 }}</div>
          <div class="text-[11px] text-bluedark/55 mt-1">Diterbitkan tahun ini</div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.disciplinary-letters.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[220px]">
        <label class="f-label" for="q">Cari Siswa</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama atau NIS siswa" class="f-input">
      </div>
      <div class="w-full sm:w-40">
        <label class="f-label" for="student_id">Siswa</label>
        <select id="student_id" name="student_id" class="f-select">
          <option value="">Semua Siswa</option>
          @foreach($students as $student)
            <option value="{{ $student->id }}" @selected($studentIdFilter === $student->id)>
              {{ $student->full_name }} — {{ $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="w-full sm:w-36">
        <label class="f-label" for="type">Jenis SP</label>
        <select id="type" name="type" class="f-select">
          <option value="">Semua</option>
          @foreach(\App\Enums\DisciplinaryLetterType::cases() as $case)
            <option value="{{ $case->value }}" @selected($typeFilter === $case->value)>{{ $case->value }}</option>
          @endforeach
        </select>
      </div>
      <div class="w-full sm:w-40">
        <label class="f-label" for="status">Status</label>
        <select id="status" name="status" class="f-select">
          <option value="">Semua Status</option>
          @foreach($statuses as $status)
            <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $status }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $typeFilter || $statusFilter || $studentIdFilter)
        <a href="{{ route('counselor.disciplinary-letters.index') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
      <a href="{{ route('counselor.disciplinary-letters.index', array_filter(['q' => $search, 'student_id' => $studentIdFilter, 'status' => $statusFilter])) }}"
         class="badge {{ $typeFilter ? 'badge-gray' : 'badge-blue' }}">Semua ({{ $typeCounts->sum() }})</a>
      @foreach(\App\Enums\DisciplinaryLetterType::cases() as $case)
        <a href="{{ route('counselor.disciplinary-letters.index', array_filter(['q' => $search, 'student_id' => $studentIdFilter, 'status' => $statusFilter, 'type' => $case->value])) }}"
           class="badge {{ $typeFilter === $case->value ? 'badge-blue' : 'badge-gray' }}">
          {{ $case->value }} ({{ $typeCounts[$case->value] ?? 0 }})
        </a>
      @endforeach
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Jenis</th>
            <th>Siswa</th>
            <th>Tanggal Terbit</th>
            <th>Alasan</th>
            <th>Diterbitkan Oleh</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($letters as $letter)
            @php
                $tone = match ($letter->status) { 'ACTIVE' => 'red', 'RESOLVED' => 'green', default => 'gray' };
                $label = match ($letter->status) { 'ACTIVE' => 'Aktif', 'RESOLVED' => 'Selesai', default => $letter->status };
            @endphp
            <tr>
              <td><span class="badge badge-{{ $letter->type->value === 'SP1' ? 'yellow' : 'red' }}">{{ $letter->type->value }}</span></td>
              <td>
                <a href="{{ route('counselor.students.show', $letter->student_id) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $letter->student?->full_name }}
                </a>
                <div class="text-[11px] text-bluedark/45">{{ $letter->student?->currentEnrollment?->schoolClass?->name ?? '—' }}</div>
              </td>
              <td class="text-xs whitespace-nowrap">{{ $letter->issued_at->format('d M Y') }}</td>
              <td class="text-xs max-w-[240px] truncate" title="{{ $letter->reason }}">{{ $letter->reason }}</td>
              <td class="text-xs">{{ $letter->issuer?->name ?? '—' }}</td>
              <td><x-bk.status-badge :label="$label" :tone="$tone" :dot="true" /></td>
              <td>
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('counselor.disciplinary-letters.show', $letter) }}" class="btn btn-outline btn-sm">Detail</a>
                  @if($letter->document_path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($letter->document_path) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Berkas</a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-8 text-xs text-bluedark/50">Belum ada surat peringatan yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$letters" />
  </div>

  <div class="panel p-5">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Saran Surat Peringatan</h2>
        <p class="text-xs text-bluedark/50">
          @if($setting)
            Ambang SP1 &le; {{ $setting->sp1_threshold }} &middot; SP2 &le; {{ $setting->sp2_threshold }} &middot; SP3 &le; {{ $setting->sp3_threshold }}
          @else
            Pengaturan poin belum dibuat untuk tahun ajaran ini
          @endif
        </p>
      </div>
      <span class="badge badge-blue">Tahun {{ $academicYear?->name ?? 'belum diatur' }}</span>
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Siswa</th>
            <th>Kelas</th>
            <th class="text-center">Saldo Poin</th>
            <th>Saran</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php
              $suggestions = $pointHints->filter(fn (array $hint) => $hint['suggested'] !== null);
          @endphp
          @forelse($suggestions as $studentId => $hint)
            @php($student = $students->firstWhere('id', $studentId))
            <tr>
              <td>
                <a href="{{ route('counselor.students.show', $studentId) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $student?->full_name ?? 'Siswa #'.$studentId }}
                </a>
              </td>
              <td class="text-xs">{{ $student?->currentEnrollment?->schoolClass?->name ?? '—' }}</td>
              <td class="text-center">
                <span class="font-heading font-bold text-sm {{ $hint['balance'] < 0 ? 'text-red-600' : 'text-bluedark' }}">{{ $hint['balance'] }}</span>
              </td>
              <td><span class="badge badge-{{ $hint['suggested'] === 'SP1' ? 'yellow' : 'red' }}">Terbitkan {{ $hint['suggested'] }}</span></td>
              <td class="text-right">
                <button type="button" class="btn btn-primary btn-sm"
                        data-modal-open="letterModal"
                        data-preset-student="{{ $studentId }}"
                        data-preset-type="{{ $hint['suggested'] }}">Terbitkan</button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">Tidak ada siswa yang menyentuh ambang Surat Peringatan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal-overlay" id="letterModal" role="dialog" aria-modal="true">
    <div class="modal-box max-w-2xl">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="font-heading font-bold text-bluedark">Terbitkan Surat Peringatan</h3>
          <p class="text-xs text-bluedark/50 mt-0.5">Tahun ajaran {{ $academicYear?->name ?? 'belum diatur' }}</p>
        </div>
        <button type="button" data-modal-close class="text-bluedark/40 hover:text-bluedark" aria-label="Tutup">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 p-3 mb-4">
          <ul class="text-xs text-red-700 space-y-0.5">
            @foreach($errors->all() as $message)
              <li>{{ $message }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('counselor.disciplinary-letters.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="f-label" for="letter_student_id">Siswa <span class="text-red-500">*</span></label>
            <select id="letter_student_id" name="student_id" required class="f-select">
              <option value="">Pilih siswa</option>
              @foreach($students as $student)
                <option value="{{ $student->id }}" data-balance="{{ $pointHints[$student->id]['balance'] ?? '' }}">
                  {{ $student->full_name }} — {{ $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
                </option>
              @endforeach
            </select>
            <p class="text-[11px] text-bluedark/45 mt-1" id="balanceHint">Saldo poin siswa akan tampil di sini.</p>
            @error('student_id')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="f-label" for="letter_type">Jenis SP <span class="text-red-500">*</span></label>
            <select id="letter_type" name="type" required class="f-select">
              <option value="">Pilih jenis</option>
              @foreach(\App\Enums\DisciplinaryLetterType::cases() as $case)
                <option value="{{ $case->value }}" @selected(old('type') === $case->value)>
                  {{ $case->value }} — {{ match ($case->value) { 'SP1' => 'Peringatan pertama', 'SP2' => 'Peringatan kedua', default => 'Peringatan ketiga / terakhir' } }}
                </option>
              @endforeach
            </select>
            @error('type')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="f-label" for="issued_at">Tanggal Terbit <span class="text-red-500">*</span></label>
            <input type="date" id="issued_at" name="issued_at" value="{{ old('issued_at', now()->toDateString()) }}" required class="f-input">
            @error('issued_at')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="f-label" for="document">Berkas Scan (PDF/FOTO)</label>
            <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" class="f-input">
            <p class="text-[11px] text-bluedark/45 mt-1">Maks. 2 MB. Dokumen hasil pindai.</p>
            @error('document')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="sm:col-span-2">
            <label class="f-label" for="reason">Alasan Penerbitan <span class="text-red-500">*</span></label>
            <textarea id="reason" name="reason" rows="3" required class="f-textarea"
                      placeholder="Contoh: Akumulasi 4 pelanggaran Absensi dan 2 pelanggaran Sikap pada semester ini">{{ old('reason') }}</textarea>
            @error('reason')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="sm:col-span-2">
            <label class="f-label" for="notes">Catatan Internal (opsional)</label>
            <textarea id="notes" name="notes" rows="2" class="f-textarea"
                      placeholder="Contoh: Sudah melakukan home visit dan telah dikonfirmasi ke wali kelas">{{ old('notes') }}</textarea>
            @error('notes')
              <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="flex gap-2 mt-5">
          <button type="submit" class="btn btn-primary flex-1">Terbitkan Surat</button>
          <button type="button" data-modal-close class="btn btn-outline">Batal</button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  // Prasetel dari tombol "Terbitkan" pada tabel saran SP.
  document.addEventListener('bk:before-open', function (event) {
    var opener = event.target.closest('[data-modal-open]');

    if (!opener || opener.getAttribute('data-modal-open') !== 'letterModal') return;

    var studentSelect = document.getElementById('letter_student_id');
    var typeSelect = document.getElementById('letter_type');

    if (opener.dataset.presetStudent && studentSelect) {
      studentSelect.value = opener.dataset.presetStudent;
      studentSelect.dispatchEvent(new Event('change'));
    }

    if (opener.dataset.presetType && typeSelect) {
      typeSelect.value = opener.dataset.presetType;
    }
  });

  document.addEventListener('change', function (event) {
    if (event.target.id !== 'letter_student_id') return;

    var option = event.target.options[event.target.selectedIndex];
    var hint = document.getElementById('balanceHint');

    hint.textContent = option && option.dataset.balance !== ''
      ? 'Saldo poin siswa saat ini: ' + option.dataset.balance
      : 'Saldo poin siswa akan tampil di sini.';
  });

  @if($errors->any())
    openModal('letterModal');
  @endif
</script>
@endpush
