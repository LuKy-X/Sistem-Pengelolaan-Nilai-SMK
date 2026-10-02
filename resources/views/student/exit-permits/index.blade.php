@extends('layouts.student')

@section('title', 'Izin Keluar Sekolah')

@section('content')
@php
  // Warna lencana izin disamakan dengan komponen milik dashboard Guru BK
  // (x-bk.permit-status) supaya siswa dan Guru BK melihat warna yang sama.
  // APPROVED hijau, COMPLETED biru, bukan sebaliknya.
  $statusFilters = [
    'PENDING' => ['label' => 'Menunggu', 'badge' => 'badge-yellow'],
    'APPROVED' => ['label' => 'Disetujui', 'badge' => 'badge-green'],
    'REJECTED' => ['label' => 'Ditolak', 'badge' => 'badge-red'],
    'COMPLETED' => ['label' => 'Sudah Kembali', 'badge' => 'badge-blue'],
    'LATE' => ['label' => 'Kembali Terlambat', 'badge' => 'badge-red'],
    'CANCELLED' => ['label' => 'Dibatalkan', 'badge' => 'badge-gray'],
  ];

  $appealFilters = [
    'PENDING' => ['label' => 'Diproses', 'badge' => 'badge-yellow'],
    'ACCEPTED' => ['label' => 'Diterima', 'badge' => 'badge-green'],
    'REJECTED' => ['label' => 'Ditolak', 'badge' => 'badge-red'],
  ];
@endphp
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Izin Keluar Sekolah</h1>
      <p class="text-sm text-bluedark/60 mt-1">Ajukan izin keluar dan pantau status persetujuan dari Guru BK.</p>
    </div>
    <a href="{{ route('student.exit-permits.create') }}" class="btn btn-primary">+ Ajukan Izin</a>
  </div>

  @if($activePermit)
    <div class="panel p-4 border-l-4 {{ $activePermit->isOverdue() ? 'border-red-400' : 'border-blueprim' }}">
      <div class="flex flex-wrap items-center gap-3 justify-between">
        <div class="min-w-0">
          <div class="text-[11px] font-semibold uppercase tracking-wide {{ $activePermit->isOverdue() ? 'text-red-600' : 'text-blueprim' }}">Izin sedang aktif</div>
          <div class="font-heading font-semibold text-bluedark text-sm mt-0.5">{{ $activePermit->reason?->name }} &middot; rencana kembali {{ $activePermit->planned_return_at?->format('d M Y, H:i') }}</div>
        </div>
        <div class="flex items-center gap-3">
          <span class="countdown-pill ok" data-return-at="{{ $activePermit->planned_return_at?->timestamp }}"><span class="dot"></span>Memuat...</span>
          <a href="{{ route('student.exit-permits.show', $activePermit) }}" class="btn btn-outline btn-sm">Detail</a>
        </div>
      </div>
    </div>
  @endif

  <nav class="flex gap-2 flex-wrap" aria-label="Saring izin berdasarkan status">
    <a href="{{ route('student.exit-permits.index') }}"
       @if($statusFilter === null) aria-current="page" @endif
       class="badge {{ $statusFilter === null ? 'badge-blue' : 'badge-gray' }} !px-3 !py-1.5">Semua ({{ $statusCounts->sum() }})</a>
    @foreach($statusFilters as $statusValue => $meta)
      @if(($statusCounts[$statusValue] ?? 0) > 0 || $statusFilter === $statusValue)
        <a href="{{ route('student.exit-permits.index', ['status' => $statusValue]) }}"
           @if($statusFilter === $statusValue) aria-current="page" @endif
           class="badge {{ $statusFilter === $statusValue ? $meta['badge'] : 'badge-gray' }} !px-3 !py-1.5">{{ $meta['label'] }} ({{ $statusCounts[$statusValue] ?? 0 }})</a>
      @endif
    @endforeach
  </nav>

  <div class="panel p-4 lg:p-5 overflow-x-auto">
    <table class="w-full text-xs min-w-[640px]">
      <thead>
        <tr class="text-left text-bluedark/50 border-b border-bluelight">
          <th class="py-2 pr-3 font-semibold">Keperluan</th>
          <th class="py-2 pr-3 font-semibold">Waktu Keluar</th>
          <th class="py-2 pr-3 font-semibold">Rencana Kembali</th>
          <th class="py-2 pr-3 font-semibold">Status</th>
          <th class="py-2 font-semibold text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($permits as $permit)
          <tr class="border-b border-bluelight/60">
            <td class="py-2.5 pr-3">
              <div class="font-semibold text-bluedark">{{ $permit->reason?->name ?? '-' }}</div>
              <div class="text-[10px] text-bluedark/45 max-w-[220px] truncate">{{ $permit->reason_detail }}</div>
            </td>
            <td class="py-2.5 pr-3 text-bluedark/70">{{ $permit->planned_exit_at?->format('d M Y, H:i') }}</td>
            <td class="py-2.5 pr-3 text-bluedark/70">
              {{ $permit->planned_return_at?->format('d M Y, H:i') }}
              @if(in_array($permit->status->value, ['APPROVED', 'LATE'], true) && $permit->actual_return_at === null)
                <div class="mt-1"><span class="countdown-pill ok" data-return-at="{{ $permit->planned_return_at?->timestamp }}"><span class="dot"></span>Memuat...</span></div>
              @endif
            </td>
            <td class="py-2.5 pr-3">
              <x-bk.permit-status :status="$permit->status" />
              @if($permit->appeal)
                @php $appealMeta = $appealFilters[$permit->appeal->decision->value] ?? ['label' => $permit->appeal->decision->value, 'badge' => 'badge-gray']; @endphp
                <span class="badge {{ $appealMeta['badge'] }}">Banding {{ $appealMeta['label'] }}</span>
              @endif
            </td>
            <td class="py-2.5 text-right">
              <a href="{{ route('student.exit-permits.show', $permit) }}" class="btn btn-outline btn-sm">Detail</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="py-6 text-center text-bluedark/50">Belum ada pengajuan izin keluar.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <x-bk.pagination :paginator="$permits" />
  </div>

</div>
@endsection
