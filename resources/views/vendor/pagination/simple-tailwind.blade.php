@if ($paginator->hasPages())
    <style>
        .pag-simple, .pag-simple *, .pag-simple a, .pag-simple span {
            outline: none !important;
            border-color: transparent !important;
            -webkit-tap-highlight-color: transparent !important;
        }
        .pag-simple a:focus, .pag-simple a:focus-visible, .pag-simple a:active {
            outline: none !important;
            box-shadow: 0 4px 12px rgba(13, 71, 161, 0.12) !important;
        }
    </style>

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pag-simple flex items-center justify-between gap-3 pt-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-300 bg-slate-50/80 rounded-xl cursor-not-allowed border-0 outline-none select-none">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span>Sebelumnya</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-bluedark/75 hover:text-blueprim bg-white hover:bg-slate-50 rounded-xl shadow-xs hover:shadow-sm border-0 outline-none transition-all">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span>Sebelumnya</span>
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-bluedark/75 hover:text-blueprim bg-white hover:bg-slate-50 rounded-xl shadow-xs hover:shadow-sm border-0 outline-none transition-all">
                <span>Selanjutnya</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-300 bg-slate-50/80 rounded-xl cursor-not-allowed border-0 outline-none select-none">
                <span>Selanjutnya</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
        @endif
    </nav>
@endif
