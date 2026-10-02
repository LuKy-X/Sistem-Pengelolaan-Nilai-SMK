@extends('layouts.student')

@section('title', 'Banding Keterlambatan')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Banding Keterlambatan</h1>
    <p class="text-sm text-bluedark/60 mt-1">Riwayat banding atas keterlambatan kembali dari izin keluar. Banding diajukan dari halaman detail izin yang tercatat terlambat.</p>
  </div>

  <div class="flex gap-2 flex-wrap">
    <a href="{{ route('student.appeals.index') }}" class="badge {{ $decisionFilter === null ? 'badge-blue' : 'badge-gray' }} !px-3 !py-1.5">Semua ({{ $decisionCounts->sum() }})</a>
    <a href="{{ route('student.appeals.index', ['decision' => 'PENDING']) }}" class="badge {{ $decisionFilter === 'PENDING' ? 'badge-yellow' : 'badge-gray' }} !px-3 !py-1.5">Diproses ({{ $decisionCounts['PENDING'] ?? 0 }})</a>
    <a href="{{ route('student.appeals.index', ['decision' => 'ACCEPTED']) }}" class="badge {{ $decisionFilter === 'ACCEPTED' ? 'badge-green' : 'badge-gray' }} !px-3 !py-1.5">Diterima ({{ $decisionCounts['ACCEPTED'] ?? 0 }})</a>
    <a href="{{ route('student.appeals.index', ['decision' => 'REJECTED']) }}" class="badge {{ $decisionFilter === 'REJECTED' ? 'badge-red' : 'badge-gray' }} !px-3 !py-1.5">Ditolak ({{ $decisionCounts['REJECTED'] ?? 0 }})</a>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    @forelse($appeals as $appeal)
      <div class="panel p-4">
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="min-w-0">
            <div class="font-heading font-semibold text-bluedark text-sm truncate">{{ $appeal->exitPermit?->reason?->name ?? 'Izin Keluar' }}</div>
            <div class="text-[10px] text-bluedark/45">Diajukan {{ $appeal->submitted_at?->format('d M Y, H:i') }}</div>
          </div>
          <span class="badge {{ ['PENDING' => 'badge-yellow', 'ACCEPTED' => 'badge-green', 'REJECTED' => 'badge-red'][$appeal->decision->value] ?? 'badge-gray' }} shrink-0">
            {{ ['PENDING' => 'Diproses', 'ACCEPTED' => 'Diterima', 'REJECTED' => 'Ditolak'][$appeal->decision->value] }}
          </span>
        </div>

        <div class="p-2.5 rounded-lg bg-bluelight/40 border border-bluelight mb-2">
          <div class="text-[10px] font-semibold text-bluedark/50 uppercase tracking-wide mb-0.5">Alasan Anda</div>
          <p class="text-xs text-bluedark/80">{{ $appeal->reason }}</p>
        </div>

        @if($appeal->decision->value !== 'PENDING')
          <div class="p-2.5 rounded-lg {{ $appeal->decision->value === 'ACCEPTED' ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }} mb-2">
            <div class="text-[10px] font-semibold {{ $appeal->decision->value === 'ACCEPTED' ? 'text-emerald-700' : 'text-red-700' }} uppercase tracking-wide mb-0.5">
              Keputusan {{ $appeal->decider?->full_name ?? 'Guru BK' }} &middot; {{ $appeal->decided_at?->format('d M Y, H:i') }}
            </div>
            <p class="text-xs {{ $appeal->decision->value === 'ACCEPTED' ? 'text-emerald-900' : 'text-red-900' }}">{{ $appeal->decision_note ?? 'Tanpa catatan tambahan.' }}</p>
          </div>
        @endif

        <div class="text-right">
          <a href="{{ route('student.exit-permits.show', $appeal->exit_permit_id) }}" class="text-[11px] font-semibold text-blueprim hover:underline">Lihat izin terkait &rarr;</a>
        </div>
      </div>
    @empty
      <div class="panel p-6 text-center md:col-span-2">
        <p class="text-sm text-bluedark/60">Belum ada banding yang diajukan.</p>
        <p class="text-[11px] text-bluedark/45 mt-1">Banding hanya dapat dibuat dari izin keluar yang berstatus terlambat.</p>
      </div>
    @endforelse
  </div>

  <div>{{ $appeals->links() }}</div>

</div>
@endsection
