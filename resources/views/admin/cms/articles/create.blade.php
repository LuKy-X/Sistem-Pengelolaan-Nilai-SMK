@extends('layouts.admin')

@section('title', 'Tulis Artikel Baru')

@push('styles')
<!-- Google Fonts for rich typography options -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Quill.js Snow Theme (100% Free & Open Source, No API Key / No Expiration) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<!-- Cropper.js CSS (Free & Open Source Image Cropping, Rotation, Safe Area Framing) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<style>
/* ==========================================================================
   MICROSOFT WORD STYLE WYSIWYG SYSTEM
   ========================================================================== */
.word-editor-box {
    border: 1px solid #CBD5E1;
    border-radius: 1rem;
    background: #FFFFFF;
    box-shadow: 0 4px 16px -4px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.word-editor-box:focus-within {
    border-color: #1565C0;
    box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.12);
}

/* Ribbon Toolbar Header */
.word-ribbon {
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
    padding: 0.5rem 0.75rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.5rem;
    user-select: none;
}
.word-tool-group {
    display: inline-flex;
    align-items: center;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 0.5rem;
    padding: 2px 3px;
    gap: 2px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.word-tool-divider {
    width: 1px;
    height: 22px;
    background: #CBD5E1;
    margin: 0 1px;
}
.word-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    background: transparent;
    border: 1px solid transparent;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s ease;
}
.word-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
    border-color: #E2E8F0;
}
.word-btn.active {
    background: #E0F2FE !important;
    color: #0369A1 !important;
    border-color: #BAE6FD !important;
}
.word-select {
    height: 28px;
    padding: 0 0.5rem;
    font-size: 12px;
    font-weight: 500;
    color: #334155;
    background: #FFFFFF;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    outline: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.word-select:hover, .word-select:focus {
    background: #F8FAFC;
    border-color: #94A3B8;
}
.word-font-select {
    width: 135px;
}
.word-size-select {
    width: 105px;
}
.word-heading-select {
    width: 135px;
}

/* Color Picker Container */
.word-color-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    background: transparent;
    color: #334155;
    cursor: pointer;
}
.word-color-btn:hover {
    background: #F1F5F9;
}
.word-color-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

/* Word Document Paper Sheet */
.word-paper-desk {
    background: #F1F5F9;
    padding: 1.25rem 1rem;
    overflow-y: auto;
}
@media (min-width: 640px) {
    .word-paper-desk {
        padding: 1.75rem 1.5rem;
    }
}
.word-paper-sheet {
    background: #FFFFFF;
    border-radius: 0.75rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.08), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
    max-width: 820px;
    margin: 0 auto;
    min-height: 480px;
}
.word-quill-sheet.ql-container {
    border: none !important;
    font-size: 15px;
    line-height: 1.8;
}
.word-quill-sheet .ql-editor {
    min-height: 480px;
    max-height: 720px;
    overflow-y: auto;
    padding: 2rem 2.25rem;
    color: #1E293B;
}
.word-quill-sheet .ql-editor.ql-blank::before {
    color: #94A3B8;
    font-style: normal;
    left: 2.25rem;
}
/* Hide default Quill toolbar if any is attached */
.ql-toolbar.ql-snow {
    display: none !important;
}

/* Word Statusbar */
.word-statusbar {
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
    padding: 0.45rem 1rem;
    font-size: 11px;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Dropzone Styling */
.dropzone-container {
    border: 2px dashed #90CAF9;
    background: #F8FBFE;
    border-radius: 0.75rem;
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}
.dropzone-container:hover,
.dropzone-container.dragover {
    border-color: #1565C0;
    background: #E3F2FD;
    transform: scale(1.005);
}

/* Public Card Animation Replica */
.berita-card, .link-card {
    transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.25s ease;
    border-radius: 1.25rem;
    background: #FFFFFF;
    border: 1px solid #E3F2FD;
    overflow: hidden;
}
.berita-card:hover, .berita-card:active, .link-card:hover, .link-card:active {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px -8px rgba(13, 71, 161, 0.16);
}
.berita-card .card-thumb-wrap, .link-card .card-thumb-wrap {
    overflow: hidden;
    position: relative;
    aspect-ratio: 16 / 10;
}
.berita-card .card-thumb-wrap img, .link-card .card-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s cubic-bezier(0.2, 0, 0, 1);
}
.berita-card:hover .card-thumb-wrap img,
.berita-card:active .card-thumb-wrap img,
.link-card:hover .card-thumb-wrap img,
.link-card:active .card-thumb-wrap img {
    transform: scale(1.08);
}

/* Cropper.js Custom Styling: Solid Neutral Grey Background (No Transparent Checkerboard) */
.cropper-bg {
    background-image: none !important;
    background-color: #27272a !important; /* Zinc-800 solid abu-abu */
}
.cropper-modal {
    opacity: 0.75 !important;
    background-color: #18181b !important;
}
.cropper-view-box {
    outline: 2px solid #2563EB !important;
    outline-color: rgba(37, 99, 235, 0.9) !important;
}
.cropper-line {
    background-color: #3B82F6 !important;
}
.cropper-point {
    background-color: #2563EB !important;
    width: 8px !important;
    height: 8px !important;
}
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-bluelight/60">
        <div>
            <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-1">
                <a href="{{ route('admin.cms.articles') }}" onclick="handleExit(event, '{{ route('admin.cms.articles') }}')" class="hover:text-blueprim transition-colors">Manajemen Berita &amp; Artikel</a>
                <span>/</span>
                <span class="text-blueprim font-semibold">Tulis Baru</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold text-bluedark flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blueprim/10 text-blueprim grid place-items-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </span>
                <span>Tulis Artikel Baru</span>
            </h1>
            <p class="text-xs sm:text-sm text-bluedark/70 mt-0.5">
                Gunakan editor bergaya Microsoft Word. Blok teks yang ingin diubah font atau ukurannya secara spesifik.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="handleExit(event, '{{ route('admin.cms.articles') }}')" class="btn btn-outline btn-sm text-xs py-1.5 px-3 flex items-center gap-1.5 text-bluedark/70 hover:text-bluedark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Batal &amp; Kembali</span>
            </button>
            <button type="button" onclick="openPublicPreviewModal()" class="btn btn-outline btn-sm text-xs py-1.5 px-3 border-blueprim/40 text-blueprim hover:bg-blue-50 flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Pratinjau Publik</span>
            </button>
            <button type="button" onclick="submitArticleForm()" class="btn btn-primary btn-sm text-xs py-1.5 px-4 font-semibold shadow-xs hover:shadow-md flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>Simpan Artikel</span>
            </button>
        </div>
    </div>

    <!-- Error Validation Alert -->
    @if (isset($errors) && $errors->any())
        <div class="panel p-4 bg-rose-50 border-rose-200 text-rose-800 rounded-xl">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-bold">!</div>
                <div>
                    <h4 class="font-bold text-sm">Terdapat kesalahan pada isian form:</h4>
                    <ul class="list-disc pl-5 mt-1 text-xs space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Form Grid -->
    <form id="createArticleForm" method="POST" action="{{ route('admin.cms.articles.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Kolom Kiri: Judul, Editor WYSIWYG Microsoft Word, & Ringkasan Singkat (8 Kolom) -->
            <div class="lg:col-span-8 space-y-5">
                
                <!-- Judul Artikel -->
                <div class="panel p-5 space-y-3">
                    <div>
                        <label for="titleInput" class="block text-xs font-bold text-bluedark uppercase tracking-wider mb-1">
                            Judul Artikel <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="titleInput" name="title" value="{{ old('title') }}" required
                            placeholder="Contoh: Tim Robotik SMKN 2 Karanganyar Meraih Juara 1 Tingkat Nasional"
                            class="w-full px-3.5 py-2.5 text-base sm:text-lg font-semibold text-bluedark border border-bluelight rounded-xl focus:border-blueprim focus:ring-2 focus:ring-blueprim/20 outline-none transition-all placeholder:text-bluedark/30"
                            oninput="updateSlugPreview(this.value)">
                    </div>

                    <!-- Otomatisasi Slug Info -->
                    <div class="flex items-center gap-2 text-xs text-bluedark/60 bg-blue-50/60 px-3 py-2 rounded-lg border border-blue-100">
                        <svg class="text-blueprim shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <span class="truncate">
                            Tautan Publik (Otomatis): <strong class="text-blueprim font-mono" id="slugPreviewText">/berita/judul-artikel</strong>
                        </span>
                    </div>
                </div>

                <!-- Konten Lengkap (Editor WYSIWYG Model Microsoft Word) -->
                <div class="panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-bluedark uppercase tracking-wider">
                            Konten Lengkap Artikel (Editor Word) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-bluedark/60">Blok teks untuk mengubah font atau ukuran secara spesifik</span>
                    </div>

                    <!-- Hidden input to submit the actual HTML -->
                    <textarea name="content" id="rawContentTextarea" class="hidden" required>{{ old('content') }}</textarea>

                    <!-- The Complete Word Editor Box -->
                    <div class="word-editor-box">
                        
                        <!-- Microsoft Word Ribbon Toolbar -->
                        <div id="wordRibbonToolbar" class="word-ribbon" role="toolbar" aria-label="Toolbar Format Word">
                            
                            <!-- Group 1: Undo / Redo -->
                            <div class="word-tool-group">
                                <button type="button" class="word-btn" onclick="execUndo()" title="Urungkan (Undo - Ctrl+Z)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execRedo()" title="Ulangi (Redo - Ctrl+Y)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"/></svg>
                                </button>
                            </div>

                            <div class="word-tool-divider"></div>

                            <!-- Group 2: Jenis Huruf (Font) & Ukuran (Size) - Khusus untuk teks yang diblok -->
                            <div class="word-tool-group">
                                <!-- Font Family Dropdown -->
                                <select id="wordFontFamily" class="word-select word-font-select" onchange="applyFontFamily(this.value)" title="Pilih Jenis Huruf (Hanya untuk teks yang diblok)">
                                    <option value="" disabled selected>Pilih Font...</option>
                                    <option value="Arial, sans-serif" style="font-family: Arial, sans-serif;">Arial</option>
                                    <option value="'Times New Roman', Times, serif" style="font-family: 'Times New Roman', Times, serif;">Times New Roman</option>
                                    <option value="Calibri, sans-serif" style="font-family: Calibri, sans-serif;">Calibri</option>
                                    <option value="Georgia, serif" style="font-family: Georgia, serif;">Georgia</option>
                                    <option value="'Courier New', Courier, monospace" style="font-family: 'Courier New', Courier, monospace;">Courier New</option>
                                    <option value="'Segoe UI', Tahoma, sans-serif" style="font-family: 'Segoe UI', Tahoma, sans-serif;">Segoe UI</option>
                                    <option value="Inter, sans-serif" style="font-family: Inter, sans-serif;">Inter</option>
                                    <option value="Roboto, sans-serif" style="font-family: Roboto, sans-serif;">Roboto</option>
                                    <option value="Poppins, sans-serif" style="font-family: Poppins, sans-serif;">Poppins</option>
                                    <option value="'Comic Sans MS', cursive" style="font-family: 'Comic Sans MS', cursive;">Comic Sans</option>
                                </select>

                                <!-- Font Size Dropdown -->
                                <select id="wordFontSize" class="word-select word-size-select" onchange="applyFontSize(this.value)" title="Pilih Ukuran Huruf (Hanya untuk teks yang diblok)">
                                    <option value="" disabled selected>Ukuran...</option>
                                    <option value="11px">11 (Kecil)</option>
                                    <option value="13px">13 (Ramping)</option>
                                    <option value="15px">15 (Standar)</option>
                                    <option value="18px">18 (Sedang)</option>
                                    <option value="24px">24 (Besar)</option>
                                    <option value="32px">32 (Judul)</option>
                                </select>
                            </div>

                            <div class="word-tool-divider"></div>

                            <!-- Group 3: Gaya Huruf (B, I, U, S, Warna Teks, Warna Stabilo) -->
                            <div class="word-tool-group">
                                <button type="button" class="word-btn" id="btnBold" onclick="toggleFormat('bold')" title="Tebal (Bold - Ctrl+B)">
                                    <strong class="font-black text-sm">B</strong>
                                </button>
                                <button type="button" class="word-btn italic" id="btnItalic" onclick="toggleFormat('italic')" title="Miring (Italic - Ctrl+I)">
                                    <span class="font-serif italic font-bold text-sm">I</span>
                                </button>
                                <button type="button" class="word-btn underline" id="btnUnderline" onclick="toggleFormat('underline')" title="Garis Bawah (Underline - Ctrl+U)">
                                    <span class="underline font-bold text-sm">U</span>
                                </button>
                                <button type="button" class="word-btn line-through" id="btnStrike" onclick="toggleFormat('strike')" title="Coret (Strikethrough)">
                                    <span class="line-through font-bold text-sm">S</span>
                                </button>

                                <!-- Text Color Picker -->
                                <div class="word-color-btn relative" title="Warna Huruf (Teks Diblok)">
                                    <span class="text-xs font-black">A</span>
                                    <span id="textColorBar" class="absolute bottom-1 inset-x-1.5 h-1 bg-rose-600 rounded-sm"></span>
                                    <input type="color" class="word-color-input" value="#DC2626" onchange="applyTextColor(this.value)" title="Pilih Warna Huruf">
                                </div>

                                <!-- Background / Highlight Color Picker -->
                                <div class="word-color-btn relative" title="Warna Sorotan / Stabilo (Teks Diblok)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11-6 6v3h3l6-6"/><path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/></svg>
                                    <span id="bgColorBar" class="absolute bottom-1 inset-x-1.5 h-1 bg-amber-400 rounded-sm"></span>
                                    <input type="color" class="word-color-input" value="#FBBF24" onchange="applyHighlightColor(this.value)" title="Pilih Warna Sorotan">
                                </div>
                            </div>

                            <div class="word-tool-divider"></div>

                            <!-- Group 4: Gaya Paragraf & Perataan Teks (Alignment) -->
                            <div class="word-tool-group">
                                <!-- Heading Dropdown -->
                                <select id="wordHeading" class="word-select word-heading-select" onchange="applyHeading(this.value)" title="Gaya Paragraf (Heading)">
                                    <option value="">Normal (Paragraf)</option>
                                    <option value="1">Judul Utama (H1)</option>
                                    <option value="2">Sub Judul (H2)</option>
                                    <option value="3">Bagian Kecil (H3)</option>
                                </select>

                                <!-- Alignment Buttons -->
                                <button type="button" class="word-btn" id="btnAlignLeft" onclick="applyAlign('left')" title="Rata Kiri">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnAlignCenter" onclick="applyAlign('center')" title="Rata Tengah">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="10" x2="6" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="18" y1="18" x2="6" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnAlignRight" onclick="applyAlign('right')" title="Rata Kanan">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="7" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="7" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnAlignJustify" onclick="applyAlign('justify')" title="Rata Kiri-Kanan (Justify)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                                </button>
                            </div>

                            <div class="word-tool-divider"></div>

                            <!-- Group 5: Daftar (Lists), Kutipan, & Kode -->
                            <div class="word-tool-group">
                                <button type="button" class="word-btn" id="btnListBullet" onclick="toggleList('bullet')" title="Daftar Simbol (Bullets)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnListOrdered" onclick="toggleList('ordered')" title="Daftar Angka (Numbering)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnBlockquote" onclick="toggleBlockquote()" title="Kutipan (Blockquote)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>
                                </button>
                                <button type="button" class="word-btn" id="btnCodeBlock" onclick="toggleCodeBlock()" title="Kotak Kode (Code Block)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                </button>
                            </div>

                            <div class="word-tool-divider"></div>

                            <!-- Group 6: Sisipkan (Insert Link, Image) & Bersihkan -->
                            <div class="word-tool-group">
                                <button type="button" class="word-btn" id="btnLink" onclick="insertLink()" title="Sisipkan Tautan (Link)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="insertImage()" title="Sisipkan Gambar dari URL">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="clearFormatting()" title="Hapus Pemformatan (Clear Formatting)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                        </div>

                        <!-- Word Document Paper Canvas (Centered Paper Sheet with Shadow) -->
                        <div class="word-paper-desk">
                            <div class="word-paper-sheet">
                                <div id="quillEditor" class="word-quill-sheet">{!! old('content') !!}</div>
                            </div>
                        </div>

                        <!-- Word Statusbar (Bottom) -->
                        <div class="word-statusbar">
                            <div class="flex items-center gap-3 font-medium">
                                <span id="quillWordCount">0 kata | 0 karakter</span>
                                <span>&bull;</span>
                                <span>Halaman 1 dari 1</span>
                                <span>&bull;</span>
                                <span class="text-slate-500">Bahasa Indonesia</span>
                            </div>
                            <div class="flex items-center gap-2 text-emerald-600 font-semibold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                <span>Format Word Aktif</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Input Ringkasan Singkat (Lead / Excerpt) Manual -->
                <div class="panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="excerptInput" class="block text-xs font-bold text-bluedark uppercase tracking-wider">
                                Ringkasan Singkat (Lead / Excerpt)
                            </label>
                            <span class="text-[11px] text-bluedark/60">Tulis ringkasan singkat pengantar berita untuk cuplikan kartu di portal publik</span>
                        </div>
                        <span class="text-[11px] font-semibold text-bluedark/70 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-md" id="excerptCount">
                            0 / 250 karakter
                        </span>
                    </div>
                    <textarea id="excerptInput" name="excerpt" rows="3" maxlength="250"
                        placeholder="Tuliskan intisari atau rangkuman singkat dari artikel ini (maksimal 250 karakter)..."
                        class="w-full px-3.5 py-2.5 text-xs text-bluedark border border-bluelight rounded-xl focus:border-blueprim focus:ring-2 focus:ring-blueprim/20 outline-none transition-all placeholder:text-bluedark/30 leading-relaxed"
                        oninput="updateExcerptCounter(this.value)">{{ old('excerpt') }}</textarea>
                    <p class="text-[11px] text-bluedark/50">
                        * Input ini bersifat manual (tidak diisi otomatis) agar Anda dapat menyusun kalimat pengantar yang paling menarik.
                    </p>
                </div>

            </div>

            <!-- Kolom Kanan: Pengaturan Penerbitan, Kategori, Foto Sampul, Info Penulis (4 Kolom) -->
            <div class="lg:col-span-4 space-y-5">

                <!-- Panel Penerbitan (Status Terbit / Draf) -->
                <div class="panel p-5 space-y-4">
                    <h3 class="text-xs font-bold text-bluedark uppercase tracking-wider border-b border-bluelight/60 pb-2">
                        Status Penerbitan
                    </h3>

                    <div class="space-y-2.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight hover:border-emerald-300 hover:bg-emerald-50/30 cursor-pointer transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                            <input type="radio" name="status" value="PUBLISHED" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" {{ old('status', 'PUBLISHED') === 'PUBLISHED' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-sm text-emerald-800 flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span>Terbitkan Langsung (Online)</span>
                                </div>
                                <div class="text-[11px] text-bluedark/60 mt-0.5">
                                    Artikel langsung tampil di portal publik sekolah. Waktu terbit otomatis dicatat saat ini.
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight hover:border-slate-300 hover:bg-slate-50 cursor-pointer transition-colors has-[:checked]:border-slate-500 has-[:checked]:bg-slate-50">
                            <input type="radio" name="status" value="DRAFT" class="mt-0.5 text-slate-600 focus:ring-slate-500" {{ old('status') === 'DRAFT' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 shrink-0">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Simpan Sebagai Draf (Offline)</span>
                                </div>
                                <div class="text-[11px] text-bluedark/60 mt-0.5">
                                    Hanya disimpan di panel admin, belum dapat dibaca oleh siswa atau masyarakat.
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Info Penulis Otomatis -->
                    <div class="pt-3 border-t border-bluelight/60 space-y-2">
                        <div class="text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider">Penulis Artikel</div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-blueprim text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 1)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-bold text-bluedark truncate">{{ auth()->user()->name ?? 'Administrator' }}</div>
                                <div class="text-[10px] text-bluedark/50">Akun sesi saat ini (Otomatis)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Kategori -->
                <div class="panel p-5 space-y-3">
                    <label for="categorySelect" class="block text-xs font-bold text-bluedark uppercase tracking-wider">
                        Kategori Artikel <span class="text-rose-500">*</span>
                    </label>
                    <select id="categorySelect" name="category_id" required class="w-full px-3 py-2 text-xs font-medium text-bluedark border border-bluelight rounded-xl focus:border-blueprim focus:ring-2 focus:ring-blueprim/20 outline-none transition-all bg-white">
                        <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)old('category_id') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Panel Foto Sampul (Drag & Drop Zone + Preview) -->
                <div class="panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-bluedark uppercase tracking-wider">
                            Foto Sampul (Thumbnail)
                        </label>
                        <span class="text-[10px] text-bluedark/40">Maks. 4MB</span>
                    </div>

                    <!-- Dropzone -->
                    <div id="dropzoneArea" class="dropzone-container" onclick="document.getElementById('thumbnailInput').click()">
                        <input type="file" id="thumbnailInput" name="thumbnail" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" onchange="handleFileSelected(this.files)">
                        <input type="file" id="originalThumbnailInput" name="original_thumbnail" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">
                        
                        <!-- Empty placeholder state -->
                        <div id="dropzonePrompt" class="space-y-2 py-3">
                            <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <div class="text-xs font-bold text-bluedark">Tarik &amp; Lepaskan foto ke sini</div>
                            <div class="text-[11px] text-bluedark/50">atau klik untuk memilih dari komputer Anda (JPG, PNG, WEBP)</div>
                        </div>

                        <!-- Active Preview State -->
                        <div id="dropzonePreviewWrap" class="hidden space-y-2">
                            <!-- Clickable Image directly opening the adjust modal -->
                            <div class="relative w-full aspect-[16/10] rounded-2xl overflow-hidden border border-bluelight/80 hover:border-blueprim bg-slate-100 shadow-sm cursor-pointer group transition-all" onclick="event.stopPropagation(); openImageCropperModal();" title="Klik untuk menyesuaikan foto">
                                <img id="dropzonePreviewImg" src="" alt="Foto Sampul" class="w-full h-full object-cover transition-opacity duration-200 group-hover:opacity-90 pointer-events-none">
                            </div>

                            <!-- Actions Row -->
                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <button type="button" onclick="event.stopPropagation(); document.getElementById('thumbnailInput').click();" class="text-blueprim hover:text-blue-700 text-[11px] font-semibold flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span>Ganti Foto</span>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); removeSelectedFile();" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold flex items-center gap-1 px-2.5 py-1 rounded-lg hover:bg-rose-50 transition-colors">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    <span>Hapus Foto</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button Card -->
                <div class="panel p-5 space-y-3 bg-gradient-to-br from-blue-50/50 to-white">
                    <button type="button" onclick="submitArticleForm()" class="w-full btn btn-primary py-2.5 text-xs font-bold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan &amp; Publikasikan</span>
                    </button>
                    <button type="button" onclick="openPublicPreviewModal()" class="w-full btn btn-outline py-2 text-xs font-semibold text-bluedark border-bluelight hover:border-blueprim hover:text-blueprim flex items-center justify-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Pratinjau Tampilan Publik</span>
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>

<!-- ========================================================================= -->
<!-- MODAL: PRATINJAU TAMPILAN WEBSITE PUBLIK (KARTU + HALAMAN LENGKAP)         -->
<!-- ========================================================================= -->
<div id="previewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-bluelight overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Pratinjau -->
        <div class="px-6 py-4 border-b border-bluelight bg-slate-50/80 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark">Pratinjau Tampilan Website Publik</h3>
                    <p class="text-xs text-bluedark/60">Simulasi tampilan nyata artikel Anda saat dibaca pengunjung portal sekolah</p>
                </div>
            </div>

            <!-- Tab Switcher (Kartu vs Halaman Penuh) -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-bluelight">
                <button type="button" id="tabBtnCard" onclick="switchPreviewTab('card')" class="px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all">
                    Kartu Berita
                </button>
                <button type="button" id="tabBtnFull" onclick="switchPreviewTab('full')" class="px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all">
                    Halaman Penuh
                </button>
                <button type="button" onclick="closePublicPreviewModal()" class="ml-2 text-bluedark/40 hover:text-bluedark text-xl font-bold leading-none p-1">&times;</button>
            </div>
        </div>

        <!-- Body Pratinjau -->
        <div class="p-6 overflow-y-auto bg-slate-100/50 flex-1">

            <!-- TAB 1: PRATINJAU KARTU BERITA PUBLIK -->
            <div id="previewCardTab" class="max-w-md mx-auto py-6">
                <div class="text-xs text-center text-bluedark/50 mb-3">Arahkan kursor / tahan kartu di bawah untuk merasakan animasi public card:</div>
                
                <div class="berita-card cursor-pointer">
                    <div class="card-thumb-wrap bg-blue-50">
                        <img id="prevCardImg" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='250' viewBox='0 0 400 250'%3E%3Crect width='400' height='250' fill='%23E3F2FD'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%231565C0'%3EFoto Sampul Belum Dipilih%3C/text%3E%3C/svg%3E" alt="Sampul">
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between text-[11px] text-bluedark/60">
                            <span class="badge badge-blue text-[10px] px-2.5 py-0.5 rounded-full font-bold" id="prevCardCategory">
                                Informasi
                            </span>
                            <span id="prevCardDate">{{ now()->translatedFormat('d M Y') }}</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-bluedark line-clamp-2 leading-snug" id="prevCardTitle">
                            Judul Artikel Anda Akan Tampil Di Sini
                        </h4>
                        <p class="text-xs text-bluedark/70 line-clamp-3 leading-relaxed" id="prevCardExcerpt">
                            Ringkasan singkat akan muncul di sini sesuai yang Anda ketikkan...
                        </p>
                        <div class="pt-2 border-t border-bluelight flex items-center justify-between text-xs text-bluedark/60">
                            <span class="flex items-center gap-1.5 font-medium">
                                <span class="w-5 h-5 rounded-full bg-blueprim text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </span>
                                <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                            </span>
                            <span class="text-blueprim font-semibold flex items-center gap-1">
                                Baca Selengkapnya &rarr;
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PRATINJAU HALAMAN PENUH -->
            <div id="previewFullTab" class="hidden max-w-2xl mx-auto bg-white p-6 sm:p-10 rounded-2xl border border-bluelight shadow-sm">
                <!-- Category badge & date -->
                <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-3">
                    <span class="badge badge-blue text-xs px-3 py-1 rounded-full font-bold" id="prevFullCategory">Informasi</span>
                    <span>&bull;</span>
                    <span id="prevFullDate">{{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-heading font-bold text-bluedark leading-tight mb-4" id="prevFullTitle">
                    Judul Artikel Lengkap Anda
                </h1>

                <div class="flex items-center gap-3 pb-6 mb-6 border-b border-bluelight text-xs text-bluedark/70">
                    <div class="w-9 h-9 rounded-full bg-blueprim text-white font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-bluedark">{{ auth()->user()->name ?? 'Admin Sekolah' }}</div>
                        <div class="text-[11px] text-bluedark/50">Penulis Berita &amp; Humas SMKN 2 Karanganyar</div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="rounded-2xl overflow-hidden border border-bluelight mb-6 bg-slate-100 aspect-video">
                    <img id="prevFullImg" src="" alt="Sampul Artikel" class="w-full h-full object-cover">
                </div>

                <!-- Excerpt Block -->
                <div class="p-4 rounded-xl bg-blue-50/60 border-l-4 border-blueprim text-bluedark/80 text-sm font-medium leading-relaxed mb-6" id="prevFullExcerpt">
                    Ringkasan singkat artikel...
                </div>

                <!-- Formatted WYSIWYG Content Output -->
                <div class="ql-editor p-0 leading-relaxed text-bluedark/85" id="prevFullContent">
                    Konten lengkap artikel Anda akan muncul di sini...
                </div>
            </div>

        </div>

        <!-- Footer Pratinjau -->
        <div class="px-6 py-3 border-t border-bluelight bg-white flex items-center justify-between shrink-0">
            <span class="text-xs text-bluedark/50">Ini adalah pratinjau langsung sebelum disimpan ke database</span>
            <button type="button" onclick="closePublicPreviewModal()" class="btn btn-primary btn-sm text-xs py-1.5 px-4 font-semibold">
                Tutup Pratinjau
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PERINGATAN KONFIRMASI PEMBATALAN PERUBAHAN                          -->
<!-- ========================================================================= -->
<div id="cancelConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-bluelight overflow-hidden p-6 text-center animate-in fade-in zoom-in-95 duration-150">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center mb-4">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <h3 class="font-heading font-bold text-lg text-bluedark mb-2">
            Konfirmasi Pembatalan
        </h3>
        <p class="text-xs text-bluedark/70 leading-relaxed mb-6">
            Apakah Anda yakin ingin membatalkan perubahan? Data atau tulisan yang telah Anda masukkan belum disimpan dan seluruh perubahan akan dibatalkan.
        </p>
        <div class="grid grid-cols-2 gap-3">
            <button type="button" onclick="closeCancelConfirmModal()" class="btn btn-outline py-2.5 text-xs font-semibold text-bluedark border-bluelight hover:bg-slate-50 transition-colors">
                Lanjutkan Mengedit
            </button>
            <button type="button" onclick="proceedExit()" class="btn btn-danger py-2.5 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white transition-colors">
                Ya, Batalkan Perubahan
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ATUR POSISI FRAME 16:10 & ROTASI FOTO (NON-DESTRUCTIVE)             -->
<!-- ========================================================================= -->
<div id="imageCropModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[94vh] flex flex-col shadow-2xl border border-bluelight overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Modal -->
        <div class="px-6 py-4 border-b border-bluelight bg-slate-50/90 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blueprim flex items-center justify-center shrink-0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M9 3v18"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-base text-bluedark">Sesuaikan Foto</h3>
                    <p class="text-xs text-bluedark/60">Geser atau atur frame foto sesuai tampilan yang diinginkan</p>
                </div>
            </div>
            <button type="button" onclick="closeImageCropperModal()" class="w-8 h-8 rounded-lg text-bluedark/40 hover:text-bluedark hover:bg-slate-200/60 flex items-center justify-center text-xl font-bold leading-none transition-colors">
                &times;
            </button>
        </div>

        <!-- Body Modal: Workspace Frame Foto -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 flex flex-col space-y-4 bg-slate-50">

            <!-- Canvas Container: Solid Neutral Dark Grey (Tanpa Background Kotak-Kotak Catur) -->
            <div id="cropperImageContainer" class="w-full bg-zinc-900 rounded-2xl overflow-hidden flex items-center justify-center p-2 min-h-[380px] max-h-[480px] border border-zinc-700 shadow-inner relative">
                <img id="cropperImageTarget" src="" alt="Edit Foto" class="max-w-full max-h-[440px] block select-none">
            </div>

            <!-- Control Toolbar: Putar, Zoom, dan Reset -->
            <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-white rounded-xl border border-bluelight shadow-2xs">
                <!-- Rotasi & Balik -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-bluedark/70 uppercase tracking-wider px-2">Putar:</span>
                    <button type="button" onclick="cropperRotate(-90)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blueprim text-bluedark text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Putar 90° Berlawanan Jarum Jam">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>-90° Kiri</span>
                    </button>
                    <button type="button" onclick="cropperRotate(90)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blueprim text-bluedark text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Putar 90° Searah Jarum Jam">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.85.99 6.57 2.6L21 8"/><path d="M21 3v5h-5"/></svg>
                        <span>+90° Kanan</span>
                    </button>
                    <button type="button" onclick="cropperFlipH()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blueprim text-bluedark text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Cermin Horisontal (Balik Kiri-Kanan)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                        <span>Balik H</span>
                    </button>
                </div>

                <!-- Zoom & Reset -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold text-bluedark/70 uppercase tracking-wider px-2">Zoom:</span>
                    <button type="button" onclick="cropperZoom(0.1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blueprim text-bluedark text-sm font-bold flex items-center justify-center transition-colors" title="Perbesar Zoom">
                        +
                    </button>
                    <button type="button" onclick="cropperZoom(-0.1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blueprim text-bluedark text-sm font-bold flex items-center justify-center transition-colors" title="Perkecil Zoom">
                        -
                    </button>
                    <button type="button" onclick="cropperReset()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-bluedark/70 text-xs font-semibold flex items-center gap-1 transition-colors" title="Reset Frame ke Posisi Awal">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset Posisi</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-4 border-t border-bluelight bg-slate-50 flex items-center justify-end gap-2.5 shrink-0">
            <button type="button" onclick="closeImageCropperModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-bluedark border border-bluelight hover:bg-slate-100 transition-colors">
                Batal
            </button>
            <button type="button" onclick="applyImageCrop()" class="px-5 py-2 rounded-xl text-xs font-bold bg-blueprim hover:bg-blueprim/90 text-white shadow-md hover:shadow-lg flex items-center gap-1.5 transition-all">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Terapkan Posisi Frame</span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- Quill.js CDN (Free, perpetual, no API key) -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<!-- Cropper.js CDN (Free & Open Source Image Cropping, Rotation, Safe Area Framing) -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
let quill;
let selectedFileBlob = null;
let isFormDirty = false;
let isSubmitting = false;
let pendingExitUrl = '{{ route('admin.cms.articles') }}';
let savedSelectionRange = null;

// Register style-based font and size attributors so inline style="font-family: ...; font-size: ..." is used
try {
    const FontStyle = Quill.import('attributors/style/font');
    FontStyle.whitelist = null; // Accept any font family
    Quill.register(FontStyle, true);

    const SizeStyle = Quill.import('attributors/style/size');
    SizeStyle.whitelist = null; // Accept any font size
    Quill.register(SizeStyle, true);

    const AlignStyle = Quill.import('attributors/style/align');
    Quill.register(AlignStyle, true);
} catch (e) {
    console.warn('Quill attributor registration notice:', e);
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Quill Editor with custom toolbar handling
    quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Ketik naskah artikel Anda di sini seperti di Microsoft Word...',
        modules: {
            toolbar: false, // We control the ribbon toolbar directly with full Word features
            history: {
                delay: 500,
                maxStack: 100,
                userOnly: true
            }
        }
    });

    // Track text changes
    quill.on('text-change', function () {
        syncQuillToTextarea();
        isFormDirty = true;
    });

    // Track selection changes to update ribbon state and save selection
    quill.on('selection-change', function (range, oldRange, source) {
        if (range) {
            savedSelectionRange = range;
            updateToolbarState(range);
        }
    });

    // Initial sync
    syncQuillToTextarea();
    setupDropzone();
    updateSlugPreview(document.getElementById('titleInput').value);
    updateExcerptCounter(document.getElementById('excerptInput').value);

    // Track input dirty state for other form fields
    ['titleInput', 'categorySelect', 'excerptInput'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => { isFormDirty = true; });
            el.addEventListener('change', () => { isFormDirty = true; });
        }
    });
    document.querySelectorAll('input[name="status"]').forEach(el => {
        el.addEventListener('change', () => { isFormDirty = true; });
    });

    // Browser navigation / close tab protection
    window.addEventListener('beforeunload', function (e) {
        if (isFormDirty && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

/* ==========================================================================
   MICROSOFT WORD FORMATTING HANDLERS (SELECTION-SPECIFIC)
   ========================================================================== */

// Apply Font Family to ONLY the selected / blocked text
function applyFontFamily(fontValue) {
    if (!quill || !fontValue) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        if (range.length > 0) {
            // Text is selected ("di-blok")! Format ONLY the selected text:
            quill.formatText(range.index, range.length, 'font', fontValue, 'user');
            quill.setSelection(range.index, range.length, 'silent');
        } else {
            // No selection, set format for cursor
            quill.format('font', fontValue, 'user');
        }
    }
}

// Apply Font Size to ONLY the selected / blocked text
function applyFontSize(sizeValue) {
    if (!quill || !sizeValue) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        if (range.length > 0) {
            // Text is selected ("di-blok")! Format ONLY the selected text:
            quill.formatText(range.index, range.length, 'size', sizeValue, 'user');
            quill.setSelection(range.index, range.length, 'silent');
        } else {
            quill.format('size', sizeValue, 'user');
        }
    }
}

// Toggle Inline Formats (Bold, Italic, Underline, Strike) on selected text
function toggleFormat(formatName) {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const currentFormats = quill.getFormat(range);
        const isActive = !!currentFormats[formatName];
        if (range.length > 0) {
            quill.formatText(range.index, range.length, formatName, !isActive, 'user');
            quill.setSelection(range.index, range.length, 'silent');
        } else {
            quill.format(formatName, !isActive, 'user');
        }
    }
}

