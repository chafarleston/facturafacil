<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Nota de Venta - Ticket</title>
  <style>
    @media print {
      body { width: 80mm; }
    }
    body { font-family: Arial, sans-serif; padding: 16px; }
  </style>
  </head>
  <body>
  <div>
    <h3>Nota de Venta <?php echo e($invoice->full_number); ?></h3>
    <p>Fecha: <?php echo e($invoice->fecha_emision); ?></p>
    <p>Cliente: <?php echo e($invoice->customer->nombre ?? ''); ?></p>
  </div>
  </body>
</html>
<?php /**PATH C:\laragon\www\facturafacil\resources\views\invoices\print_nv_ticket.blade.php ENDPATH**/ ?>