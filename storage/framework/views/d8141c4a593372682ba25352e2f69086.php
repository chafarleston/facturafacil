<?php $__env->startSection('title', 'Modo de Pedidos'); ?>
<?php $__env->startSection('page_title', 'Modo de Pedidos'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configuración del Modo de Pedidos</h3>
            </div>
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <span class="badge badge-<?php echo e($orderMode === 'print' ? 'info' : 'secondary'); ?>" style="font-size:16px; padding:8px 20px;">
                        Modo actual: <strong><?php echo e($orderMode === 'print' ? 'IMPRESIÓN 80mm' : 'KDS'); ?></strong>
                    </span>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card card-<?php echo e($orderMode === 'kds' ? 'primary' : 'outline-secondary'); ?> h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-tv fa-4x mb-3 text-<?php echo e($orderMode === 'kds' ? 'primary' : 'secondary'); ?>"></i>
                                <h4>Modo KDS</h4>
                                <p class="text-muted">Los pedidos se muestran en pantallas KDS conectadas por cada zona (Cocina, Bar, etc.)</p>
                                <?php if($orderMode === 'kds'): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> ACTIVO</span>
                                <?php else: ?>
                                <form method="POST" action="<?php echo e(route('restaurant.toggleMode')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-primary mt-2">Activar Modo KDS</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-<?php echo e($orderMode === 'print' ? 'info' : 'outline-secondary'); ?> h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-print fa-4x mb-3 text-<?php echo e($orderMode === 'print' ? 'info' : 'secondary'); ?>"></i>
                                <h4>Modo Impresión 80mm</h4>
                                <p class="text-muted">Los pedidos se imprimen directamente en impresoras térmicas asignadas por zona</p>
                                <?php if($orderMode === 'print'): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> ACTIVO</span>
                                <?php else: ?>
                                <form method="POST" action="<?php echo e(route('restaurant.toggleMode')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-info mt-2">Activar Modo Impresión</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($orderMode === 'print'): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    Asegúrate de tener configuradas las impresoras en
                    <a href="<?php echo e(route('printers.index')); ?>">Gestión de Impresoras</a>
                    con las asignaciones: Cocina-1, Cocina-2, Bar-1 y Precuenta.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\restaurant\mode.blade.php ENDPATH**/ ?>