// Text Color Picker
function applyTextColor(color) {
    if (!quill || !color) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        if (range.length > 0) {
            quill.formatText(range.index, range.length, 'color', color, 'user');
            quill.setSelection(range.index, range.length, 'silent');
        } else {
            quill.format('color', color, 'user');
        }
    }
    const bar = document.getElementById('textColorBar');
    if (bar) bar.style.backgroundColor = color;
}

// Highlight Background Color Picker
function applyHighlightColor(color) {
    if (!quill || !color) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        if (range.length > 0) {
            quill.formatText(range.index, range.length, 'background', color, 'user');
            quill.setSelection(range.index, range.length, 'silent');
        } else {
            quill.format('background', color, 'user');
        }
    }
    const bar = document.getElementById('bgColorBar');
    if (bar) bar.style.backgroundColor = color;
}

// Paragraph Style / Heading
function applyHeading(level) {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const headerVal = level ? parseInt(level) : false;
        quill.formatLine(range.index, range.length || 1, 'header', headerVal, 'user');
    }
}

// Paragraph Alignment (Left, Center, Right, Justify)
function applyAlign(alignment) {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const alignVal = alignment === 'left' ? false : alignment;
        quill.formatLine(range.index, range.length || 1, 'align', alignVal, 'user');
    }
}

