<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\PlaceholderImageGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateProductImages extends Command
{
    protected $signature = 'products:generate-images
                            {--company= : Generar solo para esta empresa}
                            {--force : Regenerar aunque el producto ya tenga imagen}';

    protected $description = 'Genera imágenes (tarjetas WebP) para los productos según su descripción';

    public function handle(PlaceholderImageGenerator $generator): int
    {
        $query = Product::with('category')->orderBy('id');

        if ($company = $this->option('company')) {
            $query->where('company_id', $company);
        }

        $products = $query->get();
        $force = (bool) $this->option('force');

        if ($products->isEmpty()) {
            $this->warn('No hay productos para procesar.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            if (!$force && $product->imagen && Storage::disk('public')->exists($product->imagen)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $old = $product->imagen;
            $path = $generator->generateProductImage($product);
            $product->update(['imagen' => $path]);

            if ($old && $old !== $path) {
                Storage::disk('public')->delete($old);
            }

            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Productos -> Generadas: {$generated} | Omitidas (ya tenían): {$skipped}");

        foreach ($products->pluck('company_id')->filter()->unique() as $companyId) {
            Cache::forget('kiosko_products_' . $companyId);
            Cache::forget('restaurant_products_' . $companyId);
        }

        return self::SUCCESS;
    }
}
