@extends('layouts.admin')
@section('title', 'Detalle de preventa')
@section('page_title', 'Preventas / ' . $preventa->tipoLabel())

@section('content')
@php
    $estadoClass = match($preventa->estado) {
        'APROBADO', 'VIGENTE', 'ATENDIDO', 'ENTREGADO' => 'success',
        'RECHAZADO', 'CANCELADO' => 'danger',
        'EN_PROCESO', 'ENVIADO' => 'info',
        default => 'secondary',
    };
@endphp
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> {{ $preventa->numero }} · {{ $preventa->titulo }}</h3>
                <div class="card-tools">
                    <a href="{{ route('preventas.edit', $preventa) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                    <a href="{{ route('preventas.index', ['tipo' => $preventa->tipo]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ $preventa->cliente_nombre }}</p>
                        <p><strong>Documento:</strong> {{ $preventa->cliente_documento ?: '-' }}</p>
                        <p><strong>Correo:</strong> {{ $preventa->cliente_email ?: '-' }}</p>
                        <p><strong>Teléfono:</strong> {{ $preventa->cliente_telefono ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> {{ $preventa->tipoLabel() }}</p>
                        <p><strong>Estado:</strong> <span class="badge badge-{{ $estadoClass }}">{{ $preventa->estadoLabel() }}</span></p>
                        <p><strong>Fecha:</strong> {{ $preventa->fecha?->format('d/m/Y') }}</p>
                        <p><strong>Vencimiento:</strong> {{ $preventa->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>
                <hr>
                <p><strong>Monto:</strong>
                    {{ $preventa->monto !== null ? 'S/ ' . number_format((float) $preventa->monto, 2) : '-' }}
                </p>
                <p><strong>Descripción:</strong></p>
                <div class="border rounded p-3 bg-light" style="white-space: pre-line;">{{ $preventa->descripcion ?: 'Sin descripción.' }}</div>
                <p class="mt-3"><strong>Observaciones:</strong></p>
                <div class="border rounded p-3 bg-light" style="white-space: pre-line;">{{ $preventa->observaciones ?: 'Sin observaciones.' }}</div>
            </div>
            <div class="card-footer">
                <form action="{{ route('preventas.destroy', $preventa) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
