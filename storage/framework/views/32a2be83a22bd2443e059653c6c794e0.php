
<?php $__env->startSection('title', 'Nueva Serie'); ?>
<?php $__env->startSection('page_title', 'Nueva Serie'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Serie</h3>
    </div>
    <form method="POST" action="<?php echo e(route('series.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">
        <div class="card-body">
            <div class="form-group">
                <label>Tipo de Documento</label>
                <select name="tipo_documento" class="form-control" required>
                    <option value="01">Factura</option>
                    <option value="03">Boleta</option>
                </select>
            </div>
            <div class="form-group">
                <label>Número de Serie</label>
                <input type="text" name="serie" class="form-control" placeholder="Ej: F001, B001" maxlength="4" required>
                <small class="text-muted">Ingrese hasta 4 caracteres (ej: F001, B001)</small>
            </div>
            <div class="form-group">
                <label>Número de Inicio</label>
                <input type="number" name="numero_inicio" class="form-control" value="1" min="0" required>
                <small class="text-muted">El primer documento comenzará desde este número</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear Serie</button>
            <a href="<?php echo e(route('series.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\series\create.blade.php ENDPATH**/ ?>