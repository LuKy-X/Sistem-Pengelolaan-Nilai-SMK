@extends('layouts.bk')

@section('title', 'Detail SP — BK')

@section('content')
<div class="space-y-6">

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.disciplinary-letters.index') }}" class="font-semibold text-blueprim hover:underline">Surat Peringatan</a>
    /
    <span class="font-semibold text-bluedark">{{ $letter->type->value }} — {{ $letter->student?->full_name }}</span>
  </p>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="panel p-6">
        <div class="flex flex-wrap items-start justify-between gap-3 pb-5 border-b border-bluelight">
          <div class="flex items-center gap-3">
            <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr($letter->student?->full_name ?? 'S', 0, 2)) }}</div>
            <div>
              <h1 class="font-heading font-bold text-bluedark text-lg">{{ $letter->student?->full_name }}</h1>
              <p class="text-xs text-bluedark/50">
                NIS {{ $letter->student?->nis }} &middot;
                {{ $letter->student?->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
              </p>
            </div>
          </div>
          <div class="text-right">
            <span class="badge badge-{{ $letter->type->value === 'SP1' ? 'yellow' : 'red' }} text-xs">{{ $letter->type->value }}</span>
            <p class="text-[11px] text-bluedark/45 mt-1">Diterbitkan {{ $letter->issued_at->format('d F Y') }}</p>
          </div>
        </div>

        <div class="pt-5 space-y-4">
          <div>
            <h2 class="text-[11px] font-semibold uppercase tracking-wide text-bluedark/45 mb-1">Alasan Penerbitan</h2>
            <p class="text-sm text-bluedark leading-relaxed">{{ $letter->reason }}</p>
          </div>

          @if($letter->notes)
            <div>
              <h2 class="text-[11px] font-semibold uppercase tracking-wide text-bluedark/45 mb-1">Catatan Internal</h2>
              <p class="text-sm text-bluedark/70 leading-relaxed">{{ $letter->notes }}</p>
            </div>
          @endif
        </div>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Dokumen Surat</h2>
        @if($letter->document_path)
          <div class="rounded-xl border border-bluelight p-4 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
              <div class="text-sm font-semibold text-bluedark truncate">{{ basename($letter->document_path) }}</div>
              <div class="text-[11px] text-bluedark/45 mt-0.5">Berkas pindai tersimpan pada penyimpanan publik.</div>
            </div>
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($letter->document_path) }}"
               target="_blank" rel="noopener" class="btn btn-primary btn-sm flex-shrink-0">Buka / Unduh</a>
          </div>
        @else
          <p class="text-sm text-bluedark/50">Tidak ada berkas pindai yang dilampirkan pada surat ini.</p>
        @endif
      </div>
    </div>

    <div class="space-y-5">
      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Informasi Surat</h2>
        <div class="space-y-2.5 text-xs">
          <div class="flex justify-between">
            <span class="text-bluedark/50">Jenis</span>
            <span class="font-semibold text-bluedark">{{ $letter->type->value }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-bluedark/50">Tanggal terbit</span>
            <span class="font-semibold text-bluedark">{{ $letter->issued_at->format('d M Y') }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-bluedark/50">Tahun ajaran</span>
            <span class="font-semibold text-bluedark">{{ $letter->academicYear?->name ?? '—' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-bluedark/50">Diterbitkan oleh</span>
            <span class="font-semibold text-bluedark">{{ $letter->issuer?->name ?? '—' }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-bluedark/50">Status</span>
            <x-bk.status-badge
              :label="match ($letter->status) { 'ACTIVE' => 'Aktif', 'RESOLVED' => 'Selesai', default => $letter->status }"
              :tone="match ($letter->status) { 'ACTIVE' => 'red', 'RESOLVED' => 'green', default => 'gray' }"
              :dot="true" />
          </div>
        </div>

        <div class="flex gap-2 mt-5">
          <a href="{{ route('counselor.students.show', $letter->student_id) }}" class="btn btn-outline btn-sm flex-1">Rekap Siswa</a>
          <form action="{{ route('counselor.disciplinary-letters.destroy', $letter) }}" method="POST"
                onsubmit="return confirm('Cabut dan hapus surat peringatan ini? Tindakan tercatat pada log audit.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Cabut SP</button>
          </form>
        </div>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Surat Peringatan Lain</h2>
        <p class="text-xs text-bluedark/50 mb-3">Riwayat SP siswa ini</p>

        <div class="space-y-2.5">
          @forelse($letter->student?->disciplinaryLetters()->whereKeyNot($letter->getKey())->orderByDesc('issued_at')->limit(5)->get() ?? [] as $other)
            <a href="{{ route('counselor.disciplinary-letters.show', $other) }}" class="hl-row block hover:opacity-90 transition-opacity">
              <div class="min-w-0">
                <div class="font-semibold">{{ $other->type->value }} &middot; {{ $other->issued_at->format('d M Y') }}</div>
                <div class="hl-sub truncate">{{ $other->reason }}</div>
              </div>
            </a>
          @empty
            <p class="text-sm text-bluedark/50">Tidak ada surat peringatan lain untuk siswa ini.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
