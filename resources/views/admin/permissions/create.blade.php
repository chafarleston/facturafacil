@extends('layouts.admin')
@section('title', 'Crear Permiso')
@section('page_title', 'Crear Permiso')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Permiso</h3>
    </div>
    <form action="{{ route('permissions.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" class="form-control" placeholder="Ej: Ver Productos" required>
            </div>
            <div class="form-group">
                <label>Módulo</label>
                <select name="module" class="form-control" required>
                    <option value="">Seleccionar módulo</option>
                    @foreach($modules as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" checked>
                    <label class="custom-control-label" for="status">Activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection