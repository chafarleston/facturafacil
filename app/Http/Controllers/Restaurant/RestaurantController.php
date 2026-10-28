<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantKitchenBatch;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Serie;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Events\KitchenOrderUpdated;
use App\Services\PrintServerService;
use App\Services\PrintService;
use App\Services\KitchenBatchService;
use Barryvdh\DomPDF\Facade\Pdf;

class RestaurantController extends Controller
{
    public function index(Request $request, PrintServerService $printServer)
    {
        $companyId = $request->company_id ?? Company::first()->id;

        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        $floors = Floor::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->with(['tables' => function($q) use ($companyId) {
                $q->excludeKiosko()->with(['orders' => function($oq) use ($companyId) {
                    $oq->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                       ->withCount('items');
                }]);
            }])
            ->get();

        $auxiliaryItems = \App\Models\AuxiliaryItem::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Cache::remember('restaurant_products_' . $companyId, 300, function() use ($companyId) {
            return Product::where('company_id', $companyId)
                ->where('estado', 'ACTIVO')
                ->orderBy('descripcion')
                ->get();
        });

        $categories = Cache::remember('restaurant_categories_' . $companyId, 300, function() use ($companyId) {
            return Category::where('company_id', $companyId)
                ->whereIn('estado', ['ACTIVO', 'ACT'])
                ->orderBy('nombre')
                ->get();
        });

        $customers = Cache::remember('restaurant_customers_' . $companyId, 300, function() use ($companyId) {
            return Customer::where('company_id', $companyId)
                ->where('estado', 'ACTIVO')
                ->get();
        });

        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereIn('tipo_documento', ['01', '03', 'NV'])
            ->get();

        $company = Company::find($companyId);
        $orderMode = $company->order_mode ?? 'kds';
        $printServerRunning = $printServer->isServerRunning();
        $igvPercent = $company ? $company->getActiveIgvPercent() : 18;

        $showProductImages = \App\Models\Setting::showProductImages('restaurant');
        $showCategoryImages = \App\Models\Setting::showCategoryImages('restaurant');
        $categoriesOnly = \App\Models\Setting::categoriesOnly('restaurant');

        return view('restaurant.index', compact('floors', 'products', 'categories', 'customers', 'series', 'companyId', 'orderMode', 'printServerRunning', 'igvPercent', 'cajaAbierta', 'showProductImages', 'showCategoryImages', 'categoriesOnly', 'auxiliaryItems'));
    }

    public function modeIndex()
    {
        $company = Company::getMainCompany();
        $orderMode = $company->order_mode ?? 'kds';
        return view('restaurant.mode', compact('orderMode', 'company'));
    }

    public function toggleMode(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $company = Company::findOrFail($companyId);
        $newMode = $company->order_mode === 'print' ? 'kds' : 'print';
        $company->update(['order_mode' => $newMode]);
        \App\Models\Company::clearCache();
        return back()->with('success', "Modo cambiado a " . ($newMode === 'print' ? 'Impresi��n 80mm' : 'KDS'));
    }

