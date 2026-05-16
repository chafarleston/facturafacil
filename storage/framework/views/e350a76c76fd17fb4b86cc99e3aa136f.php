<?php $__env->startSection('title', 'Roles'); ?>
<?php $__env->startSection('page_title', 'Roles'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Roles</h3>
        <a href="<?php echo e(route('roles.create')); ?>" class="btn btn-primary btn-sm float-right">Nuevo Rol</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Permisos</th>
                    <th>Usuarios</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($role->name); ?></td>
                    <td><span class="badge badge-secondary"><?php echo e($role->slug); ?></span></td>
                    <td><?php echo e($role->description ?: '-'); ?></td>
                    <td><span class="badge badge-info"><?php echo e($role->permissions->count()); ?></span></td>
                    <td><span class="badge badge-primary"><?php echo e($role->users->count()); ?></span></td>
                    <td>
                        <?php if($role->is_system): ?>
                            <span class="badge badge-danger">Sistema</span>
                        <?php elseif($role->status): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('roles.edit', $role)); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <?php if(!$role->is_system): ?>
                            <form action="<?php echo e(route('roles.destroy', $role)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar rol?')"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\roles\index.blade.php ENDPATH**/ ?>