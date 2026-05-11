<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $mainCompany = \App\Models\Company::getMainCompany();
        
        if (!$mainCompany) {
            abort(400, 'No hay empresa principal configurada');
        }
        
        $companyId = $mainCompany->id;
        
        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->where('user_id', auth()->id())
            ->first();
            
        if (!$cajaAbierta) {
            return redirect()->route('cashregisters.index')
                ->with('error', 'No se puede acceder al punto de venta sin tener una caja abierta');
        }
        
        $categories = Category::where('estado', 'ACTIVO')->get();
        $products = Product::where('estado', 'ACTIVO')
            ->with('category')
            ->get();
        $customers = Customer::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->get();
        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereIn('tipo', ['01', '03', 'NV'])
            ->get();
        
        return view('pos.index', compact('categories', 'products', 'customers', 'series', 'cajaAbierta'));
    }
    
    public function store(Request $request)
    {
        $mainCompany = \App\Models\Company::getMainCompany();
        $companyId = $mainCompany->id;
        
        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->where('user_id', auth()->id())
            ->first();
            
        if (!$cajaAbierta) {
            return redirect()->route('cashregisters.index')
                ->with('error', 'No se puede realizar ventas sin tener una caja abierta');
        }
        
        $items = json_decode($request->items_json, true);
        
        if (empty($items)) {
            return redirect()->back()->with('error', 'No hay productos en la venta');
        }
        
        $customerId = $request->customer_id;
        
        $customer = null;
        if ($customerId) {
            $customer = Customer::find($customerId);
        }
        
        $lastInvoice = \App\Models\Invoice::where('company_id', $companyId)
            ->where('tipo_documento', 'NV')
            ->orderBy('numero', 'desc')
            ->first();
        $nextNumber = $lastInvoice ? ((int)$lastInvoice->numero + 1) : 1;
        
        $serie = Serie::where('company_id', $companyId)
            ->where('tipo', 'NV')
            ->where('estado', 'ACTIVO')
            ->first();
        
        if (!$serie) {
            $serie = new Serie();
            $serie->company_id = $companyId;
            $serie->tipo = 'NV';
            $serie->serie = 'NV001';
            $serie->numero_inicial = 1;
            $serie->numero_actual = $nextNumber;
            $serie->estado = 'ACTIVO';
            $serie->save();
        }
        
        $subtotal = 0;
        $igv = 0;
        
        foreach ($items as $item) {
            $subtotalItem = $item['price'] * $item['quantity'];
            $igvItem = $subtotalItem * 0.18;
            $subtotal += $subtotalItem;
            $igv += $igvItem;
        }
        
        $total = $subtotal + $igv;
        
        $invoice = \App\Models\Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customerId,
            'tipo_documento' => 'NV',
            'serie' => $serie->serie,
            'numero' => $serie->numero_actual,
            'full_number' => $serie->serie . '-' . str_pad($serie->numero_actual, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => now()->format('Y-m-d'),
            'hora_emision' => now()->format('H:i:s'),
            'fecha_vencimiento' => now()->format('Y-m-d'),
            'moneda' => 'PEN',
            'gravado' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'subtotal' => $subtotal,
            'total_letras' => strtoupper($this->numberToLetter($total)) . ' SOLES',
            'metodo_pago' => 'EFECTIVO',
            'sunat_estado' => 'PENDIENTE',
            'estado' => 'ACTIVO',
        ]);
        
        foreach ($items as $item) {
            $producto = Product::find($item['id']);
            
            $precioVenta = $item['price'] * 1.18;
            
            $invoice->items()->create([
                'product_id' => $item['id'],
                'codigo' => $producto ? $producto->codigo : 'N/A',
                'descripcion' => $item['name'],
                'cantidad' => $item['quantity'],
                'precio_unitario' => $item['price'],
                'precio_venta' => $precioVenta * $item['quantity'],
                'igv' => ($item['price'] * $item['quantity']) * 0.18,
            ]);
            
            if ($producto) {
                $producto->decrement('stock', $item['quantity']);
            }
        }
        
        $serie->increment('numero_actual');
        
        return redirect()->back()
            ->with('success', 'Venta realizada: ' . $invoice->full_number . ' - Total: S/ ' . number_format($total, 2))
            ->with(compact('invoice'));
    }
    
    private function numberToLetter($number)
    {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }
}