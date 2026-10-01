@extends('layouts.bk')

@section('title', 'Data Siswa — BK')

@section('content')
<div class="space-y-6">

  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Rekap Siswa</h1>
      <p class="text-sm text-bluedark/60 mt-1">
        Pantau saldo poin, izin keluar, dan konseling setiap siswa
        @if($academicYear)
          &middot; {{ $academicYear->name }}
          @if($setting)
            &middot; mulai dari {{ $setting->initial_points }} poin
          @endif
        @endif
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('counselor.discipline.create') }}" class="btn btn-outline btn-sm">Catat Poin</a>
      <a href="{{ route('counselor.counseling.index') }}" class="btn btn-primary btn-sm">Catat Konseling</a>
    </div>
  </div>

  <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="{{ route('counselor.students.index', ['scope' => 'all']) }}"
       class="kpi-card {{ $scope === 'all' ? 'ring-2 ring-blueprim/30' : '' }}">
      <div class="kpi-icon bg-bluelight text-bluedark">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalStudents }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Siswa Aktif</div>
      </div>
    </a>

    <a href="{{ route('counselor.students.index', ['scope' => 'attention']) }}"
       class="kpi-card {{ $scope === 'attention' ? 'ring-2 ring-amber-400/40' : '' }}">
      <div class="kpi-icon bg-amber-100 text-amber-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">
          {{ ($thresholdCounts['warning'] ?? 0) + ($thresholdCounts['sp1'] ?? 0) + ($thresholdCounts['sp2'] ?? 0) + ($thresholdCounts['sp3'] ?? 0) }}
        </div>
        <div class="text-[11px] text-bluedark/55 mt-1">Perlu Perhatian</div>
      </div>
    </a>

    <div class="kpi-card">
      <div class="kpi-icon bg-red-100 text-red-600">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M8 9h8M8 13h5"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $thresholdCounts['sp1'] ?? 0 }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Mencapai Ambang SP1</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon bg-red-200 text-red-800">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="7" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ ($thresholdCounts['sp2'] ?? 0) + ($thresholdCounts['sp3'] ?? 0) }}</div>
        <div class="text-[11px] text-bluedark/55 mt-1">Mencapai Ambang SP2&ndash;SP3</div>
      </div>
    </div>
  </div>

  <div class="panel p-5">
    <form method="GET" action="{{ route('counselor.students.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
      <div class="flex-1 min-w-[220px]">
        <label class="f-label" for="q">Cari Siswa</label>
        <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama, NIS, atau NISN" class="f-input">
      </div>
      <div class="w-full sm:w-56">
        <label class="f-label" for="scope">Cakupan</label>
        <select id="scope" name="scope" class="f-select">
          <option value="all" @selected($scope === 'all')>Semua Siswa Aktif</option>
          <option value="attention" @selected($scope === 'attention')>Perlu Perhatian / Ambang SP</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Terapkan</button>
      @if($search !== '' || $scope !== 'all')
        <a href="{{ route('counselor.students.index') }}" class="btn btn-outline">Reset</a>
      @endif
    </form>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
      @forelse($students as $student)
        @php($studentStanding = $standings[$student->id] ?? ['label' => 'Aman', 'badge' => 'badge-green', 'tone' => 'safe'])
        <a href="{{ route('counselor.students.show', $student) }}"
           class="rounded-2xl border border-bluelight p-4 hover:border-blueprim/40 hover:shadow-sm transition-all">
          <div class="flex items-start gap-3">
            <div class="avatar-circle">{{ strtoupper(substr($student->full_name, 0, 2)) }}</div>
            <div class="min-w-0 flex-1">
              <div class="font-semibold text-sm text-bluedark truncate">{{ $student->full_name }}</div>
              <div class="text-[11px] text-bluedark/50 truncate">
                NIS {{ $student->nis }}
                @if($student->currentEnrollment?->schoolClass)
                  &middot; {{ $student->currentEnrollment->schoolClass->name }}
                @endif
              </div>
            </div>
            <span class="font-heading font-bold text-sm flex-shrink-0 {{ ($balances[$student->id] ?? 0) <= 30 ? 'text-red-600' : 'text-bluedark' }} ml-1">
              {{ $balances[$student->id] ?? 0 }}
            </span>
          </div>

          <div class="flex items-center gap-1.5 mt-3">
            <x-bk.point-badge :standing="$studentStanding" :show-balance="false" />
            <span class="text-[10px] text-bluedark/40">saldo poin</span>
          </div>
        </a>
      @empty
        <p class="text-sm text-bluedark/50 col-span-full">
          @if($scope === 'attention')
            Tidak ada siswa yang menyentuh ambang Surat Peringatan.
          @else
            Tidak ada siswa yang cocok dengan pencarian.
          @endif
        </p>
      @endforelse
    </div>

    <x-bk.pagination :paginator="$students" />
  </div>

</div>
@endsection
