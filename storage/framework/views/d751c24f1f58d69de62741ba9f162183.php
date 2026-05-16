<?php $__env->startSection('title', 'Editar Rol'); ?>
<?php $__env->startSection('page_title', 'Editar Rol'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Rol: <?php echo e($role->name); ?></h3>
    </div>
    <form action="<?php echo e(route('roles.update', $role)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" value="<?php echo e($role->name); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo e($role->description); ?></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" <?php echo e($role->status ? 'checked' : ''); ?>>
                            <label class="custom-control-label" for="status">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Permisos</label>
                        <div class="permissions-grid" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="module-section mb-3">
                                <h6 class="text-primary font-weight-bold"><?php echo e($module); ?></h6>
                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="permissions[]" class="custom-control-input" id="perm_<?php echo e($permission->id); ?>" value="<?php echo e($permission->id); ?>" <?php echo e(in_array($permission->id, $rolePermissions) ? 'checked' : ''); ?>>
                                    <label class="custom-control-label" for="perm_<?php echo e($permission->id); ?>"><?php echo e($permission->name); ?></label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?php echo e(route('roles.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\roles\edit.blade.php ENDPATH**/ ?>