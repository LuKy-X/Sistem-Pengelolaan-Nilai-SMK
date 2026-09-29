@extends('layouts.teacher')

@section('title', 'Profil & Pengaturan — Guru')

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Profil &amp; Pengaturan</h1>
    <p class="text-sm text-bluedark/60 mt-1">Kelola Profil Anda dan Pengaturan</p>
  </div>

  <!-- Panel 1: Info Ringkas Profil matching profil.html -->
  <div class="panel p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Profil &amp; Pengaturan</h2>
    <p class="text-xs text-bluedark/50 mb-4">Informasi identitas pendidik yang terdaftar pada sistem sekolah</p>

    <div class="flex flex-col sm:flex-row items-start gap-6">
      <dl class="info-list flex-1 w-full">
        <div class="info-list__row">
          <dt>Nama</dt>
          <dd>: {{ $teacher?->full_name ?? $user->name }}</dd>
        </div>
        <div class="info-list__row">
          <dt>NIP</dt>
          <dd>: {{ $teacher?->nip ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Jenis Kelamin</dt>
          <dd>: {{ in_array(strtoupper($teacher?->gender ?? 'L'), ['L', 'MALE']) ? 'Laki-laki' : 'Perempuan' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Status Kepegawaian</dt>
          <dd>: {{ $teacher?->status ?? 'Guru Tetap' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Email</dt>
          <dd>: {{ $user->email }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Nomor Telepon</dt>
          <dd>: {{ $teacher?->phone ?? '-' }}</dd>
        </div>
      </dl>

      <div class="w-28 h-28 rounded-2xl overflow-hidden bg-bluelight flex items-center justify-center flex-shrink-0 mx-auto sm:mx-0 shadow-xs">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-bluesoft"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
      </div>
    </div>
  </div>

  <!-- Panel 2: Form Edit Profil & Keamanan Password -->
  <div class="grid md:grid-cols-2 gap-5">
    <!-- Form Edit Data Diri -->
    <div class="form-block">
      <div class="form-block__header">
        <h3>Perbarui Data Diri</h3>
        <p>Ubah nama lengkap, email, atau kontak telepon</p>
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
          <input type="text" value="{{ $teacher?->nip ?? '-' }}" readonly class="f-input bg-bluelight/20 text-slate-500 cursor-not-allowed">
        </div>

        <div>
          <label class="f-label">Email Kedinasan / Aktif <span class="text-red-500">*</span></label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="f-input">
        </div>

        <div class="form-row cols-2">
          <div>
            <label class="f-label">Nomor Telepon / WA</label>
            <input type="text" name="phone" value="{{ old('phone', $teacher?->phone) }}" placeholder="08xxxxxxxxxx" class="f-input">
          </div>
          <div>
            <label class="f-label">Jenis Kelamin <span class="text-red-500">*</span></label>
            <select name="gender" class="f-select" required>
              <option value="L" {{ in_array(strtoupper($teacher?->gender ?? 'L'), ['L', 'MALE']) ? 'selected' : '' }}>Laki-laki</option>
              <option value="P" {{ in_array(strtoupper($teacher?->gender ?? ''), ['P', 'FEMALE']) ? 'selected' : '' }}>Perempuan</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>

    <!-- Form Ganti Password -->
    <div class="form-block">
      <div class="form-block__header">
        <h3>Keamanan Akun</h3>
        <p>Perbarui kata sandi akun portal Anda secara berkala</p>
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
          <input type="password" name="password" required placeholder="Minimal 6 karakter" class="f-input">
        </div>

        <div>
          <label class="f-label">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
          <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="f-input">
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
          <button type="submit" class="btn btn-primary">Ubah Kata Sandi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Panel 3: Pengaturan Keterlambatan Default matching profil.html -->
  <div class="panel p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Pengaturan Penilaian</h2>
    <p class="text-xs text-bluedark/50 mb-4">Default Pengurangan Batas Maksimal Nilai Tugas Karena Terlambat</p>
    
    <div class="grid grid-cols-[6rem_auto_10rem] gap-2 items-center max-w-sm">
      <input type="number" class="f-input" value="5">
      <span class="text-bluedark/50 text-sm text-center font-bold">/</span>
      <select class="f-select">
        <option>Minggu</option>
        <option>Hari</option>
      </select>
    </div>
    
    <div class="flex justify-end mt-5">
      <button type="button" onclick="alert('Pengaturan default nilai tugas tersimpan.')" class="btn btn-primary">
        Simpan Pengaturan
      </button>
    </div>
  </div>

</div>
@endsection
