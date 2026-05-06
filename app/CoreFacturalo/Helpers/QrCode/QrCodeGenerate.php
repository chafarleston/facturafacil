<?php

namespace App\CoreFacturalo\Helpers\QrCode;

use Mpdf\QrCode\QrCode;

class QrCodeGenerate
{
    public function displayPNGBase64($value, $w = 150, $level = 'L', $background = [255, 255, 255], $color = [0, 0, 0], $filename = null, $quality = 0)
    {
        $qrCode = new QrCode($value, $level);

        ob_start();

        $qrCode->displayPNG($w, $background, $color, $filename, $quality);
        $image = ob_get_clean();
        return base64_encode($image);
    }
}