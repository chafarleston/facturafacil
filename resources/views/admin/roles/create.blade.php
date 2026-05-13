@extends('layouts.admin')
@section('title', 'Crear Rol')
@section('page_title', 'Crear Rol')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Rol</h3>
    </div>
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
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
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection