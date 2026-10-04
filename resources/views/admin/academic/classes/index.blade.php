@extends('layouts.admin')

@section('title', 'Manajemen Rombel / Kelas')

@push('styles')
<style>
/* Sembunyikan scrollbar di seluruh elemen modal, tabel, dan halaman */
*::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
* {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}

/* Neutral Blur Modal Overlay (Tanpa Latar Biru) */
.custom-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow-y: auto;
}
.custom-modal-overlay.show {
    display: flex !important;
}
.custom-modal-dialog {
    background: #FFFFFF;
    border-radius: 1.5rem;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #E2E8F0;
    margin: auto;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalScaleIn {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
@endpush

@section('content')
<div class="space-y-6">


    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Rombel / Kelas</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola rombongan belajar, wali kelas, jurusan, dan proses kenaikan kelas</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Tombol Kenaikan Kelas (Promosi Rombel) -->
            <button type="button" onclick="openPromoteModal()" class="btn btn-outline btn-sm flex items-center gap-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all shadow-2xs font-semibold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="18 15 12 9 6 15"/>
                    <polyline points="18 9 12 3 6 9"/>
                </svg>
                <span>Kenaikan Kelas</span>
            </button>

            <!-- Tombol Tambah Kelas Baru -->
            <button type="button" onclick="openClassModal()" class="btn btn-primary btn-sm flex items-center gap-2 shadow-xs">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Kelas Baru</span>
            </button>
        </div>
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

    <!-- Grid Kelas Cards -->
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($classes as $c)
            <div class="kelas-card group flex flex-col justify-between p-5 bg-white border border-bluelight rounded-2xl shadow-card hover:shadow-lg transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-bluelight text-blueprim flex items-center justify-center font-bold text-xs group-hover:bg-blueprim group-hover:text-white transition-colors">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-bluedark text-sm">{{ $c->name }}</h3>
                                <p class="text-[11px] text-bluedark/50">{{ $c->department?->name ?? '-' }}</p>
                            </div>
                        </div>

                        <span class="badge {{ $c->is_active ? 'badge-green' : 'badge-gray' }} text-[10px]">
                            {{ $c->gradeLevel?->name ?? 'Tingkat' }}
                        </span>
                    </div>

                    <div class="kelas-card__meta text-xs text-bluedark/60 space-y-1.5 py-1">
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blueprim shrink-0" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <span>Wali Kelas: <strong class="text-bluedark">{{ $c->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blueprim shrink-0" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span><strong class="text-bluedark font-semibold">{{ $c->enrollments_count }}</strong> Siswa Terdaftar</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blueprim shrink-0" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>Tahun Ajaran: {{ $c->academicYear?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-bluelight mt-3">
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('admin.academic.classes.show', $c) }}" class="text-xs font-semibold text-blueprim hover:underline">
                            Detail Kelas &rarr;
                        </a>
                        <button type="button" onclick="openPromoteModal({{ $c->id }})" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 inline-flex items-center gap-1 transition-colors" title="Naikkan rombel ini ke tingkat berikutnya">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
                            <span>Naik Kelas</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="editClass({{ json_encode($c) }})" class="btn btn-sm btn-outline text-xs px-2.5">
                            Edit
                        </button>
                        <form action="{{ route('admin.academic.classes.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus rombel ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs px-2.5">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-xs text-bluedark/40">
                Belum ada data rombongan belajar (kelas). Silakan buat kelas baru.
            </div>
        @endforelse
    </div>

    @if($classes->hasPages())
        <div class="mt-4 pt-3 border-t border-bluelight">
            {{ $classes->links() }}
        </div>
    @endif

</div>

<!-- ========================================================================
     MODAL 1: TAMBAH / EDIT KELAS
     ======================================================================== -->
<div id="classModal" class="custom-modal-overlay">
    <div class="custom-modal-dialog max-w-md max-h-[92vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/80">
            <h3 id="classModalTitle" class="font-heading font-bold text-base text-bluedark">Tambah Kelas Baru</h3>
            <button type="button" onclick="closeClassModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold">&times;</button>
        </div>

        <form id="classForm" method="POST" action="{{ old('_action', route('admin.academic.classes.store')) }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div id="classMethodField">
                @if(old('_method') === 'PUT')
                    <input type="hidden" name="_method" value="PUT">
                @endif
            </div>

            <div>
                <label class="f-label text-xs">Nama Kelas (cth: XII RA, X RPL 1) <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="class_name" value="{{ old('name') }}" required placeholder="XII RA" class="f-input text-xs w-full @error('name') border-rose-500 @enderror">
                @error('name')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label text-xs">Kode Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" id="class_code" value="{{ old('code') }}" data-check-unique="class_code" required placeholder="XII-RA" class="f-input text-xs w-full @error('code') border-rose-500 @enderror">
                    @error('code')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label text-xs">Tingkat Kelas <span class="text-rose-500">*</span></label>
                    <select name="grade_level_id" id="class_grade_level_id" required class="f-select text-xs w-full @error('grade_level_id') border-rose-500 @enderror">
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}" {{ old('grade_level_id') == $gl->id ? 'selected' : '' }}>{{ $gl->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_level_id')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label text-xs">Jurusan <span class="text-rose-500">*</span></label>
                    <select name="department_id" id="class_department_id" required class="f-select text-xs w-full @error('department_id') border-rose-500 @enderror">
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="f-label text-xs">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="academic_year_id" id="class_academic_year_id" required class="f-select text-xs w-full @error('academic_year_id') border-rose-500 @enderror">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ (old('academic_year_id') == $y->id || (!old('academic_year_id') && $y->is_active)) ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label class="f-label text-xs">Wali Kelas</label>
                <select name="homeroom_teacher_id" id="class_homeroom_teacher_id" class="f-select text-xs w-full @error('homeroom_teacher_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ old('homeroom_teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
                @error('homeroom_teacher_id')
                    <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="class_is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="rounded text-blueprim">
                <label for="class_is_active" class="text-xs font-medium text-bluedark cursor-pointer">Kelas Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeClassModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm text-xs font-semibold px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================
     MODAL 2: KENAIKAN KELAS / PROMOSI ROMBEL (100% BEBAS SCROLLBAR, USER FRIENDLY)
     ======================================================================== -->
<div id="promoteClassModal" class="custom-modal-overlay">
    <div class="custom-modal-dialog max-w-2xl max-h-[92vh] w-full">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-bluelight bg-slate-50/90 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"/>
                        <polyline points="18 9 12 3 6 9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark leading-tight">Kenaikan Kelas (Promosi Rombel)</h3>
                    <p class="text-[11px] text-bluedark/50">Naikkan seluruh atau sebagian siswa rombel ke tingkat kelas berikutnya</p>
                </div>
            </div>

            <button type="button" onclick="closePromoteModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 text-xl font-bold leading-none">&times;</button>
        </div>

        <!-- Form Kenaikan Kelas -->
        <form id="promoteForm" method="POST" action="{{ route('admin.academic.classes.promote') }}" class="overflow-y-auto p-6 space-y-5">
            @csrf

            <!-- 1. Pemilihan Kelas Asal (Sumber) -->
            <div>
                <label class="f-label text-xs">Pilih Kelas / Rombel Asal <span class="text-rose-500">*</span></label>
                <select name="source_class_id" id="promote_source_class_id" required class="f-select text-xs w-full" onchange="onSourceClassSelect(this.value)">
                    <option value="">-- Pilih Kelas yang Ingin Dinaikkan --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" data-grade="{{ $c->gradeLevel?->code }}">
                            {{ $c->name }} &middot; {{ $c->department?->name }} ({{ $c->gradeLevel?->name ?? 'Tingkat' }}) &mdash; {{ $c->enrollments_count }} Siswa
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Loader State -->
            <div id="promoteLoading" class="hidden py-8 text-center text-xs text-bluedark/50 space-y-2">
                <div class="w-8 h-8 mx-auto border-2 border-blueprim border-t-transparent rounded-full animate-spin"></div>
                <p>Memuat data siswa dan rekomendasi kelas tingkat berikutnya...</p>
            </div>

            <!-- Konten Dinamis Kenaikan Kelas (Setelah Kelas Asal Dipilih) -->
            <div id="promoteContent" class="hidden space-y-5">

                <!-- Transition Flow Banner: Asal -> Tujuan -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-emerald-50 border border-blue-100 flex items-center justify-between gap-3 text-xs">
                    <div class="min-w-0">
                        <span class="text-[10px] text-bluedark/50 block font-semibold uppercase tracking-wider">Kelas Asal</span>
                        <span id="promoteSourceBannerName" class="font-heading font-bold text-bluedark truncate block">-</span>
                        <span id="promoteSourceBannerMeta" class="text-[11px] text-bluedark/60 block">-</span>
                    </div>

                    <div class="w-8 h-8 rounded-full bg-white text-emerald-600 border border-emerald-200 shadow-xs flex items-center justify-center shrink-0">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </div>

                    <div class="min-w-0 text-right">
                        <span class="text-[10px] text-emerald-700 block font-semibold uppercase tracking-wider">Target Kenaikan</span>
                        <span id="promoteTargetBannerName" class="font-heading font-bold text-emerald-800 truncate block">-</span>
                        <span id="promoteTargetBannerMeta" class="text-[11px] text-emerald-700/80 block">-</span>
                    </div>
                </div>

                <!-- OPSI PENENTUAN ROMBEL / KELULUSAN -->
                <div id="promoteClassOptions" class="space-y-4">
                    <!-- Radio Button Pilihan Tindakan (Hanya untuk Kelas X dan XI) -->
                    <div id="promoteActionRadioContainer" class="space-y-1.5">
                        <label class="f-label text-xs mb-1">Pilihan Tindakan Kenaikan Rombel</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Opsi 1: Otomatis Buat Kelas Baru -->
                            <label id="labelOptCreateNew" class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 cursor-pointer transition-all flex items-start gap-2.5">
                                <input type="radio" name="action_type" id="optActionCreateNew" value="create_new" checked class="mt-0.5 text-blueprim focus:ring-blueprim" onchange="toggleActionType('create_new')">
                                <div>
                                    <span class="font-heading font-bold text-xs text-bluedark block">Buat Kelas Baru</span>
                                    <span class="text-[11px] text-bluedark/60 block mt-0.5">Sistem otomatis menyiapkan kelas tingkat berikutnya</span>
                                </div>
                            </label>

                            <!-- Opsi 2: Pilih Kelas yang Sudah Ada -->
                            <label id="labelOptExisting" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer transition-all flex items-start gap-2.5">
                                <input type="radio" name="action_type" id="optActionExisting" value="existing" class="mt-0.5 text-blueprim focus:ring-blueprim" onchange="toggleActionType('existing')">
                                <div>
                                    <span class="font-heading font-bold text-xs text-bluedark block">Gunakan Kelas yang Ada</span>
                                    <span class="text-[11px] text-bluedark/60 block mt-0.5">Pindahkan siswa ke rombel yang sudah ada / kosong</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden Radio untuk Kelulusan Kelas XII -->
                    <input type="radio" name="action_type" id="optActionGraduate" value="graduate" class="hidden" onchange="toggleActionType('graduate')">

                    <!-- Panel Keterangan Kelulusan Siswa (Tingkat XII / Akhir) -->
                    <div id="promoteGraduationNotice" class="hidden p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-xs text-amber-950 space-y-2.5 shadow-2xs">
                        <div class="flex items-center gap-2.5 font-bold text-amber-900 text-sm">
                            <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            </div>
                            <div>
                                <h4 class="leading-tight font-heading font-bold">Mode Kelulusan Siswa Tingkat Akhir (Kelas XII)</h4>
                                <span class="text-[11px] text-amber-700 font-normal">Kenaikan kelas untuk tingkat akhir langsung diproses sebagai kelulusan resmi</span>
                            </div>
                        </div>
                        <p class="leading-relaxed text-amber-900/90 pl-10">
                            Seluruh siswa yang dicentang akan <strong>dinyatakan Lulus Resmi (Status: GRADUATED)</strong>, riwayat kelas di rombel ini diselesaikan, dan otomatis terhubung dengan modul Alumni sekolah. Rombel kelas XII ini akan tetap aktif dalam keadaan kosong (0 siswa) sehingga siap digunakan kembali oleh adik kelas yang naik tingkat.
                        </p>
                    </div>

                    <!-- Panel Opsi A: Parameter Kelas Baru -->
                    <div id="panelCreateNewClass" class="p-4 rounded-2xl bg-slate-50/80 border border-bluelight space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="f-label text-xs">Nama Kelas Baru <span class="text-rose-500">*</span></label>
                                <input type="text" name="new_class_name" id="promote_new_class_name" placeholder="XII Rekayasa Perangkat Lunak 1" class="f-input text-xs w-full">
                            </div>
                            <div>
                                <label class="f-label text-xs">Kode Kelas Baru <span class="text-rose-500">*</span></label>
                                <input type="text" name="new_class_code" id="promote_new_class_code" placeholder="XII-RPL-1" class="f-input text-xs w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="f-label text-xs">Tahun Ajaran Rombel Baru <span class="text-rose-500">*</span></label>
                                <select name="target_academic_year_id" id="promote_target_academic_year_id" class="f-select text-xs w-full">
                                    @foreach($academicYears as $y)
                                        <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="f-label text-xs">Wali Kelas Baru (Opsional)</label>
                                <select name="new_homeroom_teacher_id" id="promote_new_homeroom_teacher_id" class="f-select text-xs w-full">
                                    <option value="">-- Tetap / Pilih Nanti --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Opsi B: Dropdown Kelas Existing -->
                    <div id="panelExistingClass" class="hidden p-4 rounded-2xl bg-slate-50/80 border border-bluelight space-y-2">
                        <label class="f-label text-xs">Pilih Kelas Tujuan yang Ada <span class="text-rose-500">*</span></label>
                        <select name="target_class_id" id="promote_target_class_id" class="f-select text-xs w-full">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                        </select>
                        <p class="text-[11px] text-bluedark/50">Menampilkan rombel aktif pada tingkat berikutnya. Rombel bertanda <strong>[Kosong / Siap Digunakan]</strong> dapat langsung dipilih.</p>
                    </div>
                </div>

                <!-- 2. Checklist Siswa yang Dinaikkan -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between border-b border-bluelight pb-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="promoteCheckAll" checked onchange="togglePromoteSelectAll(this.checked)" class="rounded text-blueprim focus:ring-blueprim">
                            <label for="promoteCheckAll" class="text-xs font-bold text-bluedark cursor-pointer">Pilih Semua Siswa</label>
                        </div>
                        <span class="text-xs text-bluedark/60 font-medium">
                            <strong id="promoteSelectedCount" class="text-emerald-700">0</strong> dari <span id="promoteTotalCount">0</span> Siswa <span id="promoteActionCountLabel">Akan Dipromosikan</span>
                        </span>
                    </div>

                    <!-- Student List Container (Clean Table, No Scrollbar) -->
                    <div class="border border-bluelight rounded-2xl overflow-hidden shadow-2xs max-h-56 overflow-y-auto">
                        <table class="tbl w-full text-left">
                            <thead class="bg-slate-100 text-bluedark text-[11px]">
                                <tr>
                                    <th class="w-10 text-center">Pilih</th>
                                    <th class="w-24">NIS</th>
                                    <th>Nama Siswa</th>
                                    <th class="w-12 text-center">L/P</th>
                                    <th class="w-28 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="promoteStudentsTableBody" class="divide-y divide-slate-100 text-xs">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-bluedark/45 italic">
                        Petunjuk: Hapus centang bagi siswa yang dinyatakan <strong>tinggal kelas / tidak naik</strong>. Siswa yang tidak dicentang tidak akan dipindahkan ke rombel tingkat baru.
                    </p>
                </div>

                <!-- 3. Pengaturan Eksekusi -->
                <div class="pt-2 border-t border-bluelight/70 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="f-label text-xs" id="promote_date_label">Tanggal Efektif Kenaikan Kelas</label>
                        <input type="date" name="promotion_date" id="promote_promotion_date" value="{{ date('Y-m-d') }}" class="f-input text-xs w-full">
                    </div>

                    <div class="space-y-1">
                        <label class="flex items-center gap-2 cursor-pointer mt-5">
                            <input type="checkbox" name="deactivate_source_class" id="promote_deactivate_source_class" value="1" class="rounded text-blueprim">
                            <span class="text-xs font-semibold text-bluedark">Nonaktifkan / Arsipkan rombel asal</span>
                        </label>
                        <p id="promote_deactivate_help" class="text-[11px] text-bluedark/55 leading-relaxed">
                            Secara default tidak dicentang: rombel asal tetap aktif dengan status kosong (0 siswa) agar dapat langsung digunakan oleh kelas tingkat di bawahnya.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePromoteModal()" class="btn btn-outline btn-sm text-xs">Batal</button>
                <button type="submit" id="promoteSubmitBtn" class="btn btn-primary btn-sm text-xs font-bold px-5 flex items-center gap-1.5 shadow-xs" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/><polyline points="18 9 12 3 6 9"/></svg>
                    <span>Proses Kenaikan Kelas</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ==========================================
    // CLASS MODAL (CREATE / EDIT)
    // ==========================================
    function resetClassUnique() {
        const m = document.getElementById('classModal');
        m.querySelectorAll('.check-unique-feedback').forEach(el => el.innerHTML = '');
        m.querySelectorAll('input').forEach(el => el.classList.remove('border-emerald-500', 'border-rose-500'));
    }

    function openClassModal() {
        document.getElementById('classModalTitle').innerText = 'Tambah Kelas Baru';
        document.getElementById('classForm').action = "{{ route('admin.academic.classes.store') }}";
        document.getElementById('classMethodField').innerHTML = '';
        document.getElementById('class_name').value = '';
        document.getElementById('class_code').value = '';
        document.getElementById('class_code').removeAttribute('data-ignore-id');
        document.getElementById('class_homeroom_teacher_id').value = '';
        document.getElementById('class_is_active').checked = true;
        resetClassUnique();
        document.getElementById('classModal').classList.add('show');
    }

    function editClass(c) {
        document.getElementById('classModalTitle').innerText = 'Edit Kelas';
        document.getElementById('classForm').action = "/admin/academic/classes/" + c.id;
        document.getElementById('classMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('class_name').value = c.name;
        document.getElementById('class_code').value = c.code;
        document.getElementById('class_code').setAttribute('data-ignore-id', c.id);
        document.getElementById('class_grade_level_id').value = c.grade_level_id;
        document.getElementById('class_department_id').value = c.department_id;
        document.getElementById('class_academic_year_id').value = c.academic_year_id;
        document.getElementById('class_homeroom_teacher_id').value = c.homeroom_teacher_id || '';
        document.getElementById('class_is_active').checked = !!c.is_active;
        resetClassUnique();
        document.getElementById('classModal').classList.add('show');
    }

    function closeClassModal() {
        document.getElementById('classModal').classList.remove('show');
    }

    // ==========================================
    // PROMOTE MODAL (KENAIKAN KELAS / PROMOSI ROMBEL)
    // ==========================================
    let currentPromotionData = null;

    function openPromoteModal(sourceClassId = null) {
        document.getElementById('promoteClassModal').classList.add('show');
        if (sourceClassId) {
            const select = document.getElementById('promote_source_class_id');
            select.value = sourceClassId;
            onSourceClassSelect(sourceClassId);
        } else {
            resetPromoteForm();
        }
    }

    function closePromoteModal() {
        document.getElementById('promoteClassModal').classList.remove('show');
    }

    function resetPromoteForm() {
        document.getElementById('promote_source_class_id').value = '';
        document.getElementById('promoteLoading').classList.add('hidden');
        document.getElementById('promoteContent').classList.add('hidden');
        document.getElementById('promoteSubmitBtn').disabled = true;
        const deactCb = document.getElementById('promote_deactivate_source_class');
        if (deactCb) deactCb.checked = false;
        currentPromotionData = null;
    }

    function onSourceClassSelect(classId) {
        if (!classId) {
            resetPromoteForm();
            return;
        }

        const loader = document.getElementById('promoteLoading');
        const content = document.getElementById('promoteContent');
        const submitBtn = document.getElementById('promoteSubmitBtn');

        loader.classList.remove('hidden');
        content.classList.add('hidden');
        submitBtn.disabled = true;

        fetch(`/admin/academic/classes/${classId}/students-for-promotion`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            currentPromotionData = data;
            loader.classList.add('hidden');
            content.classList.remove('hidden');
            submitBtn.disabled = false;

            // Banner Information
            document.getElementById('promoteSourceBannerName').textContent = data.source_class.name;
            document.getElementById('promoteSourceBannerMeta').textContent = `${data.source_class.grade_level_name} &middot; ${data.source_class.department_name}`;

            // Populate suggestions
            document.getElementById('promote_new_class_name').value = data.suggested_name || '';
            document.getElementById('promote_new_class_code').value = data.suggested_code || '';

            // Populate Existing Target Classes dropdown
            const targetSelect = document.getElementById('promote_target_class_id');
            targetSelect.innerHTML = '<option value="">-- Pilih Kelas Tujuan --</option>';
            if (data.available_target_classes && data.available_target_classes.length > 0) {
                data.available_target_classes.forEach(tc => {
                    const opt = document.createElement('option');
                    opt.value = tc.id;
                    const count = tc.active_students_count ?? 0;
                    const statusText = count === 0 ? '0 Siswa · Kosong / Siap Digunakan' : `${count} Siswa Terdaftar`;
                    opt.textContent = `${tc.name} (${tc.code}) — [${statusText}] — ${tc.academic_year || '-'}`;
                    targetSelect.appendChild(opt);
                });
            }

            const actionRadioContainer = document.getElementById('promoteActionRadioContainer');
            const dateLabel = document.getElementById('promote_date_label');

            if (data.is_graduation) {
                // Tingkat XII -> Otomatis sembunyikan radio pilihan tindakan, langsung mode kelulusan
                if (actionRadioContainer) actionRadioContainer.classList.add('hidden');
                document.getElementById('optActionGraduate').checked = true;
                if (dateLabel) dateLabel.textContent = 'Tanggal Kelulusan Siswa';
                toggleActionType('graduate');
            } else {
                // Tingkat X / XI -> Tampilkan radio pilihan tindakan
                if (actionRadioContainer) actionRadioContainer.classList.remove('hidden');
                document.getElementById('optActionCreateNew').checked = true;
                if (dateLabel) dateLabel.textContent = 'Tanggal Efektif Kenaikan Kelas';
                toggleActionType('create_new');
            }

            // Populate Student Checklist Table
            renderStudentsTable(data.students);
        })
        .catch(err => {
            console.error('Error fetching students:', err);
            loader.classList.add('hidden');
            alert('Gagal mengambil data rombel dan siswa.');
        });
    }

    function toggleActionType(type) {
        const panelCreate = document.getElementById('panelCreateNewClass');
        const panelExisting = document.getElementById('panelExistingClass');
        const gradNotice = document.getElementById('promoteGraduationNotice');
        const nameInput = document.getElementById('promote_new_class_name');
        const codeInput = document.getElementById('promote_new_class_code');
        const targetSelect = document.getElementById('promote_target_class_id');
        const submitBtn = document.getElementById('promoteSubmitBtn');
        const labelCreate = document.getElementById('labelOptCreateNew');
        const labelExisting = document.getElementById('labelOptExisting');

        if (type === 'graduate') {
            panelCreate.classList.add('hidden');
            panelExisting.classList.add('hidden');
            gradNotice.classList.remove('hidden');
            nameInput.required = false;
            codeInput.required = false;
            targetSelect.required = false;

            document.getElementById('promoteTargetBannerName').textContent = 'Kelulusan Siswa (Alumni)';
            document.getElementById('promoteTargetBannerMeta').textContent = 'Status: GRADUATED & Selesai';
            submitBtn.querySelector('span').textContent = 'Proses Kelulusan Siswa & Kosongkan Rombel';
        } else if (type === 'create_new') {
            panelCreate.classList.remove('hidden');
            panelExisting.classList.add('hidden');
            gradNotice.classList.add('hidden');
            nameInput.required = true;
            codeInput.required = true;
            targetSelect.required = false;

            if (labelCreate) {
                labelCreate.classList.add('border-blue-200', 'bg-blue-50/50');
                labelCreate.classList.remove('border-slate-200', 'bg-slate-50');
            }
            if (labelExisting) {
                labelExisting.classList.remove('border-blue-200', 'bg-blue-50/50');
                labelExisting.classList.add('border-slate-200', 'bg-slate-50');
            }

            if (currentPromotionData) {
                document.getElementById('promoteTargetBannerName').textContent = nameInput.value || currentPromotionData.suggested_name;
                document.getElementById('promoteTargetBannerMeta').textContent = `${currentPromotionData.next_level ? currentPromotionData.next_level.name : 'Tingkat Baru'}`;
            }
            submitBtn.querySelector('span').textContent = 'Proses Kenaikan Kelas';
        } else { // existing
            panelCreate.classList.add('hidden');
            panelExisting.classList.remove('hidden');
            gradNotice.classList.add('hidden');
            nameInput.required = false;
            codeInput.required = false;
            targetSelect.required = true;

            if (labelExisting) {
                labelExisting.classList.add('border-blue-200', 'bg-blue-50/50');
                labelExisting.classList.remove('border-slate-200', 'bg-slate-50');
            }
            if (labelCreate) {
                labelCreate.classList.remove('border-blue-200', 'bg-blue-50/50');
                labelCreate.classList.add('border-slate-200', 'bg-slate-50');
            }

            targetSelect.onchange = function() {
                const selectedText = targetSelect.options[targetSelect.selectedIndex]?.text || '-';
                document.getElementById('promoteTargetBannerName').textContent = selectedText.split('—')[0]?.trim() || selectedText;
                document.getElementById('promoteTargetBannerMeta').textContent = 'Rombel Tujuan Terpilih';
            };
            const selectedText = targetSelect.options[targetSelect.selectedIndex]?.text || '-';
            document.getElementById('promoteTargetBannerName').textContent = selectedText.split('—')[0]?.trim() || selectedText;
            document.getElementById('promoteTargetBannerMeta').textContent = 'Rombel Tujuan Terpilih';
            submitBtn.querySelector('span').textContent = 'Proses Kenaikan Kelas';
        }
    }

    function renderStudentsTable(students) {
        const tbody = document.getElementById('promoteStudentsTableBody');
        tbody.innerHTML = '';

        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-bluedark/40 italic">Tidak ada siswa aktif terdaftar di rombel ini.</td></tr>';
            document.getElementById('promoteSelectedCount').textContent = '0';
            document.getElementById('promoteTotalCount').textContent = '0';
            return;
        }

        students.forEach((st, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="text-center py-2.5">
                    <input type="checkbox" name="student_ids[]" value="${st.id}" checked class="rounded text-blueprim focus:ring-blueprim student-promote-cb" onchange="updatePromoteCounts()">
                </td>
                <td class="font-mono text-slate-500 py-2.5">${st.nis || '-'}</td>
                <td class="font-semibold text-bluedark py-2.5">
                    <span>${st.full_name}</span>
                </td>
                <td class="text-center font-mono text-slate-400 py-2.5">${st.gender === 'MALE' ? 'L' : 'P'}</td>
                <td class="text-right py-2.5 pr-3">
                    <span class="status-indicator badge badge-green text-[10px]">${currentPromotionData?.is_graduation ? 'Lulus' : 'Akan Naik'}</span>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updatePromoteCounts();
    }

    function togglePromoteSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.student-promote-cb');
        checkboxes.forEach(cb => {
            cb.checked = checked;
        });
        updatePromoteCounts();
    }

    function updatePromoteCounts() {
        const checkboxes = document.querySelectorAll('.student-promote-cb');
        let selected = 0;

        const countActionLabel = document.getElementById('promoteActionCountLabel');
        if (countActionLabel) {
            countActionLabel.textContent = currentPromotionData?.is_graduation ? 'Akan Dinyatakan Lulus' : 'Akan Dipromosikan';
        }

        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            const badge = row.querySelector('.status-indicator');
            if (cb.checked) {
                selected++;
                badge.className = 'status-indicator badge badge-green text-[10px]';
                badge.textContent = currentPromotionData?.is_graduation ? 'Lulus' : 'Akan Naik';
            } else {
                badge.className = 'status-indicator badge badge-gray text-[10px]';
                badge.textContent = currentPromotionData?.is_graduation ? 'Tidak Lulus / Tinggal' : 'Tetap (Tinggal)';
            }
        });

        document.getElementById('promoteSelectedCount').textContent = selected;
        document.getElementById('promoteTotalCount').textContent = checkboxes.length;

        const checkAll = document.getElementById('promoteCheckAll');
        checkAll.checked = selected === checkboxes.length && checkboxes.length > 0;

        const submitBtn = document.getElementById('promoteSubmitBtn');
        submitBtn.disabled = selected === 0;
    }

    // Auto-open modal if promote_id is present in URL or upon validation error
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const promoteId = urlParams.get('promote_id');
        if (promoteId) {
            openPromoteModal(promoteId);
        }

        @if($errors->any())
            @if(old('source_class_id'))
                openPromoteModal({{ old('source_class_id') }});
            @elseif(old('code') || old('name'))
                document.getElementById('classModal').classList.add('show');
            @endif
        @endif
    });
</script>
@endpush
@endsection
