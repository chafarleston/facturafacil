<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class VirtualPrebillQrService
{
    /**
     * Genera un QR PNG como data URI (sin archivos ni peticiones externas).
     */
    public static function dataUri(string $content, int $size = 280): string
    {
        if (method_exists(QrCode::class, 'create')) {
            $qrCode = QrCode::create($content);
        } else {
            $qrCode = new QrCode($content);
        }

        if (method_exists($qrCode, 'setSize')) {
            $qrCode->setSize($size);
        }

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
