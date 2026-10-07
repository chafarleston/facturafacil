<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\PlaceholderImageGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateCategoryImages extends Command
{
    protected $signature = 'categories:generate-images
                            {--company= : Generar solo para esta empresa}
                            {--force : Regenerar aunque la categoría ya tenga imagen}';

    protected $description = 'Genera imágenes (tarjetas WebP) para las categorías según su nombre';

    public function handle(PlaceholderImageGenerator $generator): int
    {
        $query = Category::orderBy('id');

        if ($company = $this->option('company')) {
            $query->where('company_id', $company);
        }

        $categories = $query->get();
        $force = (bool) $this->option('force');

        if ($categories->isEmpty()) {
            $this->warn('No hay categorías para procesar.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            if (!$force && $category->imagen && Storage::disk('public')->exists($category->imagen)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $old = $category->imagen;
            $path = $generator->generateCategoryImage($category);
            $category->update(['imagen' => $path]);

            if ($old && $old !== $path) {
                Storage::disk('public')->delete($old);
            }

            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Categorías -> Generadas: {$generated} | Omitidas (ya tenían): {$skipped}");

        foreach ($categories->pluck('company_id')->filter()->unique() as $companyId) {
            Cache::forget('kiosko_categories_' . $companyId);
            Cache::forget('restaurant_categories_' . $companyId);
        }

        return self::SUCCESS;
    }
}
