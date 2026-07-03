@extends('layouts.admin')
@section('title', 'Editar Elemento Auxiliar')
@section('page_title', 'Editar Elemento Auxiliar')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('auxiliary-items.update', $auxiliaryItem) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" required maxlength="100" value="{{ $auxiliaryItem->name }}">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="status" class="form-control">
                    <option value="ACTIVO" {{ $auxiliaryItem->status == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                    <option value="INACTIVO" {{ $auxiliaryItem->status == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('auxiliary-items.index', ['company_id' => $auxiliaryItem->company_id]) }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
