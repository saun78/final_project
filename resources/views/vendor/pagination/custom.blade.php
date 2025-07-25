@if ($paginator->hasPages())
    <nav>
        <ul class="pagination" style="display:flex;justify-content:center;align-items:center;gap:0.2rem;margin:0;">
            {{-- Previous Page Link --}}
            @if ($paginator->currentPage() > 1)
                <li>
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="background:#fff;color:#5b8c87;border:1px solid #8bb3ad;min-width:28px;min-height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border-radius:4px;padding:0 6px;transition:background 0.2s, color 0.2s;">&lt;</a>
                </li>
            @endif
            {{-- Page Number Links --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page">
                                <span class="page-link" style="background:#8bb3ad;color:#fff;border:1px solid #8bb3ad;min-width:28px;min-height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border-radius:4px;padding:0 6px;">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a class="page-link" href="{{ $url }}" style="background:#fff;color:#5b8c87;border:1px solid #8bb3ad;min-width:28px;min-height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border-radius:4px;padding:0 6px;transition:background 0.2s, color 0.2s;">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="background:#fff;color:#5b8c87;border:1px solid #8bb3ad;min-width:28px;min-height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border-radius:4px;padding:0 6px;transition:background 0.2s, color 0.2s;">&gt;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true">
                    <span class="page-link" style="background:#fff;color:#b0b0b0;border:1px solid #8bb3ad;min-width:28px;min-height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border-radius:4px;padding:0 6px;">&gt;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif 