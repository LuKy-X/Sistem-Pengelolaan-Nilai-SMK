@extends('layouts.admin')

@section('title', 'Penugasan Guru Mengajar')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penugasan Guru Mengajar</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola distribusi pengajaran guru per rombel dan semester aktif (termasuk Guru BK)</p>
        </div>

        <button type="button" onclick="openAssignmentModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Penugasan Baru</span>
        </button>
    </div>

    <!-- Filter & Pencarian Panel -->
    <div class="panel p-4 flex flex-col md:flex-row items-center justify-between gap-3 bg-white">
        <form method="GET" action="{{ route('admin.academic.teaching-assignments.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Filter Semester -->
            <div>
                <label class="text-[11px] font-bold text-bluedark/60 block mb-1">Semester:</label>
                <select name="semester_id" onchange="this.form.submit()" class="f-select text-xs py-1.5 w-56">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                            {{ $sem->name }} ({{ $sem->academicYear?->name }}) {{ $sem->is_active ? '— Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas / Rombel -->
            <div>
                <label class="text-[11px] font-bold text-bluedark/60 block mb-1">Kelas / Rombel:</label>
                <select name="class_id" onchange="this.form.submit()" class="f-select text-xs py-1.5 w-44">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Pencarian -->
            <div>
                <label class="text-[11px] font-bold text-bluedark/60 block mb-1">Pencarian:</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari Guru / Mapel..." class="f-input text-xs py-1.5 w-48">
            </div>

            <div class="flex items-center gap-2 pt-5">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-1.5">
                    Filter
                </button>
                @if($search || $selectedClassId)
                    <a href="{{ route('admin.academic.teaching-assignments.index', ['semester_id' => $selectedSemesterId]) }}" class="text-xs text-rose-600 hover:underline">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <span class="text-xs text-bluedark/50 pt-2 md:pt-0">Total Penugasan: <strong>{{ $assignments->total() }}</strong></span>
    </div>

    <!-- Table Penugasan Mengajar -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Guru Pengajar</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas / Rombel</th>
                        <th>Semester</th>
                        <th>Jam / Minggu</th>
                        <th class="w-24">Status</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $idx => $ta)
                        @php
                            $isCounselorTeacher = $ta->teacher?->isCounselor();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td>{{ $assignments->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">
                                <div class="leading-tight">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $ta->teacher?->full_name }}</span>
                                        @if($isCounselorTeacher)
                                            <span class="badge text-[9.5px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                Guru BK
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-bluedark/50 mt-0.5 font-mono">NIP: {{ $ta->teacher?->nip }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-bluedark">{{ $ta->subject?->name }}</div>
                                <div class="text-[10px] text-bluedark/50 font-mono">Kode: {{ $ta->subject?->code }}</div>
                            </td>
                            <td>
                                <span class="font-semibold text-bluedark">{{ $ta->schoolClass?->name }}</span>
                                <span class="badge badge-gray text-[10px] block mt-0.5">{{ $ta->schoolClass?->department?->name }}</span>
                            </td>
                            <td>{{ $ta->semester?->name }}</td>
                            <td>
                                <span class="badge badge-blue font-semibold">{{ $ta->weekly_hours }} Jam / Mg</span>
                            </td>
                            <td>
                                <span class="badge {{ $ta->is_active ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $ta->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit -->
                                    <button type="button"
                                            onclick="openEditAssignmentModal({{ json_encode([
                                                'id' => $ta->id,
                                                'semester_id' => $ta->semester_id,
                                                'teacher_id' => $ta->teacher_id,
                                                'subject_id' => $ta->subject_id,
                                                'class_id' => $ta->class_id,
                                                'weekly_hours' => $ta->weekly_hours,
                                                'is_active' => (bool) $ta->is_active,
                                                'update_url' => route('admin.academic.teaching-assignments.update', $ta),
                                            ]) }})"
                                            class="btn btn-sm btn-outline text-blueprim hover:bg-blue-50 border-blue-200 text-xs py-1 px-2.5 flex items-center gap-1"
                                            title="Ubah Penugasan">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.academic.teaching-assignments.destroy', $ta) }}" method="POST" onsubmit="return confirm('Hapus penugasan guru ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs py-1 px-2.5 flex items-center gap-1" title="Hapus Penugasan">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada penugasan mengajar pada filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignments->hasPages())
            <div class="mt-4 pt-3 border-t border-bluelight">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Penugasan -->
