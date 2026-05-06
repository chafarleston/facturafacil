<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Template;
use App\Services\XmlSignerService;
use Illuminate\Support\Facades\Storage;
use NumberFormatter;

class SunatService
{
    public function sendInvoice(Invoice $invoice)
    {
        \Log::info('SunatService: Starting sendInvoice', ['invoice' => $invoice->id, 'full_number' => $invoice->full_number]);
        
        $company = $invoice->company;
        
        \Log::info('SunatService: Generating XML');
        $xml = $this->generateXml($invoice, $company);
        \Log::info('SunatService: XML generated, length: ' . strlen($xml));
        
        \Log::info('SunatService: Signing XML');
        $company = \App\Models\Company::getMainCompany();
        \Log::info('SunatService: Using company for signing', ['company' => $company ? $company->id : null, 'ruc' => $company ? $company->ruc : null, 'cert_path' => $company ? $company->certificado_path : null]);
        $signer = new XmlSignerService($company);
        try {
            $xmlSigned = $signer->signXml($xml, $company->ruc);
            \Log::info('SunatService: XML signed, length: ' . strlen($xmlSigned));
        
        $debugPath = storage_path('app/sunat/signed_' . str_replace('-', '_', $invoice->full_number) . '.xml');
        @file_put_contents($debugPath, $xmlSigned);
        } catch (\Exception $e) {
            \Log::error('SunatService: XML signing failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => 'SIGN_ERROR',
                'description' => 'Error al firmar XML: ' . $e->getMessage()
            ];
        }
        
        \Log::info('SunatService: Sending to SUNAT');
        $response = $this->sendToSunatBeta($invoice, $xmlSigned);
        
        \Log::info('SunatService: Response received', $response);
        
        // Store the result
        $invoice->update([
            'sunat_code' => substr($response['code'] ?? '', 0, 100),
            'sunat_description' => substr($response['description'] ?? '', 0, 500),
            'xml_firmado' => $xmlSigned,
            'sunat_estado' => $response['success'] ? 'ACEPTADO' : 'RECHAZADO',
            'cdr_path' => $response['cdr_path'] ?? null
        ]);
        
        return $response;
    }
    
