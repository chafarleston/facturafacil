@extends('layouts.admin')
@section('title', 'Series')
@section('page_title', 'Series')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Series de Comprobantes</h3>
        <div class="card-tools">
          <a href="{{ route('series.create', ['company_id' => $companyId ?? null]) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Serie
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Serie</th>
              <th>Tipo de Documento</th>
              <th>Próximo Número</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($series as $serie)
            <tr>
              <td><strong>{{ $serie->serie }}</strong></td>
              <td>
                @if($serie->tipo_documento === '01')
                  <span class="badge badge-info">Factura</span>
                @elseif($serie->tipo_documento === '03')
                  <span class="badge badge-success">Boleta</span>
                @elseif($serie->tipo_documento === 'NV')
                  <span class="badge badge-warning">Nota de Venta</span>
                @elseif($serie->tipo_documento === '07')
                  <span class="badge badge-primary">Nota de Crédito</span>
                @elseif($serie->tipo_documento === '08')
                  <span class="badge badge-danger">Nota de Débito</span>
                @elseif($serie->tipo_documento === '09')
                  <span class="badge badge-secondary">Guía de Remisión</span>
                @elseif($serie->tipo_documento === '20')
                  <span class="badge badge-dark">Retención</span>
                @elseif($serie->tipo_documento === '40')
                  <span class="badge badge-dark">Percepción</span>
                @else
                  <span class="badge badge-secondary">{{ $serie->tipo_documento }}</span>
                @endif
              </td>
              <td>{{ $serie->numero_actual + 1 }}</td>
              <td>
                @if($serie->estado === 'ACTIVO')
                  <span class="badge badge-success">ACTIVO</span>
                @else
                  <span class="badge badge-secondary">{{ $serie->estado }}</span>
                @endif
              </td>
              <td>
                <a href="{{ route('series.edit', $serie) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No hay series configuradas</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection