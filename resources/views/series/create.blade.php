@extends('layouts.admin')
@section('title', 'Nueva Serie')
@section('page_title', 'Nueva Serie')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Serie</h3>
    </div>
    <form method="POST" action="{{ route('series.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <div class="card-body">
            <div class="form-group">
                <label>Tipo de Documento</label>
                <select name="tipo_documento" class="form-control" required>
                    <option value="01">Factura</option>
                    <option value="03">Boleta</option>
                </select>
            </div>
            <div class="form-group">
                <label>Número de Serie</label>
                <input type="text" name="serie" class="form-control" placeholder="Ej: F001, B001" maxlength="4" required>
                <small class="text-muted">Ingrese hasta 4 caracteres (ej: F001, B001)</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear Serie</button>
            <a href="{{ route('series.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection