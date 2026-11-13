@extends('layouts.admin')
@section('title', 'Clientes')
@section('page_title', 'Clientes')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Clientes</h3>
        <div class="card-tools">
          <a href="{{ route('customers.create', ['company_id' => $companyId ?? null]) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nuevo Cliente
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Nombre</th>
              <th>Estado SUNAT</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($customers as $customer)
            <tr>
              <td>{{ $customer->documento_tipo == '1' ? 'DNI' : 'RUC' }}: {{ $customer->documento_numero }}</td>
              <td>{{ $customer->nombre }}</td>
              <td>
                @if($customer->documento_tipo === '6')
                  @if($customer->isSunatBlocked())
                    <span class="badge badge-danger" title="{{ $customer->sunat_condicion }}">BLOQUEADO: {{ $customer->sunat_estado ?: 'SIN VERIFICAR' }}</span>
                  @else
                    <span class="badge badge-success">ACTIVO</span>
                  @endif
                @else
                  <span class="text-muted">No aplica</span>
                @endif
              </td>
              <td>{{ $customer->email }}</td>
              <td>{{ $customer->telefono }}</td>
              <td>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                @if($customer->canBeEdited())
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">No hay clientes</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="card-footer">{{ $customers->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection