@extends('layouts.admin')
@section('title', 'Editar Cliente')
@section('page_title', 'Editar Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Cliente</h3>
    </div>
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="documento_tipo" class="form-control" required>
                            <option value="1" {{ $customer->documento_tipo == '1' ? 'selected' : '' }}>DNI</option>
                            <option value="6" {{ $customer->documento_tipo == '6' ? 'selected' : '' }}>RUC</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Número Documento</label>
                        <input type="text" name="documento_numero" value="{{ $customer->documento_numero }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" value="{{ $customer->nombre }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $customer->direccion }}" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ $customer->telefono }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $customer->email }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection