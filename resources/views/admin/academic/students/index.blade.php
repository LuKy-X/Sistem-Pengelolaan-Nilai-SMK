@extends('layouts.admin')

@section('title', 'Manajemen Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Data &amp; Manajemen Siswa</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola data induk siswa, penempatan rombel, dan akun login siswa</p>
        </div>

        <button type="button" onclick="openStudentModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Siswa Baru</span>
        </button>
    </div>

    <!-- Step Navigation (Hierarki Master Data Akademik) -->
    <nav class="flex items-center gap-1.5 p-1.5 rounded-2xl bg-white border border-bluelight shadow-2xs overflow-x-auto max-w-full" aria-label="Hierarki Data Akademik">
        <!-- Step 1: Daftar Jurusan -->
        <a href="{{ route('admin.academic.departments.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.departments.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.departments.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">1</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            <span>Daftar Jurusan</span>
        </a>

        <!-- Separator -->
        <svg class="w-3.5 h-3.5 text-bluedark/30 shrink-0 hidden sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>

        <!-- Step 2: Daftar Kelas -->
        <a href="{{ route('admin.academic.classes.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.classes.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.classes.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">2</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Daftar Kelas</span>
        </a>

        <!-- Separator -->
        <svg class="w-3.5 h-3.5 text-bluedark/30 shrink-0 hidden sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>

        <!-- Step 3: Daftar Siswa -->
        <a href="{{ route('admin.academic.students.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all shrink-0 {{ request()->routeIs('admin.academic.students.*') ? 'bg-blueprim text-white shadow-xs' : 'text-bluedark/70 hover:text-bluedark hover:bg-slate-50' }}">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-bold {{ request()->routeIs('admin.academic.students.*') ? 'bg-white/20 text-white' : 'bg-bluelight text-blueprim' }}">3</span>
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span>Daftar Siswa</span>
        </a>
    </nav>

    <!-- Filter & Pencarian -->
    <div class="panel p-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.academic.students.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIS, NISN, atau Nama Siswa..." class="f-input text-xs py-1.5 w-64">

            <select name="class_id" class="f-select text-xs py-1.5 w-48">
                <option value="">-- Semua Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm text-xs py-1.5">
                Filter
            </button>

            @if($search || $classId)
                <a href="{{ route('admin.academic.students.index') }}" class="text-xs text-rose-600 hover:underline">
                    Reset
                </a>
            @endif
        </form>

        <span class="text-xs text-bluedark/50">Total Siswa: <strong>{{ $students->total() }}</strong></span>
    </div>

    <!-- Table Siswa -->
    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Rombel / Kelas</th>
                        <th>L/P</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $st)
                        @php
                            $activeEnrollment = $st->classEnrollments->first();
                        @endphp
                        <tr>
                            <td>{{ $students->firstItem() + $idx }}</td>
                            <td class="font-mono text-xs">{{ $st->nis }} / {{ $st->nisn }}</td>
                            <td class="font-semibold text-bluedark">
                                <a href="{{ route('admin.academic.students.show', $st) }}" class="hover:underline">
                                    {{ $st->full_name }}
                                </a>
                            </td>
                            <td>
                                @if($activeEnrollment && $activeEnrollment->schoolClass)
                                    <span class="font-semibold text-bluedark">{{ $activeEnrollment->schoolClass->name }}</span>
                                @else
                                    <span class="text-xs text-bluedark/40 italic">Belum ada rombel</span>
                                @endif
                            </td>
                            <td>{{ $st->gender === 'MALE' ? 'L' : 'P' }}</td>
                            <td class="text-xs">{{ $st->phone ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $st->status === 'ACTIVE' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $st->status }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.academic.students.show', $st) }}" class="btn btn-outline btn-sm text-xs py-1 px-2.5">
                                        Detail
                                    </a>
                                    <form action="{{ route('admin.academic.students.destroy', $st) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?');">
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
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Tidak ada data siswa yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $students->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Siswa Baru -->
<div id="studentModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Pendaftaran Siswa Baru</h3>
            <button type="button" onclick="closeStudentModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.students.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" required placeholder="12345" class="f-input">
                </div>
                <div>
                    <label class="f-label">NISN (Nasional)</label>
                    <input type="text" name="nisn" required placeholder="0056789012" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Nama Lengkap Siswa</label>
                <input type="text" name="full_name" required placeholder="Nama Siswa" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Jenis Kelamin</label>
                    <select name="gender" required class="f-select">
                        <option value="MALE">Laki-laki</option>
                        <option value="FEMALE">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Penempatan Kelas</label>
                    <select name="class_id" class="f-select">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->department?->name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" placeholder="Karanganyar" class="f-input">
                </div>
                <div>
                    <label class="f-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="f-input">
            </div>

            <div>
                <label class="f-label">Alamat Domisili</label>
                <textarea name="address" rows="2" class="f-textarea"></textarea>
            </div>

            <!-- Akun Pengguna Opsional -->
            <div class="p-3.5 rounded-2xl bg-bluelight/20 border border-bluelight space-y-3">
                <div class="text-xs font-bold text-bluedark uppercase tracking-wider">Pembuatan Akun Login Siswa</div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="f-label text-xs">Email Login</label>
                        <input type="email" name="email" placeholder="siswa@smkn2.sch.id" class="f-input text-xs">
                    </div>
                    <div>
                        <label class="f-label text-xs">Password Default</label>
                        <input type="text" name="password" value="password123" class="f-input text-xs">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeStudentModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Data Siswa</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openStudentModal() {
        document.getElementById('studentModal').classList.remove('hidden');
    }
    function closeStudentModal() {
        document.getElementById('studentModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
