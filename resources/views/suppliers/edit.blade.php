@extends('layouts.admin')
@section('title', 'Editar Proveedor')
@section('page_title', 'Editar Proveedor')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Proveedor</h3>
    </div>
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" value="{{ $supplier->nombre }}" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" value="{{ $supplier->ruc }}" class="form-control" maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ $supplier->telefono }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $supplier->direccion }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ $supplier->email }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT" {{ $supplier->estado == 'ACT' ? 'selected' : '' }}>Activo</option>
                    <option value="INA" {{ $supplier->estado == 'INA' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('suppliers.index', ['company_id' => $supplier->company_id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection