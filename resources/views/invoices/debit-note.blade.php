@extends('layouts.admin')
@section('title', 'Nota de Débito')
@section('page_title', 'Nota de Débito')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Documento a modificar</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>{{ $invoice->tipo_documento == '01' ? 'FACTURA' : 'BOLETA' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Número:</strong></td>
                        <td>{{ $invoice->full_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>{{ $invoice->fecha_emision }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total:</strong></td>
                        <td>S/ {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>{{ $invoice->customer->nombre }}<br><small>{{ $invoice->customer->documento_tipo == '6' ? 'RUC: ' : 'DNI: ' }}{{ $invoice->customer->documento_numero }}</small></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Datos de la Nota de Débito</h3>
            </div>
            <form action="{{ route('invoices.sendDebitNote', $invoice) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="motivo">Motivo de la nota</label>
                        <select name="motivo" id="motivo" class="form-control" required>
                            <option value="">Seleccione un motivo</option>
                            <option value="01">01 - Intereses por mora</option>
                            <option value="02">02 - Aumento en el valor</option>
                            <option value="03">03 - Penalidades</option>
                            <option value="10">10 - Otros conceptos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción del motivo</label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control" required placeholder="Ej: AUMENTO POR INTERESES">
                    </div>
                    <div class="form-group">
                        <label>Monto a debitar</label>
                        <p class="h4 text-danger">S/ {{ number_format($invoice->total, 2) }}</p>
                        <p class="text-muted">Se generará nota de débito por el total del documento</p>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger">Generar Nota de Débito</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
