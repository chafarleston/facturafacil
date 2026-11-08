@extends('layouts.admin')

@section('title', 'Salud de Pedidos')
@section('page_title', 'Salud de Pedidos')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(session('health_backup'))
            <div class="alert alert-info">
                <i class="fas fa-database"></i> Backup generado:
                <code>{{ session('health_backup') }}</code>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $health['safe_count'] }}</h3>
                <p>Seguros de eliminar</p>
            </div>
            <div class="icon"><i class="fas fa-broom"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $health['completed_no_paid_count'] }}</h3>
                <p>Completados sin pago</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $health['partial_count'] }}</h3>
                <p>Con pago parcial</p>
            </div>
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $health['active_count'] }}</h3>
                <p>Pedidos activos</p>
            </div>
            <div class="icon"><i class="fas fa-utensils"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-heartbeat"></i> Diagnostico</h3>
        <a href="{{ route('order-health.index') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-sync-alt"></i> Actualizar
        </a>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-shield-alt"></i>
            Antes de eliminar, cierre caja/restaurante y evite que los mozos esten operando.
            Se crea un backup automatico; si el backup falla, no se borra nada.
            Nunca se eliminan facturas, items pagados ni pedidos activos.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-broom"></i> Seguros de eliminar</h3>
    </div>
    <div class="card-body">
        @if($health['safe_count'] === 0)
            <p class="text-success mb-0"><i class="fas fa-check-circle"></i> No hay pedidos vacios ni registros huerfanos.</p>
        @else
            <p>
                Se eliminaran:
                <strong>{{ $health['empty_cancelled']->count() }}</strong> pedidos cancelados vacios,
                <strong>{{ $health['orphan_items']->count() }}</strong> items sin pedido y
                <strong>{{ $health['orphan_batches']->count() }}</strong> lotes sin pedido.
            </p>

            @if($health['empty_cancelled']->isNotEmpty())
                <h5>Pedidos CANCELLED vacios</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pedido</th>
                                <th>Mesa</th>
                                <th>Lotes</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['empty_cancelled'] as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->table->name ?? '-' }}</td>
                                    <td>{{ $order->kitchen_batches_count }}</td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($health['orphan_items']->isNotEmpty())
                <h5 class="mt-3">Items sin pedido</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Estado</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['orphan_items'] as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->kitchen_status }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($health['orphan_batches']->isNotEmpty())
                <h5 class="mt-3">Lotes sin pedido</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Destino</th>
                                <th>Estado</th>
                                <th>Enviado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['orphan_batches'] as $batch)
                                <tr>
                                    <td>{{ $batch->id }}</td>
                                    <td>{{ $batch->kds_destination }}</td>
                                    <td>{{ $batch->status }}</td>
                                    <td>{{ $batch->sent_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST" action="{{ route('order-health.cleanup') }}"
                  onsubmit="return confirmHealthCleanup(this, 'Se eliminaran {{ $health['safe_count'] }} registros seguros.')">
                @csrf
                <input type="hidden" name="mode" value="safe">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar seguros
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Completados sin pago (revisar)</h3>
    </div>
    <div class="card-body">
        @if($health['completed_no_paid']->isEmpty())
            <p class="text-success mb-0"><i class="fas fa-check-circle"></i> No hay pedidos completados sin pago.</p>
        @else
            <p class="text-muted">
                Estos pedidos no tienen ninguna factura vinculada. Verifique que realmente sean descartables antes de borrarlos.
            </p>
            <form method="POST" action="{{ route('order-health.cleanup') }}"
                  onsubmit="return confirmHealthCleanup(this, 'Se eliminaran los pedidos seleccionados.')">
                @csrf
                <input type="hidden" name="mode" value="completed">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllNoPaid"></th>
                                <th>ID</th>
                                <th>Pedido</th>
                                <th>Mesa</th>
                                <th>Items impagos</th>
                                <th>Total</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['completed_no_paid'] as $order)
                                <tr>
                                    <td><input type="checkbox" class="no-paid-check" name="orders[]" value="{{ $order->id }}"></td>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->table->name ?? '-' }}</td>
                                    <td>{{ $order->unpaid_items_count }}</td>
                                    <td>S/ {{ number_format((float) $order->total, 2) }}</td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar seleccionados
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt"></i> Protegidos: pago parcial</h3>
    </div>
    <div class="card-body">
        @if($health['completed_partial']->isEmpty() && $health['orphan_paid_items']->isEmpty())
            <p class="text-success mb-0"><i class="fas fa-check-circle"></i> No hay registros protegidos.</p>
        @else
            <p class="text-muted">
                Estos pedidos tienen items pagados o facturas. No se eliminan completos para no perder el historial.
            </p>

            @if($health['completed_partial']->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pedido</th>
                                <th>Mesa</th>
                                <th>Pagados</th>
                                <th>Impagos</th>
                                <th>Total</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['completed_partial'] as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->table->name ?? '-' }}</td>
                                    <td>{{ $order->paid_items_count }}</td>
                                    <td>{{ $order->unpaid_items_count }}</td>
                                    <td>S/ {{ number_format((float) $order->total, 2) }}</td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($health['orphan_paid_items']->isNotEmpty())
                <h5 class="mt-3">Items sin pedido con factura</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Factura</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($health['orphan_paid_items'] as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->paid_invoice_id }}</td>
                                    <td>{{ $item->kitchen_status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-copy"></i> Numeros de pedido duplicados</h3>
    </div>
    <div class="card-body">
        @if($health['duplicate_count'] === 0)
            <p class="text-success mb-0"><i class="fas fa-check-circle"></i> No hay numeros de pedido duplicados.</p>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Hay {{ $health['duplicate_count'] }} grupos de numeros repetidos. No se eliminan automaticamente.
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Empresa</th>
                            <th>Repeticiones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($health['duplicate_groups']->take(20) as $group)
                            <tr>
                                <td>{{ $group->order_number }}</td>
                                <td>{{ $group->company_id }}</td>
                                <td>{{ $group->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    function confirmHealthCleanup(form, message) {
        var value = prompt(message + '\n\nEscriba ELIMINAR para continuar:');
        return value === 'ELIMINAR';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('selectAllNoPaid');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.no-paid-check').forEach(function(checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        }
    });
</script>
@endsection
