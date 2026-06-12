<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\SpecialDocument;
use App\Models\Serie;
use App\Services\SpecialDocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index($tipo)
    {
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

    public function create($tipo)
    {
        $companyId = Company::getMainCompany()->id;
        $company = Company::find($companyId);
        $title = match($tipo) {
            'R' => 'Nueva Retención',
            'T' => 'Nueva Guía de Remisión',
            'P' => 'Nueva Percepción',
        };

        // Find the active series for this doc type
        $series = Serie::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->where('serie', 'like', $tipo . '%')
            ->get();

        return view('documents.create', compact('title', 'tipo', 'company', 'series'));
    }

    public function store(Request $request, $tipo)
    {
        $companyId = Company::getMainCompany()->id;
        $tipos = ['R' => '20', 'T' => '09', 'P' => '40'];

        $validated = $request->validate([
            'serie_id' => 'required|exists:series,id',
            'fecha_emision' => 'required|date',
            'entity_tipo_doc' => 'required|in:6',
            'entity_num_doc' => 'required|size:11',
            'entity_razon_social' => 'required',
            'total' => 'required|numeric|min:0',
            'items' => $tipo === 'T' ? 'required|json' : 'nullable',
        ]);

        $serie = Serie::findOrFail($validated['serie_id']);
        $nextNumber = $serie->getNextNumber();

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
        $companyId = Company::getMainCompany()->id;
        $company = Company::find($companyId);
        $customer = $invoice->customer;

        // Find or create T001 series
        $serie = Serie::where('company_id', $companyId)
            ->where('serie', 'like', 'T%')
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$serie) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'No hay serie activa para Guías de Remisión. Cree una serie T001 primero.');
        }

        $nextNumber = $serie->getNextNumber();
        $fullNumber = $serie->serie . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        $doc = SpecialDocument::create([
            'company_id' => $companyId,
            'tipo_documento' => '09',
            'serie' => $serie->serie,
            'numero' => $nextNumber,
            'full_number' => $fullNumber,
            'fecha_emision' => date('Y-m-d'),
            'total' => $invoice->total,
            'dir_partida' => $company->direccion ?? 'Lima',
            'dir_llegada' => $customer->direccion ?? 'Lima',
        ]);

        $serie->incrementNumber();

        // Create entity
        $doc->entity()->create([
            'tipo_doc' => $customer->documento_tipo == '6' ? '6' : '1',
            'num_doc' => $customer->documento_numero,
            'razon_social' => $customer->nombre,
            'direccion' => $customer->direccion,
        ]);

        // Copy invoice items
        foreach ($invoice->items as $item) {
            $doc->items()->create([
                'codigo' => $item->codigo ?? '',
                'descripcion' => $item->descripcion,
                'cantidad' => $item->cantidad,
                'unidad' => 'NIU',
            ]);
        }

        return redirect()->route('documents.show', ['T', $doc->id])
            ->with('success', "Guía de Remisión {$fullNumber} generada desde {$invoice->full_number}");
    }
}
