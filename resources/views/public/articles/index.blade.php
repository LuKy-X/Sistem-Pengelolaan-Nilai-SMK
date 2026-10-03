@extends('layouts.public')

@section('title', 'Berita & Artikel')
@section('meta_description', 'Kabar terbaru, kegiatan, dan pengumuman dari '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Informasi"
        title="Berita & Artikel"
        description="Kabar kegiatan sekolah, prestasi siswa, dan pengumuman resmi dari {{ $schoolName }}."
        :breadcrumb="['Beranda' => route('public.home'), 'Berita' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @php
                $categoryFilters = $categories->map(fn ($category) => [
                    'value' => $category->slug,
                    'label' => $category->name,
                ])->all();
            @endphp

            <div class="mb-8 flex justify-end">
                <x-public.search-form
                    :action="route('public.articles.index')"
                    id="article-search"
                    :search-value="$search"
                    placeholder="Cari berita..."
                    filter-name="category"
                    filter-label="Semua kategori"
                    :filter-value="$selectedCategory"
                    :filters="$categoryFilters" />
            </div>

            @if ($articles->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="news"
                        :title="$search !== '' || $selectedCategory !== '' ? 'Berita tidak ditemukan' : 'Belum ada artikel'"
                        :description="$search !== '' || $selectedCategory !== '' ? 'Coba ubah kata kunci atau kategori filter.' : 'Belum ada berita yang dipublikasikan. Silakan kembali lagi nanti.'" />
                </div>
            @else
                <div id="publicArticles" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 stagger-group">
                    @foreach ($articles as $article)
                        <x-public.article-card :article="$article" class="stagger-item" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </section>

    <x-public.cta-band
        title="Ingin tahu lebih lanjut tentang sekolah kami?"
        description="Ikuti kabar terbaru, jadwal PPDB, dan kegiatan siswa langsung dari sekolah."
        action-label="Lihat Program PPDB"
        :action-url="route('public.ppdb.index')"
        secondary-label="Kunjungi halaman profil"
        :secondary-url="route('public.profile')" />
@endsection
