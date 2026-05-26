@extends('layouts.admin')
@section('title', 'Nueva Mesa')
@section('page_title', 'Nueva Mesa')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('restaurant.index', ['company_id' => $companyId]) }}">Restaurante</a></li>
<li class="breadcrumb-item"><a href="{{ route('restaurant.floors.index', ['company_id' => $companyId]) }}">Pisos</a></li>
<li class="breadcrumb-item active">Nueva Mesa</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Crear Nueva Mesa</h3>
    </div>
    @if($errors->any())
    <div class="alert alert-danger m-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form action="{{ route('restaurant.tables.store') }}" method="POST">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Piso</label>
                        <select name="floor_id" class="form-control" required>
                            @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" {{ isset($floorId) && $floorId == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de Mesa</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Mesa 1, VIP 1, etc.">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacidad (personas)</label>
                        <input type="number" name="capacity" class="form-control" value="4" min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="#28a745">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('restaurant.floors.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection
