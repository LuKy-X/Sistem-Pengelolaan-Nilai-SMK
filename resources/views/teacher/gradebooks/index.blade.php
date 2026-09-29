@extends('layouts.teacher')

@section('title', 'Buku Nilai Digital — Guru')

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

  <!-- Header & Actions -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Buku Nilai Digital</h1>
      <p class="text-sm text-bluedark/60 mt-1">Kelola Buku Nilai &amp; Struktur Rekapitulasi Nilai Siswa</p>
    </div>
    <div>
      <a href="{{ route('teacher.gradebooks.create') }}" class="btn btn-primary btn-sm shadow-xs">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Buat Buku Nilai Baru</span>
      </a>
    </div>
  </div>

  <!-- Filter Kelas Pills -->
  <div class="panel p-4 flex flex-wrap items-center gap-2">
    <span class="text-xs font-bold text-bluedark mr-1">Filter Rombel:</span>
    <a href="{{ route('teacher.gradebooks.index') }}" 
       class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ empty($selectedAssignmentId) ? 'bg-bluedark text-white' : 'bg-bluelight/60 text-bluedark hover:bg-bluelight' }}">
      Semua Kelas ({{ $assignments->count() }} Rombel)
    </a>
    @foreach($assignments as $assign)
      <a href="{{ route('teacher.gradebooks.index', ['assignment_id' => $assign->id]) }}" 
         class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $selectedAssignmentId == $assign->id ? 'bg-bluedark text-white' : 'bg-bluelight/60 text-bluedark hover:bg-bluelight' }}">
        {{ $assign->schoolClass?->name }} &middot; {{ $assign->subject?->code ?? $assign->subject?->name }}
      </a>
    @endforeach
  </div>

  @if(empty($selectedAssignmentId))
    {{-- Tampilkan Buku Nilai Dikelompokkan Per Kelas --}}
    <div class="space-y-8">
      @forelse($assignments as $assign)
        @php
          $classGradebooks = $gradebooks->where('teaching_assignment_id', $assign->id);
          $class = $assign->schoolClass;
          $subject = $assign->subject;
          $semester = $assign->semester;
          $academicYear = $semester?->academicYear;
        @endphp
        <div class="panel p-5 bg-white border border-bluelight/80 rounded-2xl shadow-2xs">
          <!-- Class Section Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-5 border-b border-bluelight/70">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-heading font-bold text-sm">
                {{ substr($class?->name ?? 'K', 0, 3) }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h2 class="font-heading font-bold text-bluedark text-base md:text-lg">
                    {{ $class?->name }}
                  </h2>
                  <span class="badge badge-blue text-[11px]">{{ $subject?->name }}</span>
                </div>
                <p class="text-xs text-bluedark/50 mt-0.5">
                  {{ $class?->department?->name }} &middot; Semester {{ $semester?->name }} ({{ $academicYear?->name }})
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <span class="badge badge-gray text-xs font-semibold">
                {{ $classGradebooks->count() }} Buku Nilai
              </span>
              <a href="{{ route('teacher.gradebooks.create', ['assignment_id' => $assign->id]) }}" class="btn btn-outline btn-sm text-xs">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Buku Nilai Baru</span>
              </a>
            </div>
          </div>

          <!-- Cards Grid for This Class -->
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($classGradebooks as $gb)
              @php
                $colCount = $gb->columns->count();
                $studentCount = $gb->students->count() ?: ($class?->enrollments?->count() ?? 36);
              @endphp
              <div class="panel p-4 flex flex-col justify-between border border-bluelight hover:border-blueprim transition-all hover:shadow-xs bg-slate-50/50 rounded-xl">
                <div>
                  <div class="flex items-start justify-between gap-2 mb-2.5">
                    <div class="min-w-0">
                      <h3 class="font-heading font-bold text-bluedark text-sm line-clamp-1" title="{{ $gb->name }}">
                        {{ $gb->name }}
                      </h3>
                      <p class="text-[11px] text-bluedark/50">
                        {{ $colCount }} Kolom &middot; {{ $studentCount }} Siswa
                      </p>
                    </div>
                    @if($gb->is_active)
                      <span class="badge badge-green text-[9px] px-1.5 py-0.5">Aktif</span>
                    @else
                      <span class="badge badge-gray text-[9px] px-1.5 py-0.5">Nonaktif</span>
                    @endif
                  </div>

                  @if($gb->description)
                    <p class="text-xs text-bluedark/60 line-clamp-2 mb-3">
                      {{ $gb->description }}
                    </p>
                  @endif

                  <!-- Columns Preview -->
                  <div class="mb-3">
                    <div class="flex flex-wrap gap-1 max-h-14 overflow-hidden">
                      @forelse($gb->columns->take(6) as $col)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $col->column_type->value === 'SUMMARY' ? 'bg-amber-100 text-amber-800' : 'bg-bluelight text-blueprim' }}" title="{{ $col->name }}">
                          {{ $col->code ?? Str::limit($col->name, 5) }}
                        </span>
                      @empty
                        <span class="text-[11px] text-bluedark/40 italic">Belum ada kolom</span>
                      @endforelse
                      @if($colCount > 6)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-200 text-slate-600">
                          +{{ $colCount - 6 }}
                        </span>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-2.5 border-t border-bluelight/70 flex items-center justify-between gap-1.5">
                  <div class="flex items-center gap-1">
                    <a href="{{ route('teacher.gradebooks.edit', $gb) }}" class="btn btn-outline btn-sm py-1 px-2 text-xs" title="Edit Identitas & Kolom">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      <span>Edit</span>
                    </a>
                    <a href="{{ route('teacher.gradebooks.export', $gb) }}" class="btn btn-outline btn-sm py-1 px-2 text-xs" title="Export CSV">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </a>
                    <form action="{{ route('teacher.gradebooks.destroy', $gb) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku nilai ini? Data nilai di dalamnya akan terhapus.');" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm py-1 px-2 text-xs text-red-600 hover:bg-red-50 hover:border-red-300" title="Hapus">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  </div>

                  <a href="{{ route('teacher.gradebooks.show', $gb) }}" class="btn btn-primary btn-sm py-1 px-2.5 text-xs font-semibold">
                    <span>Buka Lembar &rarr;</span>
                  </a>
                </div>
              </div>
            @empty
              <div class="col-span-full py-6 text-center text-xs text-bluedark/40 italic bg-white rounded-xl border border-dashed border-bluelight">
                Belum ada buku nilai untuk kelas {{ $class?->name }}. 
                <a href="{{ route('teacher.gradebooks.create', ['assignment_id' => $assign->id]) }}" class="text-blueprim font-semibold hover:underline ml-1">Buat sekarang &rarr;</a>
              </div>
            @endforelse
          </div>
        </div>
      @empty
        <div class="col-span-full py-16 text-center panel p-8">
          <div class="w-12 h-12 rounded-2xl bg-bluelight flex items-center justify-center text-blueprim mx-auto mb-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
          </div>
          <h3 class="font-heading font-bold text-bluedark text-base mb-1">Belum Ada Kelas Mengajar</h3>
          <p class="text-xs text-bluedark/60 max-w-sm mx-auto mb-4">
            Anda belum memiliki penugasan kelas mengajar aktif. Hubungi bagian kurikulum atau administrator sekolah.
          </p>
        </div>
      @endforelse
    </div>
  @else
    {{-- Tampilan Filtered 1 Kelas --}}
    @php
      $currentAssign = $assignments->firstWhere('id', $selectedAssignmentId);
      $class = $currentAssign?->schoolClass;
      $subject = $currentAssign?->subject;
      $semester = $currentAssign?->semester;
      $academicYear = $semester?->academicYear;
    @endphp

    <div class="panel p-5 bg-white border border-bluelight/80 rounded-2xl shadow-2xs mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-5 border-b border-bluelight/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-heading font-bold text-sm">
            {{ substr($class?->name ?? 'K', 0, 3) }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="font-heading font-bold text-bluedark text-base md:text-lg">
                {{ $class?->name }}
              </h2>
              <span class="badge badge-blue text-[11px]">{{ $subject?->name }}</span>
            </div>
            <p class="text-xs text-bluedark/50 mt-0.5">
              {{ $class?->department?->name }} &middot; Semester {{ $semester?->name }} ({{ $academicYear?->name }})
            </p>
          </div>
        </div>

        <a href="{{ route('teacher.gradebooks.create', ['assignment_id' => $currentAssign?->id]) }}" class="btn btn-outline btn-sm text-xs">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Buku Nilai Baru</span>
        </a>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($gradebooks as $gb)
          @php
            $colCount = $gb->columns->count();
            $studentCount = $gb->students->count() ?: ($class?->enrollments?->count() ?? 36);
          @endphp
          <div class="panel p-5 flex flex-col justify-between hover:border-blueprim transition-all hover:shadow-md bg-white border border-bluelight rounded-xl">
            <div>
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                  <div class="crud-card__icon bg-bluelight text-blueprim">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
                  </div>
                  <div class="min-w-0">
                    <h3 class="font-heading font-bold text-bluedark text-base line-clamp-1" title="{{ $gb->name }}">
                      {{ $gb->name }}
                    </h3>
                    <p class="text-xs text-bluedark/60 font-medium">
                      {{ $class?->name ?? 'Kelas' }} &middot; {{ $subject?->name ?? 'Mapel' }}
                    </p>
                  </div>
                </div>
                @if($gb->is_active)
                  <span class="badge badge-green text-[10px]">Aktif</span>
                @else
                  <span class="badge badge-gray text-[10px]">Nonaktif</span>
                @endif
              </div>

              @if($gb->description)
                <p class="text-xs text-bluedark/50 line-clamp-2 mb-3">
                  {{ $gb->description }}
                </p>
              @endif

              <div class="grid grid-cols-2 gap-2 py-3 border-y border-bluelight/70 text-xs mb-3">
                <div>
                  <span class="text-bluedark/40 block text-[11px]">Semester / TA</span>
                  <span class="font-semibold text-bluedark">
                    {{ $semester?->name ?? 'Gasal' }} {{ $academicYear?->name ?? '2026/2027' }}
                  </span>
                </div>
                <div>
                  <span class="text-bluedark/40 block text-[11px]">Siswa Terdaftar</span>
                  <span class="font-semibold text-bluedark">{{ $studentCount }} Siswa</span>
                </div>
              </div>

              <div class="mb-4">
                <span class="text-[11px] font-semibold text-bluedark/50 block mb-1.5">
                  Struktur Kolom ({{ $colCount }} Kolom):
                </span>
                <div class="flex flex-wrap gap-1 max-h-16 overflow-hidden">
                  @forelse($gb->columns->take(8) as $col)
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $col->column_type->value === 'SUMMARY' ? 'bg-amber-100 text-amber-800' : 'bg-bluelight text-blueprim' }}" title="{{ $col->name }}">
                      {{ $col->code ?? Str::limit($col->name, 6) }}
                    </span>
                  @empty
                    <span class="text-[11px] text-bluedark/40 italic">Belum ada kolom</span>
                  @endforelse
                  @if($colCount > 8)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">
                      +{{ $colCount - 8 }} lainnya
                    </span>
                  @endif
                </div>
              </div>
            </div>

            <div class="pt-3 border-t border-bluelight/60 flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5">
                <a href="{{ route('teacher.gradebooks.edit', $gb) }}" class="btn btn-outline btn-sm py-1 px-2.5 text-xs" title="Edit Identitas & Kolom">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Edit</span>
                </a>
                <a href="{{ route('teacher.gradebooks.export', $gb) }}" class="btn btn-outline btn-sm py-1 px-2 text-xs" title="Export CSV">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
                <form action="{{ route('teacher.gradebooks.destroy', $gb) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku nilai ini? Data nilai di dalamnya akan terhapus.');" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline btn-sm py-1 px-2 text-xs text-red-600 hover:bg-red-50 hover:border-red-300" title="Hapus Buku Nilai">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
              </div>

              <a href="{{ route('teacher.gradebooks.show', $gb) }}" class="btn btn-primary btn-sm py-1 px-3 text-xs">
                <span>Buka Nilai &rarr;</span>
              </a>
            </div>
          </div>
        @empty
          <div class="col-span-full py-12 text-center panel p-6">
            <h3 class="font-heading font-bold text-bluedark text-base mb-1">Belum Ada Buku Nilai Untuk Kelas Ini</h3>
            <p class="text-xs text-bluedark/60 max-w-sm mx-auto mb-4">
              Buat buku nilai baru untuk memulai pengisian dan kalkulasi nilai di kelas ini.
            </p>
            <a href="{{ route('teacher.gradebooks.create', ['assignment_id' => $currentAssign?->id]) }}" class="btn btn-primary btn-sm">
              Buat Buku Nilai Sekarang
            </a>
          </div>
        @endforelse
      </div>
    </div>
  @endif

</div>
@endsection
