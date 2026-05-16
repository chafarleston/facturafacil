<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Nla Nota de Venta - A4</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
    .page { width: 210mm; padding: 20mm; }
  </style>
  </head>
  <body>
  <div class="page">
    <h2>Nota de Venta <?php echo e($invoice->full_number); ?></h2>
    <p>Tipo: <?php echo e($invoice->tipo_documento); ?></p>
    <p>Cliente: <?php echo e($invoice->customer->nombre ?? ''); ?></p>
    <p>Fecha: <?php echo e($invoice->fecha_emision); ?></p>
    <!-- Muestra más datos según sea necesario -->
  </div>
  </body>
</html>
<?php /**PATH C:\laragon\www\facturafacil\resources\views\invoices\print_nv_a4.blade.php ENDPATH**/ ?>