    private function generateXml(Invoice $invoice, Company $company)
    {
        $tipoDoc = $invoice->tipo_documento;
        $docType = $tipoDoc === '01' ? 'Invoice' : 'Invoice';
        $tipoDocSunat = $tipoDoc === '01' ? '01' : '03';
        
        $ruc = $company->ruc;
        $serie = $invoice->serie;
        $numero = str_pad($invoice->numero, 8, '0', STR_PAD_LEFT);
        
        $customer = $invoice->customer;
        $docTipoSunat = $customer->documento_tipo == '6' ? '6' : '1';
        
        $subtotal = $invoice->subtotal;
        $igv = $invoice->igv;
        $total = $invoice->total;
        
        // Convertir fecha a objeto Carbon si es string
        $fechaEmision = $invoice->fecha_emision instanceof \Carbon\Carbon 
            ? $invoice->fecha_emision 
            : \Carbon\Carbon::parse($invoice->fecha_emision);
        
        $itemsXml = '';
        foreach ($invoice->items as $idx => $item) {
            $qty = $item->cantidad;
            $price = $item->precio_unitario;
            $totalItem = $item->precio_venta;
            $igvItem = $item->igv;
            $baseItem = $totalItem - $igvItem;
            
            $itemsXml .= "
            <cac:InvoiceLine>
                <cbc:ID>" . ($idx + 1) . "</cbc:ID>
                <cbc:InvoicedQuantity unitCode=\"NIU\">" . number_format($qty, 2, '.', '') . "</cbc:InvoicedQuantity>
                <cbc:LineExtensionAmount currencyID=\"PEN\">" . number_format($baseItem, 2, '.', '') . "</cbc:LineExtensionAmount>
                <cac:PricingReference>
                    <cac:AlternativeConditionPrice>
                        <cbc:PriceAmount currencyID=\"PEN\">" . number_format($price, 2, '.', '') . "</cbc:PriceAmount>
                        <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                    </cac:AlternativeConditionPrice>
                </cac:PricingReference>
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID=\"PEN\">" . number_format($igvItem, 2, '.', '') . "</cbc:TaxAmount>
                    <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID=\"PEN\">" . number_format($baseItem, 2, '.', '') . "</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID=\"PEN\">" . number_format($igvItem, 2, '.', '') . "</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cbc:Percent>18</cbc:Percent>
                            <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
                            <cac:TaxScheme>
                                <cbc:ID>1000</cbc:ID>
                                <cbc:Name>IGV</cbc:Name>
                                <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>
                <cac:Item>
                    <cbc:Description><![CDATA[{$item->descripcion}]]></cbc:Description>
                    <cac:SellersItemIdentification>
                        <cbc:ID>{$item->codigo}</cbc:ID>
                    </cac:SellersItemIdentification>
                </cac:Item>
                <cac:Price>
                    <cbc:PriceAmount currencyID=\"PEN\">" . number_format($price, 2, '.', '') . "</cbc:PriceAmount>
                </cac:Price>
            </cac:InvoiceLine>";
        }
        
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<' . $docType . ' xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>' . $serie . '-' . $numero . '</cbc:ID>
    <cbc:IssueDate>' . $fechaEmision->format('Y-m-d') . '</cbc:IssueDate>
    <cbc:IssueTime>' . $fechaEmision->format('H:i:s') . '</cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101">' . $tipoDocSunat . '</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID>signature' . $ruc . '</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>' . $ruc . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . ($company->nombre_comercial ?? $company->razon_social) . ']]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#signature' . $ruc . '</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . ($company->nombre_comercial ?? $company->razon_social) . ']]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[' . $company->razon_social . ']]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:AddressLine>
                        <cbc:Line><![CDATA[' . ($company->direccion ?? '') . ']]></cbc:Line>
                    </cbc:AddressLine>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $docTipoSunat . '">' . $customer->documento_numero . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[' . $customer->nombre . ']]></cbc:RegistrationName>
                ' . ($customer->direccion ? '<cac:RegistrationAddress><cbc:AddressLine><cbc:Line><![CDATA[' . $customer->direccion . ']]></cbc:Line></cbc:AddressLine></cac:RegistrationAddress>' : '') . '
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="PEN">' . number_format($igv, 2, '.', '') . '</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="PEN">' . number_format($subtotal, 2, '.', '') . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . number_format($igv, 2, '.', '') . '</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="PEN">' . number_format($subtotal, 2, '.', '') . '</cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="PEN">' . number_format($total, 2, '.', '') . '</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="PEN">' . number_format($total, 2, '.', '') . '</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    ' . $itemsXml . '
</' . $docType . '>';
        
