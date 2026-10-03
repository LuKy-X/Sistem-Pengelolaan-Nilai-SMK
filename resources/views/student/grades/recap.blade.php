@extends('layouts.student')

@section('title', 'Rekap Nilai')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Rekap Nilai</h1>
      <p class="text-sm text-bluedark/60 mt-1">Ringkasan nilai seluruh mata pelajaran pada semester berjalan.</p>
    </div>
    <a href="{{ route('student.grades.index') }}" class="btn btn-outline btn-sm shrink-0">Daftar buku nilai</a>
  </div>

  @if($subjects->isEmpty())
    <div class="panel p-6 text-center">
      <p class="text-sm text-bluedark/60">
        Anda belum terdaftar pada buku nilai manapun. Rekap nilai muncul setelah guru menambahkan Anda ke buku nilai kelas.
      </p>
    </div>
  @else
    <div class="panel p-5 lg:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="shrink-0">
          <div class="font-heading text-4xl font-bold {{ $overall['average'] !== null ? ($overall['passing'] ? 'text-emerald-600' : 'text-amber-600') : 'text-bluedark/30' }}">
            {{ $overall['average'] !== null ? rtrim(rtrim(number_format($overall['average'], 2), '0'), '.') : '-' }}
          </div>
          <div class="text-[11px] text-bluedark/50 mt-0.5">Rata-rata lintas mata pelajaran</div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-1 min-w-0">
          <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
            <div class="font-heading text-lg font-bold text-bluedark">{{ $overall['predicate'] ?? '-' }}</div>
            <div class="text-[10px] text-bluedark/50">Predikat</div>
          </div>
          <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
            <div class="font-heading text-lg font-bold text-bluedark">{{ $overall['predicateLabel'] ?? 'Belum ada nilai' }}</div>
            <div class="text-[10px] text-bluedark/50">Keterangan</div>
          </div>
          <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
            <div class="font-heading text-lg font-bold text-bluedark">{{ $overall['subjectCount'] }}</div>
            <div class="text-[10px] text-bluedark/50">Mata pelajaran</div>
          </div>
          <div class="rounded-xl bg-bluelight/40 px-3 py-2.5">
            <div class="font-heading text-lg font-bold text-bluedark">{{ $overall['graded'] }}/{{ $overall['total'] }}</div>
            <div class="text-[10px] text-bluedark/50">Komponen terisi</div>
          </div>
        </div>
      </div>

      <p class="text-[11px] text-bluedark/50 mt-4 pt-3 border-t border-bluelight/70">
        Kriteria ketuntasan minimum {{ rtrim(rtrim(number_format($passingScore, 2), '0'), '.') }}.
        Predikat: A &ge; 90, B &ge; 80, C &ge; {{ rtrim(rtrim(number_format($passingScore, 2), '0'), '.') }}, D &ge; 70, E di bawahnya.
      </p>
    </div>

    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Progres Pengumpulan Tugas</h2>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-xl border border-bluelight/80 px-3 py-2.5">
          <div class="font-heading text-lg font-bold text-bluedark">{{ $tasks['total'] }}</div>
          <div class="text-[10px] text-bluedark/50">Tugas terbit</div>
        </div>
        <div class="rounded-xl border border-bluelight/80 px-3 py-2.5">
          <div class="font-heading text-lg font-bold text-emerald-600">{{ $tasks['submitted'] }}</div>
          <div class="text-[10px] text-bluedark/50">Sudah dikumpulkan</div>
        </div>
        <div class="rounded-xl border border-bluelight/80 px-3 py-2.5">
          <div class="font-heading text-lg font-bold text-bluedark">{{ $tasks['pending'] }}</div>
          <div class="text-[10px] text-bluedark/50">Belum dikumpulkan</div>
        </div>
        <div class="rounded-xl border border-bluelight/80 px-3 py-2.5">
          <div class="font-heading text-lg font-bold {{ $tasks['overdue'] > 0 ? 'text-red-500' : 'text-bluedark' }}">{{ $tasks['overdue'] }}</div>
          <div class="text-[10px] text-bluedark/50">Terlewat tenggat</div>
        </div>
      </div>
    </div>

    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Rincian per Mata Pelajaran</h2>

      {{-- Kartu untuk layar kecil. --}}
      <div class="space-y-2.5 md:hidden">
        @foreach($subjects as $row)
          @php
            $gradebook = $row['gradebook'];
            $letter = $row['predicate'];
          @endphp
          <div class="rounded-xl border border-bluelight/80 px-3 py-2.5">
            <div class="flex items-start justify-between gap-2 mb-1">
              <div class="min-w-0">
                <div class="text-xs font-semibold text-bluedark break-words">{{ $row['subject']?->name ?? 'Mata Pelajaran' }}</div>
                <div class="text-[10px] text-bluedark/45 break-words">{{ $gradebook->name }} &middot; {{ $row['teacher']?->full_name ?? '-' }}</div>
              </div>
              <div class="text-right shrink-0">
                <span class="font-heading font-bold text-bluedark">
                  {{ $row['average'] !== null ? rtrim(rtrim(number_format($row['average'], 2), '0'), '.') : '-' }}
                </span>
                <div class="mt-0.5">
                  <span class="badge {{ match ($letter) { 'A' => 'badge-green', 'B' => 'badge-blue', 'C' => 'badge-yellow', 'D' => 'badge-yellow', 'E' => 'badge-red', default => 'badge-gray' } }}">
                    {{ $letter ?? '-' }}
                  </span>
                </div>
              </div>
            </div>
            <div class="text-[10px] text-bluedark/45 mb-2">Komponen terisi {{ $row['graded'] }}/{{ $row['total'] }}</div>
            <a href="{{ route('student.grades.show', $gradebook) }}" class="btn btn-outline btn-sm w-full">Lihat Rincian</a>
          </div>
        @endforeach
      </div>

      {{-- Tabel untuk layar lebar. --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-xs min-w-[640px]">
          <thead>
            <tr class="text-left text-bluedark/50 border-b border-bluelight">
              <th class="py-2 pr-3 font-semibold">Mata Pelajaran</th>
              <th class="py-2 pr-3 font-semibold">Guru</th>
              <th class="py-2 pr-3 font-semibold text-center">Komponen</th>
              <th class="py-2 pr-3 font-semibold text-center">Rata-rata</th>
              <th class="py-2 pr-3 font-semibold text-center">Predikat</th>
              <th class="py-2 font-semibold"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($subjects as $row)
              @php
                $gradebook = $row['gradebook'];
                $average = $row['average'];
                $letter = $row['predicate'];
                $passing = $row['passing'];
              @endphp
              <tr class="border-b border-bluelight/60">
                <td class="py-2.5 pr-3 max-w-[220px]">
                  <div class="font-semibold text-bluedark break-words">{{ $row['subject']?->name ?? 'Mata Pelajaran' }}</div>
                  <div class="text-[10px] text-bluedark/45">{{ $gradebook->name }}</div>
                </td>
                <td class="py-2.5 pr-3 text-bluedark/60 max-w-[180px] break-words">{{ $row['teacher']?->full_name ?? '-' }}</td>
                <td class="py-2.5 pr-3 text-center text-bluedark/60">{{ $row['graded'] }}/{{ $row['total'] }}</td>
                <td class="py-2.5 pr-3 text-center">
                  <span class="font-heading font-bold {{ $passing ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $average !== null ? rtrim(rtrim(number_format($average, 2), '0'), '.') : '-' }}
                  </span>
                </td>
                <td class="py-2.5 pr-3 text-center">
                  <span class="badge {{ match ($letter) { 'A' => 'badge-green', 'B' => 'badge-blue', 'C' => 'badge-yellow', 'D' => 'badge-yellow', 'E' => 'badge-red', default => 'badge-gray' } }}">
                    {{ $letter ?? '-' }}
                  </span>
                </td>
                <td class="py-2.5 text-right">
                  <a href="{{ route('student.grades.show', $gradebook) }}" class="text-[11px] font-semibold text-blueprim hover:underline">Rincian &rarr;</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

</div>
@endsection