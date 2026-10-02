@extends('layouts.admin')

@section('title', 'Edit Artikel - ' . $article->title)

@push('styles')
<style>
/* ==========================================================================
   WYSIWYG & FORM STYLING (MICROSOFT WORD STYLE)
   ========================================================================== */

/* Editor Container */
.word-editor-box {
    border: 1.5px solid #CFD8DC;
    border-radius: 0.75rem;
    background: #FFFFFF;
    box-shadow: 0 4px 16px -4px rgba(13, 71, 161, 0.06);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.word-editor-box:focus-within {
    border-color: #1565C0;
    box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.15);
}

/* Word-Style Ribbon Toolbar */
.word-toolbar {
    background: #F8FAFC;
    border-bottom: 1.5px solid #E2E8F0;
    padding: 0.5rem 0.75rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem 0.5rem;
    user-select: none;
}
.word-toolbar-group {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 2px 4px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 0.4rem;
}
.word-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    border: 1px solid transparent;
    background: transparent;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.word-btn:hover {
    background: #EEF2F6;
    color: #0F172A;
}
.word-btn.active {
    background: #E3F2FD;
    color: #1565C0;
    border-color: #90CAF9;
}
.word-btn:active {
    transform: scale(0.95);
}
.word-select {
    height: 28px;
    padding: 0 0.5rem;
    font-size: 12px;
    font-weight: 500;
    color: #334155;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 0.25rem;
    outline: none;
    cursor: pointer;
}
.word-select:focus {
    border-color: #1565C0;
}
.word-divider {
    width: 1px;
    height: 22px;
    background: #CBD5E1;
    margin: 0 2px;
}

/* Color picker wrapper */
.word-color-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.word-color-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    inset: 0;
}

/* Document Canvas / Contenteditable */
.word-canvas {
    min-height: 420px;
    max-height: 720px;
    overflow-y: auto;
    padding: 1.5rem 1.75rem;
    outline: none;
    font-size: 15px;
    line-height: 1.75;
    color: #1E293B;
    background: #FFFFFF;
}
.word-canvas[contenteditable="true"]:empty:before {
    content: attr(data-placeholder);
    color: #94A3B8;
    pointer-events: none;
    display: block;
}
/* Formatting inside canvas matching public .prose-article */
.word-canvas h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0D2A4A;
    margin: 1.2rem 0 0.6rem;
    line-height: 1.25;
}
.word-canvas h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0D2A4A;
    margin: 1.1rem 0 0.5rem;
    line-height: 1.3;
}
.word-canvas h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: #0D2A4A;
    margin: 0.9rem 0 0.4rem;
}
.word-canvas p {
    margin: 0 0 0.85rem;
}
.word-canvas ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}
.word-canvas ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}
.word-canvas li {
    margin-bottom: 0.25rem;
}
.word-canvas blockquote {
    border-left: 3.5px solid #2196F3;
    background: #F8FAFC;
    padding: 0.5rem 1rem;
    margin: 0.85rem 0;
    color: #334155;
    font-style: italic;
    border-radius: 0 0.35rem 0.35rem 0;
}
.word-canvas hr {
    border: none;
    border-top: 1.5px solid #E2E8F0;
    margin: 1.25rem 0;
}
.word-canvas a {
    color: #1565C0;
    text-decoration: underline;
    text-underline-offset: 3px;
}

