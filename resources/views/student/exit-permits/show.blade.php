@extends('layouts.student')

@section('title', 'Detail Izin Keluar')

@section('content')
@php
  // Status memakai komponen milik dashboard Guru BK supaya warna lencana di
  // halaman siswa dan di halaman BK sama persis.
  $isActive = in_array($permit->status->value, ['APPROVED', 'LATE'], true) && $permit->actual_return_at === null;
  $appeal = $permit->appeal;
@endphp
<div class="space-y-4 lg:space-y-5 max-w-3xl">

  <div>
    <a href="{{ route('student.exit-permits.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Riwayat izin</a>
    <div class="flex flex-wrap items-center gap-2 mt-1">
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Izin Keluar — {{ $permit->reason?->name ?? 'Keperluan' }}</h1>
      <x-bk.permit-status :status="$permit->status" />
    </div>
  </div>
  </div>

  @if($isActive)
    <div class="panel p-5 text-center">
      <div class="text-[11px] font-semibold uppercase tracking-wide text-bluedark/50 mb-2">Timer Kepulangan</div>
      <span class="countdown-pill ok !text-base !px-4 !py-2" data-return-at="{{ $permit->planned_return_at?->timestamp }}"><span class="dot"></span>Memuat...</span>
      <p class="text-[11px] text-bluedark/50 mt-2">Kembali ke sekolah sebelum <span class="font-semibold text-bluedark">{{ $permit->planned_return_at?->format('d M Y, H:i') }}</span>. Keterlambatan tercatat otomatis oleh sistem.</p>
    </div>
  @endif

  <div class="grid md:grid-cols-2 gap-4 lg:gap-5">
    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Detail Pengajuan</h2>
      <dl class="info-list">
        <div class="info-list__row">
          <dt>Alasan</dt>
          <dd>: {{ $permit->reason?->name ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Keperluan</dt>
          <dd>: {{ $permit->reason_detail }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Diajukan</dt>
          <dd>: {{ $permit->requested_at?->format('d M Y, H:i') }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Rencana Keluar</dt>
          <dd>: {{ $permit->planned_exit_at?->format('d M Y, H:i') }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Rencana Kembali</dt>
          <dd>: {{ $permit->planned_return_at?->format('d M Y, H:i') }}</dd>
        </div>
        @if($permit->actual_return_at)
          <div class="info-list__row">
            <dt>Kembali Aktual</dt>
            <dd>: {{ $permit->actual_return_at->format('d M Y, H:i') }}</dd>
          </div>
        @endif
      </dl>
    </div>

    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Keputusan Guru BK</h2>
      @if($permit->status->value === 'PENDING')
        <p class="text-xs text-bluedark/60">Pengajuan Anda sedang menunggu verifikasi Guru BK.</p>
      @else
        <dl class="info-list">
          <div class="info-list__row">
            <dt>Diproses oleh</dt>
            <dd>: {{ $permit->approver?->full_name ?? 'Guru BK' }}</dd>
          </div>
          <div class="info-list__row">
            <dt>Waktu Proses</dt>
            <dd>: {{ $permit->approved_at?->format('d M Y, H:i') ?? '-' }}</dd>
          </div>
        </dl>
        @if($permit->approval_note)
          <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 mt-3">
            <div class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wide mb-1">Catatan Persetujuan</div>
            <p class="text-xs text-emerald-900">{{ $permit->approval_note }}</p>
          </div>
        @endif
        @if($permit->rejection_note)
          <div class="p-3 rounded-xl bg-red-50 border border-red-200 mt-3">
            <div class="text-[10px] font-semibold text-red-700 uppercase tracking-wide mb-1">Alasan Penolakan</div>
            <p class="text-xs text-red-900">{{ $permit->rejection_note }}</p>
          </div>
        @endif
      @endif
    </div>
  </div>

  @if($appeal)
    <div class="panel p-4 lg:p-5">
      <div class="flex items-center gap-2 mb-3">
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Banding Keterlambatan</h2>
        <span class="badge {{ ['PENDING' => 'badge-yellow', 'ACCEPTED' => 'badge-green', 'REJECTED' => 'badge-red'][$appeal->decision->value] ?? 'badge-gray' }}">
          {{ ['PENDING' => 'Sedang Diproses', 'ACCEPTED' => 'Diterima', 'REJECTED' => 'Ditolak'][$appeal->decision->value] }}
        </span>
      </div>
      <div class="p-3 rounded-xl bg-bluelight/40 border border-bluelight mb-3">
        <div class="text-[10px] font-semibold text-bluedark/50 uppercase tracking-wide mb-1">Alasan Anda &middot; {{ $appeal->submitted_at?->format('d M Y, H:i') }}</div>
        <p class="text-xs text-bluedark/80">{{ $appeal->reason }}</p>
      </div>
      @if($appeal->decision->value !== 'PENDING')
        <div class="p-3 rounded-xl {{ $appeal->decision->value === 'ACCEPTED' ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
          <div class="text-[10px] font-semibold {{ $appeal->decision->value === 'ACCEPTED' ? 'text-emerald-700' : 'text-red-700' }} uppercase tracking-wide mb-1">
            Keputusan {{ $appeal->decider?->full_name ?? 'Guru BK' }} &middot; {{ $appeal->decided_at?->format('d M Y, H:i') }}
          </div>
          <p class="text-xs {{ $appeal->decision->value === 'ACCEPTED' ? 'text-emerald-900' : 'text-red-900' }}">{{ $appeal->decision_note ?? 'Tanpa catatan tambahan.' }}</p>
        </div>
      @endif
    </div>
  @elseif($permit->status->value === 'LATE')
    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Ajukan Banding Keterlambatan</h2>
      <p class="text-[11px] text-bluedark/50 mb-4">Anda tercatat terlambat kembali. Jika ada alasan yang dapat dipertanggungjawabkan, ajukan banding kepada Guru BK.</p>
      <form method="POST" action="{{ route('student.exit-permits.appeal', $permit) }}" class="space-y-3">
        @csrf
        <div>
          <label class="f-label" for="reason">Alasan Keterlambatan <span class="text-red-500">*</span></label>
          <textarea id="reason" name="reason" rows="3" class="f-textarea" placeholder="Contoh: Antrean di fasilitas kesehatan sangat panjang, ada bukti nomor antrean." required minlength="10">{{ old('reason') }}</textarea>
          @error('reason')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Kirim Banding</button>
      </form>
    </div>
  @endif

</div>
@endsection
