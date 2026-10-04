@extends('layouts.admin')

@section('title', 'Profil & Pengaturan Akun')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Profil &amp; Pengaturan Akun</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola data identitas akun administrator dan keamanan kata sandi</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('public.home') }}" target="_blank" class="btn btn-outline btn-sm flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Web Publik</span>
            </a>
        </div>
    </div>

    <!-- Panel 1: Info Ringkasan Akun -->
    <div class="panel p-5 sm:p-6 bg-white border border-bluelight rounded-2xl shadow-xs">
        <h2 class="font-heading font-semibold text-bluedark text-base mb-1">Informasi Akun</h2>
        <p class="text-xs text-bluedark/50 mb-5">Rincian status akun administrator yang saat ini sedang aktif digunakan</p>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-blueprim text-white flex items-center justify-center font-heading font-bold text-2xl shrink-0 shadow-md">
                {{ strtoupper(substr($user->name ?? 'AD', 0, 2)) }}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1 w-full">
                <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight/80">
                    <span class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider block">Nama Lengkap</span>
                    <span class="font-semibold text-sm text-bluedark mt-0.5 block truncate">{{ $user->name }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight/80">
                    <span class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider block">Username</span>
                    <span class="font-semibold text-sm text-bluedark mt-0.5 block truncate">{{ $user->username }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight/80">
                    <span class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider block">Hak Akses</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-blueprim mt-1">
                        <span class="w-2 h-2 rounded-full bg-blueprim"></span>
                        Administrator
                    </span>
                </div>
                <div class="p-3.5 rounded-xl bg-bluelight/40 border border-bluelight/80">
                    <span class="text-[11px] font-bold text-bluedark/60 uppercase tracking-wider block">Status Akun</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-emerald-700 mt-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel 2: Form Edit Data Akun & Form Ganti Password -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Form Edit Data Diri -->
        <div class="panel p-5 sm:p-6 bg-white border border-bluelight rounded-2xl shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-bluelight text-blueprim flex items-center justify-center shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-semibold text-bluedark text-[15px]">Perbarui Data Akun</h3>
                        <p class="text-xs text-bluedark/50">Ubah nama lengkap, username, atau email login Anda</p>
                    </div>
                </div>

                <form id="profileUpdateForm" action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="f-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="f-input @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="f-label">Username Login <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required class="f-input @error('username') border-rose-500 @enderror">
                        @error('username')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="f-label">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="f-input @error('email') border-rose-500 @enderror">
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Ganti Password -->
        <div class="panel p-5 sm:p-6 bg-white border border-bluelight rounded-2xl shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-semibold text-bluedark text-[15px]">Keamanan &amp; Kata Sandi</h3>
                        <p class="text-xs text-bluedark/50">Ubah kata sandi dengan memasukkan sandi lama dan sandi baru</p>
                    </div>
                </div>

                <form id="passwordUpdateForm" action="{{ route('admin.profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="f-label">Kata Sandi Saat Ini (Sandi Lama) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi lama Anda" class="f-input pr-10 @error('current_password') border-rose-500 @enderror">
                            <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" title="Lihat/Sembunyikan sandi">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="f-label">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter" class="f-input pr-10 @error('password') border-rose-500 @enderror">
                            <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" title="Lihat/Sembunyikan sandi">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="f-label">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="f-input pr-10">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" title="Lihat/Sembunyikan sandi">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Ubah Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.classList.add('text-blueprim');
        btn.classList.remove('text-slate-400');
    } else {
        input.type = 'password';
        btn.classList.remove('text-blueprim');
        btn.classList.add('text-slate-400');
    }
}
</script>
@endpush
@endsection
