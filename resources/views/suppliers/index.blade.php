@extends('layouts.admin')
@section('title', 'Proveedores')
@section('page_title', 'Proveedores')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Proveedores</h3>
        <a href="{{ route('suppliers.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm float-right">Nuevo Proveedor</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUC</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->nombre }}</td>
                    <td>{{ $supplier->ruc }}</td>
                    <td>{{ $supplier->telefono }}</td>
                    <td><span class="badge badge-{{ $supplier->estado == 'ACT' ? 'success' : 'danger' }}">{{ $supplier->estado }}</span></td>
                    <td>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No hay proveedores</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection