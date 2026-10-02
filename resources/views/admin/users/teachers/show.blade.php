@extends('layouts.admin')

@section('title', 'Detail Guru — ' . $teacher->full_name)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.users.teachers.index') }}" class="text-xs text-blueprim hover:underline">&larr; Kembali ke Data Guru</a>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $teacher->full_name }}</h1>
            <p class="text-sm text-bluedark/60">
                NIP: <strong class="font-mono text-bluedark">{{ $teacher->nip }}</strong> &middot;
                Status: <span class="badge {{ $teacher->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }}">{{ $teacher->status }}</span> &middot;
                Akun: <strong class="text-bluedark">{{ $teacher->user?->email ?? 'Belum ada akun' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.academic.teaching-assignments.index') }}" class="btn btn-primary btn-sm">
                + Penugasan Mengajar
            </a>
        </div>
    </div>

    <!-- Detail Profil & Penugasan -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Kolom Form Profil (2 Kolom) -->
        <div class="panel p-5 lg:col-span-2 space-y-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px] border-b border-bluelight pb-3">Profil Data Tenaga Pendidik</h2>

            <form action="{{ route('admin.users.teachers.update', $teacher) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip', $teacher->nip) }}" required class="f-input">
                    </div>
                    <div>
                        <label class="f-label">Nama Lengkap &amp; Gelar</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $teacher->full_name) }}" required class="f-input">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Jenis Kelamin</label>
                        <select name="gender" class="f-select">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="MALE" {{ old('gender', $teacher->gender) === 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="FEMALE" {{ old('gender', $teacher->gender) === 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="f-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" class="f-input">
                    </div>
                </div>

                <div>
                    <label class="f-label">Status Kepegawaian</label>
                    <select name="status" required class="f-select">
                        <option value="ACTIVE" {{ old('status', $teacher->status) === 'ACTIVE' ? 'selected' : '' }}>Aktif Mengajar</option>
                        <option value="INACTIVE" {{ old('status', $teacher->status) === 'INACTIVE' ? 'selected' : '' }}>Non-Aktif / Cuti</option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-bluelight">
                    <div>
                        <span class="text-xs text-bluedark/60">Username login: <strong>{{ $teacher->user?->username ?? '-' }}</strong></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Penugasan & Wali Kelas (1 Kolom) -->
        <div class="space-y-5">
            <!-- Wali Kelas -->
            <div class="panel p-5 space-y-3">
                <h3 class="font-heading font-semibold text-bluedark text-sm">Wali Kelas</h3>
                @forelse($teacher->homeroomClasses as $c)
                    <div class="p-3 rounded-xl border border-bluelight bg-bluelight/20 space-y-1">
                        <div class="font-semibold text-xs text-bluedark">{{ $c->name }}</div>
                        <div class="text-[11px] text-bluedark/60">{{ $c->academicYear?->name ?? 'Tahun Ajaran Aktif' }}</div>
                    </div>
                @empty
                    <p class="text-xs text-bluedark/40 italic py-2">Bukan wali kelas pada rombel manapun.</p>
                @endforelse
            </div>

            <!-- Penugasan Mengajar -->
            <div class="panel p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-heading font-semibold text-bluedark text-sm">Penugasan Mengajar ({{ $teacher->teachingAssignments->count() }})</h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($teacher->teachingAssignments as $ta)
                        <div class="p-3 rounded-xl border border-bluelight hover:border-blueprim/40 transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-xs text-bluedark">{{ $ta->subject?->name }}</span>
                                <span class="badge badge-blue text-[10px]">{{ $ta->weekly_hours }} Jam</span>
                            </div>
                            <div class="text-[11px] text-bluedark/60 mt-1 flex items-center justify-between">
                                <span>Kelas: <strong>{{ $ta->schoolClass?->name }}</strong></span>
                                <span>{{ $ta->semester?->name }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-bluedark/40 italic py-2">Belum ada penugasan mengajar.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
