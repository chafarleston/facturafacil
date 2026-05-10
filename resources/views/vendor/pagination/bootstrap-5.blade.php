@if ($paginator->hasPages())
    <nav>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div style="font-size: 12px; color: #6c757d;">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </div>
            <ul class="pagination pagination-sm mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Anterior</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Anterior</a></li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">{{ $element }}</span></li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link" style="font-size: 12px; padding: 2px 8px; background: #0066cc; border-color: #0066cc; color: #fff;">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Siguiente</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Siguiente</span></li>
                @endif
            </ul>
        </div>
    </nav>
@endif