// Lists (Bullet & Ordered)
function toggleList(listType) {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const currentFormats = quill.getFormat(range);
        const isCurrent = currentFormats.list === listType;
        quill.formatLine(range.index, range.length || 1, 'list', isCurrent ? false : listType, 'user');
    }
}

// Blockquote
function toggleBlockquote() {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const currentFormats = quill.getFormat(range);
        const isCurrent = !!currentFormats.blockquote;
        quill.formatLine(range.index, range.length || 1, 'blockquote', !isCurrent, 'user');
    }
}

// Code Block
function toggleCodeBlock() {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range) {
        quill.focus();
        const currentFormats = quill.getFormat(range);
        const isCurrent = !!currentFormats['code-block'];
        quill.formatLine(range.index, range.length || 1, 'code-block', !isCurrent, 'user');
    }
}

// Insert Link
function insertLink() {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    const currentFormats = range ? quill.getFormat(range) : {};
    
    if (currentFormats.link) {
        quill.format('link', false, 'user');
        return;
    }
    
    const url = prompt('Masukkan tautan URL (contoh: https://smkn2-kra.sch.id):', 'https://');
    if (url && url.trim() !== '' && url !== 'https://') {
        let validUrl = url.trim();
        if (!validUrl.startsWith('http://') && !validUrl.startsWith('https://')) {
            validUrl = 'https://' + validUrl;
        }
        if (range && range.length > 0) {
            quill.formatText(range.index, range.length, 'link', validUrl, 'user');
        } else {
            const text = prompt('Teks yang ditampilkan:', validUrl);
            const index = range ? range.index : quill.getLength() - 1;
            quill.insertText(index, text || validUrl, 'link', validUrl, 'user');
        }
    }
}

