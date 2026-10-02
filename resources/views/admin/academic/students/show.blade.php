@extends('layouts.admin')

@section('title', 'Detail Siswa — ' . $student->full_name)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.academic.students.index') }}" class="text-xs text-blueprim hover:underline">&larr; Kembali ke Daftar Siswa</a>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $student->full_name }}</h1>
            <p class="text-sm text-bluedark/60">
                NIS: <strong class="font-mono text-bluedark">{{ $student->nis }}</strong> &middot;
                NISN: <strong class="font-mono text-bluedark">{{ $student->nisn }}</strong> &middot;
                Status: <span class="badge {{ $student->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }}">{{ $student->status }}</span>
            </p>
        </div>
    </div>

    <!-- Detail Profil & Form Update -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Kolom Form Profil (2 Kolom) -->
        <div class="panel p-5 lg:col-span-2 space-y-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px] border-b border-bluelight pb-3">Profil Data Diri Siswa</h2>

            <form action="{{ route('admin.academic.students.update', $student) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">NIS</label>
                        <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" required class="f-input">
                    </div>
                    <div>
                        <label class="f-label">NISN</label>
                        <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}" required class="f-input">
                    </div>
                </div>

                <div>
                    <label class="f-label">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}" required class="f-input">
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Jenis Kelamin</label>
                        <select name="gender" required class="f-select">
                            <option value="MALE" {{ $student->gender === 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="FEMALE" {{ $student->gender === 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="f-label">Nomor Telepon / WA</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="f-input">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" class="f-input">
                    </div>
                    <div>
                        <label class="f-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}" class="f-input">
                    </div>
                </div>

                <div>
                    <label class="f-label">Alamat Lengkap</label>
                    <textarea name="address" rows="2" class="f-textarea">{{ old('address', $student->address) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-bluelight">
                    <div>
                        <span class="text-xs text-bluedark/60">Akun Login: <strong>{{ $student->user?->email ?? 'Tidak ada akun' }}</strong></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Rombel & Rekam Siswa (1 Kolom) -->
        <div class="space-y-5">
            <!-- Penempatan Rombel Aktif -->
            <div class="panel p-5 space-y-4">
                <h3 class="font-heading font-semibold text-bluedark text-sm">Penempatan Rombel</h3>

                <form action="{{ route('admin.academic.students.enroll', $student) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="f-label text-xs">Pindahkan / Tempatkan ke Kelas</label>
                        <select name="class_id" required class="f-select text-xs">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ $student->classEnrollments->firstWhere('status', 'ACTIVE')?->class_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->department?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-outline btn-sm text-xs w-full">
                        Update Penempatan Kelas
                    </button>
                </form>

                <!-- Riwayat Kelas -->
                <div class="pt-3 border-t border-bluelight">
                    <div class="text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider mb-2">Riwayat Rombel</div>
                    <div class="space-y-2">
                        @forelse($student->classEnrollments as $enr)
                            <div class="p-2 rounded-xl bg-bluelight/20 border border-bluelight flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-bluedark">{{ $enr->schoolClass?->name }}</div>
                                    <div class="text-[10px] text-bluedark/50">Mulai: {{ \Carbon\Carbon::parse($enr->start_date)->translatedFormat('d M Y') }}</div>
                                </div>
                                <span class="badge {{ $enr->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $enr->status }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-bluedark/40 italic">Belum ada riwayat kelas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Rekam Ringkas Disiplin / Poin -->
            <div class="panel p-5">
                <h3 class="font-heading font-semibold text-bluedark text-sm mb-3">Catatan BK &amp; Kedisiplinan</h3>
                <div class="text-xs text-bluedark/70 space-y-2">
                    <div class="flex items-center justify-between">
                        <span>Riwayat Izin Keluar:</span>
                        <strong class="font-semibold">{{ $student->exitPermits->count() }} kali</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Catatan Poin Pelanggaran:</span>
                        <strong class="font-semibold">{{ $student->disciplineRecords->count() }} catatan</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
