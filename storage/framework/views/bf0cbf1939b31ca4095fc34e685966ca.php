
<?php $__env->startSection('title', 'Editar Categoría'); ?>
<?php $__env->startSection('page_title', 'Editar Categoría'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Categoría</h3>
    </div>
    <form method="POST" action="<?php echo e(route('categories.update', $category)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo e($category->nombre); ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?php echo e($category->descripcion); ?></textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT" <?php echo e($category->estado == 'ACT' ? 'selected' : ''); ?>>Activo</option>
                    <option value="INA" <?php echo e($category->estado == 'INA' ? 'selected' : ''); ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('categories.index', ['company_id' => $category->company_id])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\categories\edit.blade.php ENDPATH**/ ?>