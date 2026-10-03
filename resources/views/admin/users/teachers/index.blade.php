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

    <!-- KPI Metric Cards Row (Sesuai Style Dashboard Admin) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-bluelight text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Guru &amp; Pendidik</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['active'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Guru Status Aktif</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-amber-100 text-amber-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['homeroom'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Penugasan Wali Kelas</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="panel p-4 space-y-3">
        <form method="GET" action="{{ route('admin.users.teachers.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-bluedark/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIP, Nama Lengkap, atau Email..." class="f-input text-xs py-2 pl-9 pr-3 w-full">
            </div>

            <div class="w-full sm:w-auto min-w-[140px]">
                <select name="status" class="f-select text-xs py-2 w-full">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="LEAVE" {{ $status === 'LEAVE' ? 'selected' : '' }}>Cuti</option>
                    <option value="INACTIVE" {{ $status === 'INACTIVE' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[150px]">
                <select name="is_homeroom" class="f-select text-xs py-2 w-full">
                    <option value="">Semua Penugasan</option>
                    <option value="1" {{ $isHomeroom === '1' ? 'selected' : '' }}>Wali Kelas</option>
                    <option value="0" {{ $isHomeroom === '0' ? 'selected' : '' }}>Bukan Wali Kelas</option>
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[130px]">
                <select name="gender" class="f-select text-xs py-2 w-full">
                    <option value="">Semua Gender</option>
                    <option value="MALE" {{ $gender === 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="FEMALE" {{ $gender === 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-2 px-4 flex items-center gap-1.5 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Terapkan</span>
                </button>
                @if($search || $status || $gender || ($isHomeroom !== null && $isHomeroom !== ''))
                    <a href="{{ route('admin.users.teachers.index') }}" class="btn btn-outline btn-sm text-xs py-2 px-3 text-rose-600 border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between text-xs text-bluedark/50 pt-2 border-t border-slate-100">
            <div>
                Menampilkan <strong>{{ $teachers->count() }}</strong> dari <strong>{{ $teachers->total() }}</strong> guru
            </div>
            @if($search || $status || $gender || ($isHomeroom !== null && $isHomeroom !== ''))
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blueprim">
                    <span class="w-2 h-2 rounded-full bg-blueprim animate-pulse"></span>
                    Filter aktif
                </span>
            @endif
        </div>
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
                                    <a href="{{ route('admin.users.teachers.show', $t) }}" class="hover:underline hover:text-blueprim">
                                        {{ $t->full_name }}
                                    </a>
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
                                    <a href="{{ route('admin.users.teachers.show', $t) }}" class="btn btn-outline btn-sm text-xs py-1 px-2.5">
                                        Detail
                                    </a>
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
                            <td colspan="8" class="text-center py-12">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-bluedark/40 flex items-center justify-center">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-bluedark">Data Guru Tidak Ditemukan</p>
                                    <p class="text-xs text-bluedark/50">Tidak ada data guru yang cocok dengan kriteria pencarian atau filter yang dipilih.</p>
                                    @if($search || $status || $gender || ($isHomeroom !== null && $isHomeroom !== ''))
                                        <div class="pt-2">
                                            <a href="{{ route('admin.users.teachers.index') }}" class="btn btn-outline btn-sm text-xs py-1 px-3">
                                                Reset Filter
                                            </a>
                                        </div>
                                    @endif
                                </div>
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

        <form id="teacherForm" method="POST" action="{{ old('_action', route('admin.users.teachers.store')) }}" class="space-y-4">
            @csrf
            <div id="teacherMethodField">
                @if(old('_method') === 'PUT')
                    <input type="hidden" name="_method" value="PUT">
                @endif
            </div>

            <div>
                <label class="f-label">NIP (Nomor Induk Pegawai) <span class="text-rose-500">*</span></label>
                <input type="text" name="nip" id="teacher_nip" value="{{ old('nip') }}" data-check-unique="nip" required placeholder="198501012010011001" class="f-input @error('nip') border-rose-500 @enderror">
                @error('nip')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="f-label">Nama Lengkap &amp; Gelar <span class="text-rose-500">*</span></label>
                <input type="text" name="full_name" id="teacher_full_name" value="{{ old('full_name') }}" required placeholder="Drs. H. Mulyono, M.Kom" class="f-input @error('full_name') border-rose-500 @enderror">
                @error('full_name')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Jenis Kelamin</label>
                    <select name="gender" id="teacher_gender" class="f-select @error('gender') border-rose-500 @enderror">
                        <option value="MALE" {{ old('gender') == 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="FEMALE" {{ old('gender') == 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">No. Telepon / WA</label>
                    <input type="text" name="phone" id="teacher_phone" value="{{ old('phone') }}" placeholder="08xxxxxxxx" class="f-input @error('phone') border-rose-500 @enderror">
                    @error('phone')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div id="teacherAccountFields" class="space-y-3 pt-2 border-t border-bluelight">
                <div class="text-xs font-bold text-bluedark">Akun Login Guru</div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="f-label text-xs mb-0">Email Resmi Sekolah <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="generateTeacherEmail()" class="text-[11px] font-semibold text-blueprim hover:text-blue-800 hover:underline inline-flex items-center gap-1 transition-colors" title="Generate email otomatis dari nama lengkap">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/></svg>
                            <span>Sesuaikan dari Nama</span>
                        </button>
                    </div>
                    <input type="email" name="email" id="teacher_email" value="{{ old('email') }}" data-check-unique="email" placeholder="guru@smkn2kra.sch.id" class="f-input text-xs @error('email') border-rose-500 @enderror">
                    @error('email')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label text-xs">Password Default</label>
                    <input type="text" name="password" value="guru12345" class="f-input text-xs @error('password') border-rose-500 @enderror">
                    @error('password')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
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
    function resetUniqueFeedbacks(modalEl) {
        modalEl.querySelectorAll('.check-unique-feedback').forEach(el => el.innerHTML = '');
        modalEl.querySelectorAll('input').forEach(el => el.classList.remove('border-emerald-500', 'border-rose-500'));
    }

    function generateTeacherEmail() {
        const nameInput = document.getElementById('teacher_full_name');
        const emailInput = document.getElementById('teacher_email');
        const rawName = nameInput ? nameInput.value.trim() : '';

        if (!rawName) {
            alert('Silakan isi Nama Lengkap & Gelar terlebih dahulu.');
            if (nameInput) nameInput.focus();
            return;
        }

        // Bersihkan gelar akademik umum agar email rapi dan mudah diingat
        let cleanName = rawName.toLowerCase();
        cleanName = cleanName.replace(/\b(drs|dra|dr|prof|h|hj|s\.pd|m\.pd|s\.kom|m\.kom|s\.t|m\.t|s\.e|m\.m|s\.si|m\.si|s\.sos|m\.sos|s\.ag|m\.ag|ph\.d|gr)\b\.?/gi, '');
        cleanName = cleanName.replace(/[^a-z0-9]/g, '');

        if (!cleanName) {
            cleanName = rawName.toLowerCase().replace(/[^a-z0-9]/g, '');
        }

        emailInput.value = `${cleanName}@smkn2kra.sch.id`;
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function openTeacherModal() {
        document.getElementById('teacherModalTitle').innerText = 'Tambah Guru Baru';
        document.getElementById('teacherForm').action = "{{ route('admin.users.teachers.store') }}";
        document.getElementById('teacherMethodField').innerHTML = '';
        document.getElementById('teacher_nip').value = '';
        document.getElementById('teacher_nip').removeAttribute('data-ignore-id');
        document.getElementById('teacher_full_name').value = '';
        document.getElementById('teacher_phone').value = '';
        document.getElementById('teacher_email').value = '';
        document.getElementById('teacher_email').removeAttribute('data-ignore-id');
        document.getElementById('teacherAccountFields').classList.remove('hidden');
        document.getElementById('teacher_email').required = true;
        resetUniqueFeedbacks(document.getElementById('teacherModal'));
        document.getElementById('teacherModal').classList.remove('hidden');
    }

    function editTeacher(t) {
        document.getElementById('teacherModalTitle').innerText = 'Edit Data Guru';
        document.getElementById('teacherForm').action = "/admin/users/teachers/" + t.id;
        document.getElementById('teacherMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('teacher_nip').value = t.nip;
        document.getElementById('teacher_nip').setAttribute('data-ignore-id', t.id);
        document.getElementById('teacher_full_name').value = t.full_name;
        document.getElementById('teacher_gender').value = t.gender || 'MALE';
        document.getElementById('teacher_phone').value = t.phone || '';
        document.getElementById('teacherAccountFields').classList.add('hidden');
        document.getElementById('teacher_email').required = false;
        resetUniqueFeedbacks(document.getElementById('teacherModal'));
        document.getElementById('teacherModal').classList.remove('hidden');
    }

    function closeTeacherModal() {
        document.getElementById('teacherModal').classList.add('hidden');
    }

    @if($errors->any() && (old('nip') || old('full_name') || old('email')))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('teacherModal').classList.remove('hidden');
    });
    @endif
</script>
@endpush
@endsection