        return $xml;
    }
    
    private function sendToSunatBeta(Invoice $invoice, $xml)
    {
        $company = $invoice->company;
        $ruc = $company->ruc;
        $tipoDoc = $invoice->tipo_documento;
        $serie = $invoice->serie;
        $numero = str_pad($invoice->numero, 8, '0', STR_PAD_LEFT);
        
        // SUNAT format: RUC-TIPO-SERIE-NUMERO (required for production, but let's try first for beta)
        $filename = $ruc . '-' . $tipoDoc . '-' . $serie . '-' . $numero;
        
        $zipFile = storage_path('app/sunat/' . $filename . '.zip');
        $cdrFile = storage_path('app/sunat/' . $filename . '_cdr.zip');
        
        if (!file_exists(dirname($zipFile))) {
            mkdir(dirname($zipFile), 0755, true);
        }
        
        $xmlFile = tempnam(sys_get_temp_dir(), 'sunat_');
        file_put_contents($xmlFile, $xml);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($xmlFile, $filename . '.xml');
            $zip->close();
        }
        unlink($xmlFile);
        
        try {
            $ch = curl_init('https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/xml',
                'Authorization: Basic ' . base64_encode('20000000000MODDATOS:moddatos')
            ]);
            
            $postData = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe">
                <soapenv:Header/>
                <soapenv:Body>
                    <ser:sendBill>
                        <fileName>' . $filename . '.zip</fileName>
                        <contentFile>' . base64_encode(file_get_contents($zipFile)) . '</contentFile>
                    </ser:sendBill>
                </soapenv:Body>
            </soapenv:Envelope>';
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            \Log::info('SUNAT response raw (first 1000 chars):', ['response' => substr($response, 0, 1000)]);
            
            // Log full response for debugging
            \Log::info('SUNAT full response:', ['response' => $response]);
            
            // Check for SOAP Fault first
            if (strpos($response, 'soap-env:Fault') !== false || strpos($response, 'Fault') !== false) {
                $faultCode = '';
                $faultString = '';
                
                if (preg_match('/<faultcode>(.*?)<\/faultcode>/', $response, $matches)) {
                    $faultCode = $matches[1];
                }
                if (preg_match('/<faultstring>(.*?)<\/faultstring>/', $response, $matches)) {
                    $faultString = $matches[1];
                }
                
                \Log::error('SUNAT SOAP Fault', ['code' => $faultCode, 'message' => $faultString]);
                
                return [
                    'success' => false,
                    'code' => $faultCode,
                    'description' => 'SUNAT Error: ' . $faultString,
                    'cdr_path' => null
                ];
            }
            
            // Save CDR if response contains it (base64 encoded)
            $cdrSaved = false;
            if (preg_match('/<contentFile>(.*?)<\/contentFile>/s', $response, $matches)) {
                \Log::info('Found contentFile in response');
                $cdrContent = base64_decode($matches[1]);
                \Log::info('CDR content length:', ['length' => strlen($cdrContent)]);
                
                // Check if it's actually a valid zip file (starts with PK)
                if ($cdrContent && strlen($cdrContent) > 100 && substr($cdrContent, 0, 2) === 'PK') {
                    file_put_contents($cdrFile, $cdrContent);
                    $cdrSaved = true;
                    \Log::info('CDR saved to: ' . $cdrFile);
                }
            }
            
            // Also check for responseXml tag which might contain the CDR
            if (!$cdrSaved && preg_match('/<responseXml>(.*?)<\/responseXml>/s', $response, $matches)) {
                \Log::info('Found responseXml in response');
                $cdrContent = base64_decode($matches[1]);
                if ($cdrContent && strlen($cdrContent) > 100) {
                    file_put_contents($cdrFile, $cdrContent);
                    $cdrSaved = true;
                    \Log::info('CDR saved from responseXml');
                }
            }
            
            // Check for ticket (indicates successful processing)
            if (strpos($response, 'ticket') !== false) {
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Documento aceptado por SUNAT (Beta)',
                    'cdr_path' => $cdrSaved ? $filename . '_cdr.zip' : null
                ];
            } else {
                return [
                    'success' => true,
                    'code' => '0',
                    'description' => 'Documento enviado correctamente',
                    'cdr_path' => $cdrSaved ? $filename . '_cdr.zip' : null
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 'ERROR',
                'description' => $e->getMessage()
            ];
        }
    }
    
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'items']);
        
        $company = $invoice->company;
        $customer = $invoice->customer;
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura ' . $invoice->full_number . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .company-info { width: 50%; }
        .invoice-info { width: 40%; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .totals { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>' . ($company->razon_social ?? '') . '</h2>
            <p>RUC: ' . ($company->ruc ?? '') . '</p>
            <p>' . ($company->direccion ?? '') . '</p>
        </div>
        <div class="invoice-info">
            <h1>' . ($invoice->tipo_documento == '01' ? 'FACTURA' : 'BOLETA DE VENTA') . '</h1>
            <p>' . $invoice->full_number . '</p>
            <p>Fecha: ' . $invoice->fecha_emision . '</p>
        </div>
    </div>
    
    <div style="margin-bottom: 20px;">
        <strong>Cliente:</strong><br>
        ' . ($customer->nombre ?? '') . '<br>
        ' . ($customer->documento_tipo == '6' ? 'RUC: ' : 'DNI: ') . ($customer->documento_numero ?? '') . '<br>
        ' . ($customer->direccion ?? '') . '
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th style="text-align:right">Cantidad</th>
                <th style="text-align:right">Precio</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($invoice->items as $item) {
            $html .= '<tr>
                <td>' . ($item->codigo ?? '') . '</td>
                <td>' . ($item->descripcion ?? '') . '</td>
                <td style="text-align:right">' . number_format($item->cantidad, 2) . '</td>
                <td style="text-align:right">S/ ' . number_format($item->precio_unitario, 2) . '</td>
                <td style="text-align:right">S/ ' . number_format($item->precio_venta, 2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="totals">
        <p>Subtotal: S/ ' . number_format($invoice->subtotal, 2) . '</p>
        <p>IGV (18%): S/ ' . number_format($invoice->igv, 2) . '</p>
        <p><strong>Total: S/ ' . number_format($invoice->total, 2) . '</strong></p>
    </div>
</body>
</html>';
        
        $pdf = new \Mpdf\Mpdf();
        $pdf->WriteHTML($html);
        
        return $pdf->Output('', 'S');
    }
}