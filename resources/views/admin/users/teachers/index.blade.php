@extends('layouts.admin')

@section('title', 'Manajemen Guru & Tenaga Pendidik')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Data Guru &amp; Tenaga Pendidik</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola biodata guru, NIP, akun pengajar, dan penugasan kelas</p>
        </div>

        <button type="button" onclick="openTeacherModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Guru Baru</span>
        </button>
    </div>

    <!-- Filter & Search -->
    <div class="panel p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.teachers.index') }}" class="flex items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIP atau Nama Guru..." class="f-input text-xs py-1.5 w-64">
            <button type="submit" class="btn btn-primary btn-sm text-xs py-1.5">Cari</button>
            @if($search)
                <a href="{{ route('admin.users.teachers.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
            @endif
        </form>

        <span class="text-xs text-bluedark/50">Total Guru: <strong>{{ $teachers->total() }}</strong></span>
    </div>

    <!-- Table Guru -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Email Login</th>
                        <th>Wali Kelas</th>
                        <th>Jam / Beban Ajar</th>
                        <th class="w-24">Status</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $idx => $t)
                        <tr>
                            <td>{{ $teachers->firstItem() + $idx }}</td>
                            <td class="font-mono text-xs font-semibold text-bluedark">{{ $t->nip }}</td>
                            <td class="font-semibold text-bluedark">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blueprim/10 text-blueprim font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($t->full_name, 0, 1)) }}
                                    </div>
                                    <span>{{ $t->full_name }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-bluedark/70">{{ $t->user?->email ?? '-' }}</td>
                            <td>
                                @if($t->homeroomClasses->isNotEmpty())
                                    <span class="badge badge-yellow text-[11px]">{{ $t->homeroomClasses->pluck('name')->implode(', ') }}</span>
                                @else
                                    <span class="text-xs text-bluedark/40">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-blue text-[11px]">{{ $t->teaching_assignments_count }} Penugasan</span>
                            </td>
                            <td>
                                <span class="badge {{ $t->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="editTeacher({{ json_encode($t) }})" class="btn btn-outline btn-sm text-xs py-1 px-2.5">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.users.teachers.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus guru ini?');">
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
                                Belum ada data guru yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $teachers->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah/Edit Guru -->
<div id="teacherModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="teacherModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Guru Baru</h3>
            <button type="button" onclick="closeTeacherModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="teacherForm" method="POST" action="{{ route('admin.users.teachers.store') }}" class="space-y-4">
            @csrf
            <div id="teacherMethodField"></div>

            <div>
                <label class="f-label">NIP (Nomor Induk Pegawai)</label>
                <input type="text" name="nip" id="teacher_nip" required placeholder="198501012010011001" class="f-input">
            </div>

            <div>
                <label class="f-label">Nama Lengkap &amp; Gelar</label>
                <input type="text" name="full_name" id="teacher_full_name" required placeholder="Drs. H. Mulyono, M.Kom" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Jenis Kelamin</label>
                    <select name="gender" id="teacher_gender" class="f-select">
                        <option value="MALE">Laki-laki</option>
                        <option value="FEMALE">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">No. Telepon / WA</label>
                    <input type="text" name="phone" id="teacher_phone" placeholder="08xxxxxxxx" class="f-input">
                </div>
            </div>

            <div id="teacherAccountFields" class="space-y-3 pt-2 border-t border-bluelight">
                <div class="text-xs font-bold text-bluedark">Akun Login Guru</div>
                <div>
                    <label class="f-label text-xs">Email Resmi Sekolah</label>
                    <input type="email" name="email" id="teacher_email" placeholder="guru@smkn2kra.sch.id" class="f-input text-xs">
                </div>
                <div>
                    <label class="f-label text-xs">Password Default</label>
                    <input type="text" name="password" value="guru12345" class="f-input text-xs">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeTeacherModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTeacherModal() {
        document.getElementById('teacherModalTitle').innerText = 'Tambah Guru Baru';
        document.getElementById('teacherForm').action = "{{ route('admin.users.teachers.store') }}";
        document.getElementById('teacherMethodField').innerHTML = '';
        document.getElementById('teacher_nip').value = '';
        document.getElementById('teacher_full_name').value = '';
        document.getElementById('teacher_phone').value = '';
        document.getElementById('teacherAccountFields').classList.remove('hidden');
        document.getElementById('teacher_email').required = true;
        document.getElementById('teacherModal').classList.remove('hidden');
    }

    function editTeacher(t) {
        document.getElementById('teacherModalTitle').innerText = 'Edit Data Guru';
        document.getElementById('teacherForm').action = "/admin/users/teachers/" + t.id;
        document.getElementById('teacherMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('teacher_nip').value = t.nip;
        document.getElementById('teacher_full_name').value = t.full_name;
        document.getElementById('teacher_gender').value = t.gender || 'MALE';
        document.getElementById('teacher_phone').value = t.phone || '';
        document.getElementById('teacherAccountFields').classList.add('hidden');
        document.getElementById('teacher_email').required = false;
        document.getElementById('teacherModal').classList.remove('hidden');
    }

    function closeTeacherModal() {
        document.getElementById('teacherModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
