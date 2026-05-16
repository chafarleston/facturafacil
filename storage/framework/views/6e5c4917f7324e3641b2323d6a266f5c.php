<?php $__env->startSection('title', 'Editar Usuario'); ?>
<?php $__env->startSection('page_title', 'Editar Usuario'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Usuario: <?php echo e($user->name); ?></h3>
    </div>
    <form action="<?php echo e(route('users.update', $user)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" value="<?php echo e($user->name); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo e($user->email); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Rol Principal</label>
                        <select name="role" class="form-control" required>
                            <option value="user" <?php echo e($user->role === 'user' ? 'selected' : ''); ?>>Usuario</option>
                            <option value="admin" <?php echo e($user->role === 'admin' ? 'selected' : ''); ?>>Administrador</option>
                            <option value="mozo" <?php echo e($user->role === 'mozo' ? 'selected' : ''); ?>>Mozo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Permisos Adicionales</label>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="roles[]" class="custom-control-input" id="role_<?php echo e($r->id); ?>" value="<?php echo e($r->id); ?>" <?php echo e(in_array($r->id, $userRoles) ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="role_<?php echo e($r->id); ?>"><?php echo e($r->name); ?></label>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted">No hay roles disponibles</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\users\edit.blade.php ENDPATH**/ ?>