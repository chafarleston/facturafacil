
<?php $__env->startSection('title', 'Ver Producto'); ?>
<?php $__env->startSection('page_title', 'Ver Producto'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Producto: <?php echo e($product->descripcion); ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-box"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Código Interno</span>
                        <span class="info-box-number"><?php echo e($product->codigo); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-barcode"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Código SUNAT</span>
                        <span class="info-box-number"><?php echo e($product->codigo_sunat ?: 'No asignado'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Precio</span>
                        <span class="info-box-number">S/ <?php echo e(number_format($product->precio, 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tipo Afectación</span>
                        <span class="info-box-number"><?php echo e($product->tipo_afectacion); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-ruler"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unidad de Medida</span>
                        <span class="info-box-number"><?php echo e($product->umedida_codigo); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-<?php echo e($product->estado == 'ACT' ? 'success' : 'danger'); ?>"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number"><?php echo e($product->estado); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Categoría</span>
                        <span class="info-box-number"><?php echo e($product->category->nombre ?? 'Sin categoría'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon <?php echo e($product->stock < 0 ? 'bg-danger' : ($product->stock == 0 ? 'bg-warning' : 'bg-info')); ?>">
                        <i class="fas fa-cubes"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Stock</span>
                        <span class="info-box-number <?php echo e($product->stock < 0 ? 'text-danger font-weight-bold' : ''); ?>">
                            <?php echo e($product->stock); ?>

                            <?php if($product->stock < 0): ?>
                                <small class="text-muted">(Saldo negativo)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-dark"><i class="fas fa-utensils"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Destino KDS</span>
                        <span class="info-box-number">
                            <?php if(($product->kds_destination ?? 'cocina') == 'cocina2'): ?> KDS Cocina 2
                            <?php elseif(($product->kds_destination ?? 'cocina') == 'bar'): ?> KDS Bar
                            <?php else: ?> KDS Cocina
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <div>
            <?php if($prev): ?>
            <a href="<?php echo e(route('products.show', $prev)); ?>" class="btn btn-outline-primary"><i class="fas fa-chevron-left"></i> Anterior</a>
            <?php endif; ?>
            <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
            <a href="<?php echo e(route('products.index')); ?>" class="btn btn-secondary">Volver</a>
            <?php if($next): ?>
            <a href="<?php echo e(route('products.show', $next)); ?>" class="btn btn-outline-primary">Siguiente <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\products\show.blade.php ENDPATH**/ ?>