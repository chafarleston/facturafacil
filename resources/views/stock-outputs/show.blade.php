@extends('layouts.admin')
@section('title', 'Consumo #' . $stockOutput->id)
@section('page_title', 'Consumo Interno #' . $stockOutput->id)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle del Consumo</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Fecha:</strong><br>
                        {{ $stockOutput->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Usuario:</strong><br>
                        {{ $stockOutput->user->name ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Motivo:</strong><br>
                        @php
                            $motivos = [
                                'consumo_cocina' => 'Consumo cocina',
                                'merma' => 'Merma',
                                'degustacion' => 'Degustación',
                                'otro' => $stockOutput->motivo_otro ?? 'Otro',
                            ];
                        @endphp
                        <span class="badge badge-info">{{ $motivos[$stockOutput->motivo] ?? $stockOutput->motivo }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Referencia:</strong><br>
                        {{ $stockOutput->referencia ?? '-' }}
                    </div>
                </div>

                @if($stockOutput->notas)
                <div class="mt-3">
                    <strong>Notas:</strong><br>
                    {{ $stockOutput->notas }}
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Productos</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Stock antes</th>
                            <th>Stock después</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOutput->items as $item)
                        <tr>
                            <td>{{ $item->product->descripcion ?? 'Producto #' . $item->product_id }}</td>
                            <td>{{ number_format($item->cantidad, 4) }}</td>
                            <td>{{ number_format($item->stock_antes, 4) }}</td>
                            <td>{{ number_format($item->stock_despues, 4) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('stock-outputs.print.a4', $stockOutput) }}" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Imprimir A4</a>
        <a href="{{ route('stock-outputs.print.ticket', $stockOutput) }}" class="btn btn-info" target="_blank"><i class="fas fa-receipt"></i> Imprimir Ticket 80mm</a>
        <a href="{{ route('stock-outputs.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection
