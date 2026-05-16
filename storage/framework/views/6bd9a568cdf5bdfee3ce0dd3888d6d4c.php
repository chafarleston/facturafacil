
<?php $__env->startSection('title', 'Ver Compra'); ?>
<?php $__env->startSection('page_title', 'Ver Compra'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo e($purchase->tipo_documento); ?> - <?php echo e($purchase->numero_documento); ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Fecha</span>
                        <span class="info-box-number"><?php echo e($purchase->fecha); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Proveedor</span>
                        <span class="info-box-number"><?php echo e($purchase->supplier->nombre ?? 'Sin proveedor'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number">S/ <?php echo e(number_format($purchase->total, 2)); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-<?php echo e($purchase->estado == 'REGISTRADO' ? 'success' : 'danger'); ?>"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number"><?php echo e($purchase->estado); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <h4 class="mt-4">Productos</h4>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $purchase->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product->descripcion); ?></td>
                    <td class="text-right"><?php echo e($item->cantidad); ?></td>
                    <td class="text-right">S/ <?php echo e(number_format($item->precio_unitario, 2)); ?></td>
                    <td class="text-right">S/ <?php echo e(number_format($item->subtotal, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <a href="<?php echo e(route('purchases.index', ['company_id' => $purchase->company_id])); ?>" class="btn btn-secondary">Volver</a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\purchases\show.blade.php ENDPATH**/ ?>