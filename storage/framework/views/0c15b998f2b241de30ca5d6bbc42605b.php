
<?php $__env->startSection('title', 'Empresas'); ?>
<?php $__env->startSection('page_title', 'Empresas'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Empresas</h3>
        <div class="card-tools">
          <a href="<?php echo e(route('companies.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Empresa
          </a>
          <form action="<?php echo e(route('sunat.padron.download')); ?>" method="POST" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-info btn-sm" title="Descargar padrón SUNAT">
              <i class="fas fa-download"></i> Descargar padrón SUNAT
            </button>
          </form>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>RUC</th>
              <th>Razón Social</th>
              <th>Email</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($company->ruc); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($company->razon_social); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($company->email); ?></td>
                    <td>
                <?php if($company->estado === 'ACTIVO'): ?>
                  <span class="badge badge-success">ACTIVO</span>
                <?php else: ?>
                  <span class="badge badge-secondary"><?php echo e($company->estado); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?php echo e(route('companies.show', $company)); ?>" class="btn btn-info btn-xs" title="Ver">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="<?php echo e(route('companies.edit', $company)); ?>" class="btn btn-warning btn-xs" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>
                <?php if(!$company->is_main): ?>
                <form action="<?php echo e(route('companies.setMain', $company)); ?>" method="POST" style="display:inline;">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-primary btn-xs" title="Establecer principal" onclick="return confirm('¿Establecer como empresa principal?')">
                    <i class="fas fa-star"></i>
                  </button>
                </form>
                <?php endif; ?>
                <form action="<?php echo e(route('companies.destroy', $company)); ?>" method="POST" style="display:inline;">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="btn btn-danger btn-xs" title="Eliminar" onclick="return confirm('¿Eliminar empresa?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center">No hay empresas</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\companies\index.blade.php ENDPATH**/ ?>