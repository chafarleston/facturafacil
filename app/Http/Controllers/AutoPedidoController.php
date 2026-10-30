<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AutoPedidoController extends Controller
{
    private function branding(Company $company): array
    {
        $templates = ['modern', 'elegant', 'minimal', 'classic', 'dark', 'tropical'];
        $template = \App\Models\Setting::web('template', 'modern');
        if (!in_array($template, $templates, true)) {
            $template = 'modern';
        }

        $fonts = ['Poppins', 'Montserrat', 'Raleway', 'Playfair Display', 'Lora', 'Nunito'];
        $font = \App\Models\Setting::web('font', 'Poppins');
        if (!in_array($font, $fonts, true)) {
            $font = 'Poppins';
        }

        $logo = \App\Models\Setting::web('logo', '');
        $logoUrl = $logo ? asset('storage/' . $logo) : $company->getLogoUrl();

        return [
            'template' => $template,
            'font' => $font,
            'fontUrl' => 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font) . ':wght@400;500;600;700&display=swap',
            'primary_color' => \App\Models\Setting::web('primary_color', '#e94560'),
            'secondary_color' => \App\Models\Setting::web('secondary_color', '#3a1c4a'),
            'logo' => $logo,
            'logoUrl' => $logoUrl,
            'logo_height' => (int) \App\Models\Setting::web('logo_height', 56),
            'company_name' => $company->nombre_comercial ?: $company->razon_social,
        ];
    }

    public function index()
    {
        $company = Company::getMainCompany();
        $companyId = $company->id;

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

        $auxiliaryItems = \App\Models\AuxiliaryItem::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $showProductImages = \App\Models\Setting::showProductImages('kiosko');
        $showCategoryImages = \App\Models\Setting::showCategoryImages('kiosko');
        $categoriesOnly = \App\Models\Setting::categoriesOnly('kiosko');
        $deliveryType = \App\Models\Setting::kiosk('delivery_type', 'local');

        $web = $this->branding($company);

        return view('autopedido.index', compact('products', 'categories', 'companyId', 'showProductImages', 'showCategoryImages', 'categoriesOnly', 'deliveryType', 'auxiliaryItems', 'company', 'web'));
    }

    public function confirmOrder(Request $request)
    {
        $deliveryType = \App\Models\Setting::kiosk('delivery_type', 'local');
        $isWeb = $deliveryType === 'web';

        $validated = $request->validate([
            'items' => 'required|json',
            'customer_name' => $isWeb ? 'required|string|max:150' : 'nullable|string|max:150',
            'delivery_address' => $isWeb ? 'required|string|max:250' : 'nullable|string|max:250',
            'delivery_reference' => 'nullable|string|max:250',
            'contact_number' => $isWeb ? 'required|string|max:50' : 'nullable|string|max:50',
        ]);

        $companyId = Company::getMainCompany()->id;
        $items = json_decode($request->items, true);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío']);
        }

        $cashRegister = \App\Models\CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        if (!$cashRegister) {
            return response()->json(['success' => false, 'message' => 'No hay caja abierta. No se puede realizar el pedido.']);
        }

        $kioskoTable = RestaurantTable::where('company_id', $companyId)
            ->where('is_for_kiosko', true)
            ->first();

        if (!$kioskoTable) {
            return response()->json(['success' => false, 'message' => 'Mesa Kiosko no configurada']);
        }

        $order = RestaurantOrder::create([
            'company_id' => $companyId,
            'table_id' => $kioskoTable->id,
            'user_id' => null,
            'order_number' => RestaurantOrder::generateKioskoOrderNumber($companyId),
            'status' => 'PENDING_PAYMENT',
            'order_type' => 'kiosko',
            'delivery_type' => $deliveryType,
            'customer_name' => $isWeb ? ($validated['customer_name'] ?? null) : null,
            'delivery_address' => $isWeb ? ($validated['delivery_address'] ?? null) : null,
            'delivery_reference' => $isWeb ? (($validated['delivery_reference'] ?? null) ?: null) : null,
            'contact_number' => $isWeb ? ($validated['contact_number'] ?? null) : null,
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
                'notes' => $item['notes'] ?? null,
                'auxiliary_items' => $item['auxiliary_items'] ?? null,
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
        $company = Company::getMainCompany();
        $web = $this->branding($company);
        $order = RestaurantOrder::with(['items'])->findOrFail($orderId);

        $successTexts = [
            'ticket' => \App\Models\Setting::kiosk('success_ticket_text', 'Su ticket se está imprimiendo'),
            'pay' => \App\Models\Setting::kiosk('success_pay_text', 'Pase a caja para pagar'),
            'prepare' => \App\Models\Setting::kiosk('success_prepare_text', 'Su pedido comenzará a prepararse después del pago'),
        ];

        return view('autopedido.success', compact('order', 'successTexts', 'company', 'web'));
    }
}
