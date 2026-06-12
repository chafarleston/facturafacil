@extends('layouts.admin')
@section('title', 'Editar Serie')
@section('page_title', 'Editar Serie')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Serie: {{ $serie->serie }}</h3>
    </div>
    <form method="POST" action="{{ route('series.update', $serie) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Documento</label>
                        <input type="text" class="form-control" value="{{ $serie->tipo_documento === '01' ? 'Factura Electrónica' : ($serie->tipo_documento === '03' ? 'Boleta de Venta Electrónica' : ($serie->tipo_documento === 'NV' ? 'Nota de Venta' : $serie->tipo_documento)) }}" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Serie</label>
                        <input type="text" class="form-control" value="{{ $serie->serie }}" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Próximo Número</label>
                        <input type="text" class="form-control" value="{{ $serie->numero_actual + 1 }}" readonly>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Reiniciar desde número</label>
                <input type="number" name="numero_inicio" class="form-control" value="{{ $serie->numero_actual + 1 }}" min="1" required>
                <small class="text-muted">El próximo documento comenzará desde este número</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('series.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection