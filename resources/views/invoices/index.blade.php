@extends('layouts.admin')
@section('title', 'Comprobantes')
@section('page_title', 
    $tipoDocumento == '01' ? 'Facturas' : 
    ($tipoDocumento == '03' ? 'Boletas' : 
    ($tipoDocumento == '07' ? 'Notas de Crédito' : 'Todos los Comprobantes'))
)

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Comprobantes</h3>
        <div class="card-tools">
          <a href="{{ route('invoices.create', ['company_id' => $companyId ?? null]) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Generar Comprobante
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado SUNAT</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $invoice)
            <tr>
              <td>{{ $invoice->document_type_name }} {{ $invoice->full_number }}</td>
              <td>{{ $invoice->customer->nombre }}</td>
              <td>{{ $invoice->fecha_emision }}</td>
              <td>S/ {{ number_format($invoice->total, 2) }}</td>
              <td>
                @switch($invoice->sunat_estado)
                  @case('PENDIENTE')<span class="badge badge-warning">Pendiente</span>@break
                  @case('ENVIADO')<span class="badge badge-info">Enviado</span>@break
                  @case('ACEPTADO')<span class="badge badge-success">Aceptado</span>@break
                  @case('RECHAZADO')<span class="badge badge-danger">Rechazado</span>@break
                  @case('ANULADO')<span class="badge badge-secondary">Anulado</span>@break
                @endswitch
              </td>
              <td>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary btn-xs" target="_blank"><i class="fas fa-file-pdf"></i></a>
                @if($invoice->xml_path)
                <a href="{{ route('invoices.downloadXml', $invoice) }}" class="btn btn-primary btn-xs"><i class="fas fa-code"></i></a>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">No hay comprobantes</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="card-footer">{{ $invoices->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection