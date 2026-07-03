@extends('layouts.admin')
@section('title', 'Nuevo Elemento Auxiliar')
@section('page_title', 'Nuevo Elemento Auxiliar')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('auxiliary-items.store') }}" method="POST">
            @csrf
            <input type="hidden" name="company_id" value="{{ $companyId }}">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="Ej: Mayonesa, Kétchup, Mostaza...">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="status" class="form-control">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('auxiliary-items.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
