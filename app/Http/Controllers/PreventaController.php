<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Preventa;
use App\Models\Product;
use App\Models\Serie;
use App\Services\GreenterService;
use App\Services\InventoryService;
use App\Services\SunatCustomerStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use NumberFormatter;

class PreventaController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));
        $search = trim((string) $request->get('search', ''));

        $preventas = Preventa::where('company_id', $companyId)
            ->where('tipo', $tipo)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('numero', 'like', "%{$search}%")
                        ->orWhere('cliente_nombre', 'like', "%{$search}%")
                        ->orWhere('titulo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $conteos = Preventa::where('company_id', $companyId)
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return view('preventas.index', [
            'preventas' => $preventas,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
            'tipo' => $tipo,
            'conteos' => $conteos,
            'search' => $search,
        ]);
    }

    public function create(Request $request)
    {
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));

        return view('preventas.create', array_merge([
            'tipo' => $tipo,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
        ], $this->formData()));
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));
        [$data, $items] = $this->prepareData($request, $tipo, $companyId);

        DB::transaction(function () use ($data, $items, $companyId, $tipo) {
            $preventa = Preventa::create(array_merge($data, [
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'tipo' => $tipo,
                'numero' => Preventa::nextNumero($companyId, $tipo),
            ]));

            if ($items !== []) {
                $preventa->items()->createMany($items);
            }
        });

        return redirect()
            ->route('preventas.index', ['tipo' => $tipo])
            ->with('success', 'Registro de ' . strtolower(Preventa::TIPOS[$tipo]) . ' creado correctamente.');
    }

    public function show($id)
    {
        $preventa = $this->find($id);

        return view('preventas.show', array_merge([
            'preventa' => $preventa,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
            'cajaAbierta' => $this->openCashRegister($this->companyId()),
        ], $this->formData()));
    }

    public function edit($id)
    {
        $preventa = $this->find($id);

        if ($preventa->tipo === Preventa::TIPO_COTIZACION && $preventa->children()->exists()) {
            return redirect()
                ->route('preventas.show', $preventa)
                ->with('error', 'La cotización ya fue convertida en pedido y no puede editarse.');
        }

        return view('preventas.edit', array_merge([
            'preventa' => $preventa,
            'tipo' => $preventa->tipo,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
        ], $this->formData()));
    }

    public function update(Request $request, $id)
    {
        $preventa = $this->find($id);

        if ($preventa->tipo === Preventa::TIPO_COTIZACION && $preventa->children()->exists()) {
            return redirect()
                ->route('preventas.show', $preventa)
                ->with('error', 'La cotización ya fue convertida en pedido y no puede editarse.');
        }

        $companyId = $this->companyId();
        [$data, $items] = $this->prepareData($request, $preventa->tipo, $companyId);

        DB::transaction(function () use ($preventa, $data, $items) {
            $preventa->update($data);
            $preventa->items()->delete();

            if ($items !== []) {
                $preventa->items()->createMany($items);
            }
        });

        return redirect()
            ->route('preventas.index', ['tipo' => $preventa->tipo])
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $preventa = $this->find($id);
        $tipo = $preventa->tipo;
        $preventa->delete();

        return redirect()
            ->route('preventas.index', ['tipo' => $tipo])
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function convertToOrder($id)
    {
        $quote = $this->find($id);

        if ($quote->tipo !== Preventa::TIPO_COTIZACION) {
            return back()->with('error', 'Solo una cotización puede convertirse en pedido.');
        }

        $existingOrder = $quote->children()
            ->where('tipo', Preventa::TIPO_PEDIDO)
            ->first();

        if ($existingOrder) {
            return redirect()
                ->route('preventas.show', $existingOrder)
                ->with('success', 'Esta cotización ya tiene un pedido generado.');
        }

        if ($quote->items->isEmpty()) {
            return back()->with('error', 'Agregue productos antes de confirmar la cotización.');
        }

        $companyId = $this->companyId();

        if (!$this->openCashRegister($companyId)) {
            return back()->with('error', 'Debe aperturar una caja antes de validar la cotización.');
        }
        $order = DB::transaction(function () use ($quote, $companyId) {
            $order = Preventa::create([
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'customer_id' => $quote->customer_id,
                'parent_id' => $quote->id,
                'tipo' => Preventa::TIPO_PEDIDO,
                'document_type' => $quote->document_type,
                'payment_condition' => $quote->payment_condition,
                'credit_days' => $quote->credit_days,
                'numero' => Preventa::nextNumero($companyId, Preventa::TIPO_PEDIDO),
                'cliente_nombre' => $quote->cliente_nombre,
                'cliente_documento' => $quote->cliente_documento,
                'cliente_email' => $quote->cliente_email,
                'cliente_telefono' => $quote->cliente_telefono,
                'titulo' => $quote->titulo,
                'descripcion' => $quote->descripcion,
                'monto' => $quote->monto,
                'subtotal' => $quote->subtotal,
                'igv' => $quote->igv,
                'estado' => 'PENDIENTE',
                'fecha' => now()->format('Y-m-d'),
                'fecha_vencimiento' => $quote->fecha_vencimiento,
                'observaciones' => $quote->observaciones,
            ]);

            foreach ($quote->items as $item) {
                $order->items()->create($item->only([
                    'product_id',
                    'descripcion',
                    'cantidad',
                    'precio_unitario',
                    'igv_percent',
                    'subtotal',
                    'igv',
                    'total',
                ]));
            }

            $quote->update(['estado' => 'CONVERTIDO']);

            return $order;
        });

        return redirect()
            ->route('preventas.show', $order)
            ->with('success', 'Pedido ' . $order->numero . ' creado desde la cotización ' . $quote->numero . '.');
    }

    public function printPdf($id)
    {
        $preventa = $this->find($id);
        $company = Company::findOrFail($preventa->company_id);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $logoBase64 = null;

        if ($company->logo && file_exists(storage_path('app/public/' . $company->logo))) {
            $logoBase64 = 'data:image/' . pathinfo($company->logo, PATHINFO_EXTENSION) . ';base64,'
                . base64_encode(file_get_contents(storage_path('app/public/' . $company->logo)));
        }

        $html = view('preventas.pdf', compact('preventa', 'company', 'logoBase64'))->render();
        $pdf->WriteHTML($html);

        $filename = strtolower($preventa->tipo) . '_' . $preventa->numero . '.pdf';

        return response($pdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateInvoice(Request $request, $id)
    {
        $preventa = $this->find($id);

        if ($preventa->tipo !== Preventa::TIPO_PEDIDO) {
            return back()->with('error', 'Confirme la cotización y genere un pedido antes de emitir el comprobante.');
        }

        if ($preventa->invoice_id) {
            return redirect()
                ->route('invoices.show', $preventa->invoice_id)
                ->with('success', 'Esta preventa ya tiene un comprobante generado.');
        }

        if (!$this->openCashRegister($this->companyId())) {
            return back()->with('error', 'Debe aperturar una caja antes de generar el comprobante.');
        }

        if ($preventa->items->isEmpty()) {
            return back()->with('error', 'La preventa no tiene productos para generar un comprobante.');
        }

        $validated = $request->validate([
            'document_type' => 'required|in:01,03,NV',
        ]);

        $company = Company::findOrFail($this->companyId());
        $documentType = $validated['document_type'];
        $customer = $this->resolveInvoiceCustomer($preventa, $company);
        $statusService = app(SunatCustomerStatusService::class);
        $billingError = $statusService->billingValidationError($documentType, $customer);

        if ($billingError) {
            return back()->with('error', $billingError);
        }

        $totals = [
            'subtotal' => round((float) $preventa->items->sum('subtotal'), 2),
            'igv' => round((float) $preventa->items->sum('igv'), 2),
            'total' => round((float) $preventa->items->sum('total'), 2),
        ];

        try {
            $invoice = DB::transaction(function () use ($preventa, $company, $customer, $documentType, $totals) {
                $serie = Serie::where('company_id', $company->id)
                    ->where('tipo_documento', $documentType)
                    ->where('estado', 'ACTIVO')
                    ->lockForUpdate()
                    ->first();

                if (!$serie) {
                    $prefix = $documentType === 'NV' ? 'NV' : ($documentType === '01' ? 'F' : 'B');
                    $serie = Serie::create([
                        'company_id' => $company->id,
                        'tipo_documento' => $documentType,
                        'serie' => $prefix . '001',
                        'numero_actual' => 0,
                        'estado' => 'ACTIVO',
                    ]);
                }

                $numero = $serie->getNextNumber();
                $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
                $totalLetras = ucfirst($formatter->formatCurrency($totals['total'], 'PEN'));

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer?->id,
                    'tipo_documento' => $documentType,
                    'serie' => $serie->serie,
                    'numero' => $numero,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'hora_emision' => now()->format('H:i:s'),
                    'fecha_vencimiento' => $preventa->fecha_vencimiento?->format('Y-m-d') ?: now()->format('Y-m-d'),
                    'moneda' => 'PEN',
                    'gravado' => $totals['subtotal'],
                    'subtotal' => $totals['subtotal'],
                    'igv' => $totals['igv'],
                    'total' => $totals['total'],
                    'total_letras' => $totalLetras,
                    'sunat_estado' => 'PENDIENTE',
                    'exclude_from_totals' => $documentType === 'NV',
                    'metodo_pago' => $preventa->payment_condition === 'CREDITO' ? 'CREDITO' : 'EFECTIVO',
                    'referencia_pago' => $preventa->numero,
                    'observaciones' => 'Generado desde preventa ' . $preventa->numero,
                ]);

                $this->copyItemsToInvoice($invoice, $preventa);

                $hashSource = json_encode([
                    'serie' => $serie->serie,
                    'numero' => $numero,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'subtotal' => $totals['subtotal'],
                    'igv' => $totals['igv'],
                    'total' => $totals['total'],
                    'customer' => $customer?->id,
                ]);
                $invoice->update(['codigo_hash' => hash('sha256', $hashSource)]);

                $serie->incrementNumber();
                $preventa->update(['invoice_id' => $invoice->id]);

                return $invoice;
            });
        } catch (\Exception $exception) {
            return back()->with('error', 'No se pudo generar el comprobante: ' . $exception->getMessage());
        }

        $sunatResult = null;

        if ($documentType === '01') {
            try {
                $sunatResult = (new GreenterService())->sendInvoice($invoice->fresh());
            } catch (\Exception $exception) {
                \Log::error('Preventa envío inmediato factura error: ' . $exception->getMessage());
                $sunatResult = ['success' => false, 'description' => $exception->getMessage()];
            }
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Comprobante generado: ' . $invoice->full_number)
            ->with('sunat_sent', $sunatResult);
    }

    private function resolveInvoiceCustomer(Preventa $preventa, Company $company): ?Customer
    {
        if ($preventa->customer_id) {
            $customer = Customer::where('company_id', $company->id)->find($preventa->customer_id);

            if ($customer) {
                return $customer;
            }
        }

        $documento = preg_replace('/\D+/', '', (string) $preventa->cliente_documento);

        if ($documento !== '') {
            $customer = Customer::where('company_id', $company->id)
                ->where('documento_numero', $documento)
                ->first();

            if ($customer) {
                return $customer;
            }

            if ($preventa->cliente_nombre) {
                $customer = Customer::create([
                    'company_id' => $company->id,
                    'documento_tipo' => strlen($documento) === 11 ? '6' : '1',
                    'documento_numero' => $documento,
                    'nombre' => $preventa->cliente_nombre,
                    'direccion' => '',
                    'email' => $preventa->cliente_email,
                    'telefono' => $preventa->cliente_telefono,
                    'estado' => 'ACTIVO',
                ]);

                app(SunatCustomerStatusService::class)->syncCustomer($customer);

                return $customer;
            }
        }

        return Customer::where('company_id', $company->id)
            ->where('documento_numero', '88888888')
            ->first();
    }

    private function copyItemsToInvoice(Invoice $invoice, Preventa $preventa): void
    {
        $products = Product::with('components.component')
            ->whereIn('id', $preventa->items->pluck('product_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        foreach ($preventa->items as $item) {
            $product = $products->get($item->product_id);

            $invoice->items()->create([
                'product_id' => $item->product_id,
                'codigo' => $product?->codigo ?? 'N/A',
                'descripcion' => $item->descripcion,
                'cantidad' => $item->cantidad,
                'umedida' => $product?->umedida_codigo ?? 'NIU',
                'precio_unitario' => $item->precio_unitario,
                'precio_venta' => $item->total,
                'igv' => $item->igv,
                'tipo_afectacion' => $product?->tipo_afectacion ?? 'GRA',
                'igv_percent' => $item->igv_percent,
            ]);

            if (!$product) {
                continue;
            }

            $inventory = app(InventoryService::class);

            if ($product->is_composite) {
                foreach ($product->components as $component) {
                    if ($component->component) {
                        $inventory->sale(
                            $component->component,
                            (float) $component->quantity * (float) $item->cantidad,
                            $invoice,
                            'Venta ' . $invoice->full_number . ' - ' . $product->descripcion
                        );
                    }
                }
            } else {
                $inventory->sale(
                    $product,
                    (float) $item->cantidad,
                    $invoice,
                    'Venta ' . $invoice->full_number
                );
            }
        }
    }

    private function companyId(): int
    {
        return (int) Company::getMainCompany()->id;
    }

    private function openCashRegister(int $companyId): ?CashRegister
    {
        return CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->latest('id')
            ->first();
    }

    private function tipo(string $tipo): string
    {
        return array_key_exists($tipo, Preventa::TIPOS) ? $tipo : Preventa::TIPO_COTIZACION;
    }

    private function find($id): Preventa
    {
        return Preventa::with(['items', 'children'])
            ->where('company_id', $this->companyId())
            ->findOrFail($id);
    }

    private function formData(): array
    {
        $companyId = $this->companyId();

        return [
            'products' => Product::where('company_id', $companyId)
                ->where('estado', 'ACTIVO')
                ->orderBy('descripcion')
                ->get(['id', 'codigo', 'descripcion', 'precio', 'igv_percent', 'stock', 'is_composite']),
            'customers' => Customer::where('company_id', $companyId)
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'documento_tipo', 'documento_numero', 'email', 'telefono', 'direccion']),
            'igvPercent' => (float) (Company::find($companyId)?->getActiveIgvPercent() ?? 18),
        ];
    }

    private function prepareData(Request $request, string $tipo, int $companyId): array
    {
        if ($request->filled('customer_id')) {
            $customer = Customer::where('company_id', $companyId)->find($request->input('customer_id'));

            if ($customer) {
                $request->merge([
                    'cliente_nombre' => $customer->nombre,
                    'cliente_documento' => $customer->documento_numero,
                    'cliente_email' => $customer->email,
                    'cliente_telefono' => $customer->telefono,
                ]);
            }
        }

        $data = $this->validatedData($request, $tipo);
        unset($data['items']);

        $items = [];
        $igvPercent = (float) (Company::find($companyId)?->getActiveIgvPercent() ?? 18);

        if (in_array($tipo, [Preventa::TIPO_COTIZACION, Preventa::TIPO_PEDIDO], true)) {
            $items = $this->buildItems($request, $companyId, $igvPercent);
            $data['monto'] = round(array_sum(array_column($items, 'total')), 2);
            $data['subtotal'] = round(array_sum(array_column($items, 'subtotal')), 2);
            $data['igv'] = round(array_sum(array_column($items, 'igv')), 2);
        }

        if (($data['payment_condition'] ?? null) === 'CREDITO' && !empty($data['credit_days'])) {
            $data['fecha_vencimiento'] = Carbon::parse($data['fecha'])
                ->addDays((int) $data['credit_days'])
                ->format('Y-m-d');
        } elseif (($data['payment_condition'] ?? null) === 'CONTADO') {
            $data['credit_days'] = null;
            $data['fecha_vencimiento'] = null;
        }

        return [$data, $items];
    }

    private function buildItems(Request $request, int $companyId, float $igvPercent): array
    {
        $rows = $request->input('items', []);
        $productIds = collect($rows)->pluck('product_id')->filter()->unique();
        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($rows as $row) {
            $product = $products->get((int) ($row['product_id'] ?? 0));
            $quantity = round((float) ($row['cantidad'] ?? 0), 4);

            if (!$product || $quantity <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Seleccione un producto válido y una cantidad mayor a cero.',
                ]);
            }

            $price = round((float) $product->precio, 2);
            $total = round($quantity * $price, 2);
            $rate = (float) ($product->igv_percent ?? $igvPercent);
            $subtotal = round($total / (1 + ($rate / 100)), 2);

            $items[] = [
                'product_id' => $product->id,
                'descripcion' => $product->descripcion,
                'cantidad' => $quantity,
                'precio_unitario' => $price,
                'igv_percent' => $rate,
                'subtotal' => $subtotal,
                'igv' => round($total - $subtotal, 2),
                'total' => $total,
            ];
        }

        return $items;
    }

    private function validatedData(Request $request, string $tipo): array
    {
        $rules = [
            'cliente_nombre' => 'required|string|max:255',
            'cliente_documento' => 'nullable|string|max:20',
            'cliente_email' => 'nullable|email|max:255',
            'cliente_telefono' => 'nullable|string|max:30',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'estado' => 'required|in:' . implode(',', array_keys(Preventa::ESTADOS)),
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones' => 'nullable|string|max:5000',
        ];

        if (in_array($tipo, [Preventa::TIPO_COTIZACION, Preventa::TIPO_PEDIDO], true)) {
            $rules += [
                'customer_id' => 'nullable|exists:customers,id',
                'document_type' => 'required|in:01,03',
                'payment_condition' => 'required|in:CONTADO,CREDITO',
                'credit_days' => 'nullable|required_if:payment_condition,CREDITO|integer|in:7,15,30,45',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.cantidad' => 'required|numeric|min:0.0001',
            ];
        } else {
            $rules['monto'] = 'nullable|numeric|min:0|max:99999999.99';
        }

        return $request->validate($rules);
    }
}