/* Word Statusbar */
.word-statusbar {
    padding: 0.4rem 1rem;
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: #64748B;
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
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-bluelight/60">
        <div>
            <div class="flex items-center gap-2 text-xs text-bluedark/60 mb-1">
                <a href="{{ route('admin.cms.articles') }}" class="hover:text-blueprim transition-colors">Manajemen Berita &amp; Artikel</a>
                <span>/</span>
                <span class="text-blueprim font-semibold">Edit Artikel</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold text-bluedark flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blueprim/10 text-blueprim grid place-items-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </span>
                <span>Edit Artikel</span>
            </h1>
            <p class="text-xs sm:text-sm text-bluedark/70 mt-0.5">
                Perbarui konten artikel. Slug akan diperbarui otomatis jika judul diubah, dan ringkasan diperbarui dari konten lengkap.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.cms.articles') }}" class="btn btn-outline btn-sm text-xs py-1.5 px-3 flex items-center gap-1.5 text-bluedark/70 hover:text-bluedark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Batal &amp; Kembali</span>
            </a>
            <button type="button" onclick="openPublicPreviewModal()" class="btn btn-outline btn-sm text-xs py-1.5 px-3 border-blueprim/40 text-blueprim hover:bg-blue-50 flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Pratinjau Publik</span>
            </button>
            <button type="button" onclick="submitArticleForm()" class="btn btn-primary btn-sm text-xs py-1.5 px-4 font-semibold shadow-xs hover:shadow-md flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>Simpan Perubahan</span>
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
    <form id="editArticleForm" method="POST" action="{{ route('admin.cms.articles.update', $article) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Hidden input for removing existing thumbnail -->
        <input type="hidden" name="remove_thumbnail" id="removeThumbnailInput" value="0">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Kolom Kiri: Judul & Word-Like WYSIWYG Editor (8 Kolom) -->
            <div class="lg:col-span-8 space-y-5">
                
                <!-- Judul Artikel -->
                <div class="panel p-5 space-y-3">
                    <div>
                        <label for="titleInput" class="block text-xs font-bold text-bluedark uppercase tracking-wider mb-1">
                            Judul Artikel <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="titleInput" name="title" value="{{ old('title', $article->title) }}" required
                            placeholder="Judul artikel berita..."
                            class="w-full px-3.5 py-2.5 text-base sm:text-lg font-semibold text-bluedark border border-bluelight rounded-xl focus:border-blueprim focus:ring-2 focus:ring-blueprim/20 outline-none transition-all"
                            oninput="updateSlugPreview(this.value)">
                    </div>

                    <!-- Otomatisasi Slug Info -->
                    <div class="flex items-center justify-between gap-2 text-xs text-bluedark/60 bg-blue-50/60 px-3 py-2 rounded-lg border border-blue-100">
                        <div class="flex items-center gap-2 truncate">
                            <svg class="text-blueprim shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span class="truncate">
                                Tautan Publik (Otomatis): <strong class="text-blueprim font-mono" id="slugPreviewText">/berita/{{ $article->slug }}</strong>
                            </span>
                        </div>
                        @if($article->status === \App\Enums\ContentStatus::Published)
                            <a href="{{ route('public.articles.show', $article) }}" target="_blank" class="text-blueprim hover:underline shrink-0 font-medium inline-flex items-center gap-1">
                                <span>Lihat di Web</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Konten Lengkap (Microsoft Word-Style WYSIWYG Editor) -->
                <div class="panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-bluedark uppercase tracking-wider">
                            Konten Lengkap Artikel (Editor Word) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-bluedark/50">Dukung format tebal, miring, judul, daftar, kutipan, dan warna</span>
                    </div>

                    <!-- Hidden input to submit the actual HTML -->
                    <textarea name="content" id="rawContentTextarea" class="hidden" required>{{ old('content', $article->content) }}</textarea>

                    <!-- The Word Editor Box -->
                    <div class="word-editor-box">
                        <!-- Word Ribbon Toolbar -->
                        <div class="word-toolbar" role="toolbar" aria-label="Editor Formatting Options">
                            
                            <!-- Undo / Redo -->
                            <div class="word-toolbar-group">
                                <button type="button" class="word-btn" onclick="execCmd('undo')" title="Undo (Ctrl+Z)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('redo')" title="Redo (Ctrl+Y)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"/></svg>
                                </button>
                            </div>

                            <!-- Format Block (Heading / Paragraph) -->
                            <div class="word-toolbar-group">
                                <select class="word-select" onchange="execFormatBlock(this.value); this.selectedIndex=0;" title="Gaya Teks (Heading)">
                                    <option value="" disabled selected>Gaya Teks...</option>
                                    <option value="p">Normal (Paragraf)</option>
                                    <option value="h1">Judul Utama (H1)</option>
                                    <option value="h2">Sub Judul (H2)</option>
                                    <option value="h3">Bagian Kecil (H3)</option>
                                </select>
                            </div>

                            <!-- Text Styles: Bold, Italic, Underline, Strikethrough -->
                            <div class="word-toolbar-group">
                                <button type="button" class="word-btn" id="btnBold" onclick="execCmd('bold')" title="Tebal (Bold - Ctrl+B)">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="word-btn italic" id="btnItalic" onclick="execCmd('italic')" title="Miring (Italic - Ctrl+I)">
                                    <em>I</em>
                                </button>
                                <button type="button" class="word-btn underline" id="btnUnderline" onclick="execCmd('underline')" title="Garis Bawah (Underline - Ctrl+U)">
                                    <u>U</u>
                                </button>
                                <button type="button" class="word-btn line-through" id="btnStrike" onclick="execCmd('strikeThrough')" title="Coret (Strikethrough)">
                                    <s>S</s>
                                </button>
                            </div>

                            <!-- Text Color -->
                            <div class="word-toolbar-group">
                                <div class="word-color-wrap word-btn" title="Pilih Warna Teks">
                                    <span class="text-xs font-bold" id="textColorIndicator">A</span>
                                    <div class="w-full h-1 bg-red-600 rounded-full mt-2.5 absolute bottom-1 inset-x-1"></div>
                                    <input type="color" class="word-color-input" onchange="execTextColor(this.value)">
                                </div>
                            </div>

                            <!-- Alignment: Left, Center, Right, Justify -->
                            <div class="word-toolbar-group">
                                <button type="button" class="word-btn" onclick="execCmd('justifyLeft')" title="Rata Kiri">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('justifyCenter')" title="Rata Tengah">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="10" x2="6" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="18" y1="18" x2="6" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('justifyRight')" title="Rata Kanan">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="7" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="7" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('justifyFull')" title="Rata Kiri-Kanan (Justify)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                                </button>
                            </div>

                            <!-- Lists & Quotes -->
                            <div class="word-toolbar-group">
                                <button type="button" class="word-btn" onclick="execCmd('insertUnorderedList')" title="Daftar Simbol (Bullets)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('insertOrderedList')" title="Daftar Angka (Numbering)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="insertQuote()" title="Kutipan (Blockquote)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('insertHorizontalRule')" title="Garis Pemisah (Horizontal Line)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>
                                </button>
                            </div>

                            <!-- Links & Clean -->
                            <div class="word-toolbar-group">
                                <button type="button" class="word-btn" onclick="insertLinkPrompt()" title="Sisipkan Tautan (Link)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('unlink')" title="Hapus Tautan">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.84 12.25l1.72-1.71a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M5.16 11.75l-1.72 1.71a5 5 0 0 0 7.07 7.07l1.72-1.71"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                </button>
                                <button type="button" class="word-btn" onclick="execCmd('removeFormat')" title="Hapus Pemformatan (Clear Formatting)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                        </div>

                        <!-- Editable Canvas Area -->
                        <div id="wordCanvas"
                             class="word-canvas"
                             contenteditable="true"
                             data-placeholder="Ketik isi artikel Anda di sini seperti di Microsoft Word..."
                             oninput="syncCanvasToTextarea()"
                             onblur="syncCanvasToTextarea()"></div>

                        <!-- Status bar -->
                        <div class="word-statusbar">
                            <span id="canvasStatsText">0 kata | 0 karakter</span>
                            <span class="text-emerald-600 flex items-center gap-1 font-medium">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                Terhubung langsung
                            </span>
                        </div>
                    </div>

                    <!-- Otomatisasi Ringkasan Info -->
                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50/70 border border-amber-200/70 text-amber-900 text-xs leading-relaxed">
                        <div class="p-1 rounded-md bg-amber-100 text-amber-700 shrink-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        </div>
                        <div>
                            <span class="font-bold">Ringkasan Otomatis:</span>
                            Ringkasan singkat diekstrak secara otomatis (~160 karakter pertama dari konten) untuk cuplikan berita di halaman depan website sekolah tanpa perlu diketik manual.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: Pengaturan Penerbitan, Kategori, Foto Sampul, Info Penulis (4 Kolom) -->
            <div class="lg:col-span-4 space-y-5">

                <!-- Panel Penerbitan (Status Terbit / Draf) -->
                <div class="panel p-5 space-y-4">
                    <h3 class="text-xs font-bold text-bluedark uppercase tracking-wider border-b border-bluelight/60 pb-2">
                        Status Penerbitan
                    </h3>

                    @php
                        $isPub = old('status', $article->status->value) === 'PUBLISHED';
                    @endphp

                    <div class="space-y-2.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight hover:border-emerald-300 hover:bg-emerald-50/30 cursor-pointer transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                            <input type="radio" name="status" value="PUBLISHED" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" {{ $isPub ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-sm text-emerald-800 flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 shrink-0">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <span>Terbitkan (Online)</span>
                                </div>
                                <div class="text-[11px] text-bluedark/60 mt-0.5">
                                    Artikel aktif dan dapat diakses pembaca di website portal publik.
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-bluelight hover:border-slate-300 hover:bg-slate-50 cursor-pointer transition-colors has-[:checked]:border-slate-500 has-[:checked]:bg-slate-50">
                            <input type="radio" name="status" value="DRAFT" class="mt-0.5 text-slate-600 focus:ring-slate-500" {{ ! $isPub ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 shrink-0">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Simpan Sebagai Draf (Offline)</span>
                                </div>
                                <div class="text-[11px] text-bluedark/60 mt-0.5">
                                    Artikel disimpan sebagai draf, tersembunyi dari website publik.
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Metadata Info (Waktu Terbit & Views) -->
                    <div class="pt-3 border-t border-bluelight/60 space-y-2.5 text-xs text-bluedark/70">
                        <div class="flex items-center justify-between">
                            <span class="text-bluedark/50">Waktu Terbit:</span>
                            <span class="font-mono font-semibold text-bluedark">
                                {{ $article->published_at ? $article->published_at->translatedFormat('d M Y, H:i') : 'Saat pertama kali terbit' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-bluedark/50">Total Pembaca:</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono font-bold text-bluedark">
                                {{ number_format($article->views) }} views
                            </span>
                        </div>
                    </div>

                    <!-- Info Penulis Otomatis -->
                    <div class="pt-3 border-t border-bluelight/60 space-y-2">
                        <div class="text-[11px] font-semibold text-bluedark/60 uppercase tracking-wider">Penulis Artikel</div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-blueprim text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($article->author?->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-bold text-bluedark truncate">{{ $article->author?->name ?? 'Admin Sekolah' }}</div>
                                <div class="text-[10px] text-bluedark/50">{{ $article->author?->email ?? 'Akun Terdaftar' }}</div>
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
                        <option value="" disabled>Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)old('category_id', $article->category_id) === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Panel Foto Sampul (Drag & Drop Zone + Preview) -->
                @php
                    $existingMediaUrl = app(\App\Services\PublicMediaService::class)->forModel($article, 'thumbnail');
                @endphp
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
                        
                        <!-- Empty placeholder state -->
                        <div id="dropzonePrompt" class="{{ $existingMediaUrl ? 'hidden' : '' }} space-y-2 py-3">
                            <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-blueprim flex items-center justify-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <div class="text-xs font-bold text-bluedark">Tarik &amp; Lepaskan foto ke sini</div>
                            <div class="text-[11px] text-bluedark/50">atau klik untuk memilih foto baru dari komputer (JPG, PNG, WEBP)</div>
                        </div>

                        <!-- Active Preview State -->
                        <div id="dropzonePreviewWrap" class="{{ $existingMediaUrl ? '' : 'hidden' }} space-y-3">
                            <div class="relative w-full aspect-video rounded-lg overflow-hidden border border-bluelight bg-slate-100 shadow-inner">
                                <img id="dropzonePreviewImg" src="{{ $existingMediaUrl ?? '' }}" alt="Pratinjau Foto" class="w-full h-full object-cover">
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span id="dropzoneFileName" class="text-[11px] text-bluedark/70 truncate max-w-[170px] font-mono">
                                    {{ $article->thumbnail ? 'Foto Tersimpan' : '' }}
                                </span>
                                <button type="button" onclick="event.stopPropagation(); removeSelectedFile();" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold flex items-center gap-1">
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
                        <span>Perbarui Artikel</span>
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
                <div class="text-xs text-center text-bluedark/50 mb-3">Arahkan kursor / tahan kartu di bawah untuk merasakan animasi public card (zoom gambar):</div>
                
                <div class="berita-card cursor-pointer">
                    <div class="card-thumb-wrap bg-blue-50">
                        <img id="prevCardImg" src="{{ $existingMediaUrl ?? '' }}" alt="Sampul">
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between text-[11px] text-bluedark/60">
                            <span class="badge badge-blue text-[10px] px-2.5 py-0.5 rounded-full font-bold" id="prevCardCategory">
                                {{ $article->category?->name ?? 'Informasi' }}
                            </span>
                            <span id="prevCardDate">{{ $article->published_at ? $article->published_at->translatedFormat('d M Y') : now()->translatedFormat('d M Y') }}</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-bluedark line-clamp-2 leading-snug" id="prevCardTitle">
                            {{ $article->title }}
                        </h4>
                        <p class="text-xs text-bluedark/70 line-clamp-3 leading-relaxed" id="prevCardExcerpt">
                            {{ $article->excerpt }}
                        </p>
                        <div class="pt-2 border-t border-bluelight flex items-center justify-between text-xs text-bluedark/60">
                            <span class="flex items-center gap-1.5 font-medium">
                                <span class="w-5 h-5 rounded-full bg-blueprim text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($article->author?->name ?? 'A', 0, 1)) }}
                                </span>
                                <span>{{ $article->author?->name ?? 'Admin' }}</span>
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
                    <span class="badge badge-blue text-xs px-3 py-1 rounded-full font-bold" id="prevFullCategory">
                        {{ $article->category?->name ?? 'Informasi' }}
                    </span>
                    <span>&bull;</span>
                    <span id="prevFullDate">{{ $article->published_at ? $article->published_at->translatedFormat('d M Y, H:i') : now()->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-heading font-bold text-bluedark leading-tight mb-4" id="prevFullTitle">
                    {{ $article->title }}
                </h1>

                <div class="flex items-center gap-3 pb-6 mb-6 border-b border-bluelight text-xs text-bluedark/70">
                    <div class="w-9 h-9 rounded-full bg-blueprim text-white font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($article->author?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-bluedark">{{ $article->author?->name ?? 'Admin Sekolah' }}</div>
                        <div class="text-[11px] text-bluedark/50">Penulis Berita &amp; Humas SMKN 2 Karanganyar</div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="rounded-2xl overflow-hidden border border-bluelight mb-6 bg-slate-100 aspect-video">
                    <img id="prevFullImg" src="{{ $existingMediaUrl ?? '' }}" alt="Sampul Artikel" class="w-full h-full object-cover">
                </div>

                <!-- Excerpt Block -->
                <div class="p-4 rounded-xl bg-blue-50/60 border-l-4 border-blueprim text-bluedark/80 text-sm font-medium leading-relaxed mb-6" id="prevFullExcerpt">
                    {{ $article->excerpt }}
                </div>

                <!-- Formatted WYSIWYG Content Output -->
                <div class="word-canvas p-0 leading-relaxed text-bluedark/85" id="prevFullContent">
                    {!! $article->content !!}
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
@endsection

@push('scripts')
<script>
/* ==========================================================================
   WYSIWYG ENGINE (NATIVE DOCUMENT.EXECCOMMAND & RANGE API)
   ========================================================================== */

let selectedFileBlob = null;
const existingMediaUrl = @json($existingMediaUrl);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    const rawVal = document.getElementById('rawContentTextarea').value;
    const canvas = document.getElementById('wordCanvas');
    if (rawVal && rawVal.trim() !== '') {
        canvas.innerHTML = rawVal;
    }
    syncCanvasToTextarea();
    setupDropzone();
    updateSlugPreview(document.getElementById('titleInput').value);
});

// Execute format command
function execCmd(command, value = null) {
    document.getElementById('wordCanvas').focus();
    document.execCommand(command, false, value);
    syncCanvasToTextarea();
    updateToolbarState();
}

// Format block (H1, H2, H3, P)
function execFormatBlock(tag) {
    if (!tag) return;
    document.getElementById('wordCanvas').focus();
    document.execCommand('formatBlock', false, '<' + tag + '>');
    syncCanvasToTextarea();
}

// Change text color
function execTextColor(color) {
    if (!color) return;
    document.getElementById('wordCanvas').focus();
    document.execCommand('foreColor', false, color);
    document.getElementById('textColorIndicator').style.color = color;
    syncCanvasToTextarea();
}

// Insert Blockquote
function insertQuote() {
    document.getElementById('wordCanvas').focus();
    const sel = window.getSelection();
    if (!sel.rangeCount) return;
    
    // Check if current selection is already inside a blockquote
    let node = sel.anchorNode;
    while (node && node.id !== 'wordCanvas') {
        if (node.nodeName === 'BLOCKQUOTE') {
            document.execCommand('formatBlock', false, '<p>');
            syncCanvasToTextarea();
            return;
        }
        node = node.parentNode;
    }
    document.execCommand('formatBlock', false, '<blockquote>');
    syncCanvasToTextarea();
}

// Insert link with prompt
function insertLinkPrompt() {
    const url = prompt('Masukkan tautan URL (contoh: https://smkn2-kra.sch.id):');
    if (url && url.trim() !== '') {
        let validUrl = url.trim();
        if (!validUrl.startsWith('http://') && !validUrl.startsWith('https://')) {
            validUrl = 'https://' + validUrl;
        }
        execCmd('createLink', validUrl);
    }
}

// Sync canvas content with hidden form textarea
function syncCanvasToTextarea() {
    const canvas = document.getElementById('wordCanvas');
    const textarea = document.getElementById('rawContentTextarea');
    const html = canvas.innerHTML;
    
    // Clean empty placeholder tags
    const cleanHtml = (canvas.innerText.trim() === '' && !html.includes('<img')) ? '' : html;
    textarea.value = cleanHtml;

    // Word and character stats
    const text = canvas.innerText || '';
    const cleanText = text.trim();
    const wordCount = cleanText ? cleanText.split(/\s+/).length : 0;
    const charCount = cleanText.length;
    document.getElementById('canvasStatsText').innerText = `${wordCount} kata | ${charCount} karakter`;
}

// Listen to selection changes to update button active states
document.addEventListener('selectionchange', function () {
    const canvas = document.getElementById('wordCanvas');
    if (!canvas) return;
    const sel = window.getSelection();
    if (!sel || !sel.anchorNode || !canvas.contains(sel.anchorNode)) return;

    toggleBtnActive('btnBold', document.queryCommandState('bold'));
    toggleBtnActive('btnItalic', document.queryCommandState('italic'));
    toggleBtnActive('btnUnderline', document.queryCommandState('underline'));
    toggleBtnActive('btnStrike', document.queryCommandState('strikeThrough'));
});

function toggleBtnActive(btnId, isActive) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (isActive) {
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }
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

/* ==========================================================================
   DRAG AND DROP FILE UPLOAD
   ========================================================================== */
function setupDropzone() {
    const area = document.getElementById('dropzoneArea');
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
            document.getElementById('thumbnailInput').files = files;
            handleFileSelected(files);
        }
    }, false);
}

function handleFileSelected(files) {
    if (!files || !files.length) return;
    const file = files[0];

    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar (JPG, PNG, WEBP, GIF).');
        return;
    }

    if (file.size > 4 * 1024 * 1024) {
        alert('Ukuran file maksimal adalah 4MB.');
        return;
    }

    // Reset remove thumbnail flag
    document.getElementById('removeThumbnailInput').value = '0';

    const reader = new FileReader();
    reader.onload = function (e) {
        selectedFileBlob = e.target.result;
        document.getElementById('dropzonePreviewImg').src = selectedFileBlob;
        document.getElementById('dropzoneFileName').innerText = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        document.getElementById('dropzonePrompt').classList.add('hidden');
        document.getElementById('dropzonePreviewWrap').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function removeSelectedFile() {
    document.getElementById('thumbnailInput').value = '';
    selectedFileBlob = null;
    document.getElementById('removeThumbnailInput').value = '1';
    document.getElementById('dropzonePreviewImg').src = '';
    document.getElementById('dropzonePreviewWrap').classList.add('hidden');
    document.getElementById('dropzonePrompt').classList.remove('hidden');
}

/* ==========================================================================
   FORM SUBMISSION
   ========================================================================== */
function submitArticleForm() {
    syncCanvasToTextarea();

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
        document.getElementById('wordCanvas').focus();
        return;
    }

    document.getElementById('editArticleForm').submit();
}

/* ==========================================================================
   PUBLIC PREVIEW MODAL
   ========================================================================== */
function openPublicPreviewModal() {
    syncCanvasToTextarea();

    const title = document.getElementById('titleInput').value.trim() || 'Judul Artikel Anda';
    const categoryEl = document.getElementById('categorySelect');
    const categoryName = categoryEl.options[categoryEl.selectedIndex]?.text?.trim() || 'Informasi';
    const content = document.getElementById('wordCanvas').innerHTML;
    const plainText = (document.getElementById('wordCanvas').innerText || '').trim();
    const excerpt = plainText.length > 160 ? plainText.substring(0, 160) + '...' : (plainText || 'Ringkasan singkat akan muncul di sini...');

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
    const isRemoved = document.getElementById('removeThumbnailInput').value === '1';
    let imgSrc = defaultPlaceholder;
    if (selectedFileBlob) {
        imgSrc = selectedFileBlob;
    } else if (!isRemoved && existingMediaUrl) {
        imgSrc = existingMediaUrl;
    }

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
