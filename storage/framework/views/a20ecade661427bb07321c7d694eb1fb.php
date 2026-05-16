
<?php $__env->startSection('title', 'Clientes'); ?>
<?php $__env->startSection('page_title', 'Clientes'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Clientes</h3>
        <div class="card-tools">
          <a href="<?php echo e(route('customers.create', ['company_id' => $companyId ?? null])); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nuevo Cliente
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($customer->documento_tipo == '1' ? 'DNI' : 'RUC'); ?>: <?php echo e($customer->documento_numero); ?></td>
              <td><?php echo e($customer->nombre); ?></td>
              <td><?php echo e($customer->email); ?></td>
              <td><?php echo e($customer->telefono); ?></td>
              <td>
                <a href="<?php echo e(route('customers.show', $customer)); ?>" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                <a href="<?php echo e(route('customers.edit', $customer)); ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center">No hay clientes</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="card-footer"><?php echo e($customers->links()); ?></div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\customers\index.blade.php ENDPATH**/ ?>