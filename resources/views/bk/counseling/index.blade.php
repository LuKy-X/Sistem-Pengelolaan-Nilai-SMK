@extends('layouts.bk')

@section('title', 'Rekam Konseling — BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Rekam Jejak Konseling</h1>
      <p class="text-sm text-bluedark/60 mt-1">Dokumentasikan layanan BK tanpa mengubah saldo poin kedisiplinan siswa</p>
    </div>
    @can('create', App\Models\DisciplineRecord::class)
      <button type="button" class="btn btn-primary" data-modal-open="counselingModal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Catat Konseling
      </button>
    @endcan
  </div>

  <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="kpi-card">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalLogs }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Total Layanan Tahun Ini</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-blueprim/10 text-blueprim">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $studentsWithLogs }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Siswa Dilayani</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-green-100 text-green-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $serviceCounts['HOME_VISIT'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Kunjungan Rumah</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $serviceCounts['PARENT_MEETING'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Pertemuan Orang Tua</div>
      </div>
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.counseling.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[200px]">
        <label class="f-label" for="q">Cari</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama/NIS/ringkasan" class="f-input">
      </div>
      @if($counselorClasses->isNotEmpty())
        <div class="w-full sm:w-44">
          <label class="f-label" for="class_id">Kelas</label>
          <select id="class_id" name="class_id" class="f-select">
            <option value="">Semua Kelas</option>
            @foreach($counselorClasses as $class)
              <option value="{{ $class->id }}" @selected($classIdFilter === $class->id)>{{ $class->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
      <div class="w-full sm:w-52">
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
      <div class="w-full sm:w-48">
        <label class="f-label" for="service_type">Jenis Layanan</label>
        <select id="service_type" name="service_type" class="f-select">
          <option value="">Semua Layanan</option>
          @foreach($serviceTypes as $value => $label)
            <option value="{{ $value }}" @selected($serviceTypeFilter === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $serviceTypeFilter || $studentIdFilter || $classIdFilter)
        <a href="{{ route('counselor.counseling.index') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
      <a href="{{ route('counselor.counseling.index', array_filter(['q' => $search, 'student_id' => $studentIdFilter, 'class_id' => $classIdFilter])) }}"
         class="badge {{ $serviceTypeFilter ? 'badge-gray' : 'badge-blue' }}">Semua ({{ $totalLogs }})</a>
      @foreach($serviceTypes as $value => $label)
        <a href="{{ route('counselor.counseling.index', array_filter(['q' => $search, 'student_id' => $studentIdFilter, 'class_id' => $classIdFilter, 'service_type' => $value])) }}"
           class="badge {{ $serviceTypeFilter === $value ? 'badge-blue' : 'badge-gray' }}">
          {{ $label }} ({{ $serviceCounts[$value] ?? 0 }})
        </a>
      @endforeach
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>Jenis Layanan</th>
            <th>Topik</th>
            <th>Ringkasan</th>
            <th>Petugas</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td class="text-xs whitespace-nowrap">{{ $log->occurred_at->format('d M Y') }}</td>
              <td>
                <a href="{{ route('counselor.students.show', $log->student_id) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $log->student?->full_name }}
                </a>
                <div class="text-[11px] text-bluedark/45">{{ $log->student?->currentEnrollment?->schoolClass?->name ?? '—' }}</div>
              </td>
              <td>
                <x-bk.status-badge
                  :label="$serviceTypes[$log->source_type] ?? $log->source_type"
                  tone="blue"
                  :dot="true" />
              </td>
              <td class="text-xs">{{ $log->category?->name ?? '—' }}</td>
              <td class="text-xs max-w-[280px] truncate" title="{{ $log->description }}">{{ $log->description }}</td>
              <td class="text-xs">{{ $log->creator?->name ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-8 text-xs text-bluedark/50">Belum ada rekam konseling yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$logs" />
  </div>

</div>
@endsection

@can('create', App\Models\DisciplineRecord::class)
@push('modals')
<div class="modal-overlay" id="counselingModal" role="dialog" aria-modal="true">
  <div class="modal-box max-w-2xl w-full">

    <div class="flex items-start justify-between mb-5">
      <div>
        <h3 class="font-heading font-bold text-bluedark text-base">Catat Layanan Konseling</h3>
        <p class="text-xs text-bluedark/50 mt-0.5">
          Tahun ajaran {{ $academicYear?->name ?? 'belum diatur' }}
          &middot; saldo poin siswa <strong class="text-blueprim">tidak berubah</strong>
        </p>
      </div>
      <button type="button" data-modal-close class="shrink-0 ml-4 p-1 rounded-lg text-bluedark/40 hover:text-bluedark hover:bg-bluelight transition-colors" aria-label="Tutup">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    @if($errors->any())
      <div class="rounded-xl bg-red-50 border border-red-200 p-3 mb-4">
        <ul class="text-xs text-red-700 space-y-0.5 list-disc list-inside">
          @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('counselor.counseling.store') }}" method="POST">
      @csrf
      <div class="grid sm:grid-cols-2 gap-4">

        @if($counselorClasses->isNotEmpty())
          <div class="sm:col-span-2">
            <label class="f-label" for="modal_class_id">Kelas</label>
            <select id="modal_class_id" class="f-select">
              <option value="">— Tampilkan semua kelas —</option>
              @foreach($counselorClasses as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
              @endforeach
            </select>
            <p class="text-[11px] text-bluedark/45 mt-1">Pilih kelas untuk menyaring daftar siswa di bawah.</p>
          </div>
        @endif

        <div>
          <label class="f-label" for="counseling_student_search">Cari Siswa</label>
          <input type="search" id="counseling_student_search" class="f-input mb-3"
                 placeholder="Ketik nama atau NIS siswa" autocomplete="off">

          <label class="f-label" for="c_student_id">Siswa <span class="text-red-500">*</span></label>
          <select id="c_student_id" name="student_id" required class="f-select">
            <option value="">Pilih siswa</option>
            @foreach($students as $student)
              <option value="{{ $student->id }}"
                      data-class="{{ $student->currentEnrollment?->schoolClass?->id ?? '' }}"
                      data-search="{{ $student->nis }} {{ $student->nisn }}"
                      @selected(old('student_id') == $student->id)>
                {{ $student->full_name }} — {{ $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
              </option>
            @endforeach
          </select>
          <p class="text-[11px] text-bluedark/45 mt-1" id="counseling_student_count">Ketik nama atau NIS untuk menyaring daftar siswa.</p>
          @error('student_id')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="service_type_modal">Jenis Layanan <span class="text-red-500">*</span></label>
          <select id="service_type_modal" name="service_type" required class="f-select">
            <option value="">Pilih layanan</option>
            @foreach($serviceTypes as $value => $label)
              <option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('service_type')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="c_category_id">Topik <span class="text-red-500">*</span></label>
          <select id="c_category_id" name="category_id" required class="f-select">
            <option value="">Pilih topik</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
          <p class="text-[11px] text-bluedark/45 mt-1">Klasifikasi topik, bukan pengurang poin.</p>
          @error('category_id')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="c_occurred_at">Tanggal <span class="text-red-500">*</span></label>
          <input type="date" id="c_occurred_at" name="occurred_at"
                 value="{{ old('occurred_at', now()->toDateString()) }}" required class="f-input">
          @error('occurred_at')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="sm:col-span-2">
          <label class="f-label" for="summary">Ringkasan Hasil <span class="text-red-500">*</span></label>
          <textarea id="summary" name="summary" rows="4" required class="f-textarea"
                    placeholder="Contoh: Siswa mendiskusikan kendala akademik, menyusun rencana belajar, dan berjanji melapor setiap Senin.">{{ old('summary') }}</textarea>
          <p class="text-[11px] text-bluedark/45 mt-1">Hindari mencatat data pribadi sensitif yang tidak diperlukan.</p>
          @error('summary')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="rounded-xl bg-bluelight/50 border border-bluelight p-3 text-[11px] text-bluedark/70 my-4">
        Rekam konseling disimpan dengan nilai poin <strong>0</strong> sehingga tidak memengaruhi saldo dan ambang Surat Peringatan.
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary flex-1">Simpan Rekam</button>
        <button type="button" data-modal-close class="btn btn-outline">Batal</button>
      </div>
    </form>
  </div>
</div>
@endpush
@endcan

@push('scripts')
<script>
  initStudentPicker({
    search: document.getElementById('counseling_student_search'),
    select: document.getElementById('c_student_id'),
    classFilter: document.getElementById('modal_class_id'),
    feedback: document.getElementById('counseling_student_count')
  });

  @if($errors->any())
    openModal('counselingModal');
  @endif
</script>
@endpush