    public function openTable(Request $request, $tableId)
    {
        try {
            $table = RestaurantTable::findOrFail($tableId);
            
            $existingOrder = RestaurantOrder::where('table_id', $table->id)
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->first();

            if ($existingOrder) {
                return response()->json([
                    'success' => true,
                    'order_id' => $existingOrder->id,
                    'message' => 'Pedido existente cargado'
                ]);
            }

            $order = RestaurantOrder::create([
                'company_id' => $table->company_id,
                'table_id' => $table->id,
                'user_id' => Auth::id(),
                'order_number' => RestaurantOrder::generateOrderNumber(),
                'status' => 'OPEN',
            ]);

            $table->update(['status' => 'OCCUPIED']);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function getOrder(Request $request, $orderId)
    {
        try {
            $order = RestaurantOrder::with(['items', 'table.floor', 'user'])
                ->findOrFail($orderId);

            $activeItems = $order->items->where('kitchen_status', '!=', 'CANCELLED');
            $paidTotal = round($activeItems->whereNotNull('paid_invoice_id')->sum('total'), 2);
            $remainingTotal = round($activeItems->whereNull('paid_invoice_id')->sum('total'), 2);

            return response()->json([
                'success' => true,
                'order' => $order,
                'paid_total' => $paidTotal,
                'remaining_total' => $remainingTotal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addItem(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => ['required', 'numeric', 'min:1', function ($attr, $value, $fail) {
                    if ((float) $value != floor((float) $value)) {
                        $fail('La cantidad debe ser un número entero (1, 2, 3...).');
                    }
                }],
                'notes' => 'nullable|string|max:500',
                'auxiliary_items' => 'nullable|array',
                'auxiliary_items.*' => 'integer|exists:auxiliary_items,id',
            ]);

            $product = Product::findOrFail($validated['product_id']);
            $order = RestaurantOrder::findOrFail($orderId);

            $existingItem = RestaurantOrderItem::where('restaurant_order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('kitchen_status', 'PENDING')
                ->whereNull('paid_invoice_id')
                ->where('notes', $validated['notes'] ?? null)
                ->where('auxiliary_items', json_encode($validated['auxiliary_items'] ?? null))
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $validated['quantity'];
                $existingItem->total = $existingItem->quantity * $existingItem->unit_price;
                if (isset($validated['notes'])) {
                    $existingItem->notes = $validated['notes'];
                }
                $existingItem->save();
                $item = $existingItem;
            } else {
                $item = RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->descripcion,
                    'quantity' => $validated['quantity'],
                    'unit_price' => $product->precio,
                    'total' => $product->precio * $validated['quantity'],
                    'kitchen_status' => 'PENDING',
                    'notes' => $validated['notes'] ?? null,
                    'auxiliary_items' => $validated['auxiliary_items'] ?? null,
                    'kds_destination' => $product->kds_destination ?? 'cocina',
                ]);
            }

            $this->updateOrderTotals($order);

            return response()->json([
                'success' => true,
                'item' => $item,
                'order_total' => $order->fresh()->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function updateItem(Request $request, $itemId)
    {
        $item = RestaurantOrderItem::findOrFail($itemId);

        if ($item->paid_invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto ya fue facturado y no se puede modificar.'
            ], 400);
        }
        
        $validated = $request->validate([
            'quantity' => 'nullable|numeric|min:0.01',
            'quantity_delta' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        if (isset($validated['quantity_delta'])) {
            $item->quantity += $validated['quantity_delta'];
            if ($item->quantity < 1) {
                $item->quantity = 1;
            }
        } elseif (isset($validated['quantity'])) {
            $item->quantity = $validated['quantity'];
        }
        
        if (array_key_exists('notes', $validated)) {
            $item->notes = $validated['notes'];
        }
        
        $item->total = $item->quantity * $item->unit_price;
        $item->save();

        $this->updateOrderTotals($item->order);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function removeItem(Request $request, $itemId)
    {
        $item = RestaurantOrderItem::findOrFail($itemId);
        $order = $item->order;

        if ($item->paid_invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto ya fue facturado y no se puede eliminar.'
            ], 400);
        }

        if (in_array($item->kitchen_status, ['SENT', 'READY', 'DELIVERED'])) {
            $adminPassword = $request->input('admin_password');
            if (!$adminPassword) {
                return response()->json([
                    'success' => false,
                    'requires_admin' => true,
                    'message' => 'El producto ya está enviado a cocina. Requiere autorización de administrador o cajero.'
                ]);
            }

            if (!$this->checkAuthorizedPassword($adminPassword)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }

            $item->cancelled_from = $item->kitchen_status;
            $item->cancelled_at = now();
            $item->cancelled_by = auth()->id();
            $item->kitchen_status = 'CANCELLED';
            $item->save();

            $company = Company::find($order->company_id);
            if ($company && ($company->order_mode ?? 'kds') === 'print') {
                try {
                    $printService = app(PrintService::class);
                    $order->load(['table', 'items']);
                    $printService->printCancelNotification($order, $item);
                } catch (\Exception $e) {
                    \Log::error('Cancel print error: ' . $e->getMessage());
                }
            }
        } else {
            $item->delete();
        }

        $this->updateOrderTotals($order);

        if ($item->kitchen_batch_id) {
            $batch = RestaurantKitchenBatch::find($item->kitchen_batch_id);
            if ($batch) {
                app(KitchenBatchService::class)->cancelBatchIfEmpty($batch);
            }
        }

        $activeItems = $order->items()->where('kitchen_status', '!=', 'CANCELLED')->count();
        if ($activeItems == 0) {
            $order->update(['status' => 'CANCELLED']);

            $order->kitchenBatches()->update([
                'status' => RestaurantKitchenBatch::STATUS_CANCELLED,
                'finished_at' => now(),
            ]);

            $order->table->update(['status' => 'AVAILABLE']);
        } else {
            app(KitchenBatchService::class)->syncOrderStatus($order);
        }

        event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
        Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
        Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

        return response()->json(['success' => true]);
    }

    public function sendToKitchen(Request $request, $orderId)
    {
        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            
            $pendingItems = $order->items()->where('kitchen_status', 'PENDING')
                ->whereNull('paid_invoice_id')->get();
            
            if ($pendingItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos pendientes para enviar'
                ]);
            }

            $batches = app(KitchenBatchService::class)
                ->createFromItems($order, $pendingItems, auth()->id());


            event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

            $company = Company::find($order->company_id);
            if ($company && ($company->order_mode ?? 'kds') === 'print') {
                try {
                    $printService = app(PrintService::class);
                    $printService->printKitchenOrder($order->fresh(['table.floor', 'user']), $pendingItems);
                } catch (\Exception $e) {
                    \Log::error('Print error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido enviado a cocina',
                'items_sent' => $pendingItems->count(),
                'batches' => $batches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'kds_destination' => $batch->kds_destination,
                ])->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printKitchenTicket(Request $request, $orderId)
    {
        $order = RestaurantOrder::with(['items' => function($q) {
            $q->whereIn('kitchen_status', ['SENT'])->whereNull('paid_invoice_id');
        }, 'table', 'user'])->findOrFail($orderId);

        if ($order->items->isEmpty()) {
            return back()->with('error', 'No hay productos para imprimir');
        }

        $pdf = Pdf::loadView('restaurant.tickets.kitchen', compact('order'))
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('encoding', 'UTF-8');
        
        return $pdf->stream('ticket-cocina-' . $order->order_number . '.pdf');
    }

    public function printPrebill(Request $request, $orderId)
    {
$order = RestaurantOrder::with(['items', 'table.floor', 'user'])->findOrFail($orderId);
        $order->setRelation('items', $order->items
            ->where('kitchen_status', '!=', 'CANCELLED')
            ->whereNull('paid_invoice_id'));

        $company = Company::find($order->company_id) ?: Company::getMainCompany();

        $companyRecord = Company::find($order->company_id);
        if ($companyRecord && ($companyRecord->order_mode ?? 'kds') === 'print') {
            try {
                $printService = app(PrintService::class);
                $order->load(['table', 'items']);
                $printService->printPrebill($order);
            } catch (\Exception $e) {
                \Log::error('Prebill print error: ' . $e->getMessage());
            }
        }

        $pdf = Pdf::loadView('restaurant.tickets.prebill', compact('order', 'company'))
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('encoding', 'UTF-8');
        
        return $pdf->stream('precuenta-' . $order->order_number . '.pdf');
    }

    public function printPrebillTo(Request $request, $orderId, $printerKey)
    {
$order = RestaurantOrder::with(['items', 'table.floor', 'user'])->findOrFail($orderId);
        $order->setRelation('items', $order->items
            ->where('kitchen_status', '!=', 'CANCELLED')
            ->whereNull('paid_invoice_id'));

        try {
            $printService = app(PrintService::class);
            $order->load(['table', 'items']);
            $printService->printPrebill($order, $printerKey);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Prebill print error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function restaurantStream(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        
        echo "retry: 2000\n";
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $lastChange = null;
        $lastCacheKey = 'restaurant_updated_' . $companyId;
        
        while (true) {
            if (connection_aborted()) break;
            
            $current = Cache::get($lastCacheKey);
            if ($current !== $lastChange) {
                $lastChange = $current;
                echo "data: updated\n\n";
                flush();
            }
            
            usleep(2000000);
        }
    }

    public function closeOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cerrar mesas'], 403);
        }

        $order = RestaurantOrder::with('items')->findOrFail($orderId);
        
        $order->update(['status' => 'COMPLETED']);
        $order->table->update(['status' => 'AVAILABLE']);

        Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

        return response()->json([
            'success' => true,
            'message' => 'Mesa cerrada exitosamente'
        ]);
    }

    public function moveTable(Request $request, $orderId)
    {
        $order = RestaurantOrder::findOrFail($orderId);
        $newTableId = $request->input('table_id');

        if (!$newTableId) {
            return response()->json(['success' => false, 'message' => 'Seleccione una mesa destino']);
        }

        if ($order->table_id == $newTableId) {
            return response()->json(['success' => false, 'message' => 'La mesa seleccionada es la misma']);
        }

        $newTable = RestaurantTable::findOrFail($newTableId);

        if ($newTable->activeOrder()) {
            return response()->json(['success' => false, 'message' => 'La mesa destino ya tiene un pedido activo']);
        }

        $oldTable = $order->table;

        $order->update(['table_id' => $newTableId]);

        if ($oldTable) {
            $otherOrders = RestaurantOrder::where('table_id', $oldTable->id)
                ->where('id', '!=', $orderId)
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->count();
            if ($otherOrders == 0) {
                $oldTable->update(['status' => 'AVAILABLE']);
            }
        }

        $newTable->update(['status' => 'OCCUPIED']);

        event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
        Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
        Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

        return response()->json([
            'success' => true,
            'message' => 'Pedido movido a ' . $newTable->name,
            'old_table_id' => $oldTable?->id,
            'new_table_id' => $newTable->id,
        ]);
    }

    public function mergeOrder(Request $request, $orderId)
    {
        if (!auth()->user() || !auth()->user()->hasPermission('manage_order_merge')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para fusionar mesas'], 403);
        }

        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            $newTableId = $request->input('table_id');

            if (!$newTableId) {
                return response()->json(['success' => false, 'message' => 'Seleccione una mesa destino']);
            }

            if ($order->table_id == $newTableId) {
                return response()->json(['success' => false, 'message' => 'La mesa seleccionada es la misma']);
            }

            $newTable = RestaurantTable::findOrFail($newTableId);
            $destinoOrder = $newTable->activeOrder();

            if (!$destinoOrder) {
                return response()->json(['success' => false, 'message' => 'La mesa destino no tiene un pedido activo para fusionar']);
            }

            if ($order->order_type === 'kiosko' || $destinoOrder->order_type === 'kiosko') {
                return response()->json(['success' => false, 'message' => 'No se pueden fusionar pedidos de kiosko']);
            }

            $origenFacturado = $order->items()->whereNotNull('paid_invoice_id')->exists();
            $destinoFacturado = $destinoOrder->items()->whereNotNull('paid_invoice_id')->exists();
            if ($origenFacturado || $destinoFacturado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede fusionar: hay productos ya facturados en los pedidos'
                ], 400);
            }

            $oldTable = $order->table;

            $order->items()->update(['restaurant_order_id' => $destinoOrder->id]);

            foreach ($order->kitchenBatches()->get() as $batch) {
                $nextNumber = (int) RestaurantKitchenBatch::where('restaurant_order_id', $destinoOrder->id)
                    ->where('kds_destination', $batch->kds_destination)
                    ->max('batch_number') + 1;

                $batch->update([
                    'restaurant_order_id' => $destinoOrder->id,
                    'batch_number' => $nextNumber,
                ]);
            }

            $this->updateOrderTotals($destinoOrder);
            app(KitchenBatchService::class)->syncOrderStatus($destinoOrder);

            $order->delete();

            if ($oldTable) {
                $otherOrders = RestaurantOrder::where('table_id', $oldTable->id)
                    ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                    ->count();
                if ($otherOrders == 0) {
                    $oldTable->update(['status' => 'AVAILABLE']);
                }
            }

            $newTable->update(['status' => 'OCCUPIED']);

            event(new KitchenOrderUpdated($destinoOrder->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $destinoOrder->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $destinoOrder->company_id, now()->timestamp, 10);

            return response()->json([
                'success' => true,
                'message' => 'Pedidos fusionados en ' . $newTable->name,
                'old_table_id' => $oldTable?->id,
                'new_table_id' => $newTable->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al fusionar: ' . $e->getMessage()], 500);
        }
    }

    public function cancelOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para anular pedidos'], 403);
        }
        
        $order = RestaurantOrder::with('items')->findOrFail($orderId);
        
        $hasPaidItems = $order->items->whereNotNull('paid_invoice_id')->isNotEmpty();
        if ($hasPaidItems) {
            return response()->json([
                'success' => false,
                'message' => 'El pedido tiene items ya facturados. No se puede anular completamente. Elimine solo los items pendientes.'
            ], 400);
        }
        
        $hasKitchenItems = $order->items->whereIn('kitchen_status', ['SENT', 'READY', 'DELIVERED'])->isNotEmpty();

        $company = Company::find($order->company_id);
        $isPrintMode = $company && ($company->order_mode ?? 'kds') === 'print';

        if ($hasKitchenItems) {
            $adminPassword = $request->input('admin_password');
            if (!$adminPassword) {
                return response()->json([
                    'success' => false,
                    'requires_admin' => true,
                    'message' => 'El pedido tiene productos enviados a cocina. Requiere autorización de administrador o cajero.'
                ]);
            }

            if (!$this->checkAuthorizedPassword($adminPassword)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }
        }

        if ($isPrintMode && $hasKitchenItems) {
            try {
                $printService = app(PrintService::class);
                $kitchenItems = $order->items->whereIn('kitchen_status', ['SENT', 'READY', 'DELIVERED']);
                $printService->printCancelNotificationGrouped($order, $kitchenItems);
            } catch (\Exception $e) {
                \Log::error('Cancel print error: ' . $e->getMessage());
            }
        }

        $items = $order->items;
        foreach ($items as $item) {
            $item->cancelled_from = $item->kitchen_status;
            $item->cancelled_at = now();
            $item->cancelled_by = auth()->id();
            $item->kitchen_status = 'CANCELLED';
            $item->save();
        }
        $order->update(['status' => 'CANCELLED']);

        $order->kitchenBatches()->update([
            'status' => RestaurantKitchenBatch::STATUS_CANCELLED,
            'finished_at' => now(),
        ]);

        if ($order->table) {
            $order->table->update(['status' => 'AVAILABLE']);
        }

        event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

        return response()->json(['success' => true]);
    }

    public function getPollData(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;

        $orders = RestaurantOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->select('id', 'table_id', 'order_number')
            ->get();

        $locks = RestaurantTable::where('company_id', $companyId)
            ->whereNotNull('locked_by')
            ->with('lockedByUser:id,name')
            ->get()
            ->map(function ($table) {
                if ($table->isLockExpired()) {
                    $table->unlock();
                    return null;
                }
                return [
                    'table_id' => $table->id,
                    'locked_by' => $table->locked_by,
                    'user_name' => $table->lockedByUser ? $table->lockedByUser->name : 'Usuario',
                ];
            })
            ->filter()
            ->values();

        return response()->json(['success' => true, 'orders' => $orders, 'locks' => $locks])
            ->header('Cache-Control', 'no-cache, must-revalidate, no-store, private')
            ->header('Pragma', 'no-cache');
    }

    public function kitchenIndex(Request $request)
    {
        $kds = $request->kds ?? 'cocina';
        $orderMode = Company::orderMode();
        return view('restaurant.kds', compact('kds', 'orderMode'));
    }

    public function getKitchenOrders(Request $request)
    {
        $companyId = $request->company_id ?? Company::mainCompanyId();
        $kds = $request->kds ?? 'cocina';

        $orders = RestaurantOrder::where('company_id', $companyId)
            ->whereHas('kitchenBatches', function ($q) use ($kds) {
                $q->where('kds_destination', $kds)
                    ->whereIn('status', [RestaurantKitchenBatch::STATUS_PENDING, RestaurantKitchenBatch::STATUS_PREPARING])
                    ->whereHas('items', function ($items) {
                        $items->whereIn('kitchen_status', ['SENT', 'READY']);
                    });
            })
            ->with([
                'kitchenBatches' => function ($q) use ($kds) {
                    $q->where('kds_destination', $kds)
                        ->whereIn('status', [RestaurantKitchenBatch::STATUS_PENDING, RestaurantKitchenBatch::STATUS_PREPARING])
                        ->with(['items' => function ($items) {
                            $items->whereIn('kitchen_status', ['SENT', 'READY', 'CANCELLED'])
                                ->select(
                                    'id',
                                    'restaurant_order_id',
                                    'kitchen_batch_id',
                                    'product_name',
                                    'quantity',
                                    'unit_price',
                                    'kitchen_status',
                                    'notes',
                                    'auxiliary_items',
                                    'kds_destination',
                                    'sent_to_kitchen_at'
                                );
                        }])
                        ->orderBy('batch_number', 'desc');
                },
                'table' => function ($q) {
                    $q->select('id', 'name', 'floor_id');
                },
                'table.floor' => function ($q) {
                    $q->select('id', 'name');
                },
                'user' => function ($q) {
                    $q->select('id', 'name');
                },
            ])
            ->select('id', 'order_number', 'status', 'table_id', 'user_id', 'notes', 'created_at', 'order_type', 'sent_to_kitchen_at', 'preparing_started_at')
            ->orderBy('created_at', 'asc')
            ->get();

        $auxiliaryIds = $orders->flatMap(function ($order) {
            return $order->kitchenBatches->flatMap(function ($batch) {
                return $batch->items->flatMap(function ($item) {
                    return is_array($item->auxiliary_items) ? $item->auxiliary_items : [];
                });
            });
        })->unique()->values();

        $auxiliaryNames = $auxiliaryIds->isEmpty()
            ? collect()
            : \App\Models\AuxiliaryItem::whereIn('id', $auxiliaryIds)->pluck('name', 'id');

        $formattedOrders = $orders->map(function ($order) use ($auxiliaryNames) {
            $hasPreparing = $order->kitchenBatches->contains(
                fn ($batch) => $batch->status === RestaurantKitchenBatch::STATUS_PREPARING
            );

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $hasPreparing ? 'PREPARING' : 'SENT_TO_KITCHEN',
                'table_name' => $order->table ? $order->table->name : 'Mesa',
                'floor_name' => $order->table && $order->table->floor ? $order->table->floor->name : null,
                'user_name' => $order->user ? $order->user->name : null,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'sent_to_kitchen_at' => $order->sent_to_kitchen_at?->toIso8601String(),
                'preparing_started_at' => $order->preparing_started_at?->toIso8601String(),
                'order_type' => $order->order_type ?? 'mozo',
                'batches' => $order->kitchenBatches->map(function ($batch) use ($auxiliaryNames) {
                    return [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'kds_destination' => $batch->kds_destination,
                        'status' => $batch->status,
                        'sent_at' => $batch->sent_at?->toIso8601String(),
                        'preparing_started_at' => $batch->preparing_started_at?->toIso8601String(),
                        'finished_at' => $batch->finished_at?->toIso8601String(),
                        'items' => $batch->items->map(function ($item) use ($auxiliaryNames) {
                            $auxNames = collect($item->auxiliary_items ?? [])
                                ->map(fn ($id) => $auxiliaryNames[$id] ?? null)
                                ->filter()
                                ->values()
                                ->toArray();

                            return [
                                'id' => $item->id,
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'kitchen_status' => $item->kitchen_status,
                                'notes' => $item->notes,
                                'auxiliary_items' => $item->auxiliary_items,
                                'auxiliary_names' => $auxNames,
                                'sent_to_kitchen_at' => $item->sent_to_kitchen_at?->toIso8601String(),
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        });

        return response()->json(['success' => true, 'orders' => $formattedOrders, 'order_mode' => Company::orderMode()])
            ->header('Cache-Control', 'no-cache, must-revalidate, no-store, private')
            ->header('Pragma', 'no-cache');
    }

    public function kitchenHistory(Request $request)
    {
        $this->authorize('permission', 'view_kitchen');

        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $companyId = Company::mainCompanyId();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->from)->startOfDay()
            : now()->startOfDay();

        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $kdsFilter = $request->input('kds', 'all');
        if (!in_array($kdsFilter, ['all', 'cocina', 'cocina2', 'bar'], true)) {
            $kdsFilter = 'all';
        }

        $batches = RestaurantKitchenBatch::where('company_id', $companyId)
            ->where('status', RestaurantKitchenBatch::STATUS_DELIVERED)
            ->whereNotNull('finished_at')
            ->whereBetween('finished_at', [$from, $to])
            ->when($kdsFilter !== 'all', fn ($query) => $query->where('kds_destination', $kdsFilter))
            ->with(['order.table.floor', 'order'])
            ->withCount('items')
            ->orderByDesc('finished_at')
            ->get();

        $queueValues = $batches->pluck('queue_seconds')->filter(fn ($value) => !is_null($value));
        $prepValues = $batches->pluck('prep_seconds')->filter(fn ($value) => !is_null($value));
        $totalValues = $batches->pluck('total_seconds')->filter(fn ($value) => !is_null($value));

        $avgQueue = $queueValues->isNotEmpty() ? (int) round($queueValues->avg()) : null;
        $avgPrep = $prepValues->isNotEmpty() ? (int) round($prepValues->avg()) : null;
        $avgTotal = $totalValues->isNotEmpty() ? (int) round($totalValues->avg()) : null;

        $metrics = [
            'count' => $batches->count(),
            'avg_queue' => $avgQueue,
            'avg_prep' => $avgPrep,
            'avg_total' => $avgTotal,
            'queue_ratio' => ($avgTotal && $avgQueue !== null) ? (int) round($avgQueue / $avgTotal * 100) : null,
            'prep_ratio' => ($avgTotal && $avgPrep !== null) ? (int) round($avgPrep / $avgTotal * 100) : null,
        ];

        $daily = $batches
            ->groupBy(fn ($batch) => $batch->finished_at->format('Y-m-d'))
            ->map(function ($group, $date) {
                $queueValues = $group->pluck('queue_seconds')->filter(fn ($value) => !is_null($value));
                $prepValues = $group->pluck('prep_seconds')->filter(fn ($value) => !is_null($value));
                $totalValues = $group->pluck('total_seconds')->filter(fn ($value) => !is_null($value));

                return (object) [
                    'date' => $date,
                    'count' => $group->count(),
                    'avg_queue' => $queueValues->isNotEmpty() ? (int) round($queueValues->avg()) : null,
                    'avg_prep' => $prepValues->isNotEmpty() ? (int) round($prepValues->avg()) : null,
                    'avg_total' => $totalValues->isNotEmpty() ? (int) round($totalValues->avg()) : null,
                ];
            })
            ->sortKeysDesc()
            ->values();

        return view('restaurant.kitchen-history', compact('batches', 'metrics', 'from', 'to', 'kdsFilter', 'daily'));
    }

    public function markKitchenBatchPreparing($batchId)
    {
        if (Company::orderMode() === 'print') {
            return response()->json(['success' => false, 'message' => 'Modo Impresión activo — el KDS está inactivo'], 400);
        }

        try {
            $batch = RestaurantKitchenBatch::with('order')->findOrFail($batchId);

            if ($batch->status !== RestaurantKitchenBatch::STATUS_PENDING) {
                return response()->json(['success' => false, 'message' => 'El lote no está pendiente de preparación'], 400);
            }

            app(KitchenBatchService::class)->prepare($batch);

            event(new KitchenOrderUpdated($batch->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $batch->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $batch->company_id, now()->timestamp, 10);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deliverKitchenBatch($batchId)
    {
        if (Company::orderMode() === 'print') {
            return response()->json(['success' => false, 'message' => 'Modo Impresión activo — el KDS está inactivo'], 400);
        }

        try {
            $batch = RestaurantKitchenBatch::with('order')->findOrFail($batchId);

            if ($batch->status !== RestaurantKitchenBatch::STATUS_PREPARING) {
                return response()->json(['success' => false, 'message' => 'Primero debe marcar el lote en preparación'], 400);
            }

            app(KitchenBatchService::class)->deliver($batch);

            event(new KitchenOrderUpdated($batch->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $batch->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $batch->company_id, now()->timestamp, 10);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printKitchenBatch($batchId)
    {
        $batch = RestaurantKitchenBatch::with(['order.table.floor', 'order.user', 'items'])->findOrFail($batchId);
        $items = $batch->items->where('kitchen_status', '!=', 'CANCELLED');

        if ($items->isEmpty()) {
            return back()->with('error', 'No hay productos para imprimir');
        }

        $order = $batch->order;
        $order->setRelation('items', $items);

        $pdf = Pdf::loadView('restaurant.tickets.kitchen', compact('order'))
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('encoding', 'UTF-8');

        return $pdf->stream('ticket-cocina-' . $order->order_number . '-lote-' . $batch->batch_number . '.pdf');
    }

    function numberToWords($number)
    {
        $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        return ucfirst($f->format($number));
    }

    public function chargeOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cobrar'], 403);
        }

        try {
            $mainCompany = Company::getMainCompany();
            $companyId = $mainCompany->id;
            
            $cajaAbierta = CashRegister::where('company_id', $companyId)
                ->where('estado', 'ABIERTA')
                ->first();
                
            if (!$cajaAbierta) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay caja abierta. Abra una caja antes de cobrar.'
                ], 400);
            }
            
            $order = RestaurantOrder::with('items')->findOrFail($orderId);

            if ($order->status === 'OPEN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe enviar el pedido a cocina antes de cobrar'
                ], 400);
            }

            // A2/A5: solo items no cancelados y no pagados (remanente)
            $order->setRelation('items', $order->items
                ->where('kitchen_status', '!=', 'CANCELLED')
                ->whereNull('paid_invoice_id'));
            
            $items = $order->items;
            
            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no tiene productos por cobrar'
                ], 400);
            }

            // Opción B: enviar automáticamente a cocina/bar los items pendientes antes de facturar
            $pendingToSend = $items->where('kitchen_status', 'PENDING');
            if ($pendingToSend->isNotEmpty()) {
                app(KitchenBatchService::class)
                    ->createFromItems($order, $pendingToSend, auth()->id());

                $company = Company::find($order->company_id);
                if ($company && ($company->order_mode ?? 'kds') === 'print') {
                    try {
                        $printService = app(PrintService::class);
                        $printService->printKitchenOrder($order->fresh(['table.floor', 'user']), $pendingToSend);
                    } catch (\Exception $e) {
                        \Log::error('Auto-send print error: ' . $e->getMessage());
                    }
                }

                event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
                Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
                Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);
            }
            
            $customerId = $request->customer_id;
            $documentType = $request->document_type ?? 'NV';
            $payments = $request->payments ?? [['method' => 'EFECTIVO', 'amount' => $items->sum('total')]];
            $reference = $request->reference ?? '';
            $soloConsumo = $request->boolean('solo_consumo');

            $result = $this->createInvoiceFromItems(
                $order, $items, $customerId, $documentType, $payments,
                $soloConsumo, $reference, $cajaAbierta, $mainCompany, $companyId
            );
            $invoice = $result['invoice'];

            // Marcar items como pagados
            RestaurantOrderItem::whereIn('id', $items->pluck('id'))
                ->update(['paid_invoice_id' => $invoice->id]);

            $order->status = 'COMPLETED';
            $order->save();
            
            $order->table->update(['status' => 'AVAILABLE']);
            
            event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'full_number' => $result['full_number'],
                'total' => $result['total'],
                'document_type' => $result['document_type'],
                'vuelto' => $result['vuelto'],
                'sunat' => $result['sunat'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function splitChargeOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cobrar'], 403);
        }

        try {
            $mainCompany = Company::getMainCompany();
            $companyId = $mainCompany->id;
            
            $cajaAbierta = CashRegister::where('company_id', $companyId)
                ->where('estado', 'ABIERTA')
                ->first();
                
            if (!$cajaAbierta) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay caja abierta. Abra una caja antes de cobrar.'
                ], 400);
            }
            
            $order = RestaurantOrder::with('items')->findOrFail($orderId);

            if ($order->status === 'OPEN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe enviar el pedido a cocina antes de cobrar'
                ], 400);
            }

