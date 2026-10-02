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

    <!-- Filter & Pencarian -->
    <div class="panel p-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari Nama, Username, atau Email..." class="f-input text-xs py-1.5 w-64">

            <select name="role_id" class="f-select text-xs py-1.5 w-44">
                <option value="">-- Semua Role --</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ $roleId == $r->id ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm text-xs py-1.5">
                Filter
            </button>

            @if($search || $roleId)
                <a href="{{ route('admin.users.index') }}" class="text-xs text-rose-600 hover:underline">
                    Reset
                </a>
            @endif
        </form>

        <span class="text-xs text-bluedark/50">Total Akun: <strong>{{ $users->total() }}</strong></span>
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
                                    <button type="submit" class="badge {{ $u->is_active ? 'badge-green' : 'badge-gray' }} hover:scale-105 transition-transform text-[10px] cursor-pointer">
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
                            <td colspan="7" class="text-center py-8 text-xs text-bluedark/40">
                                Tidak ada data pengguna yang ditemukan.
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