<div id="assignmentModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Tambah Penugasan Mengajar</h3>
            <button type="button" onclick="closeAssignmentModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.teaching-assignments.store') }}" class="space-y-4">
            @csrf

            @if($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">
                    <p class="font-semibold mb-1">Gagal menyimpan penugasan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="f-label">Semester <span class="text-rose-500">*</span></label>
                <select name="semester_id" required class="f-select @error('semester_id') border-rose-500 @enderror">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ (old('semester_id', $selectedSemesterId) == $sem->id) ? 'selected' : '' }}>
                            {{ $sem->name }} ({{ $sem->academicYear?->name }})
                        </option>
                    @endforeach
                </select>
                @error('semester_id')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="f-label">Guru Pengajar <span class="text-rose-500">*</span></label>
                <select name="teacher_id" required class="f-select @error('teacher_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Guru / Guru BK --</option>
                    <optgroup label="Guru Mata Pelajaran">
                        @foreach($regularTeachers as $t)
                            <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }} (NIP: {{ $t->nip }})
                            </option>
                        @endforeach
                    </optgroup>
                    @if($counselorTeachers->isNotEmpty())
                        <optgroup label="Guru Bimbingan Konseling (BK)">
                            @foreach($counselorTeachers as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->full_name }} (NIP: {{ $t->nip }}) [Guru BK]
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
                @error('teacher_id')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="f-label">Mata Pelajaran <span class="text-rose-500">*</span></label>
                <select name="subject_id" required class="f-select @error('subject_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>[{{ $s->code }}] {{ $s->name }}</option>
                    @endforeach
                </select>
                @error('subject_id')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="f-label">Kelas / Rombel <span class="text-rose-500">*</span></label>
                <select name="class_id" required class="f-select @error('class_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (old('class_id', $selectedClassId) == $c->id) ? 'selected' : '' }}>{{ $c->name }} ({{ $c->department?->name }})</option>
                    @endforeach
                </select>
                @error('class_id')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="f-label">Alokasi Jam per Minggu <span class="text-rose-500">*</span></label>
                <input type="number" name="weekly_hours" min="1" max="20" value="{{ old('weekly_hours', 2) }}" required class="f-input @error('weekly_hours') border-rose-500 @enderror">
                <span class="text-[11px] text-bluedark/50 block mt-1">Total jam maksimal yang dapat dijadwalkan untuk guru di kelas ini per minggu</span>
                @error('weekly_hours')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded text-blueprim">
                <label class="text-xs font-medium text-bluedark">Penugasan Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeAssignmentModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Penugasan -->
<div id="editAssignmentModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Edit Penugasan Mengajar</h3>
            <button type="button" onclick="closeEditAssignmentModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="editAssignmentForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="f-label">Semester <span class="text-rose-500">*</span></label>
                <select name="semester_id" id="editSemesterId" required class="f-select">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">
                            {{ $sem->name }} ({{ $sem->academicYear?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Guru Pengajar <span class="text-rose-500">*</span></label>
                <select name="teacher_id" id="editTeacherId" required class="f-select">
                    <option value="">-- Pilih Guru / Guru BK --</option>
                    <optgroup label="Guru Mata Pelajaran">
                        @foreach($regularTeachers as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->full_name }} (NIP: {{ $t->nip }})
                            </option>
                        @endforeach
                    </optgroup>
                    @if($counselorTeachers->isNotEmpty())
                        <optgroup label="Guru Bimbingan Konseling (BK)">
                            @foreach($counselorTeachers as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->full_name }} (NIP: {{ $t->nip }}) [Guru BK]
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div>
                <label class="f-label">Mata Pelajaran <span class="text-rose-500">*</span></label>
                <select name="subject_id" id="editSubjectId" required class="f-select">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Kelas / Rombel <span class="text-rose-500">*</span></label>
                <select name="class_id" id="editClassId" required class="f-select">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->department?->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Alokasi Jam per Minggu <span class="text-rose-500">*</span></label>
                <input type="number" name="weekly_hours" id="editWeeklyHours" min="1" max="20" required class="f-input">
                <span class="text-[11px] text-bluedark/50 block mt-1">Total jam maksimal yang dapat dijadwalkan untuk guru di kelas ini per minggu</span>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" class="rounded text-blueprim">
                <label class="text-xs font-medium text-bluedark">Penugasan Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeEditAssignmentModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Perbarui Penugasan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAssignmentModal() {
        document.getElementById('assignmentModal').classList.remove('hidden');
    }
    function closeAssignmentModal() {
        document.getElementById('assignmentModal').classList.add('hidden');
    }

    function openEditAssignmentModal(data) {
        const form = document.getElementById('editAssignmentForm');
        form.action = data.update_url;

        document.getElementById('editSemesterId').value = data.semester_id;
        document.getElementById('editTeacherId').value = data.teacher_id;
        document.getElementById('editSubjectId').value = data.subject_id;
        document.getElementById('editClassId').value = data.class_id;
        document.getElementById('editWeeklyHours').value = data.weekly_hours;
        document.getElementById('editIsActive').checked = Boolean(data.is_active);

        document.getElementById('editAssignmentModal').classList.remove('hidden');
    }

    function closeEditAssignmentModal() {
        document.getElementById('editAssignmentModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAssignmentModal();
            closeEditAssignmentModal();
        }
    });

    @if($errors->any() || (session('error') && old('teacher_id')))
    document.addEventListener('DOMContentLoaded', function() {
        openAssignmentModal();
    });
    @endif
</script>
@endpush
@endsection
