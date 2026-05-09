<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Serie;
use App\Services\GreenterService;
use App\Services\SunatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NumberFormatter;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? \App\Models\Company::getMainCompany()->id;
        $tipoDocumento = $request->type;
        
        $query = Invoice::with(['customer', 'company'])
            ->where('company_id', $companyId);
        
        if ($tipoDocumento) {
            $query->where('tipo_documento', $tipoDocumento);
        }
        
        $invoices = $query->orderBy('fecha_emision', 'desc')
            ->paginate(15);

        return view('invoices.index', compact('invoices', 'companyId', 'tipoDocumento'));
    }

    public function create(Request $request)
    {
        $mainCompany = \App\Models\Company::getMainCompany();
        if (!$mainCompany) {
            abort(400, 'No hay empresa principal configurada');
        }
        
        $companyId = $mainCompany->id;
        $company = $mainCompany;
        $customers = Customer::where('company_id', $companyId)->where('estado', 'ACTIVO')->get();
        $products = Product::where('estado', 'ACTIVO')->get();
        $series = Serie::where('company_id', $companyId)->where('estado', 'ACTIVO')->get();

        return view('invoices.create', compact('company', 'customers', 'products', 'series'));
    }

    public function store(Request $request)
    {
        $itemsInput = $request->input('items');
        
        $itemsArray = [];
        
        if (is_array($itemsInput)) {
            foreach ($itemsInput as $idx => $item) {
                if (is_string($item)) {
                    $itemsArray[] = json_decode($item, true);
                } else {
                    $itemsArray[] = $item;
                }
            }
        }

        $tipoDoc = $request->tipo_documento;
        
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tipo_documento' => 'required|in:01,03,NV',
            'serie_id' => 'required|exists:series,id',
            'fecha_emision' => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
            'referencia_pago' => 'nullable|string|max:100',
        ], [
            'company_id.required' => 'Falta company_id',
            'tipo_documento.required' => 'Falta tipo de documento',
            'serie_id.required' => 'Falta serie',
            'fecha_emision.required' => 'Falta fecha',
        ]);

        $customerId = $request->customer_id;
        
        if (!$customerId || empty($customerId)) {
            $customerData = $request->input('customer_data', []);
            if (empty($customerData['documento_numero']) || empty($customerData['nombre'])) {
                return back()->withErrors(['customer' => 'Ingrese datos del cliente']);
            }
            
            $docNumero = $customerData['documento_numero'];
            $docTipo = $customerData['documento_tipo'] ?? '1';
            
            if ($tipoDoc === '01') {
                if (strlen($docNumero) !== 11) {
                    return back()->withErrors(['customer' => 'Las facturas requieren RUC de 11 dígitos']);
                }
                if ($docTipo !== '6') {
                    return back()->withErrors(['customer' => 'Las facturas requieren tipo de documento RUC (6)']);
                }
            }
            
            $customer = Customer::create([
                'company_id' => $validated['company_id'],
                'documento_tipo' => $docTipo,
                'documento_numero' => $docNumero,
                'nombre' => $customerData['nombre'],
                'direccion' => $customerData['direccion'] ?? '',
                'ubigeo' => $customerData['ubigeo'] ?? null,
                'estado' => 'ACTIVO',
            ]);
            
            $customerId = $customer->id;
        } else {
            $customer = Customer::find($customerId);
            if ($tipoDoc === '01' && $customer) {
                $docNumero = $customer->documento_numero;
                $docTipo = $customer->documento_tipo;
                if (strlen($docNumero) !== 11) {
                    return back()->withErrors(['customer' => 'Las facturas requieren RUC de 11 dígitos']);
                }
                if ($docTipo !== '6') {
                    return back()->withErrors(['customer' => 'Las facturas requieren tipo de documento RUC (6)']);
                }
            }
        }

        if (empty($itemsArray)) {
            return back()->withErrors(['items' => 'Agregue productos']);
        }

        $company = Company::findOrFail($validated['company_id']);
        $serie = Serie::findOrFail($validated['serie_id']);
        $numero = $serie->getNextNumber();

        $subtotal = 0;
        $igvTotal = 0;
        $itemsData = [];

        foreach ($itemsArray as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            // Reducir stock (puede ser negativo si se vendió más de lo disponible)
            $product->stock = $product->stock - $item['cantidad'];
            $product->save();
            
            // El precio que ingresa el usuario puede venir como Con IGV o Sin IGV. Preferimos Con IGV si proviene.
            $precioConIgv = $item['precio_con_igv'] ?? $item['precio'] ?? 0;
            $precioVenta = round($item['cantidad'] * $precioConIgv, 2);
            $base = round($precioVenta / 1.18, 2); // Separar IGV del subtotal
            $igv = round($precioVenta - $base, 2);
            
            $subtotal += $base;
            $igvTotal += $igv;

            $itemsData[] = [
                'product_id' => $product->id,
                'codigo' => $product->codigo,
                'descripcion' => $product->descripcion,
                'cantidad' => $item['cantidad'],
                'umedida' => $product->umedida_codigo,
                'precio_unitario' => $precioConIgv, // Precio con IGV
                'precio_venta' => $precioVenta,
                'igv' => $igv,
                'tipo_afectacion' => $product->tipo_afectacion,
                'igv_percent' => $product->igv_percent,
            ];
        }
        
        // Redondear totales finales
        $subtotal = round($subtotal, 2);
        $igvTotal = round($igvTotal, 2);
        $total = round($subtotal + $igvTotal, 2);
        
        $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
        $totalLetras = ucfirst($formatter->formatCurrency($total, 'PEN'));

        $excludeFromTotals = (isset($validated['tipo_documento']) && $validated['tipo_documento'] === 'NV');
        $invoice = Invoice::create([
            'company_id' => $validated['company_id'],
            'customer_id' => $customerId,
            'tipo_documento' => $validated['tipo_documento'],
            'serie' => $serie->serie,
            'numero' => $numero,
            'fecha_emision' => $validated['fecha_emision'],
            'moneda' => $validated['moneda'] ?? 'PEN',
            'gravado' => $subtotal,
            'subtotal' => $subtotal,
            'igv' => $igvTotal,
            'total' => $total,
            'total_letras' => $totalLetras,
            'sunat_estado' => 'PENDIENTE',
            'exclude_from_totals' => $excludeFromTotals,
            'metodo_pago' => $request->metodo_pago ?? 'EFECTIVO',
            'referencia_pago' => $request->referencia_pago,
        ]);

        foreach ($itemsData as $item) {
            $invoice->items()->create($item);
        }

        $serie->incrementNumber();

        // Compute and save hash for data integrity
        $hashSource = json_encode([
            'serie' => $serie->serie,
            'numero' => $numero,
            'fecha_emision' => $validated['fecha_emision'],
            'subtotal' => $subtotal,
            'igv' => $igvTotal,
            'total' => $total,
            'customer' => $customerId,
        ]);
        $hash = hash('sha256', $hashSource);
        $invoice->update(['codigo_hash' => $hash]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Documento creado: ' . $invoice->full_number);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    // Ensure PDF generation uses proper PDF headers
    public function generatePdf(Invoice $invoice)
    {
        $greenterService = new \App\Services\GreenterService();
        $pdfContent = $greenterService->generatePdf($invoice);
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="factura-' . $invoice->full_number . '.pdf"');
    }

    public function generateTicketPdf(Invoice $invoice)
    {
        $greenterService = new \App\Services\GreenterService();
        $pdfContent = $greenterService->generateTicketPdf($invoice);
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="ticket-' . $invoice->full_number . '.pdf"');
    }

    public function downloadXml(Invoice $invoice)
    {
        if ($invoice->xml_firmado) {
            $filename = $invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT) . '.xml';
            return response()->make($invoice->xml_firmado, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        }
        
        return back()->with('error', 'XML no disponible');
    }

    public function downloadPdf(Invoice $invoice)
    {
        return response()->download(storage_path('app/' . $invoice->pdf_path));
    }

    public function downloadCdr(Invoice $invoice)
    {
        // Check database cdr_path
        $cdrPath = $invoice->cdr_path;
        
        // If not in DB, check common locations
        if (!$cdrPath) {
            $filename = $invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT);
            
            // Try multiple file patterns
            $possibleFiles = [
                'sunat/' . $filename . '_cdr.zip',
                'sunat/' . $filename . '.zip',
            ];
            
            foreach ($possibleFiles as $path) {
                if (file_exists(storage_path('app/' . $path))) {
                    $cdrPath = $path;
                    break;
                }
            }
        }
        
        if ($cdrPath && file_exists(storage_path('app/' . $cdrPath))) {
            return response()->download(storage_path('app/' . $cdrPath));
        }
        
        return back()->with('error', 'CDR no disponible. El entorno beta de SUNAT no siempre retorna CDR.');
    }

    public function sendToSunat(Invoice $invoice)
    {
        // Nota de Venta no se envía a SUNAT
        if (isset($invoice->tipo_documento) && $invoice->tipo_documento === 'NV') {
            return back()->with('success', 'Nota de Venta no se envía a SUNAT');
        }

        try {
            \Log::info('Sending to SUNAT via Greenter', ['invoice' => $invoice->full_number, 'company' => $invoice->company->ruc]);
            
            $greenterService = new GreenterService();
            $response = $greenterService->sendInvoice($invoice);
            
            \Log::info('SUNAT response', $response);
            
            if ($response['success']) {
                return back()->with('success', 'Documento enviado a SUNAT. Código: ' . ($response['code'] ?? ''));
            } else {
                return back()->with('error', 'Error SUNAT: ' . ($response['description'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            \Log::error('Error sending to SUNAT: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar a SUNAT: ' . $e->getMessage());
        }
    }

    
    
    public function destroy(Invoice $invoice)
    {
        try {
            $greenterService = new GreenterService();
            $result = $greenterService->voidInvoice($invoice);
            
            if ($result['success']) {
                return back()->with('success', 'Documento dado de baja en SUNAT. Código: ' . ($result['code'] ?? ''));
            } else {
                return back()->with('error', 'Error al dar de baja: ' . ($result['description'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error aldar de baja: ' . $e->getMessage());
        }
    }
    
    public function creditNoteForm(Invoice $invoice)
    {
        return view('invoices.credit-note', compact('invoice'));
    }

    // Nota de Venta impresiones
    public function printNvA4(Invoice $invoice)
    {
        if ($invoice->tipo_documento !== 'NV') {
            abort(404);
        }
        return view('invoices.print_nv_a4', compact('invoice'));
    }

    public function printNvTicket(Invoice $invoice)
    {
        if ($invoice->tipo_documento !== 'NV') {
            abort(404);
        }
        return view('invoices.print_nv_ticket', compact('invoice'));
    }

    public function nvIndex(Request $request)
    {
        // Use the existing index but force NV filter
        $request->merge(['type' => 'NV']);
        return $this->index($request);
    }
    
    public function sendCreditNote(Request $request, Invoice $invoice)
    {
        $request->validate([
            'motivo' => 'required',
            'descripcion' => 'required'
        ]);
        
        try {
            $greenterService = new GreenterService();
            $result = $greenterService->sendCreditNote($invoice, $request->motivo, $request->descripcion);
            
            if ($result['success']) {
                return redirect()->route('invoices.index')
                    ->with('success', 'Nota de crédito generada. Ref: ' . ($result['note_number'] ?? ''));
            } else {
                return back()->with('error', 'Error al generar nota: ' . ($result['description'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
