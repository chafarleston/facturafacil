
<?php $__env->startSection('title', 'Usuarios'); ?>
<?php $__env->startSection('page_title', 'Usuarios'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Usuarios</h3>
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary btn-sm float-right">Nuevo Usuario</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($u->name); ?></td>
                    <td><?php echo e($u->email); ?></td>
                    <td>
                        <?php if($u->role === 'admin'): ?>
                            <span class="badge badge-primary">Administrador</span>
                        <?php elseif($u->role === 'mozo'): ?>
                            <span class="badge badge-success">Mozo</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Usuario</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('users.edit', $u)); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="<?php echo e(route('users.destroy', $u)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\users\index.blade.php ENDPATH**/ ?>