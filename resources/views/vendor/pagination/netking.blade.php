@if ($paginator->hasPages())
<nav class="nk-pager-wrap">
    <div class="nk-pager-info">
        Menampilkan <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
        dari <strong>{{ $paginator->total() }}</strong> data
    </div>
    <div class="nk-pager">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="nk-pager-nav disabled">‹ Sebelumnya</span>
        @else
            <a class="nk-pager-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Sebelumnya</a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="nk-pager-num disabled">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="nk-pager-num active">{{ $page }}</span>
                    @else
                        <a class="nk-pager-num" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="nk-pager-nav" href="{{ $paginator->nextPageUrl() }}" rel="next">Selanjutnya ›</a>
        @else
            <span class="nk-pager-nav disabled">Selanjutnya ›</span>
        @endif
    </div>
</nav>
@endif
