@extends('layouts.teacher')

@section('title', 'Daftar Nilai — ' . ($gradebook->teachingAssignment?->schoolClass?->name ?? 'Kelas'))

@section('content')
<div class="space-y-6">


  <!-- Breadcrumb & Navigasi Kembali -->
  <div class="flex items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-xs sm:text-sm text-bluedark/60 flex-wrap">
      <a href="{{ route('teacher.gradebooks.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-bluelight text-xs font-semibold text-bluedark/70 hover:text-blueprim hover:border-blueprim transition-all shadow-2xs">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Kembali ke Buku Nilai</span>
      </a>
      <span class="text-bluedark/30">/</span>
      <a href="{{ route('teacher.gradebooks.index', ['assignment_id' => $gradebook->teaching_assignment_id]) }}" class="hover:text-blueprim font-medium text-bluedark/70">
        {{ $gradebook->teachingAssignment?->schoolClass?->name }}
      </a>
      <span class="text-bluedark/30">/</span>
      <span class="font-semibold text-bluedark">{{ $gradebook->name }}</span>
    </div>
  </div>

  <!-- Title & Action Buttons -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">
          Daftar Nilai: {{ $gradebook->name }}
        </h1>
        @if($gradebook->is_active)
          <span class="badge badge-green text-xs font-semibold">Aktif</span>
        @else
          <span class="badge badge-gray text-xs font-semibold">Nonaktif</span>
        @endif
      </div>
      <p class="text-xs text-bluedark/60 mt-1">
        Kelas <strong>{{ $gradebook->teachingAssignment?->schoolClass?->name }}</strong> ({{ $gradebook->teachingAssignment?->schoolClass?->department?->name }}) &middot; 
        Mata Pelajaran: <strong>{{ $gradebook->teachingAssignment?->subject?->name }}</strong> &middot; 
        Semester {{ $gradebook->teachingAssignment?->semester?->name }} ({{ $gradebook->teachingAssignment?->semester?->academicYear?->name ?? '2026/2027' }})
      </p>
    </div>

    <!-- Tombol Aksi: Rapi, Terstruktur & Proporsional -->
    <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
      {{-- Kelompok Alat: Atur Kolom & Export Excel --}}
      <div class="inline-flex items-center bg-white border border-bluelight/90 rounded-xl p-1 shadow-2xs">
        <a href="{{ route('teacher.gradebooks.edit', $gradebook) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-bluedark/80 hover:text-blueprim hover:bg-bluelight/40 transition-colors" title="Atur dan edit susunan kolom buku nilai">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          <span>Atur Kolom</span>
        </a>
        <div class="w-px h-4 bg-bluelight"></div>
        <a href="{{ route('teacher.gradebooks.export', $gradebook) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-bluedark/80 hover:text-blueprim hover:bg-bluelight/40 transition-colors" title="Unduh rekapitulasi nilai Excel (.xlsx)" data-no-transition="true" download>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span>Export Excel</span>
        </a>
      </div>

      {{-- Navigasi Modul: Manajemen Tugas --}}
      <a href="{{ route('teacher.assessments.index', ['assignment_id' => $gradebook->teaching_assignment_id]) }}" class="btn btn-outline btn-sm text-xs font-semibold gap-1.5" title="Buka manajemen tugas dan ulangan">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/></svg>
        <span>Manajemen Tugas</span>
      </a>

      {{-- Aksi Utama: Buka Penilaian --}}
      <a href="{{ route('teacher.grading.index', ['assignment_id' => $gradebook->teaching_assignment_id, 'gradebook_id' => $gradebook->id]) }}" class="btn btn-primary btn-sm text-xs font-semibold gap-1.5 shadow-xs" title="Buka menu input penilaian siswa">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
        <span>Buka Penilaian</span>
      </a>
    </div>
  </div>

  <!-- Ringkasan Info Cepat (KPI Badges) -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-bold text-xs">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <span class="text-[11px] text-bluedark/50 block">Siswa Terdaftar</span>
        <span class="font-heading font-bold text-bluedark text-sm">{{ $students->count() }} Siswa</span>
      </div>
    </div>

    <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blueprim/10 text-blueprim flex items-center justify-center font-bold text-xs">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
      </div>
      <div>
        <span class="text-[11px] text-bluedark/50 block">Struktur Kolom</span>
        <span class="font-heading font-bold text-bluedark text-sm">{{ $columns->count() }} Kolom</span>
      </div>
    </div>

    <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div>
        <span class="text-[11px] text-bluedark/50 block">Standar KKM</span>
        <span class="font-heading font-bold text-emerald-600 text-sm">75.00</span>
      </div>
    </div>

    <div class="panel p-3.5 bg-white border border-bluelight flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
      </div>
      <div>
        <span class="text-[11px] text-bluedark/50 block">Pengaturan Kolom</span>
        <a href="{{ route('teacher.gradebooks.edit', $gradebook) }}" class="text-xs font-bold text-blueprim hover:underline">
          Ubah Kolom &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Lembar Spreadsheet Nilai (Read-Only) -->
  <div class="panel p-5 bg-white border border-bluelight rounded-2xl shadow-2xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-bluelight">
      <div>
        <h2 class="font-heading font-bold text-bluedark text-base">
          Daftar Nilai Siswa
        </h2>
        <p class="text-xs text-bluedark/50 mt-0.5">
          Tampilan rekapitulasi nilai siswa pada kelas {{ $gradebook->teachingAssignment?->schoolClass?->name }}
        </p>
      </div>

      <div class="flex items-center gap-3 text-xs">
        <span class="flex items-center gap-1.5 text-bluedark/70">
          <span class="w-2.5 h-2.5 rounded-full bg-blueprim inline-block"></span> Nilai Komponen
        </span>
        <span class="flex items-center gap-1.5 text-bluedark/70">
          <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Kalkulasi / Rata-rata
        </span>
      </div>
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl shadow-2xs">
      <table class="tbl w-full text-left">
        <thead>
          <tr style="background:#0D47A1;color:#fff;">
            <th class="text-center w-12 border-r border-blue-900" style="color:#fff;">No</th>
            <th class="w-28 border-r border-blue-900" style="color:#fff;">NIS</th>
            <th class="min-w-[190px] border-r border-blue-900" style="color:#fff;">Nama Siswa</th>
            @forelse($columns as $col)
              @php
                $isSummary = $col->column_type->value === 'SUMMARY';
              @endphp
              <th class="text-center min-w-[95px] border-r border-blue-900 px-2 py-2.5 {{ $isSummary ? 'bg-blue-900/60' : '' }}" title="{{ $col->name }} (Bobot: {{ $col->weight }}%)" style="color:#fff;">
                <div class="flex flex-col items-center">
                  <div class="font-heading font-bold text-xs uppercase">{{ $col->code }}</div>
                  <div class="text-[10px] font-normal opacity-85 truncate max-w-[90px]">{{ $col->name }}</div>
                  <span class="text-[9px] px-1 py-0.2 rounded mt-0.5 {{ $isSummary ? 'bg-amber-400 text-slate-900 font-bold' : 'bg-white/20 text-white' }}">
                    {{ $isSummary ? 'RATA' : ($col->weight . '%') }}
                  </span>
                </div>
              </th>
            @empty
              <th class="text-center text-xs py-3 text-white/70 italic" style="color:#fff;">Belum ada kolom nilai</th>
            @endforelse
          </tr>
        </thead>
        <tbody id="nilaiTableBody">
          @forelse($students as $index => $item)
            <tr class="hover:bg-blue-50/40 transition-colors">
              <td class="text-center font-semibold text-slate-500 text-xs border-r border-slate-100">{{ $index + 1 }}</td>
              <td class="font-mono text-xs text-slate-500 border-r border-slate-100">{{ $item->student?->nis ?? '-' }}</td>
              <td class="font-medium text-bluedark text-xs border-r border-slate-100 py-2.5">
                <span class="font-semibold block">{{ $item->student?->full_name ?? 'Siswa' }}</span>
              </td>
              @foreach($columns as $col)
                @php
                  $val = $scoresMatrix[$item->student_id][$col->id] ?? null;
                  $isSummary = $col->column_type->value === 'SUMMARY';
                  $hasScore = ($val !== null && $val !== '');
                @endphp
                <td class="text-center text-xs border-r border-slate-100 py-2.5 {{ $isSummary ? 'bg-amber-50/50' : '' }}">
                  @if($hasScore)
                    <span class="font-mono font-bold {{ $isSummary ? 'text-blueprim text-sm' : ($val < 75 ? 'text-rose-600' : 'text-slate-800') }}">
                      {{ is_numeric($val) ? (float) $val : $val }}
                    </span>
                  @else
                    <span class="text-slate-300 font-mono text-xs">-</span>
                  @endif
                </td>
              @endforeach
            </tr>
          @empty
            <tr>
              <td colspan="{{ 3 + max(1, $columns->count()) }}" class="text-center py-10 text-xs text-bluedark/40 italic">
                Belum ada siswa yang terdaftar pada buku nilai ini.
              </td>
            </tr>
          @endforelse
        </tbody>
        @if($students->isNotEmpty() && $columns->isNotEmpty())
          <tfoot>
            <tr style="background:#F1F7FD;" class="font-bold border-t-2 border-bluelight text-bluedark">
              <td colspan="3" class="text-right px-4 py-3 text-xs uppercase tracking-wider">
                Rata-rata Kelas:
              </td>
              @foreach($columns as $col)
                @php
                  $avg = $columnAverages[$col->id] ?? '-';
                  $isSummary = $col->column_type->value === 'SUMMARY';
                @endphp
                <td class="text-center py-3 font-mono text-xs border-r border-slate-200 {{ $isSummary ? 'text-blueprim font-bold text-sm bg-blue-50/50' : 'text-bluedark' }}">
                  {{ $avg !== '-' ? (is_numeric($avg) ? number_format((float)$avg, 1) : $avg) : '-' }}
                </td>
              @endforeach
            </tr>
          </tfoot>
        @endif
      </table>
    </div>

    <!-- Footer Information -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 mt-4 border-t border-bluelight/70 text-xs text-bluedark/60">
      <div>
        Menampilkan <strong>{{ $students->count() }} siswa</strong> dan <strong>{{ $columns->count() }} kolom penilaian</strong>.
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('teacher.gradebooks.edit', $gradebook) }}" class="text-blueprim font-semibold hover:underline">
          Edit Susunan Kolom &rarr;
        </a>
      </div>
    </div>
  </div>

</div>
@endsection
