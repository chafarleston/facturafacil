
<?php $__env->startSection('title', 'Nueva Categoría'); ?>
<?php $__env->startSection('page_title', 'Nueva Categoría'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Categoría</h3>
    </div>
    <form method="POST" action="<?php echo e(route('categories.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Servicios, Productos, etc.">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT">Activo</option>
                    <option value="INA">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('categories.index', ['company_id' => $companyId])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\categories\create.blade.php ENDPATH**/ ?>