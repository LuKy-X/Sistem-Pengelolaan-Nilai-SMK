@extends('layouts.bk')

@section('title', 'Poin Disiplin — BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Poin Disiplin</h1>
      <p class="text-sm text-bluedark/60 mt-1">Catat pelanggaran dan penghargaan, pantau ambang Surat Peringatan</p>
    </div>
    @can('create', App\Models\DisciplineRecord::class)
      <button type="button" class="btn btn-primary" data-modal-open="recordModal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Catat Poin
      </button>
    @endcan
  </div>

  <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="kpi-card">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v5H4zM4 13h16v7H4z"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalRecords }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Total Catatan</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-red-100 text-red-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalViolationPoints }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Poin Pelanggaran</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-green-100 text-green-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalRewardPoints }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Poin Penghargaan</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M8 9h8M8 13h5"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $thresholdCounts['warning'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Siswa Perlu Perhatian</div>
      </div>
    </div>
  </div>

  <div class="grid xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2">
      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Saldo Poin Siswa</h2>
        <p class="text-xs text-bluedark/50 mb-4">
          Diurutkan dari saldo terendah
          @if($setting)
            &middot; awal {{ $setting->initial_points }} poin, minimum {{ $setting->minimum_points }}
          @endif
        </p>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th>Siswa</th>
                <th>Kelas</th>
                <th class="text-center">Saldo</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($balanceStudents as $row)
                <tr>
                  <td>
                    <a href="{{ route('counselor.students.show', $row['student']) }}" class="font-medium text-bluedark hover:text-blueprim">
                      {{ $row['student']->full_name }}
                    </a>
                    <div class="text-[11px] text-bluedark/45">NIS {{ $row['student']->nis }}</div>
                  </td>
                  <td class="text-xs">{{ $row['student']->currentEnrollment?->schoolClass?->name ?? '—' }}</td>
                  <td class="text-center">
                    <span class="font-heading font-bold text-sm {{ $row['balance'] < 0 ? 'text-red-600' : 'text-bluedark' }}">{{ $row['balance'] }}</span>
                  </td>
                  <td><x-bk.point-badge :standing="$row['standing']" :show-balance="false" /></td>
                  <td class="text-right">
                    <a href="{{ route('counselor.discipline.index', ['student_id' => $row['student']->id]) }}" class="btn btn-outline btn-sm" data-no-transition="true">Riwayat</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">Belum ada catatan kedisiplinan tahun ajaran ini.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="panel p-5 h-fit">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Ambang Surat Peringatan</h2>
      <p class="text-xs text-bluedark/50 mb-4">Jumlah siswa yang menyentuh ambang batas tahun ajaran berjalan</p>

      @if($setting)
        <div class="space-y-2.5">
          @foreach([
            ['label' => 'Perlu Perhatian', 'key' => 'warning', 'color' => 'text-amber-600', 'threshold' => $setting->warning_threshold],
            ['label' => 'SP1', 'key' => 'sp1', 'color' => 'text-amber-700', 'threshold' => $setting->sp1_threshold],
            ['label' => 'SP2', 'key' => 'sp2', 'color' => 'text-red-500', 'threshold' => $setting->sp2_threshold],
            ['label' => 'SP3', 'key' => 'sp3', 'color' => 'text-red-700', 'threshold' => $setting->sp3_threshold],
          ] as $tier)
            <div class="flex items-center justify-between rounded-xl border border-bluelight px-3 py-2.5">
              <div>
                <div class="text-xs font-semibold text-bluedark">{{ $tier['label'] }}</div>
                <div class="text-[11px] text-bluedark/45">Saldo ≤ {{ $tier['threshold'] }}</div>
              </div>
              <span class="font-heading text-lg font-bold {{ $tier['color'] }}">{{ $thresholdCounts[$tier['key']] ?? 0 }}</span>
            </div>
          @endforeach
        </div>

        <a href="{{ route('counselor.disciplinary-letters.index') }}" class="btn btn-primary btn-sm w-full mt-4">Terbitkan Surat Peringatan</a>
      @else
        <p class="text-sm text-bluedark/50">Pengaturan poin belum dibuat untuk tahun ajaran ini.</p>
      @endif
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.discipline.index') }}" class="grid sm:grid-cols-2 xl:grid-cols-6 gap-3 mb-4">
      <div>
        <label class="f-label" for="q">Cari</label>
        <input type="search" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nama/NIS/uraian" class="f-input">
      </div>
      <div>
        <label class="f-label" for="student_id">Siswa</label>
        <select id="student_id" name="student_id" class="f-select">
          <option value="">Semua Siswa</option>
          @foreach($students as $student)
            <option value="{{ $student->id }}" @selected($filters['student_id'] === $student->id)>
              {{ $student->full_name }} — {{ $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="f-label" for="category_id">Kategori</label>
        <select id="category_id" name="category_id" class="f-select">
          <option value="">Semua Kategori</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>
              {{ $category->name }} ({{ $category->type->value }})
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="f-label" for="type">Tipe</label>
        <select id="type" name="type" class="f-select">
          <option value="">Semua Tipe</option>
          @foreach(\App\Enums\DisciplineCategoryType::cases() as $case)
            <option value="{{ $case->value }}" @selected($filters['type'] === $case->value)>{{ $case->value }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="f-label" for="date_from">Dari</label>
        <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from']?->toDateString() }}" class="f-input">
      </div>
      <div>
        <label class="f-label" for="date_to">Sampai</label>
        <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to']?->toDateString() }}" class="f-input">
      </div>

      <div class="sm:col-span-2 xl:col-span-6 flex gap-2">
        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
        @if(array_filter($filters))
          <a href="{{ route('counselor.discipline.index') }}" class="btn btn-outline">Reset</a>
        @endif
      </div>
    </form>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>Kategori</th>
            <th>Poin</th>
            <th>Uraian</th>
            <th>Sumber</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($records as $record)
            <tr>
              <td class="text-xs whitespace-nowrap">{{ $record->occurred_at->format('d M Y') }}</td>
              <td>
                <a href="{{ route('counselor.students.show', $record->student_id) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $record->student?->full_name }}
                </a>
                <div class="text-[11px] text-bluedark/45">{{ $record->student?->currentEnrollment?->schoolClass?->name ?? '—' }}</div>
              </td>
              <td class="text-xs">{{ $record->category?->name ?? '—' }}</td>
              <td>
                <span class="font-semibold text-xs {{ $record->points_delta < 0 ? 'text-red-600' : 'text-green-600' }}">
                  {{ $record->points_delta > 0 ? '+' : '' }}{{ $record->points_delta }}
                </span>
              </td>
              <td class="text-xs max-w-[260px] truncate" title="{{ $record->description }}">{{ $record->description }}</td>
              <td class="text-[11px] text-bluedark/50">
                {{ $record->source_type ?? 'MANUAL' }}
                @if($record->creator)
                  <div class="text-bluedark/40">oleh {{ $record->creator->name }}</div>
                @endif
              </td>
              <td class="text-right">
                @can('delete', $record)
                  <form action="{{ route('counselor.discipline.destroy', $record) }}" method="POST" class="inline"
                        onsubmit="return confirm('Hapus catatan ini? Saldo poin siswa akan dikoreksi.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-8 text-xs text-bluedark/50">Tidak ada catatan yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$records" />
  </div>

</div>
@endsection

@can('create', App\Models\DisciplineRecord::class)
@push('modals')
<div class="modal-overlay" id="recordModal" role="dialog" aria-modal="true">
  <div class="modal-box max-w-2xl w-full">

    <div class="flex items-start justify-between mb-5">
      <div>
        <h3 class="font-heading font-bold text-bluedark text-base">Catat Poin Kedisiplinan</h3>
        <p class="text-xs text-bluedark/50 mt-0.5">Tahun ajaran {{ $academicYear?->name ?? 'belum diatur' }}</p>
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

    <form action="{{ route('counselor.discipline.store') }}" method="POST">
      @csrf
      <div class="grid sm:grid-cols-2 gap-4">

        @if($counselorClasses->isNotEmpty())
          <div class="sm:col-span-2">
            <label class="f-label" for="record_class_id">Kelas</label>
            <select id="record_class_id" class="f-select">
              <option value="">— Tampilkan semua kelas —</option>
              @foreach($counselorClasses as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
              @endforeach
            </select>
            <p class="text-[11px] text-bluedark/45 mt-1">Pilih kelas untuk menyaring daftar siswa di bawah.</p>
          </div>
        @endif

        <div>
          <label class="f-label" for="record_student_search">Cari Siswa</label>
          <input type="search" id="record_student_search" class="f-input mb-3"
                 placeholder="Ketik nama atau NIS siswa" autocomplete="off">

          <label class="f-label" for="student_id_modal">Siswa <span class="text-red-500">*</span></label>
          <select id="student_id_modal" name="student_id" required class="f-select">
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
          <p class="text-[11px] text-bluedark/45 mt-1" id="record_student_count">Ketik nama atau NIS untuk menyaring daftar siswa.</p>
          @error('student_id')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="category_id_modal">Kategori <span class="text-red-500">*</span></label>
          <select id="category_id_modal" name="category_id" required class="f-select">
            <option value="">Pilih kategori</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" data-points="{{ $category->default_points }}" @selected(old('category_id') == $category->id)>
                {{ $category->name }} ({{ $category->type->value }})
              </option>
            @endforeach
          </select>
          @error('category_id')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="points_delta">Besar Poin <span class="text-red-500">*</span></label>
          <input type="number" id="points_delta" name="points_delta" value="{{ old('points_delta') }}" required
                 min="0" max="1000" class="f-input" placeholder="Ikuti kategori">
          <p class="text-[11px] text-bluedark/45 mt-1">Pelanggaran otomatis dikurangi, penghargaan ditambahkan.</p>
          @error('points_delta')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="occurred_at">Tanggal Kejadian <span class="text-red-500">*</span></label>
          <input type="date" id="occurred_at" name="occurred_at" value="{{ old('occurred_at', now()->toDateString()) }}" required class="f-input">
          @error('occurred_at')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="sm:col-span-2">
          <label class="f-label" for="description">Uraian <span class="text-red-500">*</span></label>
          <textarea id="description" name="description" rows="3" required class="f-textarea"
                    placeholder="Contoh: Terlambat masuk pelajaran PJOK 15 menit tanpa keterangan">{{ old('description') }}</textarea>
          @error('description')
            <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="flex gap-2 mt-5">
        <button type="submit" class="btn btn-primary flex-1">Simpan Catatan</button>
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
    search: document.getElementById('record_student_search'),
    select: document.getElementById('student_id_modal'),
    classFilter: document.getElementById('record_class_id'),
    feedback: document.getElementById('record_student_count')
  });

  document.addEventListener('change', function (event) {
    var select = event.target.closest('#category_id_modal');

    if (!select) return;

    var option = select.options[select.selectedIndex];
    var pointsInput = document.getElementById('points_delta');

    if (option && pointsInput && option.dataset.points) {
      pointsInput.value = Math.abs(parseInt(option.dataset.points, 10) || 0);
    }
  });

  @if($errors->any() || request()->boolean('record'))
    openModal('recordModal');
  @endif
</script>
@endpush