// Insert Image from URL
function insertImage() {
    if (!quill) return;
    const url = prompt('Masukkan URL gambar (contoh: https://domain.com/foto.jpg):');
    if (url && url.trim() !== '') {
        const range = quill.getSelection() || savedSelectionRange;
        const index = range ? range.index : quill.getLength() - 1;
        quill.insertEmbed(index, 'image', url.trim(), 'user');
    }
}

// Clear Formatting on selected text
function clearFormatting() {
    if (!quill) return;
    const range = quill.getSelection() || savedSelectionRange;
    if (range && range.length > 0) {
        quill.removeFormat(range.index, range.length, 'user');
    }
}

// Undo & Redo
function execUndo() {
    if (quill && quill.history) quill.history.undo();
}
function execRedo() {
    if (quill && quill.history) quill.history.redo();
}

// Update active states on the ribbon toolbar based on selection
function updateToolbarState(range) {
    if (!quill || !range) return;
    const formats = quill.getFormat(range);

    toggleBtnActive('btnBold', !!formats.bold);
    toggleBtnActive('btnItalic', !!formats.italic);
    toggleBtnActive('btnUnderline', !!formats.underline);
    toggleBtnActive('btnStrike', !!formats.strike);
    toggleBtnActive('btnBlockquote', !!formats.blockquote);
    toggleBtnActive('btnCodeBlock', !!formats['code-block']);
    toggleBtnActive('btnListBullet', formats.list === 'bullet');
    toggleBtnActive('btnListOrdered', formats.list === 'ordered');
    toggleBtnActive('btnAlignLeft', !formats.align || formats.align === 'left');
    toggleBtnActive('btnAlignCenter', formats.align === 'center');
    toggleBtnActive('btnAlignRight', formats.align === 'right');
    toggleBtnActive('btnAlignJustify', formats.align === 'justify');

    // Update Font Family Select
    const fontSelect = document.getElementById('wordFontFamily');
    if (fontSelect && document.activeElement !== fontSelect) {
        fontSelect.value = formats.font || '';
    }

    // Update Font Size Select
    const sizeSelect = document.getElementById('wordFontSize');
    if (sizeSelect && document.activeElement !== sizeSelect) {
        sizeSelect.value = formats.size || '';
    }

    // Update Heading Select
    const headingSelect = document.getElementById('wordHeading');
    if (headingSelect && document.activeElement !== headingSelect) {
        headingSelect.value = formats.header ? String(formats.header) : '';
    }
}

