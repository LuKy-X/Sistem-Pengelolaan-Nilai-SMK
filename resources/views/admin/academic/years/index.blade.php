@extends('layouts.admin')

@section('title', 'Tahun Ajaran & Semester')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Tahun Ajaran &amp; Semester</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola kalender akademik, tahun ajaran aktif, dan periode semester</p>
        </div>

        <button type="button" onclick="openYearModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tahun Ajaran Baru</span>
        </button>
    </div>

    <!-- Active Academic Year Banner matching template -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Tahun Ajaran Aktif Saat Ini</h2>
        </div>

        @if($activeYear)
            <div class="rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap bg-gradient-to-r from-blueprim to-bluedark text-white shadow-md">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="crud-card__icon bg-white/20 text-white rounded-xl p-3">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-heading font-bold text-lg leading-tight truncate">Tahun Ajaran {{ $activeYear->name }}</div>
                        <div class="text-xs text-white/80 mt-1">
                            Periode: {{ \Carbon\Carbon::parse($activeYear->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($activeYear->end_date)->translatedFormat('d M Y') }}
                            &middot; {{ $activeYear->semesters->count() }} Semester Terdaftar
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="badge bg-white/20 text-white border-0 px-3 py-1 font-semibold text-xs">Aktif</span>
                    <button type="button" onclick="openSemesterModal({{ $activeYear->id }})" class="btn btn-sm bg-white text-bluedark hover:bg-bluelight transition-colors">
                        + Tambah Semester
                    </button>
                </div>
            </div>
        @else
            <div class="rounded-xl p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                Belum ada tahun ajaran yang berstatus aktif. Silakan aktifkan salah satu tahun ajaran di bawah ini.
            </div>
        @endif
    </div>

    <!-- Daftar Seluruh Tahun Ajaran & Semester Terhubung -->
    <div class="panel p-5">
        <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">Daftar Seluruh Tahun Ajaran</h2>

        <div class="space-y-4">
            @forelse($academicYears as $year)
                <div class="rounded-2xl border border-bluelight p-4 hover:border-bluesoft transition-colors {{ $year->is_active ? 'bg-blue-50/40 border-blueprim' : 'bg-white' }}">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-bluelight text-bluedark flex items-center justify-center shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div>
                                <div class="font-heading font-bold text-bluedark text-base">Tahun Ajaran {{ $year->name }}</div>
                                <div class="text-xs text-bluedark/50">
                                    {{ \Carbon\Carbon::parse($year->start_date)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($year->end_date)->translatedFormat('d M Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <form action="{{ route('admin.academic.years.toggle-active', $year) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $year->is_active ? 'btn-primary' : 'btn-outline' }}">
                                    {{ $year->is_active ? 'Sedang Aktif' : 'Jadikan Aktif' }}
                                </button>
                            </form>

                            <button type="button" onclick="editYear({{ json_encode($year) }})" class="btn btn-outline btn-sm">
                                Edit
                            </button>

                            <form action="{{ route('admin.academic.years.destroy', $year) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sub-section: Semesters List -->
                    <div class="mt-4 pt-3 border-t border-bluelight/60">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-bluedark/70">Semester dalam Tahun Ajaran Ini:</span>
                            <button type="button" onclick="openSemesterModal({{ $year->id }})" class="text-xs font-medium text-blueprim hover:underline">
                                + Tambah Semester
                            </button>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-2.5">
                            @forelse($year->semesters as $sem)
                                <div class="p-2.5 rounded-xl border border-bluelight bg-white flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-semibold text-bluedark">{{ $sem->name }}</span>
                                        <span class="text-bluedark/50 block text-[11px]">
                                            {{ \Carbon\Carbon::parse($sem->start_date)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($sem->end_date)->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                    <form action="{{ route('admin.academic.semesters.toggle-active', $sem) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="badge {{ $sem->is_active ? 'badge-green' : 'badge-gray' }} hover:scale-105 transition-transform cursor-pointer">
                                            {{ $sem->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="col-span-full py-2 text-xs text-bluedark/40 italic">
                                    Belum ada semester yang dibuat untuk tahun ajaran ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-xs text-bluedark/40">
                    Belum ada data tahun ajaran. Silakan tambahkan tahun ajaran pertama Anda.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Tambah/Edit Tahun Ajaran -->
<div id="yearModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="yearModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Tahun Ajaran</h3>
            <button type="button" onclick="closeYearModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="yearForm" method="POST" action="{{ route('admin.academic.years.store') }}" class="space-y-4">
            @csrf
            <div id="yearMethodField"></div>

            <div>
                <label class="f-label">Nama Tahun Ajaran (contoh: 2026/2027)</label>
                <input type="text" name="name" id="year_name" required placeholder="2026/2027" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="year_start_date" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="year_end_date" required class="f-input">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="year_is_active" value="1" class="rounded text-blueprim focus:ring-blueprim">
                <label for="year_is_active" class="text-xs font-medium text-bluedark">Jadikan tahun ajaran aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeYearModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Semester -->
<div id="semesterModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Tambah Semester Baru</h3>
            <button type="button" onclick="closeSemesterModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.semesters.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" id="semester_academic_year_id">

            <div>
                <label class="f-label">Nama Semester</label>
                <input type="text" name="name" required placeholder="Semester Gasal / Genap" class="f-input">
            </div>

            <div>
                <label class="f-label">Nomor Semester</label>
                <select name="semester_number" required class="f-select">
                    <option value="1">1 (Gasal / Ganjil)</option>
                    <option value="2">2 (Genap)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" required class="f-input">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="semester_is_active" value="1" class="rounded text-blueprim focus:ring-blueprim">
                <label for="semester_is_active" class="text-xs font-medium text-bluedark">Jadikan semester aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeSemesterModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openYearModal() {
        document.getElementById('yearModalTitle').innerText = 'Tambah Tahun Ajaran Baru';
        document.getElementById('yearForm').action = "{{ route('admin.academic.years.store') }}";
        document.getElementById('yearMethodField').innerHTML = '';
        document.getElementById('year_name').value = '';
        document.getElementById('year_start_date').value = '';
        document.getElementById('year_end_date').value = '';
        document.getElementById('year_is_active').checked = false;
        document.getElementById('yearModal').classList.remove('hidden');
    }

    function editYear(year) {
        document.getElementById('yearModalTitle').innerText = 'Edit Tahun Ajaran';
        document.getElementById('yearForm').action = "/admin/academic/years/" + year.id;
        document.getElementById('yearMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('year_name').value = year.name;
        document.getElementById('year_start_date').value = year.start_date ? year.start_date.substring(0, 10) : '';
        document.getElementById('year_end_date').value = year.end_date ? year.end_date.substring(0, 10) : '';
        document.getElementById('year_is_active').checked = !!year.is_active;
        document.getElementById('yearModal').classList.remove('hidden');
    }

    function closeYearModal() {
        document.getElementById('yearModal').classList.add('hidden');
    }

    function openSemesterModal(yearId) {
        document.getElementById('semester_academic_year_id').value = yearId;
        document.getElementById('semesterModal').classList.remove('hidden');
    }

    function closeSemesterModal() {
        document.getElementById('semesterModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
