@extends('layouts.admin')
@section('title', 'Editar Mesa')
@section('page_title', 'Editar Mesa')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Mesa</h3>
    </div>
    <form action="{{ route('restaurant.tables.update', $restaurantTable) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Piso</label>
                        <select name="floor_id" class="form-control" required>
                            @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" {{ $restaurantTable->floor_id == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de Mesa</label>
                        <input type="text" name="name" class="form-control" required value="{{ $restaurantTable->name }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Capacidad (personas)</label>
                        <input type="number" name="capacity" class="form-control" value="{{ $restaurantTable->capacity }}" min="1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="{{ $restaurantTable->color }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="AVAILABLE" {{ $restaurantTable->status === 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                            <option value="OCCUPIED" {{ $restaurantTable->status === 'OCCUPIED' ? 'selected' : '' }}>Ocupada</option>
                            <option value="RESERVED" {{ $restaurantTable->status === 'RESERVED' ? 'selected' : '' }}>Reservada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('restaurant.floors.index', ['company_id' => $restaurantTable->company_id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection
