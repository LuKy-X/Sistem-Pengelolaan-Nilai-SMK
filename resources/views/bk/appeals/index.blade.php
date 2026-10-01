@extends('layouts.bk')

@section('title', 'Banding Keterlambatan — BK')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Banding Keterlambatan</h1>
    <p class="text-sm text-bluedark/60 mt-1">Validasi alasan siswa yang terlambat kembali dan tentukan sanksi bila diperlukan</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="kpi-card">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $decisionCounts['PENDING'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Menunggu Validasi</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-green-100 text-green-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $decisionCounts['ACCEPTED'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Alasan Diterima</div>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon bg-red-100 text-red-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $decisionCounts['REJECTED'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Alasan Ditolak</div>
      </div>
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.appeals.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[220px]">
        <label class="f-label" for="q">Cari Siswa</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama atau NIS siswa" class="f-input">
      </div>
      <div class="w-full sm:w-48">
        <label class="f-label" for="decision">Status Banding</label>
        <select id="decision" name="decision" class="f-select">
          <option value="">Semua Status</option>
          @foreach(\App\Enums\AppealDecision::cases() as $case)
            <option value="{{ $case->value }}" @selected($decisionFilter === $case->value)>{{ $case->value }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $decisionFilter)
        <a href="{{ route('counselor.appeals.index') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="flex flex-wrap gap-2 mb-4">
      <a href="{{ route('counselor.appeals.index', array_filter(['q' => $search])) }}"
         class="badge {{ $decisionFilter ? 'badge-gray' : 'badge-blue' }}">Semua ({{ $decisionCounts->sum() }})</a>
      @foreach(\App\Enums\AppealDecision::cases() as $case)
        <a href="{{ route('counselor.appeals.index', array_filter(['q' => $search, 'decision' => $case->value])) }}"
           class="badge {{ $decisionFilter === $case->value ? 'badge-blue' : 'badge-gray' }}">
          {{ $case->value }} ({{ $decisionCounts[$case->value] ?? 0 }})
        </a>
      @endforeach
    </div>

    <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
      <table class="tbl">
        <thead>
          <tr>
            <th>Siswa</th>
            <th>Kelas</th>
            <th>Diajukan</th>
            <th>Alasan Banding</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($appeals as $appeal)
            @php
                $decision = $appeal->decision->value;
                $tone = match ($decision) { 'ACCEPTED' => 'green', 'REJECTED' => 'red', default => 'yellow' };
                $label = match ($decision) { 'ACCEPTED' => 'Diterima', 'REJECTED' => 'Ditolak', default => 'Menunggu Validasi' };
            @endphp
            <tr>
              <td>
                <a href="{{ route('counselor.students.show', $appeal->exitPermit?->student_id) }}" class="font-medium text-bluedark hover:text-blueprim">
                  {{ $appeal->exitPermit?->student?->full_name }}
                </a>
                <div class="text-[11px] text-bluedark/45">Izin #{{ $appeal->exitPermit?->id }}</div>
              </td>
              <td class="text-xs">{{ $appeal->exitPermit?->student?->currentEnrollment?->schoolClass?->name ?? '—' }}</td>
              <td class="text-xs whitespace-nowrap">{{ $appeal->submitted_at->format('d M Y') }}</td>
              <td class="text-xs max-w-[260px]">
                <div class="truncate" title="{{ $appeal->reason }}">{{ $appeal->reason }}</div>
                @if($appeal->decision_note)
                  <div class="text-[11px] text-bluedark/45 truncate">Catatan: {{ $appeal->decision_note }}</div>
                @endif
              </td>
              <td><x-bk.status-badge :label="$label" :tone="$tone" :dot="true" /></td>
              <td>
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('counselor.exit-permits.show', $appeal->exit_permit_id) }}" class="btn btn-outline btn-sm">Izin</a>
                  @if($decision === 'PENDING')
                    <button type="button" class="btn btn-primary btn-sm" data-modal-open="appealModal-{{ $appeal->id }}">Putuskan</button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-8 text-xs text-bluedark/50">Belum ada banding yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-bk.pagination :paginator="$appeals" />
  </div>

  @foreach($appeals as $appeal)
    @if($appeal->decision->value === 'PENDING')
      <div class="modal-overlay" id="appealModal-{{ $appeal->id }}" role="dialog" aria-modal="true">
        <div class="modal-box max-w-xl">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="avatar-circle">{{ strtoupper(substr($appeal->exitPermit?->student?->full_name ?? 'S', 0, 2)) }}</div>
              <div>
                <h3 class="font-heading font-bold text-bluedark">Putuskan Banding</h3>
                <p class="text-xs text-bluedark/50 mt-0.5">
                  {{ $appeal->exitPermit?->student?->full_name }} &middot;
                  {{ $appeal->exitPermit?->student?->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas' }}
                </p>
              </div>
            </div>
            <button type="button" data-modal-close class="text-bluedark/40 hover:text-bluedark" aria-label="Tutup">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="rounded-xl bg-bluelight/50 p-3 text-xs text-bluedark space-y-1 mb-4">
            <div class="flex justify-between">
              <span class="text-bluedark/60">Keterlambatan</span>
              <span class="font-semibold">
                {{ (int) $appeal->exitPermit?->planned_return_at?->diffInMinutes($appeal->exitPermit?->actual_return_at ?? now()) }} menit
              </span>
            </div>
            <div class="flex justify-between"><span class="text-bluedark/60">Rencana kembali</span><span class="font-semibold">{{ $appeal->exitPermit?->planned_return_at?->format('d M Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/60">Riil kembali</span><span class="font-semibold">{{ $appeal->exitPermit?->actual_return_at?->format('d M Y H:i') ?? '—' }}</span></div>
          </div>

          <div class="rounded-xl border border-bluelight p-3 text-xs text-bluedark mb-4">
            <div class="text-bluedark/50 mb-1">Alasan dari siswa</div>
            {{ $appeal->reason }}
          </div>

          <form action="{{ route('counselor.appeals.decide', $appeal) }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="f-label" for="decision-{{ $appeal->id }}">Keputusan <span class="text-red-500">*</span></label>
              <select id="decision-{{ $appeal->id }}" name="decision" class="f-select" required>
                <option value="">Pilih keputusan</option>
                <option value="ACCEPTED">Terima alasan (tanpa sanksi)</option>
                <option value="REJECTED">Tolak alasan</option>
              </select>
              @error('decision')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="mb-4">
              <label class="f-label" for="decision_note-{{ $appeal->id }}">Catatan Keputusan</label>
              <textarea id="decision_note-{{ $appeal->id }}" name="decision_note" rows="2" class="f-textarea"
                        placeholder="Wajib diisi jika alasan ditolak">{{ old('decision_note') }}</textarea>
              @error('decision_note')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3 mb-4">
              <label class="flex items-start gap-2 text-xs font-semibold text-amber-800 cursor-pointer">
                <input type="checkbox" name="record_sanction" value="1" class="mt-0.5">
                Catat sanksi sebagai pelanggaran kedisiplinan
              </label>
              <div class="grid sm:grid-cols-2 gap-3 mt-3">
                <div>
                  <label class="f-label" for="sanction_category_id-{{ $appeal->id }}">Kategori Pelanggaran</label>
                  <select id="sanction_category_id-{{ $appeal->id }}" name="sanction_category_id" class="f-select">
                    <option value="">Pilih kategori</option>
                    @foreach($violationCategories as $category)
                      <option value="{{ $category->id }}" data-points="{{ $category->default_points }}">
                        {{ $category->name }} ({{ $category->default_points }})
                      </option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="f-label" for="sanction_points-{{ $appeal->id }}">Besar Sanksi (poin)</label>
                  <input type="number" id="sanction_points-{{ $appeal->id }}" name="sanction_points" min="0" max="1000" class="f-input"
                         placeholder="Ikuti kategori">
                </div>
              </div>
              @error('sanction_category_id')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex gap-2">
              <button type="submit" class="btn btn-primary flex-1">Simpan Keputusan</button>
              <button type="button" data-modal-close class="btn btn-outline">Batal</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endforeach

</div>
@endsection

@push('scripts')
<script>
  // Isi otomatis besar sanksi mengikuti kategori yang dipilih.
  document.addEventListener('change', function (event) {
    var select = event.target.closest('select[name="sanction_category_id"]');

    if (!select) return;

    var option = select.options[select.selectedIndex];
    var pointsInput = select.closest('.rounded-xl')?.querySelector('input[name="sanction_points"]');

    if (option && pointsInput && option.dataset.points) {
      pointsInput.value = Math.abs(parseInt(option.dataset.points, 10) || 0);
    }
  });
</script>
@endpush
