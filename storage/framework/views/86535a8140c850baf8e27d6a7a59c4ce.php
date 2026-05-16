
<?php $__env->startSection('title', 'Series'); ?>
<?php $__env->startSection('page_title', 'Series'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Series</h3>
        <div class="card-tools">
          <a href="<?php echo e(route('series.create', ['company_id' => $companyId ?? null])); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Serie
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Serie</th>
              <th>Tipo</th>
              <th>Último Número</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $series; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($serie->serie); ?></td>
              <td><?php echo e($serie->tipo_documento == '01' ? 'Factura' : 'Boleta'); ?></td>
              <td><?php echo e($serie->numero_actual + 1); ?></td>
              <td>
                <?php if($serie->estado === 'ACTIVO'): ?>
                  <span class="badge badge-success">ACTIVO</span>
                <?php else: ?>
                  <span class="badge badge-secondary"><?php echo e($serie->estado); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?php echo e(route('series.edit', $serie)); ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center">No hay series</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\series\index.blade.php ENDPATH**/ ?>