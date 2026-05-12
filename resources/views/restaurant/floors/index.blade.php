@extends('layouts.admin')
@section('title', 'Pisos - Restaurante')
@section('page_title', 'Pisos del Restaurante')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('restaurant.index', ['company_id' => $companyId]) }}">Restaurante</a></li>
<li class="breadcrumb-item active">Pisos</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Pisos</h3>
        <div class="card-tools">
            <a href="{{ route('restaurant.floors.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Piso
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($floors->isEmpty())
        <div class="alert alert-info">
            No hay pisos configurados. <a href="{{ route('restaurant.floors.create', ['company_id' => $companyId]) }}">Crear el primer piso</a>
        </div>
        @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Mesas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($floors as $floor)
                <tr>
                    <td>{{ $floor->order }}</td>
                    <td>{{ $floor->name }}</td>
                    <td>{{ $floor->tables->count() }}</td>
                    <td>
                        @if($floor->status === 'ACTIVE')
                        <span class="badge badge-success">Activo</span>
                        @else
                        <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('restaurant.tables.create', ['company_id' => $companyId, 'floor_id' => $floor->id]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-chair"></i> Mesas
                        </a>
                        <a href="{{ route('restaurant.floors.edit', $floor) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('restaurant.floors.destroy', $floor) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este piso?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
