@extends('layouts.student')

@section('title', 'Detail Nilai — ' . ($gradebook->teachingAssignment?->subject?->name ?? 'Mapel'))

@section('content')
@php
  $assignment = $gradebook->teachingAssignment;
  $fmt = fn ($value) => $value !== null ? rtrim(rtrim(number_format((float) $value, 2), '0'), '.') : '-';
@endphp
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <a href="{{ route('student.grades.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Semua nilai</a>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark mt-1">{{ $assignment?->subject?->name ?? 'Mata Pelajaran' }}</h1>
      <p class="text-sm text-bluedark/60 mt-1">
        {{ $gradebook->name }} &middot; {{ $assignment?->schoolClass?->name }} &middot; {{ $assignment?->semester?->academicYear?->name }} {{ $assignment?->semester?->name }}
      </p>
      <p class="text-xs text-bluedark/50 mt-0.5">Guru: {{ $assignment?->teacher?->full_name ?? '-' }}</p>
    </div>
    <div class="panel px-4 py-3 text-center">
      <div class="font-heading text-2xl font-bold {{ $summary['average'] !== null ? ($summary['average'] >= 75 ? 'text-emerald-600' : 'text-amber-600') : 'text-bluedark/30' }}">{{ $fmt($summary['average']) }}</div>
      <div class="text-[10px] text-bluedark/50">Rata-rata ({{ $summary['graded'] }}/{{ $summary['total'] }} kolom)</div>
    </div>
  </div>

  <div class="panel p-4 lg:p-5 overflow-x-auto">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Rincian Nilai</h2>
    <table class="w-full text-xs min-w-[560px]">
      <thead>
        <tr class="text-left text-bluedark/50 border-b border-bluelight">
          <th class="py-2 pr-3 font-semibold">Komponen</th>
          <th class="py-2 pr-3 font-semibold">Kategori</th>
          <th class="py-2 pr-3 font-semibold text-center">Nilai</th>
          <th class="py-2 pr-3 font-semibold text-center">Maks</th>
          <th class="py-2 font-semibold">Feedback Guru</th>
        </tr>
      </thead>
      <tbody>
        @forelse($gradebook->columns as $column)
          @php
            $score = $scores->get($column->id);
            $value = $values[$column->id] ?? null;
            $linkedAssessments = $assessmentsByColumn->get($column->id, collect());
            $feedback = $score?->feedback ?? $linkedAssessments->map(fn ($a) => $a->submissions->first()?->teacher_feedback)->filter()->first();
          @endphp
          <tr class="border-b border-bluelight/60 align-top">
            <td class="py-2.5 pr-3">
              <div class="font-semibold text-bluedark">{{ $column->name }}</div>
              <div class="text-[10px] text-bluedark/45">
                {{ $column->code }}
                @if($column->column_type->value === 'SUMMARY')
                  &middot; <span class="text-blueprim font-semibold">Ringkasan otomatis</span>
                @endif
                @if($linkedAssessments->isNotEmpty())
                  &middot; {{ $linkedAssessments->pluck('title')->join(', ') }}
                @endif
              </div>
            </td>
            <td class="py-2.5 pr-3 text-bluedark/60">{{ $column->category?->name ?? '-' }}</td>
            <td class="py-2.5 pr-3 text-center">
              <span class="font-heading font-bold {{ $value !== null ? ($value >= 75 ? 'text-emerald-600' : 'text-amber-600') : 'text-bluedark/30' }}">{{ $fmt($value) }}</span>
              @if($score && (float) $score->late_deduction > 0)
                <div class="text-[10px] text-red-500">potongan telat -{{ $fmt($score->late_deduction) }}</div>
              @endif
            </td>
            <td class="py-2.5 pr-3 text-center text-bluedark/60">{{ $fmt($column->max_score) }}</td>
            <td class="py-2.5 text-bluedark/70 max-w-[220px]">
              {{ $feedback ?? '-' }}
              @if($score && $score->rubricScores->isNotEmpty())
                <button type="button" class="block text-[10px] font-semibold text-blueprim hover:underline mt-0.5" data-modal-open="rubricModal{{ $score->id }}">Lihat rincian rubrik</button>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="py-6 text-center text-bluedark/50">Belum ada komponen nilai yang dapat ditampilkan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($notes->isNotEmpty())
    <div class="panel p-4 lg:p-5">
      <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-3">Catatan Guru</h2>
      <div class="flex flex-col gap-2">
        @foreach($notes as $note)
          <div class="p-3 rounded-xl bg-bluelight/40 border border-bluelight">
            <div class="flex items-center gap-2 mb-1">
              <span class="badge {{ strtoupper($note->category ?? '') === 'REMEDIAL' ? 'badge-yellow' : 'badge-blue' }}">{{ $note->category ?? 'Catatan' }}</span>
              <span class="text-[10px] text-bluedark/45">{{ $note->teacher?->full_name ?? 'Guru' }} &middot; {{ $note->created_at?->format('d M Y') }}</span>
            </div>
            <p class="text-xs text-bluedark/80">{{ $note->note }}</p>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>

@foreach($gradebook->columns as $column)
  @php $score = $scores->get($column->id); @endphp
  @if($score && $score->rubricScores->isNotEmpty())
    <div class="modal-overlay" id="rubricModal{{ $score->id }}">
      <div class="modal-box max-w-md">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-heading font-semibold text-bluedark text-sm">Rincian Rubrik — {{ $column->name }}</h3>
          <button type="button" class="text-bluedark/50 hover:text-bluedark p-1" data-modal-close>&times;</button>
        </div>
        <table class="w-full text-xs">
          <thead>
            <tr class="text-left text-bluedark/50 border-b border-bluelight">
              <th class="py-1.5 pr-2 font-semibold">Kriteria</th>
              <th class="py-1.5 font-semibold text-right">Poin</th>
            </tr>
          </thead>
          <tbody>
            @foreach($score->rubricScores as $rubricScore)
              <tr class="border-b border-bluelight/60">
                <td class="py-1.5 pr-2">
                  <div class="font-semibold text-bluedark">{{ $rubricScore->criterion?->criterion ?? '-' }}</div>
                  @if($rubricScore->note)<div class="text-[10px] text-bluedark/45">{{ $rubricScore->note }}</div>@endif
                </td>
                <td class="py-1.5 text-right font-semibold text-bluedark">{{ $fmt($rubricScore->points_awarded) }} / {{ $fmt($rubricScore->criterion?->max_points) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
@endforeach
@endsection
