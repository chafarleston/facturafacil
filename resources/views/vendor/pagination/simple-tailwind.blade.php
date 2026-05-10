@if ($paginator->hasPages())
    <nav>
        <ul class="pagination pagination-sm mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Anterior</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Siguiente</a></li>
            @else
                <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Siguiente</span></li>
            @endif
        </ul>
    </nav>
@endif