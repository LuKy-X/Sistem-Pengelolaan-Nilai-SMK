@extends('layouts.admin')

@section('title', 'Kelola Jurusan — ' . $department->name)

@section('content')
<div class="space-y-6">

    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.academic.departments.index') }}" class="text-xs text-blueprim hover:underline">&larr; Kembali ke Daftar Jurusan</a>
            </div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">{{ $department->name }}</h1>
            <p class="text-sm text-bluedark/60">Kode: <span class="font-mono font-semibold">{{ $department->code }}</span> &middot; Kelola profil, kompetensi, dan fasilitas jurusan</p>
        </div>

        <a href="{{ route('admin.academic.classes.index') }}" class="btn btn-primary btn-sm">
            Lihat Kelas Terdaftar
        </a>
    </div>

    <!-- Grid Detail Jurusan & Form Edit Profil -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Detail & Form Edit -->
        <div class="panel p-5 lg:col-span-2 space-y-5">
            <h2 class="font-heading font-semibold text-bluedark text-[15px] border-b border-bluelight pb-3">Profil Jurusan</h2>

            <form action="{{ route('admin.academic.departments.update', $department) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Kode Jurusan</label>
                        <input type="text" name="code" value="{{ old('code', $department->code) }}" required class="f-input">
                    </div>
                    <div>
                        <label class="f-label">Singkatan (Short Name)</label>
                        <input type="text" name="short_name" value="{{ old('short_name', $department->short_name) }}" class="f-input">
                    </div>
                </div>

                <div>
                    <label class="f-label">Nama Kompetensi Keahlian</label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" required class="f-input">
                </div>

                <div>
                    <label class="f-label">Deskripsi Jurusan</label>
                    <textarea name="description" rows="3" class="f-textarea">{{ old('description', $department->description) }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Visi</label>
                        <textarea name="vision" rows="3" class="f-textarea">{{ old('vision', $department->vision) }}</textarea>
                    </div>
                    <div>
                        <label class="f-label">Misi</label>
                        <textarea name="mission" rows="3" class="f-textarea">{{ old('mission', $department->mission) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="f-label">Prospek Karir &amp; Bidang Pekerjaan Lulusan</label>
                    <textarea name="career_prospects" rows="2" class="f-textarea">{{ old('career_prospects', $department->career_prospects) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-bluelight">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }} class="rounded text-blueprim">
                        <span class="text-xs font-semibold text-bluedark">Status Jurusan Aktif</span>
                    </label>

                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Rombel & Statistik -->
        <div class="space-y-5">
            <div class="panel p-5">
                <h3 class="font-heading font-semibold text-bluedark text-sm mb-3">Rombel dalam Jurusan Ini</h3>
                <div class="space-y-2">
                    @forelse($department->classes as $c)
                        <div class="p-2.5 rounded-xl border border-bluelight bg-bluelight/20 flex items-center justify-between text-xs">
                            <span class="font-semibold text-bluedark">{{ $c->name }}</span>
                            <span class="badge badge-blue">{{ $c->gradeLevel?->name ?? 'X' }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-bluedark/40 italic">Belum ada rombel terdaftar untuk jurusan ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Section Kompetensi Keahlian matching template/admin/data-jurusan-kompetensi.html -->
    <div class="panel p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-heading font-semibold text-bluedark text-[15px]">Unit &amp; Standar Kompetensi Kejuruan</h2>
                <p class="text-xs text-bluedark/50">Daftar capaian pembelajaran dan kompetensi keahlian yang dipelajari siswa</p>
            </div>
        </div>

        <!-- Form Tambah Cepat Kompetensi -->
        <form action="{{ route('admin.academic.departments.competencies.store', $department) }}" method="POST" class="p-4 rounded-2xl bg-bluelight/30 border border-bluelight flex flex-col md:flex-row items-end gap-3">
            @csrf
            <div class="flex-1 w-full">
                <label class="f-label text-xs">Judul Unit Kompetensi</label>
                <input type="text" name="title" required placeholder="Contoh: Pemrograman Berorientasi Objek Lanjut" class="f-input text-xs py-2">
            </div>
            <div class="flex-1 w-full">
                <label class="f-label text-xs">Deskripsi Singkat / Ruang Lingkup</label>
                <input type="text" name="description" placeholder="Menguasai konsep MVC, inheritance, database relationship..." class="f-input text-xs py-2">
            </div>
            <button type="submit" class="btn btn-primary btn-sm text-xs py-2 shrink-0">
                + Tambah Kompetensi
            </button>
        </form>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
            @forelse($department->competencies as $comp)
                <div class="p-3.5 rounded-2xl border border-bluelight bg-white flex flex-col justify-between shadow-xs">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-heading font-bold text-sm text-bluedark">{{ $comp->title }}</h4>
                            <form action="{{ route('admin.academic.departments.competencies.destroy', $comp) }}" method="POST" onsubmit="return confirm('Hapus kompetensi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 text-xs">&times;</button>
                            </form>
                        </div>
                        <p class="text-xs text-bluedark/60 mt-1">{{ $comp->description ?? '-' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-xs text-bluedark/40 italic">
                    Belum ada unit kompetensi yang ditambahkan.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