function toggleBtnActive(btnId, isActive) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (isActive) {
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }
}

// Sync Quill content with hidden form textarea
function syncQuillToTextarea() {
    const textarea = document.getElementById('rawContentTextarea');
    const text = quill.getText().trim();
    const html = quill.root.innerHTML;
    
    // Check if effectively empty
    textarea.value = (text.length === 0 && !html.includes('<img')) ? '' : html;

    const wordCount = text.length > 0 ? text.split(/\s+/).filter(w => w.length > 0).length : 0;
    const charCount = text.length;
    document.getElementById('quillWordCount').innerText = `${wordCount} kata | ${charCount} karakter`;
}

// Exit & Cancellation Handlers
function handleExit(e, targetUrl) {
    if (isFormDirty) {
        if (e) e.preventDefault();
        pendingExitUrl = targetUrl;
        document.getElementById('cancelConfirmModal').classList.remove('hidden');
    } else {
        window.location.href = targetUrl;
    }
}

function closeCancelConfirmModal() {
    document.getElementById('cancelConfirmModal').classList.add('hidden');
}

function proceedExit() {
    isFormDirty = false;
    window.location.href = pendingExitUrl;
}

// Live Excerpt Character Counter
function updateExcerptCounter(val) {
    const count = (val || '').length;
    document.getElementById('excerptCount').innerText = `${count} / 250 karakter`;
}

