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
use Illuminate\Support\Facades\DB;

class AutoPedidoController extends Controller
{
    private function branding(Company $company): array
    {
        $templates = ['modern', 'elegant', 'minimal', 'classic', 'dark', 'tropical', 'chifa'];
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

        $primaryColor = \App\Models\Setting::web('primary_color', '#e94560') ?: '#e94560';
        $secondaryColor = \App\Models\Setting::web('secondary_color', '#3a1c4a') ?: '#3a1c4a';

        if ($template === 'chifa') {
            if ($primaryColor === '#e94560') {
                $primaryColor = '#9e1b1b';
            }
            if ($secondaryColor === '#3a1c4a') {
                $secondaryColor = '#d4af37';
            }
        }

        return [
            'template' => $template,
            'font' => $font,
            'fontUrl' => 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font) . ':wght@400;500;600;700&display=swap',
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
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
            return Product::with('disposableProduct')
                ->where('company_id', $companyId)
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
        $deliveryEnabled = \App\Models\Setting::kioskDeliveryEnabled();
        $deliveryZones = array_values(array_filter(
            \App\Models\Setting::kioskDeliveryZones(),
            fn ($zone) => trim((string) ($zone['name'] ?? '')) !== ''
        ));

        $web = $this->branding($company);

        return view('autopedido.index', compact('products', 'categories', 'companyId', 'showProductImages', 'showCategoryImages', 'categoriesOnly', 'deliveryEnabled', 'deliveryZones', 'auxiliaryItems', 'company', 'web'));
    }

    public function confirmOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|json',
            'delivery_type' => 'required|in:local,web',
            'delivery_zone' => 'nullable|integer|min:1|max:9',
            'customer_name' => 'nullable|string|max:150',
            'delivery_address' => 'nullable|string|max:250',
            'delivery_reference' => 'nullable|string|max:250',
            'contact_number' => 'nullable|string|max:50',
        ]);

        $isWeb = $validated['delivery_type'] === 'web';

        if ($isWeb) {
            $request->validate([
                'delivery_zone' => 'required|integer|min:1|max:9',
                'customer_name' => 'required|string|max:150',
                'delivery_address' => 'required|string|max:250',
                'contact_number' => 'required|string|max:50',
            ]);

            if (!\App\Models\Setting::kioskDeliveryEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El delivery no está habilitado.',
                ], 422);
            }
        }

        $companyId = Company::getMainCompany()->id;
        $items = json_decode($validated['items'], true);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío']);
        }

        $deliveryZone = null;
        $deliveryFee = 0.0;

        if ($isWeb) {
            $zones = \App\Models\Setting::kioskDeliveryZones();
            $deliveryZone = $zones[(int) $validated['delivery_zone'] - 1] ?? null;

            if (!$deliveryZone || trim((string) ($deliveryZone['name'] ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'La zona de delivery seleccionada no es válida.',
                ], 422);
            }

            $deliveryFee = round((float) ($deliveryZone['price'] ?? 0), 2);
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

        [$order, $total] = DB::transaction(function () use (
            $companyId,
            $kioskoTable,
            $items,
            $isWeb,
            $validated,
            $deliveryZone,
            $deliveryFee
        ) {
            $order = RestaurantOrder::create([
                'company_id' => $companyId,
                'table_id' => $kioskoTable->id,
                'user_id' => null,
                'order_number' => RestaurantOrder::generateKioskoOrderNumber($companyId),
                'status' => 'PENDING_PAYMENT',
                'order_type' => 'kiosko',
                'delivery_type' => $isWeb ? 'web' : 'local',
                'delivery_zone' => $isWeb ? $deliveryZone['name'] : null,
                'delivery_fee' => $isWeb ? $deliveryFee : 0,
                'customer_name' => $isWeb ? ($validated['customer_name'] ?? null) : null,
                'delivery_address' => $isWeb ? ($validated['delivery_address'] ?? null) : null,
                'delivery_reference' => $isWeb ? (($validated['delivery_reference'] ?? null) ?: null) : null,
                'contact_number' => $isWeb ? ($validated['contact_number'] ?? null) : null,
            ]);

            $total = 0;
            foreach ($items as $item) {
                $product = Product::find($item['product_id'] ?? null);
                if (!$product) continue;

                $unitPrice = $product->precio;
                $qty = max(1, (int) ($item['quantity'] ?? 1));

                $mainItem = RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->descripcion,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $qty,
                    'kitchen_status' => RestaurantOrderItem::PENDING,
                    'notes' => $item['notes'] ?? null,
                    'auxiliary_items' => $item['auxiliary_items'] ?? null,
                    'kds_destination' => $product->kds_destination ?? 'cocina',
                ]);
                $total += $unitPrice * $qty;

                if ($product->disposable_enabled && $product->disposable_product_id) {
                    $disposable = Product::where('company_id', $companyId)
                        ->where('id', $product->disposable_product_id)
                        ->where('estado', 'ACTIVO')
                        ->first();

                    if ($disposable && $disposable->id !== $product->id) {
                        $disposablePrice = (float) $disposable->precio;
                        $disposableTotal = round($disposablePrice * $qty, 2);

                        RestaurantOrderItem::create([
                            'restaurant_order_id' => $order->id,
                            'product_id' => $disposable->id,
                            'product_name' => $disposable->descripcion,
                            'parent_item_id' => $mainItem->id,
                            'is_disposable' => true,
                            'quantity' => $qty,
                            'unit_price' => $disposablePrice,
                            'total' => $disposableTotal,
                            'kitchen_status' => RestaurantOrderItem::PENDING,
                            'kds_destination' => $mainItem->kds_destination,
                        ]);

                        $total += $disposableTotal;
                    }
                }
            }

            if ($deliveryFee > 0) {
                // El delivery no pasa por cocina/KDS, pero sí cuenta en totales, caja y comprobante.
                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'product_id' => null,
                    'product_name' => 'Delivery - ' . $deliveryZone['name'],
                    'quantity' => 1,
                    'unit_price' => $deliveryFee,
                    'total' => $deliveryFee,
                    'kitchen_status' => RestaurantOrderItem::DELIVERED,
                    'notes' => 'Cargo de envío',
                    'kds_destination' => 'delivery',
                ]);
                $total += $deliveryFee;
            }

            $total = round($total, 2);
            $order->update(['total' => $total]);

            return [$order, $total];
        });

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
            'delivery_fee' => $deliveryFee,
        ]);
    }

    public function success($orderId)
    {
        $company = Company::getMainCompany();
        $web = $this->branding($company);
        $order = RestaurantOrder::with(['items'])->findOrFail($orderId);

        $auxiliaryIds = $order->items->flatMap(function ($item) {
            return is_array($item->auxiliary_items) ? $item->auxiliary_items : [];
        })->filter()->unique()->values();

        $auxiliaryNames = \App\Models\AuxiliaryItem::namesByIds($auxiliaryIds->all());

        $successTexts = [
            'ticket' => \App\Models\Setting::kiosk('success_ticket_text', 'Su ticket se está imprimiendo'),
            'pay' => \App\Models\Setting::kiosk('success_pay_text', 'Pase a caja para pagar'),
            'prepare' => \App\Models\Setting::kiosk('success_prepare_text', 'Su pedido comenzará a prepararse después del pago'),
        ];

        return view('autopedido.success', compact('order', 'successTexts', 'company', 'web', 'auxiliaryNames'));
    }
}
