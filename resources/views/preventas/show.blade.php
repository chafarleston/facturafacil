@extends('layouts.admin')
@section('title', 'Detalle de preventa')
@section('page_title', 'Preventas / ' . $preventa->tipoLabel())

@section('content')
@php
    $estadoClass = match($preventa->estado) {
        'APROBADO', 'VIGENTE', 'ATENDIDO', 'ENTREGADO', 'CONVERTIDO' => 'success',
        'RECHAZADO', 'CANCELADO' => 'danger',
        'EN_PROCESO', 'ENVIADO' => 'info',
        default => 'secondary',
    };
    $pedidoGenerado = $preventa->children->firstWhere('tipo', \App\Models\Preventa::TIPO_PEDIDO);
    $requiereCaja = !$preventa->invoice_id
        && $preventa->items->isNotEmpty()
        && (
            ($preventa->tipo === \App\Models\Preventa::TIPO_COTIZACION && !$pedidoGenerado)
            || $preventa->tipo === \App\Models\Preventa::TIPO_PEDIDO
        );
@endphp
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span>&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span>&times;</span></button>
    </div>
@endif
@if($requiereCaja && !$cajaAbierta)
    <div class="alert alert-warning">
        <i class="fas fa-cash-register"></i>
        Debe aperturar una caja antes de validar la cotización o generar el comprobante del pedido.
        <a href="{{ route('cashregisters.index') }}" class="alert-link">Ir a Caja</a>
    </div>
@endif
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> {{ $preventa->numero }} · {{ $preventa->titulo }}</h3>
                <div class="card-tools">
                    <a href="{{ route('preventas.print', $preventa) }}" target="_blank" class="btn btn-default btn-sm">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                    @if($preventa->tipo === \App\Models\Preventa::TIPO_COTIZACION)
                        @if($pedidoGenerado)
                            <a href="{{ route('preventas.show', $pedidoGenerado) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-shopping-bag"></i> Ver pedido {{ $pedidoGenerado->numero }}
                            </a>
                        @elseif($preventa->items->isNotEmpty() && $cajaAbierta)
                            <form action="{{ route('preventas.convert', $preventa) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('¿Confirmar la cotización y crear el pedido?')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-check"></i> Confirmar y crear pedido
                                </button>
                            </form>
                        @endif
                    @elseif($preventa->tipo === \App\Models\Preventa::TIPO_PEDIDO)
                        @if($preventa->invoice_id)
                            <a href="{{ route('invoices.show', $preventa->invoice_id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-invoice"></i> Ver comprobante
                            </a>
                        @elseif($preventa->items->isNotEmpty() && $cajaAbierta)
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#preventaInvoiceModal">
                                <i class="fas fa-file-invoice"></i> Generar comprobante
                            </button>
                        @endif
                    @endif
                    @if(!$preventa->invoice_id && !($preventa->tipo === \App\Models\Preventa::TIPO_COTIZACION && $pedidoGenerado))
                        <a href="{{ route('preventas.edit', $preventa) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                    @endif
                    <a href="{{ route('preventas.index', ['tipo' => $preventa->tipo]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ $preventa->cliente_nombre }}</p>
                        <p><strong>Documento:</strong> {{ $preventa->cliente_documento ?: '-' }}</p>
                        <p><strong>Correo:</strong> {{ $preventa->cliente_email ?: '-' }}</p>
                        <p><strong>Teléfono:</strong> {{ $preventa->cliente_telefono ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> {{ $preventa->tipoLabel() }}</p>
                        <p><strong>Estado:</strong> <span class="badge badge-{{ $estadoClass }}">{{ $preventa->estadoLabel() }}</span></p>
                        <p><strong>Fecha:</strong> {{ $preventa->fecha?->format('d/m/Y') }}</p>
                        <p><strong>Vencimiento:</strong> {{ $preventa->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</p>
                        @if(in_array($preventa->tipo, [\App\Models\Preventa::TIPO_COTIZACION, \App\Models\Preventa::TIPO_PEDIDO], true))
                            <p><strong>Tipo de venta:</strong> {{ $preventa->documentTypeLabel() }}</p>
                            <p><strong>Condición de pago:</strong> {{ $preventa->paymentConditionLabel() }}</p>
                        @endif
                    </div>
                </div>
                <hr>
                @if($preventa->items->isNotEmpty())
                    <h5><i class="fas fa-boxes"></i> Productos</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Cantidad</th>
                                    <th class="text-right">Precio unit.</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preventa->items as $item)
                                    <tr>
                                        <td>{{ $item->descripcion }}</td>
                                        <td class="text-right">{{ number_format((float) $item->cantidad, 4) }}</td>
                                        <td class="text-right">S/ {{ number_format((float) $item->precio_unitario, 2) }}</td>
                                        <td class="text-right">S/ {{ number_format((float) $item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tr><th>Subtotal</th><td class="text-right">S/ {{ number_format((float) $preventa->subtotal, 2) }}</td></tr>
                                <tr><th>IGV</th><td class="text-right">S/ {{ number_format((float) $preventa->igv, 2) }}</td></tr>
                                <tr><th>Total</th><td class="text-right font-weight-bold">S/ {{ number_format((float) $preventa->monto, 2) }}</td></tr>
                            </table>
                        </div>
                    </div>
                @else
                    <p><strong>Monto:</strong>
                        {{ $preventa->monto !== null ? 'S/ ' . number_format((float) $preventa->monto, 2) : '-' }}
                    </p>
                @endif
                <p><strong>Descripción:</strong></p>
                <div class="border rounded p-3 bg-light" style="white-space: pre-line;">{{ $preventa->descripcion ?: 'Sin descripción.' }}</div>
                <p class="mt-3"><strong>Observaciones:</strong></p>
                <div class="border rounded p-3 bg-light" style="white-space: pre-line;">{{ $preventa->observaciones ?: 'Sin observaciones.' }}</div>
            </div>
            <div class="card-footer">
                <form action="{{ route('preventas.destroy', $preventa) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($preventa->tipo === \App\Models\Preventa::TIPO_PEDIDO && !$preventa->invoice_id && $preventa->items->isNotEmpty() && $cajaAbierta)
<div class="modal fade" id="preventaInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="preventaInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('preventas.invoice', $preventa) }}">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="preventaInvoiceModalLabel">
                        <i class="fas fa-file-invoice"></i> Generar comprobante
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Se usarán el cliente, los productos, el IGV y el total de la preventa <strong>{{ $preventa->numero }}</strong>.
                    </p>
                    <div class="form-group">
                        <label for="preventaDocumentType">Tipo de comprobante</label>
                        @php
                            $defaultDocumentType = in_array($preventa->document_type, ['01', '03'], true) ? $preventa->document_type : '03';
                        @endphp
                        <select name="document_type" id="preventaDocumentType" class="form-control" required>
                            <option value="01" {{ $defaultDocumentType === '01' ? 'selected' : '' }}>Factura</option>
                            <option value="03" {{ $defaultDocumentType === '03' ? 'selected' : '' }}>Boleta</option>
                            <option value="NV">Nota de venta</option>
                        </select>
                    </div>
                    @if($preventa->payment_condition)
                        <p class="mb-0"><strong>Condición de pago:</strong> {{ $preventa->paymentConditionLabel() }}</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
