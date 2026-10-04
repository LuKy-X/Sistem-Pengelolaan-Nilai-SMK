@extends('layouts.public')

@section('title', 'Produk Siswa')
@section('meta_description', 'Karya dan produk kreatif siswa '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Inovasi Siswa"
        title="Produk Unggulan"
        description="Karya siswa yang dibuat melalui pembelajaran berbasis proyek dan siap dibeli."
        :breadcrumb="['Beranda' => route('public.home'), 'Produk Siswa' => null]" />

    <section class="pt-8 sm:pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @php
                $categoryFilters = $categories->map(fn ($category) => [
                    'value' => $category->id,
                    'label' => $category->name,
                ])->all();
            @endphp

            <div class="mb-8 flex justify-end">
                <x-public.search-form
                    :action="route('public.products.index')"
                    id="product-search"
                    :search-value="$search"
                    placeholder="Cari produk..."
                    filter-name="category"
                    filter-label="Semua kategori"
                    :filter-value="$selectedCategory"
                    :filters="$categoryFilters" />
            </div>

            @if ($products->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="box"
                        :title="$search !== '' || $selectedCategory !== '' ? 'Produk tidak ditemukan' : 'Belum ada produk'"
                        :description="$search !== '' || $selectedCategory !== '' ? 'Coba ubah kata kunci atau kategori filter.' : 'Produk siswa belum dipublikasikan. Silakan kembali lagi nanti.'" />
                </div>
            @else
                <div id="publicProducts" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 stagger-group">
                    @foreach ($products as $product)
                        <x-public.product-card :product="$product" heading-level="h2"
                            data-category="{{ $product->category_id ?? 'uncategorized' }}" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>

    @php
        $productContact = $products->getCollection()->first(fn ($product) => filled($product->contact))?->contact;
        $whatsappNumber = preg_replace('/\D+/', '', (string) $productContact);
        if (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '62'.substr($whatsappNumber, 1);
        }
        $productInquiryMessage = rawurlencode('Halo, saya tertarik memesan produk siswa. Mohon informasi produk yang tersedia dan cara pemesanannya. Terima kasih.');
    @endphp

    <x-public.cta-band
        title="Butuh Produk atau Jasa Custom?"
        description="Pemesanan dilakukan langsung dengan siswa pembuat. Harga dapat menyesuaikan jumlah, bahan, dan finishing."
        :action-label="$whatsappNumber ? 'Pesan via WhatsApp' : 'Lihat Kontak Sekolah'"
        :action-url="$whatsappNumber ? 'https://wa.me/'.$whatsappNumber.'?text='.$productInquiryMessage : route('public.profile')"
        secondary-label="Lihat semua produk"
        :secondary-url="route('public.products.index')" />
@endsection
