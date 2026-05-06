@extends('layouts.admin')
@section('title', 'Ver Comprobante')
@section('page_title', 'Ver Comprobante')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ $invoice->document_type_name }} {{ $invoice->full_number }}</h3>
                @if($invoice->tipo_documento == 'NV')
                <span class="badge bg-warning ml-2">Nota de Venta</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha</span>
                                <span class="info-box-number">{{ $invoice->fecha_emision }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cliente</span>
                                <span class="info-box-number">{{ $invoice->customer->nombre }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-id-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Documento</span>
                                <span class="info-box-number">{{ $invoice->customer->documento_tipo == '1' ? 'DNI' : 'RUC' }}: {{ $invoice->customer->documento_numero }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Método de Pago</span>
                                <span class="info-box-number">{{ $invoice->metodo_pago ?? 'EFECTIVO' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $invoice->sunat_estado == 'ACEPTADO' || $invoice->sunat_estado == 'ENVIADO' ? 'success' : 'danger' }}"><i class="fas fa-cloud-upload-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Estado SUNAT</span>
                                <span class="info-box-number">{{ $invoice->sunat_estado }} @if($invoice->sunat_code)({{ $invoice->sunat_code }})@endif</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($invoice->sunat_description)
                <div class="alert alert-info mt-3">
                    {{ $invoice->sunat_description }}
                </div>
                @endif

                @if($invoice->isNotaVenta())
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle"></i> Nota de Venta - NV no envía a SUNAT. Este documento es para ventas internas.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Items</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->codigo }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td class="text-right">{{ $item->cantidad }}</td>
                    <td class="text-right">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item->precio_venta, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-sm">
                    <tr>
                        <td class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">S/ {{ number_format($invoice->gravado, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>IGV:</strong></td>
                        <td class="text-right">S/ {{ number_format($invoice->igv, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>S/ {{ number_format($invoice->total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($invoice->creditNote)
<div class="card mt-3">
    <div class="card-header bg-warning">
        <h3 class="card-title">Nota de Crédito Generada</h3>
    </div>
    <div class="card-body">
        <p><strong>Documento:</strong> {{ $invoice->creditNote->full_number }}</p>
        <p><strong>Estado SUNAT:</strong> <span class="text-{{ $invoice->creditNote->sunat_estado == 'ACEPTADO' ? 'success' : 'danger' }}">{{ $invoice->creditNote->sunat_estado }}</span></p>
        <a href="{{ route('invoices.show', $invoice->creditNote) }}" class="btn btn-warning btn-sm">Ver Nota de Crédito</a>
    </div>
</div>
@endif

@if($invoice->tipo_documento == '07' && $invoice->originalInvoice)
<div class="card mt-3">
    <div class="card-header bg-info">
        <h3 class="card-title">Documento Modificado</h3>
    </div>
    <div class="card-body">
        <p><strong>Factura/Boleta original:</strong> {{ $invoice->originalInvoice->full_number }}</p>
        <a href="{{ route('invoices.show', $invoice->originalInvoice) }}" class="btn btn-info btn-sm">Ver Documento Original</a>
    </div>
</div>
@endif

<div class="mt-3">
    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Ver PDF</a>
    <a href="{{ route('invoices.ticket', $invoice) }}" target="_blank" class="btn btn-orange"><i class="fas fa-print"></i> Ticket (80mm)</a>
    
    @if($invoice->xml_firmado)
    <a href="{{ route('invoices.downloadXml', $invoice) }}" class="btn btn-secondary"><i class="fas fa-download"></i> XML</a>
    @endif
    
    @if($invoice->cdr_path || $invoice->sunat_estado == 'ACEPTADO')
    <a href="{{ route('invoices.downloadCdr', $invoice) }}" class="btn btn-purple"><i class="fas fa-download"></i> CDR</a>
    @endif
    
    @if($invoice->sunat_estado == 'ACEPTADO' && !$invoice->credit_note_id && $invoice->tipo_documento != '07')
    <a href="{{ route('invoices.creditNoteForm', $invoice) }}" class="btn btn-warning"><i class="fas fa-minus-circle"></i> Nota de Crédito</a>
    @endif
    
    @if($invoice->sunat_estado != 'ACEPTADO' && $invoice->sunat_estado != 'ENVIADO')
    <a href="{{ route('invoices.send', $invoice) }}" class="btn btn-success"><i class="fas fa-paper-plane"></i> Enviar a SUNAT</a>
    @endif
    
    @if($invoice->sunat_estado == 'ACEPTADO' && $invoice->tipo_documento != '07')
    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de dar de baja este documento en SUNAT?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger"><i class="fas fa-power-off"></i> Dar de Baja</button>
    </form>
    @endif
    
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection