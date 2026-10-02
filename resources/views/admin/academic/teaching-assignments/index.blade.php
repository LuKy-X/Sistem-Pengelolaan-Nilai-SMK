@extends('layouts.admin')

@section('title', 'Penugasan Guru Mengajar')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penugasan Guru Mengajar</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola distribusi pengajaran guru per rombel dan semester aktif</p>
        </div>

        <button type="button" onclick="openAssignmentModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Penugasan Baru</span>
        </button>
    </div>

    <!-- Filter Semester Panel -->
    <div class="panel p-4 flex items-center justify-between gap-3 flex-wrap bg-white">
        <form method="GET" action="{{ route('admin.academic.teaching-assignments.index') }}" class="flex items-center gap-3">
            <label class="text-xs font-semibold text-bluedark">Filter Semester:</label>
            <select name="semester_id" onchange="this.form.submit()" class="f-select text-xs py-1.5 w-64">
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->name }} ({{ $sem->academicYear?->name }}) {{ $sem->is_active ? '— Aktif' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs text-bluedark/50">Total Penugasan: <strong>{{ $assignments->count() }}</strong></span>
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
                        <th class="w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $idx => $ta)
                        <tr>
                            <td>{{ $assignments->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">
                                <div class="leading-tight">
                                    <div>{{ $ta->teacher?->full_name }}</div>
                                    <div class="text-[11px] text-bluedark/50">NIP: {{ $ta->teacher?->nip }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $ta->subject?->name }}</div>
                                <div class="text-[10px] text-bluedark/50">Kode: {{ $ta->subject?->code }}</div>
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
                            <td>
                                <form action="{{ route('admin.academic.teaching-assignments.destroy', $ta) }}" method="POST" onsubmit="return confirm('Hapus penugasan guru ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs py-1 px-2.5">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada penugasan mengajar pada semester yang dipilih.
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

            <div>
                <label class="f-label">Semester</label>
                <select name="semester_id" required class="f-select">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                            {{ $sem->name }} ({{ $sem->academicYear?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Guru Pengajar</label>
                <select name="teacher_id" required class="f-select">
                    <option value="">-- Pilih Guru --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->nip }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Mata Pelajaran</label>
                <select name="subject_id" required class="f-select">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Kelas / Rombel</label>
                <select name="class_id" required class="f-select">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->department?->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Alokasi Jam per Minggu</label>
                <input type="number" name="weekly_hours" min="1" max="20" value="2" required class="f-input">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-blueprim">
                <label class="text-xs font-medium text-bluedark">Penugasan Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeAssignmentModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Penugasan</button>
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
</script>
@endpush
@endsection
