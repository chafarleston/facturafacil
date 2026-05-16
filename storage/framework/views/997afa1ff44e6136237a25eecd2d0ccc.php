
<?php $__env->startSection('title', 'Caja'); ?>
<?php $__env->startSection('page_title', 'Caja'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <?php if($cajaAbierta): ?>
        <div class="alert alert-success">
            <h4><i class="fas fa-cash-register"></i> Caja Abierta</h4>
            <p>Fecha apertura: <?php echo e($cajaAbierta->fecha_apertura ? $cajaAbierta->fecha_apertura->format('d/m/Y H:i') : '-'); ?></p>
            <p>Monto apertura: S/ <?php echo e(number_format($cajaAbierta->monto_apertura, 2)); ?></p>
            <form method="POST" action="<?php echo e(route('cashregisters.close')); ?>" class="mt-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="cashregister_id" value="<?php echo e($cajaAbierta->id); ?>">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Monto de cierre</label>
                            <input type="number" name="monto_cierre" class="form-control" step="0.01" placeholder="S/ total en caja" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="1" placeholder="Notas adicionales"></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-lock"></i> Cerrar Caja
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Abrir Caja</h3>
            </div>
            <form method="POST" action="<?php echo e(route('cashregisters.open')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Monto de apertura (efectivo inicial)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" name="monto_apertura" class="form-control" step="0.01" min="0" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-cash-register"></i> Abrir Caja</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de Cajas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Ventas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $cajas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caja): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y') : '-'); ?></td>
                            <td><?php echo e($caja->user->name); ?></td>
                            <td>S/ <?php echo e(number_format($caja->monto_apertura, 2)); ?></td>
                            <td><?php echo e($caja->monto_cierre ? 'S/ ' . number_format($caja->monto_cierre, 2) : '-'); ?></td>
                            <td><?php echo e($caja->cantidad_ventas); ?></td>
                            <td>S/ <?php echo e(number_format($caja->total_ventas, 2)); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($caja->estado == 'ABIERTA' ? 'success' : 'secondary'); ?>">
                                    <?php echo e($caja->estado); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($caja->estado === 'CERRADA'): ?>
                                <a href="<?php echo e(route('cashregisters.show', $caja)); ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="<?php echo e(route('cashregisters.pdf', $caja)); ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> A4</a>
                                <a href="<?php echo e(route('cashregisters.ticket', $caja)); ?>" class="btn btn-warning btn-sm" target="_blank"><i class="fas fa-print"></i> 80mm</a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center">No hay cajas registradas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="card-footer"><?php echo e($cajas->links()); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\cashregisters\index.blade.php ENDPATH**/ ?>