            $availableItems = $order->items
                ->where('kitchen_status', '!=', 'CANCELLED')
                ->whereNull('paid_invoice_id');

            if ($availableItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no tiene productos por dividir'
                ], 400);
            }

            // Opción B: enviar automáticamente a cocina/bar los items pendientes antes de dividir/cobrar
            $pendingToSend = $availableItems->where('kitchen_status', 'PENDING');
            if ($pendingToSend->isNotEmpty()) {
                app(KitchenBatchService::class)
                    ->createFromItems($order, $pendingToSend, auth()->id());

                $company = Company::find($order->company_id);
                if ($company && ($company->order_mode ?? 'kds') === 'print') {
                    try {
                        $printService = app(PrintService::class);
                        $printService->printKitchenOrder($order->fresh(['table.floor', 'user']), $pendingToSend);
                    } catch (\Exception $e) {
                        \Log::error('Auto-send print error: ' . $e->getMessage());
                    }
                }

                event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
                Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
                Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);
            }

            $splits = $request->splits ?? [];
            if (empty($splits)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar al menos una división'
                ], 400);
            }

            // A3: validar repartición exacta por item
            $requestedByItemId = [];
            foreach ($splits as $split) {
                foreach (($split['items'] ?? []) as $itemSel) {
                    $itemId = $itemSel['item_id'] ?? null;
                    if ($itemId === null) continue;
                    $requestedByItemId[$itemId] = ($requestedByItemId[$itemId] ?? 0) + (float) ($itemSel['quantity'] ?? 0);
                }
            }

            foreach ($availableItems as $item) {
                $requested = $requestedByItemId[$item->id] ?? 0;
                if ($requested > (float) $item->quantity + 0.001) {
                    return response()->json([
                        'success' => false,
                        'message' => "La división de '{$item->product_name}' excede lo disponible. Disponible: {$item->quantity}, solicitado: {$requested}"
                    ], 400);
                }
            }

            $invoices = [];

            foreach ($splits as $split) {
                $customerId = $split['customer_id'] ?? null;
                $documentType = $split['document_type'] ?? 'NV';
                $soloConsumo = !empty($split['solo_consumo']);

                $splitItems = collect();
                foreach (($split['items'] ?? []) as $itemSel) {
                    $origItem = $availableItems->firstWhere('id', $itemSel['item_id']);
                    if (!$origItem) continue;
                    $qty = (float) ($itemSel['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    if (abs($qty - (float) $origItem->quantity) > 0.001) {
                        // A3: cantidad parcial → clon pagado + reducir original
                        $clone = $origItem->replicate();
                        $clone->quantity = $qty;
                        $clone->total = round($qty * (float) $origItem->unit_price, 2);
                        $clone->save();
                        $splitItems->push($clone);

                        $origItem->quantity = round((float) $origItem->quantity - $qty, 2);
                        $origItem->total = round((float) $origItem->quantity * (float) $origItem->unit_price, 2);
                        $origItem->save();
                    } else {
                        $splitItems->push($origItem);
                    }
                }

                if ($splitItems->isEmpty()) continue;

                $totalSplit = $splitItems->sum('total');
                $payments = $split['payments'] ?? [['method' => 'EFECTIVO', 'amount' => $totalSplit]];

                $result = $this->createInvoiceFromItems(
                    $order, $splitItems, $customerId, $documentType, $payments,
                    $soloConsumo, '', $cajaAbierta, $mainCompany, $companyId
                );
                $invoice = $result['invoice'];

                // Marcar items pagados
                RestaurantOrderItem::whereIn('id', $splitItems->pluck('id'))
                    ->update(['paid_invoice_id' => $invoice->id]);

                $invoices[] = [
                    'id' => $invoice->id,
                    'full_number' => $result['full_number'],
                    'total' => $result['total'],
                    'document_type' => $documentType,
                    'vuelto' => $result['vuelto'],
                    'sunat' => $result['sunat'] ?? null,
                ];
            }

            // A1: verificar si todos los items activos están pagados
            $order->load('items');
            $remainingItems = $order->items
                ->where('kitchen_status', '!=', 'CANCELLED')
                ->whereNull('paid_invoice_id');
            $remainingTotal = round($remainingItems->sum('total'), 2);

            $orderCompleted = false;
            if ($remainingItems->isEmpty()) {
                $order->status = 'COMPLETED';
                $order->save();
                $order->table->update(['status' => 'AVAILABLE']);
                $orderCompleted = true;
            } else {
                $this->updateOrderTotals($order);
            }

            event(new KitchenOrderUpdated($order->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

            return response()->json([
                'success' => true,
                'invoices' => $invoices,
                'remaining_total' => $remainingTotal,
                'order_completed' => $orderCompleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function createInvoiceFromItems($order, $items, $customerId, $documentType, $payments, $soloConsumo, $reference, $cajaAbierta, $mainCompany, $companyId)
    {
        $serie = Serie::where('company_id', $companyId)
            ->where('tipo_documento', $documentType)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$serie) {
            $prefix = $documentType === 'NV' ? 'NV' : ($documentType === '01' ? 'F' : 'B');
            $serie = Serie::create([
                'company_id' => $companyId,
                'tipo_documento' => $documentType,
                'serie' => $prefix . '001',
                'numero_actual' => 0,
                'estado' => 'ACTIVO',
            ]);
        }

        $nextNumber = $serie->getNextNumber();
        $total = round($items->sum('total'), 2);

        // Seguridad: si no hay pagos válidos, registrar EFECTIVO por el total
        if (empty($payments) || collect($payments)->sum('amount') <= 0) {
            $payments = [['method' => 'EFECTIVO', 'amount' => $total]];
        }

        // Los pagos virtuales (YAPE/PLIN/TARJETA) son EXACTOS: no pueden exceder el total (no hay vuelto en ellos)
        foreach ($payments as $p) {
            $m = strtoupper($p['method']);
            if (in_array($m, ['YAPE', 'PLIN', 'TARJETA']) && ((float) $p['amount']) > $total + 0.001) {
                throw new \Exception('Los pagos con Yape/Plin/Tarjeta de crédito son exactos y no pueden exceder el total de la venta');
            }
        }

        $totalPagado = collect($payments)->sum('amount');
        $vuelto = max(0, $totalPagado - $total);

        // El vuelto solo se descuenta del EFECTIVO (efectivo neto = efectivo - vuelto)
        if ($vuelto > 0) {
            $restante = $vuelto;
            $nuevos = [];
            foreach ($payments as $p) {
                if (strtoupper($p['method']) === 'EFECTIVO') {
                    $restar = min((float) $p['amount'], $restante);
                    $nuevo = round((float) $p['amount'] - $restar, 2);
                    $restante -= $restar;
                    if ($nuevo > 0) {
                        $nuevos[] = ['method' => 'EFECTIVO', 'amount' => $nuevo];
                    }
                } else {
                    $nuevos[] = $p;
                }
            }
            if ($restante > 0.001) {
                throw new \Exception('El efectivo no cubre el vuelto de S/ ' . number_format($vuelto, 2));
            }
            $payments = $nuevos ?: [['method' => 'EFECTIVO', 'amount' => 0]];
        }

        $igvRate = $mainCompany ? $mainCompany->getIgvRate() : 0.18;
        $subtotal = $total / (1 + $igvRate);
        $igv = $total - $subtotal;

        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customerId ?: null,
            'tipo_documento' => $documentType,
            'serie' => $serie->serie,
            'numero' => $nextNumber,
            'full_number' => $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => now()->format('Y-m-d'),
            'hora_emision' => now()->format('H:i:s'),
            'fecha_vencimiento' => now()->format('Y-m-d'),
            'moneda' => 'PEN',
            'gravado' => round($subtotal, 2),
            'igv' => round($igv, 2),
            'total' => $total,
            'subtotal' => round($subtotal, 2),
            'total_letras' => $this->numberToWords($total) . ' SOLES',
            'metodo_pago' => collect($payments)->map(fn($p) => $p['method'] . '/' . $p['amount'])->implode(' + '),
            'referencia_pago' => $reference,
            'sunat_estado' => 'PENDIENTE',
        ]);

        $productIds = $items->pluck('product_id')->filter()->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        if ($soloConsumo) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => null,
                'codigo' => '90101801',
                'descripcion' => 'POR CONSUMO',
                'cantidad' => 1,
                'umedida' => 'NIU',
                'precio_unitario' => round($subtotal, 2),
                'precio_venta' => $total,
                'igv' => round($igv, 2),
                'tipo_afectacion' => '10',
                'igv_percent' => round($igvRate * 100, 2),
                'detalle_consumo' => $items->map(fn($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ])->values()->toArray(),
            ]);
        } else {
            foreach ($items as $item) {
                $unitBase = $item->unit_price / (1 + $igvRate);
                $itemIgv = $item->unit_price - $unitBase;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'codigo' => $item->product_code ?? '',
                    'descripcion' => $item->product_name,
                    'cantidad' => $item->quantity,
                    'umedida' => 'NIU',
                    'precio_unitario' => round($unitBase, 2),
                    'precio_venta' => $item->unit_price * $item->quantity,
                    'igv' => round($itemIgv, 2),
                    'tipo_afectacion' => '10',
                    'igv_percent' => round($igvRate * 100, 2),
                ]);
            }
        }

        // Stock: deduct once per item
        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            if ($product) {
                if ($product->is_composite) {
                    foreach ($product->components as $component) {
                        $componentProduct = $component->component;
                        if ($componentProduct) {
                            $componentProduct->decrement('stock', $component->quantity * $item->quantity);
                        }
                    }
                } else {
                    $product->decrement('stock', $item->quantity);
                }
            }
        }

        $serie->increment('numero_actual');
        $fullNumber = $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        $cajaAbierta->cantidad_ventas = ($cajaAbierta->cantidad_ventas ?? 0) + 1;
        $cajaAbierta->total_ventas = ($cajaAbierta->total_ventas ?? 0) + $total;

        foreach ($payments as $payment) {
            $paymentField = match($payment['method']) {
                'EFECTIVO' => 'ventas_efectivo',
                'TARJETA' => 'ventas_tarjeta',
                'YAPE' => 'ventas_yape',
                'PLIN' => 'ventas_plin',
                default => 'ventas_otro',
            };
            $cajaAbierta->$paymentField = ($cajaAbierta->$paymentField ?? 0) + $payment['amount'];
        }
        $cajaAbierta->save();

        // Envío inmediato: solo facturas (01) se envían al momento; boletas van al Resumen Diario (09:00)
        $sunatResult = null;
        if ($documentType === '01') {
            try {
                $sunatResult = (new \App\Services\GreenterService())->sendInvoice($invoice->fresh());
            } catch (\Exception $e) {
                \Log::error('Restaurante envío inmediato factura error: ' . $e->getMessage());
                $sunatResult = ['success' => false, 'code' => 'EXCEPTION', 'description' => $e->getMessage()];
            }
        }

        return [
            'invoice' => $invoice,
            'full_number' => $fullNumber,
            'total' => $total,
            'document_type' => $documentType,
            'vuelto' => $vuelto,
            'sunat' => $sunatResult,
        ];
    }

