@extends('layouts.student')

@section('title', 'Buku Saku Siswa')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Buku Saku Siswa</h1>
    <p class="text-sm text-bluedark/60 mt-1">Poin kedisiplinan, aturan sekolah, dan riwayat catatan perilaku Anda{{ $academicYear ? ' pada '.$academicYear->name : '' }}.</p>
  </div>

  <div class="grid md:grid-cols-3 gap-4">
    <div class="panel p-5 text-center">
      <div class="text-[11px] font-semibold uppercase tracking-wide text-bluedark/50">Saldo Poin Saat Ini</div>
      <div class="font-heading text-4xl font-bold mt-2 {{ $standing['tone'] === 'safe' ? 'text-emerald-600' : ($standing['tone'] === 'watch' || $standing['tone'] === 'warning' ? 'text-amber-600' : 'text-red-600') }}">{{ $balance }}</div>
      <span class="badge {{ $standing['badge'] }} mt-2">{{ $standing['label'] }}</span>
      <p class="text-[10px] text-bluedark/45 mt-2">Poin awal {{ $setting?->initial_points ?? 100 }}, dihitung dari seluruh catatan pelanggaran dan penghargaan.</p>
    </div>

    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Ambang Batas Peringatan</h2>
      @if($setting)
        <dl class="info-list">
          <div class="info-list__row">
            <dt>Peringatan Awal</dt>
            <dd>: poin &le; {{ $setting->warning_threshold ?? '-' }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Surat Peringatan 1</dt>
            <dd>: poin &le; {{ $setting->sp1_threshold ?? '-' }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Surat Peringatan 2</dt>
            <dd>: poin &le; {{ $setting->sp2_threshold ?? '-' }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Surat Peringatan 3</dt>
            <dd>: poin &le; {{ $setting->sp3_threshold ?? '-' }}</dd>
          </div>
        </dl>
      @else
        <p class="text-xs text-bluedark/50">Pengaturan poin tahun ajaran ini belum diatur admin.</p>
      @endif
    </div>

    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Surat Peringatan</h2>
      @forelse($letters as $letter)
        <div class="flex items-center gap-2.5 p-2.5 rounded-xl border border-red-100 bg-red-50/50 mb-2">
          <span class="badge badge-red">{{ $letter->type->value }}</span>
          <div class="min-w-0">
            <div class="text-xs font-semibold text-bluedark truncate">{{ $letter->reason }}</div>
            <div class="text-[10px] text-bluedark/45">Diterbitkan {{ $letter->issued_at?->format('d M Y') }}</div>
          </div>
        </div>
      @empty
        <p class="text-xs text-bluedark/50">Tidak ada surat peringatan. Pertahankan!</p>
      @endforelse
    </div>
  </div>

  <div class="panel p-4 lg:p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Aturan Sekolah &amp; Bobot Poin</h2>
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <div class="text-[11px] font-semibold text-red-600 uppercase tracking-wide mb-2">Pelanggaran (poin berkurang)</div>
        <div class="flex flex-col gap-1.5">
          @forelse($ruleCategories->get('VIOLATION', collect()) as $category)
            <div class="flex items-center justify-between gap-2 p-2 rounded-lg border border-bluelight/80">
              <div class="min-w-0">
                <div class="text-xs font-semibold text-bluedark">{{ $category->name }}</div>
                @if($category->description)<div class="text-[10px] text-bluedark/45">{{ $category->description }}</div>@endif
              </div>
              <span class="badge badge-red shrink-0">{{ $category->default_points }}</span>
            </div>
          @empty
            <p class="text-xs text-bluedark/45">Belum ada kategori pelanggaran.</p>
          @endforelse
        </div>
      </div>
      <div>
        <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-2">Penghargaan (poin bertambah)</div>
        <div class="flex flex-col gap-1.5">
          @forelse($ruleCategories->get('REWARD', collect()) as $category)
            <div class="flex items-center justify-between gap-2 p-2 rounded-lg border border-bluelight/80">
              <div class="min-w-0">
                <div class="text-xs font-semibold text-bluedark">{{ $category->name }}</div>
                @if($category->description)<div class="text-[10px] text-bluedark/45">{{ $category->description }}</div>@endif
              </div>
              <span class="badge badge-green shrink-0">+{{ $category->default_points }}</span>
            </div>
          @empty
            <p class="text-xs text-bluedark/45">Belum ada kategori penghargaan.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="panel p-4 lg:p-5 overflow-x-auto">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Riwayat Catatan</h2>
    <table class="w-full text-xs min-w-[560px]">
      <thead>
        <tr class="text-left text-bluedark/50 border-b border-bluelight">
          <th class="py-2 pr-3 font-semibold">Tanggal</th>
          <th class="py-2 pr-3 font-semibold">Kategori</th>
          <th class="py-2 pr-3 font-semibold">Keterangan</th>
          <th class="py-2 font-semibold text-right">Poin</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $record)
          <tr class="border-b border-bluelight/60">
            <td class="py-2.5 pr-3 text-bluedark/70 whitespace-nowrap">{{ $record->occurred_at?->format('d M Y') }}</td>
            <td class="py-2.5 pr-3 font-semibold text-bluedark">{{ $record->category?->name ?? '-' }}</td>
            <td class="py-2.5 pr-3 text-bluedark/70">{{ $record->description ?? '-' }}</td>
            <td class="py-2.5 text-right">
              <span class="badge {{ $record->points_delta >= 0 ? 'badge-green' : 'badge-red' }}">{{ $record->points_delta >= 0 ? '+'.$record->points_delta : $record->points_delta }}</span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="py-6 text-center text-bluedark/50">Belum ada catatan pelanggaran maupun penghargaan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt-4">{{ $records->links() }}</div>
  </div>

</div>
@endsection
