<?php $__env->startSection('title', 'Nueva Mesa'); ?>
<?php $__env->startSection('page_title', 'Nueva Mesa'); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.index', ['company_id' => $companyId])); ?>">Restaurante</a></li>
<li class="breadcrumb-item"><a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $companyId])); ?>">Pisos</a></li>
<li class="breadcrumb-item active">Nueva Mesa</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Crear Nueva Mesa</h3>
    </div>
    <form action="<?php echo e(route('restaurant.tables.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Piso</label>
                        <select name="floor_id" class="form-control" required>
                            <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($floor->id); ?>" <?php echo e(isset($floorId) && $floorId == $floor->id ? 'selected' : ''); ?>>
                                <?php echo e($floor->name); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de Mesa</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Mesa 1, VIP 1, etc.">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacidad (personas)</label>
                        <input type="number" name="capacity" class="form-control" value="4" min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="#28a745">
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\tables\create.blade.php ENDPATH**/ ?>