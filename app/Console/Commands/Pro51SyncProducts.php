<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Product;
use App\Services\Pro51ApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Pro51SyncProducts extends Command
{
    protected $signature = 'pro51:sync-products {company_id?}';
    protected $description = 'Sincroniza productos pendientes con pro51';

    public function handle(): int
    {
        $companyId = $this->argument('company_id');

        $companies = Company::when($companyId, fn($q) => $q->where('id', $companyId))
            ->where('facturacion_mode', 'api_externa')
            ->where('estado', 'ACTIVO')
            ->get();

        if ($companies->isEmpty()) {
            $this->warn('No hay empresas con facturación externa activa');
            return 0;
        }

        $totalSynced = 0;
        $totalErrors = 0;

        foreach ($companies as $company) {
            $this->info("Procesando empresa: {$company->razon_social}");

            $products = Product::where('company_id', $company->id)
                ->where('estado', 'ACTIVO')
                ->where(function ($q) {
                    $q->whereNull('pro51_synced_at')
                      ->orWhereColumn('pro51_synced_at', '<', 'updated_at');
                })
                ->get();

            if ($products->isEmpty()) {
                $this->line('  Sin productos pendientes');
                continue;
            }

            try {
                $api = new Pro51ApiService($company);
            } catch (\Exception $e) {
                $this->error("  Error al conectar: {$e->getMessage()}");
                $totalErrors += $products->count();
                continue;
            }

            foreach ($products as $product) {
                try {
                    $igvType = Pro51ApiService::getIgvTypeCode($product->tipo_afectacion);
                    $igvPercent = $company->getActiveIgvPercent();

                    $data = [
                        'codigo_interno' => $product->codigo,
                        'descripcion' => $product->descripcion,
                        'nombre' => $product->descripcion,
                        'unidad_de_medida' => $product->umedida_codigo ?? 'NIU',
                        'precio_unitario' => (float) $product->precio,
                        'codigo_tipo_afectacion_igv' => $igvType,
                        'porcentaje_igv' => $igvPercent,
                    ];

                    $response = $api->syncProduct($data);

                    if ($response['success'] ?? false) {
                        $product->update([
                            'pro51_codigo_interno' => $product->codigo,
                            'pro51_synced_at' => now(),
                        ]);
                        $totalSynced++;
                        $this->line("  ✓ {$product->codigo} - {$product->descripcion}");
                    } else {
                        $totalErrors++;
                        $this->error("  ✗ {$product->codigo}: {$response['message']}");
                    }
                } catch (\Exception $e) {
                    $totalErrors++;
                    $this->error("  ✗ {$product->codigo}: {$e->getMessage()}");
                    Log::error('pro51 sync error', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info("Sincronización completada: {$totalSynced} sincronizados, {$totalErrors} errores");

        return 0;
    }
}
