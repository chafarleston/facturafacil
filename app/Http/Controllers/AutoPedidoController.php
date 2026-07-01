<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AutoPedidoController extends Controller
{
    public function index()
    {
        $companyId = Company::getMainCompany()->id;

        $products = Cache::remember('kiosko_products_' . $companyId, 60, function () use ($companyId) {
            return Product::where('company_id', $companyId)
                ->where('estado', 'ACTIVO')
                ->orderBy('descripcion')
                ->get();
        });

        $categories = Cache::remember('kiosko_categories_' . $companyId, 60, function () use ($companyId) {
            return Category::where('company_id', $companyId)
                ->whereIn('estado', ['ACTIVO', 'ACT'])
                ->orderBy('nombre')
                ->get();
        });

        return view('autopedido.index', compact('products', 'categories'));
    }

    public function confirmOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|json',
        ]);

        $companyId = Company::getMainCompany()->id;
        $items = json_decode($request->items, true);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío']);
        }

        $order = RestaurantOrder::create([
            'company_id' => $companyId,
            'table_id' => null,
            'user_id' => null,
            'order_number' => RestaurantOrder::generateOrderNumber(),
            'status' => 'PENDING_PAYMENT',
            'order_type' => 'kiosko',
        ]);

        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $unitPrice = $product->precio;
            $qty = max(1, (int)($item['quantity'] ?? 1));

            RestaurantOrderItem::create([
                'restaurant_order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->descripcion,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total' => $unitPrice * $qty,
                'kitchen_status' => 'PENDING',
                'kds_destination' => $product->kds_destination ?? 'cocina',
            ]);
            $total += $unitPrice * $qty;
        }

        $order->update(['total' => $total]);

        // Print ticket
        try {
            $printService = app(PrintService::class);
            $printService->printAutoPedidoTicket($order);
        } catch (\Exception $e) {
            \Log::error('Kiosko print error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $total,
        ]);
    }

    public function success($orderId)
    {
        $order = RestaurantOrder::with(['items'])->findOrFail($orderId);
        return view('autopedido.success', compact('order'));
    }
}
