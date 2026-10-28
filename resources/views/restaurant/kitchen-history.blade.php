@extends('layouts.admin')
@section('title', 'Historial / Métricas KDS')
@section('page_title', 'Historial / Métricas KDS')

@section('content')
@php
    $fmt = function ($seconds) {
        if ($seconds === null) {
            return '-';
        }
        $seconds = (int) $seconds;
        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;
        return $minutes . 'm ' . str_pad($rest, 2, '0', STR_PAD_LEFT) . 's';
    };
@endphp

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Historial y métricas de cocina</h3>
                <div class="card-tools">
                    <a href="{{ route('restaurant.kitchen', ['kds' => 'cocina']) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-tv"></i> Volver al KDS
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('restaurant.kitchenHistory') }}" class="form-inline mb-4">
                    <label class="mr-2">Desde</label>
                    <input type="date" name="from" class="form-control form-control-sm mr-3" value="{{ $from->format('Y-m-d') }}">
                    <label class="mr-2">Hasta</label>
                    <input type="date" name="to" class="form-control form-control-sm mr-3" value="{{ $to->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('restaurant.kitchenHistory') }}" class="btn btn-outline-secondary btn-sm ml-2">Hoy</a>
                </form>

                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $metrics['count'] }}</h3>
                                <p>Despachos</p>
                            </div>
                            <div class="icon"><i class="fas fa-utensils"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $fmt($metrics['avg_queue']) }}</h3>
                                <p>Espera (envío → preparado)</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $fmt($metrics['avg_prep']) }}</h3>
                                <p>Preparación (preparado → terminado)</p>
                            </div>
                            <div class="icon"><i class="fas fa-fire"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $fmt($metrics['avg_total']) }}</h3>
                                <p>Tiempo total promedio</p>
                            </div>
                            <div class="icon"><i class="fas fa-stopwatch"></i></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-1">Distribución del tiempo (promedio)</h6>
                    @if($metrics['queue_ratio'] !== null)
                        <div class="progress" style="height:22px;">
                            <div class="progress-bar bg-warning" style="width: {{ $metrics['queue_ratio'] }}%">
                                Espera {{ $metrics['queue_ratio'] }}%
                            </div>
                            <div class="progress-bar bg-success" style="width: {{ $metrics['prep_ratio'] ?? 0 }}%">
                                Preparación {{ $metrics['prep_ratio'] ?? 0 }}%
                            </div>
                        </div>
                    @else
                        <span class="text-muted">Sin datos suficientes para calcular la distribución.</span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Mesa / Kiosko</th>
                                <th>Estado</th>
                                <th>Enviado</th>
                                <th>Preparado</th>
                                <th>Terminado</th>
                                <th>Espera</th>
                                <th>Preparación</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>
                                    @if($order->order_type === 'kiosko')
                                        <span class="badge badge-secondary">Kiosko</span>
                                    @else
                                        {{ $order->table->name ?? 'Mesa' }}
                                        @if($order->table && $order->table->floor)
                                            <small class="text-muted">({{ $order->table->floor->name }})</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $order->status === 'DELIVERED' ? 'success' : 'secondary' }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $order->sent_to_kitchen_at ? $order->sent_to_kitchen_at->format('d/m H:i:s') : '-' }}</td>
                                <td>{{ $order->preparing_started_at ? $order->preparing_started_at->format('d/m H:i:s') : '-' }}</td>
                                <td>{{ $order->finished_at ? $order->finished_at->format('d/m H:i:s') : '-' }}</td>
                                <td>{{ $fmt($order->queue_seconds) }}</td>
                                <td>{{ $fmt($order->prep_seconds) }}</td>
                                <td><strong>{{ $fmt($order->total_seconds) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay pedidos entregados en el rango seleccionado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    El historial se conserva en la base de datos; esta página solo consulta los pedidos terminados.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
