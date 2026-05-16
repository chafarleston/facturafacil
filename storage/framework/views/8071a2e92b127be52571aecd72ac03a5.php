
<?php $__env->startSection('title', 'Resumen de Caja'); ?>
<?php $__env->startSection('page_title', 'Resumen de Caja'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Resumen de Caja #<?php echo e($cashregister->id); ?></h3>
                <div class="card-tools float-right">
                    <a href="<?php echo e(route('cashregisters.pdf', $cashregister)); ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF A4
                    </a>
                    <a href="<?php echo e(route('cashregisters.ticket', $cashregister)); ?>" class="btn btn-warning btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Ticket 80mm
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha Apertura</span>
                                <span class="info-box-number"><?php echo e($cashregister->fecha_apertura ? $cashregister->fecha_apertura->format('d/m/Y H:i') : '-'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Monto Apertura</span>
                                <span class="info-box-number">S/ <?php echo e(number_format($cashregister->monto_apertura, 2)); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Monto Cierre</span>
                                <span class="info-box-number">S/ <?php echo e(number_format($cashregister->monto_cierre ?? 0, 2)); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Usuario</span>
                                <span class="info-box-number"><?php echo e($cashregister->user->name); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="mt-4">Resumen por Tipo de Documento</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h5 class="card-title">Facturas</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary"><?php echo e($facturas->count()); ?></h2>
                <p>ventas</p>
                <h4 class="text-success">S/ <?php echo e(number_format($facturas->sum('total'), 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h5 class="card-title">Boletas</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-info"><?php echo e($boletas->count()); ?></h2>
                <p>ventas</p>
                <h4 class="text-success">S/ <?php echo e(number_format($boletas->sum('total'), 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
                <h5 class="card-title">Notas de Venta</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-warning"><?php echo e($nvs->count()); ?></h2>
                <p>ventas</p>
                <h4 class="text-success">S/ <?php echo e(number_format($nvs->sum('total'), 2)); ?></h4>
            </div>
        </div>
    </div>
</div>

<h4 class="mt-4">Resumen por Método de Pago</h4>
<div class="row">
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Efectivo</h5>
                <h4>S/ <?php echo e(number_format($ventasEfectivo, 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Tarjeta</h5>
                <h4>S/ <?php echo e(number_format($ventasTarjeta, 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Yape</h5>
                <h4>S/ <?php echo e(number_format($ventasYape, 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Plin</h5>
                <h4>S/ <?php echo e(number_format($ventasPlin, 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Otro</h5>
                <h4>S/ <?php echo e(number_format($ventasOtro, 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success">
            <div class="card-body text-center text-white">
                <h5>TOTAL</h5>
                <h4>S/ <?php echo e(number_format($totalMetodos, 2)); ?></h4>
            </div>
        </div>
    </div>
</div>

<?php if(count($categoriasVentas) > 0): ?>
<h4 class="mt-4">Resumen por Categoría</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Categoría</th>
                <th class="text-right">Transacciones</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $categoriasVentas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($categoria); ?></td>
                <td class="text-right"><?php echo e($data['cantidad']); ?></td>
                <td class="text-right">S/ <?php echo e(number_format($data['total'], 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if(count($productosVendidos) > 0): ?>
<h4 class="mt-4">Productos Vendidos</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Producto</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $productosVendidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($producto); ?></td>
                <td class="text-right"><?php echo e(number_format($data['cantidad'], 2)); ?></td>
                <td class="text-right">S/ <?php echo e(number_format($data['total'], 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<h4 class="mt-4">Lista de Comprobantes</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Documento</th>
                <th>Cliente</th>
                <th class="text-right">Total</th>
                <th>Método Pago</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $ventas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($venta->full_number); ?></td>
                <td><?php echo e($venta->customer->nombre ?? 'Cliente Varios'); ?></td>
                <td class="text-right">S/ <?php echo e(number_format($venta->total, 2)); ?></td>
                <td><?php echo e($venta->metodo_pago ?? 'Efectivo'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<?php if(count($lineasEliminadas) > 0): ?>
<div class="mt-4">
    <h4>Reporte de Líneas Eliminadas</h4>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Estado anterior</th>
                    <th>Eliminado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $lineasEliminadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product_name); ?></td>
                    <td><?php echo e(number_format($item->quantity, 0)); ?></td>
                    <td><?php echo e($item->cancelled_from); ?></td>
                    <td><?php echo e($item->cancelled_at ? $item->cancelled_at->format('d/m/Y H:i') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="mt-4">
    <a href="<?php echo e(route('cashregisters.index')); ?>" class="btn btn-secondary">Volver</a>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\cashregisters\show.blade.php ENDPATH**/ ?>