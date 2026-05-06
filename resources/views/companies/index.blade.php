@extends('layouts.admin')
@section('title', 'Empresas')
@section('page_title', 'Empresas')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Empresas</h3>
        <div class="card-tools">
          <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Empresa
          </a>
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
                @if(!$company->is_main)
                <form action="{{ route('companies.setMain', $company) }}" method="POST" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-xs" title="Establecer principal" onclick="return confirm('¿Establecer como empresa principal?')">
                    <i class="fas fa-star"></i>
                  </button>
                </form>
                @endif
                <form action="{{ route('companies.destroy', $company) }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-xs" title="Eliminar" onclick="return confirm('¿Eliminar empresa?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
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
