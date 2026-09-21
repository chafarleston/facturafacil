<?php
/**
 * Script 1: Eliminar TODAS las ventas y cajas registradoras
 * Uso: php artisan tinker --execute="include storage_path('app/tmp/clean_ventas.php');"
 * 
 * ADVERTENCIA: Destructivo. Elimina permanentemente:
 *   - Todos los invoices e invoice_items
 *   - Todos los restaurant_orders e items
 *   - Todas las aperturas/cierres de caja
 *   - Todos los print_jobs
 *   - Pone el stock de TODOS los productos (normales y compuestos) en 0
 * 
 * NO elimina: productos, categorías, clientes, mesas, pisos, usuarios, empresas
 */

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantOrder;
use App\Models\CashRegister;
use App\Models\PrintJob;
use App\Models\RestaurantTable;
use App\Models\Serie;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "============================================\n";
echo "  ELIMINACION DE VENTAS Y CAJAS\n";
echo "============================================\n\n";

// 1. Eliminar invoice_items
$count = InvoiceItem::count();
InvoiceItem::query()->delete();
echo "[1] InvoiceItems eliminados: {$count}\n";

// 2. Eliminar invoices
$count = Invoice::count();
Invoice::query()->delete();
echo "[2] Invoices eliminados: {$count}\n";

// 3. Eliminar restaurant_order_items
$count = RestaurantOrderItem::count();
RestaurantOrderItem::query()->delete();
echo "[3] RestaurantOrderItems eliminados: {$count}\n";

// 4. Eliminar restaurant_orders
$count = RestaurantOrder::count();
RestaurantOrder::query()->delete();
echo "[4] RestaurantOrders eliminados: {$count}\n";

// 5. Eliminar cash registers
$count = CashRegister::count();
CashRegister::query()->delete();
echo "[5] CashRegisters eliminados: {$count}\n";

// 6. Eliminar print_jobs
$count = PrintJob::count();
PrintJob::query()->delete();
echo "[6] PrintJobs eliminados: {$count}\n";

// 7. Liberar todas las mesas
$count = RestaurantTable::where('status', '!=', 'AVAILABLE')->count();
RestaurantTable::query()->update(['status' => 'AVAILABLE', 'locked_by' => null, 'locked_at' => null]);
echo "[7] Mesas liberadas: {$count}\n";

// 8. Resetear secuencias de series (volver a 0)
DB::statement('UPDATE series SET numero_actual = 0');
echo "[8] Series reseteadas a 0\n";

// 9. Stock de todos los productos (normales y compuestos) a 0
$count = Product::count();
Product::query()->update(['stock' => 0]);
echo "[9] Stock de productos a 0: {$count} productos\n";

// 10. Limpiar cache (las listas de productos cacheadas incluyen el stock)
Cache::flush();
echo "[10] Cache limpiado\n";

echo "\n=== COMPLETADO ===\n";
echo "Ventas, pedidos, cajas y cola de impresión eliminados.\n";
echo "Stock de productos en 0. Mesas liberadas. Series reseteadas.\n";
echo "============================================\n";
