@props([
    'paginator',
])

@if($paginator->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
        <p class="text-[11px] text-bluedark/55">
            Menampilkan {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        @if($paginator->hasPages())
            <nav class="bk-pagination flex items-center gap-1" aria-label="Navigasi halaman">
                @if($paginator->onFirstPage())
                    <span class="is-disabled" aria-hidden="true">&larr;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">&larr;</a>
                @endif

                @php($window = \Illuminate\Pagination\UrlWindow::make($paginator))
                @php($elements = array_filter([
                    $window['first'],
                    is_array($window['slider']) ? '...' : null,
                    $window['slider'],
                    is_array($window['last']) ? '...' : null,
                    $window['last'],
                ]))

                @foreach($elements as $element)
                    @if($element === '...')
                        <span class="pg-ellipsis" aria-hidden="true">&hellip;</span>
                    @elseif(is_array($element))
                        @foreach($element as $page => $url)
                            @if($page == $paginator->currentPage())
                                <span class="is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">&rarr;</a>
                @else
                    <span class="is-disabled" aria-hidden="true">&rarr;</span>
                @endif
            </nav>
        @endif
    </div>
@endif
