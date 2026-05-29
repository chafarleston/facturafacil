<?php
/**
 * Script para corregir invoice_items.precio_venta en facturas existentes
 * 
 * Uso: php fix_precio_venta.php
 * Requiere: estar en la raíz del proyecto Laravel
 * 
 * Antes de ejecutar, hacer commit + push del código y git pull en el cliente.
 * Este script corrige SOLO los datos existentes.
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Corrigiendo invoice_items.precio_venta ===\n\n";

$affected = DB::update("
    UPDATE invoice_items ii
    JOIN products p ON p.id = ii.product_id
    SET ii.precio_venta = ROUND(p.precio * ii.cantidad, 2)
    WHERE ii.cantidad > 1
      AND ii.precio_venta > 0
      AND ROUND(ii.precio_venta / (ii.cantidad * ii.cantidad), 2) = ROUND(p.precio, 2)
      AND ROUND(ii.precio_venta / NULLIF(ii.cantidad, 0), 2) != ROUND(p.precio, 2)
");

echo "✓ $affected registros corregidos.\n\n";

$mismatches = DB::select("
    SELECT COUNT(*) as total
    FROM (
        SELECT i.id
        FROM invoices i
        JOIN invoice_items ii ON ii.invoice_id = i.id
        GROUP BY i.id
        HAVING ROUND(i.total, 2) != ROUND(SUM(ii.precio_venta), 2)
    ) as tmp
");

$total = $mismatches[0]->total ?? 0;
if ($total > 0) {
    echo "⚠ Quedan $total facturas con diferencias. Revisar manualmente.\n";
} else {
    echo "✓ Todas las facturas cuadran correctamente.\n";
}
