<?php $__env->startSection('title', 'Nuevo Piso'); ?>
<?php $__env->startSection('page_title', 'Nuevo Piso'); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.index', ['company_id' => $companyId])); ?>">Restaurante</a></li>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $companyId])); ?>">Pisos</a></li>
<li class="breadcrumb-item active">Nuevo</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Crear Nuevo Piso</h3>
    </div>
    <form action="<?php echo e(route('restaurant.floors.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
        <div class="card-body">
            <div class="form-group">
                <label>Nombre del Piso</label>
                <input type="text" name="name" class="form-control" required placeholder="Ej: Primer Piso, Terraza, etc.">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Orden de Visualización</label>
                        <input type="number" name="order" class="form-control" placeholder="1">
                        <small class="text-muted">Dejar en blanco para agregar al final</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="ACTIVE">Activo</option>
                            <option value="INACTIVE">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $companyId])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\floors\create.blade.php ENDPATH**/ ?>