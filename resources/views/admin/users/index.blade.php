@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Pengguna</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola seluruh akun pengguna sistem, role akses, dan keamanan</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.teachers.index') }}" class="btn btn-outline btn-sm">
                Kelola Data Guru &rarr;
            </a>
            <button type="button" onclick="openUserModal()" class="btn btn-primary btn-sm flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Akun Baru</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Row (Sesuai Style Dashboard Admin) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-bluelight text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Akun Terdaftar</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['active'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Akun Status Aktif</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-rose-100 text-rose-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['inactive'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Akun Nonaktif</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-amber-100 text-amber-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['admin'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Hak Akses Admin</div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Toolbar -->
    <div class="panel p-4 space-y-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-bluedark/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari Nama, Username, atau Email..." class="f-input text-xs py-2 pl-9 pr-3 w-full">
            </div>

            <div class="w-full sm:w-auto min-w-[160px]">
                <select name="role_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Semua Role --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $roleId == $r->id ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[140px]">
                <select name="status" class="f-select text-xs py-2 w-full">
                    <option value="">-- Status Akun --</option>
                    <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-2 px-4 flex items-center gap-1.5 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Terapkan</span>
                </button>

                @if($search || $roleId || ($status !== null && $status !== ''))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm text-xs py-2 px-3 text-rose-600 border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between text-xs text-bluedark/50 pt-2 border-t border-slate-100">
            <div>
                Menampilkan <strong>{{ $users->count() }}</strong> dari <strong>{{ $users->total() }}</strong> akun
            </div>
            @if($search || $roleId || ($status !== null && $status !== ''))
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blueprim">
                    <span class="w-2 h-2 rounded-full bg-blueprim animate-pulse"></span>
                    Filter aktif
                </span>
            @endif
        </div>
    </div>

    <!-- Table Pengguna matching template/admin/pengguna.html -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Pengguna</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role Akses</th>
                        <th class="w-24">Status</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $idx => $u)
                        @php
                            $userRole = $u->roles->first();
                        @endphp
                        <tr>
                            <td>{{ $users->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-blueprim/10 text-blueprim font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-xs">{{ $u->username ?? '-' }}</td>
                            <td class="text-bluedark/70 text-xs">{{ $u->email }}</td>
                            <td>
                                @if($userRole)
                                    @php
                                        $badgeColor = match($userRole->code) {
                                            'admin' => 'badge-blue',
                                            'counselor' => 'badge-green',
                                            'teacher' => 'badge-yellow',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }} text-[11px]">{{ ucfirst($userRole->name) }}</span>
                                @else
                                    <span class="badge badge-gray text-[10px]">No Role</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="badge {{ $u->is_active ? 'badge-green' : 'badge-gray' }} hover:scale-105 transition-transform text-[10px] cursor-pointer" title="Klik untuk ubah status">
                                        {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="editUser({{ json_encode($u) }}, {{ $userRole?->id ?? 'null' }})" class="btn btn-outline btn-sm text-xs py-1 px-2">
                                        Edit
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun ini secara permanen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs py-1 px-2">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-bluedark/40 flex items-center justify-center">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-bluedark">Data Pengguna Tidak Ditemukan</p>
                                    <p class="text-xs text-bluedark/50">Tidak ada pengguna yang cocok dengan kriteria pencarian atau filter yang dipilih.</p>
                                    @if($search || $roleId || ($status !== null && $status !== ''))
                                        <div class="pt-2">
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm text-xs py-1 px-3">
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
            {{ $users->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah/Edit Pengguna -->
<div id="userModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 id="userModalTitle" class="font-heading font-bold text-lg text-bluedark">Tambah Akun Pengguna</h3>
            <button type="button" onclick="closeUserModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form id="userForm" method="POST" action="{{ old('_action', route('admin.users.store')) }}" class="space-y-4">
            @csrf
            <div id="userMethodField">
                @if(old('_method') === 'PUT')
                    <input type="hidden" name="_method" value="PUT">
                @endif
            </div>

            <div>
                <label class="f-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="user_name" value="{{ old('name') }}" required placeholder="Nama Pengguna" class="f-input @error('name') border-rose-500 @enderror">
                @error('name')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="user_username" value="{{ old('username') }}" data-check-unique="username" required placeholder="username" class="f-input @error('username') border-rose-500 @enderror">
                    @error('username')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">Role Akses <span class="text-rose-500">*</span></label>
                    <select name="role_id" id="user_role_id" required class="f-select @error('role_id') border-rose-500 @enderror">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label class="f-label">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" id="user_email" value="{{ old('email') }}" data-check-unique="email" required placeholder="user@smkn2kra.sch.id" class="f-input @error('email') border-rose-500 @enderror">
                @error('email')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="f-label" id="userPasswordLabel">Password</label>
                <input type="password" name="password" id="user_password" placeholder="Minimal 6 karakter" class="f-input @error('password') border-rose-500 @enderror">
                <span id="userPasswordHelp" class="text-[10px] text-bluedark/50 hidden">Kosongkan jika tidak ingin mengubah password.</span>
                @error('password')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="user_is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="rounded text-blueprim">
                <label for="user_is_active" class="text-xs font-medium text-bluedark">Akun Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeUserModal()" class="btn btn-outline btn-sm">Batal</button>
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

    function openUserModal() {
        document.getElementById('userModalTitle').innerText = 'Tambah Akun Pengguna';
        document.getElementById('userForm').action = "{{ route('admin.users.store') }}";
        document.getElementById('userMethodField').innerHTML = '';
        document.getElementById('user_name').value = '';
        document.getElementById('user_username').value = '';
        document.getElementById('user_username').removeAttribute('data-ignore-id');
        document.getElementById('user_email').value = '';
        document.getElementById('user_email').removeAttribute('data-ignore-id');
        document.getElementById('user_password').value = '';
        document.getElementById('user_password').required = true;
        document.getElementById('userPasswordHelp').classList.add('hidden');
        document.getElementById('user_is_active').checked = true;
        resetUniqueFeedbacks(document.getElementById('userModal'));
        document.getElementById('userModal').classList.remove('hidden');
    }

    function editUser(u, roleId) {
        document.getElementById('userModalTitle').innerText = 'Edit Akun Pengguna';
        document.getElementById('userForm').action = "/admin/users/" + u.id;
        document.getElementById('userMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('user_name').value = u.name;
        document.getElementById('user_username').value = u.username || '';
        document.getElementById('user_username').setAttribute('data-ignore-id', u.id);
        document.getElementById('user_email').value = u.email;
        document.getElementById('user_email').setAttribute('data-ignore-id', u.id);
        if (roleId) {
            document.getElementById('user_role_id').value = roleId;
        }
        document.getElementById('user_password').value = '';
        document.getElementById('user_password').required = false;
        document.getElementById('userPasswordHelp').classList.remove('hidden');
        document.getElementById('user_is_active').checked = !!u.is_active;
        resetUniqueFeedbacks(document.getElementById('userModal'));
        document.getElementById('userModal').classList.remove('hidden');
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.add('hidden');
    }

    @if($errors->any() && (old('username') || old('email') || old('name')))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('userModal').classList.remove('hidden');
    });
    @endif
</script>
@endpush
@endsection
