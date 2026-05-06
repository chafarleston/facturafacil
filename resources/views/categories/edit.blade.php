@extends('layouts.admin')
@section('title', 'Editar Categoría')
@section('page_title', 'Editar Categoría')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Categoría</h3>
    </div>
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="{{ $category->nombre }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ $category->descripcion }}</textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT" {{ $category->estado == 'ACT' ? 'selected' : '' }}>Activo</option>
                    <option value="INA" {{ $category->estado == 'INA' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('categories.index', ['company_id' => $category->company_id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection