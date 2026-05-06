@extends('layouts.admin')
@section('title', 'Nueva Categoría')
@section('page_title', 'Nueva Categoría')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Categoría</h3>
    </div>
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Servicios, Productos, etc.">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT">Activo</option>
                    <option value="INA">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('categories.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection