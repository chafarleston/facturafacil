<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice as InvoiceModel;
use App\Services\SummaryService;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company as GreenterCompany;
use Greenter\Model\Company\Address;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Sale\Note;

class GreenterService
{
    private $see;
    private $company;
    
    public function __construct()
    {
        $this->see = null;
    }
    
    public function sendCreditNote(InvoiceModel $invoice, string $motivo, string $descripcion)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company) {
            return [
                'success' => false,
                'code' => 'NO_COMPANY',
                'description' => 'No hay empresa principal configurada'
            ];
        }
        
        if (!$company->certificado_path && !$company->certificate) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado digital configurado'
            ];
        }

        // ND relacionada a Boleta debe ir por Resumen Diario
        if ($invoice->tipo_documento === '03') {
            return $this->sendNoteViaSummary($invoice, $company, '08', $motivo, $descripcion);
        }
        
        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 'CERT_ERROR',
                'description' => $e->getMessage()
            ];
        }
        
        try {
            // Determine NC serie based on original invoice type
            $ncPrefix = $invoice->tipo_documento === '01' ? 'FC' : 'BC';
            $serie = \App\Models\Serie::where('company_id', $company->id)
                ->where('tipo_documento', '07')
                ->where('serie', 'like', $ncPrefix . '%')
                ->where('estado', 'ACTIVO')
                ->first();

            if (!$serie) {
                $serie = \App\Models\Serie::create([
                    'company_id' => $company->id,
                    'tipo_documento' => '07',
                    'serie' => $ncPrefix . '01',
                    'numero_actual' => 0,
                    'estado' => 'ACTIVO',
                ]);
            }

            $nextNumber = $serie->getNextNumber();
            $correlativo = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            $note = new Note();
            
            $note->setUblVersion('2.1');
            $note->setTipoDoc('07');
            $note->setSerie($serie->serie);
            $note->setCorrelativo($correlativo);
            $note->setFechaEmision(new \DateTime());
            $note->setTipoMoneda('PEN');
            
            $note->setTipDocAfectado($invoice->tipo_documento);
            $note->setNumDocfectado($invoice->full_number);
            $note->setCodMotivo($motivo);
            $note->setDesMotivo($descripcion);
            
            $note->setCompany($this->buildCompany($company));
            
            $cd = $this->getClientData($invoice);
            $greenterClient = new Client();
            $greenterClient->setTipoDoc($cd['tipo_doc']);
            $greenterClient->setNumDoc($cd['num_doc']);
            $greenterClient->setRznSocial($cd['razon_social']);
            if ($cd['direccion']) {
                $clientAddress = new Address();
                $clientAddress->setDireccion($cd['direccion']);
                $greenterClient->setAddress($clientAddress);
            }
            $note->setClient($greenterClient);
            
            $notaTotal = $invoice->total;
            $notaIgv = $invoice->igv;
            $notaSubtotal = $invoice->subtotal;
            
            $note->setMtoOperGravadas($notaSubtotal);
            $note->setMtoIGV($notaIgv);
            $note->setTotalImpuestos($notaIgv);
            $note->setValorVenta($notaSubtotal);
            $note->setSubTotal($notaTotal);
            $note->setMtoImpVenta($notaTotal);
            
            $lines = [];
            foreach ($invoice->items as $item) {
                $line = new SaleDetail();
                $line->setUnidad('NIU');
                $line->setCodProducto($item->codigo ?? '');
                $line->setDescripcion($item->descripcion);
                $line->setCantidad($item->cantidad);
                $rate = $company->getIgvRate();
                $igvPct = $company->getActiveIgvPercent();
                $valorUnitario = round($item->precio_unitario / (1 + $rate), 2);
                $baseIgv = round($valorUnitario * $item->cantidad, 2);
                $igvItem = round($baseIgv * $rate, 2);
                $line->setMtoValorUnitario($valorUnitario);
                $line->setMtoPrecioUnitario($item->precio_unitario);
                $line->setTipAfeIgv('10');
                $line->setMtoBaseIgv($baseIgv);
                $line->setPorcentajeIgv($igvPct);
                $line->setIgv($igvItem);
                $line->setMtoValorVenta($baseIgv);
                $line->setTotalImpuestos($igvItem);
                $lines[] = $line;
            }
            $note->setDetails($lines);
            
            $legend = new Legend();
            $legend->setCode('1000');
            $legend->setValue($invoice->total_letras ?? 'SON ' . number_to_letter($notaTotal) . ' SOLES');
            $note->setLegends([$legend]);
            
            $result = $this->see->send($note);
            
            if ($result->isSuccess()) {
                $serie->incrementNumber();
                
                $noteInvoice = new InvoiceModel();
                $noteInvoice->company_id = $company->id;
                $noteInvoice->customer_id = $invoice->customer_id;
                $noteInvoice->tipo_documento = '07';
                $noteInvoice->serie = $serie->serie;
                $noteInvoice->numero = $nextNumber;
                $noteInvoice->full_number = $serie->serie . '-' . $correlativo;
                $noteInvoice->fecha_emision = date('Y-m-d');
                $noteInvoice->fecha_vencimiento = date('Y-m-d');
                $noteInvoice->moneda = 'PEN';
                $noteInvoice->gravado = $notaSubtotal;
                $noteInvoice->igv = $notaIgv;
                $noteInvoice->total = $notaTotal;
                $noteInvoice->subtotal = $notaSubtotal;
                $noteInvoice->total_letras = $invoice->total_letras ?? 'SON ' . number_to_letter($notaTotal) . ' SOLES';
                $noteInvoice->sunat_estado = 'ACEPTADO';
                $noteInvoice->sunat_code = '0';
                $noteInvoice->sunat_description = 'ACEPTADO';
                $noteInvoice->save();
                
                \DB::table('invoices')->where('id', $invoice->id)->update(['credit_note_id' => $noteInvoice->id]);
                
                foreach ($invoice->items as $item) {
                    $noteInvoice->items()->create([
                        'codigo' => $item->codigo,
                        'descripcion' => $item->descripcion,
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => $item->precio_unitario,
                        'precio_venta' => $item->precio_venta,
                        'igv' => $item->igv
                    ]);
                }
                
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Nota de crédito generada correctamente',
                    'note_number' => $serie->serie . '-' . $correlativo,
                ];
            } else {
                $error = $result->getError();
                return [
                    'success' => false,
                    'code' => $error->getCode() ?? 'ERROR',
                    'description' => $error->getMessage() ?? 'Error desconocido'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Credit Note Error: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => 'EXCEPTION',
                'description' => $e->getMessage()
            ];
        }
    }
    
    private function voidInvoiceNumber()
    {
        return str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
    
    public function sendDebitNote(InvoiceModel $invoice, string $motivo, string $descripcion)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company) {
            return [
                'success' => false,
                'code' => 'NO_COMPANY',
                'description' => 'No hay empresa principal configurada'
            ];
        }
        
        if (!$company->certificado_path && !$company->certificate) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado digital configurado'
            ];
        }
        
        // ND relacionada a Boleta debe ir por Resumen Diario
        if ($invoice->tipo_documento === '03') {
            return $this->sendNoteViaSummary($invoice, $company, '08', $motivo, $descripcion);
        }
        
        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 'CERT_ERROR',
                'description' => $e->getMessage()
            ];
        }
        
        try {
            $ndPrefix = $invoice->tipo_documento === '01' ? 'FD' : 'BD';
            $serie = \App\Models\Serie::where('company_id', $company->id)
                ->where('tipo_documento', '08')
                ->where('serie', 'like', $ndPrefix . '%')
                ->where('estado', 'ACTIVO')
                ->first();

            if (!$serie) {
                $serie = \App\Models\Serie::create([
                    'company_id' => $company->id,
                    'tipo_documento' => '08',
                    'serie' => $ndPrefix . '01',
                    'numero_actual' => 0,
                    'estado' => 'ACTIVO',
                ]);
            }

            $nextNumber = $serie->getNextNumber();
            $correlativo = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            $note = new Note();
            
            $note->setUblVersion('2.1');
            $note->setTipoDoc('08');
            $note->setSerie($serie->serie);
            $note->setCorrelativo($correlativo);
            $note->setFechaEmision(new \DateTime());
            $note->setTipoMoneda('PEN');
            
            $note->setTipDocAfectado($invoice->tipo_documento);
            $note->setNumDocfectado($invoice->full_number);
            $note->setCodMotivo($motivo);
            $note->setDesMotivo($descripcion);
            
            $note->setCompany($this->buildCompany($company));
            
            $cd = $this->getClientData($invoice);
            $greenterClient = new Client();
            $greenterClient->setTipoDoc($cd['tipo_doc']);
            $greenterClient->setNumDoc($cd['num_doc']);
            $greenterClient->setRznSocial($cd['razon_social']);
            if ($cd['direccion']) {
                $clientAddress = new Address();
                $clientAddress->setDireccion($cd['direccion']);
                $greenterClient->setAddress($clientAddress);
            }
            $note->setClient($greenterClient);
            
            $notaTotal = $invoice->total;
            $notaIgv = $invoice->igv;
            $notaSubtotal = $invoice->subtotal;
            
            $note->setMtoOperGravadas($notaSubtotal);
            $note->setMtoIGV($notaIgv);
            $note->setTotalImpuestos($notaIgv);
            $note->setValorVenta($notaSubtotal);
            $note->setSubTotal($notaTotal);
            $note->setMtoImpVenta($notaTotal);
            
            $lines = [];
            foreach ($invoice->items as $item) {
                $line = new SaleDetail();
                $line->setUnidad('NIU');
                $line->setCodProducto($item->codigo ?? '');
                $line->setDescripcion($item->descripcion);
                $line->setCantidad($item->cantidad);
                $rate = $company->getIgvRate();
                $igvPct = $company->getActiveIgvPercent();
                $valorUnitario = round($item->precio_unitario / (1 + $rate), 2);
                $baseIgv = round($valorUnitario * $item->cantidad, 2);
                $igvItem = round($baseIgv * $rate, 2);
                $line->setMtoValorUnitario($valorUnitario);
                $line->setMtoPrecioUnitario($item->precio_unitario);
                $line->setTipAfeIgv('10');
                $line->setMtoBaseIgv($baseIgv);
                $line->setPorcentajeIgv($igvPct);
                $line->setIgv($igvItem);
                $line->setMtoValorVenta($baseIgv);
                $line->setTotalImpuestos($igvItem);
                $lines[] = $line;
            }
            $note->setDetails($lines);
            
            $legend = new Legend();
            $legend->setCode('1000');
            $legend->setValue($invoice->total_letras ?? 'SON ' . number_to_letter($notaTotal) . ' SOLES');
            $note->setLegends([$legend]);
            
            $result = $this->see->send($note);
            
            if ($result->isSuccess()) {
                $serie->incrementNumber();
                
                $noteInvoice = new InvoiceModel();
                $noteInvoice->company_id = $company->id;
                $noteInvoice->customer_id = $invoice->customer_id;
                $noteInvoice->tipo_documento = '08';
                $noteInvoice->serie = $serie->serie;
                $noteInvoice->numero = $nextNumber;
                $noteInvoice->full_number = $serie->serie . '-' . $correlativo;
                $noteInvoice->fecha_emision = date('Y-m-d');
                $noteInvoice->fecha_vencimiento = date('Y-m-d');
                $noteInvoice->moneda = 'PEN';
                $noteInvoice->gravado = $notaSubtotal;
                $noteInvoice->igv = $notaIgv;
                $noteInvoice->total = $notaTotal;
                $noteInvoice->subtotal = $notaSubtotal;
                $noteInvoice->total_letras = $invoice->total_letras ?? 'SON ' . number_to_letter($notaTotal) . ' SOLES';
                $noteInvoice->sunat_estado = 'ACEPTADO';
                $noteInvoice->sunat_code = '0';
                $noteInvoice->sunat_description = 'ACEPTADO';
                $noteInvoice->save();
                
                foreach ($invoice->items as $item) {
                    $noteInvoice->items()->create([
                        'codigo' => $item->codigo,
                        'descripcion' => $item->descripcion,
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => $item->precio_unitario,
                        'precio_venta' => $item->precio_venta,
                        'igv' => $item->igv
                    ]);
                }
                
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Nota de débito generada correctamente',
                    'note_number' => $serie->serie . '-' . $correlativo,
                ];
            } else {
                $error = $result->getError();
                return [
                    'success' => false,
                    'code' => $error->getCode() ?? 'ERROR',
                    'description' => $error->getMessage() ?? 'Error desconocido'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Debit Note Error: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => 'EXCEPTION',
                'description' => $e->getMessage()
            ];
        }
    }
    
    private function sendNoteViaSummary(InvoiceModel $invoice, $company, string $tipoDoc, string $motivo, string $descripcion): array
    {
        // Determine serie for the note
        $prefix = $tipoDoc === '07' ? ($invoice->tipo_documento === '01' ? 'FC' : 'BC') : ($invoice->tipo_documento === '01' ? 'FD' : 'BD');
        $serie = \App\Models\Serie::where('company_id', $company->id)
            ->where('tipo_documento', $tipoDoc)
            ->where('serie', 'like', $prefix . '%')
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$serie) {
            $serie = \App\Models\Serie::create([
                'company_id' => $company->id,
                'tipo_documento' => $tipoDoc,
                'serie' => $prefix . '01',
                'numero_actual' => 0,
                'estado' => 'ACTIVO',
            ]);
        }

        $nextNumber = $serie->getNextNumber();
        $correlativo = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        $fullNumber = $serie->serie . '-' . $correlativo;
        $notaTotal = $invoice->total;
        $notaIgv = $invoice->igv;
        $notaSubtotal = $invoice->subtotal;

        // Create the note invoice locally with PENDIENTE status
        $noteInvoice = new InvoiceModel();
        $noteInvoice->company_id = $company->id;
        $noteInvoice->customer_id = $invoice->customer_id;
        $noteInvoice->tipo_documento = $tipoDoc;
        $noteInvoice->serie = $serie->serie;
        $noteInvoice->numero = $nextNumber;
        $noteInvoice->full_number = $fullNumber;
        $noteInvoice->fecha_emision = date('Y-m-d');
        $noteInvoice->fecha_vencimiento = date('Y-m-d');
        $noteInvoice->moneda = 'PEN';
        $noteInvoice->gravado = $notaSubtotal;
        $noteInvoice->igv = $notaIgv;
        $noteInvoice->total = $notaTotal;
        $noteInvoice->subtotal = $notaSubtotal;
        $noteInvoice->total_letras = $invoice->total_letras ?? 'SON ' . number_to_letter($notaTotal) . ' SOLES';
        $noteInvoice->sunat_estado = 'PENDIENTE';
        $noteInvoice->sunat_code = $tipoDoc === '07' ? 'NC' : 'ND';
        $noteInvoice->sunat_description = 'ENVIADO POR RESUMEN DIARIO';
        $noteInvoice->save();

        $serie->incrementNumber();

        foreach ($invoice->items as $item) {
            $noteInvoice->items()->create([
                'codigo' => $item->codigo,
                'descripcion' => $item->descripcion,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'precio_venta' => $item->precio_venta,
                'igv' => $item->igv,
            ]);
        }

        if ($tipoDoc === '07') {
            \DB::table('invoices')->where('id', $invoice->id)->update(['credit_note_id' => $noteInvoice->id]);
        }

        // Send via Summary
        try {
            $summaryService = new SummaryService();
            $result = $summaryService->sendNoteToSummary($noteInvoice, $invoice, $tipoDoc);

            if ($result['success']) {
                return [
                    'success' => true,
                    'code' => $result['ticket'] ?? '',
                    'description' => $noteInvoice->full_number . ' - ' . $result['description'],
                    'note_number' => $fullNumber,
                ];
            } else {
                // If summary fails, the note stays as PENDIENTE (can be retried)
                return [
                    'success' => true,
                    'code' => 'ENVIADO',
                    'description' => $noteInvoice->full_number . ' pendiente de envío a SUNAT.',
                    'note_number' => $fullNumber,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Summary send error: ' . $e->getMessage());
            return [
                'success' => true,
                'code' => 'ENVIADO',
                'description' => $noteInvoice->full_number . ' pendiente de envío a SUNAT.',
                'note_number' => $fullNumber,
            ];
        }
    }
    
    public function voidInvoice(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company) {
            return [
                'success' => false,
                'code' => 'NO_COMPANY',
                'description' => 'No hay empresa principal configurada'
            ];
        }
        
        if (!$company->certificado_path && !$company->certificate) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado digital configurado'
            ];
        }
        
        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 'CERT_ERROR',
                'description' => $e->getMessage()
            ];
        }
        
        try {
            $voided = new Voided();
            $voided->setCorrelativo($this->voidInvoiceNumber());
            $voided->setCompany($this->buildCompany($company));
            $voided->setFecGeneracion(new \DateTime());
            $voided->setFecComunicacion(new \DateTime());
            
            $detail = new VoidedDetail();
            $detail->setTipoDoc($invoice->tipo_documento);
            $detail->setSerie($invoice->serie);
            $detail->setCorrelativo($invoice->numero);
            $detail->setDesMotivoBaja('ANULACIÓN DEL DOCUMENTO');
            $voided->setDetails([$detail]);
            
            $result = $this->see->send($voided);
            
            if ($result->isSuccess()) {
                $invoice->update([
                    'sunat_estado' => 'ANULADO',
                    'sunat_code' => '0',
                    'sunat_description' => 'BAJA REGISTRADA'
                ]);
                
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Documento dado de baja correctamente',
                    'voided_number' => $voided->getCorrelativo()
                ];
            } else {
                $error = $result->getError();
                return [
                    'success' => false,
                    'code' => $error->getCode() ?? 'ERROR',
                    'description' => $error->getMessage() ?? 'Error desconocido'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Void Error: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => 'EXCEPTION',
                'description' => $e->getMessage()
            ];
        }
    }
    
    public function generatePdf(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        $invoice->load(['company', 'customer', 'items']);

        // Generate SUNAT QR and pass to PDF renderer
        $qrUrl = \App\Services\SunatQrService::generateForInvoice($invoice);
        $styledHtml = $this->buildStyledHtml($invoice, $company, $qrUrl);
        
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);
        
        $pdf->WriteHTML($styledHtml);
        
        return $pdf->Output('', 'S');
    }
    
    public function generateTicketPdf(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        $invoice->load(['company', 'customer', 'items']);

        $qrUrl = \App\Services\SunatQrService::generateForInvoice($invoice);
        $html = $this->buildTicketHtml($invoice, $company, $qrUrl);
        
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200],
            'margin_top' => 2,
            'margin_bottom' => 2,
            'margin_left' => 2,
            'margin_right' => 2,
        ]);
        
        $pdf->WriteHTML($html);
        
        return $pdf->Output('', 'S');
    }
    
    private function buildTicketHtml($invoice, $company, $qrUrl)
    {
        $customer = $invoice->customer;
        $custName = $customer ? $customer->nombre : 'CLIENTES VARIOS';
        $custDocTipo = $customer ? ($customer->documento_tipo == '6' ? 'RUC: ' : 'DNI: ') : 'DNI: ';
        $custDocNum = $customer ? $customer->documento_numero : '88888888';
        $custDireccion = $customer ? ($customer->direccion ?? '') : '';
        $width = 76;
        // Hash block to display below QR
        $hashBlock = '';
        if (!empty($invoice->codigo_hash)) {
            $hashBlock = '<div class="text-center" style="font-family: monospace; font-size: 10px; margin-top:6px;">Hash: '.$invoice->codigo_hash.'</div>';
        }
        // QR image placeholder for the ticket
        $qrImg = '';
        if ($qrUrl) {
            $qrImg = '<div class="text-center" style="margin-top:6px;"><img src="'.$qrUrl.'" style="width: 90px; height: 90px;" alt="SUNAT QR"></div>';
        }
        $hashBlock = '';
        if (!empty($invoice->codigo_hash)) {
            $hashBlock = '<div class="text-center" style="font-family: monospace; font-size: 10px; margin-top:6px;">Hash: '.$invoice->codigo_hash.'</div>';
        }
        
        $style = '
        <style>
            * { box-sizing: border-box; }
            body { 
                font-family: "Courier New", monospace; 
                font-size: 9px; 
                color: #000;
                margin: 0;
                padding: 0;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-left { text-align: left; }
            .bold { font-weight: bold; }
            .border-bottom { border-bottom: 1px dashed #000; }
            .border-top { border-top: 1px dashed #000; }
            .border-double { border-bottom: 2px solid #000; }
            .py-1 { padding-top: 2px; padding-bottom: 2px; }
            .py-2 { padding-top: 4px; padding-bottom: 4px; }
            .px-1 { padding-left: 2px; padding-right: 2px; }
            .mb-1 { margin-bottom: 2px; }
            .mb-2 { margin-bottom: 4px; }
            .mt-1 { margin-top: 2px; }
            .mt-2 { margin-top: 4px; }
            .w-full { width: 100%; }
            .inline-block { display: inline-block; }
        </style>
        ';
        
        $logoHtml = '';
        if ($company->logo && file_exists(storage_path('app/public/' . $company->logo))) {
            $logoBase64 = 'data:image/' . pathinfo($company->logo, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $company->logo)));
            $logoHtml = '<div class="text-center mb-2"><img src="' . $logoBase64 . '" alt="Logo" style="max-height: 110px; max-width: 90px;"></div>';
        }
        
        $header = '
        <div class="text-center py-2">
            ' . $logoHtml . '
            <div class="bold" style="font-size:10px;">' . e($company->nombre_comercial ?? $company->razon_social) . '</div>
            <div>' . e($company->razon_social) . '</div>
            <div class="mb-1">RUC: ' . e($company->ruc) . '</div>
            <div>' . e($company->direccion) . '</div>
        </div>
        ';
        
        $docInfo = '
        <div class="border-double py-2 mb-2">
            <div class="text-center bold" style="font-size:11px;">' . ($invoice->tipo_documento == '01' ? 'FACTURA ELECTRÓNICA' : ($invoice->tipo_documento == 'NV' ? 'NOTA DE VENTA' : 'BOLETA ELECTRÓNICA')) . '</div>
            <div class="text-center bold" style="font-size:12px;">' . e($invoice->full_number) . '</div>
            <div class="text-center">F. Emisión: ' . date('Y-m-d', strtotime($invoice->fecha_emision)) . ' | H. Emisión: ' . ($invoice->hora_emision ? substr($invoice->hora_emision, 0, 8) : '') . '</div>
        </div>
        ';
        
        $client = '
        <div class="mb-1">
            <div class="bold">CLIENTE:</div>
            <div>' . e($custName) . '</div>
            <div>' . e($custDocTipo) . e($custDocNum) . '</div>
            ' . ($custDireccion ? '<div>' . e($custDireccion) . '</div>' : '') . '
        </div>
        ';
        
        $itemsHeader = '
        <div class="border-top border-bottom py-1 mb-1">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50"><b>Cant</b></td>
                    <td width=""><b>Descripción</b></td>
                    <td width="26" class="text-right"><b>Importe</b></td>
                </tr>
            </table>
        </div>
        ';
        
        $itemsBody = '';
        foreach ($invoice->items as $item) {
            $desc = strlen($item->descripcion) > 25 ? substr($item->descripcion, 0, 25) . '...' : $item->descripcion;
            $itemsBody .= '
            <div class="mb-1">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50" valign="top">' . number_format($item->cantidad, 0) . '</td>
                        <td valign="top">' . e($desc) . '</td>
                        <td width="26" class="text-right">' . number_format($item->precio_venta, 2) . '</td>
                    </tr>
                </table>
            </div>
            ';
        }
        
        $totals = '
        <div class="border-top py-1 mt-1">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>SUBTOTAL:</td>
                    <td class="text-right">S/ ' . number_format($invoice->subtotal, 2) . '</td>
                </tr>
                <tr>
                    <td>IGV (' . $company->getActiveIgvPercent() . '%):</td>
                    <td class="text-right">S/ ' . number_format($invoice->igv, 2) . '</td>
                </tr>
                <tr class="bold">
                    <td style="font-size:11px;">TOTAL:</td>
                    <td class="text-right" style="font-size:11px;">S/ ' . number_format($invoice->total, 2) . '</td>
                </tr>
            </table>
        </div>
        ';
        
        $pagoInfo = '';
        if (!empty($invoice->metodo_pago)) {
            $metodo = $invoice->metodo_pago;
            $ref = $invoice->referencia_pago ? ' - ' . e($invoice->referencia_pago) : '';
            $pagoInfo = '
            <div class="border-top py-1 mt-1">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td><b>FORMA PAGO:</b></td>
                        <td class="text-right">' . e($metodo) . $ref . '</td>
                    </tr>
                </table>
            </div>
            ';
        }
        
        $sunatInfo = '';
        $qrImg = '';
        if ($qrUrl) {
            $qrImg = '<div class="text-center" style="margin-top:6px;"><img src="'.$qrUrl.'" style="width: 90px; height: 90px;" alt="SUNAT QR"></div>';
        }
        if ($invoice->sunat_estado == 'ACEPTADO') {
            $sunatInfo = '
            <div class="border-top py-1 mt-2 text-center">
                <div class="bold">✓ ACEPTADO POR SUNAT</div>
                <div style="font-size:8px;">' . ($invoice->sunat_code ?? '0') . ' - ' . e($invoice->sunat_description ?? 'OK') . '</div>
            </div>
            ';
        }
        
        $footer = '
        <div class="border-top py-2 mt-2 text-center" style="font-size:8px;">
            <div>Representación impresa del documento electrónico</div>
            <div>Consultar en www.sunat.gob.pe</div>
            <div class="mt-1">¡Gracias por su preferencia!</div>
        </div>
        ';
        $qrImg = '';
        $hashBlock = '';
        if ($qrUrl) {
            $qrImg = '<div class="text-center" style="margin-top:6px;"><img src="'.$qrUrl.'" style="width: 90px; height: 90px;" alt="SUNAT QR"></div>';
        }
        if (!empty($invoice->codigo_hash)) {
            $hashBlock = '<div class="text-center" style="font-family: monospace; font-size: 10px; margin-top:6px;">Hash: '.$invoice->codigo_hash.'</div>';
        }
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . e($invoice->full_number) . '</title>
            ' . $style . '
        </head>
        <body style="width:' . $width . 'mm;">
            <div style="width:' . $width . 'mm; margin:0 auto;">
                ' . $header . '
                ' . $docInfo . '
                ' . $client . '
                ' . $itemsHeader . '
                ' . $itemsBody . '
                ' . $totals . '
                ' . $pagoInfo . '
                ' . $qrImg . ' ' . $hashBlock . ' ' . $sunatInfo . '
                ' . $footer . '
            </div>
        </body>
        </html>';
    }
    
    private function buildStyledHtml($invoice, $company, $qrUrl = null)
    {
        // QR image placeholder for A4
        $qrImg = '';
        // Hash block below QR
        $hashBlock = '';
        if (!empty($invoice->codigo_hash)) {
            $hashBlock = '<div class="text-center" style="font-family: monospace; font-size: 10px; margin-top:6px;">Hash: '.$invoice->codigo_hash.'</div>';
        }
        if ($qrUrl) {
            $qrImg = '<div class="text-center" style="margin-top:6px;"><img src="'.$qrUrl.'" style="width: 90px; height: 90px;" alt="SUNAT QR"></div>';
        }
        $customer = $invoice->customer;
        $custName = $customer ? $customer->nombre : 'CLIENTES VARIOS';
        $custDocTipo = $customer ? ($customer->documento_tipo == '6' ? 'RUC' : 'DNI') : 'DNI';
        $custDocNum = $customer ? $customer->documento_numero : '88888888';
        $custDireccion = $customer ? ($customer->direccion ?? '') : '';
        
        $style = '
        <style>
            * { box-sizing: border-box; }
            body { 
                font-family: "Helvetica", "Arial", sans-serif; 
                font-size: 10px; 
                color: #333;
                margin: 0;
                padding: 10px;
            }
            .header-table { 
                width: 100%; 
                margin-bottom: 15px;
                border-bottom: 2px solid #0066cc;
                padding-bottom: 10px;
                border-collapse: collapse;
            }
            .header-table table { border-collapse: collapse; border: none; }
            .header-table td { vertical-align: middle; padding: 5px; border: none !important; }
            .logo-block { width: 25%; text-align: left; vertical-align: middle; border: none !important; }
            .company-block { width: 45%; text-align: center; vertical-align: middle; border: none !important; }
            .invoice-block { width: 30%; text-align: center; vertical-align: middle; border: none !important; }
            
            .company-logo { max-height: 70px; max-width: 100px; }
            .company-name { font-size: 14px; font-weight: bold; margin-bottom: 3px; text-align: center; }
            .company-details { font-size: 10px; color: #666; line-height: 1.3; text-align: center; }
            .doc-box div { 
                border: 0 !important; 
                border-width: 0 !important;
            }
            
            .client-section {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 4px;
            }
            .client-title { 
                font-weight: bold; 
                color: #0066cc; 
                margin-bottom: 5px;
                font-size: 9px;
                text-transform: uppercase;
            }
            .client-info { font-size: 10px; }
            .client-info strong { display: inline-block; width: 60px; }
            
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 15px;
                font-size: 9px;
            }
            th { 
                background: #0066cc;
                color: white;
                padding: 8px 5px;
                text-align: left;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 8px;
            }
            th:nth-child(3), th:nth-child(4), th:nth-child(5) { text-align: right; }
            td { 
                border-bottom: 1px solid #eee; 
                padding: 6px 5px;
                vertical-align: top;
            }
            td:nth-child(3), td:nth-child(4), td:nth-child(5) { text-align: right; }
            
            .totals-section {
                margin-top: 15px;
                text-align: right;
            }
            .totals-table {
                display: inline-table;
                width: 200px;
                border-collapse: collapse;
                text-align: right;
                margin-left: auto;
            }
            .totals-table td {
                padding: 5px 8px;
                text-align: right;
            }
            .totals-table .label { color: #666; text-align: left; }
            .totals-table .value { font-weight: bold; text-align: right; }
            .totals-table .total-row {
                background: #0066cc;
                color: white;
                font-size: 12px;
            }
            
            .pago-section {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                text-align: right;
            }
            .pago-section .totals-table {
                display: inline-table;
                width: 200px;
            }
            .pago-section .label { text-align: left; }
            .pago-section .value { text-align: right; }
            
            .footer {
                margin-top: 20px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                font-size: 8px;
                color: #999;
                text-align: center;
            }
            .footer-final {
                margin-top: 20px;
                text-align: center;
            }
            .footer-final .footer {
                margin-top: 15px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                font-size: 8px;
                color: #999;
                text-align: center;
            }
            
            .sunat-stamp {
                margin-top: 10px;
                padding: 8px;
                background: #e8f5e9;
                border: 1px solid #4caf50;
                border-radius: 4px;
                text-align: center;
                font-size: 9px;
                color: #2e7d32;
            }
        </style>
        ';
        
        $logoHtml = '';
        if ($company->logo && file_exists(storage_path('app/public/' . $company->logo))) {
            $logoBase64 = 'data:image/' . pathinfo($company->logo, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $company->logo)));
            $logoHtml = '<img src="' . $logoBase64 . '" alt="Logo" style="max-height: 70px; max-width: 100px;">';
        }
        
        $invoiceTypeLabel = $invoice->tipo_documento == '01' ? 'FACTURA ELECTRÓNICA' : ($invoice->tipo_documento == 'NV' ? 'NOTA DE VENTA' : ($invoice->tipo_documento == '03' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'DOCUMENTO'));
        
        $horaEmision = !empty($invoice->hora_emision) ? substr($invoice->hora_emision, 0, 8) : '';
        
        $header = '
        <table class="header-table">
            <tr>
                <td class="logo-block">
                    ' . ($logoHtml ? $logoHtml : '<div style="height:70px;"></div>') . '
                </td>
<td class="company-block">
                    <div class="company-name">' . e($company->razon_social) . '</div>
                    <div class="company-details">
                        RUC: ' . e($company->ruc) . '<br>
                        ' . e($company->direccion) . '<br>
                        ' . ($company->telefono ? 'Tel: ' . e($company->telefono) : '') . '
                    </div>
                </td>
                <td class="invoice-block">
                    <table style="border:2px solid #003399; padding:15px; min-height:100px; text-align:center; width:100%;" class="doc-box">
                        <tr><td style="font-size:11px; font-weight:bold; border:none;">R.U.C.: ' . e($company->ruc) . '</td></tr>
                        <tr><td style="font-size:11px; font-weight:bold; text-transform:uppercase; border:none;">' . $invoiceTypeLabel . '</td></tr>
                        <tr><td style="font-size:15px; font-weight:bold; border:none;">' . e($invoice->full_number) . '</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        ';
        
        $clientSection = '
        <div class="client-section">
            <div class="client-title">Datos del Cliente</div>
            <div class="client-info">
                <strong>Razón Social:</strong> ' . e($custName) . '<br>
                <strong>' . e($custDocTipo) . ':</strong> ' . e($custDocNum) . '<br>
                ' . ($custDireccion ? '<strong>Dirección:</strong> ' . e($custDireccion) . '<br>' : '') . '
            </div>
            <div class="mt-2" style="font-size: 10px; color: #666;">
                <strong>F. Emisión:</strong> ' . date('Y-m-d', strtotime($invoice->fecha_emision)) . ' | <strong>H. Emisión:</strong> ' . $horaEmision . '
            </div>
        </div>
        ';
        
        $itemsTable = '
        <table>
            <thead>
                <tr>
                    <th style="width:10%">Código</th>
                    <th style="width:40%">Descripción</th>
                    <th style="width:15%">Cantidad</th>
                    <th style="width:15%">P. Unitario</th>
                    <th style="width:20%">Importe</th>
                </tr>
            </thead>
            <tbody>
        ';
        
        foreach ($invoice->items as $item) {
            $itemsTable .= '
                <tr>
                    <td>' . e($item->codigo ?? '-') . '</td>
                    <td>' . e($item->descripcion) . '</td>
                    <td>' . number_format($item->cantidad, 2) . '</td>
                    <td>S/ ' . number_format($item->precio_unitario, 2) . '</td>
                    <td>S/ ' . number_format($item->precio_venta, 2) . '</td>
                </tr>
            ';
        }
        
        $itemsTable .= '</tbody></table>';
        
        $totals = '
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">S/ ' . number_format($invoice->subtotal, 2) . '</td>
                </tr>
                <tr>
                    <td class="label">IGV (' . $company->getActiveIgvPercent() . '%):</td>
                    <td class="value">S/ ' . number_format($invoice->igv, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total:</strong></td>
                    <td><strong>S/ ' . number_format($invoice->total, 2) . '</strong></td>
                </tr>
            </table>
        </div>
        ';
        
        $pagoInfo = '';
        if (!empty($invoice->metodo_pago)) {
            $metodo = $invoice->metodo_pago;
            $ref = $invoice->referencia_pago ? ' - ' . e($invoice->referencia_pago) : '';
            $pagoInfo = '
            <div class="pago-section">
                <table class="totals-table">
                    <tr>
                        <td class="label"><strong>Forma de Pago:</strong></td>
                        <td class="value">' . e($metodo) . $ref . '</td>
                    </tr>
                </table>
            </div>
            ';
        }
        
        $sunatInfo = '';
        if ($invoice->sunat_estado == 'ACEPTADO') {
            $sunatInfo = '
            <div class="sunat-stamp">
                <strong>✓ ACEPTADO POR SUNAT</strong><br>
                Código: ' . e($invoice->sunat_code ?? '0') . ' | ' . e($invoice->sunat_description ?? 'Aceptado') . '
            </div>
            ';
        }
        
        $footer = '
        <div class="footer">
            Documento electrónico emitido en cumplimiento de la Resolución de SUNAT N° 097-2012/SUNAT<br>
            Representación impresa del documento electrónico - Consultar en www.sunat.gob.pe
        </div>
        ';
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . e($invoice->full_number) . '</title>
            ' . $style . '
        </head>
        <body>
            ' . $header . '
            ' . $clientSection . '
            ' . $itemsTable . '
            ' . $totals . '
            ' . $pagoInfo . '
            ' . $sunatInfo . '
            <div class="footer-final">
                ' . $qrImg . '
                ' . $hashBlock . '
                <div class="footer">
                    Documento electrónico emitido en cumplimiento de la Resolución de SUNAT N° 097-2012/SUNAT<br>
                    Representación impresa del documento electrónico - Consultar en www.sunat.gob.pe
                </div>
            </div>
        </body>
        </html>';
    }
    
    public function sendInvoice(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company) {
            return [
                'success' => false,
                'code' => 'NO_COMPANY',
                'description' => 'No hay empresa principal configurada'
            ];
        }
        
        if (!$company->certificado_path && !$company->certificate) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado digital configurado. Suba el archivo .p12 desde la configuración de la empresa.'
            ];
        }
        
        $this->company = $company;
        
        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 'CERT_ERROR',
                'description' => $e->getMessage()
            ];
        }
        
        $greenterCompany = $this->buildCompany($company);
        
        $greenterInvoice = $this->buildInvoice($invoice, $company);
        
        try {
            $result = $this->see->send($greenterInvoice);
            
        if ($result->isSuccess()) {
            $xmlContent = $this->see->getFactory()->getLastXml();
            // Extract DigestValue from XML (SUNAT digest) and store as hash for PDF display
            $digestValue = $this->extractDigestValueFromXml($xmlContent);
            if ($digestValue) {
                $invoice->codigo_hash = $digestValue;
                $invoice->save();
            }
                $cdrZip = $result->getCdrZip();
                
                $cdrFileName = 'R-' . $company->ruc . '-' . $invoice->tipo_documento . '-' . $invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT) . '.zip';
                $cdrPath = 'sunat/' . $cdrFileName;
                
                \Storage::put($cdrPath, $cdrZip);
                
                $invoice->update([
                    'sunat_code' => '0',
                    'sunat_description' => 'ACEPTADO',
                    'sunat_estado' => 'ACEPTADO',
                    'sunat_response' => json_encode($result->getCdrResponse()),
                    'xml_firmado' => $xmlContent,
                    'cdr_path' => $cdrPath
                ]);
                
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Documento enviado correctamente'
                ];
            } else {
                $error = $result->getError();
                $invoice->update([
                    'sunat_code' => $error->getCode() ?? 'ERROR',
                    'sunat_description' => $error->getMessage() ?? 'Error desconocido',
                    'sunat_estado' => 'RECHAZADO'
                ]);
                
                return [
                    'success' => false,
                    'code' => $error->getCode() ?? 'ERROR',
                    'description' => $error->getMessage() ?? 'Error desconocido'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Greenter Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'code' => 'EXCEPTION',
                'description' => $e->getMessage()
            ];
        }
    }
    
    private function setupSee(Company $company)
    {
        $certField = $company->certificado_path ?? $company->certificate;
        if (!$certField) {
            throw new \Exception('No hay certificado digital configurado. Suba el archivo .p12 desde la configuración de la empresa.');
        }

        $sunatUser = $company->soap_username ?? $company->ruc;
        $sunatPassword = $company->soap_password ?? $company->certificado_password ?? '';

        // Try to use PEM file directly (no password needed, OpenSSL 3.0 compatible)
        $pemPath = storage_path('app/certificates/' . $company->ruc . '_certificate.pem');
        if (file_exists($pemPath)) {
            $pemContent = file_get_contents($pemPath);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        } else {
            $pfxPath = storage_path('app/certificates/' . $certField);
            if (!file_exists($pfxPath)) {
                $pfxPath = storage_path('app/' . $certField);
            }
            if (!file_exists($pfxPath)) {
                throw new \Exception('El archivo del certificado no existe en: ' . $pfxPath);
            }

            $password = $company->certificado_password;
            if (!$password) {
                throw new \Exception('No se ha configurado la contraseña del certificado.');
            }

            $pfxContent = file_get_contents($pfxPath);
            $certificate = new X509Certificate($pfxContent, $password);
            $pemContent = $certificate->export(X509ContentType::PEM);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        }
        $this->see->setClaveSOL($company->ruc, $sunatUser, $sunatPassword);

        if ($company->soap_type_id == 2) {
            $this->see->setService(SunatEndpoints::FE_PRODUCCION);
        } else {
            $this->see->setService(SunatEndpoints::FE_BETA);
        }
    }
    
    private function buildCompany(Company $company)
    {
        $greenterCompany = new GreenterCompany();
        $greenterCompany->setRuc($company->ruc);
        $greenterCompany->setRazonSocial($company->razon_social);
        $greenterCompany->setNombreComercial($company->nombre_comercial ?? $company->razon_social);
        
        $address = new Address();
        $address->setUbigueo($company->ubigeo ?? '150101');
        $address->setDepartamento($company->departamento ?? 'LIMA');
        $address->setProvincia($company->provincia ?? 'LIMA');
        $address->setDistrito($company->distrito ?? 'LIMA');
        $address->setUrbanizacion('-');
        $address->setDireccion($company->direccion);
        $address->setCodLocal('0000');
        $greenterCompany->setAddress($address);
        
        return $greenterCompany;
    }

    private function getClientData($invoice): array
    {
        $client = $invoice->customer ?? $invoice->customer;
        if ($client) {
            return [
                'tipo_doc' => $client->documento_tipo == '6' ? '6' : '1',
                'num_doc' => $client->documento_numero ?? '',
                'razon_social' => $client->nombre ?? 'CLIENTES VARIOS',
                'direccion' => $client->direccion ?? '',
            ];
        }
        return [
            'tipo_doc' => '1',
            'num_doc' => '88888888',
            'razon_social' => 'CLIENTES VARIOS',
            'direccion' => '',
        ];
    }

    private function buildInvoice(InvoiceModel $invoice, Company $company)
    {
        $greenter = new Invoice();
        
        $greenter->setUblVersion('2.1');
        $greenter->setTipoOperacion('0101');
        $greenter->setTipoDoc($invoice->tipo_documento == '01' ? '01' : '03');
        $greenter->setSerie($invoice->serie);
        $greenter->setCorrelativo($invoice->numero);
        $greenter->setFechaEmision(new \DateTime($invoice->fecha_emision));
        $greenter->setFecVencimiento(new \DateTime($invoice->fecha_vencimiento ?? $invoice->fecha_emision));
        $greenter->setFormaPago(new FormaPagoContado());
        $greenter->setTipoMoneda('PEN');
        
        $greenter->setCompany($this->buildCompany($company));
        
        $cd = $this->getClientData($invoice);
        $greenterClient = new Client();
        $greenterClient->setTipoDoc($cd['tipo_doc']);
        $greenterClient->setNumDoc($cd['num_doc']);
        $greenterClient->setRznSocial($cd['razon_social']);
        if ($cd['direccion']) {
            $clientAddress = new Address();
            $clientAddress->setDireccion($cd['direccion']);
            $greenterClient->setAddress($clientAddress);
        }
        $greenter->setClient($greenterClient);
        
        $legend = new Legend();
        $legend->setCode('1000');
        $legend->setValue($invoice->total_letras ?? 'SON ' . number_to_letter($invoice->total) . ' SOLES');
        $greenter->setLegends([$legend]);
        
        $lines = [];
        $totalBaseIgv = 0;
        $totalIgv = 0;
        $totalValorVenta = 0;
        
        foreach ($invoice->items as $idx => $item) {
            $line = new SaleDetail();
            
            $rate = $company->getIgvRate();
            $igvPct = $company->getActiveIgvPercent();
            $valorUnitario = round($item->precio_unitario / (1 + $rate), 2);
            $baseIgv = round($valorUnitario * $item->cantidad, 2);
            $igvItem = round($baseIgv * $rate, 2);
            
            $line->setUnidad('NIU');
            $line->setCodProducto($item->codigo ?? '');
            $line->setDescripcion($item->descripcion);
            $line->setCantidad($item->cantidad);
            $line->setMtoValorUnitario($valorUnitario);
            $line->setMtoPrecioUnitario($item->precio_unitario);
            $line->setTipAfeIgv('10');
            $line->setMtoBaseIgv($baseIgv);
            $line->setPorcentajeIgv($igvPct);
            $line->setIgv($igvItem);
            $line->setMtoValorVenta($baseIgv);
            $line->setTotalImpuestos($igvItem);
            
            $totalBaseIgv += $baseIgv;
            $totalIgv += $igvItem;
            $totalValorVenta += $baseIgv;
            
            $lines[] = $line;
        }
        $greenter->setDetails($lines);
        
        $greenter->setMtoOperGravadas(round($totalBaseIgv, 2));
        $greenter->setMtoIGV(round($totalIgv, 2));
        $greenter->setTotalImpuestos(round($totalIgv, 2));
        $greenter->setValorVenta(round($totalBaseIgv, 2));
        $greenter->setSubTotal(round($totalBaseIgv + $totalIgv, 2));
        $greenter->setMtoImpVenta(round($totalBaseIgv + $totalIgv, 2));
        
        return $greenter;
    }

    /**
     * Extracts the ds:DigestValue from the Signed XML returned by SUNAT.
     * Falls back to null if not found.
     */
    private function extractDigestValueFromXml($xmlContent)
    {
        if (!$xmlContent) {
            return null;
        }
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadXML($xmlContent)) {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            return null;
        }
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        // Match any DigestValue node regardless of namespace prefix
        $nodes = $xpath->query("//*[local-name()='DigestValue']");
        if ($nodes->length > 0) {
            return (string)$nodes->item(0)->nodeValue;
        }
        return null;
    }
}

function number_to_letter($number) {
    $formatter = new \NumberFormatter('es-ES', \NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}
