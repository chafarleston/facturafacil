<?php
/**
 * Script 2: Eliminar TODOS los productos
 * Uso: php artisan tinker --execute="include storage_path('app/tmp/clean_productos.php');"
 * 
 * ADVERTENCIA: Destructivo. Elimina permanentemente:
 *   - Todos los productos
 *   - Todos los componentes de productos compuestos (product_components)
 * 
 * NO elimina: categorías, ventas, cajas, mesas, usuarios, empresas
 */

use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\InvoiceItem;
use App\Models\RestaurantOrderItem;
use Illuminate\Support\Facades\Cache;

echo "============================================\n";
echo "  ELIMINACION DE PRODUCTOS\n";
echo "============================================\n\n";

// Verificar si hay ventas activas
$ventasActivas = InvoiceItem::count();
$pedidosActivos = RestaurantOrderItem::count();

if ($ventasActivas > 0 || $pedidosActivos > 0) {
    echo "ADVERTENCIA: Hay {$ventasActivas} invoice_items y {$pedidosActivos} order_items\n";
    echo "que quedaran huerfanos si eliminas los productos.\n";
    echo "Ejecuta primero clean_ventas.php para eliminar las ventas.\n\n";
    
    $confirmar = readline("Continuar de todos modos? (si/no): ");
    if (strtolower($confirmar) !== 'si') {
        echo "Cancelado.\n";
        return;
    }
}

// 1. Eliminar componentes de productos compuestos
$count = ProductComponent::count();
ProductComponent::query()->delete();
echo "[1] ProductComponents eliminados: {$count}\n";

// 2. Eliminar productos
$count = Product::count();
Product::query()->delete();
echo "[2] Products eliminados: {$count}\n";

// 3. Limpiar cache
Cache::flush();
echo "[3] Cache limpiado\n";

echo "\n=== COMPLETADO ===\n";
echo "Todos los productos eliminados.\n";
echo "============================================\n";