private function updateOrderTotals(RestaurantOrder $order)
    {
        $order->load('items');
        $items = $order->items
            ->where('kitchen_status', '!=', 'CANCELLED')
            ->whereNull('paid_invoice_id');
        $company = Company::find($order->company_id);
        $igvRate = $company ? $company->getIgvRate() : 0.18;
        
        $subtotal = $items->sum('total') / (1 + $igvRate);
        $igv = $items->sum('total') - $subtotal;
        $total = $items->sum('total');

        $order->update([
            'subtotal' => round($subtotal, 2),
            'igv' => round($igv, 2),
            'total' => round($total, 2),
        ]);
    }

    public function saveOrderNotes(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);

            $order = RestaurantOrder::findOrFail($orderId);
            $order->update(['notes' => $validated['notes'] ?? null]);

            return response()->json([
                'success' => true,
                'order' => $order->fresh(['items']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function lockTable(Request $request, $tableId)
    {
        try {
            $table = RestaurantTable::findOrFail($tableId);
            $userId = Auth::id();

            if ($table->isLocked() && !$table->isLockedBy($userId) && !$table->isLockExpired()) {
                $lockedBy = $table->lockedByUser;
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa en uso por ' . ($lockedBy ? $lockedBy->name : 'otro usuario'),
                ]);
            }

            if ($table->isLockExpired()) {
                $table->unlock();
            }

            $table->lock($userId);

            return response()->json([
                'success' => true,
                'locked_by' => $userId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function unlockTable(Request $request, $tableId)
    {
        try {
            $table = RestaurantTable::findOrFail($tableId);
            $userId = Auth::id();

            if ($table->isLocked() && !$table->isLockedBy($userId) && !auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desbloquear esta mesa',
                ], 403);
            }

            $table->unlock();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function unlockAllTables(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user->isAdmin() && $user->role !== 'cajero') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para desbloquear mesas',
                ], 403);
            }

            $companyId = $request->company_id ?? Company::first()->id;
            RestaurantTable::where('company_id', $companyId)
                ->whereNotNull('locked_by')
                ->update(['locked_by' => null, 'locked_at' => null]);

            return response()->json(['success' => true, 'message' => 'Todas las mesas desbloqueadas']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function kioskOrders(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $orders = RestaurantOrder::where('company_id', $companyId)
            ->where('order_type', 'kiosko')
            ->whereIn('status', ['PENDING_PAYMENT', 'SENT_TO_KITCHEN', 'PREPARING', 'DELIVERED'])
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('restaurant.kiosk-orders', compact('orders', 'companyId'));
    }

    public function kioskSendToKitchen($orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            if ($order->status !== 'PENDING_PAYMENT') {
                return response()->json(['success' => false, 'message' => 'El pedido ya fue enviado a cocina']);
            }

            $batches = app(KitchenBatchService::class)
                ->createFromItems($order, $order->items, auth()->id());

            event(new \App\Events\KitchenOrderUpdated($order->company_id, 'kitchen'));
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            Cache::put('restaurant_updated_' . $order->company_id, now()->timestamp, 10);

            $company = Company::find($order->company_id);
            if ($company && ($company->order_mode ?? 'kds') === 'print') {
                try {
                    $printService = app(\App\Services\PrintService::class);
                    $printService->printKitchenOrder($order->fresh(['table', 'user']), $order->items);
                } catch (\Exception $e) {
                    \Log::error('Kiosko kitchen print error: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Pedido enviado a cocina']);
        } catch (\Exception $e) {
            \Log::error('Kiosko send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function chargeKioskOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            if (!in_array($order->status, ['SENT_TO_KITCHEN', 'PREPARING', 'DELIVERED', 'READY'], true)) {
                return response()->json(['success' => false, 'message' => 'Debe enviar el pedido a cocina antes de cobrar']);
            }

            $request->merge(['document_type' => $request->document_type ?? 'NV']);
            $request->merge(['payments' => $request->payments ?? [['method' => 'EFECTIVO', 'amount' => $order->total]]]);

            $request->request->set('customer_id', $request->customer_id);
            $request->request->set('reference', 'KIOSKO-' . $order->order_number);

            $chargeResult = $this->chargeOrder($request, $orderId);

            if ($chargeResult instanceof \Illuminate\Http\JsonResponse) {
                $data = $chargeResult->getData(true);
                if ($data['success']) {
                    if (isset($data['invoice_id'])) {
                        \App\Models\Invoice::where('id', $data['invoice_id'])
                            ->update(['order_source' => 'kiosko']);
                    }
                    return response()->json(['success' => true, 'message' => 'Pedido cobrado'] + $data);
                }
                return $chargeResult;
            }

            return response()->json(['success' => false, 'message' => 'Error al procesar el cobro']);
        } catch (\Exception $e) {
            \Log::error('Kiosko charge error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function checkAuthorizedPassword(?string $password): bool
    {
        $user = auth()->user();
        if (!$user || !$password) {
            return false;
        }
        if (!$user->hasPermission('authorize_cancel_orders')) {
            return false;
        }
        return Hash::check($password, $user->password);
    }
}

