@if ($paginator->hasPages())
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
        <span style="color: #666;">Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados</span>
        <span>
            @if ($paginator->onFirstPage())
                <span style="display: inline-block; padding: 4px 10px; background: #e9ecef; color: #999; border: 1px solid #dee2e6; cursor: not-allowed;">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="display: inline-block; padding: 4px 10px; background: #fff; color: #0066cc; border: 1px solid #0066cc; text-decoration: none;">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="padding: 4px 8px;">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="display: inline-block; padding: 4px 10px; background: #0066cc; color: #fff; border: 1px solid #0066cc; min-width: 30px; text-align: center;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="display: inline-block; padding: 4px 10px; background: #fff; color: #0066cc; border: 1px solid #dee2e6; text-decoration: none; min-width: 30px; text-align: center;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="display: inline-block; padding: 4px 10px; background: #fff; color: #0066cc; border: 1px solid #0066cc; text-decoration: none;">Siguiente</a>
            @else
                <span style="display: inline-block; padding: 4px 10px; background: #e9ecef; color: #999; border: 1px solid #dee2e6; cursor: not-allowed;">Siguiente</span>
            @endif
        </span>
    </div>
@endif