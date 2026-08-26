@extends('layouts.admin')
@section('title', 'Configuración de Reporte')
@section('page_title', 'Configuración de Reporte')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sliders-h"></i> Configuración de Reporte de Caja</h3>
            </div>
            <form method="POST" action="{{ route('cash-report-settings.update') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $companyId }}">
                <div class="card-body">
                    <p class="text-muted">
                        Seleccione qué secciones desea mostrar en los reportes de cierre de caja
                        (<b>PDF A4</b>, <b>Ticket 80mm</b> e impresora <b>Caja</b>).
                        El <b>reporte web</b> siempre muestra todas las secciones.
                    </p>
                    <div class="form-group">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="mostrar_lista_comprobantes" id="mostrar_lista_comprobantes" value="1"
                                {{ (!isset($config) || $config->mostrar_lista_comprobantes) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mostrar_lista_comprobantes">
                                Habilitar <b>"LISTA DE COMPROBANTES"</b>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="mostrar_productos_vendidos" id="mostrar_productos_vendidos" value="1"
                                {{ (!isset($config) || $config->mostrar_productos_vendidos) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mostrar_productos_vendidos">
                                Habilitar <b>"PRODUCTOS VENDIDOS"</b>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="mostrar_lineas_eliminadas" id="mostrar_lineas_eliminadas" value="1"
                                {{ (!isset($config) || $config->mostrar_lineas_eliminadas) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mostrar_lineas_eliminadas">
                                Habilitar <b>"REPORTE DE LÍNEAS ELIMINADAS"</b>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                    <a href="{{ route('cashregisters.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection