<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\SpecialDocument;
use App\Models\Serie;
use Illuminate\Http\Request;
use App\Services\SpecialDocumentService;

class DocumentController extends Controller
{
    public function index($tipo)
    {
        $this->authorize('permission', 'send_sunat');
        $companyId = Company::getMainCompany()->id;
        $tipos = ['R' => '20', 'T' => '09', 'P' => '40'];
        $codigos = ['R' => '20', 'T' => '09', 'P' => '40'];

        if (!isset($codigos[$tipo])) abort(404);

        $docs = SpecialDocument::where('company_id', $companyId)
            ->byType($codigos[$tipo])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $title = match($tipo) {
            'R' => 'Retenciones',
            'T' => 'Guías de Remisión',
            'P' => 'Percepciones',
        };

        return view('documents.index', compact('docs', 'title', 'tipo'));
    }

    public function create(Request $request, $tipo)
    {
        $this->authorize('permission', 'send_sunat');
        $companyId = Company::getMainCompany()->id;
        $company = Company::find($companyId);
        $tipos = ['R' => '20', 'T' => '09', 'P' => '40'];
        $title = match($tipo) {
            'R' => 'Nueva Retenci��n',
            'T' => 'Nueva Gu��a de Remisi��n',
            'P' => 'Nueva Percepci��n',
        };

        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($tipo === 'T', fn ($query) => $query->where('tipo_documento', $tipos[$tipo]))
            ->get();

        $invoices = [];
        if ($tipo === 'T') {
            $invoices = \App\Models\Invoice::where('company_id', $companyId)
                ->whereIn('tipo_documento', ['01', '03'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        $invoiceId = $tipo === 'T' ? $request->integer('invoice') : null;

        return view('documents.create', compact('title', 'tipo', 'company', 'series', 'invoices', 'invoiceId'));
    }

    public function store(Request $request, $tipo)
    {
        $this->authorize('permission', 'send_sunat');
        $companyId = Company::getMainCompany()->id;
        $tipos = ['R' => '20', 'T' => '09', 'P' => '40'];

        $validated = $request->validate([
            'serie_id' => 'required|exists:series,id',
            'fecha_emision' => 'required|date',
            'entity_tipo_doc' => 'required|in:6',
            'entity_num_doc' => 'required|size:11',
            'entity_razon_social' => 'required',
            'entity_direccion' => 'nullable|string|max:255',
            'total' => 'required|numeric|min:0',
            'items' => $tipo === 'T' ? 'required|json' : 'nullable',
            'observacion' => 'nullable|string|max:255',
            'regimen' => 'nullable|in:01,02',
            'tasa' => 'nullable|numeric|min:0',
            'imp_retenido' => 'nullable|numeric|min:0',
            'imp_pagado' => 'nullable|numeric|min:0',
            'dir_partida' => $tipo === 'T' ? 'required|string|max:255' : 'nullable|string|max:255',
            'dir_llegada' => $tipo === 'T' ? 'required|string|max:255' : 'nullable|string|max:255',
            'cod_traslado' => 'nullable|string|size:2',
            'des_traslado' => 'nullable|string|max:200',
            'mod_traslado' => 'nullable|in:01,02',
            'fecha_traslado' => 'nullable|date',
            'peso_total' => $tipo === 'T' ? 'required|numeric|min:0.001' : 'nullable|numeric|min:0',
            'und_peso_total' => 'nullable|string|max:5',
            'num_bultos' => 'nullable|integer|min:0',
            'ubigeo_partida' => 'nullable|string|size:6',
            'ubigeo_llegada' => 'nullable|string|size:6',
            'transportista_tipo_doc' => 'nullable|string|max:2',
            'transportista_num_doc' => 'nullable|string|max:15',
            'transportista_razon_social' => 'nullable|string|max:255',
            'transportista_nro_mtc' => 'nullable|string|max:50',
            'vehiculo_placa' => 'nullable|string|max:20',
            'vehiculo_nro_circulacion' => 'nullable|string|max:50',
            'vehiculo_cod_emisor' => 'nullable|string|max:50',
            'vehiculo_nro_autorizacion' => 'nullable|string|max:50',
            'chofer_tipo_doc' => 'nullable|string|max:2',
            'chofer_num_doc' => 'nullable|string|max:15',
            'chofer_nombres' => 'nullable|string|max:150',
            'chofer_apellidos' => 'nullable|string|max:150',
            'chofer_licencia' => 'nullable|string|max:50',
        ]);

        $serie = Serie::findOrFail($validated['serie_id']);
        $nextNumber = $serie->getNextNumber();

        if ($tipo === 'T' && $serie->tipo_documento !== '09') {
            return back()->withInput()->withErrors([
                'serie_id' => 'La serie seleccionada no es de Guía de Remisión.',
            ]);
        }

        $doc = SpecialDocument::create([
            'company_id' => $companyId,
            'tipo_documento' => $tipos[$tipo],
            'serie' => $serie->serie,
            'numero' => $nextNumber,
            'full_number' => $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => $validated['fecha_emision'],
            'total' => $validated['total'],
            'regimen' => $request->regimen,
            'tasa' => $request->tasa,
            'imp_retenido' => $request->imp_retenido,
            'imp_pagado' => $request->imp_pagado,
            'observacion' => $request->observacion,
            'dir_partida' => $request->dir_partida,
            'dir_llegada' => $request->dir_llegada,
            'cod_traslado' => $request->cod_traslado ?: '01',
            'des_traslado' => $request->des_traslado,
            'mod_traslado' => $request->mod_traslado ?: '01',
            'fecha_traslado' => $request->fecha_traslado ?: $validated['fecha_emision'],
            'peso_total' => $request->peso_total ?? 0,
            'und_peso_total' => $request->und_peso_total ?: 'KGM',
            'num_bultos' => $request->num_bultos,
            'ubigeo_partida' => $request->ubigeo_partida,
            'ubigeo_llegada' => $request->ubigeo_llegada,
            'transportista_tipo_doc' => $request->transportista_tipo_doc,
            'transportista_num_doc' => $request->transportista_num_doc,
            'transportista_razon_social' => $request->transportista_razon_social,
            'transportista_nro_mtc' => $request->transportista_nro_mtc,
            'vehiculo_placa' => $request->vehiculo_placa,
            'vehiculo_nro_circulacion' => $request->vehiculo_nro_circulacion,
            'vehiculo_cod_emisor' => $request->vehiculo_cod_emisor,
            'vehiculo_nro_autorizacion' => $request->vehiculo_nro_autorizacion,
            'chofer_tipo_doc' => $request->chofer_tipo_doc,
            'chofer_num_doc' => $request->chofer_num_doc,
            'chofer_nombres' => $request->chofer_nombres,
            'chofer_apellidos' => $request->chofer_apellidos,
            'chofer_licencia' => $request->chofer_licencia,
        ]);

        $serie->incrementNumber();

        // Create entity (supplier/client)
        $doc->entity()->create([
            'tipo_doc' => $validated['entity_tipo_doc'],
            'num_doc' => $validated['entity_num_doc'],
            'razon_social' => $validated['entity_razon_social'],
            'direccion' => $request->entity_direccion,
        ]);

        // Create items for despatch
        if ($tipo === 'T' && $request->items) {
            $items = json_decode($request->items, true);
            foreach ($items as $item) {
                $doc->items()->create([
                    'codigo' => $item['codigo'] ?? '',
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'unidad' => $item['unidad'] ?? 'NIU',
                ]);
            }
        }

        return redirect()->route('documents.show', [$tipo, $doc->id])
            ->with('success', "{$doc->full_number} creado correctamente");
    }

    public function show($tipo, SpecialDocument $document)
    {
        $this->authorize('permission', 'send_sunat');
        $title = match($tipo) {
            'R' => 'Retención',
            'T' => 'Guía de Remisión',
            'P' => 'Percepción',
        };
        $document->load(['entity', 'items']);
        return view('documents.show', compact('document', 'title', 'tipo'));
    }

    public function send($tipo, SpecialDocument $document, SpecialDocumentService $service)
    {
        $this->authorize('permission', 'send_sunat');
        $result = match($tipo) {
            'R' => $service->sendRetention($document),
            'T' => $service->sendDespatch($document),
            'P' => $service->sendPerception($document),
        };

        if ($result['success']) {
            return redirect()->route('documents.show', [$tipo, $document->id])
                ->with('success', $result['description']);
        }

        return redirect()->route('documents.show', [$tipo, $document->id])
            ->with('error', $result['description']);
    }

    public function createFromInvoice(Invoice $invoice)
    {
        $this->authorize('permission', 'send_sunat');
        $companyId = Company::getMainCompany()->id;

        $serie = Serie::where('company_id', $companyId)
            ->where('tipo_documento', '09')
            ->where('serie', 'like', 'T%')
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$serie) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'No hay serie activa tipo 09 para Guías de Remisión. Cree una serie T001 primero.');
        }

        return redirect()->route('documents.create', ['tipo' => 'T', 'invoice' => $invoice->id])
            ->with('success', "Datos de {$invoice->full_number} precargados. Complete los datos de traslado y transporte.");
    }

    public function getInvoiceData($id)
    {
        $this->authorize('permission', 'send_sunat');
        $invoice = Invoice::with(['customer', 'items'])->find($id);
        if (!$invoice) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        return response()->json([
            'id' => $invoice->id,
            'full_number' => $invoice->full_number,
            'tipo_documento' => $invoice->tipo_documento,
            'fecha_emision' => $invoice->fecha_emision,
            'total' => $invoice->total,
            'customer' => $invoice->customer ? [
                'tipo_doc' => $invoice->customer->documento_tipo ?? '6',
                'num_doc' => $invoice->customer->documento_numero ?? '',
                'razon_social' => $invoice->customer->nombre ?? '',
                'direccion' => $invoice->customer->direccion ?? '',
            ] : null,
            'items' => $invoice->items->map(fn($i) => [
                'codigo' => $i->codigo ?? '',
                'descripcion' => $i->descripcion,
                'cantidad' => $i->cantidad,
                'unidad' => 'NIU',
            ]),
        ]);
    }
}
