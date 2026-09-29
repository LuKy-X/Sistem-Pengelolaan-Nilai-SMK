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

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-8">
                    <a href="{{ route('public.products.index') }}"
                        @class([
                            'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                            'bg-bluedark text-white border-bluedark' => $selectedCategory === null,
                            'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedCategory !== null,
                        ])>
                        Semua
                    </a>

                    @foreach ($categories as $category)
                        <a href="{{ route('public.products.index', ['kategori' => $category->id]) }}"
                            @class([
                                'font-heading font-medium text-xs px-4 py-2 rounded-full border transition-colors',
                                'bg-bluedark text-white border-bluedark' => $selectedCategory === $category->id,
                                'bg-white text-bluedark/70 border-bluelight hover:border-bluesoft' => $selectedCategory !== $category->id,
                            ])>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="bg-white border border-bluelight rounded-3xl shadow-xs">
                    <x-public.empty-state
                        icon="box"
                        title="Belum ada produk"
                        description="Produk siswa belum dipublikasikan. Silakan kembali lagi nanti." />
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 stagger-group">
                    @foreach ($products as $product)
                        <x-public.product-card :product="$product" heading-level="h2" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>

    @php
        $whatsappNumber = preg_replace('/\D+/', '', (string) $schoolProfile?->phone);
    @endphp

    <x-public.cta-band
        title="Butuh Produk atau Jasa Custom?"
        description="Pemesanan dilakukan langsung dengan siswa pembuat. Harga dapat menyesuaikan jumlah, bahan, dan finishing."
        :action-label="$whatsappNumber ? 'Pesan via WhatsApp' : 'Lihat Kontak Sekolah'"
        :action-url="$whatsappNumber ? 'https://wa.me/'.$whatsappNumber : route('public.profile')"
        secondary-label="Lihat semua produk"
        :secondary-url="route('public.products.index')" />
@endsection
