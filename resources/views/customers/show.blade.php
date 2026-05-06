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
                    <span class="info-box-icon bg-{{ $customer->estado == 'ACT' ? 'success' : 'danger' }}"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">{{ $customer->estado }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection