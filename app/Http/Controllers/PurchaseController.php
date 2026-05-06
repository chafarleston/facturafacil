<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        $purchases = Purchase::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('purchases.index', compact('purchases', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
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
        ]);

        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        $purchase = Purchase::create([
            'company_id' => $companyId,
            'supplier_id' => $request->supplier_id,
            'numero_documento' => $request->numero_documento,
            'tipo_documento' => $request->tipo_documento,
            'fecha' => $request->fecha,
            'total' => 0,
        ]);

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

            $product = Product::find($item['product_id']);
            $product->stock += $item['cantidad'];
            $product->save();
        }

        $purchase->total = $total;
        $purchase->save();

        return redirect()->route('purchases.index', ['company_id' => $companyId])
            ->with('success', 'Compra registrada correctamente');
    }

    public function show(Purchase $purchase)
    {
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            $product = $item->product;
            $product->stock = max(0, $product->stock - $item->cantidad);
            $product->save();
        }
        
        $purchase->estado = 'ANULADO';
        $purchase->save();

        return back()->with('success', 'Compra anulada');
    }
}