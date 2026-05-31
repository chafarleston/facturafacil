@extends('layouts.admin')
@section('title', 'Pendientes pro51')
@section('page_title', 'Comprobantes Pendientes - pro51')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Comprobantes pendientes de envío a pro51</h3>
        <div class="card-tools">
          <button id="retryAllBtn" class="btn btn-warning btn-sm">
            <i class="fas fa-redo"></i> Reenviar todos
          </button>
          <button id="syncExistingBtn" class="btn btn-info btn-sm ml-1">
            <i class="fas fa-link"></i> Vincular existentes
          </button>
          <button id="updateStatusBtn" class="btn btn-success btn-sm ml-1">
            <i class="fas fa-sync"></i> Actualizar estados
          </button>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>#</th>
              <th>Comprobante</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Error</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $invoice)
            <tr id="row-{{ $invoice->id }}">
              <td>{{ $invoice->id }}</td>
              <td>
                <a href="{{ route('invoices.show', $invoice) }}">
                  {{ $invoice->document_type_name }} {{ $invoice->full_number }}
                </a>
              </td>
              <td>{{ $invoice->customer?->nombre ?? 'Varios' }}</td>
              <td>{{ $invoice->fecha_emision }}</td>
              <td>S/ {{ number_format($invoice->total, 2) }}</td>
              <td>
                @if($invoice->sunat_estado === 'PENDIENTE')
                  <span class="badge bg-warning">PENDIENTE</span>
                @else
                  <span class="badge bg-danger">{{ $invoice->sunat_estado }}</span>
                @endif
              </td>
              <td>
                <small class="text-muted">{{ Str::limit($invoice->sunat_description, 50) }}</small>
              </td>
              <td>
                <button class="btn btn-sm btn-warning retry-btn" data-id="{{ $invoice->id }}">
                  <i class="fas fa-redo"></i> Reenviar
                </button>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">No hay comprobantes pendientes</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="card-footer">{{ $invoices->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.retry-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const row = document.getElementById('row-' + id);
        const originalHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('/pro51/pending/retry', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ invoice_id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                row.style.backgroundColor = '#d4edda';
                setTimeout(() => row.remove(), 2000);
            } else {
                alert(data.message || 'Error al reenviar');
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

document.getElementById('retryAllBtn')?.addEventListener('click', function() {
    if (!confirm('¿Reenviar todos los comprobantes pendientes a pro51?')) return;
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reenviando...';

    fetch('/pro51/pending/retry-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || 'Proceso completado');
        location.reload();
    })
    .catch(err => {
        alert('Error de conexión');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-redo"></i> Reenviar todos';
    });
});

document.getElementById('syncExistingBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vinculando...';

    fetch('{{ route("pro51.sync-documents") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        if (!res.ok) {
            const text = await res.text();
            throw new Error(res.status + ': ' + text.substring(0, 200));
        }
        return res.json();
    })
    .then(data => {
        alert(data.message || 'Proceso completado');
        location.reload();
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-link"></i> Vincular existentes';
    });
});

document.getElementById('updateStatusBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';

    fetch('{{ route("pro51.update-status") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        if (!res.ok) {
            const text = await res.text();
            throw new Error(res.status + ': ' + text.substring(0, 200));
        }
        return res.json();
    })
    .then(data => {
        alert(data.message || 'Proceso completado');
        location.reload();
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> Actualizar estados';
    });
});
</script>
@endpush