// Live Slug Preview
function updateSlugPreview(title) {
    const slugEl = document.getElementById('slugPreviewText');
    if (!title || title.trim() === '') {
        slugEl.innerText = '/berita/judul-artikel';
        return;
    }
    const slug = title.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    slugEl.innerText = '/berita/' + (slug || 'judul-artikel');
}

// Drag and drop file upload
function setupDropzone() {
    const area = document.getElementById('dropzoneArea');
    const thumbInput = document.getElementById('thumbnailInput');
    
    if (thumbInput) {
        thumbInput.addEventListener('click', function () {
            this.value = null; // Memastikan event onchange selalu terpicu meski memilih file yang sama
        });
    }

    if (!area) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        area.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            area.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        area.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            area.classList.remove('dragover');
        }, false);
    });

    area.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length) {
            if (thumbInput) thumbInput.files = files;
            handleFileSelected(files);
        }
    }, false);
}

/* ==========================================================================
   CROPPER.JS IMAGE FRAMING & ROTATION LOGIC (NON-DESTRUCTIVE & NO-ZOOM BUG)
   ========================================================================== */
let cropperInstance = null;
let rawOriginalFile = null;
let rawOriginalFileBlob = null;
let savedCropData = null;
let currentCropFileName = 'foto-sampul.jpg';
let flipH = false;
let flipV = false;

function handleFileSelected(files) {
    if (!files || !files.length) return;
    const file = files[0];

    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar (JPG, PNG, WEBP, GIF).');
        return;
    }

    if (file.size > 8 * 1024 * 1024) {
        alert('Ukuran file maksimal adalah 8MB.');
        return;
    }

    isFormDirty = true;
    rawOriginalFile = file;
    savedCropData = null;

    // Masukkan file asli ke input original_thumbnail agar server juga menyimpan versi utuh
    try {
        const dtOrig = new DataTransfer();
        dtOrig.items.add(file);
        document.getElementById('originalThumbnailInput').files = dtOrig.files;
    } catch (e) {
        console.warn('DataTransfer notice:', e);
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        rawOriginalFileBlob = e.target.result;
        selectedFileBlob = e.target.result;
        
        const previewImg = document.getElementById('dropzonePreviewImg');
        if (previewImg) previewImg.src = selectedFileBlob;

        document.getElementById('dropzonePrompt').classList.add('hidden');
        document.getElementById('dropzonePreviewWrap').classList.remove('hidden');

        // Buka otomatis editor frame
        openImageCropperModal();
    };
    reader.readAsDataURL(file);
}

function removeSelectedFile() {
    const thumbInput = document.getElementById('thumbnailInput');
    if (thumbInput) thumbInput.value = '';
    const origInput = document.getElementById('originalThumbnailInput');
    if (origInput) origInput.value = '';
    rawOriginalFile = null;
    rawOriginalFileBlob = null;
    selectedFileBlob = null;
    savedCropData = null;
    isFormDirty = true;
    
    const previewImg = document.getElementById('dropzonePreviewImg');
    if (previewImg) previewImg.src = '';
    
    document.getElementById('dropzonePreviewWrap').classList.add('hidden');
    document.getElementById('dropzonePrompt').classList.remove('hidden');
}

function openImageCropperModal() {
    // Selalu muat foto ASLI yang utuh (bukan hasil potongan) agar bagian lain tidak hilang
    let src = rawOriginalFileBlob || selectedFileBlob;
    
    if (!src) {
        const previewImg = document.getElementById('dropzonePreviewImg');
        if (previewImg && previewImg.src && !previewImg.src.includes('data:image/svg+xml') && previewImg.src.length > 30) {
            src = previewImg.src;
        }
    }

    if (!src) {
        alert('Silakan pilih foto terlebih dahulu.');
        return;
    }

    currentCropFileName = rawOriginalFile ? rawOriginalFile.name : 'foto-sampul.jpg';

    const modal = document.getElementById('imageCropModal');
    
    // 1. Destroy any existing Cropper instance safely
    if (cropperInstance) {
        try {
            cropperInstance.destroy();
        } catch (e) {}
        cropperInstance = null;
    }

    // 2. Re-create the <img> tag inside container to eliminate duplicate wrappers completely
    const container = document.getElementById('cropperImageContainer');
    if (container) {
        container.innerHTML = '<img id="cropperImageTarget" src="" alt="Edit Foto" class="max-w-full max-h-[440px] block select-none">';
    }
    const targetImg = document.getElementById('cropperImageTarget');

    flipH = false;
    flipV = false;

    modal.classList.remove('hidden');

    if (src.startsWith('http') && !src.includes(window.location.host)) {
        targetImg.crossOrigin = 'anonymous';
    }

    // 3. Single-execution flag to prevent double execution on cached images
    let initialized = false;
    function startCropper() {
        if (initialized) return;
        initialized = true;
        targetImg.onload = null;
        targetImg.onerror = null;

        cropperInstance = new Cropper(targetImg, {
            aspectRatio: 16 / 10, // Fixed 16:10 directly to match web card size
            viewMode: 0, // viewMode 0 mencegah bug auto-zoom saat rotasi gambar!
            dragMode: 'move', // Menggeser foto dengan mudah
            autoCropArea: 0.92,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            zoomOnWheel: false, // Menghilangkan zoom tak sengaja saat scrolling
            responsive: true,
            ready: function () {
                if (savedCropData) {
                    try {
                        cropperInstance.setData(savedCropData);
                    } catch (e) {}
                }
            }
        });
    }

    targetImg.onload = startCropper;
    targetImg.onerror = function () {
        console.error('Gagal memuat gambar:', src);
        alert('Gagal memuat gambar. Silakan coba pilih kembali file foto Anda.');
        closeImageCropperModal();
    };
    targetImg.src = src;

    if (targetImg.complete && targetImg.naturalWidth > 0) {
        startCropper();
    }
}

