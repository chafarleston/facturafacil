<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\Product;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('restaurant.index', compact('floors', 'products', 'categories', 'companyId'));
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
                    'notes' => $request->notes,
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
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->quantity = $validated['quantity'];
        $item->total = $item->quantity * $item->unit_price;
        $item->notes = $request->notes ?? $item->notes;
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

    public function sendToKitchen(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:restaurant_orders,id',
        ]);

        $order = RestaurantOrder::with('items')->findOrFail($validated['order_id']);
        
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

        return response()->json([
            'success' => true,
            'message' => 'Pedido enviado a cocina',
            'items_sent' => $pendingItems->count()
        ]);
    }

    public function printKitchenTicket(Request $request, $orderId)
    {
        $order = RestaurantOrder::with(['items' => function($q) {
            $q->whereIn('kitchen_status', ['SENT', 'PENDING']);
        }, 'table', 'user'])->findOrFail($orderId);

        if ($order->items->isEmpty()) {
            return back()->with('error', 'No hay productos para imprimir');
        }

        $pdf = Pdf::loadView('restaurant.tickets.kitchen', compact('order'));
        $pdf->setPaper([0, 0, 80, 200], 'portrait');
        
        return $pdf->stream('ticket-cocina-' . $order->order_number . '.pdf');
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
}
