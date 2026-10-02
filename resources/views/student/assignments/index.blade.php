@extends('layouts.student')

@section('title', 'Tugas Saya')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Tugas Saya</h1>
      <p class="text-sm text-bluedark/60 mt-1">Semua tugas, kuis, dan ujian yang diterbitkan guru untuk kelas Anda.</p>
    </div>

    @if($subjects->isNotEmpty())
      <form method="GET" action="{{ route('student.assignments.index') }}" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <select name="subject" class="f-select text-xs" onchange="this.form.submit()">
          <option value="">Semua Mata Pelajaran</option>
          @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((string) $subjectFilter === (string) $subject->id)>{{ $subject->name }}</option>
          @endforeach
        </select>
      </form>
    @endif
  </div>

  <nav class="flex gap-2 flex-wrap" aria-label="Saring tugas berdasarkan status">
    <a href="{{ route('student.assignments.index', ['tab' => 'aktif', 'subject' => $subjectFilter]) }}"
       @if($tab === 'aktif') aria-current="page" @endif
       class="badge {{ $tab === 'aktif' ? 'badge-blue' : 'badge-gray' }} !px-3 !py-1.5">Aktif ({{ $counts['aktif'] }})</a>
    <a href="{{ route('student.assignments.index', ['tab' => 'terlewat', 'subject' => $subjectFilter]) }}"
       @if($tab === 'terlewat') aria-current="page" @endif
       class="badge {{ $tab === 'terlewat' ? 'badge-red' : 'badge-gray' }} !px-3 !py-1.5">Terlewat ({{ $counts['terlewat'] }})</a>
    <a href="{{ route('student.assignments.index', ['tab' => 'selesai', 'subject' => $subjectFilter]) }}"
       @if($tab === 'selesai') aria-current="page" @endif
       class="badge {{ $tab === 'selesai' ? 'badge-green' : 'badge-gray' }} !px-3 !py-1.5">Terkumpul ({{ $counts['selesai'] }})</a>
    <a href="{{ route('student.assignments.index', ['tab' => 'semua', 'subject' => $subjectFilter]) }}"
       @if($tab === 'semua') aria-current="page" @endif
       class="badge {{ $tab === 'semua' ? 'badge-blue' : 'badge-gray' }} !px-3 !py-1.5">Riwayat Tugas ({{ $filtered->count() }})</a>
  </nav>

  <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($filtered as $assessment)
      @php
        $submission = $assessment->student_submission;
        $typeLabels = ['TASK' => 'Tugas', 'QUIZ' => 'Kuis', 'PROJECT' => 'Proyek', 'EXAM' => 'Ujian', 'REMEDIAL' => 'Remedial', 'OTHER' => 'Lainnya'];
      @endphp
      <a href="{{ route('student.assignments.show', $assessment) }}" class="crud-card card-hover block">
        <div class="crud-card__head">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="crud-card__icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
            </div>
            <div class="min-w-0">
              <div class="crud-card__title truncate">{{ $assessment->title }}</div>
              <div class="crud-card__sub truncate">{{ $assessment->teachingAssignment?->subject?->name ?? 'Mapel' }} &middot; {{ $assessment->teachingAssignment?->schoolClass?->name }}</div>
            </div>
          </div>
          <span class="badge badge-blue shrink-0">{{ $typeLabels[$assessment->type->value] ?? $assessment->type->value }}</span>
        </div>

        <div class="crud-card__meta mt-3">
          <span>
            @if($assessment->due_at)
              Tenggat {{ $assessment->due_at->format('d M Y, H:i') }}
            @else
              Tanpa tenggat
            @endif
          </span>
        </div>

        <div class="crud-card__foot mt-3">
          @if($assessment->student_state === 'selesai')
            <span class="badge badge-green">{{ $submission?->status->value === 'REVIEWED' ? 'Sudah Dinilai' : 'Terkumpul' }}</span>
          @elseif($assessment->student_state === 'terlewat')
            <span class="badge badge-red">Terlewat</span>
          @else
            <span class="badge badge-yellow">Belum Dikumpulkan</span>
          @endif
          <span class="ml-auto text-[11px] font-semibold text-blueprim">Detail &rarr;</span>
        </div>
      </a>
    @empty
      <div class="panel p-6 text-center md:col-span-2 xl:col-span-3">
        <p class="text-sm text-bluedark/60">
          @if($tab === 'terlewat') Tidak ada tugas yang terlewat.
          @elseif($tab === 'selesai') Belum ada tugas yang dikumpulkan.
          @else Tidak ada tugas aktif saat ini.
          @endif
        </p>
      </div>
    @endforelse
  </div>

</div>
@endsection
