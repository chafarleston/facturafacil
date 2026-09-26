<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SunatAlert;
use Illuminate\Support\Facades\Cache;

class SunatAlertService
{
    public const TIPO_BOLETAS = 'sunat_boletas';

    public static function raise(string $tipo, string $descripcion): SunatAlert
    {
        $existing = SunatAlert::active()->where('tipo', $tipo)->first();
        if ($existing) {
            $existing->update(['descripcion' => $descripcion]);
            self::forgetCountCache();

            return $existing;
        }

        $alert = SunatAlert::create([
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'estado' => 'ACTIVO',
        ]);
        self::forgetCountCache();

        return $alert;
    }

    /**
     * Auto-resuelve la alerta cuando ya no quedan boletas PENDIENTE de días anteriores.
     * Se invoca en los momentos naturales (envío/resumen/check), sin polling continuo.
     */
    public static function evaluate(): void
    {
        $pending = Invoice::where('tipo_documento', '03')
            ->where('sunat_estado', 'PENDIENTE')
            ->whereDate('fecha_emision', '<', now()->toDateString())
            ->exists();

        if (!$pending) {
            SunatAlert::active()->where('tipo', self::TIPO_BOLETAS)->get()->each->resolve();
            self::forgetCountCache();
        }
    }

    public static function activeCount(): int
    {
        return Cache::remember('sunat_alerts_active_count', 60, fn () => SunatAlert::active()->count());
    }

    public static function forgetCountCache(): void
    {
        Cache::forget('sunat_alerts_active_count');
    }
}