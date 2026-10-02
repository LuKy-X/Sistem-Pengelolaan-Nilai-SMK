@extends('layouts.admin')

@section('title', 'Manajemen Jurusan & Kelas')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Jurusan &amp; Kelas</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola data kompetensi keahlian dan jurusan sekolah</p>
        </div>

        <button type="button" onclick="openDeptModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Jurusan Baru</span>
        </button>
    </div>

    <!-- Step Navigation (Hierarki Master Data Akademik) -->
    <nav class="flex items-center gap-1.5 p-1.5 rounded-2xl bg-white border border-bluelight shadow-2xs overflow-x-auto max-w-full" aria-label="Hierarki Data Akademik">
        <!-- Step 1: Daftar Jurusan -->
        <a href="{{ route('admin.academic.departments.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.departments.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.departments.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">1</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            <span>Daftar Jurusan</span>
        </a>

        <!-- Separator -->
        <svg class="w-3.5 h-3.5 text-bluedark/30 shrink-0 hidden sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>

        <!-- Step 2: Daftar Kelas -->
        <a href="{{ route('admin.academic.classes.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.classes.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.classes.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">2</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Daftar Kelas</span>
        </a>

        <!-- Separator -->
        <svg class="w-3.5 h-3.5 text-bluedark/30 shrink-0 hidden sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>

        <!-- Step 3: Daftar Siswa -->
        <a href="{{ route('admin.academic.students.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.students.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.students.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">3</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span>Daftar Siswa</span>
        </a>
    </nav>

    <!-- Grid Kartu Jurusan matching template -->
    <div class="grid sm:grid-cols-2 gap-4">
        @forelse($departments as $dept)
            <div class="kelas-card group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18 8.4 3.9a1 1 0 0 1 0 1.83l-8.4 3.9a2 2 0 0 1-1.66 0L2.77 7.91a1 1 0 0 1 0-1.83l8.4-3.9a2 2 0 0 1 1.66 0Z"/><path d="m22 12.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 12.5"/><path d="m22 17.5-8.4 3.9a2 2 0 0 1-1.66 0L2.77 17.5"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-bluedark text-base">{{ $dept->name }}</h3>
                                <p class="text-xs text-bluedark/50">Kode: {{ $dept->code }} {{ $dept->short_name ? '('.$dept->short_name.')' : '' }}</p>
                            </div>
                        </div>

                        <span class="badge {{ $dept->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $dept->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>

                    <p class="text-xs text-bluedark/70 line-clamp-2 mb-3">
                        {{ $dept->description ?? 'Kompetensi keahlian unggulan di SMK Negeri 2 Karanganyar.' }}
                    </p>

                    <div class="kelas-card__meta">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            {{ $dept->classes_count }} Rombongan Belajar
                        </span>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            {{ $dept->competencies_count }} Unit Kompetensi &middot; {{ $dept->facilities_count }} Fasilitas
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-bluelight mt-2">
                    <a href="{{ route('admin.academic.departments.show', $dept) }}" class="btn btn-outline btn-sm text-xs">
                        Kelola Jurusan &rarr;
                    </a>

                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="editDept({{ json_encode($dept) }})" class="btn btn-sm btn-outline text-xs px-2.5">
                            Edit
                        </button>
                        <form action="{{ route('admin.academic.departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?');">
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
                Belum ada data jurusan. Silakan tambahkan jurusan baru.
            </div>
        @endforelse
    </div>

    @if($departments->hasPages())
        <div class="mt-4 pt-3 border-t border-bluelight">
            {{ $departments->links() }}
        </div>
    @endif

</div>

<!-- Modal Tambah/Edit Jurusan -->
<div id="deptModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="deptModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Jurusan Baru</h3>
            <button type="button" onclick="closeDeptModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="deptForm" method="POST" action="{{ route('admin.academic.departments.store') }}" class="space-y-4">
            @csrf
            <div id="deptMethodField"></div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Kode Jurusan (cth: RPL, TPK)</label>
                    <input type="text" name="code" id="dept_code" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Nama Singkat (cth: RPL)</label>
                    <input type="text" name="short_name" id="dept_short_name" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Nama Lengkap Jurusan</label>
                <input type="text" name="name" id="dept_name" required placeholder="Rekayasa Perangkat Lunak" class="f-input">
            </div>

            <div>
                <label class="f-label">Deskripsi</label>
                <textarea name="description" id="dept_description" rows="2" class="f-textarea"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Visi Jurusan</label>
                    <textarea name="vision" id="dept_vision" rows="2" class="f-textarea"></textarea>
                </div>
                <div>
                    <label class="f-label">Misi Jurusan</label>
                    <textarea name="mission" id="dept_mission" rows="2" class="f-textarea"></textarea>
                </div>
            </div>

            <div>
                <label class="f-label">Prospek Karir / Lulusan</label>
                <textarea name="career_prospects" id="dept_career_prospects" rows="2" class="f-textarea" placeholder="Software Engineer, Web Developer, QA..."></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="dept_is_active" value="1" checked class="rounded text-blueprim focus:ring-blueprim">
                <label for="dept_is_active" class="text-xs font-medium text-bluedark">Jurusan Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeDeptModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openDeptModal() {
        document.getElementById('deptModalTitle').innerText = 'Tambah Jurusan Baru';
        document.getElementById('deptForm').action = "{{ route('admin.academic.departments.store') }}";
        document.getElementById('deptMethodField').innerHTML = '';
        document.getElementById('dept_code').value = '';
        document.getElementById('dept_short_name').value = '';
        document.getElementById('dept_name').value = '';
        document.getElementById('dept_description').value = '';
        document.getElementById('dept_vision').value = '';
        document.getElementById('dept_mission').value = '';
        document.getElementById('dept_career_prospects').value = '';
        document.getElementById('dept_is_active').checked = true;
        document.getElementById('deptModal').classList.remove('hidden');
    }

    function editDept(dept) {
        document.getElementById('deptModalTitle').innerText = 'Edit Jurusan';
        document.getElementById('deptForm').action = "/admin/academic/departments/" + dept.id;
        document.getElementById('deptMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('dept_code').value = dept.code;
        document.getElementById('dept_short_name').value = dept.short_name || '';
        document.getElementById('dept_name').value = dept.name;
        document.getElementById('dept_description').value = dept.description || '';
        document.getElementById('dept_vision').value = dept.vision || '';
        document.getElementById('dept_mission').value = dept.mission || '';
        document.getElementById('dept_career_prospects').value = dept.career_prospects || '';
        document.getElementById('dept_is_active').checked = !!dept.is_active;
        document.getElementById('deptModal').classList.remove('hidden');
    }

    function closeDeptModal() {
        document.getElementById('deptModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
