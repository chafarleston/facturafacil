<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Serie;
use App\Services\GreenterService;
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->first();
            
        if (!$cajaAbierta) {
            return redirect()->route('cashregisters.index')
                ->with('error', 'No se puede acceder al punto de venta sin tener una caja abierta');
        }
        
        $categories = Category::whereIn('estado', ['ACTIVO', 'ACT'])
            ->withCount(['products' => fn($q) => $q->where('estado', 'ACTIVO')])
            ->get();
        $products = Product::where('estado', 'ACTIVO')
            ->with('category')
            ->get();
        $customers = Customer::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->get();
        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereIn('tipo_documento', ['01', '03', 'NV'])
            ->get();
        
        return view('pos.index', compact('categories', 'products', 'customers', 'series', 'cajaAbierta', 'mainCompany'));
    }
    
    public function store(Request $request)
    {
        $mainCompany = \App\Models\Company::getMainCompany();
        $companyId = $mainCompany->id;
        
        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
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
        if (empty($customerId)) {
            $customerId = null;
        }
        
        $customer = null;
        if ($customerId) {
            $customer = Customer::find($customerId);
        }
        
        $documentType = $request->document_type ?? 'NV';
        
        $serie = Serie::where('company_id', $companyId)
            ->where('tipo_documento', $documentType)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$serie) {
            $prefix = $documentType === 'NV' ? 'NV' : ($documentType === '01' ? 'F' : 'B');
            $serie = new Serie();
            $serie->company_id = $companyId;
            $serie->tipo_documento = $documentType;
            $serie->serie = $prefix . '001';
            $serie->numero_actual = 0;
            $serie->estado = 'ACTIVO';
            $serie->save();
        }

        $nextNumber = $serie->getNextNumber();
        
        $subtotal = 0;
        $igv = 0;
        $igvRate = $mainCompany->getIgvRate();
        
        foreach ($items as $item) {
            $priceWithIgv = $item['price'] * $item['quantity'];
            $base = $priceWithIgv / (1 + $igvRate);
            $igvItem = $priceWithIgv - $base;
            
            $subtotal += $base;
            $igv += $igvItem;
        }
        
        $total = $subtotal + $igv;
        
        $invoice = \App\Models\Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customerId,
            'tipo_documento' => $documentType,
            'serie' => $serie->serie,
            'numero' => $nextNumber,
            'full_number' => $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => now()->format('Y-m-d'),
            'hora_emision' => now()->format('H:i:s'),
            'fecha_vencimiento' => now()->format('Y-m-d'),
            'moneda' => 'PEN',
            'gravado' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'subtotal' => $subtotal,
            'total_letras' => strtoupper($this->numberToLetter($total)) . ' SOLES',
            'metodo_pago' => $request->payment_method ?? 'EFECTIVO',
            'referencia_pago' => $request->reference ?? null,
            'sunat_estado' => 'PENDIENTE',
            'estado' => 'ACTIVO',
        ]);
        
        $itemIds = collect($items)->pluck('id')->toArray();
        $products = \App\Models\Product::whereIn('id', $itemIds)->get()->keyBy('id');
        
        foreach ($items as $item) {
            $producto = $products->get($item['id']);
            
            $priceWithIgv = $item['price'] * $item['quantity'];
            $baseItem = $priceWithIgv / (1 + $igvRate);
            $igvItem = $priceWithIgv - $baseItem;
            
            $invoice->items()->create([
                'product_id' => $item['id'],
                'codigo' => $producto ? $producto->codigo : 'N/A',
                'descripcion' => $item['name'],
                'cantidad' => $item['quantity'],
                'precio_unitario' => $baseItem / $item['quantity'],
                'precio_venta' => $priceWithIgv,
                'igv' => $igvItem,
            ]);
            
            if ($producto) {
                $producto->decrement('stock', $item['quantity']);
            }
        }
        
        $serie->numero_actual = $nextNumber;
        $serie->save();

        try {
            $printService = app(PrintService::class);
            $invoice->load('items', 'customer');
            $printService->printInvoice($invoice);
        } catch (\Exception $e) {
            \Log::error('POS print error: ' . $e->getMessage());
        }

        return redirect()->route('pos.success', $invoice->id);
    }
    
    private function numberToLetter($number)
    {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }
    
    public function success($id)
    {
        $invoice = \App\Models\Invoice::with(['company', 'customer', 'items.product'])->findOrFail($id);
        
        return view('pos.success', compact('invoice'))->with('invoiceId', $invoice->id);
    }
    
    public function sendToSunat($id)
    {
        $invoice = \App\Models\Invoice::with(['company', 'customer', 'items'])->findOrFail($id);
        
        if ($invoice->tipo_documento === 'NV') {
            return response()->json([
                'success' => false,
                'message' => 'Las Notas de Venta no se envían a SUNAT',
                'sunat_estado' => $invoice->sunat_estado
            ]);
        }
        
        if ($invoice->sunat_estado === 'ACEPTADO') {
            return response()->json([
                'success' => true,
                'message' => 'Documento ya fue enviado a SUNAT',
                'sunat_estado' => $invoice->sunat_estado
            ]);
        }

        $greenterService = app(GreenterService::class);
        
        try {
            // Boletas se envían mediante Resumen Diario
            if ($invoice->tipo_documento === '03') {
                $summaryService = app(SummaryService::class);
                $result = $summaryService->sendBoletaToSummary($invoice);
            } else {
                $result = $greenterService->sendInvoice($invoice);
            }
            
            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['description'] ?? 'Respuesta de SUNAT',
                'sunat_estado' => $invoice->fresh()->sunat_estado,
                'sunat_code' => $result['code'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage(),
                'sunat_estado' => $invoice->sunat_estado
            ]);
        }
    }
    
    public function printInvoice($id, $format = 'A4')
    {
        $invoice = \App\Models\Invoice::with(['company', 'customer', 'items.product'])->findOrFail($id);

        if ($format === '80mm') {
            $greenterService = app(GreenterService::class);
            $pdfContent = $greenterService->generateTicketPdf($invoice);
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="ticket-' . $invoice->full_number . '.pdf"');
        }

        $greenterService = app(GreenterService::class);
        $pdfContent = $greenterService->generatePdf($invoice);
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="factura-' . $invoice->full_number . '.pdf"');
    }

    public function openDrawer(Request $request)
    {
        $printer = \App\Models\Printer::where('assigned_to', 'caja')->where('active', true)->first();
        if (!$printer) {
            return response()->json(['success' => false, 'message' => 'No hay impresora de caja configurada']);
        }

        $drawerCommand = base64_encode("\x1B\x70\x00\x00\xFF");

        return response()->json([
            'success' => true,
            'data' => $drawerCommand,
            'printer' => $printer->printer_name,
            'ip' => $printer->ip_address,
            'port' => $printer->port,
            'type' => $printer->type,
        ]);
    }
}