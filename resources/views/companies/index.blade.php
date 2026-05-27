@extends('layouts.admin')
@section('title', 'Empresa')
@section('page_title', 'Empresa')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">&nbsp;</h3>
        <div class="card-tools">
          <form action="{{ route('sunat.padron.download') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-info btn-sm" title="Descargar padrón SUNAT">
              <i class="fas fa-download"></i> Descargar padrón SUNAT
            </button>
          </form>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>RUC</th>
              <th>Razón Social</th>
              <th>Email</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
                @forelse($companies as $company)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $company->ruc }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $company->razon_social }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $company->email }}</td>
                    <td>
                @if($company->estado === 'ACTIVO')
                  <span class="badge badge-success">ACTIVO</span>
                @else
                  <span class="badge badge-secondary">{{ $company->estado }}</span>
                @endif
              </td>
              <td>
                <a href="{{ route('companies.show', $company) }}" class="btn btn-info btn-xs" title="Ver">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning btn-xs" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No hay empresas</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
