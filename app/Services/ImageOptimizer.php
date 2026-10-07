<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    private int $maxDimension;
    private int $quality;

    public function __construct(int $maxDimension = 800, int $quality = 80)
    {
        $this->maxDimension = $maxDimension;
        $this->quality = $quality;
    }

    /**
     * Redimensiona y comprime la imagen subida y la guarda (WebP) en el disco public.
     * Devuelve la ruta relativa (ej: products/uuid.webp).
     */
    public function optimize(UploadedFile $file, string $directory): string
    {
        return $this->optimizeFile($file->getRealPath(), $directory);
    }

    /**
     * Igual que optimize() pero a partir de la ruta de un archivo existente.
     */
    public function optimizeFile(string $sourcePath, string $directory): string
    {
        $contents = @file_get_contents($sourcePath);
        if ($contents === false) {
            throw new \RuntimeException('No se pudo leer la imagen.');
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            throw new \RuntimeException('Formato de imagen no soportado.');
        }

        $image = $this->applyExifOrientation($image, $sourcePath);
        $image = $this->resize($image);

        $tmpPath = tempnam(sys_get_temp_dir(), 'img');
        if ($tmpPath === false) {
            imagedestroy($image);
            throw new \RuntimeException('No se pudo crear el archivo temporal.');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        $saved = imagewebp($image, $tmpPath, $this->quality);
        imagedestroy($image);

        if (!$saved) {
            @unlink($tmpPath);
            throw new \RuntimeException('No se pudo generar la imagen optimizada.');
        }

        $filename = trim($directory, '/') . '/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($filename, file_get_contents($tmpPath));
        @unlink($tmpPath);

        return $filename;
    }

    private function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $max = max($width, $height);

        if ($max <= $this->maxDimension) {
            return $image;
        }

        $ratio = $this->maxDimension / $max;
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    private function applyExifOrientation(\GdImage $image, string $sourcePath): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $mime = @mime_content_type($sourcePath) ?: '';
        if (!in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);
        if (!$exif || empty($exif['Orientation'])) {
            return $image;
        }

        switch ((int) $exif['Orientation']) {
            case 3:
                $rotated = imagerotate($image, 180, 0);
                break;
            case 6:
                $rotated = imagerotate($image, -90, 0);
                break;
            case 8:
                $rotated = imagerotate($image, 90, 0);
                break;
            default:
                return $image;
        }

        if ($rotated !== false) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }
}
