@extends('layouts.admin')
@section('title', 'Nuevo Cliente')
@section('page_title', 'Nuevo Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Cliente</h3>
    </div>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="documento_tipo" id="doc_tipo" class="form-control" required>
                            <option value="1">DNI</option>
                            <option value="6">RUC</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Número Documento</label>
                        <div class="input-group">
                            <input type="text" name="documento_numero" id="doc_numero" class="form-control" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" onclick="buscarCliente()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <small id="customer-status" class="text-sm"></small>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" id="customer_nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" id="customer_direccion" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('customers.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const companyId = {{ $companyId }};

function buscarCliente() {
    const docNumero = document.getElementById('doc_numero').value.trim();
    const docTipo = document.getElementById('doc_tipo').value;
    const statusEl = document.getElementById('customer-status');
    
    if (!docNumero) {
        alert('Ingrese número de documento');
        return;
    }
    
    statusEl.textContent = 'Buscando...';
    statusEl.className = 'text-sm text-info';
    
    fetch('/decolecta/search?company_id=' + companyId + '&documento=' + docNumero)
        .then(res => res.json())
        .then(data => {
            if (data.found && data.exists) {
                document.getElementById('customer_nombre').value = data.customer.nombre;
                document.getElementById('customer_direccion').value = data.customer.direccion || '';
                document.getElementById('doc_tipo').value = data.customer.documento_tipo;
                statusEl.textContent = '✓ Cliente encontrado';
                statusEl.className = 'text-sm text-success';
            } else if (data.api_data) {
                document.getElementById('customer_nombre').value = data.api_data.nombre || '';
                document.getElementById('customer_direccion').value = data.api_data.direccion || '';
                document.getElementById('doc_tipo').value = data.api_data.documento_tipo || docTipo;
                statusEl.textContent = 'Datos cargados desde SUNAT';
                statusEl.className = 'text-sm text-warning';
            } else {
                statusEl.textContent = 'Cliente no encontrado';
                statusEl.className = 'text-sm text-danger';
            }
        })
        .catch(err => {
            statusEl.textContent = 'Error al buscar';
            statusEl.className = 'text-sm text-danger';
        });
}
</script>
@endpush