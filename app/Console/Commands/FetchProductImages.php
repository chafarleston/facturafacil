<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ImageOptimizer;
use App\Services\WikimediaImageFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FetchProductImages extends Command
{
    protected $signature = 'products:fetch-images
                            {--company= : Procesar solo esta empresa}
                            {--force : Reemplazar aunque el producto ya tenga imagen}
                            {--limit=0 : Procesar solo N productos}
                            {--offset=0 : Saltar los primeros N productos}
                            {--sleep=350 : Milisegundos de espera entre descargas}';

    protected $description = 'Descarga imágenes reales de Wikimedia Commons según la descripción de cada producto';

    public function handle(ImageOptimizer $optimizer, WikimediaImageFetcher $fetcher): int
    {
        $query = Product::with('category')->orderBy('id');
        if ($company = $this->option('company')) {
            $query->where('company_id', $company);
        }

        $limit = (int) $this->option('limit');
        $offset = max(0, (int) $this->option('offset'));
        if ($offset > 0) {
            $query->offset($offset);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        $products = $query->get();
        $force = (bool) $this->option('force');
        $sleepMs = max(0, (int) $this->option('sleep'));

        if ($products->isEmpty()) {
            $this->warn('No hay productos para procesar.');

            return self::SUCCESS;
        }

        $fetched = 0;
        $notFound = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            if (!$force && $this->hasFetchedImage($product)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $downloaded = null;
            $usedQuery = '';
            foreach ($this->buildQueries($product) as $candidate) {
                $result = $fetcher->download($candidate);
                if ($result !== null) {
                    $downloaded = $result;
                    $usedQuery = $candidate;
                    break;
                }
                usleep(150000);
            }

            if ($downloaded === null) {
                $notFound++;
                $bar->advance();
                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
                continue;
            }

            try {
                $path = $optimizer->optimizeFile($downloaded['path'], 'products');
            } catch (\Throwable $e) {
                $notFound++;
                @unlink($downloaded['path']);
                $bar->advance();
                continue;
            }
            @unlink($downloaded['path']);

            $old = $product->imagen;
            $product->update(['imagen' => $path]);
            if ($old && $old !== $path) {
                Storage::disk('public')->delete($old);
            }

            $fetched++;
            $bar->advance();

            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Con imagen descargada: {$fetched} | Sin resultados: {$notFound} | Omitidas: {$skipped}");

        foreach ($products->pluck('company_id')->filter()->unique() as $companyId) {
            Cache::forget('kiosko_products_' . $companyId);
            Cache::forget('restaurant_products_' . $companyId);
        }

        return self::SUCCESS;
    }

    /**
     * Considera "ya descargada" si la imagen no es una tarjeta generada (720x540, < 20 KB).
     */
    private function hasFetchedImage(Product $product): bool
    {
        if (!$product->imagen) {
            return false;
        }

        $full = Storage::disk('public')->path($product->imagen);
        if (!is_file($full)) {
            return false;
        }

        $info = @getimagesize($full);
        if ($info === false) {
            return false;
        }

        $isCard = ($info[0] === 720 && $info[1] === 540 && filesize($full) < 20000);

        return !$isCard;
    }

    /**
     * Construye consultas candidatas, de la más específica a la más amplia.
     *
     * @return array<int, string>
     */
    private function buildQueries(Product $product): array
    {
        $desc = mb_strtolower(trim((string) $product->descripcion));
        $desc = preg_replace('/\([^)]*\)/u', ' ', $desc) ?? $desc;
        $desc = str_replace(['/', '\\', '"', "'", ',', '.', ';', ':', '-', '_'], ' ', $desc);
        $desc = preg_replace('/\d+([.,]\d+)?/u', ' ', $desc) ?? $desc;

        $stopUnits = ['kg', 'kgs', 'gr', 'grs', 'gramos', 'ml', 'lt', 'lts', 'litro', 'litros', 'onza', 'onzas', 'oz', 'und', 'unid', 'unidad', 'x'];
        $connectors = ['de', 'del', 'con', 'a', 'al', 'la', 'el', 'los', 'las', 'y', 'o', 'en', 'para', 'sin', 'un', 'una'];
        $fillers = ['adicional', 'adic', 'adicionales', 'extra', 'descarte', 'descartable', 'desechable', 'combo', 'promo', 'promocion', 'promoción', 'menu', 'menú', 'nuevo', 'nueva', 'especial'];

        $words = preg_split('/\s+/u', $desc) ?: [];
        $clean = [];
        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '' || in_array($w, $stopUnits, true) || in_array($w, $fillers, true)) {
                continue;
            }
            $clean[] = $w;
        }

        $full = trim(implode(' ', $clean));
        $keywords = array_values(array_filter($clean, fn ($w) => !in_array($w, $connectors, true)));

        $candidates = [];
        $category = mb_strtolower(trim((string) ($product->category->nombre ?? '')));

        if (!empty($keywords)) {
            $candidates[] = implode(' ', $keywords);
        }
        if ($full !== '' && $full !== implode(' ', $keywords)) {
            $candidates[] = $full;
        }
        if ($category !== '' && !empty($keywords)) {
            $candidates[] = $category . ' ' . implode(' ', $keywords);
        }
        if ($category !== '') {
            $candidates[] = $category;
        }
        if (!empty($keywords)) {
            $candidates[] = end($keywords);
        }

        $unique = [];
        foreach ($candidates as $c) {
            $c = trim($c);
            if ($c !== '' && !in_array($c, $unique, true)) {
                $unique[] = $c;
            }
        }

        return $unique;
    }
}
