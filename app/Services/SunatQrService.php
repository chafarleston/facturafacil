<?php

namespace App\Services;

use App\Models\Invoice as InvoiceModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Support\Facades\Storage;

class SunatQrService
{
    public static function generateForInvoice(InvoiceModel $invoice): ?string
    {
        if (!$invoice || !$invoice->id) return null;

        // Build SUNAT data string: ruc|serie|correlativo|igv|total|fecha_emision|tipo_doc_cliente|doc_cliente
        $ruc = $invoice->company->ruc ?? '';
        $serie = $invoice->serie ?? '';
        $correlativo = $invoice->numero ?? '';
        $igv = number_format((float) ($invoice->igv ?? 0), 2, '.', '');
        $total = number_format((float) ($invoice->total ?? 0), 2, '.', '');
        $fechaEmision = $invoice->fecha_emision ? date('Y-m-d', strtotime($invoice->fecha_emision)) : date('Y-m-d');
        $cliente = $invoice->customer;
        $tipoDocCliente = $cliente ? ($cliente->documento_tipo ?? '') : '';
        $docCliente = $cliente ? ($cliente->documento_numero ?? '') : '';

        $data = trim("$ruc|$serie|$correlativo|$igv|$total|$fechaEmision|$tipoDocCliente|$docCliente");

        // Generate QR PNG in storage
        $filename = 'sunat_qr_invoice_' . $invoice->id . '.png';
        $path = storage_path('app/public/qrcodes/' . $filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Create QR code in a way that's compatible with different Endroid QR Code versions
        // Prefer static factory if available (Endroid\QrCode\QrCode::create)
        if (method_exists(QrCode::class, 'create')) {
            $qrCode = QrCode::create($data);
        } else {
            $qrCode = new QrCode($data);
        }
        // Apply size if the method exists (compatible with v3/v4)
        if (method_exists($qrCode, 'setSize')) {
            $qrCode->setSize(300);
        }
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        file_put_contents($path, $result->getString());

        // Return a data URI to ensure MPDF renders the image without needing external HTTP access
        if (is_readable($path)) {
            $imageData = base64_encode(file_get_contents($path));
            return 'data:image/png;base64,' . $imageData;
        }

        // Fallback to a public URL if for some reason we can't read the file
        return asset('storage/qrcodes/' . $filename);
    }
}
