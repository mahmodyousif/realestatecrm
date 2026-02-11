@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        <ul class="pagination">
            {{-- السهم السابق --}}
            @if ($paginator->onFirstPage())
                <li class="disabled">
                    <span title="السابق">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" title="السابق">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @endif

            {{-- أرقام الصفحات --}}
            @foreach ($elements as $element)
                {{-- النص الثابت مثل "..." --}}
                @if (is_string($element))
                    <li class="disabled"><span class="ellipsis">{{ $element }}</span></li>
                @endif

                {{-- أرقام الصفحات --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active">
                                <span aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" aria-label="انتقل إلى الصفحة {{ $page }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- السهم التالي --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" title="التالي">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @else
                <li class="disabled">
                    <span title="التالي">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @endif
        </ul>
    </div>
@endif
