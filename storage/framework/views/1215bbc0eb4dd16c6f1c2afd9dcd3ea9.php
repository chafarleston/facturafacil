<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page_title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
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
        <h4><i class="fas fa-calendar-day"></i> Resumen del Día</h4>
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
                        <h5 class="mb-0">S/ <?php echo e(number_format($stats['ventas_hoy'], 2)); ?></h5>
                        <small class="text-muted">Ventas de Hoy</small>
                        <?php if($stats['crecimiento'] != 0): ?>
                            <span class="growth-badge ml-2 <?php echo e($stats['crecimiento'] >= 0 ? 'growth-up' : 'growth-down'); ?>">
                                <i class="fas fa-arrow-<?php echo e($stats['crecimiento'] >= 0 ? 'up' : 'down'); ?>"></i>
                                <?php echo e(abs(number_format($stats['crecimiento'], 1))); ?>%
                            </span>
                        <?php endif; ?>
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
                        <h5 class="mb-0"><?php echo e($stats['total']); ?></h5>
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
                        <h5 class="mb-0"><?php echo e($stats['aceptados']); ?></h5>
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
                        <h5 class="mb-0"><?php echo e($stats['pendientes']); ?></h5>
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
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Ventas de los Últimos 7 Días</h5>
                    <span class="text-muted">Total: <strong>S/ <?php echo e(number_format(collect($ventasPorDia)->sum('monto'), 2)); ?></strong></span>
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
                        <strong><?php echo e($stats['facturas']); ?></strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo e($stats['total'] > 0 ? ($stats['facturas'] / $stats['total']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-receipt text-success"></i> Boletas</span>
                        <strong><?php echo e($stats['boletas']); ?></strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?php echo e($stats['total'] > 0 ? ($stats['boletas'] / $stats['total']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-file-alt text-warning"></i> Notas de Venta</span>
                        <strong><?php echo e($stats['notas_venta']); ?></strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo e($stats['total'] > 0 ? ($stats['notas_venta'] / $stats['total']) * 100 : 0); ?>%"></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Productos</span>
                    <strong><?php echo e($stats['total_productos']); ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Clientes</span>
                    <strong><?php echo e($stats['total_clientes']); ?></strong>
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
                <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="top-product-item px-3">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-primary mr-2"><?php echo e($index + 1); ?></span>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold"><?php echo e($product->descripcion); ?></div>
                                <small class="text-muted"><?php echo e($product->total_vendido); ?> unidades - S/ <?php echo e(number_format($product->total_monto, 2)); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-3 text-center text-muted">Sin ventas este mes</div>
                <?php endif; ?>
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
                        <?php $__empty_1 = true; $__currentLoopData = $recentInvoices->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="text-muted"><?php echo e($invoice->document_type_name); ?></span><br>
                                <strong><?php echo e($invoice->full_number); ?></strong>
                            </td>
                            <td><?php echo e($invoice->customer->nombre ?? '-'); ?></td>
                            <td class="text-right">S/ <?php echo e(number_format($invoice->total, 2)); ?></td>
                            <td>
                                <?php switch($invoice->sunat_estado):
                                    case ('ACEPTADO'): ?><span class="badge badge-success">✓</span><?php break; ?>
                                    <?php case ('PENDIENTE'): ?><span class="badge badge-warning">⏳</span><?php break; ?>
                                    <?php case ('ENVIADO'): ?><span class="badge badge-info">↗</span><?php break; ?>
                                    <?php case ('RECHAZADO'): ?><span class="badge badge-danger">✗</span><?php break; ?>
                                    <?php default: ?><span class="badge badge-secondary">-</span>
                                <?php endswitch; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">Sin documentos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode(collect($ventasPorDia)->pluck('dia')); ?>,
    datasets: [{
      label: 'Ventas',
      data: <?php echo json_encode(collect($ventasPorDia)->pluck('monto')); ?>,
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
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\dashboard.blade.php ENDPATH**/ ?>