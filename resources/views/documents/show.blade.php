@extends('layouts.admin')
@section('title', "{$title}: {$document->full_number}")
@section('page_title', "{$title}: {$document->full_number}")

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Documento</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><td><strong>Documento:</strong></td><td>{{ $document->full_number }}</td></tr>
                    <tr><td><strong>Fecha:</strong></td><td>{{ $document->fecha_emision }}</td></tr>
                    <tr><td><strong>Total:</strong></td><td>S/ {{ number_format($document->total, 2) }}</td></tr>
                    @if($document->regimen)
                    <tr><td><strong>Régimen:</strong></td><td>{{ $document->regimen }}</td></tr>
                    @endif
                    @if($document->tasa)
                    <tr><td><strong>Tasa:</strong></td><td>{{ $document->tasa }}%</td></tr>
                    @endif
                    @if($document->observacion)
                    <tr><td><strong>Observación:</strong></td><td>{{ $document->observacion }}</td></tr>
                    @endif
                    <tr><td><strong>Estado SUNAT:</strong></td>
                        <td>
                            @switch($document->sunat_estado)
                                @case('PENDIENTE')<span class="badge badge-warning">Pendiente</span>@break
                                @case('ACEPTADO')<span class="badge badge-success">Aceptado</span>@break
                                @case('RECHAZADO')<span class="badge badge-danger">Rechazado</span>@break
                                @default<span class="badge badge-secondary">{{ $document->sunat_estado }}</span>
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">{{ $tipo === 'T' ? 'Destinatario' : 'Proveedor' }}</h3>
            </div>
            <div class="card-body">
                @if($document->entity)
                <table class="table table-bordered">
                    <tr><td><strong>Documento:</strong></td><td>{{ $document->entity->tipo_doc == '6' ? 'RUC' : $document->entity->tipo_doc }}: {{ $document->entity->num_doc }}</td></tr>
                    <tr><td><strong>Razón Social:</strong></td><td>{{ $document->entity->razon_social }}</td></tr>
                    @if($document->entity->direccion)
                    <tr><td><strong>Dirección:</strong></td><td>{{ $document->entity->direccion }}</td></tr>
                    @endif
                </table>
                @else
                <p class="text-muted">Sin datos</p>
                @endif
            </div>
        </div>

        @if($tipo === 'T' && $document->items->isNotEmpty())
        <div class="card card-secondary">
            <div class="card-header"><h3 class="card-title">Items</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Unidad</th></tr></thead>
                    <tbody>
                        @foreach($document->items as $item)
                        <tr>
                            <td>{{ $item->codigo ?? '—' }}</td>
                            <td>{{ $item->descripcion }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>{{ $item->unidad }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($document->sunat_estado === 'PENDIENTE')
        <div class="card">
            <div class="card-body text-center">
                <form action="{{ route('documents.send', [$tipo, $document]) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Enviar a SUNAT?')">
                        <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                    </button>
                </form>
                <a href="{{ route('documents.create', $tipo) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
