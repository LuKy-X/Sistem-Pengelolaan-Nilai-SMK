@extends('layouts.teacher')

@section('title', 'Buat Rubrik Penilaian Baru')

@section('content')
<div class="space-y-6">

    <!-- Stepper Navigation -->
    <div class="flex items-center gap-2 text-xs md:text-sm text-bluedark/60">
        <a href="{{ route('teacher.rubrics.index') }}" class="font-semibold text-blueprim hover:underline">
            Rubrik Penilaian
        </a>
        <span>/</span>
        <span class="font-semibold text-bluedark">
            Buat Rubrik Baru
        </span>
    </div>

    <!-- Main Form Block -->
    <div class="form-block max-w-4xl mx-auto">
        <div class="form-block__header">
            <h3>Form Rubrik Penilaian</h3>
            <p>Tentukan parameter dan kriteria rubrik penskoran tugas atau ujian praktik</p>
        </div>

        <form action="{{ route('teacher.rubrics.store') }}" method="POST" class="form-block__body space-y-6">
            @csrf

            <!-- Rubric Details -->
            <div class="space-y-4">
                <div>
                    <label class="f-label">Nama / Judul Rubrik <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="cth. Rubrik Penilaian Praktik Web PWPB" required class="f-input">
                </div>

                <div>
                    <label class="f-label">Deskripsi Rubrik</label>
                    <textarea name="description" rows="2" placeholder="Tuliskan petunjuk umum penskoran..." class="f-textarea"></textarea>
                </div>
            </div>

            <!-- Dynamic Criteria Section -->
            <div class="pt-4 border-t border-[#E3F2FD]">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-heading font-bold text-bluedark text-sm">Daftar Kriteria Penskoran</h4>
                        <p class="text-xs text-bluedark/60">Tambahkan minimal 1 kriteria penilaian</p>
                    </div>
                    <button type="button" onclick="addCriterionRow()" class="btn btn-outline btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>+ Tambah Kriteria</span>
                    </button>
                </div>

                <div id="criteriaContainer" class="space-y-3">
                    <!-- Default Row 1 -->
                    <div class="criterion-item p-4 rounded-2xl bg-[#FAFDFF] border border-[#E3F2FD] space-y-3 relative">
                        <div class="grid md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="f-label">Nama Kriteria <span class="text-red-500">*</span></label>
                                <input type="text" name="criteria[0][criterion]" placeholder="cth. Fungsionalitas Fitur Utama" required class="f-input">
                            </div>
                            <div>
                                <label class="f-label">Poin Maksimal <span class="text-red-500">*</span></label>
                                <input type="number" name="criteria[0][max_points]" value="40" min="1" max="100" required class="f-input">
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Deskriptor Pencapaian</label>
                            <input type="text" name="criteria[0][description]" placeholder="cth. Seluruh modul CRUD dan validasi berjalan sempurna tanpa bug" class="f-input text-xs">
                        </div>
                    </div>

                    <!-- Default Row 2 -->
                    <div class="criterion-item p-4 rounded-2xl bg-[#FAFDFF] border border-[#E3F2FD] space-y-3 relative">
                        <div class="grid md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="f-label">Nama Kriteria <span class="text-red-500">*</span></label>
                                <input type="text" name="criteria[1][criterion]" placeholder="cth. Kerapian Kode &amp; Standar Penulisan" required class="f-input">
                            </div>
                            <div>
                                <label class="f-label">Poin Maksimal <span class="text-red-500">*</span></label>
                                <input type="number" name="criteria[1][max_points]" value="30" min="1" max="100" required class="f-input">
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Deskriptor Pencapaian</label>
                            <input type="text" name="criteria[1][description]" placeholder="cth. Mengikuti arsitektur MVC, indentasi rapi, dan penamaan variabel semantik" class="f-input text-xs">
                        </div>
                        <button type="button" onclick="this.closest('.criterion-item').remove()" class="text-xs text-red-500 hover:text-red-700 font-semibold mt-1">
                            &times; Hapus Kriteria
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('teacher.rubrics.index') }}" class="btn btn-outline btn-sm">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Simpan Rubrik</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    let criterionIndex = 2;

    function addCriterionRow() {
        const container = document.getElementById('criteriaContainer');
        const div = document.createElement('div');
        div.className = 'criterion-item p-4 rounded-2xl bg-[#FAFDFF] border border-[#E3F2FD] space-y-3 relative';
        div.innerHTML = `
            <div class="grid md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="f-label">Nama Kriteria <span class="text-red-500">*</span></label>
                    <input type="text" name="criteria[${criterionIndex}][criterion]" placeholder="cth. Desain Tampilan & Responsivitas" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Poin Maksimal <span class="text-red-500">*</span></label>
                    <input type="number" name="criteria[${criterionIndex}][max_points]" value="30" min="1" max="100" required class="f-input">
                </div>
            </div>
            <div>
                <label class="f-label">Deskriptor Pencapaian</label>
                <input type="text" name="criteria[${criterionIndex}][description]" placeholder="cth. Antarmuka menarik, konsisten, dan berfungsi baik di HP & Laptop" class="f-input text-xs">
            </div>
            <button type="button" onclick="this.closest('.criterion-item').remove()" class="text-xs text-red-500 hover:text-red-700 font-semibold mt-1">
                &times; Hapus Kriteria
            </button>
        `;
        container.appendChild(div);
        criterionIndex++;
    }
</script>
@endsection
