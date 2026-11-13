<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);
        $purchases = Purchase::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('purchases.index', compact('purchases', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);
        $suppliers = Supplier::where('company_id', $companyId)->where('estado', 'ACT')->get();
        $products = Product::where('company_id', $companyId)->where('estado', 'ACTIVO')->get();
        return view('purchases.create', compact('companyId', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'numero_documento' => 'required|string|max:20',
            'tipo_documento' => 'required|in:BOLETA,FACTURA',
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'required|numeric|min:0',
            'items.*.precio_venta' => 'nullable|numeric|min:0',
        ]);

        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);
        $purchase = DB::transaction(function () use ($request, $companyId) {
            $purchase = Purchase::create([
                'company_id' => $companyId,
                'supplier_id' => $request->supplier_id,
                'numero_documento' => $request->numero_documento,
                'tipo_documento' => $request->tipo_documento,
                'fecha' => $request->fecha,
                'total' => 0,
            ]);

            $inventory = app(InventoryService::class);
            $total = 0;

            foreach ($request->items as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::findOrFail($item['product_id']);
                $product->precio_compra = $item['precio'];
                if (array_key_exists('precio_venta', $item) && $item['precio_venta'] !== null && $item['precio_venta'] !== '') {
                    $product->precio = $item['precio_venta'];
                }
                $product->save();

                $inventory->purchase(
                    $product,
                    (float) $item['cantidad'],
                    $purchase,
                    'Compra ' . $purchase->numero_documento
                );
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.index', ['company_id' => $companyId])
            ->with('success', 'Compra registrada correctamente');
    }

    public function show(Purchase $purchase)
    {
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->estado === 'ANULADO') {
            return back()->with('success', 'La compra ya estaba anulada');
        }

        DB::transaction(function () use ($purchase) {
            $inventory = app(InventoryService::class);

            foreach ($purchase->items as $item) {
                if ($item->product) {
                    $inventory->purchaseCancel(
                        $item->product,
                        (float) $item->cantidad,
                        $purchase,
                        'Anulación compra ' . $purchase->numero_documento
                    );
                }
            }

            $purchase->update(['estado' => 'ANULADO']);
        });

        return back()->with('success', 'Compra anulada');
    }

    public function printA4(Purchase $purchase)
    {
        $purchase->load('items.product', 'supplier');
        $pdf = Pdf::loadView('purchases.print-a4', compact('purchase'))
            ->setPaper('A4', 'portrait');
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="compra-' . $purchase->id . '.pdf"');
    }

    public function printTicket(Purchase $purchase)
    {
        $purchase->load('items.product', 'supplier');
        $pdf = Pdf::loadView('purchases.print-ticket', compact('purchase'))
            ->setPaper([0, 0, 226.77, 800], 'portrait')
            ->setOption('margin-top', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 2)
            ->setOption('margin-left', 2);
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="ticket-compra-' . $purchase->id . '.pdf"');
    }
}