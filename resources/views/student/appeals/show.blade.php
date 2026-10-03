@extends('layouts.student')

@section('title', 'Detail Banding')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <a href="{{ route('student.appeals.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Kembali ke daftar banding</a>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark mt-1">Detail Banding</h1>
  </div>

  @php
    $permit = $appeal->exitPermit;
    $decision = $appeal->decision->value;
    $minutesLate = $permit?->planned_return_at && $permit->actual_return_at
      ? (int) $permit->planned_return_at->diffInMinutes($permit->actual_return_at)
      : null;
  @endphp

  <div class="panel p-5 lg:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0">
        <h2 class="font-heading text-lg font-bold text-bluedark">{{ $permit?->reason?->name ?? 'Izin Keluar' }}</h2>
        <p class="text-xs text-bluedark/55 mt-0.5">Diajukan {{ $appeal->submitted_at?->format('d M Y, H:i') }}</p>
      </div>
      <span class="badge {{ ['PENDING' => 'badge-yellow', 'ACCEPTED' => 'badge-green', 'REJECTED' => 'badge-red'][$decision] ?? 'badge-gray' }}">
        {{ ['PENDING' => 'Diproses', 'ACCEPTED' => 'Diterima', 'REJECTED' => 'Ditolak'][$decision] ?? $decision }}
      </span>
    </div>

    <dl class="info-list mt-4">
      <div class="info-list__row">
        <dt>Alasan izin</dt>
        <dd>{{ $permit?->reason_detail ?? '-' }}</dd>
      </div>
      <div class="info-list__row">
        <dt>Rencana keluar</dt>
        <dd>{{ $permit?->planned_exit_at?->format('d M Y, H:i') ?? '-' }}</dd>
      </div>
      <div class="info-list__row">
        <dt>Rencana kembali</dt>
        <dd>{{ $permit?->planned_return_at?->format('d M Y, H:i') ?? '-' }}</dd>
      </div>
      <div class="info-list__row">
        <dt>Waktu kembali</dt>
        <dd>
          {{ $permit?->actual_return_at?->format('d M Y, H:i') ?? 'Belum tercatat' }}
          @if($minutesLate !== null && $minutesLate > 0)
            <span class="text-red-500 font-semibold">(+{{ $minutesLate }} menit)</span>
          @endif
        </dd>
      </div>
    </dl>

    @if($permit !== null)
      <div class="mt-3 text-right">
        <a href="{{ route('student.exit-permits.show', $permit->id) }}" class="text-[11px] font-semibold text-blueprim hover:underline">Lihat izin terkait &rarr;</a>
      </div>
    @endif
  </div>

  <div class="panel p-4 lg:p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Alasan Banding Anda</h2>
    <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight">
      <p class="text-sm text-bluedark/85 leading-relaxed break-words">{{ $appeal->reason }}</p>
    </div>
  </div>

  <div class="panel p-4 lg:p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Keputusan Guru BK</h2>

    @if($decision === 'PENDING')
      <div class="p-3.5 rounded-xl border border-bluelight bg-bluelight/30">
        <p class="text-sm text-bluedark/70">
          Banding sedang diproses Guru BK. Anda akan melihat keputusan dan catatannya di halaman ini setelah BK memberi putusan.
        </p>
      </div>
    @else
      <div class="p-3.5 rounded-xl {{ $decision === 'ACCEPTED' ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
        <p class="text-[11px] font-semibold {{ $decision === 'ACCEPTED' ? 'text-emerald-700' : 'text-red-700' }} mb-1">
          {{ $decision === 'ACCEPTED' ? 'Banding diterima' : 'Banding ditolak' }}
          &middot; {{ $appeal->decider?->full_name ?? 'Guru BK' }}
          @if($appeal->decided_at)
            &middot; {{ $appeal->decided_at->format('d M Y, H:i') }}
          @endif
        </p>
        <p class="text-sm {{ $decision === 'ACCEPTED' ? 'text-emerald-900' : 'text-red-900' }} leading-relaxed">
          {{ $appeal->decision_note ?? 'Tanpa catatan tambahan.' }}
        </p>
      </div>
    @endif
  </div>

</div>
@endsection