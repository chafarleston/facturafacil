<?php $__env->startSection('title', 'Editar Mesa'); ?>
<?php $__env->startSection('page_title', 'Editar Mesa'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Mesa</h3>
    </div>
    <form action="<?php echo e(route('restaurant.tables.update', $restaurantTable)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Piso</label>
                        <select name="floor_id" class="form-control" required>
                            <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($floor->id); ?>" <?php echo e($restaurantTable->floor_id == $floor->id ? 'selected' : ''); ?>>
                                <?php echo e($floor->name); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de Mesa</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo e($restaurantTable->name); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Capacidad (personas)</label>
                        <input type="number" name="capacity" class="form-control" value="<?php echo e($restaurantTable->capacity); ?>" min="1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="<?php echo e($restaurantTable->color); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="AVAILABLE" <?php echo e($restaurantTable->status === 'AVAILABLE' ? 'selected' : ''); ?>>Disponible</option>
                            <option value="OCCUPIED" <?php echo e($restaurantTable->status === 'OCCUPIED' ? 'selected' : ''); ?>>Ocupada</option>
                            <option value="RESERVED" <?php echo e($restaurantTable->status === 'RESERVED' ? 'selected' : ''); ?>>Reservada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('restaurant.floors.index', ['company_id' => $restaurantTable->company_id])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\tables\edit.blade.php ENDPATH**/ ?>