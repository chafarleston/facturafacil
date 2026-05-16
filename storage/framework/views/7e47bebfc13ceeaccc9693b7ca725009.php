<?php $__env->startSection('title', 'Editar Serie'); ?>
<?php $__env->startSection('page_title', 'Editar Serie'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Serie: <?php echo e($serie->serie); ?></h3>
    </div>
    <form method="POST" action="<?php echo e(route('series.update', $serie)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Documento</label>
                        <input type="text" class="form-control" value="<?php echo e($serie->tipo_documento == '01' ? 'Factura' : 'Boleta'); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Serie</label>
                        <input type="text" class="form-control" value="<?php echo e($serie->serie); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número Actual</label>
                        <input type="text" class="form-control" value="<?php echo e($serie->numero_actual + 1); ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Reiniciar desde número</label>
                <input type="number" name="numero_inicio" class="form-control" value="<?php echo e($serie->numero_actual + 1); ?>" min="0" required>
                <small class="text-muted">El próximo documento comenzará desde este número</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?php echo e(route('series.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\series\edit.blade.php ENDPATH**/ ?>