@extends('layouts.public')

@section('title', $product->name)
@section('meta_description', str($product->description ?? '')->limit(155)->value() ?: 'Detail produk '.$product->name)

@section('content')
    <x-public.page-header
        :eyebrow="$product->category?->name"
        :title="$product->name"
        :breadcrumb="[
            'Beranda' => route('public.home'),
            'Produk Siswa' => route('public.products.index'),
            $product->name => null,
        ]" />

    <section class="pt-8 pb-14 sm:pt-10 sm:pb-16 md:pb-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="grid md:grid-cols-2 gap-8 md:gap-12">

                {{-- Galeri --}}
                <div>
                    <div class="rounded-3xl overflow-hidden border border-bluelight h-72 sm:h-80 md:h-96">
                        <x-public.media :model="$product" :alt="$product->name" icon="box" />
                    </div>

                    @if ($product->students->isNotEmpty())
                        <p class="text-xs text-bluedark/50 mt-3">
                            Dikerjakan oleh
                            {{ $product->students->pluck('full_name')->join(', ') }}
                        </p>
                    @endif
                </div>

                {{-- Informasi & aksi --}}
                <div class="flex flex-col">
                    <span
                        class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">
                        {{ $product->category?->name ?? 'Produk' }}
                    </span>

                    <h1 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark mt-4 leading-snug">
                        {{ $product->name }}
                    </h1>

                    <div class="flex items-end gap-2 mt-4">
                        <p class="font-heading font-bold text-2xl sm:text-3xl text-bluedark">
                            {{ $product->price !== null
                                ? 'Rp '.number_format((float) $product->price, 0, ',', '.')
                                : 'Tanyakan ke penjual' }}
                        </p>
                        <p class="text-xs sm:text-sm text-bluedark/50 mb-1">harga</p>
                    </div>

                    @if (filled($product->description))
                        <p class="text-sm sm:text-base text-bluedark/60 mt-4 leading-relaxed">
                            {{ str($product->description)->limit(220) }}
                        </p>
                    @endif

                    <div class="mt-6 bg-bluelight/50 rounded-2xl p-5">
                        <p class="font-heading font-semibold text-bluedark text-sm mb-3">Detail &amp; Spesifikasi</p>
                        <ul class="space-y-2 text-sm text-bluedark/70">
                            <li class="flex items-start gap-2">
                                <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                <span>Program keahlian: {{ $product->department?->name ?? 'Umum' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                <span>Kategori: {{ $product->category?->name ?? 'Produk' }}</span>
                            </li>
                            @if (filled($product->contact))
                                <li class="flex items-start gap-2">
                                    <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                    <span>Kontak penjual: {{ $product->contact }}</span>
                                </li>
                            @endif
                            <li class="flex items-start gap-2">
                                <span class="text-blueprim mt-0.5" aria-hidden="true">&bull;</span>
                                <span>Status: {{ $product->status === 'AVAILABLE' ? 'Tersedia' : $product->status }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        @if (filled($product->contact))
                            @php
                                $orderMessage = 'Halo, saya tertarik memesan '.$product->name.'. Apakah produk ini masih tersedia? Mohon informasi lebih lanjut mengenai pemesanan. Terima kasih.';
                                $whatsappNumber = preg_replace('/\D+/', '', $product->contact);
                                if (str_starts_with($whatsappNumber, '0')) {
                                    $whatsappNumber = '62'.substr($whatsappNumber, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode($orderMessage) }}"
                                target="_blank" rel="noopener"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 font-heading font-medium text-white bg-[#25D366] hover:bg-[#1DA851] transition-colors px-6 py-3.5 rounded-full text-sm sm:w-auto">
                                Pesan via WhatsApp
                            </a>
                        @endif
                        <a href="{{ route('public.products.index') }}"
                            class="inline-flex min-h-12 w-full items-center justify-center gap-2 font-heading font-medium text-bluedark border border-bluelight hover:bg-bluelight transition-colors px-6 py-3.5 rounded-full text-sm sm:w-auto">
                            Lihat Produk Lain
                        </a>
                    </div>
                    <p class="text-xs text-bluedark/40 mt-3">
                        Harga dapat berubah sesuai spesifikasi &amp; kesepakatan. Konfirmasi akhir dilakukan lewat chat
                        WhatsApp.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pb-20 sm:pb-24 md:pb-28">
            <x-public.section-heading eyebrow="Karya Siswa & Sekolah" title="Produk Lainnya" />

            <div class="mt-6 sm:mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
                @foreach ($relatedProducts as $related)
                    <x-public.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
