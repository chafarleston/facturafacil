
<?php $__env->startSection('title', 'Proveedores'); ?>
<?php $__env->startSection('page_title', 'Proveedores'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Proveedores</h3>
        <a href="<?php echo e(route('suppliers.create', ['company_id' => $companyId])); ?>" class="btn btn-primary btn-sm float-right">Nuevo Proveedor</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUC</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($supplier->nombre); ?></td>
                    <td><?php echo e($supplier->ruc); ?></td>
                    <td><?php echo e($supplier->telefono); ?></td>
                    <td><span class="badge badge-<?php echo e($supplier->estado == 'ACT' ? 'success' : 'danger'); ?>"><?php echo e($supplier->estado); ?></span></td>
                    <td>
                        <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="<?php echo e(route('suppliers.destroy', $supplier)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center">No hay proveedores</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\suppliers\index.blade.php ENDPATH**/ ?>