@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6">

  {{-- 1. Greeting & Academic Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Dashboard Guru</h1>
      <p class="text-sm text-bluedark/65 mt-1">
        Selamat datang kembali, <span class="font-semibold text-bluedark">{{ $teacher->full_name }}</span>! Pantau seluruh kegiatan pembelajaran Anda di sini.
      </p>
    </div>
    <div class="flex items-center gap-2.5 self-start sm:self-auto bg-white/90 border border-bluelight px-3.5 py-1.5 rounded-xl shadow-xs text-xs font-medium text-bluedark">
      <svg class="w-4 h-4 text-blueprim shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span>{{ $todayFormatted }}</span>
      <span class="text-bluedark/30">&bull;</span>
      <span class="text-blueprim font-semibold">T.A. 2026/2027</span>
    </div>
  </div>



  {{-- 3. KPI Cards Row --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="kpiRow">
    <a href="{{ route('teacher.gradebooks.index') }}" class="kpi-card group hover:border-blueprim/40 transition-colors">
      <div class="kpi-icon bg-bluelight text-bluedark group-hover:scale-105 transition-transform">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalStudents }}</div>
        <div class="text-[11px] text-bluedark/60 mt-1">Total Siswa Diampu</div>
        <div class="text-[10px] text-blueprim font-medium mt-0.5">{{ $totalClasses }} Kelas Terdaftar</div>
      </div>
    </a>

    <a href="{{ route('teacher.grading.index') }}" class="kpi-card group hover:border-amber-400 transition-colors">
      <div class="kpi-icon bg-amber-100 text-amber-600 group-hover:scale-105 transition-transform">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-amber-600 leading-none">{{ $pendingReviews }}</div>
        <div class="text-[11px] text-bluedark/60 mt-1">Tugas Belum Dinilai</div>
        <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Menunggu Evaluasi</div>
      </div>
    </a>

    <a href="{{ route('teacher.assessments.index') }}" class="kpi-card group hover:border-blueprim/40 transition-colors">
      <div class="kpi-icon bg-blueprim/10 text-blueprim group-hover:scale-105 transition-transform">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ $totalAssessments }}</div>
        <div class="text-[11px] text-bluedark/60 mt-1">Total Tugas &amp; UH</div>
        <div class="text-[10px] text-blueprim font-medium mt-0.5">Kelola Asesmen &rarr;</div>
      </div>
    </a>

    <a href="{{ route('teacher.journals.index') }}" class="kpi-card group hover:border-emerald-400 transition-colors">
      <div class="kpi-icon bg-emerald-100 text-emerald-600 group-hover:scale-105 transition-transform">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div class="font-heading text-xl font-bold text-emerald-600 leading-none">{{ $totalJournals }}</div>
        <div class="text-[11px] text-bluedark/60 mt-1">Jurnal Kelas Terisi</div>
        <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Rekap Tatap Muka</div>
      </div>
    </a>
  </div>

  {{-- 4. Main Two-Column Layout --}}
  <div class="grid lg:grid-cols-3 gap-5">

    {{-- LEFT COLUMN (2 Cols on lg) --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Section A: Rata-rata Nilai per Kelas (Main Chart) --}}
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Rata-rata Nilai per Kelas</h2>
            <p class="text-xs text-bluedark/55 mt-0.5">Perhitungan otomatis dari kolom buku nilai yang terakumulasi</p>
          </div>
          <a href="{{ route('teacher.gradebooks.index') }}" class="text-xs font-semibold text-blueprim hover:underline shrink-0">Lihat Buku Nilai &rarr;</a>
        </div>
        <div class="chart-box">
          <canvas id="mainChart"></canvas>
        </div>
        <div class="mt-4 pt-3 border-t border-bluelight/70 flex flex-wrap items-center justify-between gap-2 text-xs text-bluedark/60">
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-blueprim"></span>
              <span>KKM Acuan: <strong class="text-bluedark">75.00</strong></span>
            </span>
            <span class="text-bluedark/30">&bull;</span>
            <span>Total: <strong class="text-bluedark">{{ $totalClasses }} Kelas</strong> Aktif</span>
          </div>
          <a href="{{ route('teacher.gradebooks.index') }}" class="font-medium text-blueprim hover:underline">Kelola Bobot &amp; Skor &rarr;</a>
        </div>
      </div>

      {{-- Section B: Aktivitas Tugas & Ulangan Harian Terbaru --}}
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3.5">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tugas &amp; Ulangan Harian Terbaru</h2>
            <p class="text-xs text-bluedark/55 mt-0.5">Pantau status penugasan dan pengumpulan siswa secara berkala</p>
          </div>
          <a href="{{ route('teacher.assessments.index') }}" class="text-xs font-semibold text-blueprim hover:underline shrink-0">Kelola Semua &rarr;</a>
        </div>

        <div class="space-y-2.5">
          @forelse($recentAssessments as $ass)
            @php
              $typeBadgeClass = match($ass->type?->value ?? $ass->type) {
                'UH' => 'bg-purple-100 text-purple-700 border-purple-200',
                'UTS', 'UAS' => 'bg-amber-100 text-amber-800 border-amber-200',
                default => 'bg-blue-100 text-blue-700 border-blue-200',
              };
              $typeLabel = match($ass->type?->value ?? $ass->type) {
                'UH' => 'Ulangan Harian',
                'UTS' => 'UTS',
                'UAS' => 'UAS',
                default => 'Tugas',
              };
            @endphp
            <div class="p-3 rounded-xl bg-bluelight/20 border border-bluelight hover:border-blueprim/40 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $typeBadgeClass }} uppercase tracking-wider">{{ $typeLabel }}</span>
                  <span class="text-xs font-semibold text-bluedark/70">{{ $ass->teachingAssignment?->schoolClass?->name }} &middot; {{ $ass->teachingAssignment?->subject?->name }}</span>
                </div>
                <div class="font-semibold text-sm text-bluedark truncate">{{ $ass->title }}</div>
                <div class="flex items-center gap-3 text-[11px] text-bluedark/55 mt-1">
                  @if($ass->due_date)
                    <span>Batas: {{ $ass->due_date->format('d M Y, H:i') }}</span>
                    <span class="text-bluedark/30">&bull;</span>
                  @endif
                  <span class="font-medium text-bluedark">{{ $ass->total_submissions_count }} Siswa Mengumpulkan</span>
                  @if($ass->pending_submissions_count > 0)
                    <span class="text-amber-600 font-semibold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                      {{ $ass->pending_submissions_count }} Perlu Dinilai
                    </span>
                  @endif
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                <a href="{{ route('teacher.grading.index', ['assignment_id' => $ass->teaching_assignment_id, 'assessment_id' => $ass->id]) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-blueprim hover:bg-blue-600 text-white transition-colors shadow-xs">
                  Nilai
                </a>
              </div>
            </div>
          @empty
            <div class="text-center py-6 bg-bluelight/20 rounded-xl border border-dashed border-bluelight">
              <p class="text-xs text-bluedark/50">Belum ada tugas atau ulangan harian yang dibuat.</p>
              <a href="{{ route('teacher.assessments.create') }}" class="inline-block mt-2 text-xs font-semibold text-blueprim hover:underline">+ Buat Tugas Pertama</a>
            </div>
          @endforelse
        </div>
      </div>

      {{-- Section C: Riwayat Jurnal Kelas & Absensi Terkini --}}
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3.5">
          <div>
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Riwayat Jurnal &amp; Absensi Terkini</h2>
            <p class="text-xs text-bluedark/55 mt-0.5">Catatan tatap muka dan rekapitulasi kehadiran siswa di kelas</p>
          </div>
          <a href="{{ route('teacher.journals.index') }}" class="text-xs font-semibold text-blueprim hover:underline shrink-0">Buku Jurnal &rarr;</a>
        </div>

        <div class="space-y-2.5">
          @forelse($recentJournals as $journal)
            @php
              $startTime = $journal->startPeriod?->start_time ? substr((string) $journal->startPeriod->start_time, 0, 5) : '';
              $endTime = $journal->endPeriod?->end_time ? substr((string) $journal->endPeriod->end_time, 0, 5) : '';
            @endphp
            <div class="p-3 rounded-xl bg-white border border-bluelight hover:border-blueprim/30 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <span class="text-xs font-bold text-bluedark">{{ $journal->journal_date?->format('d M Y') ?? 'Tanggal -' }}</span>
                  <span class="text-bluedark/30">&bull;</span>
                  <span class="text-xs font-semibold text-blueprim">{{ $journal->teachingAssignment?->schoolClass?->name }}</span>
                  <span class="text-bluedark/30">&bull;</span>
                  <span class="text-[11px] text-bluedark/60">Jam ke-{{ $journal->startPeriod?->period_number }} sd {{ $journal->endPeriod?->period_number }} ({{ $startTime }} - {{ $endTime }})</span>
                </div>
                <div class="text-xs text-bluedark/85 font-medium line-clamp-1">
                  {{ $journal->material ?: 'Materi pembelajaran tidak tercatat' }}
                </div>
                <div class="flex items-center gap-2 mt-2">
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800" title="Siswa Hadir">
                    Hadir: {{ $journal->hadir_count }}
                  </span>
                  @if($journal->sakit_count > 0)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800" title="Sakit">
                      Sakit: {{ $journal->sakit_count }}
                    </span>
                  @endif
                  @if($journal->izin_count > 0)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800" title="Izin">
                      Izin: {{ $journal->izin_count }}
                    </span>
                  @endif
                  @if($journal->alpha_count > 0)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800" title="Alpa / Tanpa Keterangan">
                      Alpa: {{ $journal->alpha_count }}
                    </span>
                  @endif
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                <a href="{{ route('teacher.journals.export.pdf', ['assignment_id' => $journal->teaching_assignment_id]) }}" target="_blank" class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition-colors flex items-center gap-1" title="Unduh Cetak PDF">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <span>PDF</span>
                </a>
                <a href="{{ route('teacher.journals.index', ['assignment_id' => $journal->teaching_assignment_id, 'date' => $journal->journal_date?->format('Y-m-d')]) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-bluelight hover:bg-bluesoft text-bluedark transition-colors">
                  Buka Jurnal
                </a>
              </div>
            </div>
          @empty
            <div class="text-center py-6 bg-bluelight/20 rounded-xl border border-dashed border-bluelight">
              <p class="text-xs text-bluedark/50">Belum ada catatan jurnal mengajar yang tersimpan.</p>
              <a href="{{ route('teacher.journals.create') }}" class="inline-block mt-2 text-xs font-semibold text-blueprim hover:underline">+ Mulai Isi Jurnal Kelas</a>
            </div>
          @endforelse
        </div>
      </div>

    </div>

    {{-- RIGHT COLUMN (1 Col on lg) --}}
    <div class="flex flex-col gap-5">

      {{-- Widget 1: Jadwal Mengajar Hari Ini (STRICT + Without Seconds) --}}
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3.5">
          <div class="min-w-0">
            <h2 class="font-heading font-semibold text-bluedark text-[15px] truncate">Jadwal Mengajar Hari Ini</h2>
            <div class="text-[11px] text-bluedark/55 flex items-center gap-1.5 mt-0.5">
              <span class="inline-block w-2 h-2 rounded-full {{ $todaySchedules->isNotEmpty() ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
              <span class="font-semibold text-bluedark">{{ $todayDayName }}</span>
              <span>&bull; {{ count($todaySchedules) }} Sesi Tatap Muka</span>
            </div>
          </div>
          <a href="{{ route('teacher.journals.index') }}" class="text-xs font-semibold text-blueprim hover:underline shrink-0">Jurnal &rarr;</a>
        </div>

        <div class="flex flex-col gap-2.5" id="jadwalHariIniList">
          @if($todaySchedules->isNotEmpty())
            {{-- Loop ONLY today's real schedules --}}
            @foreach($todaySchedules as $schedule)
              @php
                $startFormatted = $schedule->startPeriod?->start_time ? substr((string) $schedule->startPeriod->start_time, 0, 5) : '';
                $endFormatted = $schedule->endPeriod?->end_time ? substr((string) $schedule->endPeriod->end_time, 0, 5) : '';
                $isJournalFilled = in_array($schedule->teaching_assignment_id, $todayJournalAssignmentIds);
              @endphp
              <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight hover:border-blueprim/40 transition-colors">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <div class="font-bold text-sm text-bluedark truncate">
                      {{ $schedule->teachingAssignment?->schoolClass?->name }}
                    </div>
                    <div class="text-xs font-semibold text-blueprim truncate mt-0.5">
                      {{ $schedule->teachingAssignment?->subject?->name }}
                    </div>
                  </div>
                  @if($schedule->room)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-white text-bluedark border border-bluelight shrink-0">
                      {{ $schedule->room }}
                    </span>
                  @endif
                </div>

                <div class="text-[11.5px] text-bluedark/70 mt-2 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-blueprim shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <span>Jam ke-{{ $schedule->startPeriod?->period_number }} s/d {{ $schedule->endPeriod?->period_number }}</span>
                  <span class="font-semibold text-bluedark">({{ $startFormatted }} - {{ $endFormatted }})</span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-bluelight/60 flex items-center justify-between gap-2">
                  @if($isJournalFilled)
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-100/90 px-2 py-0.5 rounded-lg">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      Jurnal Terisi
                    </span>
                  @else
                    <span class="text-[11px] text-amber-700 font-medium">Jurnal belum diisi</span>
                    <a href="{{ route('teacher.journals.index', ['assignment_id' => $schedule->teaching_assignment_id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-blueprim hover:bg-blue-600 text-white transition-colors shadow-xs">
                      Buka Jurnal &rarr;
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
          @else
            {{-- Clean Empty State for days without schedule (e.g. Saturday, Sunday, free day) --}}
            <div class="py-5 px-4 text-center rounded-xl bg-bluelight/30 border border-bluelight/70">
              <div class="w-10 h-10 mx-auto rounded-full bg-blueprim/10 text-blueprim flex items-center justify-center mb-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <div class="text-xs font-bold text-bluedark">Tidak Ada Jadwal Mengajar Hari Ini</div>
              <div class="text-[11px] text-bluedark/60 mt-1 leading-relaxed">
                Hari ini ({{ $todayDayName }}) Anda tidak memiliki jadwal kelas tatap muka.
              </div>
            </div>
          @endif
        </div>

        {{-- Toggleable Full Weekly Schedule --}}
        <div class="mt-4 pt-3 border-t border-bluelight/70">
          <button type="button" id="toggleWeeklyBtn" class="w-full py-2 px-3 rounded-lg bg-bluelight/50 hover:bg-bluelight text-xs font-semibold text-blueprim flex items-center justify-between transition-colors cursor-pointer">
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
              <span id="toggleWeeklyLabel">Lihat Jadwal Mingguan Lengkap</span>
            </span>
            <svg id="toggleWeeklyChevron" class="w-4 h-4 text-blueprim transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>

          <div id="weeklyScheduleWrap" class="hidden mt-3 space-y-3 max-h-72 overflow-y-auto pr-1">
            @forelse($weeklySchedulesGrouped as $dayNum => $schedules)
              <div class="p-2.5 rounded-lg bg-white border border-bluelight shadow-xs">
                <div class="text-xs font-bold text-bluedark mb-1.5 flex items-center justify-between">
                  <span>Hari {{ $dayNames[$dayNum] ?? 'Hari '.$dayNum }}</span>
                  <span class="text-[10px] font-semibold text-blueprim bg-blueprim/10 px-1.5 py-0.5 rounded">{{ count($schedules) }} Sesi</span>
                </div>
                <div class="space-y-1.5">
                  @foreach($schedules as $ws)
                    @php
                      $wsStart = $ws->startPeriod?->start_time ? substr((string) $ws->startPeriod->start_time, 0, 5) : '';
                      $wsEnd = $ws->endPeriod?->end_time ? substr((string) $ws->endPeriod->end_time, 0, 5) : '';
                    @endphp
                    <div class="text-[11px] p-2 rounded bg-bluelight/30 border border-bluelight/50 flex items-center justify-between gap-1">
                      <div class="min-w-0">
                        <span class="font-bold text-bluedark">{{ $ws->teachingAssignment?->schoolClass?->name }}</span>
                        <span class="text-bluedark/40">&middot;</span>
                        <span class="text-bluedark/80">{{ $ws->teachingAssignment?->subject?->code ?? $ws->teachingAssignment?->subject?->name }}</span>
                        <div class="text-[10px] text-bluedark/60">
                          Jam ke-{{ $ws->startPeriod?->period_number }} sd {{ $ws->endPeriod?->period_number }} ({{ $wsStart }} - {{ $wsEnd }})
                        </div>
                      </div>
                      @if($ws->room)
                        <span class="text-[9.5px] px-1.5 py-0.5 rounded bg-white text-bluedark font-medium shrink-0 border border-bluelight">
                          {{ $ws->room }}
                        </span>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @empty
              <p class="text-xs text-bluedark/50 text-center py-2">Belum ada jadwal mengajar terdaftar.</p>
            @endforelse
          </div>
        </div>
      </div>

      {{-- Widget 2: Tugas Perlu Dinilai (Pending Submissions to Grade) --}}
      <div class="panel p-5">
        <div class="flex items-center justify-between mb-3.5">
          <div class="flex items-center gap-2">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tugas Perlu Dinilai</h2>
            @if($pendingReviews > 0)
              <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                {{ $pendingReviews }}
              </span>
            @endif
          </div>
          <a href="{{ route('teacher.grading.index') }}" class="text-xs font-semibold text-blueprim hover:underline">Semua Penilaian &rarr;</a>
        </div>

        <div class="flex flex-col gap-2" id="tugasBelumDinilaiList">
          @forelse($recentPendingSubmissions as $sub)
            @php
              $gradingUrl = route('teacher.grading.index', array_filter([
                'assignment_id' => $sub->assessment?->teaching_assignment_id,
                'gradebook_id' => $sub->assessment?->gradebookColumn?->gradebook_id,
                'column_id' => $sub->assessment?->gradebook_column_id,
                'assessment_id' => $sub->assessment_id,
                'student_id' => $sub->student_id,
              ]));
            @endphp
            <div class="hl-row hl-amber flex items-center justify-between gap-2 p-2.5 rounded-xl border border-amber-200 bg-amber-50/70">
              <a href="{{ $gradingUrl }}" class="min-w-0 flex-1 hover:underline group">
                <div class="font-semibold text-xs text-bluedark truncate group-hover:text-amber-900 transition-colors">{{ $sub->assessment?->title }}</div>
                <div class="text-[11px] text-bluedark/60 truncate mt-0.5">
                  <span class="font-medium text-amber-900">{{ $sub->student?->full_name }}</span> &middot; {{ $sub->assessment?->teachingAssignment?->schoolClass?->name }}
                </div>
              </a>
              <a href="{{ $gradingUrl }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white shrink-0 ml-1.5 transition-colors cursor-pointer shadow-xs">
                Nilai
              </a>
            </div>
          @empty
            <div class="text-center py-6 bg-emerald-50/50 rounded-xl border border-emerald-100">
              <div class="w-8 h-8 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </div>
              <p class="text-xs font-semibold text-emerald-800">Semua tugas siswa telah dinilai!</p>
              <p class="text-[10.5px] text-emerald-700/70 mt-0.5">Tidak ada kiriman tugas yang menunggu pemeriksaan saat ini.</p>
            </div>
          @endforelse
        </div>
      </div>

    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Toggle Jadwal Mingguan
  const toggleBtn = document.getElementById("toggleWeeklyBtn");
  const weeklyWrap = document.getElementById("weeklyScheduleWrap");
  const toggleLabel = document.getElementById("toggleWeeklyLabel");
  const toggleChevron = document.getElementById("toggleWeeklyChevron");

  if (toggleBtn && weeklyWrap) {
    toggleBtn.addEventListener("click", () => {
      const isHidden = weeklyWrap.classList.contains("hidden");
      weeklyWrap.classList.toggle("hidden", !isHidden);
      if (toggleLabel) {
        toggleLabel.textContent = isHidden ? "Tutup Jadwal Mingguan" : "Lihat Jadwal Mingguan Lengkap";
      }
      if (toggleChevron) {
        toggleChevron.style.transform = isHidden ? "rotate(180deg)" : "rotate(0deg)";
      }
    });
  }

  // Chart Nilai Rata-rata Kelas
  const ctx = document.getElementById("mainChart");
  if (ctx && window.Chart) {
    const labels = @json($chartLabels);
    const data = @json($chartAverages);

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels.length ? labels : ["XII RA", "XII TA", "XII OA", "XII MA"],
        datasets: [{
          label: "Rata-rata Nilai",
          data: data.length ? data : [82, 79, 85, 77],
          backgroundColor: "#90CAF9",
          hoverBackgroundColor: "#2196F3",
          borderRadius: 8,
          maxBarThickness: 42
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return ' Rata-rata: ' + context.parsed.y.toFixed(1);
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              font: { family: 'Inter', size: 11 },
              color: '#5C7C9E',
              stepSize: 20
            },
            grid: { color: '#E3F2FD' }
          },
          x: {
            ticks: {
              font: { family: 'Inter', size: 11 },
              color: '#5C7C9E'
            },
            grid: { display: false }
          }
        }
      }
    });
  }
});
</script>
@endpush
