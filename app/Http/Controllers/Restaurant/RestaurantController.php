<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Serie;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        
        $floors = Floor::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->with(['tables' => function($q) {
                $q->with(['orders' => function($oq) {
                    $oq->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                       ->with('items');
                }]);
            }])
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->orderBy('descripcion')
            ->get();

        $categories = Category::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        $customers = Customer::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->get();

        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereIn('tipo_documento', ['01', '03', 'NV'])
            ->get();

        return view('restaurant.index', compact('floors', 'products', 'categories', 'customers', 'series', 'companyId'));
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

            return response()->json([
                'success' => true,
                'order' => $order,
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
                'quantity' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string|max:500',
            ]);

            $product = Product::findOrFail($validated['product_id']);
            $order = RestaurantOrder::findOrFail($orderId);

            $existingItem = RestaurantOrderItem::where('restaurant_order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('kitchen_status', 'PENDING')
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
        
        $validated = $request->validate([
            'quantity' => 'nullable|numeric|min:0.01',
            'quantity_delta' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        if (isset($validated['quantity_delta'])) {
            $item->quantity += $validated['quantity_delta'];
            if ($item->quantity < 0.1) {
                $item->quantity = 0.1;
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

    public function removeItem($itemId)
    {
        $item = RestaurantOrderItem::findOrFail($itemId);
        $order = $item->order;
        
        $item->delete();
        $this->updateOrderTotals($order);

        if ($order->items()->count() == 0) {
            $order->update(['status' => 'CANCELLED']);
            $order->table->update(['status' => 'AVAILABLE']);
        }

        return response()->json(['success' => true]);
    }

    public function sendToKitchen(Request $request, $orderId)
    {
        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            
            $pendingItems = $order->items()->where('kitchen_status', 'PENDING')->get();
            
            if ($pendingItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos pendientes para enviar'
                ]);
            }

            foreach ($pendingItems as $item) {
                $item->kitchen_status = 'SENT';
                $item->sent_to_kitchen_at = now();
                $item->save();
            }

            $order->status = 'SENT_TO_KITCHEN';
            $order->save();

            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);

            return response()->json([
                'success' => true,
                'message' => 'Pedido enviado a cocina',
                'items_sent' => $pendingItems->count()
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
            $q->whereIn('kitchen_status', ['SENT']);
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

        $company = Company::getMainCompany();

        $pdf = Pdf::loadView('restaurant.tickets.prebill', compact('order', 'company'))
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('encoding', 'UTF-8');
        
        return $pdf->stream('precuenta-' . $order->order_number . '.pdf');
    }

    public function markItemReady($itemId)
    {
        $item = RestaurantOrderItem::findOrFail($itemId);
        $item->kitchen_status = 'READY';
        $item->save();

        $order = $item->order;
        $allReady = $order->items()->where('kitchen_status', '!=', 'READY')->where('kitchen_status', '!=', 'DELIVERED')->count() == 0;
        if ($allReady) {
            $order->status = 'READY';
            $order->save();
        }

        Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);

        return response()->json(['success' => true]);
    }

    public function deliverItem($itemId)
    {
        $item = RestaurantOrderItem::findOrFail($itemId);
        $item->kitchen_status = 'DELIVERED';
        $item->save();

        return response()->json(['success' => true]);
    }

    public function closeOrder(Request $request, $orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para cerrar mesas'], 403);
        }

        $order = RestaurantOrder::with('items')->findOrFail($orderId);
        
        $order->update(['status' => 'COMPLETED']);
        $order->table->update(['status' => 'AVAILABLE']);

        return response()->json([
            'success' => true,
            'message' => 'Mesa cerrada exitosamente'
        ]);
    }

    public function cancelOrder($orderId)
    {
        if (auth()->user()->isMozo()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para anular pedidos'], 403);
        }
        
        $order = RestaurantOrder::findOrFail($orderId);
        
        $order->items()->delete();
        $order->update(['status' => 'CANCELLED']);
        $order->table->update(['status' => 'AVAILABLE']);

        return response()->json(['success' => true]);
    }

    public function getActiveOrders(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        
        $orders = RestaurantOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->with(['table.floor', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    public function kitchenIndex(Request $request)
    {
        return view('restaurant.kds');
    }

    public function getKitchenOrders(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        
        $orders = RestaurantOrder::where('company_id', $companyId)
            ->whereIn('status', ['OPEN', 'SENT_TO_KITCHEN', 'READY'])
            ->whereHas('items', function($q) {
                $q->whereIn('kitchen_status', ['SENT', 'READY']);
            })
            ->with(['items' => function($q) {
                $q->whereIn('kitchen_status', ['SENT', 'READY']);
            }, 'table.floor', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedOrders = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'table_name' => $order->table ? $order->table->name : 'Mesa',
                'floor_name' => $order->table && $order->table->floor ? $order->table->floor->name : null,
                'user_name' => $order->user ? $order->user->name : null,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'kitchen_status' => $item->kitchen_status,
                        'notes' => $item->notes,
                    ];
                })
            ];
        });

        return response()->json(['success' => true, 'orders' => $formattedOrders])
            ->header('Cache-Control', 'no-cache, must-revalidate, no-store, private')
            ->header('Pragma', 'no-cache');
    }

    public function kitchenStream(Request $request)
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
        
        $lastOrderCount = -1;
        $lastStatusChange = null;
        $lastCheck = time();
        $lastCacheKey = 'kitchen_updated_' . $companyId;
        
        while (true) {
            if (connection_aborted()) {
                break;
            }
            
            $currentTime = time();
            $currentCache = Cache::get($lastCacheKey);
            
            $orders = RestaurantOrder::where('company_id', $companyId)
                ->whereIn('status', ['OPEN', 'SENT_TO_KITCHEN', 'READY'])
                ->whereHas('items', function($q) {
                    $q->whereIn('kitchen_status', ['SENT', 'READY']);
                })
                ->with(['items' => function($q) {
                    $q->whereIn('kitchen_status', ['SENT', 'READY']);
                }, 'table.floor', 'user'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            $shouldSend = false;
            
            if ($currentCache !== $lastStatusChange) {
                $shouldSend = true;
                $lastStatusChange = $currentCache;
            }
            
            if ($orders->count() !== $lastOrderCount) {
                $shouldSend = true;
                $lastOrderCount = $orders->count();
            }
            
            if ($currentTime - $lastCheck >= 5) {
                $shouldSend = true;
                $lastCheck = $currentTime;
            }
            
            if ($shouldSend) {
                $formattedOrders = $orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'table_id' => $order->table_id,
                        'table_name' => $order->table ? $order->table->name : 'Mesa',
                        'floor_name' => $order->table && $order->table->floor ? $order->table->floor->name : null,
                        'user_name' => $order->user ? $order->user->name : null,
                        'notes' => $order->notes,
                        'created_at' => $order->created_at->toIso8601String(),
                        'items' => $order->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'kitchen_status' => $item->kitchen_status,
                                'notes' => $item->notes,
                            ];
                        })
                    ];
                });
                
                $eventId = time();
                echo "id: {$eventId}\n";
                echo "data: " . json_encode(['success' => true, 'orders' => $formattedOrders, 'timestamp' => date('H:i:s')]) . "\n\n";
                flush();
            }
            
            usleep(1000000);
        }
        
        return response()->json(['success' => true]);
    }

    public function markKitchenReady($orderId)
    {
        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            
            $order->items()->whereIn('kitchen_status', ['SENT', 'PENDING'])->update([
                'kitchen_status' => 'READY'
            ]);
            
            $order->status = 'READY';
            $order->save();

            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deliverKitchenOrder($orderId)
    {
        try {
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            
            $order->items()->update(['kitchen_status' => 'DELIVERED']);
            $order->status = 'COMPLETED';
            $order->save();

            $order->table->update(['status' => 'AVAILABLE']);

            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
                ->where('user_id', auth()->id())
                ->first();
                
            if (!$cajaAbierta) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay caja abierta. Abra una caja antes de cobrar.'
                ], 400);
            }
            
            $order = RestaurantOrder::with('items')->findOrFail($orderId);
            
            if ($order->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no tiene productos'
                ], 400);
            }
            
            $customerId = $request->customer_id;
            $documentType = $request->document_type ?? 'NV';
            $paymentMethod = $request->payment_method ?? 'EFECTIVO';
            $reference = $request->reference ?? '';
            
            $serie = Serie::where('company_id', $companyId)
                ->where('tipo_documento', $documentType)
                ->where('estado', 'ACTIVO')
                ->first();
            
            $lastInvoice = Invoice::where('company_id', $companyId)
                ->where('tipo_documento', $documentType);
            
            if ($serie) {
                $lastInvoice = $lastInvoice->where('serie', $serie->serie);
            }
            
            $lastInvoice = $lastInvoice->orderBy('numero', 'desc')->first();
            $nextNumber = $lastInvoice ? ((int)$lastInvoice->numero + 1) : 1;
            
            if (!$serie) {
                $prefix = $documentType === 'NV' ? 'NV' : ($documentType === '01' ? 'F' : 'B');
                $serie = Serie::create([
                    'company_id' => $companyId,
                    'tipo_documento' => $documentType,
                    'serie' => $prefix . '001',
                    'numero_actual' => $nextNumber,
                    'estado' => 'ACTIVO',
                ]);
            }
            
            $items = $order->items;
            $total = $items->sum('total');
            $subtotal = $total / 1.18;
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
                'total' => round($total, 2),
                'subtotal' => round($subtotal, 2),
                'total_letras' => $this->numberToWords(round($total, 2)) . ' SOLES',
                'metodo_pago' => $paymentMethod,
                'referencia_pago' => $reference,
                'sunat_estado' => 'PENDIENTE',
                'estado' => 'ACTIVO',
            ]);
            
            foreach ($items as $item) {
                $unitBase = $item->unit_price / 1.18;
                $itemIgv = $item->unit_price - $unitBase;
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'codigo' => $item->product_code ?? '',
                    'descripcion' => $item->product_name,
                    'cantidad' => $item->quantity,
                    'umedida' => 'NIU',
                    'precio_unitario' => round($unitBase, 2),
                    'precio_venta' => $item->unit_price,
                    'igv' => round($itemIgv, 2),
                    'tipo_afectacion' => '10',
                    'igv_percent' => 18,
                ]);
                
                $product = Product::find($item->product_id);
                if ($product && $product->stock > 0) {
                    $product->decrement('stock', $item->quantity);
                }
            }
            
            $serie->increment('numero_actual');
            
            $order->status = 'COMPLETED';
            $order->save();
            
            $order->table->update(['status' => 'AVAILABLE']);
            
            Cache::put('kitchen_updated_' . $order->company_id, now()->timestamp, 10);
            
            $fullNumber = $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            
            $cajaAbierta->cantidad_ventas = ($cajaAbierta->cantidad_ventas ?? 0) + 1;
            $cajaAbierta->total_ventas = ($cajaAbierta->total_ventas ?? 0) + round($total, 2);
            
            $paymentField = match($paymentMethod) {
                'EFECTIVO' => 'ventas_efectivo',
                'TARJETA' => 'ventas_tarjeta',
                'YAPE' => 'ventas_yape',
                'PLIN' => 'ventas_plin',
                default => 'ventas_otro',
            };
            $cajaAbierta->$paymentField = ($cajaAbierta->$paymentField ?? 0) + round($total, 2);
            $cajaAbierta->save();
            
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'full_number' => $fullNumber,
                'total' => round($total, 2),
                'document_type' => $documentType,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

private function updateOrderTotals(RestaurantOrder $order)
    {
        $items = $order->items;
        
        $subtotal = $items->sum('total') / 1.18;
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
}
