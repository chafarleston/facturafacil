<?php $__env->startSection('title', 'Editar Permiso'); ?>
<?php $__env->startSection('page_title', 'Editar Permiso'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Permiso</h3>
    </div>
    <form action="<?php echo e(route('permissions.update', $permission)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" value="<?php echo e($permission->name); ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Módulo</label>
                <select name="module" class="form-control" required>
                    <option value="">Seleccionar módulo</option>
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e($permission->module == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control" rows="2"><?php echo e($permission->description); ?></textarea>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" <?php echo e($permission->status ? 'checked' : ''); ?>>
                    <label class="custom-control-label" for="status">Activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?php echo e(route('permissions.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\permissions\edit.blade.php ENDPATH**/ ?>