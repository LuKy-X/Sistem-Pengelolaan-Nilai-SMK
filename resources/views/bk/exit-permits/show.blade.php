@extends('layouts.bk')

@section('title', 'Detail Izin — BK')

@section('content')
<div class="space-y-6">

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.exit-permits.index') }}" class="font-semibold text-blueprim hover:underline">Izin Keluar</a>
    /
    <span class="font-semibold text-bluedark">Detail #{{ $permit->id }}</span>
  </p>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="panel p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
          <div class="flex items-center gap-3">
            <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr($permit->student?->full_name ?? 'S', 0, 2)) }}</div>
            <div>
              <h1 class="font-heading font-bold text-bluedark">{{ $permit->student?->full_name }}</h1>
              <p class="text-xs text-bluedark/50">
                NIS {{ $permit->student?->nis }} &middot;
                {{ $permit->student?->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
              </p>
            </div>
          </div>
          <x-bk.permit-status :status="$permit->status" />
        </div>

        <div class="grid sm:grid-cols-2 gap-3 text-xs">
          <div class="rounded-xl border border-bluelight p-3">
            <div class="text-bluedark/50">Alasan Izin</div>
            <div class="font-semibold text-bluedark mt-0.5">{{ $permit->reason?->name }}</div>
            <p class="text-bluedark/60 mt-1">{{ $permit->reason_detail }}</p>
            @if($permit->reason?->description)
              <p class="text-[11px] text-bluedark/40 mt-1">{{ $permit->reason->description }}</p>
            @endif
          </div>
          <div class="rounded-xl border border-bluelight p-3 space-y-1.5">
            <div class="flex justify-between"><span class="text-bluedark/50">Diajukan</span><span class="font-semibold">{{ $permit->requested_at->format('d M Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Rencana keluar</span><span class="font-semibold">{{ $permit->planned_exit_at->format('d M Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Rencana kembali</span><span class="font-semibold">{{ $permit->planned_return_at->format('d M Y H:i') }}</span></div>
            @if($permit->approved_at)
              <div class="flex justify-between"><span class="text-bluedark/50">Diproses</span><span class="font-semibold">{{ $permit->approved_at->format('d M Y H:i') }}</span></div>
            @endif
            @if($permit->approver)
              <div class="flex justify-between"><span class="text-bluedark/50">Disetujui oleh</span><span class="font-semibold">{{ $permit->approver->full_name }}</span></div>
            @endif
          </div>
        </div>

        @if($permit->status === \App\Enums\ExitPermitStatus::Approved || $permit->status === \App\Enums\ExitPermitStatus::Late)
          @if($permit->actual_return_at === null)
            <div class="mt-4 rounded-xl bg-bluelight/60 p-4 flex flex-wrap items-center justify-between gap-3">
              <div>
                <div class="text-[11px] text-bluedark/60 font-semibold uppercase tracking-wide">Countdown Kepulangan</div>
                <div class="mt-1.5">
                  <span class="countdown-pill ok" data-return-at="{{ $permit->planned_return_at->timestamp }}">
                    <span class="dot"></span>Sisa --:--
                  </span>
                </div>
                @if($permit->isOverdue())
                  <p class="text-[11px] text-red-600 font-semibold mt-1.5">Siswa sudah melewati batas waktu kepulangan.</p>
                @endif
              </div>
              <form action="{{ route('counselor.exit-permits.complete', $permit) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Tandai Sudah Kembali</button>
              </form>
            </div>
          @else
            <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs text-emerald-800">
              Siswa tercatat kembali pada <strong>{{ $permit->actual_return_at->format('d M Y H:i') }}</strong>
              @if($permit->status === \App\Enums\ExitPermitStatus::Late)
                &mdash; terlambat {{ (int) $permit->planned_return_at->diffInMinutes($permit->actual_return_at) }} menit dari rencana.
              @else
                &mdash; tepat waktu.
              @endif
            </div>
          @endif
        @endif

        @if($permit->approval_note)
          <div class="mt-4 rounded-xl bg-bluelight/50 p-3 text-xs text-bluedark">
            <span class="font-semibold">Catatan persetujuan:</span> {{ $permit->approval_note }}
          </div>
        @endif

        @if($permit->rejection_note)
          <div class="mt-4 rounded-xl bg-red-50 border border-red-100 p-3 text-xs text-red-700">
            <span class="font-semibold">Alasan penolakan:</span> {{ $permit->rejection_note }}
          </div>
        @endif
      </div>

      @if($permit->status === \App\Enums\ExitPermitStatus::Pending)
        <div class="panel p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Keputusan BK</h2>
          <p class="text-xs text-bluedark/50 mb-4">Pastikan pengajuan disertai keterangan dan telah mendapat izin wali/orang tua.</p>

          <div class="grid sm:grid-cols-2 gap-4">
            <form action="{{ route('counselor.exit-permits.approve', $permit) }}" method="POST" class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4">
              @csrf
              <label class="f-label" for="approval_note">Catatan Persetujuan (opsional)</label>
              <textarea id="approval_note" name="approval_note" rows="2" class="f-textarea mb-3"
                        placeholder="Contoh: wajib melapor ke BK sebelum pulang">{{ old('approval_note') }}</textarea>
              <button type="submit" class="btn btn-success w-full">Setujui Izin</button>
            </form>

            <form action="{{ route('counselor.exit-permits.reject', $permit) }}" method="POST" class="rounded-2xl border border-red-200 bg-red-50/40 p-4">
              @csrf
              <label class="f-label" for="rejection_note">Alasan Penolakan <span class="text-red-500">*</span></label>
              <textarea id="rejection_note" name="rejection_note" rows="2" required class="f-textarea mb-3"
                        placeholder="Contoh: tidak ada surat keterangan orang tua">{{ old('rejection_note') }}</textarea>
              <button type="submit" class="btn btn-danger w-full">Tolak Izin</button>
            </form>
          </div>
        </div>
      @endif

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">Riwayat Izin Siswa Ini</h2>
        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th>Diajukan</th>
                <th>Alasan</th>
                <th>Rencana Kembali</th>
                <th>Status</th>
                <th class="text-right">Detail</th>
              </tr>
            </thead>
            <tbody>
              @forelse($studentHistory as $history)
                <tr>
                  <td class="text-xs whitespace-nowrap">{{ $history->requested_at->format('d M Y') }}</td>
                  <td class="text-xs max-w-[220px] truncate" title="{{ $history->reason_detail }}">{{ $history->reason_detail }}</td>
                  <td class="text-xs whitespace-nowrap">{{ $history->planned_return_at->format('d M Y H:i') }}</td>
                  <td><x-bk.permit-status :status="$history->status" /></td>
                  <td class="text-right">
                    <a href="{{ route('counselor.exit-permits.show', $history) }}" class="btn btn-outline btn-sm">Buka</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">Belum ada riwayat izin lain untuk siswa ini.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="space-y-5">
      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Status Kedisiplinan</h2>
        <p class="text-xs text-bluedark/50 mb-4">Saldo poin {{ $balance }} pada tahun ajaran berjalan</p>

        <div class="flex items-center justify-between">
          <span class="font-heading text-3xl font-bold text-bluedark">{{ $balance }}</span>
          <x-bk.point-badge :standing="$standing" :show-balance="false" />
        </div>

        <div class="mt-4 space-y-1.5 text-[11px] text-bluedark/60">
          <div class="flex justify-between"><span>Ambang peringatan</span><span>{{ $setting?->warning_threshold ?? '—' }}</span></div>
          <div class="flex justify-between"><span>Ambang SP1</span><span>{{ $setting?->sp1_threshold ?? '—' }}</span></div>
          <div class="flex justify-between"><span>Ambang SP2</span><span>{{ $setting?->sp2_threshold ?? '—' }}</span></div>
          <div class="flex justify-between"><span>Ambang SP3</span><span>{{ $setting?->sp3_threshold ?? '—' }}</span></div>
        </div>

        <a href="{{ route('counselor.students.show', $permit->student_id) }}" class="btn btn-outline btn-sm w-full mt-4">Buka Rekap Siswa</a>
      </div>

      @if($permit->appeal)
        <div class="panel p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Banding Keterlambatan</h2>
          <p class="text-xs text-bluedark/50 mb-4">Diajukan {{ $permit->appeal->submitted_at->format('d M Y H:i') }}</p>

          <div class="rounded-xl bg-bluelight/50 p-3 text-xs text-bluedark mb-3">{{ $permit->appeal->reason }}</div>

          <div class="flex items-center justify-between text-xs">
            <span class="text-bluedark/50">Status</span>
            <x-bk.status-badge
              :label="match ($permit->appeal->decision->value) { 'ACCEPTED' => 'Diterima', 'REJECTED' => 'Ditolak', default => 'Menunggu Validasi' }"
              :tone="match ($permit->appeal->decision->value) { 'ACCEPTED' => 'green', 'REJECTED' => 'red', default => 'yellow' }" />
          </div>

          @if($permit->appeal->decision_note)
            <div class="mt-3 rounded-xl bg-bluelight/40 p-3 text-xs text-bluedark">
              <span class="font-semibold">Catatan keputusan:</span> {{ $permit->appeal->decision_note }}
            </div>
          @endif

          @if($permit->appeal->decision_at)
            <p class="text-[11px] text-bluedark/45 mt-2">
              Diputuskan {{ $permit->appeal->decision_at->format('d M Y H:i') }}
              @if($permit->appeal->decider)
                oleh {{ $permit->appeal->decider->full_name }}
              @endif
            </p>
          @endif

          <a href="{{ route('counselor.appeals.index') }}" class="btn btn-outline btn-sm w-full mt-4">Kelola Banding</a>
        </div>
      @endif
    </div>
  </div>

</div>
@endsection