function closeImageCropperModal() {
    const modal = document.getElementById('imageCropModal');
    modal.classList.add('hidden');
    if (cropperInstance) {
        try {
            cropperInstance.destroy();
        } catch (e) {}
        cropperInstance = null;
    }
    const container = document.getElementById('cropperImageContainer');
    if (container) {
        container.innerHTML = '<img id="cropperImageTarget" src="" alt="Edit Foto" class="max-w-full max-h-[440px] block select-none">';
    }
}

function cropperRotate(degrees) {
    if (!cropperInstance) return;
    cropperInstance.rotate(degrees);

    // Pastikan cropbox tetap proporsional tanpa memaksa canvas melakukan zooming
    const canvasData = cropperInstance.getCanvasData();
    const cropBoxData = cropperInstance.getCropBoxData();
    if (cropBoxData.width > canvasData.width || cropBoxData.height > canvasData.height) {
        const targetWidth = Math.min(canvasData.width * 0.9, canvasData.height * 0.9 * (16 / 10));
        const targetHeight = targetWidth / (16 / 10);
        cropperInstance.setCropBoxData({
            width: targetWidth,
            height: targetHeight,
            left: canvasData.left + (canvasData.width - targetWidth) / 2,
            top: canvasData.top + (canvasData.height - targetHeight) / 2,
        });
    }
}

function cropperFlipH() {
    if (cropperInstance) {
        flipH = !flipH;
        cropperInstance.scaleX(flipH ? -1 : 1);
    }
}

function cropperZoom(val) {
    if (cropperInstance) {
        cropperInstance.zoom(val);
    }
}

function cropperReset() {
    if (cropperInstance) {
        cropperInstance.reset();
        flipH = false;
        flipV = false;
        savedCropData = null;
    }
}

function applyImageCrop() {
    if (!cropperInstance) return;

    // Simpan data koordinat frame agar saat dibuka kembali, posisinya persis seperti sebelumnya
    savedCropData = cropperInstance.getData();

    const canvas = cropperInstance.getCroppedCanvas({
        width: 1280,
        height: 800, // Exact 16:10 high quality web card
        fillColor: '#FFFFFF',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    if (!canvas) {
        alert('Gagal memproses frame gambar.');
        return;
    }

    canvas.toBlob(function (blob) {
        if (!blob) {
            alert('Gagal memproses gambar.');
            return;
        }

        let baseName = (rawOriginalFile ? rawOriginalFile.name : currentCropFileName).replace(/\.[^/.]+$/, "");
        let safeName = baseName + '-framed.jpg';
        const croppedFile = new File([blob], safeName, { type: 'image/jpeg' });

        try {
            const dt = new DataTransfer();
            dt.items.add(croppedFile);
            document.getElementById('thumbnailInput').files = dt.files;
        } catch (err) {
            console.warn('DataTransfer notice:', err);
        }

        // Pastikan file asli tetap ada di originalThumbnailInput
        if (rawOriginalFile) {
            try {
                const dtOrig = new DataTransfer();
                dtOrig.items.add(rawOriginalFile);
                document.getElementById('originalThumbnailInput').files = dtOrig.files;
            } catch (e) {}
        }

        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        selectedFileBlob = dataUrl;
        
        const previewImg = document.getElementById('dropzonePreviewImg');
        if (previewImg) previewImg.src = dataUrl;
        
        const fileNameEl = document.getElementById('dropzoneFileName');
        if (fileNameEl) fileNameEl.innerText = baseName;

        document.getElementById('dropzonePrompt').classList.add('hidden');
        document.getElementById('dropzonePreviewWrap').classList.remove('hidden');

        isFormDirty = true;
        closeImageCropperModal();
    }, 'image/jpeg', 0.92);
}

// Form Submission
function submitArticleForm() {
    syncQuillToTextarea();

    const title = document.getElementById('titleInput').value.trim();
    if (!title) {
        alert('Silakan isi Judul Artikel terlebih dahulu.');
        document.getElementById('titleInput').focus();
        return;
    }

    const category = document.getElementById('categorySelect').value;
    if (!category) {
        alert('Silakan pilih Kategori Artikel.');
        document.getElementById('categorySelect').focus();
        return;
    }

    const content = document.getElementById('rawContentTextarea').value.trim();
    if (!content) {
        alert('Silakan tulis Konten Lengkap artikel di editor sebelum menyimpan.');
        quill.focus();
        return;
    }

    isSubmitting = true;
    isFormDirty = false;
    document.getElementById('createArticleForm').submit();
}

// Public Preview Modal
function openPublicPreviewModal() {
    syncQuillToTextarea();

    const title = document.getElementById('titleInput').value.trim() || 'Judul Artikel Anda';
    const categoryEl = document.getElementById('categorySelect');
    const categoryName = categoryEl.options[categoryEl.selectedIndex]?.text?.trim() || 'Informasi';
    const content = quill.root.innerHTML;
    const excerpt = (document.getElementById('excerptInput').value || '').trim() || 'Ringkasan singkat belum diisi...';

    // Fill Card Preview
    document.getElementById('prevCardTitle').innerText = title;
    document.getElementById('prevCardCategory').innerText = categoryName === 'Pilih Kategori...' ? 'Informasi' : categoryName;
    document.getElementById('prevCardExcerpt').innerText = excerpt;

    // Fill Full Page Preview
    document.getElementById('prevFullTitle').innerText = title;
    document.getElementById('prevFullCategory').innerText = categoryName === 'Pilih Kategori...' ? 'Informasi' : categoryName;
    document.getElementById('prevFullExcerpt').innerText = excerpt;
    document.getElementById('prevFullContent').innerHTML = content || '<p class="text-bluedark/40 italic">Konten artikel masih kosong.</p>';

    // Image preview
    const defaultPlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='250' viewBox='0 0 400 250'%3E%3Crect width='400' height='250' fill='%23E3F2FD'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%231565C0'%3EFoto Sampul Belum Dipilih%3C/text%3E%3C/svg%3E";
    const imgSrc = selectedFileBlob || defaultPlaceholder;

    document.getElementById('prevCardImg').src = imgSrc;
    document.getElementById('prevFullImg').src = imgSrc;

    document.getElementById('previewModal').classList.remove('hidden');
}

function closePublicPreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
}

function switchPreviewTab(tab) {
    const cardTab = document.getElementById('previewCardTab');
    const fullTab = document.getElementById('previewFullTab');
    const btnCard = document.getElementById('tabBtnCard');
    const btnFull = document.getElementById('tabBtnFull');

    if (tab === 'card') {
        cardTab.classList.remove('hidden');
        fullTab.classList.add('hidden');
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all';
        btnFull.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    } else {
        cardTab.classList.add('hidden');
        fullTab.classList.remove('hidden');
        btnFull.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-blueprim text-white transition-all';
        btnCard.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-bluedark/70 hover:text-bluedark transition-all';
    }
}
</script>
@endpush
