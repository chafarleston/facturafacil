<style>
.pagination-custom .page-link {
    color: #0066cc;
    border-color: #dee2e6;
    background-color: #fff;
}
.pagination-custom .page-item.active .page-link {
    background-color: #0066cc !important;
    border-color: #0066cc !important;
    color: #fff !important;
}
.pagination-custom .page-link:hover {
    color: #004a99;
    background-color: #e9ecef;
    border-color: #dee2e6;
}
.pagination-custom .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}
</style>

@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted" style="font-size: 0.875rem;">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0 pagination-custom">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&laquo; Anterior</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Anterior</a>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente &raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">Siguiente &raquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif