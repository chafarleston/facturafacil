<?php $__env->startSection('title', 'Crear Permiso'); ?>
<?php $__env->startSection('page_title', 'Crear Permiso'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Permiso</h3>
    </div>
    <form action="<?php echo e(route('permissions.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" class="form-control" placeholder="Ej: Ver Productos" required>
            </div>
            <div class="form-group">
                <label>Módulo</label>
                <select name="module" class="form-control" required>
                    <option value="">Seleccionar módulo</option>
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" checked>
                    <label class="custom-control-label" for="status">Activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="<?php echo e(route('permissions.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\permissions\create.blade.php ENDPATH**/ ?>