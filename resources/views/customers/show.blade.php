@extends('layouts.admin')
@section('title', 'Ver Cliente')
@section('page_title', 'Ver Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Cliente: {{ $customer->nombre }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-id-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Documento</span>
                        <span class="info-box-number">{{ $customer->documento_tipo == '1' ? 'DNI' : 'RUC' }}: {{ $customer->documento_numero }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Email</span>
                        <span class="info-box-number">{{ $customer->email }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-phone"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Teléfono</span>
                        <span class="info-box-number">{{ $customer->telefono }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dirección</span>
                        <span class="info-box-number">{{ $customer->direccion }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $customer->estado == 'ACTIVO' ? 'success' : 'danger' }}"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado interno</span>
                        <span class="info-box-number">{{ $customer->estado }}</span>
                    </div>
                </div>
            </div>
        </div>
        @if($customer->documento_tipo === '6')
        <div class="row mt-3">
            <div class="col-md-12">
                @if($customer->isSunatBlocked())
                <div class="alert alert-danger">
                    <i class="fas fa-ban"></i>
                    <strong>Cliente bloqueado para facturación.</strong>
                    Estado SUNAT: {{ $customer->sunat_estado ?: 'SIN VERIFICAR' }}
                    @if($customer->sunat_condicion)
                        · Condición: {{ $customer->sunat_condicion }}
                    @endif
                    <br>
                    No puede editarse ni usarse para emitir facturas o boletas mientras el RUC no esté ACTIVO.
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    RUC ACTIVO en SUNAT · Condición: {{ $customer->sunat_condicion ?: 'NO DISPONIBLE' }}
                </div>
                @endif
                <p class="text-muted mb-0">Última verificación: {{ $customer->sunat_checked_at ? $customer->sunat_checked_at->format('d/m/Y H:i:s') : 'Sin verificar' }}</p>
            </div>
        </div>
        @endif
    </div>
    <div class="card-footer">
        @if($customer->canBeEdited())
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
        @endif
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection