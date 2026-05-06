@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

<div class="row">
  <!-- Total Comprobantes -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $stats['total'] }}</h3>
        <p>Total Comprobantes</p>
      </div>
      <div class="icon">
        <i class="fas fa-file-invoice"></i>
      </div>
    </div>
  </div>
  
  <!-- Aceptados SUNAT -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $stats['aceptados'] }}</h3>
        <p>Aceptados SUNAT</p>
      </div>
      <div class="icon">
        <i class="fas fa-check-circle"></i>
      </div>
    </div>
  </div>
  
  <!-- Pendientes -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $stats['pendientes'] }}</h3>
        <p>Pendientes</p>
      </div>
      <div class="icon">
        <i class="fas fa-clock"></i>
      </div>
    </div>
  </div>
  
  <!-- Total Ventas -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3>S/ {{ number_format($stats['total_ventas'], 2) }}</h3>
        <p>Total Ventas</p>
      </div>
      <div class="icon">
        <i class="fas fa-dollar-sign"></i>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Ventas Últimos 7 días</h3>
      </div>
      <div class="card-body">
        <canvas id="salesChart" style="min-height: 250px;"></canvas>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Documentos por Tipo</h3>
      </div>
      <div class="card-body">
        <div class="progress-group">
          <span class="progress-text">Facturas</span>
          <span class="float-right"><b>{{ $stats['facturas'] }}</b></span>
          <div class="progress progress-sm">
            <div class="progress-bar bg-primary" style="width: {{ $stats['total'] > 0 ? ($stats['facturas'] / $stats['total']) * 100 : 0 }}%"></div>
          </div>
        </div>
        <div class="progress-group">
          <span class="progress-text">Boletas</span>
          <span class="float-right"><b>{{ $stats['boletas'] }}</b></span>
          <div class="progress progress-sm">
            <div class="progress-bar bg-success" style="width: {{ $stats['total'] > 0 ? ($stats['boletas'] / $stats['total']) * 100 : 0 }}%"></div>
          </div>
        </div>
        <div class="progress-group">
          <span class="progress-text">Notas de Crédito</span>
          <span class="float-right"><b>{{ $stats['notas_credito'] }}</b></span>
          <div class="progress progress-sm">
            <div class="progress-bar bg-warning" style="width: {{ $stats['total'] > 0 ? ($stats['notas_credito'] / $stats['total']) * 100 : 0 }}%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Documents -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Documentos Recientes</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentInvoices as $invoice)
            <tr>
              <td>{{ $invoice->document_type_name }} {{ $invoice->full_number }}</td>
              <td>{{ $invoice->customer->nombre ?? '-' }}</td>
              <td>{{ $invoice->fecha_emision }}</td>
              <td>S/ {{ number_format($invoice->total, 2) }}</td>
              <td>
                @switch($invoice->sunat_estado)
                  @case('ACEPTADO')
                    <span class="badge badge-success">Aceptado</span>
                    @break
                  @case('PENDIENTE')
                    <span class="badge badge-warning">Pendiente</span>
                    @break
                  @case('ENVIADO')
                    <span class="badge badge-info">Enviado</span>
                    @break
                  @case('RECHAZADO')
                    <span class="badge badge-danger">Rechazado</span>
                    @break
                  @default
                    <span class="badge badge-secondary">{{ $invoice->sunat_estado ?? 'Sin estado' }}</span>
                @endswitch
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">No hay documentos</td>
            </tr>
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
    labels: {!! json_encode(collect($ventasPorDia)->pluck('dia')) !!},
    datasets: [{
      label: 'Ventas',
      data: {!! json_encode(collect($ventasPorDia)->pluck('monto')) !!},
      backgroundColor: 'rgba(60, 141, 188, 0.8)',
      borderColor: 'rgba(60, 141, 188, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return 'S/ ' + value.toFixed(2);
          }
        }
      }
    }
  }
});
</script>
@endpush

@endsection