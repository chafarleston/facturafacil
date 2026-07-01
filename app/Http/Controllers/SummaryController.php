<?php

namespace App\Http\Controllers;

use App\Models\SummaryDocument;
use App\Services\GreenterService;
use App\Services\SummaryService;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', '');
        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);

        $summaries = SummaryDocument::where('company_id', $companyId)
            ->byStatus($status)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingCount = SummaryDocument::where('company_id', $companyId)
            ->pending()
            ->count();

        // Envíos individuales (facturas, boletas, NC, ND)
        $individualSends = \App\Models\Invoice::where('company_id', $companyId)
            ->whereIn('sunat_estado', ['ACEPTADO', 'RECHAZADO', 'ENVIADO', 'ANULADO', 'PENDIENTE'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('sunat-summaries.index', compact('summaries', 'status', 'pendingCount', 'companyId', 'individualSends'));
    }

    public function checkStatus(SummaryDocument $summary)
    {
        if (!$summary->ticket) {
            return back()->with('error', 'Este resumen no tiene ticket asignado');
        }

        try {
            $summaryService = new SummaryService();
            $result = $summaryService->checkTicketStatus($summary->ticket);

            if ($result['success']) {
                return redirect()->route('sunat-summaries.index')
                    ->with('success', 'Resumen ' . $summary->correlativo . ' aceptado por SUNAT');
            } else {
                return redirect()->route('sunat-summaries.index')
                    ->with('info', 'Resumen ' . $summary->correlativo . ': ' . $result['description']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function checkAllPending()
    {
        try {
            $summaryService = new SummaryService();
            $pending = SummaryDocument::pending()->whereNotNull('ticket')->get();

            if ($pending->isEmpty()) {
                return redirect()->route('sunat-summaries.index')
                    ->with('info', 'No hay resúmenes pendientes');
            }

            $aceptados = 0;
            $errores = 0;

            foreach ($pending as $summary) {
                $result = $summaryService->checkTicketStatus($summary->ticket);
                if ($result['success']) {
                    $aceptados++;
                } else {
                    $errores++;
                }
            }

            return redirect()->route('sunat-summaries.index')
                ->with('success', "Procesados: {$pending->count()} | Aceptados: {$aceptados} | Pendientes: {$errores}");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function retryPending(GreenterService $greenterService, SummaryService $summaryService)
    {
        $invoices = \App\Models\Invoice::whereIn('sunat_estado', ['PENDIENTE', 'ERROR', 'RECHAZADO'])
            ->whereIn('tipo_documento', ['01', '03'])
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->route('sunat-summaries.index')
                ->with('info', 'No hay documentos pendientes');
        }

        $sent = 0;
        $errors = 0;
        $messages = [];

        foreach ($invoices as $invoice) {
            if ($invoice->tipo_documento === '03') {
                $result = $summaryService->sendBoletaToSummary($invoice);
            } else {
                $result = $greenterService->sendInvoice($invoice);
            }

            if ($result['success']) {
                $sent++;
            } else {
                $errors++;
                $messages[] = "{$invoice->full_number}: {$result['description']}";
            }
        }

        $msg = "{$sent} enviado(s), {$errors} con error(es)";
        if (!empty($messages)) {
            $msg .= ' | ' . implode(' | ', array_slice($messages, 0, 3));
        }

        return redirect()->route('sunat-summaries.index')
            ->with($errors > 0 ? 'warning' : 'success', $msg);
    }

    public function sendDaily(SummaryService $summaryService)
    {
        $result = $summaryService->sendDailySummary();

        if ($result['success']) {
            return redirect()->route('sunat-summaries.index')
                ->with('success', $result['description']);
        }

        return redirect()->route('sunat-summaries.index')
            ->with('info', $result['description']);
    }
}
