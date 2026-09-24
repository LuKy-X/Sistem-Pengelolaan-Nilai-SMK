@extends('layouts.teacher')

@section('title', 'Profil & Pengaturan — Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="font-heading text-2xl md:text-3xl font-bold text-bluedark">Profil &amp; Pengaturan</h1>
        <p class="text-sm text-bluedark/60 mt-0.5">Kelola identitas pendidik dan keamanan akun portal Anda</p>
    </div>

    <!-- Teacher Identity Banner Card -->
    <div class="panel p-6 bg-gradient-to-r from-white via-white to-bluelight/30 flex flex-col sm:flex-row items-center gap-5">
        <div class="avatar-circle w-20 h-20 text-2xl shadow-lg ring-4 ring-white">
            {{ strtoupper(substr($teacher?->full_name ?? $user->name, 0, 2)) }}
        </div>
        <div class="text-center sm:text-left flex-1">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                <h2 class="font-heading font-bold text-xl md:text-2xl text-bluedark">
                    {{ $teacher?->full_name ?? $user->name }}
                </h2>
                <span class="badge badge-blue">Guru Pengajar</span>
                <span class="badge badge-green">{{ $teacher?->status ?? 'TETAP' }}</span>
            </div>
            <div class="text-xs text-slate-500 space-x-2">
                <span>NIP: <strong class="text-bluedark font-mono">{{ $teacher?->nip ?? '-' }}</strong></span>
                <span>•</span>
                <span>Username: <strong class="text-bluedark font-mono">{{ $user->username }}</strong></span>
                <span>•</span>
                <span>Email: <strong class="text-bluedark">{{ $user->email }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Forms Grid: Profile & Password -->
    <div class="grid md:grid-cols-2 gap-6">

        <!-- Form 1: Data Profil Pendidik -->
        <div class="form-block">
            <div class="form-block__header">
                <h3>Informasi Pribadi</h3>
                <p>Perbarui informasi kontak dan data diri Anda</p>
            </div>

            <form action="{{ route('teacher.profile.update') }}" method="POST" class="form-block__body space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="f-label">Nama Lengkap &amp; Gelar <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $teacher?->full_name ?? $user->name) }}" required class="f-input">
                </div>

                <div>
                    <label class="f-label">Nomor Induk Pegawai (NIP)</label>
                    <input type="text" readonly value="{{ $teacher?->nip ?? '-' }}" class="f-input bg-slate-50 text-slate-500 cursor-not-allowed">
                    <p class="text-[11px] text-slate-400 mt-1">Perubahan NIP memerlukan otorisasi administrator sekolah.</p>
                </div>

                <div>
                    <label class="f-label">Email Kedinasan / Aktif <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="f-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="f-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $teacher?->phone) }}" placeholder="08xxxxxxxxxx" class="f-input">
                    </div>
                    <div>
                        <label class="f-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="gender" class="f-select" required>
                            <option value="L" {{ ($teacher?->gender ?? 'L') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ ($teacher?->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 text-right border-t border-slate-100">
                    <button type="submit" class="btn btn-primary btn-sm shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Ganti Kata Sandi -->
        <div class="form-block">
            <div class="form-block__header">
                <h3>Keamanan Akun</h3>
                <p>Ubah kata sandi untuk melindungi keamanan akun Anda</p>
            </div>

            <form action="{{ route('teacher.profile.password') }}" method="POST" class="form-block__body space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="f-label">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password" required placeholder="Masukkan kata sandi lama" class="f-input">
                </div>

                <div>
                    <label class="f-label">Kata Sandi Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="f-input">
                </div>

                <div>
                    <label class="f-label">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi baru" class="f-input">
                </div>

                <div class="pt-3 text-right border-t border-slate-100">
                    <button type="submit" class="btn btn-dark btn-sm shadow-md">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
