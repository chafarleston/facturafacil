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
    <h2>Boleta de venta {{ $invoice->full_number }}</h2>
    <p>Tipo: {{ $invoice->tipo_documento }}</p>
    <p>Cliente: {{ $invoice->customer->nombre ?? '' }}</p>
    <p>Fecha: {{ $invoice->fecha_emision }}</p>
    <!-- Muestra más datos según sea necesario -->
  </div>
  </body>
</html>
