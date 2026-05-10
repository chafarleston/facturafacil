<?php if($paginator->hasPages()): ?>
    <nav>
        <div class="d-flex justify-content-between align-items-center">
            <div style="font-size: 12px; color: #666;">
                Mostrando <?php echo e($paginator->firstItem()); ?> a <?php echo e($paginator->lastItem()); ?> de <?php echo e($paginator->total()); ?> resultados
            </div>
            <div style="display: flex; gap: 4px; align-items: center;">
                <?php if($paginator->onFirstPage()): ?>
                    <button class="btn btn-sm btn-default" disabled style="font-size: 12px; padding: 4px 10px; cursor: not-allowed;">Anterior</button>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e($paginator->previousPageUrl()); ?>" style="font-size: 12px; padding: 4px 10px; color: #0066cc; border-color: #0066cc;">Anterior</a>
                <?php endif; ?>

                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(is_string($element)): ?>
                        <span style="padding: 4px 8px; font-size: 12px;"><?php echo e($element); ?></span>
                    <?php endif; ?>
                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <button class="btn btn-sm btn-primary" style="font-size: 12px; padding: 4px 10px; min-width: 30px;"><?php echo e($page); ?></button>
                            <?php else: ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo e($url); ?>" style="font-size: 12px; padding: 4px 10px; min-width: 30px; color: #0066cc; border-color: #0066cc;"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($paginator->hasMorePages()): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e($paginator->nextPageUrl()); ?>" style="font-size: 12px; padding: 4px 10px; color: #0066cc; border-color: #0066cc;">Siguiente</a>
                <?php else: ?>
                    <button class="btn btn-sm btn-default" disabled style="font-size: 12px; padding: 4px 10px; cursor: not-allowed;">Siguiente</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<?php endif; ?><?php /**PATH C:\laragon\www\facturafacil\resources\views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>