<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice as InvoiceModel;
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
        
        if (!$company || !$company->certificado_path) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado configurado'
            ];
        }
        
        $this->setupSee($company);
        
        try {
            $note = new Note();
            
            $note->setUblVersion('2.1');
            $note->setTipoDoc('07');
            $note->setSerie($invoice->serie);
            $note->setCorrelativo($this->generateNoteNumber());
            $note->setFechaEmision(new \DateTime());
            $note->setTipoMoneda('PEN');
            
            $note->setTipDocAfectado($invoice->tipo_documento);
            $note->setNumDocfectado($invoice->full_number);
            $note->setCodMotivo($motivo);
            $note->setDesMotivo($descripcion);
            
            $note->setCompany($this->buildCompany($company));
            
            $client = $invoice->customer;
            $greenterClient = new Client();
            $greenterClient->setTipoDoc($client->documento_tipo == '6' ? '6' : '1');
            $greenterClient->setNumDoc($client->documento_numero);
            $greenterClient->setRznSocial($client->nombre);
            if ($client->direccion) {
                $clientAddress = new Address();
                $clientAddress->setDireccion($client->direccion);
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
                $valorUnitario = round($item->precio_unitario / 1.18, 2);
                $baseIgv = round($valorUnitario * $item->cantidad, 2);
                $igvItem = round($baseIgv * 0.18, 2);
                $line->setMtoValorUnitario($valorUnitario);
                $line->setMtoPrecioUnitario($item->precio_unitario);
                $line->setTipAfeIgv('10');
                $line->setMtoBaseIgv($baseIgv);
                $line->setPorcentajeIgv(18);
                $line->setIgv($igvItem);
                $line->setMtoValorVenta($baseIgv);
                $line->setTotalImpuestos($igvItem);
                $lines[] = $line;
            }
            $note->setDetails($lines);
            
            $legend = new Legend();
            $legend->setCode('1000');
            $legend->setValue('DESCUENTO POR ' . strtoupper($descripcion));
            $note->setLegends([$legend]);
            
            $result = $this->see->send($note);
            
            if ($result->isSuccess()) {
                $noteInvoice = new InvoiceModel();
                $noteInvoice->company_id = $company->id;
                $noteInvoice->customer_id = $invoice->customer_id;
                $noteInvoice->tipo_documento = '07';
                $noteInvoice->serie = $invoice->serie;
                $noteInvoice->numero = $this->getLastNoteNumber();
                $noteInvoice->fecha_emision = date('Y-m-d');
                $noteInvoice->fecha_vencimiento = date('Y-m-d');
                $noteInvoice->moneda = 'PEN';
                $noteInvoice->gravado = $notaSubtotal;
                $noteInvoice->igv = $notaIgv;
                $noteInvoice->total = $notaTotal;
                $noteInvoice->subtotal = $notaSubtotal;
                $noteInvoice->total_letras = 'SON ' . number_to_letter($notaTotal) . ' SOLES';
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
                    'note_number' => $note->getCorrelativo()
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
    
    private function generateNoteNumber()
    {
        $num = $this->getLastNoteNumber();
        return str_pad($num, 8, '0', STR_PAD_LEFT);
    }
    
    private function getLastNoteNumber()
    {
        $last = InvoiceModel::where('tipo_documento', '07')
            ->where('serie', 'like', 'F%')
            ->max('numero');
        return ($last ?? 0) + 1;
    }
    
    public function voidInvoice(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company || !$company->certificado_path) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado configurado'
            ];
        }
        
        $this->setupSee($company);
        
        try {
            $voided = new Voided();
            $voided->setCorrelativo($this->generateVoidedNumber());
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
    
    private function generateVoidedNumber()
    {
        return str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
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
        
        $header = '
        <div class="text-center py-2">
            <div class="bold" style="font-size:10px;">' . e($company->nombre_comercial ?? $company->razon_social) . '</div>
            <div>' . e($company->razon_social) . '</div>
            <div class="mb-1">RUC: ' . e($company->ruc) . '</div>
            <div>' . e($company->direccion) . '</div>
        </div>
        ';
        
        $docInfo = '
        <div class="border-double py-2 mb-2">
            <div class="text-center bold" style="font-size:11px;">' . ($invoice->tipo_documento == '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA ELECTRÓNICA') . '</div>
            <div class="text-center bold" style="font-size:12px;">' . e($invoice->full_number) . '</div>
            <div class="text-center">Fecha: ' . date('d/m/Y', strtotime($invoice->fecha_emision)) . '</div>
        </div>
        ';
        
        $client = '
        <div class="mb-1">
            <div class="bold">CLIENTE:</div>
            <div>' . e($customer->nombre) . '</div>
            <div>' . ($customer->documento_tipo == '6' ? 'RUC: ' : 'DNI: ') . e($customer->documento_numero) . '</div>
            ' . ($customer->direccion ? '<div>' . e($customer->direccion) . '</div>' : '') . '
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
                    <td>IGV (18%):</td>
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
            .header { 
                display: flex; 
                justify-content: space-between; 
                margin-bottom: 15px;
                border-bottom: 2px solid #0066cc;
                padding-bottom: 10px;
            }
            .company-info { width: 55%; }
            .company-logo { 
                font-size: 18px; 
                font-weight: bold; 
                color: #0066cc; 
                margin-bottom: 5px;
            }
            .company-name { font-size: 14px; font-weight: bold; }
            .company-details { font-size: 9px; color: #666; margin-top: 5px; }
            
            .invoice-info { 
                width: 40%; 
                text-align: right;
                background: #f5f5f5;
                padding: 10px;
                border-radius: 4px;
            }
            .invoice-type { 
                font-size: 16px; 
                font-weight: bold; 
                color: #0066cc;
                text-transform: uppercase;
            }
            .invoice-number { 
                font-size: 12px; 
                font-weight: bold;
                margin-top: 5px;
            }
            .invoice-date { font-size: 9px; color: #666; }
            
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
                display: flex;
                justify-content: flex-end;
            }
            .totals-table {
                width: 200px;
                border-collapse: collapse;
            }
            .totals-table td {
                padding: 5px 8px;
                text-align: right;
            }
            .totals-table .label { color: #666; }
            .totals-table .value { font-weight: bold; }
            .totals-table .total-row {
                background: #0066cc;
                color: white;
                font-size: 12px;
            }
            
            .pago-section {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
            }
            .pago-section .totals-table {
                width: 200px;
            }
            
            .footer {
                margin-top: 20px;
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
        
        $header = '
        <div class="header">
            <div class="company-info">
                <div class="company-logo">' . e($company->nombre_comercial ?? $company->razon_social) . '</div>
                <div class="company-name">' . e($company->razon_social) . '</div>
                <div class="company-details">
                    RUC: ' . e($company->ruc) . '<br>
                    ' . e($company->direccion) . '<br>
                    ' . ($company->telefono ? 'Tel: ' . e($company->telefono) : '') . '
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-type">' . ($invoice->tipo_documento == '01' ? 'FACTURA ELECTRÓNICA' : ($invoice->tipo_documento == 'NV' ? 'NOTA DE VENTA' : 'BOLETA DE VENTA ELECTRÓNICA')) . '</div>
                <div class="invoice-number">' . e($invoice->full_number) . '</div>
                <div class="invoice-date">Fecha: ' . date('d/m/Y', strtotime($invoice->fecha_emision)) . '</div>
            </div>
        </div>
        ';
        
        $clientSection = '
        <div class="client-section">
            <div class="client-title">Datos del Cliente</div>
            <div class="client-info">
                <strong>Razón Social:</strong> ' . e($customer->nombre) . '<br>
                <strong>' . ($customer->documento_tipo == '6' ? 'RUC' : 'DNI') . ':</strong> ' . e($customer->documento_numero) . '<br>
                ' . ($customer->direccion ? '<strong>Dirección:</strong> ' . e($customer->direccion) . '<br>' : '') . '
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
                    <td class="label">IGV (18%):</td>
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
            ' . $footer . ' ' . $qrImg . ' ' . $hashBlock . '
        </body>
        </html>';
    }
    
    public function sendInvoice(InvoiceModel $invoice)
    {
        $company = \App\Models\Company::getMainCompany();
        
        if (!$company || !$company->certificado_path) {
            return [
                'success' => false,
                'code' => 'NO_CERT',
                'description' => 'No hay certificado configurado'
            ];
        }
        
        $this->company = $company;
        
        $greenterCompany = $this->buildCompany($company);
        
        $greenterInvoice = $this->buildInvoice($invoice, $company);
        
        $this->setupSee($company);
        
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
        $pfxPath = storage_path('app/' . $company->certificado_path);
        $password = $company->certificado_password;
        
        $pfxContent = file_get_contents($pfxPath);
        
        $certificate = new X509Certificate($pfxContent, $password);
        $pemContent = $certificate->export(X509ContentType::PEM);
        
        $sunatUser = $company->ruc;
        $sunatPassword = $password;
        
        $this->see = new \Greenter\See();
        $this->see->setCertificate($pemContent);
        $this->see->setClaveSOL($company->ruc, $sunatUser, $sunatPassword);
        $this->see->setService(SunatEndpoints::FE_BETA);
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
        
        $client = $invoice->customer;
        $greenterClient = new Client();
        $greenterClient->setTipoDoc($client->documento_tipo == '6' ? '6' : '1');
        $greenterClient->setNumDoc($client->documento_numero);
        $greenterClient->setRznSocial($client->nombre);
        if ($client->direccion) {
            $clientAddress = new Address();
            $clientAddress->setDireccion($client->direccion);
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
            
            $valorUnitario = round($item->precio_unitario / 1.18, 2);
            $baseIgv = round($valorUnitario * $item->cantidad, 2);
            $igvItem = round($baseIgv * 0.18, 2);
            
            $line->setUnidad('NIU');
            $line->setCodProducto($item->codigo ?? '');
            $line->setDescripcion($item->descripcion);
            $line->setCantidad($item->cantidad);
            $line->setMtoValorUnitario($valorUnitario);
            $line->setMtoPrecioUnitario($item->precio_unitario);
            $line->setTipAfeIgv('10');
            $line->setMtoBaseIgv($baseIgv);
            $line->setPorcentajeIgv(18);
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
