@extends('layouts.public')

@section('title', 'Produk Siswa')
@section('meta_description', 'Karya dan produk kreatif siswa '.$schoolName)

@section('content')
    <x-public.page-header
        eyebrow="Inovasi Siswa"
        title="Produk Unggulan"
        description="Karya siswa yang dibuat melalui pembelajaran berbasis proyek dan siap dibeli."
        :breadcrumb="['Beranda' => route('public.home'), 'Produk Siswa' => null]" />

    <section class="py-14 sm:py-16 md:py-20">
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
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach ($products as $product)
                        <article class="flex flex-col bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden h-full card-hover">
                            <div class="h-48 shrink-0">
                                <x-public.media :model="$product" :alt="$product->name" icon="box" />
                            </div>

                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                                        {{ $product->category?->name ?? 'Produk' }}
                                    </span>
                                    @if ($product->department)
                                        <span class="text-xs text-bluedark/50">{{ $product->department->short_name ?: $product->department->code }}</span>
                                    @endif
                                </div>

                                <h2 class="font-heading font-semibold text-bluedark mt-3 leading-snug">{{ $product->name }}</h2>

                                @if (filled($product->description))
                                    <p class="text-sm text-bluedark/60 mt-2.5 leading-relaxed line-clamp-3">{{ $product->description }}</p>
                                @endif

                                <div class="mt-auto pt-4">
                                    <p class="font-heading font-bold text-blueprim">
                                        {{ $product->price !== null
                                            ? 'Rp '.number_format((float) $product->price, 0, ',', '.')
                                            : 'Tanyakan ke penjual' }}
                                    </p>
                                    @if (filled($product->contact))
                                        <p class="text-xs text-bluedark/50 mt-1">{{ $product->contact }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
