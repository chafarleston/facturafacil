@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<style>
.dashboard-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.dashboard-card:hover {
    transform: translateY(-2px);
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.growth-badge {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
}
.growth-up { background: #d4edda; color: #155724; }
.growth-down { background: #f8d7da; color: #721c24; }
.top-product-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}
.top-product-item:last-child { border-bottom: none; }
.chart-container { position: relative; height: 250px; }
</style>

<div class="row">
    <div class="col-12 mb-3">
        <h4><i class="fas fa-calendar-alt"></i> Resumen del Mes</h4>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success text-white">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="mb-0">S/ {{ number_format($stats['ventas_mes'], 2) }}</h5>
                        <small class="text-muted">Ventas del Mes</small>
                        @if($stats['crecimiento'] != 0)
                            <span class="growth-badge ml-2 {{ $stats['crecimiento'] >= 0 ? 'growth-up' : 'growth-down' }}">
                                <i class="fas fa-arrow-{{ $stats['crecimiento'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs(number_format($stats['crecimiento'], 1)) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary text-white">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="mb-0">{{ $stats['total'] }}</h5>
                        <small class="text-muted">Total Documentos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="mb-0">{{ $stats['aceptados'] }}</h5>
                        <small class="text-muted">Aceptados SUNAT</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning text-white">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="mb-0">{{ $stats['pendientes'] }}</h5>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card dashboard-card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Ventas de los Últimos 30 Días</h5>
                    <span class="text-muted">Total: <strong>S/ {{ number_format(collect($monthlySales)->sum('monto'), 2) }}</strong></span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-3">
        <div class="card dashboard-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie text-primary"></i> Distribución</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-file-invoice text-primary"></i> Facturas</span>
                        <strong>{{ $stats['facturas'] }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $stats['total'] > 0 ? ($stats['facturas'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-receipt text-success"></i> Boletas</span>
                        <strong>{{ $stats['boletas'] }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['total'] > 0 ? ($stats['boletas'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-file-alt text-warning"></i> Notas de Venta</span>
                        <strong>{{ $stats['notas_venta'] }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['total'] > 0 ? ($stats['notas_venta'] / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Productos</span>
                    <strong>{{ $stats['total_productos'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Clientes</span>
                    <strong>{{ $stats['total_clientes'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-trophy text-warning"></i> Productos Más Vendidos (Mes)</h5>
            </div>
            <div class="card-body p-0">
                @forelse($topProducts as $index => $product)
                    <div class="top-product-item px-3">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-primary mr-2">{{ $index + 1 }}</span>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $product->descripcion }}</div>
                                <small class="text-muted">{{ $product->total_vendido }} unidades - S/ {{ number_format($product->total_monto, 2) }}</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted">Sin ventas este mes</div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock text-info"></i> Documentos Recientes</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Documento</th>
                            <th>Cliente</th>
                            <th class="text-right">Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices->take(5) as $invoice)
                        <tr>
                            <td>
                                <span class="text-muted">{{ $invoice->document_type_name }}</span><br>
                                <strong>{{ $invoice->full_number }}</strong>
                            </td>
                            <td>{{ $invoice->customer->nombre ?? '-' }}</td>
                            <td class="text-right">S/ {{ number_format($invoice->total, 2) }}</td>
                            <td>
                                @switch($invoice->sunat_estado)
                                    @case('ACEPTADO')<span class="badge badge-success">✓</span>@break
                                    @case('PENDIENTE')<span class="badge badge-warning">⏳</span>@break
                                    @case('ENVIADO')<span class="badge badge-info">↗</span>@break
                                    @case('RECHAZADO')<span class="badge badge-danger">✗</span>@break
                                    @default<span class="badge badge-secondary">-</span>
                                @endswitch
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Sin documentos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
  type: 'bar',
  data: {
    labels: {!! json_encode(collect($monthlySales)->pluck('dia')) !!},
    datasets: [{
      label: 'Ventas',
      data: {!! json_encode(collect($monthlySales)->pluck('monto')) !!},
      backgroundColor: 'rgba(0, 102, 204, 0.8)',
      borderColor: 'rgba(0, 102, 204, 1)',
      borderWidth: 1,
      borderRadius: 5,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: {
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return 'S/ ' + value.toLocaleString('es-PE', {minimumFractionDigits: 0});
          }
        }
      }
    }
  }
});
</script>
@endpush

@endsection