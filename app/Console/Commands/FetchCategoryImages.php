<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\ImageOptimizer;
use App\Services\WikimediaImageFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FetchCategoryImages extends Command
{
    protected $signature = 'categories:fetch-images
                            {--company= : Procesar solo esta empresa}
                            {--force : Reemplazar aunque la categoría ya tenga imagen}
                            {--limit=0 : Procesar solo N categorías}
                            {--offset=0 : Saltar las primeras N categorías}
                            {--sleep=350 : Milisegundos de espera entre descargas}';

    protected $description = 'Descarga imágenes reales de Wikimedia Commons según el nombre de cada categoría';

    public function handle(ImageOptimizer $optimizer, WikimediaImageFetcher $fetcher): int
    {
        $query = Category::query()->orderBy('id');
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

        $categories = $query->get();
        $force = (bool) $this->option('force');
        $sleepMs = max(0, (int) $this->option('sleep'));

        if ($categories->isEmpty()) {
            $this->warn('No hay categorías para procesar.');

            return self::SUCCESS;
        }

        $fetched = 0;
        $notFound = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            if (!$force && $this->hasFetchedImage($category)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $downloaded = null;
            foreach ($this->buildQueries($category) as $candidate) {
                $result = $fetcher->download($candidate);
                if ($result !== null) {
                    $downloaded = $result;
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
                $path = $optimizer->optimizeFile($downloaded['path'], 'categories');
            } catch (\Throwable $e) {
                $notFound++;
                @unlink($downloaded['path']);
                $bar->advance();
                continue;
            }
            @unlink($downloaded['path']);

            $old = $category->imagen;
            $category->update(['imagen' => $path]);
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

        foreach ($categories->pluck('company_id')->filter()->unique() as $companyId) {
            Cache::forget('kiosko_categories_' . $companyId);
            Cache::forget('restaurant_categories_' . $companyId);
        }

        return self::SUCCESS;
    }

    /**
     * Considera "ya descargada" si la imagen no es una tarjeta generada (720x540, < 20 KB).
     */
    private function hasFetchedImage(Category $category): bool
    {
        if (!$category->imagen) {
            return false;
        }

        $full = Storage::disk('public')->path($category->imagen);
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
    private function buildQueries(Category $category): array
    {
        $raw = mb_strtolower(trim((string) $category->nombre));
        $raw = str_replace(['/', '\\', '"', "'", ',', '.', ';', ':', '-', '_', '(', ')'], ' ', $raw);

        $connectors = ['de', 'del', 'con', 'a', 'al', 'la', 'el', 'los', 'las', 'y', 'o', 'en', 'para', 'sin', 'un', 'una'];
        $fillers = ['categoria', 'categoría', 'menu', 'menú'];

        $words = preg_split('/\s+/u', $raw) ?: [];
        $clean = [];
        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '' || in_array($w, $fillers, true)) {
                continue;
            }
            $clean[] = $w;
        }
        $keywords = array_values(array_filter($clean, fn ($w) => !in_array($w, $connectors, true)));

        $candidates = [];
        if (!empty($keywords)) {
            $candidates[] = implode(' ', $keywords);
            $candidates[] = implode(' ', $keywords) . ' comida';
            $candidates[] = implode(' ', $keywords) . ' food';
            $candidates[] = end($keywords);
        }
        if (!empty($clean) && $clean !== $keywords) {
            $candidates[] = implode(' ', $clean);
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
