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
@endsection
