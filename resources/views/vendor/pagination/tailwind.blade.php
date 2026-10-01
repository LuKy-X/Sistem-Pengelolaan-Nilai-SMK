@if ($paginator->hasPages())
    <style>
        .pag-nav, .pag-nav *, .pag-nav a, .pag-nav span, .pag-nav button {
            outline: none !important;
            border-color: transparent !important;
            -webkit-tap-highlight-color: transparent !important;
        }
        .pag-nav a:focus, .pag-nav a:focus-visible, .pag-nav a:active {
            outline: none !important;
            box-shadow: 0 4px 12px rgba(13, 71, 161, 0.12) !important;
        }
    </style>

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pag-nav flex flex-col sm:flex-row items-center justify-between gap-3 pt-3">
        
        {{-- Results Summary --}}
        <div>
            <p class="text-xs text-bluedark/60 font-medium select-none">
                <span>Menampilkan</span>
                @if ($paginator->firstItem())
                    <span class="font-bold text-bluedark">{{ $paginator->firstItem() }}</span>
                    <span>sampai</span>
                    <span class="font-bold text-bluedark">{{ $paginator->lastItem() }}</span>
                @else
                    <span class="font-bold text-bluedark">{{ $paginator->count() }}</span>
                @endif
                <span>dari total</span>
                <span class="font-bold text-bluedark">{{ $paginator->total() }}</span>
                <span>data</span>
            </p>
        </div>

        {{-- Page Navigation Links with Pure Shadow (No Outline, No Dark Borders) --}}
        <div>
            <div class="inline-flex items-center gap-1.5 bg-white p-1 rounded-2xl shadow-sm border-0 outline-none">
                
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-slate-300 bg-slate-50/80 cursor-not-allowed border-0 outline-none select-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-bluedark/70 hover:text-blueprim bg-white hover:bg-slate-50 shadow-2xs hover:shadow-xs border-0 outline-none transition-all" aria-label="{{ __('pagination.previous') }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-slate-400 border-0 outline-none select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center min-w-[34px] h-8 px-2.5 rounded-xl text-xs font-bold text-white bg-blueprim shadow-md shadow-blueprim/30 border-0 outline-none select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[34px] h-8 px-2.5 rounded-xl text-xs font-semibold text-bluedark/75 hover:text-blueprim bg-white hover:bg-slate-50 shadow-2xs hover:shadow-xs border-0 outline-none transition-all" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-bluedark/70 hover:text-blueprim bg-white hover:bg-slate-50 shadow-2xs hover:shadow-xs border-0 outline-none transition-all" aria-label="{{ __('pagination.next') }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-slate-300 bg-slate-50/80 cursor-not-allowed border-0 outline-none select-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                @endif

            </div>
        </div>

    </nav>
@endif
