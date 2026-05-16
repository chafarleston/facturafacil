<?php $__env->startSection('title', 'Editar Piso'); ?>
<?php $__env->startSection('page_title', 'Editar Piso'); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.index', ['company_id' => $floor->company_id])); ?>">Restaurante</a></li>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $floor->company_id])); ?>">Pisos</a></li>
<li class="breadcrumb-item active">Editar</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Piso</h3>
    </div>
    <form action="<?php echo e(route('restaurant.floors.update', $floor)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="form-group">
                <label>Nombre del Piso</label>
                <input type="text" name="name" class="form-control" required value="<?php echo e($floor->name); ?>">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Orden de Visualización</label>
                        <input type="number" name="order" class="form-control" value="<?php echo e($floor->order); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="ACTIVE" <?php echo e($floor->status === 'ACTIVE' ? 'selected' : ''); ?>>Activo</option>
                            <option value="INACTIVE" <?php echo e($floor->status === 'INACTIVE' ? 'selected' : ''); ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $floor->company_id])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\floors\edit.blade.php ENDPATH**/ ?>