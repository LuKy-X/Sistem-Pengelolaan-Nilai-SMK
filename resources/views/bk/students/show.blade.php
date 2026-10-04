@extends('layouts.bk')

@section('title', $student->full_name.' — BK')

@section('content')
<div class="space-y-6">

  <p class="text-sm text-bluedark/60">
    <a href="{{ route('counselor.students.index') }}" class="font-semibold text-blueprim hover:underline">Rekap Siswa</a>
    /
    <span class="font-semibold text-bluedark">{{ $student->full_name }}</span>
  </p>

  <div class="panel p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="avatar-circle avatar-circle--lg">{{ strtoupper(substr($student->full_name, 0, 2)) }}</div>
        <div>
          <h1 class="font-heading font-bold text-bluedark text-xl">{{ $student->full_name }}</h1>
          <p class="text-xs text-bluedark/50 mt-0.5">
            NIS {{ $student->nis }} &middot; NISN {{ $student->nisn ?? '—' }}
            @if($student->currentEnrollment?->schoolClass)
              &middot; {{ $student->currentEnrollment->schoolClass->name }}
              @if($student->currentEnrollment->schoolClass->department)
                {{ $student->currentEnrollment->schoolClass->department->name }}
              @endif
            @endif
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('counselor.discipline.create', ['student_id' => $student->id]) }}" class="btn btn-outline btn-sm">Catat Poin</a>
        <a href="{{ route('counselor.counseling.index', ['student_id' => $student->id]) }}" class="btn btn-primary btn-sm">Catat Konseling</a>
      </div>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3 mt-6">
      <div class="rounded-xl border border-bluelight p-4">
        <div class="text-[11px] text-bluedark/50">Saldo Poin</div>
        <div class="flex items-end gap-2 mt-1">
          <span class="font-heading text-3xl font-bold {{ $balance < 0 ? 'text-red-600' : 'text-bluedark' }}">{{ $balance }}</span>
        </div>
        <div class="mt-2"><x-bk.point-badge :standing="$standing" :show-balance="false" /></div>
      </div>

      <div class="rounded-xl border border-bluelight p-4">
        <div class="text-[11px] text-bluedark/50">Poin Pelanggaran</div>
        <div class="font-heading text-2xl font-bold text-red-600 mt-1">&minus;{{ $violationPoints }}</div>
        <div class="text-[11px] text-bluedark/45 mt-1">Total 20 catatan terbaru</div>
      </div>

      <div class="rounded-xl border border-bluelight p-4">
        <div class="text-[11px] text-bluedark/50">Poin Penghargaan</div>
        <div class="font-heading text-2xl font-bold text-green-600 mt-1">+{{ $rewardPoints }}</div>
        <div class="text-[11px] text-bluedark/45 mt-1">Total 20 catatan terbaru</div>
      </div>

      <div class="rounded-xl border border-bluelight p-4">
        <div class="text-[11px] text-bluedark/50">Izin Keluar</div>
        <div class="font-heading text-2xl font-bold text-bluedark mt-1">{{ $permitStats['total'] }}</div>
        <div class="text-[11px] text-bluedark/45 mt-1">
          {{ $permitStats['late'] }} terlambat &middot; {{ $permitStats['rejected'] }} ditolak
        </div>
      </div>
    </div>

    @if($suggestedLetter !== null)
      <div class="mt-5 rounded-xl bg-amber-50 border border-amber-200 p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
          <div class="text-xs font-semibold text-amber-800">Saldo poin siswa menyentuh ambang {{ $suggestedLetter->value }}</div>
          <p class="text-[11px] text-amber-700 mt-0.5">Pertimbangkan penerbitan surat peringatan sesuai ketentuan sekolah.</p>
        </div>
        <a href="{{ route('counselor.disciplinary-letters.index', ['student_id' => $student->id]) }}" class="btn btn-primary btn-sm flex-shrink-0">
          Terbitkan {{ $suggestedLetter->value }}
        </a>
      </div>
    @endif
  </div>

  <div class="grid xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 space-y-5">
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Riwayat Kedisiplinan</h2>
            <p class="text-xs text-bluedark/50">
              {{ $academicYear?->name ?? 'Tahun ajaran berjalan' }} &middot; 20 catatan terbaru
            </p>
          </div>
          <a href="{{ route('counselor.discipline.index', ['student_id' => $student->id]) }}" class="btn btn-outline btn-sm">Semua Riwayat</a>
        </div>

        <div class="overflow-x-auto db-scroll border border-bluelight rounded-xl">
          <table class="tbl">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Poin</th>
                <th>Uraian</th>
                <th>Sumber</th>
              </tr>
            </thead>
            <tbody>
              @forelse($records as $record)
                @php($isCounseling = in_array($record->source_type, ['COUNSELING', 'INTERVIEW', 'HOME_VISIT', 'PARENT_MEETING'], true))
                <tr>
                  <td class="text-xs whitespace-nowrap">{{ $record->occurred_at->format('d M Y') }}</td>
                  <td class="text-xs">{{ $record->category?->name ?? '—' }}</td>
                  <td>
                    @if($record->points_delta === 0)
                      <span class="text-xs text-bluedark/40">0</span>
                    @else
                      <span class="text-xs font-semibold {{ $record->points_delta < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $record->points_delta > 0 ? '+' : '' }}{{ $record->points_delta }}
                      </span>
                    @endif
                  </td>
                  <td class="text-xs max-w-[240px] truncate" title="{{ $record->description }}">{{ $record->description }}</td>
                  <td>
                    @if($isCounseling)
                      <x-bk.status-badge label="Konseling" tone="blue" />
                    @else
                      <span class="text-[11px] text-bluedark/50">{{ $record->source_type ?? 'MANUAL' }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">Belum ada catatan kedisiplinan untuk siswa ini.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Riwayat Izin Keluar</h2>
            <p class="text-xs text-bluedark/50">10 pengajuan terakhir</p>
          </div>
          <a href="{{ route('counselor.exit-permits.index', ['q' => $student->nis]) }}" class="btn btn-outline btn-sm">Semua Izin</a>
        </div>

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
              @forelse($exitPermits as $permit)
                <tr>
                  <td class="text-xs whitespace-nowrap">{{ $permit->requested_at->format('d M Y') }}</td>
                  <td class="text-xs max-w-[200px] truncate" title="{{ $permit->reason_detail }}">{{ $permit->reason_detail }}</td>
                  <td class="text-xs whitespace-nowrap">{{ $permit->planned_return_at->format('d M Y H:i') }}</td>
                  <td><x-bk.permit-status :status="$permit->status" /></td>
                  <td class="text-right">
                    <a href="{{ route('counselor.exit-permits.show', $permit) }}" class="btn btn-outline btn-sm">Buka</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-8 text-xs text-bluedark/50">Belum ada pengajuan izin keluar.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Riwayat Konseling</h2>
        <p class="text-xs text-bluedark/50 mb-4">Layanan konseling yang tercatat dan tidak memengaruhi saldo poin</p>

        <div class="space-y-2.5">
          @forelse($counselingLogs as $log)
            <div class="hl-row">
              <div class="min-w-0">
                <div class="font-semibold text-xs">{{ $log->occurred_at->format('d M Y') }} &middot; {{ $log->category?->name ?? 'Konseling' }}</div>
                <div class="hl-sub">{{ $log->description }}</div>
              </div>
            </div>
          @empty
            <p class="text-sm text-bluedark/50">Belum ada rekam konseling untuk siswa ini.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="space-y-5">
      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Surat Peringatan</h2>
        <div class="grid grid-cols-3 gap-2.5 text-center mb-3">
          @foreach(\App\Enums\DisciplinaryLetterType::cases() as $case)
            <div class="rounded-xl border border-bluelight py-3">
              <div class="font-heading text-lg font-bold {{ $case->value === 'SP1' ? 'text-amber-600' : 'text-red-600' }} leading-none">
                {{ $letterSummary[$case->value] ?? 0 }}
              </div>
              <div class="text-[11px] text-bluedark/50 mt-1">{{ $case->value }}</div>
            </div>
          @endforeach
        </div>

        <div class="space-y-2">
          @forelse($letters as $letter)
            <a href="{{ route('counselor.disciplinary-letters.show', $letter) }}" class="hl-row block hover:opacity-90 transition-opacity">
              <div class="min-w-0">
                <div class="font-semibold text-xs">{{ $letter->type->value }} &middot; {{ $letter->issued_at->format('d M Y') }}</div>
                <div class="hl-sub truncate">{{ $letter->reason }}</div>
              </div>
            </a>
          @empty
            <p class="text-sm text-bluedark/50">Belum ada surat peringatan.</p>
          @endforelse
        </div>
      </div>

      <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Ringkasan Izin</h2>
        <div class="space-y-2.5 text-xs">
          <div class="flex justify-between"><span class="text-bluedark/50">Total pengajuan</span><span class="font-semibold">{{ $permitStats['total'] }}</span></div>
          <div class="flex justify-between"><span class="text-bluedark/50">Sedang di luar</span><span class="font-semibold text-amber-600">{{ $permitStats['active'] }}</span></div>
          <div class="flex justify-between"><span class="text-bluedark/50">Menunggu persetujuan</span><span class="font-semibold">{{ $permitStats['pending'] }}</span></div>
          <div class="flex justify-between"><span class="text-bluedark/50">Terlambat kembali</span><span class="font-semibold text-red-600">{{ $permitStats['late'] }}</span></div>
          <div class="flex justify-between"><span class="text-bluedark/50">Ditolak</span><span class="font-semibold">{{ $permitStats['rejected'] }}</span></div>
        </div>
      </div>

      @if($setting)
        <div class="panel p-5">
          <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Aturan Poin</h2>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between"><span class="text-bluedark/50">Poin awal</span><span class="font-semibold">{{ $setting->initial_points }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Poin minimum</span><span class="font-semibold">{{ $setting->minimum_points }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Ambang perhatian</span><span class="font-semibold">{{ $setting->warning_threshold ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Ambang SP1</span><span class="font-semibold">{{ $setting->sp1_threshold ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Ambang SP2</span><span class="font-semibold">{{ $setting->sp2_threshold ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-bluedark/50">Ambang SP3</span><span class="font-semibold">{{ $setting->sp3_threshold ?? '—' }}</span></div>
          </div>
        </div>
      @endif
    </div>
  </div>

</div>
@endsection
