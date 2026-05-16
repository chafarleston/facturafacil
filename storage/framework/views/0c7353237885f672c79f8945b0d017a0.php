
<?php $__env->startSection('title', 'Compras'); ?>
<?php $__env->startSection('page_title', 'Compras'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Compras</h3>
        <a href="<?php echo e(route('purchases.create', ['company_id' => $companyId])); ?>" class="btn btn-primary btn-sm float-right">Nueva Compra</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($purchase->fecha); ?></td>
                    <td><?php echo e($purchase->tipo_documento); ?> - <?php echo e($purchase->numero_documento); ?></td>
                    <td><?php echo e($purchase->supplier->nombre ?? 'Sin proveedor'); ?></td>
                    <td>S/ <?php echo e(number_format($purchase->total, 2)); ?></td>
                    <td><span class="badge badge-<?php echo e($purchase->estado == 'REGISTRADO' ? 'success' : 'danger'); ?>"><?php echo e($purchase->estado); ?></span></td>
                    <td>
                        <a href="<?php echo e(route('purchases.show', $purchase)); ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        <?php if($purchase->estado == 'REGISTRADO'): ?>
                        <form action="<?php echo e(route('purchases.destroy', $purchase)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Anular compra? Esto restará el stock.')"><i class="fas fa-times"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center">No hay compras</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="card-footer"><?php echo e($purchases->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\purchases\index.blade.php ENDPATH**/ ?>