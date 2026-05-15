@extends('layouts.admin')
@section('title', 'Modo de Pedidos')
@section('page_title', 'Modo de Pedidos')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configuración del Modo de Pedidos</h3>
            </div>
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <span class="badge badge-{{ $orderMode === 'print' ? 'info' : 'secondary' }}" style="font-size:16px; padding:8px 20px;">
                        Modo actual: <strong>{{ $orderMode === 'print' ? 'IMPRESIÓN 80mm' : 'KDS' }}</strong>
                    </span>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card card-{{ $orderMode === 'kds' ? 'primary' : 'outline-secondary' }} h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-tv fa-4x mb-3 text-{{ $orderMode === 'kds' ? 'primary' : 'secondary' }}"></i>
                                <h4>Modo KDS</h4>
                                <p class="text-muted">Los pedidos se muestran en pantallas KDS conectadas por cada zona (Cocina, Bar, etc.)</p>
                                @if($orderMode === 'kds')
                                <span class="badge badge-success"><i class="fas fa-check"></i> ACTIVO</span>
                                @else
                                <form method="POST" action="{{ route('restaurant.toggleMode') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary mt-2">Activar Modo KDS</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-{{ $orderMode === 'print' ? 'info' : 'outline-secondary' }} h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-print fa-4x mb-3 text-{{ $orderMode === 'print' ? 'info' : 'secondary' }}"></i>
                                <h4>Modo Impresión 80mm</h4>
                                <p class="text-muted">Los pedidos se imprimen directamente en impresoras térmicas asignadas por zona</p>
                                @if($orderMode === 'print')
                                <span class="badge badge-success"><i class="fas fa-check"></i> ACTIVO</span>
                                @else
                                <form method="POST" action="{{ route('restaurant.toggleMode') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info mt-2">Activar Modo Impresión</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($orderMode === 'print')
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    Asegúrate de tener configuradas las impresoras en
                    <a href="{{ route('printers.index') }}">Gestión de Impresoras</a>
                    con las asignaciones: Cocina-1, Cocina-2, Bar-1 y Precuenta.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
