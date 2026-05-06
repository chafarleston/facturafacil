@extends('layouts.admin')
@section('title', 'Series')
@section('page_title', 'Series')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Series</h3>
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
              <th>Tipo</th>
              <th>Último Número</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($series as $serie)
            <tr>
              <td>{{ $serie->serie }}</td>
              <td>{{ $serie->tipo == '01' ? 'Factura' : 'Boleta' }}</td>
              <td>{{ $serie->numero }}</td>
              <td>
                @if($serie->estado === 'ACTIVO')
                  <span class="badge badge-success">ACTIVO</span>
                @else
                  <span class="badge badge-secondary">{{ $serie->estado }}</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">No hay series</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection