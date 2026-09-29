@extends('layouts.admin')

@section('title', 'Manajemen Rombel / Kelas')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Rombel / Kelas</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola rombongan belajar, wali kelas, dan jurusan</p>
        </div>

        <button type="button" onclick="openClassModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Kelas Baru</span>
        </button>
    </div>

    <!-- Step Navigation matching template -->
    <div class="step-nav max-w-xl">
        <a href="{{ route('admin.academic.departments.index') }}" class="step-nav-item">
            1. Daftar Jurusan
        </a>
        <a href="{{ route('admin.academic.classes.index') }}" class="step-nav-item active">
            2. Daftar Kelas
        </a>
        <a href="{{ route('admin.academic.students.index') }}" class="step-nav-item">
            3. Daftar Siswa
        </a>
    </div>

    <!-- Grid Kelas Cards matching template -->
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($classes as $c)
            <div class="kelas-card group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-bluedark text-sm">{{ $c->name }}</h3>
                                <p class="text-[11px] text-bluedark/50">{{ $c->department?->name ?? '-' }}</p>
                            </div>
                        </div>

                        <span class="badge {{ $c->is_active ? 'badge-green' : 'badge-gray' }} text-[10px]">
                            {{ $c->gradeLevel?->name ?? 'Tingkat' }}
                        </span>
                    </div>

                    <div class="kelas-card__meta text-xs text-bluedark/60 space-y-1">
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <span>Wali Kelas: <strong class="text-bluedark">{{ $c->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>{{ $c->class_enrollments_count }} Siswa Terdaftar</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>{{ $c->academicYear?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-bluelight mt-3">
                    <a href="{{ route('admin.academic.classes.show', $c) }}" class="text-xs font-semibold text-blueprim hover:underline">
                        Detail Kelas &rarr;
                    </a>

                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="editClass({{ json_encode($c) }})" class="btn btn-sm btn-outline text-xs px-2.5">
                            Edit
                        </button>
                        <form action="{{ route('admin.academic.classes.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus rombel ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs px-2.5">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-xs text-bluedark/40">
                Belum ada data rombongan belajar (kelas). Silakan buat kelas baru.
            </div>
        @endforelse
    </div>

</div>

<!-- Modal Tambah/Edit Kelas -->
<div id="classModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="classModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Kelas Baru</h3>
            <button type="button" onclick="closeClassModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="classForm" method="POST" action="{{ route('admin.academic.classes.store') }}" class="space-y-4">
            @csrf
            <div id="classMethodField"></div>

            <div>
                <label class="f-label">Nama Kelas (cth: XII RA, X RPL 1)</label>
                <input type="text" name="name" id="class_name" required placeholder="XII RA" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Kode Kelas</label>
                    <input type="text" name="code" id="class_code" required placeholder="XII-RA" class="f-input">
                </div>
                <div>
                    <label class="f-label">Tingkat Kelas</label>
                    <select name="grade_level_id" id="class_grade_level_id" required class="f-select">
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Jurusan</label>
                    <select name="department_id" id="class_department_id" required class="f-select">
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f-label">Tahun Ajaran</label>
                    <select name="academic_year_id" id="class_academic_year_id" required class="f-select">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="f-label">Wali Kelas</label>
                <select name="homeroom_teacher_id" id="class_homeroom_teacher_id" class="f-select">
                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="class_is_active" value="1" checked class="rounded text-blueprim">
                <label for="class_is_active" class="text-xs font-medium text-bluedark">Kelas Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeClassModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openClassModal() {
        document.getElementById('classModalTitle').innerText = 'Tambah Kelas Baru';
        document.getElementById('classForm').action = "{{ route('admin.academic.classes.store') }}";
        document.getElementById('classMethodField').innerHTML = '';
        document.getElementById('class_name').value = '';
        document.getElementById('class_code').value = '';
        document.getElementById('class_homeroom_teacher_id').value = '';
        document.getElementById('class_is_active').checked = true;
        document.getElementById('classModal').classList.remove('hidden');
    }

    function editClass(c) {
        document.getElementById('classModalTitle').innerText = 'Edit Kelas';
        document.getElementById('classForm').action = "/admin/academic/classes/" + c.id;
        document.getElementById('classMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('class_name').value = c.name;
        document.getElementById('class_code').value = c.code;
        document.getElementById('class_grade_level_id').value = c.grade_level_id;
        document.getElementById('class_department_id').value = c.department_id;
        document.getElementById('class_academic_year_id').value = c.academic_year_id;
        document.getElementById('class_homeroom_teacher_id').value = c.homeroom_teacher_id || '';
        document.getElementById('class_is_active').checked = !!c.is_active;
        document.getElementById('classModal').classList.remove('hidden');
    }

    function closeClassModal() {
        document.getElementById('classModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
