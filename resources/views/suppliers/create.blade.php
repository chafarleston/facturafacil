@extends('layouts.admin')
@section('title', 'Nuevo Proveedor')
@section('page_title', 'Nuevo Proveedor')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Proveedor</h3>
    </div>
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" class="form-control" maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
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
            <a href="{{ route('suppliers.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection