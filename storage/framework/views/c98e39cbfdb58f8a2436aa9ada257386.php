<?php if($paginator->hasPages()): ?>
    <nav>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div style="font-size: 12px; color: #6c757d;">
                Mostrando <?php echo e($paginator->firstItem()); ?> a <?php echo e($paginator->lastItem()); ?> de <?php echo e($paginator->total()); ?> resultados
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?php if($paginator->onFirstPage()): ?>
                    <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Anterior</span></li>
                <?php else: ?>
                    <li class="page-item"><a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Anterior</a></li>
                <?php endif; ?>

                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(is_string($element)): ?>
                        <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;"><?php echo e($element); ?></span></li>
                    <?php endif; ?>
                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <li class="page-item active"><span class="page-link" style="font-size: 12px; padding: 2px 8px; background: #0066cc; border-color: #0066cc; color: #fff;"><?php echo e($page); ?></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="<?php echo e($url); ?>" style="font-size: 12px; padding: 2px 8px; color: #0066cc;"><?php echo e($page); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($paginator->hasMorePages()): ?>
                    <li class="page-item"><a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" style="font-size: 12px; padding: 2px 8px; color: #0066cc;">Siguiente</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link" style="font-size: 12px; padding: 2px 8px;">Siguiente</span></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
<?php endif; ?><?php /**PATH C:\laragon\www\facturafacil\resources\views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>