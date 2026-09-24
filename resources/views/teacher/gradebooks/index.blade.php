@extends('layouts.teacher')

@section('title', 'Buku Nilai Digital')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">Buku Nilai Digital</h1>
            <p class="text-sm text-bluedark/70 mt-1">Pilih rombongan belajar untuk mengelola rekap nilai kompetensi siswa</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-blue">Tahun Ajaran 2026/2027</span>
            <span class="badge badge-gray">Gasal</span>
        </div>
    </div>

    <!-- Class Cards Grid -->
    <div class="panel p-5">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-[16px]">Daftar Rombel / Kelas</h2>
                <p class="text-xs text-bluedark/50">Klik kelas untuk membuka spreadsheet buku nilai</p>
            </div>
            <span class="text-xs font-semibold text-blueprim">{{ $assignments->count() }} Kelas Aktif</span>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($assignments as $assignment)
                @php
                    $gradebook = $assignment->gradebooks->first();
                @endphp
                <div class="kelas-card group">
                    <div class="flex items-center justify-between mb-2">
                        <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
                        </div>
                        @if($gradebook)
                            <a href="{{ route('teacher.gradebooks.show', $gradebook) }}" class="text-bluesoft hover:text-blueprim p-1" title="Buka Spreadsheet">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        @endif
                    </div>

                    <div class="font-heading font-bold text-bluedark text-base">
                        {{ $assignment->schoolClass?->name ?? 'Kelas' }}
                    </div>
                    <div class="text-xs text-bluedark/60 font-medium">
                        {{ $assignment->subject?->name ?? 'Mata Pelajaran' }}
                    </div>

                    <div class="kelas-card__meta">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ $assignment->schoolClass?->students_count ?? 36 }} Siswa
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $gradebook?->columns?->count() ?? 0 }} Kolom Nilai
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-100">
                        <span class="badge badge-blue">{{ $assignment->semester?->academicYear?->name ?? '2026/2027' }}</span>
                        <span class="badge badge-gray">{{ $assignment->semester?->semester_number == 1 ? 'Gasal' : 'Genap' }}</span>
                    </div>

                    <div class="mt-4">
                        @if($gradebook)
                            <a href="{{ route('teacher.gradebooks.show', $gradebook) }}" class="btn btn-primary btn-sm w-full shadow-xs">
                                Buka Buku Nilai
                            </a>
                        @else
                            <button type="button" disabled class="btn btn-outline btn-sm w-full opacity-50 cursor-not-allowed">
                                Belum Dikonfigurasi
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-xs text-bluedark/50">
                    Belum ada rombel atau buku nilai yang terdaftar pada akun Anda.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
