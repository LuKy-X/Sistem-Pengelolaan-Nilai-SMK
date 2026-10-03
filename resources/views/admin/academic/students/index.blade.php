@extends('layouts.admin')

@section('title', 'Manajemen Siswa')

@push('styles')
<style>
/* Hilangkan seluruh scrollbar pada modal pendaftaran siswa */
.no-scrollbar::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
.no-scrollbar {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}
</style>
@endpush

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

    <!-- KPI Metric Cards Row (Sesuai Style Dashboard Admin) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card">
            <div class="kpi-icon bg-bluelight text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Total Siswa Terdaftar</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['active'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Siswa Status Aktif</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-blueprim/10 text-blueprim">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['enrolled'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Siswa Masuk Rombel</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-indigo-100 text-indigo-600">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div>
                <div class="font-heading text-xl font-bold text-bluedark leading-none">{{ number_format($stats['graduated'] ?? 0) }}</div>
                <div class="text-[11px] text-bluedark/55 mt-1">Siswa Telah Lulus</div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Toolbar -->
    <div class="panel p-4 space-y-3">
        <form method="GET" action="{{ route('admin.academic.students.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-bluedark/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIS, NISN, Nama, atau Email..." class="f-input text-xs py-2 pl-9 pr-3 w-full">
            </div>

            <div class="w-full sm:w-auto min-w-[140px]">
                <select name="class_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[130px]">
                <select name="grade_level_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Tingkat --</option>
                    @foreach($gradeLevels as $gl)
                        <option value="{{ $gl->id }}" {{ ($gradeLevelId ?? '') == $gl->id ? 'selected' : '' }}>{{ $gl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[160px]">
                <select name="department_id" class="f-select text-xs py-2 w-full">
                    <option value="">-- Semua Jurusan --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->short_name ?: $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[110px]">
                <select name="gender" class="f-select text-xs py-2 w-full">
                    <option value="">-- Gender --</option>
                    <option value="MALE" {{ ($gender ?? '') === 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="FEMALE" {{ ($gender ?? '') === 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[130px]">
                <select name="status" class="f-select text-xs py-2 w-full">
                    <option value="">-- Status Siswa --</option>
                    <option value="ACTIVE" {{ ($status ?? '') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="GRADUATED" {{ ($status ?? '') === 'GRADUATED' ? 'selected' : '' }}>Lulus</option>
                    <option value="MOVED" {{ ($status ?? '') === 'MOVED' ? 'selected' : '' }}>Pindah</option>
                    <option value="DROPOUT" {{ ($status ?? '') === 'DROPOUT' ? 'selected' : '' }}>Keluar (DO)</option>
                    <option value="INACTIVE" {{ ($status ?? '') === 'INACTIVE' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary btn-sm text-xs py-2 px-4 flex items-center gap-1.5 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Terapkan</span>
                </button>
                @if($search || $classId || ($departmentId ?? '') || ($gradeLevelId ?? '') || ($gender ?? '') || ($status ?? ''))
                    <a href="{{ route('admin.academic.students.index') }}" class="btn btn-outline btn-sm text-xs py-2 px-3 text-rose-600 border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between text-xs text-bluedark/50 pt-2 border-t border-slate-100">
            <div>
                Menampilkan <strong>{{ $students->count() }}</strong> dari <strong>{{ $students->total() }}</strong> siswa
            </div>
            @if($search || $classId || ($departmentId ?? '') || ($gradeLevelId ?? '') || ($gender ?? '') || ($status ?? ''))
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blueprim">
                    <span class="w-2 h-2 rounded-full bg-blueprim animate-pulse"></span>
                    Filter aktif
                </span>
            @endif
        </div>
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
                            $schoolClass = $activeEnrollment?->schoolClass;
                            $department = $schoolClass?->department;
                        @endphp
                        <tr>
                            <td>{{ $students->firstItem() + $idx }}</td>
                            <td>
                                <span class="font-mono text-xs font-semibold text-bluedark block">{{ $st->nis }}</span>
                                <span class="text-[11px] text-bluedark/50 font-mono">{{ $st->nisn }}</span>
                            </td>
                            <td class="font-semibold text-bluedark">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-blueprim/10 text-blueprim font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($st->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.academic.students.show', $st) }}" class="hover:underline hover:text-blueprim block">
                                            {{ $st->full_name }}
                                        </a>
                                        @if($st->user?->email)
                                            <span class="text-[11px] text-bluedark/50 block font-normal">{{ $st->user->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($schoolClass)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-bluedark text-xs">{{ $schoolClass->name }}</span>
                                        @if($department)
                                            <span class="badge badge-blue text-[9px] py-0 px-1.5">{{ $department->short_name ?: $department->code }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-bluedark/40 italic">Belum ada rombel</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs font-semibold {{ $st->gender === 'MALE' ? 'text-blue-600' : 'text-rose-600' }}">
                                    {{ $st->gender === 'MALE' ? 'L' : 'P' }}
                                </span>
                            </td>
                            <td class="text-xs text-bluedark/70">{{ $st->phone ?? '-' }}</td>
                            <td>
                                @php
                                    $stBadge = match($st->status) {
                                        'ACTIVE' => 'badge-green',
                                        'GRADUATED' => 'badge-blue',
                                        'MOVED' => 'badge-yellow',
                                        'DROPOUT' => 'badge-red',
                                        default => 'badge-gray',
                                    };
                                    $stLabel = match($st->status) {
                                        'ACTIVE' => 'Aktif',
                                        'GRADUATED' => 'Lulus',
                                        'MOVED' => 'Pindah',
                                        'DROPOUT' => 'Keluar',
                                        default => $st->status,
                                    };
                                @endphp
                                <span class="badge {{ $stBadge }} text-[10px]">
                                    {{ $stLabel }}
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
                            <td colspan="8" class="text-center py-12">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-bluedark/40 flex items-center justify-center">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-bluedark">Data Siswa Tidak Ditemukan</p>
                                    <p class="text-xs text-bluedark/50">Tidak ada data siswa yang cocok dengan kriteria pencarian atau filter yang dipilih.</p>
                                    @if($search || $classId || ($departmentId ?? '') || ($gradeLevelId ?? '') || ($gender ?? '') || ($status ?? ''))
                                        <div class="pt-2">
                                            <a href="{{ route('admin.academic.students.index') }}" class="btn btn-outline btn-sm text-xs py-1 px-3">
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
            {{ $students->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Siswa Baru -->
<div id="studentModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl animate-in fade-in duration-150 max-h-[92vh] overflow-y-auto no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
        <div class="flex items-center justify-between mb-5 pb-3 border-b border-bluelight">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-bluelight text-blueprim flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-lg text-bluedark leading-tight">Pendaftaran Siswa Baru</h3>
                    <p class="text-xs text-bluedark/50">Lengkapi identitas siswa dan buat akun login resmi</p>
                </div>
            </div>
            <button type="button" onclick="closeStudentModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-bluedark/50 hover:text-bluedark flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.academic.students.store') }}" class="space-y-4">
            @csrf

            <!-- Baris 1: NIS & NISN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label">NIS (Nomor Induk Siswa) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nis" id="student_nis" value="{{ old('nis') }}" data-check-unique="nis" required placeholder="Contoh: 12345" class="f-input @error('nis') border-rose-500 @enderror">
                    @error('nis')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">NISN (Nasional) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nisn" id="student_nisn" value="{{ old('nisn') }}" data-check-unique="nisn" required placeholder="Contoh: 0056789012" class="f-input @error('nisn') border-rose-500 @enderror">
                    @error('nisn')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Nama Lengkap & Jenis Kelamin -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" id="student_full_name" value="{{ old('full_name') }}" required placeholder="Nama Lengkap Siswa" class="f-input @error('full_name') border-rose-500 @enderror">
                    @error('full_name')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="gender" required class="f-select @error('gender') border-rose-500 @enderror">
                        <option value="MALE" {{ old('gender', 'MALE') === 'MALE' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="FEMALE" {{ old('gender') === 'FEMALE' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 3: Penempatan Rombel / Kelas & Nomor Telepon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label">Penempatan Kelas</label>
                    <select name="class_id" class="f-select @error('class_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->department?->name }})</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" class="f-input @error('phone') border-rose-500 @enderror">
                    @error('phone')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 4: Tempat Lahir & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="f-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place') }}" placeholder="Contoh: Karanganyar" class="f-input @error('birth_place') border-rose-500 @enderror">
                    @error('birth_place')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="f-input @error('birth_date') border-rose-500 @enderror">
                    @error('birth_date')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 5: Alamat Domisili -->
            <div>
                <label class="f-label">Alamat Domisili Siswa</label>
                <textarea name="address" rows="2" placeholder="Masukkan alamat lengkap tempat tinggal siswa saat ini..." class="f-textarea @error('address') border-rose-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Baris 6: Akun Pengguna Siswa -->
            <div class="p-4 rounded-2xl bg-bluelight/20 border border-bluelight space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blueprim" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span class="text-xs font-bold text-bluedark uppercase tracking-wider">Pembuatan Akun Login Siswa</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="f-label text-xs mb-0">Email Resmi Siswa</label>
                            <button type="button" onclick="generateStudentEmail()" class="text-[11px] font-semibold text-blueprim hover:text-blue-800 hover:underline inline-flex items-center gap-1 transition-colors" title="Generate email otomatis dari nama lengkap">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/></svg>
                                <span>Sesuaikan dari Nama</span>
                            </button>
                        </div>
                        <input type="email" name="email" id="student_email" value="{{ old('email') }}" data-check-unique="email" placeholder="siswa@smkn2kra.sch.id" class="f-input text-xs @error('email') border-rose-500 @enderror">
                        @error('email')
                            <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="f-label text-xs mb-1">Password Default</label>
                        <input type="text" name="password" value="{{ old('password', 'password123') }}" class="f-input text-xs @error('password') border-rose-500 @enderror">
                        @error('password')
                            <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeStudentModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Simpan Data Siswa</span>
                </button>
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

    function generateStudentEmail() {
        const nameInput = document.getElementById('student_full_name');
        const emailInput = document.getElementById('student_email');
        const rawName = nameInput ? nameInput.value.trim() : '';

        if (!rawName) {
            alert('Silakan isi Nama Lengkap Siswa terlebih dahulu.');
            if (nameInput) nameInput.focus();
            return;
        }

        const cleanName = rawName.toLowerCase().replace(/[^a-z0-9]/g, '');
        if (!cleanName) {
            alert('Nama Lengkap tidak mengandung huruf atau angka yang valid.');
            return;
        }

        emailInput.value = `${cleanName}@smkn2kra.sch.id`;
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    @if($errors->any() && (old('nis') || old('full_name') || old('nisn')))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('studentModal').classList.remove('hidden');
    });
    @endif
</script>
@endpush
@endsection
