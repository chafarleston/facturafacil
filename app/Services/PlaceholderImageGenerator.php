<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class PlaceholderImageGenerator
{
    private const WIDTH = 720;
    private const HEIGHT = 540;

    private const PALETTE = [
        [0xE5, 0x39, 0x35], [0xD8, 0x1B, 0x60], [0x8E, 0x24, 0xAA], [0x5E, 0x35, 0xB1],
        [0x39, 0x49, 0xAB], [0x1E, 0x88, 0xE5], [0x03, 0x9B, 0xE5], [0x00, 0x89, 0x7B],
        [0x43, 0xA0, 0x47], [0x7C, 0xB3, 0x42], [0xF4, 0x51, 0x1E], [0xFB, 0x8C, 0x00],
        [0x6D, 0x4C, 0x41], [0x54, 0x6E, 0x7A], [0x00, 0x83, 0x8F], [0xC0, 0x39, 0x2B],
    ];

    private string $boldFont;
    private string $regularFont;

    public function __construct()
    {
        $bold = $this->findFont([
            'arialbd.ttf', 'ARIALBD.TTF', 'segoeuib.ttf',
            'DejaVuSans-Bold.ttf', 'LiberationSans-Bold.ttf',
        ]);
        $regular = $this->findFont([
            'arial.ttf', 'ARIAL.TTF', 'segoeui.ttf',
            'DejaVuSans.ttf', 'LiberationSans-Regular.ttf',
        ]);

        $font = $bold ?? $regular;
        if (!$font) {
            throw new \RuntimeException('No se encontró una fuente TTF (arial/DejaVu/Liberation) para generar las imágenes.');
        }

        $this->boldFont = $bold ?? $font;
        $this->regularFont = $regular ?? $font;
    }

    public function generateProductImage(Product $product, string $directory = 'products'): string
    {
        $subtitle = trim(($product->category->nombre ?? '') . '  ' . ($product->codigo ?? ''));

        return $this->generateCard($product->descripcion, $subtitle, $directory);
    }

    public function generateCategoryImage(Category $category, string $directory = 'categories'): string
    {
        return $this->generateCard($category->nombre, trim((string) $category->descripcion), $directory);
    }

    /**
     * Genera una tarjeta WebP con el título (y subtítulo) y la guarda en el disco public.
     * Devuelve la ruta relativa (ej: products/uuid.webp).
     */
    public function generateCard(string $title, string $subtitle = '', string $directory = 'products'): string
    {
        $title = trim($title);
        if ($title === '') {
            $title = '—';
        }

        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        $rgb = self::PALETTE[crc32($title) % count(self::PALETTE)];
        $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($im, 0, 0, self::WIDTH, self::HEIGHT, $bg);

        imagealphablending($im, true);
        $circle = imagecolorallocatealpha($im, 255, 255, 255, 105);
        imagefilledellipse($im, self::WIDTH - 70, 70, 220, 220, $circle);
        imagefilledellipse($im, 60, self::HEIGHT - 60, 160, 160, $circle);

        $cardX1 = 40;
        $cardY1 = 70;
        $cardX2 = self::WIDTH - 40;
        $cardY2 = self::HEIGHT - 70;
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, $cardX1, $cardY1, $cardX2, $cardY2, $white);
        imagefilledrectangle($im, $cardX1, $cardY1, $cardX2, $cardY1 + 14, $bg);

        $dark = imagecolorallocate($im, 0x21, 0x21, 0x21);
        $gray = imagecolorallocate($im, 0x70, 0x70, 0x70);
        $maxTextWidth = ($cardX2 - $cardX1) - 70;

        $size = 46;
        $lines = [];
        while ($size >= 20) {
            $lines = $this->wrapText($title, $this->boldFont, $size, $maxTextWidth);
            $lineHeight = (int) round($size * 1.35);
            if (count($lines) * $lineHeight <= ($cardY2 - $cardY1) - 160) {
                break;
            }
            $size -= 4;
        }

        $lineHeight = (int) round($size * 1.35);
        $blockHeight = count($lines) * $lineHeight;
        $startY = $cardY1 + 30 + (int) ((($cardY2 - $cardY1) - 60 - $blockHeight) / 2) + $size;

        foreach ($lines as $i => $line) {
            $bbox = imagettfbbox($size, 0, $this->boldFont, $line);
            $lineW = $bbox[2] - $bbox[0];
            $x = $cardX1 + (int) ((($cardX2 - $cardX1) - $lineW) / 2);
            $y = $startY + ($i * $lineHeight);
            imagettftext($im, $size, 0, $x, $y, $dark, $this->boldFont, $line);
        }

        if ($subtitle !== '') {
            $fsize = 18;
            $bbox = imagettfbbox($fsize, 0, $this->regularFont, $subtitle);
            $fw = $bbox[2] - $bbox[0];
            $fx = $cardX1 + (int) ((($cardX2 - $cardX1) - $fw) / 2);
            imagettftext($im, $fsize, 0, $fx, $cardY2 - 24, $gray, $this->regularFont, $subtitle);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pimg');
        imagewebp($im, $tmp, 80);
        imagedestroy($im);

        $path = trim($directory, '/') . '/' . bin2hex(random_bytes(16)) . '.webp';
        Storage::disk('public')->put($path, file_get_contents($tmp));
        @unlink($tmp);

        return $path;
    }

    private function wrapText(string $text, string $font, int $size, int $maxWidth): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $test = $current === '' ? $word : $current . ' ' . $word;
            $bbox = imagettfbbox($size, 0, $font, $test);
            $width = $bbox[2] - $bbox[0];
            if ($width > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $test;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function findFont(array $names): ?string
    {
        $dirs = [
            rtrim((string) (getenv('WINDIR') ?: 'C:\\Windows'), '\\') . '\\Fonts\\',
            '/usr/share/fonts/truetype/dejavu/',
            '/usr/share/fonts/truetype/liberation/',
            '/usr/share/fonts/truetype/msttcorefonts/',
            '/Library/Fonts/',
            '/System/Library/Fonts/',
        ];

        foreach ($names as $name) {
            foreach ($dirs as $dir) {
                if (is_file($dir . $name)) {
                    return $dir . $name;
                }
            }
        }

        return null;
    }
}
