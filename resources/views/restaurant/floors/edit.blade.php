@extends('layouts.admin')
@section('title', 'Editar Piso')
@section('page_title', 'Editar Piso')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('restaurant.index', ['company_id' => $floor->company_id]) }}">Restaurante</a></li>
<li class="breadcrumb-item"><a href="{{ route('restaurant.floors.index', ['company_id' => $floor->company_id]) }}">Pisos</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Piso</h3>
    </div>
    <form action="{{ route('restaurant.floors.update', $floor) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre del Piso</label>
                <input type="text" name="name" class="form-control" required value="{{ $floor->name }}">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Orden de Visualización</label>
                        <input type="number" name="order" class="form-control" value="{{ $floor->order }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="ACTIVE" {{ $floor->status === 'ACTIVE' ? 'selected' : '' }}>Activo</option>
                            <option value="INACTIVE" {{ $floor->status === 'INACTIVE' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('restaurant.floors.index', ['company_id' => $floor->company_id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Mesas del Piso</h3>
        <div class="card-tools">
            <a href="{{ route('restaurant.tables.create', ['company_id' => $floor->company_id, 'floor_id' => $floor->id]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Mesa
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if($tables->isEmpty())
        <div class="alert alert-info m-3">No hay mesas en este piso.</div>
        @else
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tables as $table)
                <tr>
                    <td>{{ $table->name }}</td>
                    <td>{{ $table->capacity }}</td>
                    <td>
                        @if($table->status === 'AVAILABLE')
                        <span class="badge badge-success">Disponible</span>
                        @elseif($table->status === 'OCCUPIED')
                        <span class="badge badge-warning">Ocupada</span>
                        @else
                        <span class="badge badge-secondary">{{ $table->status }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('restaurant.tables.edit', $table) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('restaurant.tables.destroy', $table) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta mesa?')"><i class="fas fa-trash"></i></button>
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
