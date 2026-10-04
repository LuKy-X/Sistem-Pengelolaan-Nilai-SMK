@props([
    'product',
    'headingLevel' => 'h3',
    'showDepartment' => true,
])

<article
    {{ $attributes->merge([
        'class' => 'link-card relative stagger-item flex flex-col h-full card-hover bg-white rounded-3xl border border-bluelight shadow-card overflow-hidden',
        'data-search' => trim(implode(' ', [
            $product->name,
            $product->description,
            $product->category?->name,
            $product->department?->name,
            $product->department?->short_name,
            $product->contact,
        ])),
    ]) }}>
    <a href="{{ route('public.products.show', $product) }}"
        class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
        aria-label="Lihat detail produk {{ $product->name }}"></a>
    <div class="block h-48 shrink-0">
        <x-public.media :model="$product" :alt="$product->name" icon="box" />
    </div>

    <div class="p-5 flex flex-col flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">
                {{ $product->category?->name ?? 'Produk' }}
            </span>
            @if ($showDepartment && $product->department)
                <span class="text-xs text-bluedark/50">{{ $product->department->short_name ?: $product->department->code }}</span>
            @endif
        </div>

        <{{ $headingLevel }} class="font-heading font-semibold text-bluedark mt-3 leading-snug">
            {{ $product->name }}
        </{{ $headingLevel }}>

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
                <p class="text-xs text-bluedark/50 mt-1 line-clamp-2">{{ $product->contact }}</p>
            @endif
        </div>
    </div>
</article>
