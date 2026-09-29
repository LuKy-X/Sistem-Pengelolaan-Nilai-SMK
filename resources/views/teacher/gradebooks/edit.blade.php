@extends('layouts.teacher')

@section('title', 'Edit Buku Nilai — Guru')

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
  @endif

  <!-- Breadcrumb -->
  <p class="text-sm text-bluedark/60">
    <a href="{{ route('teacher.gradebooks.index') }}" class="hover:underline text-blueprim font-medium">Buku Nilai</a> / 
    <span class="font-semibold text-bluedark">Edit: {{ $gradebook->name }}</span>
  </p>

  <!-- Title & Action -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Edit Buku Nilai</h1>
      <p class="text-sm text-bluedark/60 mt-1">
        {{ $gradebook->teachingAssignment?->schoolClass?->name }} &middot; {{ $gradebook->teachingAssignment?->subject?->name }} &middot; {{ $gradebook->teachingAssignment?->semester?->name }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('teacher.gradebooks.show', $gradebook) }}" class="btn btn-outline btn-sm">
        Lihat Lembar Nilai
      </a>
      <a href="{{ route('teacher.gradebooks.index') }}" class="btn btn-outline btn-sm">
        &larr; Kembali
      </a>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri: Form Informasi Buku Nilai -->
    <div class="lg:col-span-1 space-y-6">
      <form action="{{ route('teacher.gradebooks.update', $gradebook) }}" method="POST" class="panel p-6">
        @csrf
        @method('PUT')

        <h3 class="font-heading font-bold text-bluedark text-base mb-4 pb-2 border-b border-bluelight">
          Identitas Buku Nilai
        </h3>

        <div class="space-y-4">
          <div>
            <label class="f-label">Nama Buku Nilai <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="f-input" value="{{ old('name', $gradebook->name) }}" required>
            @error('name')
              <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="f-label">Deskripsi</label>
            <textarea name="description" class="f-textarea" rows="3">{{ old('description', $gradebook->description) }}</textarea>
          </div>

          <div>
            <label class="f-label">Status Aktif</label>
            <div class="field-toggle">
              <span>Buku nilai aktif</span>
              <label class="toggle-switch">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $gradebook->is_active) ? 'checked' : '' }}>
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="pt-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
              Simpan Perubahan
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Kolom Kanan: Pengelolaan Kolom Penilaian -->
    <div class="lg:col-span-2 space-y-6">

      <!-- Panel Daftar Kolom Tersimpan -->
      <div class="panel p-6">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
          <div>
            <h3 class="font-heading font-bold text-bluedark text-base">
              Kolom Penilaian Tersimpan ({{ $gradebook->columns->count() }} Kolom)
            </h3>
            <p class="text-xs text-bluedark/50">Daftar kolom yang tampil pada spreadsheet buku nilai</p>
          </div>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl w-full">
            <thead>
              <tr>
                <th class="w-10 text-center">No</th>
                <th>Nama Kolom</th>
                <th class="text-center w-20">Kode</th>
                <th class="text-center w-28">Tipe</th>
                <th class="text-center w-24">Bobot</th>
                <th class="text-center w-20">Max</th>
                <th class="text-center w-16">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($gradebook->columns as $idx => $col)
                <tr>
                  <td class="text-center font-semibold text-slate-500">{{ $idx + 1 }}</td>
                  <td class="font-semibold text-bluedark text-xs">
                    {{ $col->name }}
                  </td>
                  <td class="text-center">
                    <span class="badge badge-gray text-xs font-mono font-bold">{{ $col->code }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $col->column_type->value === 'SUMMARY' ? 'badge-yellow' : 'badge-blue' }} text-[10px]">
                      {{ $col->column_type->value }}
                    </span>
                  </td>
                  <td class="text-center text-xs font-semibold">{{ $col->weight ?? 0 }}%</td>
                  <td class="text-center text-xs">{{ round($col->max_score) }}</td>
                  <td class="text-center">
                    <form action="{{ route('teacher.gradebooks.columns.destroy', [$gradebook, $col]) }}" method="POST" onsubmit="return confirm('Hapus kolom {{ $col->name }}? Nilai pada kolom ini akan ikut terhapus.');" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus Kolom">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-6 text-xs text-bluedark/40 italic">
                    Belum ada kolom penilaian pada buku nilai ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Panel Tambah Kolom Baru -->
      <div class="panel p-6">
        <h3 class="font-heading font-bold text-bluedark text-base mb-2">
          Tambah Kolom Penilaian Baru
        </h3>
        <p class="text-xs text-bluedark/50 mb-4">Tambahkan kolom tugas, ulangan, atau rata-rata tambahan ke buku nilai ini</p>

        <form action="{{ route('teacher.gradebooks.columns.store', $gradebook) }}" method="POST">
          @csrf
          <div class="grid sm:grid-cols-2 gap-3 mb-3">
            <div>
              <label class="f-label">Nama Kolom <span class="text-red-500">*</span></label>
              <input type="text" name="name" class="f-input" placeholder="cth. Ulangan Harian 3" required>
            </div>
            <div>
              <label class="f-label">Kode Singkat <span class="text-red-500">*</span></label>
              <input type="text" name="code" class="f-input uppercase" placeholder="UH3" required>
            </div>
          </div>

          <div class="grid sm:grid-cols-3 gap-3 mb-4">
            <div>
              <label class="f-label">Tipe Kolom</label>
              <select name="column_type" class="f-select" required>
                <option value="SCORE">Nilai (SCORE)</option>
                <option value="SUMMARY">Rata-rata (SUMMARY)</option>
                <option value="MANUAL">Manual</option>
              </select>
            </div>
            <div>
              <label class="f-label">Bobot Nilai (%)</label>
              <input type="number" name="weight" value="15" min="0" max="100" class="f-input" required>
            </div>
            <div>
              <label class="f-label">Nilai Maksimum</label>
              <input type="number" name="max_score" value="100" min="1" max="100" class="f-input" required>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="btn btn-primary btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tambah Kolom
            </button>
          </div>
        </form>
      </div>

    </div>

  </div>

</div>
@endsection
