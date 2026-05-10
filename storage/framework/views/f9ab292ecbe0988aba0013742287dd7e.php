
<?php $__env->startSection('title', 'Productos'); ?>
<?php $__env->startSection('page_title', 'Productos'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Productos</h3>
        <div class="card-tools">
          <form method="GET" action="<?php echo e(route('products.index')); ?>" class="form-inline">
            <input type="hidden" name="company_id" value="<?php echo e($companyId ?? null); ?>">
            <input type="text" name="search" class="form-control" placeholder="Buscar..." value="<?php echo e(request('search')); ?>">
            <button type="submit" class="btn btn-secondary ml-1"><i class="fas fa-search"></i></button>
            <?php if(request('search')): ?>
            <a href="<?php echo e(route('products.index', ['company_id' => $companyId ?? null])); ?>" class="btn btn-link ml-1">Limpiar</a>
            <?php endif; ?>
          </form>
          <a href="<?php echo e(route('products.create', ['company_id' => $companyId ?? null])); ?>" class="btn btn-primary btn-sm ml-2">
            <i class="fas fa-plus"></i> Nuevo
          </a>
          <a href="<?php echo e(route('products.import.form', ['company_id' => $companyId ?? null])); ?>" class="btn btn-success btn-sm ml-1">
            <i class="fas fa-file-import"></i> Importar
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Código</th>
              <th>Cód. Barras</th>
              <th>Descripción</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($product->codigo); ?></td>
              <td><?php echo e($product->codigo_barras ?? '-'); ?></td>
              <td><?php echo e($product->descripcion); ?></td>
              <td><?php echo e($product->category->nombre ?? '-'); ?></td>
              <td>S/ <?php echo e(number_format($product->precio, 2)); ?></td>
              <td>
                <?php if($product->stock < 0): ?>
                  <span class="text-danger font-weight-bold"><?php echo e($product->stock); ?></span>
                <?php elseif($product->stock == 0): ?>
                  <span class="text-warning font-weight-bold"><?php echo e($product->stock); ?></span>
                <?php else: ?>
                  <?php echo e($product->stock); ?>

                <?php endif; ?>
              </td>
              <td>
                <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="text-center">No hay productos</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="card-footer"><?php echo e($products->links()); ?></div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views/products/index.blade.php ENDPATH**/ ?>