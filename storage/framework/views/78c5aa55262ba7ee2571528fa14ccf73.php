<?php $__env->startSection('title', 'Cola de Impresión'); ?>
<?php $__env->startSection('page_title', 'Cola de Impresión'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trabajos de Impresión</h3>
                <div class="card-tools">
                    <span class="badge badge-info mr-2">Pendientes: <?php echo e($jobs->where('status', 'pending')->count()); ?></span>
                    <span class="badge badge-success mr-2">Completados: <?php echo e($jobs->where('status', 'completed')->count()); ?></span>
                    <span class="badge badge-danger">Fallidos: <?php echo e($jobs->where('status', 'failed')->count()); ?></span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Impresora</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Creado</th>
                            <th>Error</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($job->id); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($job->job_type == 'invoice' ? 'primary' : ($job->job_type == 'prebill' ? 'info' : 'secondary')); ?>">
                                    <?php echo e($job->job_type); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($job->type === 'network'): ?>
                                    <?php echo e($job->printer_ip); ?>:<?php echo e($job->printer_port); ?>

                                <?php else: ?>
                                    <?php echo e($job->printer_name ?? '—'); ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($job->status == 'completed' ? 'success' : ($job->status == 'failed' ? 'danger' : ($job->status == 'processing' ? 'warning' : 'secondary'))); ?>">
                                    <?php echo e($job->status); ?>

                                </span>
                            </td>
                            <td><?php echo e($job->attempts); ?></td>
                            <td><?php echo e($job->created_at->format('H:i:s')); ?></td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;font-size:11px;">
                                <?php echo e(Str::limit($job->error_message, 40) ?? '—'); ?>

                            </td>
                            <td>
                                <?php if($job->status === 'failed'): ?>
                                <form action="<?php echo e(route('printers.queue.retry', $job)); ?>" method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-xs btn-info"><i class="fas fa-redo"></i></button>
                                </form>
                                <?php endif; ?>
                                <form action="<?php echo e(route('printers.queue.destroy', $job)); ?>" method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay trabajos de impresión</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <?php echo e($jobs->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\print_jobs\index.blade.php ENDPATH**/ ?>