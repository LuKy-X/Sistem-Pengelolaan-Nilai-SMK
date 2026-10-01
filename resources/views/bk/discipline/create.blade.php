@extends('layouts.bk')

@section('title', 'Catat Poin — BK')

@section('content')
<div class="space-y-6">

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.discipline.index') }}" class="font-semibold text-blueprim hover:underline">Poin Disiplin</a>
    /
    <span class="font-semibold text-bluedark">Catat Poin</span>
  </p>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2">
      <div class="panel p-6">
        <h1 class="font-heading font-bold text-bluedark text-lg">Catat Poin Kedisiplinan</h1>
        <p class="text-xs text-bluedark/50 mt-1 mb-5">
          Tahun ajaran {{ $academicYear?->name ?? 'belum diatur' }} — perubahan poin langsung memengaruhi saldo dan ambang Surat Peringatan.
        </p>

        @if($errors->any())
          <div class="rounded-xl bg-red-50 border border-red-200 p-3 mb-5">
            <p class="text-xs font-semibold text-red-700 mb-1">Periksa kembali isian berikut:</p>
            <ul class="text-xs text-red-700 space-y-0.5">
              @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('counselor.discipline.store') }}" method="POST" class="space-y-4">
          @csrf

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="f-label" for="student_id">Siswa <span class="text-red-500">*</span></label>
              <select id="student_id" name="student_id" required class="f-select">
                <option value="">Pilih siswa</option>
                @foreach($students as $student)
                  <option value="{{ $student->id }}" @selected(old('student_id', $prefill['student_id']) === $student->id)>
                    {{ $student->full_name }} — {{ $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
                  </option>
                @endforeach
              </select>
              @error('student_id')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="f-label" for="category_id">Kategori <span class="text-red-500">*</span></label>
              <select id="category_id" name="category_id" required class="f-select">
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}"
                          data-points="{{ $category->default_points }}"
                          data-type="{{ $category->type->value }}"
                          @selected(old('category_id', $prefill['category_id']) === $category->id)>
                    {{ $category->name }} ({{ $category->type->value }})
                  </option>
                @endforeach
              </select>
              <p class="text-[11px] text-bluedark/45 mt-1" id="categoryHint">Pilih kategori untuk melihat besar poin default.</p>
              @error('category_id')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="f-label" for="points_delta">Besar Poin <span class="text-red-500">*</span></label>
              <input type="number" id="points_delta" name="points_delta" value="{{ old('points_delta') }}"
                     required min="0" max="1000" class="f-input" placeholder="Ikuti kategori">
              <p class="text-[11px] text-bluedark/45 mt-1">Pelanggaran otomatis dikurangi, penghargaan ditambahkan.</p>
              @error('points_delta')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="f-label" for="occurred_at">Tanggal Kejadian <span class="text-red-500">*</span></label>
              <input type="date" id="occurred_at" name="occurred_at"
                     value="{{ old('occurred_at', $prefill['occurred_at']) }}" required class="f-input">
              @error('occurred_at')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="sm:col-span-2">
              <label class="f-label" for="description">Uraian <span class="text-red-500">*</span></label>
              <textarea id="description" name="description" rows="4" required class="f-textarea"
                        placeholder="Contoh: Mencontek pada ulangan harian Matematika kelas X">{{ old('description') }}</textarea>
              @error('description')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary">Simpan Catatan</button>
            <a href="{{ route('counselor.discipline.index') }}" class="btn btn-outline">Batal</a>
          </div>
        </form>
      </div>
    </div>

    <div class="space-y-5">
      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Panduan Penilaian</h2>
        <ul class="text-xs text-bluedark/65 space-y-2">
          <li class="flex gap-2"><span class="text-red-500 font-bold">&minus;</span><span>Pelanggaran mengurangi saldo poin siswa.</span></li>
          <li class="flex gap-2"><span class="text-green-600 font-bold">+</span><span>Penghargaan menambah saldo poin siswa.</span></li>
          <li class="flex gap-2"><span class="text-bluedark/40">&bull;</span><span>Saldo tidak dapat turun di bawah nilai minimum pengaturan.</span></li>
          <li class="flex gap-2"><span class="text-bluedark/40">&bull;</span><span>Setiap perubahan tercatat pada log audit sistem.</span></li>
        </ul>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Kategori Tersedia</h2>
        <div class="space-y-2">
          @foreach($categories->groupBy(fn ($category) => $category->type->value) as $type => $group)
            <div>
              <div class="text-[11px] font-semibold uppercase tracking-wide text-bluedark/45 mb-1">{{ $type }}</div>
              @foreach($group as $category)
                <div class="flex items-center justify-between text-xs py-1">
                  <span class="text-bluedark/70 truncate">{{ $category->name }}</span>
                  <span class="font-semibold {{ $category->default_points < 0 ? 'text-red-600' : 'text-green-600' }} flex-shrink-0 ml-2">
                    {{ $category->default_points }}
                  </span>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('change', function (event) {
    if (event.target.id !== 'category_id') return;

    var option = event.target.options[event.target.selectedIndex];
    var pointsInput = document.getElementById('points_delta');
    var hint = document.getElementById('categoryHint');

    if (!option || !option.value) return;

    pointsInput.value = Math.abs(parseInt(option.dataset.points, 10) || 0);
    hint.textContent = option.dataset.type === 'REWARD'
      ? 'Kategori penghargaan: poin akan ditambahkan ke saldo siswa.'
      : 'Kategori pelanggaran: poin akan dikurangi dari saldo siswa.';
  });
</script>
@endpush
