<?php use App\Models\Company; $company = Company::getMainCompany(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Caja</title>
    <style>
        @media print { body { width: 80mm; } }
        body { font-family: "Courier New", monospace; font-size: 9px; padding: 8px; width: 76mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .border-top { border-bottom: 1px solid #000; }
        .border-double { border-bottom: 2px solid #000; }
    </style>
</head>
<body>
    <div class="text-center py-1">
        <div class="bold"><?php echo e($company->nombre_comercial ?? $company->razon_social); ?></div>
        <div>RUC: <?php echo e($company->ruc); ?></div>
        <div class="bold" style="font-size:11px;">RESUMEN DE CAJA</div>
    </div>

    <div class="border-bottom py-1 mb-1">
        <div>Apertura: <?php echo e($cashregister->fecha_apertura->format('d/m/Y H:i')); ?></div>
        <div>Cierre: <?php echo e($cashregister->fecha_cierre ? $cashregister->fecha_cierre->format('d/m/Y H:i') : 'Ahora'); ?></div>
        <div><?php echo e($cashregister->user->name); ?></div>
    </div>

    <div class="border-top py-1 mb-1 bold">RESUMEN POR DOCUMENTO</div>
    <div>
        <div class="bold">Facturas:</div>
        <div><?php echo e($facturas->count()); ?> und - S/ <?php echo e(number_format($facturas->sum('total'), 2)); ?></div>
    </div>
    <div>
        <div class="bold">Boletas:</div>
        <div><?php echo e($boletas->count()); ?> und - S/ <?php echo e(number_format($boletas->sum('total'), 2)); ?></div>
    </div>
    <div>
        <div class="bold">Notas Venta:</div>
        <div><?php echo e($nvs->count()); ?> und - S/ <?php echo e(number_format($nvs->sum('total'), 2)); ?></div>
    </div>
    <div class="border-top py-1 mt-1">
        <div class="bold">TOTAL: <?php echo e($facturas->count() + $boletas->count() + $nvs->count()); ?> und</div>
        <div class="bold">S/ <?php echo e(number_format($cashregister->total_ventas, 2)); ?></div>
    </div>

    <div class="border-top py-1 mt-1 mb-1 bold">POR MÉTODO PAGO</div>
    <div>
        <div>Efectivo: S/ <?php echo e(number_format($cashregister->ventas_efectivo, 2)); ?></div>
        <div>Tarjeta: S/ <?php echo e(number_format($cashregister->ventas_tarjeta, 2)); ?></div>
        <div>Yape: S/ <?php echo e(number_format($cashregister->ventas_yape, 2)); ?></div>
        <div>Plin: S/ <?php echo e(number_format($cashregister->ventas_plin, 2)); ?></div>
    </div>

    <div class="border-top py-1 mt-1 mb-1 bold">COMPROBANTES POR MÉTODO PAGO</div>
    <?php $__currentLoopData = $ventasPorMetodo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metodo => $ventasMetodo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bold border-bottom"><?php echo e($metodo); ?> (<?php echo e(count($ventasMetodo)); ?> und - S/ <?php echo e(number_format(collect($ventasMetodo)->sum('total'), 2)); ?>)</div>
    <?php $__currentLoopData = $ventasMetodo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="font-size:8px;"><?php echo e($venta->full_number); ?> - <?php echo e($venta->customer->nombre ?? 'Varios'); ?> - S/ <?php echo e(number_format($venta->total, 2)); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if(count($categoriasVentas) > 0): ?>
    <div class="border-top py-1 mt-1 mb-1 bold">POR CATEGORÍA</div>
    <?php $__currentLoopData = $categoriasVentas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="font-size:8px;"><?php echo e($categoria); ?>: <?php echo e($data['cantidad']); ?> und - S/ <?php echo e(number_format($data['total'], 2)); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php if(count($productosVendidos) > 0): ?>
    <div class="border-top py-1 mt-1 mb-1 bold">PRODUCTOS VENDIDOS</div>
    <?php $__currentLoopData = $productosVendidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="font-size:8px;"><?php echo e(Str::limit($producto, 20)); ?>: <?php echo e(number_format($data['cantidad'], 0)); ?> und - S/ <?php echo e(number_format($data['total'], 2)); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php if(count($lineasEliminadas) > 0): ?>
    <div class="border-top py-1 mt-1"></div>
    <div class="border-bottom py-1 mb-1 bold text-center">REPORTE DE LÍNEAS ELIMINADAS</div>
    <div style="font-size:8px; margin-bottom:3px;">Hay <?php echo e(count($lineasEliminadas)); ?> línea(s) eliminada(s) en el Sistema</div>
    <?php $__currentLoopData = $lineasEliminadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="font-size:8px;"><?php echo e(Str::limit($item->product_name, 22)); ?> x<?php echo e(number_format($item->quantity, 0)); ?> - <?php echo e($item->cancelled_from); ?> - <?php echo e($item->cancelled_at ? $item->cancelled_at->format('H:i') : ''); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div class="border-top py-1 mt-1"></div>
    <?php endif; ?>

    <div class="border-top py-1 mt-1 text-center">
        <div class="bold">GRACIAS POR SU PREFERENCIA</div>
    </div>
</body>
</html><?php /**PATH C:\laragon\www\facturafacil\resources\views\cashregisters\ticket.blade.php ENDPATH**/ ?>