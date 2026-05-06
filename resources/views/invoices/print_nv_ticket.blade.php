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
    <h3>Boleta de venta {{ $invoice->full_number }}</h3>
    <p>Fecha: {{ $invoice->fecha_emision }}</p>
    <p>Cliente: {{ $invoice->customer->nombre ?? '' }}</p>
  </div>
  </body>
</html>
