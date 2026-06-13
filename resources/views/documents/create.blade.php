@extends('layouts.admin')
@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            @if($tipo === 'T' && isset($invoices))
            <div class="card-body bg-light">
                <div class="form-group mb-0">
                    <label><i class="fas fa-file-invoice"></i> Generar desde comprobante</label>
                    <select id="invoiceSelector" class="form-control" onchange="loadInvoiceData(this.value)">
                        <option value="">— Seleccione factura/boleta —</option>
                        @foreach($invoices as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->full_number }} - {{ $inv->customer?->nombre ?? 'VARIOS' }} (S/ {{ number_format($inv->total, 2) }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seleccione un comprobante para precargar los datos del destinatario y los items</small>
                </div>
            </div>
            @endif
            <form action="{{ route('documents.store', $tipo) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Serie</label>
                                <select name="serie_id" class="form-control" required>
                                    @foreach($series as $s)
                                    <option value="{{ $s->id }}">{{ $s->serie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha de Emisión</label>
                                <input type="date" name="fecha_emision" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Datos del {{ $tipo === 'T' ? 'Destinatario' : 'Proveedor' }}</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Doc.</label>
                                <select name="entity_tipo_doc" class="form-control">
                                    <option value="6">RUC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número</label>
                                <input type="text" name="entity_num_doc" class="form-control" maxlength="11" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Razón Social</label>
                                <input type="text" name="entity_razon_social" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="entity_direccion" class="form-control">
                    </div>

                    <hr>
                    <h5>Detalle</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total</label>
                                <input type="number" step="0.01" min="0" name="total" class="form-control" value="0.00" required>
                            </div>
                        </div>
                        @if(in_array($tipo, ['R', 'P']))
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Régimen</label>
                                <select name="regimen" class="form-control">
                                    <option value="01">Ventas Internas</option>
                                    <option value="02">Exportación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tasa (%)</label>
                                <input type="number" step="0.01" name="tasa" class="form-control" value="3">
                            </div>
                        </div>
                        @endif
                        @if($tipo === 'R')
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Imp. Retenido</label>
                                <input type="number" step="0.01" name="imp_retenido" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Imp. Pagado</label>
                                <input type="number" step="0.01" name="imp_pagado" class="form-control" value="0.00">
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($tipo === 'T')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección de Partida</label>
                                <input type="text" name="dir_partida" id="dir_partida" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección de Llegada</label>
                                <input type="text" name="dir_llegada" id="dir_llegada" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Items (se cargarán automáticamente al seleccionar un comprobante)</label>
                        <div id="itemsContainer" class="p-2 border rounded bg-light">
                            <p class="text-muted mb-0" id="itemsPlaceholder">Seleccione un comprobante para ver los items</p>
                        </div>
                        <input type="hidden" name="items" id="itemsJson">
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Observación</label>
                        <input type="text" name="observacion" class="form-control">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('documents.index', $tipo) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@if($tipo === 'T')
@push('scripts')
<script>
function loadInvoiceData(invoiceId) {
    if (!invoiceId) {
        document.getElementById('itemsContainer').innerHTML = '<p class="text-muted mb-0">Seleccione un comprobante para ver los items</p>';
        document.getElementById('itemsJson').value = '';
        return;
    }

    fetch('/api/invoice-data/' + invoiceId)
        .then(r => r.json())
        .then(data => {
            if (data.customer) {
                document.querySelector('[name="entity_num_doc"]').value = data.customer.num_doc || '';
                document.querySelector('[name="entity_razon_social"]').value = data.customer.razon_social || '';
                document.querySelector('[name="entity_direccion"]').value = data.customer.direccion || '';
            }
            document.querySelector('[name="total"]').value = data.total || 0;
            document.querySelector('[name="fecha_emision"]').value = data.fecha_emision || '{{ date("Y-m-d") }}';

            if (data.items && data.items.length > 0) {
                let html = '<table class="table table-sm mb-0"><thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Unidad</th></tr></thead><tbody>';
                data.items.forEach(item => {
                    html += `<tr><td>${item.codigo || '—'}</td><td>${item.descripcion}</td><td>${item.cantidad}</td><td>${item.unidad}</td></tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('itemsContainer').innerHTML = html;
                document.getElementById('itemsJson').value = JSON.stringify(data.items);
            }

            // Pre-fill addresses from company/customer
            if (data.customer && data.customer.direccion) {
                document.getElementById('dir_llegada').value = data.customer.direccion;
            }
        })
        .catch(err => {
            document.getElementById('itemsContainer').innerHTML = '<p class="text-danger mb-0">Error al cargar datos del comprobante</p>';
        });
}
</script>
@endpush
@endif
