@extends('layouts.bk')

@section('title', 'Izin Keluar — BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Izin Keluar</h1>
      <p class="text-sm text-bluedark/60 mt-1">
        Verifikasi pengajuan, pantau kepulangan siswa, dan catat keterlambatan
        @if($academicYear)
          &middot; {{ $academicYear->name }}
        @endif
      </p>
    </div>
    <a href="{{ route('counselor.students.index') }}" class="btn btn-outline btn-sm">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Data Siswa
    </a>
  </div>

  <div class="panel p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-heading font-semibold text-bluedark text-[15px]">Siswa Sedang di Luar Sekolah</h2>
        <p class="text-xs text-bluedark/50">Countdown dihitung dari rencana kepulangan siswa</p>
      </div>
      <span class="badge badge-blue">{{ $permitsOut->count() }} siswa</span>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
      @forelse($permitsOut as $permit)
        <div class="rounded-2xl border border-bluelight p-4">
          <div class="flex items-start gap-3">
            <div class="avatar-circle">{{ strtoupper(substr($permit->student?->full_name ?? 'S', 0, 2)) }}</div>
            <div class="min-w-0 flex-1">
              <a href="{{ route('counselor.exit-permits.show', $permit) }}" class="font-semibold text-sm text-bluedark truncate block hover:text-blueprim">
                {{ $permit->student?->full_name }}
              </a>
              <div class="text-xs text-bluedark/50 truncate">
                {{ $permit->student?->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
              </div>
            </div>
          </div>
          <p class="text-xs text-bluedark/60 mt-2 line-clamp-2">{{ $permit->reason?->name }} — {{ $permit->reason_detail }}</p>
          <p class="text-[11px] text-bluedark/45 mt-1">
            Rencana kembali {{ $permit->planned_return_at->format('H:i') }} WIB
            @if($permit->actual_exit_at)
              &middot; keluar {{ $permit->actual_exit_at->format('H:i') }}
            @endif
          </p>
          <div class="flex items-center justify-between gap-2 mt-3">
            <span class="countdown-pill ok" data-return-at="{{ $permit->planned_return_at->timestamp }}">
              <span class="dot"></span>Sisa --:--
            </span>
            <form action="{{ route('counselor.exit-permits.complete', $permit) }}" method="POST" class="inline">
              @csrf
              <button type="submit" class="btn btn-outline btn-sm">Sudah Kembali</button>
            </form>
          </div>
        </div>
      @empty
        <p class="text-sm text-bluedark/50 col-span-full">Tidak ada siswa yang sedang izin di luar sekolah.</p>
      @endforelse
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.exit-permits.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[220px]">
        <label class="f-label" for="q">Cari Siswa</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama, NIS, atau NISN" class="f-input">
      </div>
      <div class="w-full sm:w-48">
        <label class="f-label" for="status">Status Izin</label>
        <select id="status" name="status" class="f-select">
          <option value="">Semua Status</option>
          @foreach(\App\Enums\ExitPermitStatus::cases() as $case)
            <option value="{{ $case->value }}" @selected($statusFilter === $case->value)>{{ $case->value }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $statusFilter)
        <a href="{{ route('counselor.exit-permits.index') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
      <a href="{{ route('counselor.exit-permits.index', array_filter(['q' => $search])) }}"
         class="badge {{ $statusFilter ? 'badge-gray' : 'badge-blue' }}">Semua ({{ $statusCounts->sum() }})</a>
      @foreach(\App\Enums\ExitPermitStatus::cases() as $case)
        <a href="{{ route('counselor.exit-permits.index', array_filter(['q' => $search, 'status' => $case->value])) }}"
           class="badge {{ $statusFilter === $case->value ? 'badge-blue' : 'badge-gray' }}">
          {{ $case->value }} ({{ $statusCounts[$case->value] ?? 0 }})
        </a>
      @endforeach
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Siswa</th>
            <th>Kelas</th>
            <th>Alasan</th>
            <th>Rencana Kembali</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($permits as $permit)
            <tr>
              <td>
                <a href="{{ route('counselor.students.show', $permit->student_id) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $permit->student?->full_name }}
                </a>
                <div class="text-[11px] text-bluedark/45">NIS {{ $permit->student?->nis }}</div>
              </td>
              <td class="text-xs">{{ $permit->student?->currentEnrollment?->schoolClass?->name ?? '—' }}</td>
              <td class="max-w-[240px]">
                <div class="text-xs font-medium truncate" title="{{ $permit->reason_detail }}">{{ $permit->reason_detail }}</div>
                <div class="text-[11px] text-bluedark/45 truncate">{{ $permit->reason?->name }}</div>
              </td>
              <td class="text-xs whitespace-nowrap">
                {{ $permit->planned_return_at->format('d M Y H:i') }}
                @if(in_array($permit->status, [\App\Enums\ExitPermitStatus::Approved, \App\Enums\ExitPermitStatus::Late], true) && $permit->actual_return_at === null)
                  <div class="mt-1">
                    <span class="countdown-pill ok" data-return-at="{{ $permit->planned_return_at->timestamp }}">
                      <span class="dot"></span>Sisa --:--
                    </span>
                  </div>
                @elseif($permit->actual_return_at)
                  <div class="text-[11px] text-emerald-600 font-semibold mt-1">
                    Kembali {{ $permit->actual_return_at->format('H:i') }}
                  </div>
                @endif
              </td>
              <td>
                <x-bk.permit-status :status="$permit->status" />
                @if($permit->appeal)
                  <div class="text-[11px] text-bluedark/50 mt-1">
                    Banding: {{ $permit->appeal->decision->value }}
                  </div>
                @endif
              </td>
              <td>
                <div class="flex items-center justify-end gap-1.5">
                  @if($permit->status === \App\Enums\ExitPermitStatus::Pending)
                    <button type="button" class="btn btn-success btn-sm" data-modal-open="approveModal-{{ $permit->id }}">Setujui</button>
                    <button type="button" class="btn btn-danger btn-sm" data-modal-open="rejectModal-{{ $permit->id }}">Tolak</button>
                  @else
                    <a href="{{ route('counselor.exit-permits.show', $permit) }}" class="btn btn-outline btn-sm">Detail</a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-8 text-xs text-bluedark/50">Belum ada data izin keluar yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$permits" />
  </div>

  @foreach($permits as $permit)
    @if($permit->status === \App\Enums\ExitPermitStatus::Pending)
      <div class="modal-overlay" id="approveModal-{{ $permit->id }}" role="dialog" aria-modal="true">
        <div class="modal-box max-w-lg">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-heading font-bold text-bluedark">Setujui Izin Keluar</h3>
              <p class="text-xs text-bluedark/50 mt-0.5">{{ $permit->student?->full_name }} &middot; {{ $permit->reason?->name }}</p>
            </div>
            <button type="button" data-modal-close class="text-bluedark/40 hover:text-bluedark" aria-label="Tutup">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="rounded-xl bg-bluelight/50 p-3 text-xs text-bluedark space-y-1 mb-4">
            <div class="flex justify-between"><span class="text-bluedark/60">Rencana keluar</span><span class="font-semibold">{{ $permit->planned_exit_at->format('d M Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/60">Rencana kembali</span><span class="font-semibold">{{ $permit->planned_return_at->format('d M Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/60">Uraian</span><span class="font-semibold text-right max-w-[60%]">{{ $permit->reason_detail }}</span></div>
          </div>

          <form action="{{ route('counselor.exit-permits.approve', $permit) }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="f-label" for="approval_note-{{ $permit->id }}">Catatan Persetujuan (opsional)</label>
              <textarea id="approval_note-{{ $permit->id }}" name="approval_note" rows="2" class="f-textarea"
                        placeholder="Contoh: dikembalikan sebelum pukul 12.00, wajib melapor ke BK">{{ old('approval_note') }}</textarea>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="btn btn-success flex-1">Ya, Setujui Izin</button>
              <button type="button" data-modal-close class="btn btn-outline">Batal</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal-overlay" id="rejectModal-{{ $permit->id }}" role="dialog" aria-modal="true">
        <div class="modal-box max-w-lg">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-heading font-bold text-bluedark">Tolak Izin Keluar</h3>
              <p class="text-xs text-bluedark/50 mt-0.5">{{ $permit->student?->full_name }} &middot; {{ $permit->reason?->name }}</p>
            </div>
            <button type="button" data-modal-close class="text-bluedark/40 hover:text-bluedark" aria-label="Tutup">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <form action="{{ route('counselor.exit-permits.reject', $permit) }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="f-label" for="rejection_note-{{ $permit->id }}">Alasan Penolakan <span class="text-red-500">*</span></label>
              <textarea id="rejection_note-{{ $permit->id }}" name="rejection_note" rows="3" required class="f-textarea"
                        placeholder="Contoh: tidak melampirkan surat keterangan orang tua">{{ old('rejection_note') }}</textarea>
              @error('rejection_note')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="flex gap-2">
              <button type="submit" class="btn btn-danger flex-1">Ya, Tolak Izin</button>
              <button type="button" data-modal-close class="btn btn-outline">Batal</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endforeach

</div>
@endsection
