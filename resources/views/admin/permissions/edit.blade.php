@extends('layouts.admin')
@section('title', 'Editar Permiso')
@section('page_title', 'Editar Permiso')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Permiso</h3>
    </div>
    <form action="{{ route('permissions.update', $permission) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" value="{{ $permission->name }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Módulo</label>
                <select name="module" class="form-control" required>
                    <option value="">Seleccionar módulo</option>
                    @foreach($modules as $key => $label)
                    <option value="{{ $key }}" {{ $permission->module == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control" rows="2">{{ $permission->description }}</textarea>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" {{ $permission->status ? 'checked' : '' }}>
                    <label class="custom-control-label" for="status">Activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection