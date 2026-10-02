@extends('layouts.student')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Profil Saya</h1>
    <p class="text-sm text-bluedark/60 mt-1">Data diri, kelas, dan pengaturan akun Anda.</p>
  </div>

  <div class="panel p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Data Diri</h2>
    <p class="text-xs text-bluedark/50 mb-4">Data resmi yang terdaftar di sekolah. Hubungi admin jika ada kesalahan data.</p>

    <div class="flex flex-col sm:flex-row items-start gap-6">
      <dl class="info-list flex-1 w-full">
        <div class="info-list__row">
          <dt>Nama Lengkap</dt>
          <dd>: {{ $student->full_name }}</dd>
        </div>
        <div class="info-list__row">
          <dt>NIS / NISN</dt>
          <dd>: {{ $student->nis }} / {{ $student->nisn ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Jenis Kelamin</dt>
          <dd>: {{ in_array(strtoupper($student->gender ?? 'L'), ['L', 'MALE']) ? 'Laki-laki' : 'Perempuan' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Tempat, Tgl Lahir</dt>
          <dd>: {{ $student->birth_place ?? '-' }}, {{ $student->birth_date?->format('d M Y') ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Email</dt>
          <dd>: {{ $user->email ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Nomor Telepon</dt>
          <dd>: {{ $student->phone ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Alamat</dt>
          <dd>: {{ $student->address ?? '-' }}</dd>
        </div>
      </dl>

      <div class="w-28 h-28 rounded-2xl overflow-hidden bg-bluelight flex items-center justify-center flex-shrink-0 mx-auto sm:mx-0 shadow-xs">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-bluesoft"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
      </div>
    </div>
  </div>

  <div class="panel p-5">
    <h2 class="font-heading font-semibold text-bluedark text-[15px] mb-4">Kelas &amp; Jurusan</h2>
    @if($enrollment?->schoolClass)
      <dl class="info-list">
        <div class="info-list__row">
          <dt>Kelas</dt>
          <dd>: {{ $enrollment->schoolClass->name }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Tingkat</dt>
          <dd>: {{ $enrollment->schoolClass->gradeLevel?->name ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Jurusan</dt>
          <dd>: {{ $enrollment->schoolClass->department?->name ?? '-' }}</dd>
        </div>
        <div class="info-list__row">
          <dt>Wali Kelas</dt>
          <dd>: {{ $enrollment->schoolClass->homeroomTeacher?->full_name ?? '-' }}</dd>
        </div>
      </dl>
    @else
      <p class="text-xs text-bluedark/50">Anda belum terdaftar pada kelas aktif. Hubungi admin sekolah.</p>
    @endif
  </div>

  <div class="grid md:grid-cols-2 gap-4 lg:gap-5">
    <div class="panel p-4 lg:p-5">
      <h3 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Perbarui Kontak</h3>
      <p class="text-[11px] text-bluedark/50 mb-4">Hanya email, nomor telepon, dan alamat yang dapat Anda ubah sendiri.</p>

      <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-3">
        @csrf
        @method('PUT')
        <div>
          <label class="f-label" for="email">Email</label>
          <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="f-input">
          @error('email')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="f-label" for="phone">Nomor Telepon</label>
          <input type="text" id="phone" name="phone" value="{{ old('phone', $student->phone) }}" placeholder="08xxxxxxxxxx" class="f-input">
          @error('phone')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="f-label" for="address">Alamat</label>
          <textarea id="address" name="address" rows="3" class="f-textarea">{{ old('address', $student->address) }}</textarea>
          @error('address')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </form>
    </div>

    <div class="panel p-4 lg:p-5">
      <h3 class="font-heading font-semibold text-bluedark text-[15px] mb-1">Keamanan Akun</h3>
      <p class="text-[11px] text-bluedark/50 mb-4">Ganti kata sandi secara berkala untuk menjaga keamanan akun Anda.</p>

      <form method="POST" action="{{ route('student.profile.password') }}" class="space-y-3">
        @csrf
        @method('PUT')
        <div>
          <label class="f-label" for="current_password">Kata Sandi Saat Ini</label>
          <input type="password" id="current_password" name="current_password" required class="f-input" placeholder="Masukkan kata sandi lama">
          @error('current_password')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="f-label" for="password">Kata Sandi Baru</label>
          <input type="password" id="password" name="password" required class="f-input" placeholder="Minimal 6 karakter">
          @error('password')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="f-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required class="f-input" placeholder="Ulangi kata sandi baru">
        </div>
        <button type="submit" class="btn btn-primary">Ubah Kata Sandi</button>
      </form>
    </div>
  </div>

</div>
@endsection
