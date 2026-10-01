@extends('layouts.admin')

@section('title', 'Jadwal Mengajar & Jam Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Jadwal Pelajaran &amp; Jam Belajar</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola jam pelajaran sekolah dan jadwal mingguan tiap rombel</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openPeriodModal()" class="btn btn-outline btn-sm flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Jam Pelajaran Baru</span>
            </button>
            <button type="button" onclick="openScheduleModal()" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Jadwal Mapel Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Kelas -->
    <div class="panel p-4 flex items-center justify-between gap-3 flex-wrap">
        <form method="GET" action="{{ route('admin.academic.schedules.index') }}" class="flex items-center gap-3">
            <label class="text-xs font-semibold text-bluedark">Pilih Kelas:</label>
            <select name="class_id" onchange="this.form.submit()" class="f-select text-xs py-1.5 w-64">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->department?->name }})
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs text-bluedark/50">Jadwal Kelas: <strong>{{ $selectedClass?->name ?? 'Belum ada kelas' }}</strong></span>
    </div>

    <!-- Grid Timetable Mingguan -->
    <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">
            Jadwal Mingguan: {{ $selectedClass?->name }}
        </h2>

        <div class="grid md:grid-cols-5 gap-4">
            @foreach($days as $dayNum => $dayName)
                @php
                    $daySchedules = $schedules->where('day_of_week', $dayNum)->sortBy(fn($s) => $s->startPeriod?->period_number ?? 0);
                @endphp
                <div class="rounded-2xl border border-bluelight bg-bluelight/10 p-3.5 flex flex-col justify-between">
                    <div>
                        <div class="font-heading font-bold text-sm text-bluedark pb-2 mb-3 border-b border-bluelight flex items-center justify-between">
                            <span>{{ $dayName }}</span>
                            <span class="badge badge-blue text-[10px]">{{ $daySchedules->count() }} Mapel</span>
                        </div>

                        <div class="space-y-2.5">
                            @forelse($daySchedules as $sch)
                                <div class="p-2.5 rounded-xl border border-bluelight bg-white shadow-2xs space-y-1">
                                    <div class="flex items-start justify-between gap-1">
                                        <div class="font-semibold text-xs text-bluedark line-clamp-1">
                                            {{ $sch->teachingAssignment?->subject?->name }}
                                        </div>
                                        <form action="{{ route('admin.academic.schedules.destroy', $sch) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold leading-none">&times;</button>
                                        </form>
                                    </div>

                                    <div class="text-[11px] text-bluedark/60 truncate">
                                        {{ $sch->teachingAssignment?->teacher?->full_name }}
                                    </div>

                                    <div class="text-[10px] text-blueprim font-medium flex items-center justify-between pt-1 border-t border-bluelight/40">
                                        <span>Jam {{ $sch->startPeriod?->period_number }} - {{ $sch->endPeriod?->period_number }}</span>
                                        <span>{{ $sch->room ?? 'R. Kelas' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-bluedark/40 italic">
                                    Tidak ada jadwal.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Panel Jam Pelajaran Terdaftar -->
    <div class="panel p-5">
        <h3 class="font-heading font-semibold text-bluedark text-sm mb-3">Master Jam Pelajaran</h3>
        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            @forelse($periods as $p)
                <div class="px-3 py-2 rounded-xl border border-bluelight bg-white text-center shrink-0">
                    <div class="text-xs font-bold text-bluedark">Jam ke-{{ $p->period_number }}</div>
                    <div class="text-[10px] text-bluedark/50">{{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($p->end_time)->format('H:i') }}</div>
                </div>
            @empty
                <div class="text-xs text-bluedark/40 italic">Belum ada jam pelajaran terdaftar.</div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Tambah Jam Pelajaran -->
<div id="periodModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Tambah Jam Pelajaran</h3>
            <button type="button" onclick="closePeriodModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.schedules.periods.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="f-label">Jam Ke- (Nomor Urut)</label>
                <input type="number" name="period_number" min="1" max="15" required class="f-input" placeholder="1">
            </div>

            <div>
                <label class="f-label">Label Jam (cth: Jam Ke-1)</label>
                <input type="text" name="label" required class="f-input" placeholder="Jam Ke-1">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Mulai (WIB)</label>
                    <input type="text" name="start_time" required class="f-input" placeholder="07:00">
                </div>
                <div>
                    <label class="f-label">Selesai (WIB)</label>
                    <input type="text" name="end_time" required class="f-input" placeholder="07:45">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePeriodModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Jadwal Pelajaran -->
<div id="scheduleModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Tambah Jadwal Mengajar</h3>
            <button type="button" onclick="closeScheduleModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.schedules.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Mata Pelajaran &amp; Guru</label>
                <select name="teaching_assignment_id" required class="f-select">
                    <option value="">-- Pilih Penugasan Mengajar --</option>
                    @foreach($assignments as $a)
                        <option value="{{ $a->id }}">{{ $a->subject?->name }} — {{ $a->teacher?->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Hari</label>
                <select name="day_of_week" required class="f-select">
                    @foreach($days as $num => $day)
                        <option value="{{ $num }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Mulai Jam Ke-</label>
                    <select name="start_period_id" required class="f-select">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}">Jam {{ $p->period_number }} ({{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f-label">Selesai Jam Ke-</label>
                    <select name="end_period_id" required class="f-select">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}">Jam {{ $p->period_number }} ({{ \Carbon\Carbon::parse($p->end_time)->format('H:i') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="f-label">Ruangan (Opsional)</label>
                <input type="text" name="room" placeholder="Lab Komputer 2 / R. Teori 3" class="f-input">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeScheduleModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPeriodModal() {
        document.getElementById('periodModal').classList.remove('hidden');
    }
    function closePeriodModal() {
        document.getElementById('periodModal').classList.add('hidden');
    }
    function openScheduleModal() {
        document.getElementById('scheduleModal').classList.remove('hidden');
    }
    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
