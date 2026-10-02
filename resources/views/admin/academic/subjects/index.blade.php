@extends('layouts.admin')

@section('title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Mata Pelajaran</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola kurikulum, kode mapel, kelompok muatan, dan jurusan</p>
        </div>

        <button type="button" onclick="openSubjectModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Mapel Baru</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="panel p-4">
        <form method="GET" action="{{ route('admin.academic.subjects.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="f-label text-xs">Cari Mata Pelajaran</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bluedark/40">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau kode mapel..." class="f-input pl-9 text-xs w-full">
                </div>
            </div>

            <!-- Kategori -->
            <div>
                <label class="f-label text-xs">Kategori / Kelompok</label>
                <select name="category" class="f-select text-xs w-full">
                    <option value="">Semua Kategori</option>
                    <option value="MUATAN_NASIONAL" {{ $category === 'MUATAN_NASIONAL' ? 'selected' : '' }}>Muatan Nasional (A)</option>
                    <option value="MUATAN_KEWILAYAHAN" {{ $category === 'MUATAN_KEWILAYAHAN' ? 'selected' : '' }}>Muatan Kewilayahan (B)</option>
                    <option value="MUATAN_KEJURUAN" {{ $category === 'MUATAN_KEJURUAN' ? 'selected' : '' }}>Peminatan Kejuruan (C)</option>
                    <option value="MULOK" {{ $category === 'MULOK' ? 'selected' : '' }}>Muatan Lokal</option>
                </select>
            </div>

            <!-- Jurusan -->
            <div>
                <label class="f-label text-xs">Jurusan</label>
                <select name="department_id" class="f-select text-xs w-full">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ (string)$departmentId === (string)$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions (Filter & Reset) -->
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs flex-1 flex items-center justify-center gap-1.5 py-2 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                </button>
                @if($search !== '' || !empty($category) || !empty($departmentId) || $status !== null)
                    <a href="{{ route('admin.academic.subjects.index') }}" class="btn btn-outline btn-sm text-xs py-2 px-2.5 text-bluedark/60 hover:text-bluedark" title="Reset Filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Mapel matching template/admin/akademik-mapel.html -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Mata Pelajaran</h2>
            <span class="text-xs text-bluedark/50">Total: {{ $subjects->total() }} Mapel</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-24">Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kategori / Kelompok</th>
                        <th>Jurusan Khusus</th>
                        <th>Penugasan Mengajar</th>
                        <th class="w-24">Status</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $idx => $s)
                        <tr>
                            <td>{{ $subjects->firstItem() + $idx }}</td>
                            <td class="font-mono font-bold text-blueprim">{{ $s->code }}</td>
                            <td class="font-semibold text-bluedark">{{ $s->name }}</td>
                            <td>
                                <span class="badge badge-blue text-[11px]">{{ $s->category }}</span>
                            </td>
                            <td>{{ $s->department?->name ?? 'Semua Jurusan (Umum)' }}</td>
                            <td>
                                <span class="badge badge-gray text-[10px]">{{ $s->teaching_assignments_count }} Guru Terhubung</span>
                            </td>
                            <td>
                                <span class="badge {{ $s->is_active ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $s->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="editSubject({{ json_encode($s) }})" class="btn btn-outline btn-sm text-xs py-1 px-2.5">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.academic.subjects.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs py-1 px-2">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada mata pelajaran terdaftar yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subjects->hasPages())
            <div class="mt-4 pt-3 border-t border-bluelight">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah/Edit Mapel -->
<div id="subjectModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="subjectModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Mata Pelajaran</h3>
            <button type="button" onclick="closeSubjectModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="subjectForm" method="POST" action="{{ route('admin.academic.subjects.store') }}" class="space-y-4">
            @csrf
            <div id="subjectMethodField"></div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-1">
                    <label class="f-label">Kode</label>
                    <input type="text" name="code" id="subject_code" required placeholder="RPL01" class="f-input">
                </div>
                <div class="col-span-2">
                    <label class="f-label">Kategori</label>
                    <select name="category" id="subject_category" required class="f-select">
                        <option value="MUATAN_NASIONAL">Muatan Nasional (A)</option>
                        <option value="MUATAN_KEWILAYAHAN">Muatan Kewilayahan (B)</option>
                        <option value="MUATAN_KEJURUAN" selected>Peminatan Kejuruan (C)</option>
                        <option value="MULOK">Muatan Lokal</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="f-label">Nama Mata Pelajaran</label>
                <input type="text" name="name" id="subject_name" required placeholder="Pemrograman Web & Perangkat Bergerak" class="f-input">
            </div>

            <div>
                <label class="f-label">Khusus Jurusan Tertentu (Opsional)</label>
                <select name="department_id" id="subject_department_id" class="f-select">
                    <option value="">-- Semua Jurusan / Umum --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="subject_is_active" value="1" checked class="rounded text-blueprim">
                <label for="subject_is_active" class="text-xs font-medium text-bluedark">Mata Pelajaran Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeSubjectModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openSubjectModal() {
        document.getElementById('subjectModalTitle').innerText = 'Tambah Mata Pelajaran';
        document.getElementById('subjectForm').action = "{{ route('admin.academic.subjects.store') }}";
        document.getElementById('subjectMethodField').innerHTML = '';
        document.getElementById('subject_code').value = '';
        document.getElementById('subject_name').value = '';
        document.getElementById('subject_department_id').value = '';
        document.getElementById('subject_is_active').checked = true;
        document.getElementById('subjectModal').classList.remove('hidden');
    }

    function editSubject(s) {
        document.getElementById('subjectModalTitle').innerText = 'Edit Mata Pelajaran';
        document.getElementById('subjectForm').action = "/admin/academic/subjects/" + s.id;
        document.getElementById('subjectMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('subject_code').value = s.code;
        document.getElementById('subject_name').value = s.name;
        document.getElementById('subject_category').value = s.category;
        document.getElementById('subject_department_id').value = s.department_id || '';
        document.getElementById('subject_is_active').checked = !!s.is_active;
        document.getElementById('subjectModal').classList.remove('hidden');
    }

    function closeSubjectModal() {
        document.getElementById('subjectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
