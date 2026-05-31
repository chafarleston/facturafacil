@extends('layouts.admin')
@section('title', 'Productos')
@section('page_title', 'Productos')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Productos</h3>
        <div class="card-tools">
          <form method="GET" action="{{ route('products.index') }}" class="form-inline">
            <input type="hidden" name="company_id" value="{{ $companyId ?? null }}">
            <select name="search_type" class="form-control form-control-sm mr-1" style="width:auto;" onchange="updateSearchPlaceholder(this)">
              <option value="descripcion" {{ request('search_type', 'descripcion') == 'descripcion' ? 'selected' : '' }}>Descripción</option>
              <option value="codigo" {{ request('search_type') == 'codigo' ? 'selected' : '' }}>Código</option>
              <option value="codigo_barras" {{ request('search_type') == 'codigo_barras' ? 'selected' : '' }}>Cód. Barras</option>
              <option value="categoria" {{ request('search_type') == 'categoria' ? 'selected' : '' }}>Categoría</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por descripción..." value="{{ request('search') }}" id="searchInput">
            <button type="submit" class="btn btn-secondary btn-sm ml-1"><i class="fas fa-search"></i></button>
            @if(request('search'))
            <a href="{{ route('products.index', ['company_id' => $companyId ?? null]) }}" class="btn btn-link btn-sm ml-1">Limpiar</a>
            @endif
          </form>
          <script>
          function updateSearchPlaceholder(sel) {
            const labels = { 'descripcion': 'Buscar por descripción...', 'codigo': 'Buscar por código...', 'codigo_barras': 'Buscar por código de barras...', 'categoria': 'Buscar por categoría...' };
            document.getElementById('searchInput').placeholder = labels[sel.value] || 'Buscar...';
          }
          </script>
          <a href="{{ route('products.create', ['company_id' => $companyId ?? null]) }}" class="btn btn-primary btn-sm ml-2">
            <i class="fas fa-plus"></i> Nuevo
          </a>
          <a href="{{ route('products.import.form', ['company_id' => $companyId ?? null]) }}" class="btn btn-success btn-sm ml-1">
            <i class="fas fa-file-import"></i> Importar
          </a>
          <a href="{{ route('products.export', ['company_id' => $companyId ?? null]) }}" class="btn btn-info btn-sm ml-1">
            <i class="fas fa-file-export"></i> Exportar
          </a>
            @php $_mainCompany = \App\Models\Company::getMainCompany(); @endphp
            @if($_mainCompany && ($_mainCompany->facturacion_mode ?? 'propio') === 'api_externa')
            <button id="syncAllPro51" class="btn btn-primary btn-sm ml-1">
              <i class="fas fa-cloud-upload-alt"></i> Sincronizar todos
            </button>
          @endif
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Código</th>
              <th>Cód. Barras</th>
              <th>Descripción</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>pro51</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $product)
            <tr>
              <td>{{ $product->codigo }}</td>
              <td>{{ $product->codigo_barras ?? '-' }}</td>
              <td>{{ $product->descripcion }}</td>
              <td>{{ $product->category->nombre ?? '-' }}</td>
              <td>S/ {{ number_format($product->precio, 2) }}</td>
              <td>
                @if($product->stock < 0)
                  <span class="text-danger font-weight-bold">{{ $product->stock }}</span>
                @elseif($product->stock == 0)
                  <span class="text-warning font-weight-bold">{{ $product->stock }}</span>
                @else
                  {{ $product->stock }}
                @endif
              </td>
              <td>
                @if($product->pro51_synced_at)
                  <span class="badge bg-success" title="Sincronizado {{ $product->pro51_synced_at->diffForHumans() }}">OK</span>
                @else
                  <span class="badge bg-secondary">Pendiente</span>
                @endif
              </td>
              <td>
                <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                <form action="{{ route('products.duplicate', $product) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-xs" title="Duplicar producto" onclick="return confirm('¿Duplicar este producto?')">
                        <i class="fas fa-copy"></i>
                    </button>
                </form>
                @if($_mainCompany && ($_mainCompany->facturacion_mode ?? 'propio') === 'api_externa' && !$product->pro51_synced_at)
                    <button class="btn btn-success btn-xs sync-pro51-btn" data-id="{{ $product->id }}" title="Sincronizar con pro51">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </button>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">No hay productos</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="card-footer">{{ $products->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.sync-pro51-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const originalHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('/pro51/products/' + id + '/sync', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = this.closest('tr');
                const statusCell = row.querySelector('td:nth-child(7)');
                statusCell.innerHTML = '<span class="badge bg-success">OK</span>';
                this.remove();
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
                this.disabled = false;
                this.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            alert('Error de conexión');
            this.disabled = false;
            this.innerHTML = originalHtml;
        });
    });
});

const syncAllBtn = document.getElementById('syncAllPro51');
if (syncAllBtn) {
    syncAllBtn.addEventListener('click', function() {
        if (!confirm('¿Sincronizar todos los productos pendientes con pro51?')) return;
        const originalHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';

        fetch('{{ route("pro51.products.sync-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ company_id: '{{ $companyId ?? "" }}' })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Sincronización completada');
            location.reload();
        })
        .catch(err => {
            alert('Error de conexión');
            this.disabled = false;
            this.innerHTML = originalHtml;
        });
    });
}
</script>
@endpush