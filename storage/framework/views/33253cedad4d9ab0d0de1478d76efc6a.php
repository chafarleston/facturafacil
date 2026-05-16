<?php $__env->startSection('title', 'Pisos - Restaurante'); ?>
<?php $__env->startSection('page_title', 'Pisos del Restaurante'); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.index', ['company_id' => $companyId])); ?>">Restaurante</a></li>
<li class="breadcrumb-item active">Pisos</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Pisos</h3>
        <div class="card-tools">
            <a href="<?php echo e(route('restaurant.floors.create', ['company_id' => $companyId])); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Piso
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if($floors->isEmpty()): ?>
        <div class="alert alert-info">
            No hay pisos configurados. <a href="<?php echo e(route('restaurant.floors.create', ['company_id' => $companyId])); ?>">Crear el primer piso</a>
        </div>
        <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Mesas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($floor->order); ?></td>
                    <td><?php echo e($floor->name); ?></td>
                    <td><?php echo e($floor->tables->count()); ?></td>
                    <td>
                        <?php if($floor->status === 'ACTIVE'): ?>
                        <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                        <span class="badge badge-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('restaurant.tables.create', ['company_id' => $companyId, 'floor_id' => $floor->id])); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-chair"></i> Mesas
                        </a>
                        <a href="<?php echo e(route('restaurant.floors.edit', $floor)); ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="<?php echo e(route('restaurant.floors.destroy', $floor)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este piso?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\floors\index.blade.php ENDPATH**/ ?>