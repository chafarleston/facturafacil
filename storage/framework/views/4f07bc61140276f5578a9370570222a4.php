
<?php $__env->startSection('title', 'Editar Proveedor'); ?>
<?php $__env->startSection('page_title', 'Editar Proveedor'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Proveedor</h3>
    </div>
    <form method="POST" action="<?php echo e(route('suppliers.update', $supplier)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <div class="card-body">
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" value="<?php echo e($supplier->nombre); ?>" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" value="<?php echo e($supplier->ruc); ?>" class="form-control" maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="<?php echo e($supplier->telefono); ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?php echo e($supplier->direccion); ?>" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($supplier->email); ?>" class="form-control">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT" <?php echo e($supplier->estado == 'ACT' ? 'selected' : ''); ?>>Activo</option>
                    <option value="INA" <?php echo e($supplier->estado == 'INA' ? 'selected' : ''); ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('suppliers.index', ['company_id' => $supplier->company_id])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\suppliers\edit.blade.php ENDPATH**/ ?>