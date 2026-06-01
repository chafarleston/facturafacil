<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\Pro51ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Pro51Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::findOrFail($request->company_id);

        if ($company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'Esta empresa no tiene configurado el modo API externa'
            ]);
        }

        if (!$company->pro51_url || !$company->pro51_token) {
            return response()->json([
                'success' => false,
                'message' => 'Complete la URL del servidor y el token de API'
            ]);
        }

        try {
            $api = new Pro51ApiService($company);
            $result = $api->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ]);
        }
    }

    public function syncProduct(Request $request, Product $product)
    {
        $company = $product->company;

        if (!$company || $company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        try {
            $igvType = Pro51ApiService::getIgvTypeCode($product->tipo_afectacion);
            $igvPercent = $company->getActiveIgvPercent();

            $data = [
                'codigo_interno' => $product->codigo,
                'descripcion' => $product->descripcion,
                'nombre' => $product->descripcion,
                'unidad_de_medida' => $product->umedida_codigo ?? 'NIU',
                'precio_unitario' => (float) $product->precio,
                'codigo_tipo_afectacion_igv' => $igvType,
                'porcentaje_igv' => $igvPercent,
            ];

            $api = new Pro51ApiService($company);
            $response = $api->syncProduct($data);

            if ($response['success'] ?? false) {
                $pro51ItemId = $response['data']['item_id'] ?? $response['data']['id'] ?? null;

                $product->update([
                    'pro51_codigo_interno' => $product->codigo,
                    'pro51_synced_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Producto sincronizado correctamente',
                    'pro51_item_id' => $pro51ItemId,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Error al sincronizar producto'
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing product to pro51', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function syncAllProducts(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::findOrFail($request->company_id);

        if ($company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        $products = Product::where('company_id', $company->id)
            ->where('estado', 'ACTIVO')
            ->where(function ($q) {
                $q->whereNull('pro51_synced_at')
                  ->orWhere('pro51_synced_at', '<', \DB::raw('updated_at'));
            })
            ->get();

        $synced = 0;
        $errors = [];

        foreach ($products as $product) {
            try {
                $igvType = Pro51ApiService::getIgvTypeCode($product->tipo_afectacion);
                $igvPercent = $company->getActiveIgvPercent();

                $data = [
                    'codigo_interno' => $product->codigo,
                    'descripcion' => $product->descripcion,
                    'nombre' => $product->descripcion,
                    'unidad_de_medida' => $product->umedida_codigo ?? 'NIU',
                    'precio_unitario' => (float) $product->precio,
                    'codigo_tipo_afectacion_igv' => $igvType,
                    'porcentaje_igv' => $igvPercent,
                ];

                $api = new Pro51ApiService($company);
                $response = $api->syncProduct($data);

                if ($response['success'] ?? false) {
                    $product->update([
                        'pro51_codigo_interno' => $product->codigo,
                        'pro51_synced_at' => now(),
                    ]);
                    $synced++;
                } else {
                    $errors[] = "{$product->codigo}: {$response['message']}";
                }
            } catch (\Exception $e) {
                $errors[] = "{$product->codigo}: {$e->getMessage()}";
            }
        }

        $msg = "Sincronización completada: {$synced} productos sincronizados";
        if (!empty($errors)) {
            $msg .= '. Errores: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'synced' => $synced,
            'errors' => $errors,
        ]);
    }

    public function syncSeries(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::findOrFail($request->company_id);

        if ($company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        try {
            $api = new Pro51ApiService($company);
            $result = $api->getSeries();

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Error al obtener series de pro51'
                ]);
            }

            $seriesList = $result['data'] ?? [];
            $created = 0;
            $updated = 0;

            foreach ($seriesList as $seriesItem) {
                $documentTypeId = $seriesItem['document_type_id'] ?? null;
                $number = $seriesItem['number'] ?? null;

                if (!$documentTypeId || !$number) continue;

                if (!in_array($documentTypeId, ['01', '03', '07', '08', '09', '31', '80'])) continue;

                $serie = \App\Models\Serie::firstOrNew([
                    'company_id' => $company->id,
                    'tipo_documento' => $documentTypeId,
                    'serie' => $number,
                ]);

                if (!$serie->exists) {
                    $serie->numero_actual = 0;
                    $serie->estado = 'ACTIVO';
                    $serie->save();
                    $created++;
                } else {
                    $updated++;
                }
            }

            $invoiceSerie = collect($seriesList)->firstWhere('document_type_id', '01');
            $receiptSerie = collect($seriesList)->firstWhere('document_type_id', '03');

            if ($invoiceSerie && isset($invoiceSerie['number'])) {
                $company->pro51_series_invoice = $invoiceSerie['number'];
            }
            if ($receiptSerie && isset($receiptSerie['number'])) {
                $company->pro51_series_receipt = $receiptSerie['number'];
            }
            if ($invoiceSerie || $receiptSerie) {
                $company->save();
            }

            $docsResponse = $api->apiGet('documents/lists/2020-01-01/2030-12-31');
            $docsList = $docsResponse['data'] ?? $docsResponse;
            $maxNumberBySerie = [];

            if (is_array($docsList)) {
                foreach ($docsList as $doc) {
                    $doc = (array) $doc;
                    $fullNumber = $doc['number_full'] ?? $doc['full_number'] ?? '';
                    if ($fullNumber) {
                        $parts = explode('-', $fullNumber);
                        $docSeries = $parts[0] ?? '';
                        $docNum = (int) ($parts[1] ?? 0);
                    } else {
                        $docSeries = $doc['series'] ?? $doc['serie'] ?? '';
                        $docNum = (int) ($doc['number'] ?? $doc['numero'] ?? 0);
                    }

                    if ($docSeries && $docNum > ($maxNumberBySerie[$docSeries] ?? 0)) {
                        $maxNumberBySerie[$docSeries] = $docNum;
                    }
                }

                foreach ($maxNumberBySerie as $serieNumber => $maxNum) {
                    \App\Models\Serie::where('company_id', $company->id)
                        ->where('serie', $serieNumber)
                        ->where('numero_actual', '<', $maxNum)
                        ->update(['numero_actual' => $maxNum]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Series sincronizadas: {$created} creadas, {$updated} ya existían. Correlativos actualizados.",
                'created' => $created,
                'updated' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function pendingList(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id;

        $company = Company::find($companyId);

        $invoices = Invoice::with(['customer', 'company'])
            ->where('company_id', $companyId)
            ->whereNull('pro51_external_id')
            ->where('tipo_documento', '!=', 'NV')
            ->whereIn('sunat_estado', ['PENDIENTE', 'RECHAZADO'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pro51.pending', compact('invoices', 'company'));
    }

    public function retryAll(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id;

        $company = Company::find($companyId);
        $invoices = Invoice::where('company_id', $companyId)
            ->whereNull('pro51_external_id')
            ->where('tipo_documento', '!=', 'NV')
            ->whereIn('sunat_estado', ['PENDIENTE', 'RECHAZADO'])
            ->where(function ($q) use ($company) {
                if ($company?->pro51_activated_at) {
                    $q->where('created_at', '>=', $company->pro51_activated_at);
                }
            })
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            try {
                $controller = app(\App\Http\Controllers\InvoiceController::class);
                $controller->sendToPro51($invoice, $invoice->company);
                $invoice->refresh();
                if ($invoice->pro51_external_id) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $message = "Proceso completado: {$sent} enviados, {$failed} fallaron";

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function retryPending(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::with(['company', 'items'])->findOrFail($request->invoice_id);

        if ($invoice->company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        try {
            $controller = app(\App\Http\Controllers\InvoiceController::class);
            $controller->sendToPro51($invoice, $invoice->company);

            $invoice->refresh();

            if ($invoice->pro51_external_id) {
                $message = "Documento {$invoice->full_number} enviado correctamente";
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return redirect()->back()->with('success', $message);
            }

            $message = "Error: {$invoice->sunat_description}";
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->with('error', $message);
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    public function syncExistingDocuments(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id;
        $company = Company::find($companyId);

        if (!$company || $company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        try {
            $api = new Pro51ApiService($company);
            $docsResponse = $api->apiGet('documents/lists/2020-01-01/2030-12-31');
            $docsList = $docsResponse['data'] ?? $docsResponse;

            if (!is_array($docsList) || empty($docsList)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron obtener documentos de pro51'
                ]);
            }

            $synced = 0;
            $errors = [];

            foreach ($docsList as $doc) {
                $doc = (array) $doc;
                $externalId = $doc['external_id'] ?? '';
                $docType = $doc['document_type_id'] ?? '';
                $fullNumber = $doc['number'] ?? '';
                $parts = explode('-', $fullNumber);
                $docSeries = $parts[0] ?? '';
                $docNum = (int) ($parts[1] ?? 0);

                if (!$docSeries || !$docNum || !$docType || !$externalId) continue;

                $invoice = Invoice::where('company_id', $company->id)
                    ->where('serie', $docSeries)
                    ->where('numero', $docNum)
                    ->where('tipo_documento', $docType)
                    ->whereNull('pro51_external_id')
                    ->first();

                if ($invoice) {
                    $stateMap = [
                        '01' => 'PENDIENTE', '03' => 'ENVIADO', '05' => 'ACEPTADO',
                        '07' => 'OBSERVADO', '09' => 'RECHAZADO', '11' => 'ANULADO', '13' => 'ANULADO',
                    ];

                    $baseUrl = rtrim($company->pro51_url, '/');
                    $ticketUrl = "{$baseUrl}/print/document/{$externalId}/ticket";

                    $invoice->update([
                        'pro51_external_id' => $externalId,
                        'pro51_response' => json_encode($doc),
                        'pro51_pdf_url' => $doc['download_pdf'] ?? null,
                        'pro51_ticket_url' => $ticketUrl,
                        'sunat_estado' => $stateMap[$doc['state_type_id'] ?? ''] ?? 'PENDIENTE',
                        'pro51_sent_at' => $doc['created_at'] ?? now(),
                    ]);

                    $synced++;
                }
            }

            $message = "{$synced} documentos vinculados correctamente";
            if (!empty($errors)) {
                $message .= '. Errores: ' . implode('; ', $errors);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    public function updatePro51Status(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id;
        $company = Company::find($companyId);

        if (!$company || $company->facturacion_mode !== 'api_externa') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no usa facturación externa'
            ]);
        }

        try {
            $api = new Pro51ApiService($company);
            $docsResponse = $api->apiGet('documents/lists/2020-01-01/2030-12-31');
            $docsList = $docsResponse['data'] ?? $docsResponse;

            if (!is_array($docsList) || empty($docsList)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron obtener documentos de pro51'
                ]);
            }

            $updated = 0;
            $errors = [];

            $stateMap = [
                '01' => 'PENDIENTE', '03' => 'ENVIADO', '05' => 'ACEPTADO',
                '07' => 'OBSERVADO', '09' => 'RECHAZADO', '11' => 'ANULADO', '13' => 'ANULADO',
            ];

            foreach ($docsList as $doc) {
                $doc = (array) $doc;
                $externalId = $doc['external_id'] ?? '';
                $pro51StateId = $doc['state_type_id'] ?? '';

                if (!$externalId || !$pro51StateId) continue;

                $invoice = Invoice::where('company_id', $company->id)
                    ->where('pro51_external_id', $externalId)
                    ->first();

                if ($invoice) {
                    $newEstado = $stateMap[$pro51StateId] ?? null;
                    if ($newEstado && $newEstado !== $invoice->sunat_estado) {
                        $invoice->update(['sunat_estado' => $newEstado]);
                        $updated++;
                    }
                }
            }

            $message = "{$updated} documentos actualizados";

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->back()->with('error', $message);
        }
    }
}
