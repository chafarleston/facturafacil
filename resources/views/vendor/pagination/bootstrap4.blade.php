@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted" style="font-size: 12px;">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled"><span class="page-link py-1 px-2" style="font-size: 12px; color: #999;">Anterior</span></li>
                @else
                    <li class="page-item"><a class="page-link py-1 px-2" href="{{ $paginator->previousPageUrl() }}" style="font-size: 12px; color: #0066cc;">Anterior</a></li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link py-1 px-2" style="font-size: 12px;">{{ $element }}</span></li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link py-1 px-2" style="font-size: 12px; background: #0066cc; border-color: #0066cc; color: #fff;">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link py-1 px-2" href="{{ $url }}" style="font-size: 12px; color: #0066cc;">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link py-1 px-2" href="{{ $paginator->nextPageUrl() }}" style="font-size: 12px; color: #0066cc;">Siguiente</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link py-1 px-2" style="font-size: 12px; color: #999;">Siguiente</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif