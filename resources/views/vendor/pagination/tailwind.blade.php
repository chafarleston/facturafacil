@if ($paginator->hasPages())
    <nav>
        <div class="d-flex justify-content-between align-items-center">
            <div style="font-size: 12px; color: #666;">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </div>
            <div style="display: flex; gap: 4px; align-items: center;">
                @if ($paginator->onFirstPage())
                    <button class="btn btn-sm btn-default" disabled style="font-size: 12px; padding: 4px 10px; cursor: not-allowed;">Anterior</button>
                @else
                    <a class="btn btn-sm btn-outline-primary" href="{{ $paginator->previousPageUrl() }}" style="font-size: 12px; padding: 4px 10px; color: #0066cc; border-color: #0066cc;">Anterior</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span style="padding: 4px 8px; font-size: 12px;">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <button class="btn btn-sm btn-primary" style="font-size: 12px; padding: 4px 10px; min-width: 30px;">{{ $page }}</button>
                            @else
                                <a class="btn btn-sm btn-outline-primary" href="{{ $url }}" style="font-size: 12px; padding: 4px 10px; min-width: 30px; color: #0066cc; border-color: #0066cc;">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a class="btn btn-sm btn-outline-primary" href="{{ $paginator->nextPageUrl() }}" style="font-size: 12px; padding: 4px 10px; color: #0066cc; border-color: #0066cc;">Siguiente</a>
                @else
                    <button class="btn btn-sm btn-default" disabled style="font-size: 12px; padding: 4px 10px; cursor: not-allowed;">Siguiente</button>
                @endif
            </div>
        </div>
    </nav>
@endif